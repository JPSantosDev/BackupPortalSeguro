<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: denuncia_cpf.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    flashSet('danger', 'Sessão expirada. Tente novamente.');
    header('Location: denuncia_cpf.php');
    exit;
}

$cpf            = somenteDigitos($_POST['cpf'] ?? '');
$tipoDenunciaIds = $_POST['tipo_denuncia_id'] ?? [];
if (!is_array($tipoDenunciaIds)) {
    $tipoDenunciaIds = [$tipoDenunciaIds];
}
$tipoDenunciaIds = array_values(array_unique(array_filter(array_map('intval', $tipoDenunciaIds))));
$descricao      = trim($_POST['descricao'] ?? '');
$local          = trim($_POST['local_ocorrencia'] ?? '');
$dataOcorrencia = trim($_POST['data_ocorrencia'] ?? '');

if (!cpfValido($cpf)) {
    flashSet('danger', 'Informe um CPF válido.');
    header('Location: denuncia_cpf.php');
    exit;
}
if ($tipoDenunciaIds === [] || $descricao === '') {
    flashSet('danger', 'Selecione ao menos um tipo de ocorrência e descreva o que aconteceu.');
    header('Location: denuncia_cpf.php');
    exit;
}

$pdo = getConexao();

$inQuery = implode(',', array_fill(0, count($tipoDenunciaIds), '?'));
$stmt = $pdo->prepare("SELECT id FROM tipos_denuncia WHERE id IN ({$inQuery}) AND ativo = 1");
$stmt->execute($tipoDenunciaIds);
$tiposValidos = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
if (count($tiposValidos) !== count($tipoDenunciaIds)) {
    flashSet('danger', 'Um ou mais tipos de denúncia são inválidos.');
    header('Location: denuncia_cpf.php');
    exit;
}

try {
    $caminhoImagem = salvarAnexo('anexo_imagem', TIPOS_IMAGEM_PERMITIDOS, 8);
    $caminhoAudio  = salvarAnexo('anexo_audio', TIPOS_AUDIO_PERMITIDOS, 15);
} catch (RuntimeException $e) {
    flashSet('danger', $e->getMessage());
    header('Location: denuncia_cpf.php');
    exit;
}

// Se já existe uma conta com este CPF, a denúncia já entra vinculada a ela
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE cpf = ? LIMIT 1');
$stmt->execute([$cpf]);
$usuarioExistente = $stmt->fetch();

$protocolo = gerarProtocolo($pdo);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO denuncias
        (usuario_id, cpf_denunciante, protocolo, anonima, local_ocorrencia, envolvidos, data_ocorrencia, descricao, anexo_imagem, anexo_audio, status)
        VALUES (?, ?, ?, 0, ?, NULL, ?, ?, ?, ?, "pendente")');
    $stmt->execute([
        $usuarioExistente['id'] ?? null,
        $usuarioExistente ? null : $cpf,
        $protocolo,
        $local !== '' ? $local : null,
        $dataOcorrencia !== '' ? $dataOcorrencia : null,
        $descricao,
        $caminhoImagem,
        $caminhoAudio,
    ]);

    $denunciaId = (int) $pdo->lastInsertId();
    salvarTiposDenuncia($pdo, $denunciaId, $tipoDenunciaIds);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flashSet('danger', 'Não foi possível registrar a denúncia. Tente novamente.');
    header('Location: denuncia_cpf.php');
    exit;
}

$_SESSION['protocolo_gerado'] = $protocolo;
header('Location: protocolo_gerado.php');
exit;
