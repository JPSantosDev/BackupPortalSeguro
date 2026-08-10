<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    flashSet('danger', 'Sessão expirada. Tente novamente.');
    header('Location: index.php?aba=cadastro');
    exit;
}

$nome   = trim($_POST['nome'] ?? '');
$turma  = trim($_POST['turma'] ?? '');
$cpf    = somenteDigitos($_POST['cpf'] ?? '');
$email  = trim($_POST['email'] ?? '');
$senha  = $_POST['senha'] ?? '';
$senha2 = $_POST['senha_confirmacao'] ?? '';

if ($nome === '' || $email === '' || $senha === '' || $cpf === '') {
    flashSet('danger', 'Preencha todos os campos obrigatórios.');
    header('Location: index.php?aba=cadastro');
    exit;
}

if (!cpfValido($cpf)) {
    flashSet('danger', 'Informe um CPF válido.');
    header('Location: index.php?aba=cadastro');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashSet('danger', 'Informe um e-mail válido.');
    header('Location: index.php?aba=cadastro');
    exit;
}

if (strlen($senha) < 6) {
    flashSet('danger', 'A senha deve ter pelo menos 6 caracteres.');
    header('Location: index.php?aba=cadastro');
    exit;
}

if ($senha !== $senha2) {
    flashSet('danger', 'As senhas informadas não conferem.');
    header('Location: index.php?aba=cadastro');
    exit;
}

$pdo = getConexao();

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    flashSet('danger', 'Já existe uma conta cadastrada com este e-mail.');
    header('Location: index.php?aba=cadastro');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE cpf = ? LIMIT 1');
$stmt->execute([$cpf]);
if ($stmt->fetch()) {
    flashSet('danger', 'Já existe uma conta cadastrada com este CPF.');
    header('Location: index.php?aba=cadastro');
    exit;
}

$hash = password_hash($senha, PASSWORD_BCRYPT);

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, turma, cpf, tipo_usuario) VALUES (?, ?, ?, ?, ?, 1)');
    $stmt->execute([$nome, $email, $hash, $turma !== '' ? $turma : null, $cpf]);
    $novoUsuarioId = (int) $pdo->lastInsertId();

    // Vincula denúncias enviadas anteriormente sem cadastro, usando o mesmo CPF
    $stmt = $pdo->prepare('UPDATE denuncias SET usuario_id = ?, cpf_denunciante = NULL WHERE cpf_denunciante = ? AND usuario_id IS NULL');
    $stmt->execute([$novoUsuarioId, $cpf]);
    $vinculadas = $stmt->rowCount();

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flashSet('danger', 'Não foi possível criar sua conta. Tente novamente.');
    header('Location: index.php?aba=cadastro');
    exit;
}

$mensagem = 'Conta criada com sucesso! Faça login para continuar.';
if ($vinculadas > 0) {
    $mensagem .= " Encontramos {$vinculadas} denúncia(s) feita(s) anteriormente com seu CPF e já vinculamos à sua conta.";
}
flashSet('success', $mensagem);
header('Location: index.php?aba=login');
exit;
