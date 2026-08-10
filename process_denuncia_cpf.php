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
$tipoDenunciaId = (int) ($_POST['tipo_denuncia_id'] ?? 0);
$descricao      = trim($_POST['descricao'] ?? '');
$local          = trim($_POST['local_ocorrencia'] ?? '');
$dataOcorrencia = trim($_POST['data_ocorrencia'] ?? '');

if (!cpfValido($cpf)) {
    flashSet('danger', 'Informe um CPF válido.');
    header('Location: denuncia_cpf.php');
    exit;
}
if ($tipoDenunciaId <= 0 || $descricao === '') {
    flashSet('danger', 'Selecione o tipo de ocorrência e descreva o que aconteceu.');
    header('Location: denuncia_cpf.php');
    exit;
}

$pdo = getConexao();

$stmt = $pdo->prepare('SELECT id FROM tipos_denuncia WHERE id = ? AND ativo = 1');
$stmt->execute([$tipoDenunciaId]);
if (!$stmt->fetch()) {
    flashSet('danger', 'Tipo de denúncia inválido.');
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

$stmt = $pdo->prepare('INSERT INTO denuncias
    (usuario_id, cpf_denunciante, protocolo, tipo_denuncia_id, anonima, local_ocorrencia, envolvidos, data_ocorrencia, descricao, anexo_imagem, anexo_audio, status)
    VALUES (?, ?, ?, ?, 0, ?, NULL, ?, ?, ?, ?, "pendente")');
$stmt->execute([
    $usuarioExistente['id'] ?? null,
    $usuarioExistente ? null : $cpf,
    $protocolo,
    $tipoDenunciaId,
    $local !== '' ? $local : null,
    $dataOcorrencia !== '' ? $dataOcorrencia : null,
    $descricao,
    $caminhoImagem,
    $caminhoAudio,
]);

$_SESSION['protocolo_gerado'] = $protocolo;
header('Location: protocolo_gerado.php');
exit;
