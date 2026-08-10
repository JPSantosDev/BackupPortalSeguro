<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
exigirTipo([3]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_usuarios.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    flashSet('danger', 'Sessão expirada. Tente novamente.');
    header('Location: admin_usuarios.php');
    exit;
}

$pdo = getConexao();
$acao = $_POST['acao'] ?? '';

if ($acao === 'criar') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo  = (int) ($_POST['tipo_usuario'] ?? 0);

    if ($nome === '' || $email === '' || $senha === '' || !in_array($tipo, [2, 3], true)) {
        flashSet('danger', 'Preencha todos os campos corretamente.');
        header('Location: admin_usuarios.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flashSet('danger', 'Informe um e-mail válido.');
        header('Location: admin_usuarios.php');
        exit;
    }

    if (strlen($senha) < 6) {
        flashSet('danger', 'A senha deve ter pelo menos 6 caracteres.');
        header('Location: admin_usuarios.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flashSet('danger', 'Já existe um usuário com este e-mail.');
        header('Location: admin_usuarios.php');
        exit;
    }

    $hash = password_hash($senha, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nome, $email, $hash, $tipo]);

    flashSet('success', 'Conta criada com sucesso.');
} elseif ($acao === 'alternar_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $usuarioAtual = usuarioLogado();

    if ($id === $usuarioAtual['id']) {
        flashSet('danger', 'Você não pode alterar o status da própria conta.');
        header('Location: admin_usuarios.php');
        exit;
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET ativo = NOT ativo WHERE id = ?');
    $stmt->execute([$id]);
    flashSet('success', 'Status do usuário atualizado.');
}

header('Location: admin_usuarios.php');
exit;
