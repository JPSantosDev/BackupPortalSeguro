<?php
require_once __DIR__ . '/includes/auth.php';

if (estaLogado()) {
    header('Location: home.php');
    exit;
}

$aba = ($_GET['aba'] ?? 'login') === 'cadastro' ? 'cadastro' : 'login';
$flash = flashPegar();
$csrf = tokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Voz School · SESI Ibura</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">

  <div class="auth-brand">
    <div>
      <svg class="shield" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z" fill="#5FBE85"/><path d="M9 12l2 2 4-4" stroke="#10233F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <h1>Sua voz protege toda a escola.</h1>
      <p class="lead">O Voz School é o canal de denúncia de bullying do SESI Ibura. Registre uma ocorrência, acompanhe o retorno da equipe de apoio e ajude a construir um ambiente mais seguro para todos.</p>
    </div>
    <div class="brand-footer">Projeto desenvolvido pela turma 3º Ano C · SESI Ibura</div>
  </div>

  <div class="auth-form-side">
    <div class="auth-card">
      <div class="logo-row">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z" fill="#5FBE85"/></svg>
        <strong style="font-family:'Poppins',sans-serif;color:#10233F;font-size:1.1rem;">Voz School</strong>
      </div>

      <div class="auth-tabs">
        <a href="?aba=login" class="<?= $aba === 'login' ? 'active' : '' ?>">Entrar</a>
        <a href="?aba=cadastro" class="<?= $aba === 'cadastro' ? 'active' : '' ?>">Criar conta</a>
      </div>

      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>"><?= htmlspecialchars($flash['mensagem']) ?></div>
      <?php endif; ?>

      <?php if ($aba === 'login'): ?>
        <h2 style="font-size:1.3rem;">Acesse sua conta</h2>
        <p class="small mb-0" style="margin-bottom:22px;">Alunos, atendentes e administradores usam este mesmo formulário.</p>

        <form method="POST" action="process_login.php" novalidate>
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="field">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
          </div>
          <div class="field">
            <label for="senha">Senha</label>
            <div class="password-wrapper">
              <input type="password" id="senha" name="senha" required placeholder="••••••••">
              <button type="button" class="toggle-senha" data-target="senha" aria-label="Mostrar senha">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.87 20.87 0 0 1 5.06-6.06M9.9 4.24A10.87 10.87 0 0 1 12 4c7 0 11 8 11 8a20.87 20.87 0 0 1-3.22 4.44M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>

      <?php else: ?>
        <h2 style="font-size:1.3rem;">Criar conta de denunciante</h2>
        <div class="role-note">
          <span>ℹ️</span>
          <span>O cadastro público é destinado a alunos que desejam registrar denúncias. Contas de atendente e administrador são criadas pela equipe do SESI.</span>
        </div>

        <form method="POST" action="process_cadastro.php" novalidate>
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="field">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" required placeholder="Seu nome">
          </div>
          <div class="field">
            <label for="turma">Turma (opcional)</label>
            <input type="text" id="turma" name="turma" placeholder="Ex: 2º Ano B">
          </div>
          <div class="field">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00" inputmode="numeric" maxlength="14">
            <div class="hint">Se você já denunciou sem cadastro usando este CPF, suas denúncias serão vinculadas automaticamente à sua conta.</div>
          </div>
          <div class="field">
            <label for="email_c">E-mail</label>
            <input type="email" id="email_c" name="email" required placeholder="seuemail@exemplo.com">
          </div>
          <div class="field">
            <label for="senha_c">Senha</label>
            <div class="password-wrapper">
              <input type="password" id="senha_c" name="senha" required minlength="6" placeholder="Mínimo 6 caracteres">
              <button type="button" class="toggle-senha" data-target="senha_c" aria-label="Mostrar senha">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.87 20.87 0 0 1 5.06-6.06M9.9 4.24A10.87 10.87 0 0 1 12 4c7 0 11 8 11 8a20.87 20.87 0 0 1-3.22 4.44M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>
          <div class="field">
            <label for="senha_conf">Confirmar senha</label>
            <div class="password-wrapper">
              <input type="password" id="senha_conf" name="senha_confirmacao" required minlength="6" placeholder="Repita a senha">
              <button type="button" class="toggle-senha" data-target="senha_conf" aria-label="Mostrar senha">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.87 20.87 0 0 1 5.06-6.06M9.9 4.24A10.87 10.87 0 0 1 12 4c7 0 11 8 11 8a20.87 20.87 0 0 1-3.22 4.44M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Criar minha conta</button>
        </form>
      <?php endif; ?>

      <div class="cpf-shortcut">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
        <span>Precisa denunciar agora e não quer criar conta? <a href="denuncia_cpf.php">Denunciar usando apenas o CPF</a></span>
      </div>
    </div>
  </div>

</div>
<script>
  const cpfInput = document.getElementById('cpf');
  if (cpfInput) {
    cpfInput.addEventListener('input', () => {
      let v = cpfInput.value.replace(/\D/g, '').slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      cpfInput.value = v;
    });
  }

  document.querySelectorAll('.toggle-senha').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.target);
      const iconEye = btn.querySelector('.icon-eye');
      const iconEyeOff = btn.querySelector('.icon-eye-off');

      const mostrando = input.type === 'text';
      input.type = mostrando ? 'password' : 'text';
      iconEye.style.display = mostrando ? 'inline' : 'none';
      iconEyeOff.style.display = mostrando ? 'none' : 'inline';
      btn.setAttribute('aria-label', mostrando ? 'Mostrar senha' : 'Ocultar senha');
    });
  });
</script>
</body>
</html>