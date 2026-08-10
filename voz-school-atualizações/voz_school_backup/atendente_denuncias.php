<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigirTipo([2]);

$activePage = 'fila';
$pageTitle = 'Fila de denúncias';
require_once __DIR__ . '/includes/header.php';

$pdo = getConexao();

$filtro = $_GET['status'] ?? 'todas';
$statusValidos = ['pendente', 'em_andamento', 'resolvida', 'arquivada'];

$sql = 'SELECT d.*, t.nome AS tipo_nome, u.nome AS denunciante_nome
        FROM denuncias d
        JOIN tipos_denuncia t ON t.id = d.tipo_denuncia_id
        LEFT JOIN usuarios u ON u.id = d.usuario_id';
$params = [];
if (in_array($filtro, $statusValidos, true)) {
    $sql .= ' WHERE d.status = ?';
    $params[] = $filtro;
}
$sql .= ' ORDER BY FIELD(d.status,"pendente","em_andamento","resolvida","arquivada"), d.criado_em ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$denuncias = $stmt->fetchAll();

function pillLabel(string $status): string {
    return match ($status) {
        'pendente' => 'Pendente',
        'em_andamento' => 'Em andamento',
        'resolvida' => 'Resolvida',
        'arquivada' => 'Arquivada',
        default => $status,
    };
}

$abas = [
    'todas' => 'Todas',
    'pendente' => 'Pendentes',
    'em_andamento' => 'Em andamento',
    'resolvida' => 'Resolvidas',
    'arquivada' => 'Arquivadas',
];
?>

<div class="page-head">
  <div>
    <span class="eyebrow">Atendimento</span>
    <h1>Fila de denúncias</h1>
    <p class="mb-0">Analise os relatos recebidos e dê retorno para os alunos envolvidos.</p>
  </div>
</div>

<div class="auth-tabs" style="max-width:640px;margin-bottom:22px;">
  <?php foreach ($abas as $key => $label): ?>
    <a href="?status=<?= $key ?>" class="<?= $filtro === $key ? 'active' : '' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<?php if (empty($denuncias)): ?>
  <div class="card empty-state"><p class="mb-0">Nenhuma denúncia encontrada nesse filtro.</p></div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tipo</th><th>Descrição</th><th>Denunciante</th><th>Status</th><th>Data</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($denuncias as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['tipo_nome']) ?></td>
          <td class="desc-cell"><?= htmlspecialchars(mb_strimwidth($d['descricao'], 0, 60, '…')) ?></td>
          <td><?= $d['anonima'] ? '<span class="text-muted">Anônima</span>' : htmlspecialchars($d['denunciante_nome'] ?? 'Aluno removido') ?></td>
          <td><span class="pill pill-<?= $d['status'] ?>"><?= pillLabel($d['status']) ?></span></td>
          <td><?= date('d/m/Y', strtotime($d['criado_em'])) ?></td>
          <td><a class="btn btn-sm btn-outline" href="atendente_denuncia_detalhe.php?id=<?= $d['id'] ?>">Abrir</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
