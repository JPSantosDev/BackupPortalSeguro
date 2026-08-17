<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigirTipo([3]);

$activePage = 'dashboard';
$pageTitle = 'Painel geral';
require_once __DIR__ . '/includes/header.php';

$pdo = getConexao();

$totalDenuncias = (int) $pdo->query('SELECT COUNT(*) c FROM denuncias')->fetch()['c'];
$totalUsuarios  = (int) $pdo->query('SELECT COUNT(*) c FROM usuarios')->fetch()['c'];
$totalAtendentes = (int) $pdo->query('SELECT COUNT(*) c FROM usuarios WHERE tipo_usuario = 2')->fetch()['c'];

$stmt = $pdo->query("SELECT status, COUNT(*) qtd FROM denuncias GROUP BY status");
$porStatus = ['pendente' => 0, 'em_andamento' => 0, 'resolvida' => 0, 'arquivada' => 0];
foreach ($stmt->fetchAll() as $row) { $porStatus[$row['status']] = (int) $row['qtd']; }

$recentes = $pdo->query("SELECT d.id, d.descricao, d.status, d.anonima, d.criado_em,
                          (SELECT GROUP_CONCAT(DISTINCT t.nome ORDER BY t.nome SEPARATOR ', ')
                           FROM denuncia_tipos dt JOIN tipos_denuncia t ON t.id = dt.tipo_denuncia_id
                           WHERE dt.denuncia_id = d.id) AS tipo_nome,
                          u.nome AS denunciante_nome
                          FROM denuncias d
                          LEFT JOIN usuarios u ON u.id = d.usuario_id
                          ORDER BY d.criado_em DESC LIMIT 8")->fetchAll();

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
    <span class="eyebrow">Administração</span>
    <h1>Painel geral</h1>
    <p class="mb-0">Visão consolidada do Voz School para a coordenação do SESI Ibura.</p>
  </div>
</div>

<div class="grid-cards">
  <div class="stat-card c-blue"><div class="bar"></div><div class="value"><?= $totalDenuncias ?></div><div class="label">Denúncias no total</div></div>
  <div class="stat-card c-warn"><div class="bar"></div><div class="value"><?= $porStatus['pendente'] ?></div><div class="label">Pendentes</div></div>
  <div class="stat-card c-info"><div class="bar"></div><div class="value"><?= $porStatus['em_andamento'] ?></div><div class="label">Em andamento</div></div>
  <div class="stat-card c-green"><div class="bar"></div><div class="value"><?= $porStatus['resolvida'] ?></div><div class="label">Resolvidas</div></div>
</div>

<div class="flex-between" style="margin-bottom:14px;">
  <h2 style="font-size:1.05rem;margin:0;">Atividade recente</h2>
  <span class="small text-muted"><?= $totalAtendentes ?> atendente(s) ativos · <?= $totalUsuarios ?> usuários no sistema</span>
</div>

<?php if (empty($recentes)): ?>
  <div class="card empty-state"><p class="mb-0">Nenhuma denúncia registrada ainda.</p></div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tipo</th><th>Descrição</th><th>Origem</th><th>Status</th><th>Data</th></tr></thead>
      <tbody>
      <?php foreach ($recentes as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['tipo_nome']) ?></td>
          <td class="desc-cell"><?= htmlspecialchars(mb_strimwidth($r['descricao'], 0, 60, '…')) ?></td>
          <td><?= $r['anonima'] ? 'Anônima' : htmlspecialchars($r['denunciante_nome'] ?? 'Aluno removido') ?></td>
          <td><span class="pill pill-<?= $r['status'] ?>"><?= pillLabel($r['status']) ?></span></td>
          <td><?= date('d/m/Y', strtotime($r['criado_em'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
