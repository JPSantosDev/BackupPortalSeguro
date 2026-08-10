<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
exigirTipo([2]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: atendente_denuncias.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    flashSet('danger', 'Sessão expirada. Tente novamente.');
    header('Location: atendente_denuncias.php');
    exit;
}

$id       = (int) ($_POST['id'] ?? 0);
$status   = $_POST['status'] ?? '';
$resposta = trim($_POST['resposta'] ?? '');

$statusValidos = ['pendente', 'em_andamento', 'resolvida', 'arquivada'];
if ($id <= 0 || !in_array($status, $statusValidos, true)) {
    flashSet('danger', 'Dados inválidos.');
    header('Location: atendente_denuncias.php');
    exit;
}

$pdo = getConexao();
$usuario = usuarioLogado();

$stmt = $pdo->prepare('UPDATE denuncias SET status = ?, resposta = ?, atendente_id = ? WHERE id = ?');
$stmt->execute([$status, $resposta !== '' ? $resposta : null, $usuario['id'], $id]);

flashSet('success', 'Denúncia atualizada com sucesso.');
header('Location: atendente_denuncia_detalhe.php?id=' . $id);
exit;
