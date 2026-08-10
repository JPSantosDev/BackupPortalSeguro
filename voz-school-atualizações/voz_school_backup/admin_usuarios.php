<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigirTipo([3]);

$activePage = 'usuarios';
$pageTitle = 'Usuários';
require_once __DIR__ . '/includes/header.php';

$pdo = getConexao();
$csrf = tokenCsrf();

$filtro = $_GET['tipo'] ?? 'todos';
$sql = 'SELECT * FROM usuarios';
$params = [];
if (in_array($filtro, ['1', '2', '3'], true)) {
    $sql .= ' WHERE tipo_usuario = ?';
    $params[] = $filtro;
}
$sql .= ' ORDER BY tipo_usuario, nome';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();
?>

<div class="page-head">
  <div>
    <span class="eyebrow">Administração</span>
    <h1>Usuários do sistema</h1>
    <p class="mb-0">Crie contas para a equipe de apoio (atendentes) e para outros administradores.</p>
  </div>
</div>

<div class="detail-grid" style="grid-template-columns: 380px 1fr;">
  <div class="card">
    <h2 style="font-size:1rem;">Nova conta de equipe</h2>
    <form method="POST" action="process_usuario.php">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="acao" value="criar">

      <div class="field">
        <label for="nome">Nome completo</label>
        <input type="text" id="nome" name="nome" required>
      </div>
      <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="field">
        <label for="senha">Senha provisória</label>
        <input type="password" id="senha" name="senha" required minlength="6">
      </div>
      <div class="field">
        <label for="tipo_usuario">Tipo de acesso</label>
        <select id="tipo_usuario" name="tipo_usuario">
          <option value="2">Atendente</option>
          <option value="3">Administrador</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Criar conta</button>
    </form>
  </div>

  <div>
    <div class="auth-tabs" style="max-width:420px;margin-bottom:18px;">
      <a href="?tipo=todos" class="<?= $filtro === 'todos' ? 'active' : '' ?>">Todos</a>
      <a href="?tipo=1" class="<?= $filtro === '1' ? 'active' : '' ?>">Alunos</a>
      <a href="?tipo=2" class="<?= $filtro === '2' ? 'active' : '' ?>">Atendentes</a>
      <a href="?tipo=3" class="<?= $filtro === '3' ? 'active' : '' ?>">Admins</a>
    </div>

    <div class="table-wrap">
      <table>
        <thead><tr><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['nome']) ?><?= $u['turma'] ? '<br><span class="small text-muted">'.htmlspecialchars($u['turma']).'</span>' : '' ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= nomeTipoUsuario((int) $u['tipo_usuario']) ?></td>
            <td><span class="pill <?= $u['ativo'] ? 'pill-resolvida' : 'pill-arquivada' ?>"><?= $u['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
            <td>
              <?php if ((int) $u['id'] !== (int) $usuario['id']): ?>
                <form method="POST" action="process_usuario.php">
                  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                  <input type="hidden" name="acao" value="alternar_status">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline"><?= $u['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                </form>
              <?php else: ?>
                <span class="small text-muted">Você</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
