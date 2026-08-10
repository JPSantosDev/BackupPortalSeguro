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
