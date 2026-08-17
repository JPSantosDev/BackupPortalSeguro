<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
exigirTipo([3]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValido($_POST['csrf_token'] ?? null)) {
    flashSet('danger', 'Sessão expirada. Tente novamente.');
    header('Location: admin_tipos_denuncia.php');
    exit;
}

$pdo = getConexao();
$acao = $_POST['acao'] ?? '';

if ($acao === 'salvar') {
    $id = (int) ($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '') {
        flashSet('danger', 'Informe o nome do tipo de denúncia.');
        header('Location: admin_tipos_denuncia.php');
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE tipos_denuncia SET nome = ?, descricao = ?, ativo = ? WHERE id = ?');
        $stmt->execute([$nome, $descricao ?: null, $ativo, $id]);
        flashSet('success', 'Tipo de denúncia atualizado.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO tipos_denuncia (nome, descricao, ativo) VALUES (?, ?, ?)');
        $stmt->execute([$nome, $descricao ?: null, $ativo]);
        flashSet('success', 'Tipo de denúncia cadastrado.');
    }
} elseif ($acao === 'excluir') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM denuncia_tipos WHERE tipo_denuncia_id = ?');
    $stmt->execute([$id]);
    if ((int) $stmt->fetch()['c'] > 0) {
        flashSet('danger', 'Não é possível excluir: este tipo já foi usado em denúncias.');
    } else {
        $stmt = $pdo->prepare('DELETE FROM tipos_denuncia WHERE id = ?');
        $stmt->execute([$id]);
        flashSet('success', 'Tipo de denúncia removido.');
    }
}

header('Location: admin_tipos_denuncia.php');
exit;
