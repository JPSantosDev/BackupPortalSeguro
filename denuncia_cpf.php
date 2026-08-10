<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getConexao();
$tipos = $pdo->query('SELECT id, nome FROM tipos_denuncia WHERE ativo = 1 ORDER BY nome')->fetchAll();
$csrf = tokenCsrf();
$flash = flashPegar();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#164092">
<title>Denunciar sem cadastro · Voz School</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">

  <div class="auth-brand">
    <div>
      <svg class="shield" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z" fill="#5FBE85"/><path d="M9 12l2 2 4-4" stroke="#10233F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <h1>Você pode denunciar sem criar conta.</h1>
      <p class="lead">Informe apenas o seu CPF, conte o que aconteceu — por texto, foto ou áudio — e receba um número de protocolo para acompanhar depois. Se quiser ver a resposta da equipe dentro do sistema, você pode criar uma conta a qualquer momento com o mesmo CPF.</p>
    </div>
    <div class="brand-footer">Projeto desenvolvido pela turma 3º Ano C · SESI Ibura</div>
  </div>

  <div class="auth-form-side">
    <div class="auth-card" style="max-width:480px;">
      <div class="logo-row">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z" fill="#5FBE85"/></svg>
        <strong style="font-family:'Poppins',sans-serif;color:#10233F;font-size:1.1rem;">Voz School</strong>
      </div>

      <h2 style="font-size:1.25rem;">Denúncia sem cadastro</h2>
      <p class="small" style="margin-bottom:20px;">Preencha os campos abaixo. Você não precisa criar conta agora.</p>

      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>"><?= htmlspecialchars($flash['mensagem']) ?></div>
      <?php endif; ?>

      <form method="POST" action="process_denuncia_cpf.php" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <div class="field">
          <label for="cpf">Seu CPF</label>
          <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00" inputmode="numeric" maxlength="14">
          <div class="hint">Usado apenas para você acompanhar a denúncia depois. Não é mostrado publicamente.</div>
        </div>

        <div class="field">
          <label for="tipo_denuncia_id">Tipo de ocorrência</label>
          <select id="tipo_denuncia_id" name="tipo_denuncia_id" required>
            <option value="" disabled selected>Selecione o tipo mais adequado</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="descricao">Descreva o que aconteceu</label>
          <textarea id="descricao" name="descricao" required placeholder="Conte com detalhes o que você viu ou vivenciou."></textarea>
        </div>

        <div class="field">
          <label for="local_ocorrencia">Local da ocorrência</label>
          <input type="text" id="local_ocorrencia" name="local_ocorrencia" placeholder="Ex: pátio, sala 12, corredor do bloco B">
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
            <input type="file" name="anexo_imagem" accept="image/png,image/jpeg,image/webp" onchange="prevImgCpf(this)">
            <span>Escolher foto</span>
          </label>
          <div class="upload-preview" id="previewImgCpf" style="margin-top:10px;"></div>
        </div>

        <div class="anexo-box" data-gravador data-input="anexoAudioInputCpf">
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
          <input type="file" id="anexoAudioInputCpf" name="anexo_audio" accept="audio/*" style="display:none;">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Enviar denúncia</button>
      </form>

      <div class="cpf-shortcut" style="margin-top:16px;">
        <span>Já denunciou antes? <a href="consultar_protocolo.php">Consultar pelo protocolo</a> · <a href="index.php">Voltar para o login</a></span>
      </div>
    </div>
  </div>

</div>

<script>
  document.getElementById('cpf').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    this.value = v;
  });

  function prevImgCpf(input) {
    const box = document.getElementById('previewImgCpf');
    box.innerHTML = '';
    if (input.files && input.files[0]) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(input.files[0]);
      box.appendChild(img);
    }
  }
</script>
<script src="js/gravador-audio.js"></script>
</body>
</html>
