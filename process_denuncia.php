<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
exigirTipo([1]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: denuncia_nova.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    flashSet('danger', 'Sessão expirada. Tente novamente.');
    header('Location: denuncia_nova.php');
    exit;
}

$tipoDenunciaId = (int) ($_POST['tipo_denuncia_id'] ?? 0);
$descricao      = trim($_POST['descricao'] ?? '');
$local          = trim($_POST['local_ocorrencia'] ?? '');
$envolvidos     = trim($_POST['envolvidos'] ?? '');
$dataOcorrencia = trim($_POST['data_ocorrencia'] ?? '');
$anonima        = isset($_POST['anonima']) ? 1 : 0;

if ($tipoDenunciaId <= 0 || $descricao === '') {
    flashSet('danger', 'Selecione o tipo de ocorrência e descreva o que aconteceu.');
    header('Location: denuncia_nova.php');
    exit;
}

$pdo = getConexao();

$stmt = $pdo->prepare('SELECT id FROM tipos_denuncia WHERE id = ? AND ativo = 1');
$stmt->execute([$tipoDenunciaId]);
if (!$stmt->fetch()) {
    flashSet('danger', 'Tipo de denúncia inválido.');
    header('Location: denuncia_nova.php');
    exit;
}

$usuario = usuarioLogado();

try {
    $caminhoImagem = salvarAnexo('anexo_imagem', TIPOS_IMAGEM_PERMITIDOS, 8);
    $caminhoAudio  = salvarAnexo('anexo_audio', TIPOS_AUDIO_PERMITIDOS, 15);
} catch (RuntimeException $e) {
    flashSet('danger', $e->getMessage());
    header('Location: denuncia_nova.php');
    exit;
}

$stmt = $pdo->prepare('INSERT INTO denuncias
    (usuario_id, tipo_denuncia_id, anonima, local_ocorrencia, envolvidos, data_ocorrencia, descricao, anexo_imagem, anexo_audio, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pendente")');
$stmt->execute([
    $usuario['id'],
    $tipoDenunciaId,
    $anonima,
    $local !== '' ? $local : null,
    $envolvidos !== '' ? $envolvidos : null,
    $dataOcorrencia !== '' ? $dataOcorrencia : null,
    $descricao,
    $caminhoImagem,
    $caminhoAudio,
]);

flashSet('success', 'Sua denúncia foi enviada com sucesso. A equipe de apoio irá analisar o caso.');
header('Location: minhas_denuncias.php');
exit;
