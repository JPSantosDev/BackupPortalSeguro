<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigirTipo([2]);

$activePage = 'fila';
$pageTitle = 'Detalhe da denúncia';

$pdo = getConexao();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT d.*, COALESCE(
                        (SELECT GROUP_CONCAT(DISTINCT t.nome ORDER BY t.nome SEPARATOR ", ")
                         FROM denuncia_tipos dt JOIN tipos_denuncia t ON t.id = dt.tipo_denuncia_id
                         WHERE dt.denuncia_id = d.id),
                        (SELECT t2.nome FROM tipos_denuncia t2 WHERE t2.id = d.tipo_denuncia_id LIMIT 1)
                       ) AS tipo_nome,
                        u.nome AS denunciante_nome, u.email AS denunciante_email, u.turma
                        FROM denuncias d
                        LEFT JOIN usuarios u ON u.id = d.usuario_id
                        WHERE d.id = ?');
$stmt->execute([$id]);
$d = $stmt->fetch();

if (!$d) {
    flashSet('danger', 'Denúncia não encontrada.');
    header('Location: atendente_denuncias.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

function pillLabel(string $status): string {
    return match ($status) {
        'pendente' => 'Pendente',
        'em_andamento' => 'Em andamento',
        'resolvida' => 'Resolvida',
        'arquivada' => 'Arquivada',
        default => $status,
    };
}
$csrf = tokenCsrf();
?>

<div class="page-head">
  <div>
    <span class="eyebrow"><?= htmlspecialchars($d['tipo_nome']) ?></span>
    <h1>Denúncia #<?= $d['id'] ?></h1>
    <p class="mb-0">Registrada em <?= date('d/m/Y \à\s H:i', strtotime($d['criado_em'])) ?></p>
  </div>
  <span class="pill pill-<?= $d['status'] ?>" style="font-size:0.82rem;padding:8px 16px;"><?= pillLabel($d['status']) ?></span>
</div>

<div class="detail-grid">
  <div class="card">
    <h2 style="font-size:1rem;">Relato</h2>
    <p><?= nl2br(htmlspecialchars($d['descricao'])) ?></p>

    <?php if (!empty($d['anexo_imagem']) || !empty($d['anexo_audio'])): ?>
      <div class="anexo-box">
        <div class="anexo-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.4 11.4 12.6 20.2a4 4 0 0 1-5.6-5.6l9-9a2.7 2.7 0 0 1 3.8 3.8l-8.8 8.8a1.3 1.3 0 0 1-1.9-1.9l8-8"/></svg>
          Anexos enviados pelo aluno
        </div>
        <?php if (!empty($d['anexo_imagem'])): ?>
          <div class="upload-preview" style="margin-bottom:12px;">
            <a href="<?= htmlspecialchars($d['anexo_imagem']) ?>" target="_blank" rel="noopener">
              <img src="<?= htmlspecialchars($d['anexo_imagem']) ?>" alt="Foto enviada na denúncia">
            </a>
          </div>
        <?php endif; ?>
        <?php if (!empty($d['anexo_audio'])): ?>
          <audio controls style="width:100%;"><source src="<?= htmlspecialchars($d['anexo_audio']) ?>"></audio>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <h2 style="font-size:1rem;margin-top:22px;">Atualizar caso</h2>
    <form method="POST" action="process_status_denuncia.php">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="id" value="<?= $d['id'] ?>">

      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="pendente" <?= $d['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
          <option value="em_andamento" <?= $d['status'] === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
          <option value="resolvida" <?= $d['status'] === 'resolvida' ? 'selected' : '' ?>>Resolvida</option>
          <option value="arquivada" <?= $d['status'] === 'arquivada' ? 'selected' : '' ?>>Arquivada</option>
        </select>
      </div>

      <div class="field">
        <label for="resposta">Retorno para o(a) denunciante</label>
        <textarea id="resposta" name="resposta" placeholder="Explique as providências tomadas ou próximos passos."><?= htmlspecialchars($d['resposta'] ?? '') ?></textarea>
        <div class="hint">Este texto ficará visível para quem enviou a denúncia (mesmo em denúncias anônimas).</div>
      </div>

      <button type="submit" class="btn btn-primary">Salvar atualização</button>
    </form>
  </div>

  <div class="card">
    <h2 style="font-size:1rem;">Informações</h2>
    <div class="meta-row"><span class="k">Denunciante</span><span class="v"><?php
      if ($d['anonima']) {
          echo 'Anônima';
      } elseif (!empty($d['cpf_denunciante'])) {
          echo 'Via CPF (sem cadastro)';
      } else {
          echo htmlspecialchars($d['denunciante_nome'] ?? 'Aluno removido');
      }
    ?></span></div>
    <?php if (!$d['anonima'] && empty($d['cpf_denunciante'])): ?>
      <div class="meta-row"><span class="k">Turma</span><span class="v"><?= htmlspecialchars($d['turma'] ?: '—') ?></span></div>
      <div class="meta-row"><span class="k">E-mail</span><span class="v"><?= htmlspecialchars($d['denunciante_email'] ?? '—') ?></span></div>
    <?php endif; ?>
    <div class="meta-row"><span class="k">Local</span><span class="v"><?= htmlspecialchars($d['local_ocorrencia'] ?: '—') ?></span></div>
    <div class="meta-row"><span class="k">Envolvidos</span><span class="v"><?= htmlspecialchars($d['envolvidos'] ?: '—') ?></span></div>
    <div class="meta-row"><span class="k">Data da ocorrência</span><span class="v"><?= $d['data_ocorrencia'] ? date('d/m/Y', strtotime($d['data_ocorrencia'])) : '—' ?></span></div>
    <div class="meta-row"><span class="k">Última atualização</span><span class="v"><?= date('d/m/Y H:i', strtotime($d['atualizado_em'])) ?></span></div>
  </div>
</div>

<a href="atendente_denuncias.php" class="btn btn-outline btn-sm mt-24">← Voltar para a fila</a>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
