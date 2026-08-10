<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigirTipo([1]);

$activePage = 'minhas';
$pageTitle = 'Minhas denúncias';
require_once __DIR__ . '/includes/header.php';

$pdo = getConexao();

$stmt = $pdo->prepare('SELECT d.*, t.nome AS tipo_nome FROM denuncias d
                        JOIN tipos_denuncia t ON t.id = d.tipo_denuncia_id
                        WHERE d.usuario_id = ? ORDER BY d.criado_em DESC');
$stmt->execute([$usuario['id']]);
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

$verId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$detalhe = null;
if ($verId) {
    foreach ($denuncias as $d) {
        if ((int) $d['id'] === $verId) { $detalhe = $d; break; }
    }
}
?>

<div class="page-head">
  <div>
    <span class="eyebrow">Acompanhamento</span>
    <h1>Minhas denúncias</h1>
    <p class="mb-0">Acompanhe o status e a resposta da equipe de apoio para cada denúncia enviada.</p>
  </div>
  <a href="denuncia_nova.php" class="btn btn-accent">+ Nova denúncia</a>
</div>

<?php if ($detalhe): ?>
  <div class="card mt-24" style="margin-bottom:24px;">
    <div class="flex-between" style="margin-bottom:10px;">
      <h2 style="font-size:1.05rem;margin:0;"><?= htmlspecialchars($detalhe['tipo_nome']) ?></h2>
      <span class="pill pill-<?= $detalhe['status'] ?>"><?= pillLabel($detalhe['status']) ?></span>
    </div>
    <p><?= nl2br(htmlspecialchars($detalhe['descricao'])) ?></p>

    <?php if (!empty($detalhe['anexo_imagem']) || !empty($detalhe['anexo_audio'])): ?>
      <div class="anexo-box">
        <div class="anexo-title">Seus anexos</div>
        <?php if (!empty($detalhe['anexo_imagem'])): ?>
          <div class="upload-preview" style="margin-bottom:12px;">
            <img src="<?= htmlspecialchars($detalhe['anexo_imagem']) ?>" alt="Foto enviada">
          </div>
        <?php endif; ?>
        <?php if (!empty($detalhe['anexo_audio'])): ?>
          <audio controls style="width:100%;"><source src="<?= htmlspecialchars($detalhe['anexo_audio']) ?>"></audio>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="meta-row"><span class="k">Local</span><span class="v"><?= htmlspecialchars($detalhe['local_ocorrencia'] ?: '—') ?></span></div>
    <div class="meta-row"><span class="k">Envolvidos</span><span class="v"><?= htmlspecialchars($detalhe['envolvidos'] ?: '—') ?></span></div>
    <div class="meta-row"><span class="k">Data da ocorrência</span><span class="v"><?= $detalhe['data_ocorrencia'] ? date('d/m/Y', strtotime($detalhe['data_ocorrencia'])) : '—' ?></span></div>
    <div class="meta-row"><span class="k">Enviada em</span><span class="v"><?= date('d/m/Y H:i', strtotime($detalhe['criado_em'])) ?></span></div>

    <?php if (!empty($detalhe['resposta'])): ?>
      <div class="alert alert-info mt-24" style="margin-bottom:0;">
        <strong>Retorno da equipe de apoio:</strong><br>
        <?= nl2br(htmlspecialchars($detalhe['resposta'])) ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info mt-24" style="margin-bottom:0;">Ainda não há retorno da equipe de apoio para este caso.</div>
    <?php endif; ?>

    <a href="minhas_denuncias.php" class="btn btn-outline btn-sm mt-24">← Voltar para a lista</a>
  </div>
<?php endif; ?>

<?php if (empty($denuncias)): ?>
  <div class="card empty-state">
    <p class="mb-0">Você ainda não enviou nenhuma denúncia.</p>
    <a href="denuncia_nova.php" class="btn btn-primary" style="margin-top:16px;">Registrar minha primeira denúncia</a>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tipo</th><th>Descrição</th><th>Status</th><th>Data</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($denuncias as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['tipo_nome']) ?></td>
          <td class="desc-cell"><?= htmlspecialchars(mb_strimwidth($d['descricao'], 0, 70, '…')) ?></td>
          <td><span class="pill pill-<?= $d['status'] ?>"><?= pillLabel($d['status']) ?></span></td>
          <td><?= date('d/m/Y', strtotime($d['criado_em'])) ?></td>
          <td><a class="btn btn-sm btn-outline" href="minhas_denuncias.php?id=<?= $d['id'] ?>">Ver detalhes</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
