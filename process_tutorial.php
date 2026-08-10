<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
exigirLogin();

header('Content-Type: application/json');

$usuario = usuarioLogado();
$pdo = getConexao();
$stmt = $pdo->prepare('UPDATE usuarios SET tutorial_visto = 1 WHERE id = ?');
$stmt->execute([$usuario['id']]);
$_SESSION['usuario_tutorial_visto'] = 1;

echo json_encode(['ok' => true]);
