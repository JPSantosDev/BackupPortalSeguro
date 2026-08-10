<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
exigirTipo([1]);

$activePage = 'nova';
$pageTitle = 'Nova denúncia';
require_once __DIR__ . '/includes/header.php';

$pdo = getConexao();
$tipos = $pdo->query('SELECT id, nome, descricao FROM tipos_denuncia WHERE ativo = 1 ORDER BY nome')->fetchAll();
$csrf = tokenCsrf();
?>

<div class="page-head">
  <div>
    <span class="eyebrow">Denúncia</span>
    <h1>Registrar uma nova denúncia</h1>
    <p class="mb-0">Todas as informações são tratadas com cuidado pela equipe de apoio do SESI Ibura. Você pode optar por manter sua identidade em sigilo.</p>
  </div>
</div>

<div class="card" style="max-width:720px;">
  <form method="POST" action="process_denuncia.php" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div class="field">
      <label for="tipo_denuncia_id">Tipo de ocorrência</label>
      <select id="tipo_denuncia_id" name="tipo_denuncia_id" required>
        <option value="" disabled selected>Selecione o tipo mais adequado</option>
        <?php foreach ($tipos as $t): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="hint">Não sabe qual escolher? Selecione "Outro" e explique na descrição.</div>
    </div>

    <div class="field">
      <label for="descricao">Descreva o que aconteceu</label>
      <textarea id="descricao" name="descricao" required placeholder="Conte com detalhes o que você viu ou vivenciou. Quanto mais informação, mais fácil será agir."></textarea>
    </div>

    <div class="field">
      <label for="local_ocorrencia">Local da ocorrência</label>
      <input type="text" id="local_ocorrencia" name="local_ocorrencia" placeholder="Ex: pátio, sala 12, corredor do bloco B">
    </div>

    <div class="field">
      <label for="envolvidos">Pessoas envolvidas (opcional)</label>
      <input type="text" id="envolvidos" name="envolvidos" placeholder="Nomes ou turmas, se souber">
    </div>

    <div class="field">
      <label for="data_ocorrencia">Data em que aconteceu</label>
      <input type="date" id="data_ocorrencia" name="data_ocorrencia" max="<?= date('Y-m-d') ?>">
    </div>

    <div class="field">
      <div class="checkbox-row">
        <input type="checkbox" id="anonima" name="anonima" value="1">
        <label for="anonima">Quero enviar esta denúncia de forma anônima (seu nome não será mostrado para a equipe de apoio)</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Enviar denúncia</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
