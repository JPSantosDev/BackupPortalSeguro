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
  <form method="POST" action="process_denuncia.php" enctype="multipart/form-data" novalidate>
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

    <div class="anexo-box">
      <div class="anexo-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>
        Foto (opcional)
      </div>
      <label class="file-btn">
        <input type="file" name="anexo_imagem" accept="image/png,image/jpeg,image/webp" onchange="prevImgDenuncia(this)">
        <span>Escolher foto</span>
      </label>
      <div class="upload-preview" id="previewImgDenuncia" style="margin-top:10px;"></div>
    </div>

    <div class="anexo-box" data-gravador data-input="anexoAudioInputDenuncia">
      <div class="anexo-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10a7 7 0 0 0 14 0M12 19v3"/></svg>
        Áudio (opcional) — conte com a sua voz
      </div>
      <div class="recorder">
        <button type="button" class="recorder-btn" data-rec-btn><span class="dot"></span><span>Gravar áudio</span></button>
        <span class="recorder-time" data-rec-time>00:00</span>
        <audio data-rec-audio controls style="display:none;"></audio>
        <button type="button" class="recorder-remove" data-rec-remove style="display:none;">Remover</button>
        <div class="recorder-hint">Gravação de até 3 minutos. O microfone só é acessado quando você clicar em "Gravar".</div>
      </div>
      <input type="file" id="anexoAudioInputDenuncia" name="anexo_audio" accept="audio/*" style="display:none;">
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

<script>
  function prevImgDenuncia(input) {
    const box = document.getElementById('previewImgDenuncia');
    box.innerHTML = '';
    if (input.files && input.files[0]) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(input.files[0]);
      box.appendChild(img);
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
