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

$tipoDenunciaIds = $_POST['tipo_denuncia_id'] ?? [];
if (!is_array($tipoDenunciaIds)) {
    $tipoDenunciaIds = [$tipoDenunciaIds];
}
$tipoDenunciaIds = array_values(array_unique(array_filter(array_map('intval', $tipoDenunciaIds))));
$descricao      = trim($_POST['descricao'] ?? '');
$local          = trim($_POST['local_ocorrencia'] ?? '');
$envolvidos     = trim($_POST['envolvidos'] ?? '');
$dataOcorrencia = trim($_POST['data_ocorrencia'] ?? '');
$anonima        = isset($_POST['anonima']) ? 1 : 0;

if ($tipoDenunciaIds === [] || $descricao === '') {
    flashSet('danger', 'Selecione ao menos um tipo de ocorrência e descreva o que aconteceu.');
    header('Location: denuncia_nova.php');
    exit;
}

$pdo = getConexao();

$inQuery = implode(',', array_fill(0, count($tipoDenunciaIds), '?'));
$stmt = $pdo->prepare("SELECT id FROM tipos_denuncia WHERE id IN ({$inQuery}) AND ativo = 1");
$stmt->execute($tipoDenunciaIds);
$tiposValidos = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
if (count($tiposValidos) !== count($tipoDenunciaIds)) {
    flashSet('danger', 'Um ou mais tipos de denúncia são inválidos.');
    header('Location: denuncia_nova.php');
    exit;
}

$tipoDenunciaId = $tipoDenunciaIds[0];

$usuario = usuarioLogado();

try {
    $caminhoImagem = salvarAnexo('anexo_imagem', TIPOS_IMAGEM_PERMITIDOS, 8);
    $caminhoAudio  = salvarAnexo('anexo_audio', TIPOS_AUDIO_PERMITIDOS, 15);
} catch (RuntimeException $e) {
    flashSet('danger', $e->getMessage());
    header('Location: denuncia_nova.php');
    exit;
}

try {
    $pdo->beginTransaction();

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

    $denunciaId = (int) $pdo->lastInsertId();
    salvarTiposDenuncia($pdo, $denunciaId, $tipoDenunciaIds);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
       $pdo->rollBack();
    }
    flashSet('danger', 'Não foi possível registrar a denúncia. Tente novamente.');
    header('Location: denuncia_nova.php');
    exit;
}

flashSet('success', 'Sua denúncia foi enviada com sucesso. A equipe de apoio irá analisar o caso.');
header('Location: minhas_denuncias.php');
exit;
