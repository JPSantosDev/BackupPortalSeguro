<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    flashSet('danger', 'Sessão expirada. Tente novamente.');
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    flashSet('danger', 'Preencha e-mail e senha para continuar.');
    header('Location: index.php');
    exit;
}

$pdo = getConexao();
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    flashSet('danger', 'E-mail ou senha incorretos.');
    header('Location: index.php');
    exit;
}

if ((int) $usuario['ativo'] !== 1) {
    flashSet('danger', 'Esta conta está desativada. Procure a coordenação.');
    header('Location: index.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['usuario_id']    = $usuario['id'];
$_SESSION['usuario_nome']  = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_tipo']  = (int) $usuario['tipo_usuario'];

header('Location: home.php');
exit;
