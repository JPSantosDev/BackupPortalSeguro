<?php
/**
 * Voz School - Controle de sessão e permissões
 * tipo_usuario: 1 = Denunciante (aluno) | 2 = Atendente | 3 = Administrador
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function usuarioLogado(): ?array
{
    if (!estaLogado()) {
        return null;
    }
    return [
        'id'    => $_SESSION['usuario_id'],
        'nome'  => $_SESSION['usuario_nome'],
        'email' => $_SESSION['usuario_email'],
        'tipo'  => (int) $_SESSION['usuario_tipo'],
        'tutorial_visto' => (int) ($_SESSION['usuario_tutorial_visto'] ?? 0),
    ];
}

/** Redireciona para o login se não estiver autenticado */
function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: index.php');
        exit;
    }
}

/** Exige um ou mais tipos de usuário para acessar a página atual */
function exigirTipo(array $tiposPermitidos): void
{
    exigirLogin();
    $usuario = usuarioLogado();
    if (!in_array($usuario['tipo'], $tiposPermitidos, true)) {
        header('Location: home.php');
        exit;
    }
}

function nomeTipoUsuario(int $tipo): string
{
    return match ($tipo) {
        1 => 'Denunciante',
        2 => 'Atendente',
        3 => 'Administrador',
        default => 'Desconhecido',
    };
}

/** Gera e valida token CSRF simples para os formulários */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfValido(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flashSet(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function flashPegar(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* =========================================================
 * CPF
 * ========================================================= */

/** Remove tudo que não é dígito */
function somenteDigitos(string $valor): string
{
    return preg_replace('/\D/', '', $valor) ?? '';
}

/** Valida CPF (dígitos verificadores) */
function cpfValido(string $cpf): bool
{
    $cpf = somenteDigitos($cpf);
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cpf[$i] * (($t + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

function formatarCpf(string $cpf): string
{
    $cpf = somenteDigitos($cpf);
    if (strlen($cpf) !== 11) return $cpf;
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/* =========================================================
 * Protocolo de acompanhamento (denúncias sem cadastro)
 * ========================================================= */

function gerarProtocolo(PDO $pdo): string
{
    do {
        $protocolo = 'VS' . date('y') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $stmt = $pdo->prepare('SELECT id FROM denuncias WHERE protocolo = ?');
        $stmt->execute([$protocolo]);
    } while ($stmt->fetch());
    return $protocolo;
}

/* =========================================================
 * Upload de anexos (imagem / áudio das denúncias)
 * ========================================================= */

const UPLOAD_DIR = __DIR__ . '/../uploads/denuncias';
const UPLOAD_URL = 'uploads/denuncias';

const TIPOS_IMAGEM_PERMITIDOS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const TIPOS_AUDIO_PERMITIDOS  = ['audio/webm' => 'webm', 'audio/ogg' => 'ogg', 'audio/mpeg' => 'mp3', 'audio/mp4' => 'm4a', 'audio/wav' => 'wav'];

/**
 * Move um arquivo enviado ($_FILES[campo]) para a pasta de uploads,
 * validando tipo e tamanho. Retorna o caminho relativo salvo no banco
 * ou null se nenhum arquivo válido foi enviado.
 * Lança RuntimeException em caso de erro de validação.
 */
function salvarAnexo(string $campo, array $tiposPermitidos, int $tamanhoMaximoMb): ?string
{
    if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $arquivo = $_FILES[$campo];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha ao enviar o arquivo. Tente novamente.');
    }
    if ($arquivo['size'] > $tamanhoMaximoMb * 1024 * 1024) {
        throw new RuntimeException("O arquivo deve ter no máximo {$tamanhoMaximoMb}MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($arquivo['tmp_name']);
    if (!isset($tiposPermitidos[$mime])) {
        throw new RuntimeException('Formato de arquivo não suportado.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$mime];
    $destino = UPLOAD_DIR . '/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        throw new RuntimeException('Não foi possível salvar o arquivo enviado.');
    }

    return UPLOAD_URL . '/' . $nomeArquivo;
}
