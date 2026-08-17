<?php
require_once __DIR__ . '/config/database.php';
$activePage = 'home';
$pageTitle = 'Início';
require_once __DIR__ . '/includes/header.php';

$pdo = getConexao();
$tipo = $usuario['tipo'];

if ($tipo === 1) {
    $stmt = $pdo->prepare('SELECT status, COUNT(*) qtd FROM denuncias WHERE usuario_id = ? GROUP BY status');
    $stmt->execute([$usuario['id']]);
    $porStatus = ['pendente' => 0, 'em_andamento' => 0, 'resolvida' => 0, 'arquivada' => 0];
    foreach ($stmt->fetchAll() as $row) { $porStatus[$row['status']] = (int) $row['qtd']; }

    $stmt = $pdo->prepare('SELECT d.id, d.descricao, d.status, d.criado_em,
                            COALESCE(
                              (SELECT GROUP_CONCAT(DISTINCT t.nome ORDER BY t.nome SEPARATOR ", ")
                               FROM denuncia_tipos dt JOIN tipos_denuncia t ON t.id = dt.tipo_denuncia_id
                               WHERE dt.denuncia_id = d.id),
                              (SELECT t2.nome FROM tipos_denuncia t2 WHERE t2.id = d.tipo_denuncia_id LIMIT 1)
                            ) AS tipo_nome
                            FROM denuncias d WHERE d.usuario_id = ? ORDER BY d.criado_em DESC LIMIT 4');
    $stmt->execute([$usuario['id']]);
    $recentes = $stmt->fetchAll();
}

if ($tipo === 2) {
    $stmt = $pdo->query("SELECT status, COUNT(*) qtd FROM denuncias GROUP BY status");
    $porStatus = ['pendente' => 0, 'em_andamento' => 0, 'resolvida' => 0, 'arquivada' => 0];
    foreach ($stmt->fetchAll() as $row) { $porStatus[$row['status']] = (int) $row['qtd']; }

    $stmt = $pdo->query("SELECT d.id, d.descricao, d.status, d.anonima, d.criado_em,
                          COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT t.nome ORDER BY t.nome SEPARATOR ', ')
                             FROM denuncia_tipos dt JOIN tipos_denuncia t ON t.id = dt.tipo_denuncia_id
                             WHERE dt.denuncia_id = d.id),
                            (SELECT t2.nome FROM tipos_denuncia t2 WHERE t2.id = d.tipo_denuncia_id LIMIT 1)
                          ) AS tipo_nome
                          FROM denuncias d
                          WHERE d.status IN ('pendente','em_andamento')
                          ORDER BY d.criado_em ASC LIMIT 5");
    $recentes = $stmt->fetchAll();
}

if ($tipo === 3) {
    $totalDenuncias = (int) $pdo->query('SELECT COUNT(*) c FROM denuncias')->fetch()['c'];
    $totalUsuarios  = (int) $pdo->query('SELECT COUNT(*) c FROM usuarios')->fetch()['c'];
    $totalTipos     = (int) $pdo->query('SELECT COUNT(*) c FROM tipos_denuncia WHERE ativo = 1')->fetch()['c'];
    $stmt = $pdo->query("SELECT status, COUNT(*) qtd FROM denuncias GROUP BY status");
    $porStatus = ['pendente' => 0, 'em_andamento' => 0, 'resolvida' => 0, 'arquivada' => 0];
    foreach ($stmt->fetchAll() as $row) { $porStatus[$row['status']] = (int) $row['qtd']; }

    $stmt = $pdo->query("SELECT t.nome,
                          (
                            (SELECT COUNT(*) FROM denuncias d
                             WHERE d.tipo_denuncia_id = t.id
                               AND NOT EXISTS (
                                   SELECT 1 FROM denuncia_tipos dt
                                   WHERE dt.denuncia_id = d.id AND dt.tipo_denuncia_id = t.id
                               ))
                            + (SELECT COUNT(*) FROM denuncia_tipos dt WHERE dt.tipo_denuncia_id = t.id)
                          ) AS qtd
                          FROM tipos_denuncia t
                          ORDER BY qtd DESC LIMIT 6");
    $porTipo = $stmt->fetchAll();
}

function pillLabel(string $status): string {
    return match ($status) {
        'pendente' => 'Pendente',
        'em_andamento' => 'Em andamento',
        'resolvida' => 'Resolvida',
        'arquivada' => 'Arquivada',
        default => $status,
    };
}
?>

<div class="page-head">
  <div>
    <span class="eyebrow">Painel · <?= nomeTipoUsuario($tipo) ?></span>
    <h1>Olá, <?= htmlspecialchars(explode(' ', $usuario['nome'])[0]) ?> - Seja Bem Vindo!</h1>
    <p class="mb-0">
      <?php if ($tipo === 1): ?>
        Este é o seu espaço seguro para registrar e acompanhar denúncias de bullying.
      <?php elseif ($tipo === 2): ?>
        Acompanhe as denúncias recebidas e dê retorno para quem precisa de apoio.
      <?php else: ?>
        Visão geral do sistema Voz School — SESI Ibura.
      <?php endif; ?>
    </p>
  </div>
  <?php if ($tipo === 1): ?>
    <a href="denuncia_nova.php" class="btn btn-accent">+ Registrar denúncia</a>
  <?php endif; ?>
</div>

<?php if ($tipo === 1): ?>
  <div class="grid-cards">

    <div class="stat-card c-warn">
      <div class="bar"></div>
      <div class="value"><?= $porStatus['pendente'] ?></div>
      <div class="label">Pendentes</div>
    </div>

    <div class="stat-card c-info">
      <div class="bar"></div>
      <div class="value"><?= $porStatus['em_andamento'] ?></div>
      <div class="label">Em andamento</div>
    </div>

    <div class="stat-card c-green">
      <div class="bar"></div>
      <div class="value"><?= $porStatus['resolvida'] ?></div>
      <div class="label">Resolvidas</div>
    </div>

    <div class="stat-card c-blue">
      <div class="bar"></div>
      <div class="value"><?= array_sum($porStatus) ?></div>
      <div class="label">Total enviadas</div>
    </div>

  </div>

  <div class="grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(260px,1fr));">
    <a class="action-card" href="denuncia_nova.php">
      <div class="icon-wrap">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>
        </svg>
        </div>
      <div>
        <h3>Nova denúncia</h3>
        <p>Relate uma situação de bullying de forma rápida e, se preferir, anônima.</p>
      </div>
    </a>

    <a class="action-card" href="minhas_denuncias.php">
      <div class="icon-wrap">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 2h6l1 3H8l1-3z"/><path d="M6 6h12l-1 15H7L6 6z"/>
        </svg>
      </div>
      <div>
        <h3>Minhas denúncias</h3>
        <p>Veja o status e as respostas da equipe de apoio sobre seus relatos.</p>
      </div>
    </a>
  </div>

  <h2 style="font-size:1.05rem;margin-bottom:14px;">Últimas denúncias enviadas</h2>
  <?php if (empty($recentes)): ?>
    <div class="card empty-state">
      <p class="mb-0">Você ainda não enviou nenhuma denúncia.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Tipo</th><th>Descrição</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
        <?php foreach ($recentes as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['tipo_nome']) ?></td>
            <td class="desc-cell"><?= htmlspecialchars(mb_strimwidth($r['descricao'], 0, 70, '…')) ?></td>
            <td><span class="pill pill-<?= $r['status'] ?>"><?= pillLabel($r['status']) ?></span></td>
            <td><?= date('d/m/Y', strtotime($r['criado_em'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php if ($tipo === 2): ?>
  <div class="grid-cards">
    <div class="stat-card c-warn"><div class="bar"></div><div class="value"><?= $porStatus['pendente'] ?></div><div class="label">Aguardando atendimento</div></div>
    <div class="stat-card c-info"><div class="bar"></div><div class="value"><?= $porStatus['em_andamento'] ?></div><div class="label">Em andamento</div></div>
    <div class="stat-card c-green"><div class="bar"></div><div class="value"><?= $porStatus['resolvida'] ?></div><div class="label">Resolvidas</div></div>
    <div class="stat-card c-blue"><div class="bar"></div><div class="value"><?= array_sum($porStatus) ?></div><div class="label">Total no sistema</div></div>
  </div>

  <div class="flex-between" style="margin-bottom:14px;">
    <h2 style="font-size:1.05rem;margin:0;">Casos aguardando atenção</h2>
    <a href="atendente_denuncias.php" class="btn btn-outline btn-sm">Ver fila completa</a>
  </div>
  <?php if (empty($recentes)): ?>
    <div class="card empty-state"><p class="mb-0">Nenhuma denúncia pendente no momento. 🎉</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Tipo</th><th>Descrição</th><th>Origem</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentes as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['tipo_nome']) ?></td>
            <td class="desc-cell"><?= htmlspecialchars(mb_strimwidth($r['descricao'], 0, 60, '…')) ?></td>
            <td><?= $r['anonima'] ? 'Anônima' : 'Identificada' ?></td>
            <td><span class="pill pill-<?= $r['status'] ?>"><?= pillLabel($r['status']) ?></span></td>
            <td><a class="btn btn-sm btn-outline" href="atendente_denuncia_detalhe.php?id=<?= $r['id'] ?>">Abrir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php if ($tipo === 3): ?>
  <div class="grid-cards">
    <div class="stat-card c-blue"><div class="bar"></div><div class="value"><?= $totalDenuncias ?></div><div class="label">Denúncias no total</div></div>
    <div class="stat-card c-warn"><div class="bar"></div><div class="value"><?= $porStatus['pendente'] ?></div><div class="label">Pendentes</div></div>
    <div class="stat-card c-green"><div class="bar"></div><div class="value"><?= $porStatus['resolvida'] ?></div><div class="label">Resolvidas</div></div>
    <div class="stat-card c-info"><div class="bar"></div><div class="value"><?= $totalUsuarios ?></div><div class="label">Usuários cadastrados</div></div>
  </div>

  <div class="grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(260px,1fr));">
    <a class="action-card" href="admin_tipos_denuncia.php">
      <div class="icon-wrap"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 12.6 12.7 20.5a2 2 0 0 1-2.8 0l-7-7a2 2 0 0 1 0-2.8l7.9-7.9a2 2 0 0 1 1.4-.6H19a2 2 0 0 1 2 2v6a2 2 0 0 1-.6 1.4z"/></svg></div>
      <div><h3>Tipos de denúncia</h3><p><?= $totalTipos ?> categorias ativas. Adicione ou edite os tipos disponíveis.</p></div>
    </a>
    <a class="action-card" href="admin_usuarios.php">
      <div class="icon-wrap"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c1-3.6 3.8-5.5 6.5-5.5s5.5 1.9 6.5 5.5"/></svg></div>
      <div><h3>Gerenciar usuários</h3><p>Crie contas de atendentes e administradores, ative ou desative acessos.</p></div>
    </a>
  </div>

  <h2 style="font-size:1.05rem;margin-bottom:14px;">Denúncias por categoria</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tipo de denúncia</th><th>Quantidade</th></tr></thead>
      <tbody>
      <?php foreach ($porTipo as $r): ?>
        <tr><td><?= htmlspecialchars($r['nome']) ?></td><td><?= (int) $r['qtd'] ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
