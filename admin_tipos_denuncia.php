<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigirTipo([3]);

$activePage = 'tipos';
$pageTitle = 'Tipos de denúncia';
require_once __DIR__ . '/includes/header.php';

$pdo = getConexao();
$csrf = tokenCsrf();

$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT * FROM tipos_denuncia WHERE id = ?');
    $stmt->execute([(int) $_GET['editar']]);
    $editar = $stmt->fetch();
}

$tipos = $pdo->query('SELECT t.*, (
                           SELECT COUNT(*) FROM denuncias d
                           WHERE d.tipo_denuncia_id = t.id
                             AND NOT EXISTS (
                                 SELECT 1 FROM denuncia_tipos dt
                                 WHERE dt.denuncia_id = d.id AND dt.tipo_denuncia_id = t.id
                             )
                       ) + (
                           SELECT COUNT(*) FROM denuncia_tipos dt WHERE dt.tipo_denuncia_id = t.id
                       ) AS total_uso
                       FROM tipos_denuncia t ORDER BY t.nome')->fetchAll();
?>

<div class="page-head">
  <div>
    <span class="eyebrow">Configuração</span>
    <h1>Tipos de denúncia</h1>
    <p class="mb-0">Cadastre as categorias que os alunos poderão selecionar ao registrar uma denúncia.</p>
  </div>
</div>

<div class="detail-grid" style="grid-template-columns: 380px 1fr;">
  <div class="card">
    <h2 style="font-size:1rem;"><?= $editar ? 'Editar tipo' : 'Novo tipo de denúncia' ?></h2>
    <form method="POST" action="process_tipo_denuncia.php">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <?php if ($editar): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>
      <input type="hidden" name="acao" value="salvar">

      <div class="field">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($editar['nome'] ?? '') ?>" placeholder="Ex: Cyberbullying">
      </div>
      <div class="field">
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" placeholder="Explique brevemente o que se enquadra nesse tipo"><?= htmlspecialchars($editar['descricao'] ?? '') ?></textarea>
      </div>
      <div class="field">
        <div class="checkbox-row">
          <input type="checkbox" id="ativo" name="ativo" value="1" <?= (!$editar || $editar['ativo']) ? 'checked' : '' ?>>
          <label for="ativo">Ativo (visível para os alunos ao denunciar)</label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block"><?= $editar ? 'Salvar alterações' : 'Cadastrar tipo' ?></button>
      <?php if ($editar): ?>
        <a href="admin_tipos_denuncia.php" class="btn btn-outline btn-block" style="margin-top:10px;">Cancelar edição</a>
      <?php endif; ?>
    </form>
  </div>

  <div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Nome</th><th>Status</th><th>Uso</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tipos as $t): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars($t['nome']) ?></strong>
              <?php if ($t['descricao']): ?><br><span class="small text-muted"><?= htmlspecialchars($t['descricao']) ?></span><?php endif; ?>
            </td>
            <td><span class="pill <?= $t['ativo'] ? 'pill-resolvida' : 'pill-arquivada' ?>"><?= $t['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
            <td><?= (int) $t['total_uso'] ?> denúncia(s)</td>
            <td class="flex gap-10">
              <a class="btn btn-sm btn-outline" href="admin_tipos_denuncia.php?editar=<?= $t['id'] ?>">Editar</a>
              <form method="POST" action="process_tipo_denuncia.php" onsubmit="return confirm('Remover este tipo de denúncia?');">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger" <?= $t['total_uso'] > 0 ? 'disabled title="Não é possível excluir: já usado em denúncias"' : '' ?>>Excluir</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
