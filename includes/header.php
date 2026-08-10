<?php
/**
 * Espera (opcional):
 * $activePage  string  identificador da página ativa no menu
 * $pageTitle   string  título exibido em <title>
 */
require_once __DIR__ . '/auth.php';
exigirLogin();
$usuario = usuarioLogado();
$activePage = $activePage ?? '';
$pageTitle  = $pageTitle ?? 'Voz School';
$flash = flashPegar();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#164092">
<title><?= htmlspecialchars($pageTitle) ?> · Voz School</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/tutorial.php'; ?>
<div class="app-shell">

  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <aside class="sidebar" id="sidebar">
    <div class="logo-row">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z" fill="#5FBE85"/><path d="M9 12l2 2 4-4" stroke="#10233F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <strong>Voz School</strong>
    </div>
    <div class="school-tag">SESI IBURA · 3º Ano C</div>

    <nav>
      <a href="home.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5"/><path d="M5 10v10h14V10"/></svg>
        Início
      </a>

      <?php if ($usuario['tipo'] === 1): ?>
        <a href="denuncia_nova.php" class="<?= $activePage === 'nova' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
          Nova denúncia
        </a>
        <a href="minhas_denuncias.php" class="<?= $activePage === 'minhas' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2h6l1 3H8l1-3z"/><path d="M6 6h12l-1 15H7L6 6z"/></svg>
          Minhas denúncias
        </a>
      <?php endif; ?>

      <?php if ($usuario['tipo'] === 2): ?>
        <a href="atendente_denuncias.php" class="<?= $activePage === 'fila' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
          Fila de denúncias
        </a>
      <?php endif; ?>

      <?php if ($usuario['tipo'] === 3): ?>
        <a href="admin_dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
          Painel geral
        </a>
        <a href="admin_tipos_denuncia.php" class="<?= $activePage === 'tipos' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 12.6 12.7 20.5a2 2 0 0 1-2.8 0l-7-7a2 2 0 0 1 0-2.8l7.9-7.9a2 2 0 0 1 1.4-.6H19a2 2 0 0 1 2 2v6a2 2 0 0 1-.6 1.4z"/><circle cx="14.5" cy="7.5" r="1"/></svg>
          Tipos de denúncia
        </a>
        <a href="admin_usuarios.php" class="<?= $activePage === 'usuarios' ? 'active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c1-3.6 3.8-5.5 6.5-5.5s5.5 1.9 6.5 5.5"/><circle cx="18" cy="8" r="2.6"/><path d="M15.7 14.7c2.1.3 4 2 4.8 5.3"/></svg>
          Usuários
        </a>
      <?php endif; ?>
    </nav>

    <div class="user-box">
      <div class="name"><?= htmlspecialchars($usuario['nome']) ?></div>
      <div class="role"><?= nomeTipoUsuario($usuario['tipo']) ?></div>
      <a href="logout.php" class="logout">Sair da conta →</a>
    </div>
  </aside>

  <div class="main-content">
    <div class="topbar">
      <button class="hamburger" id="hamburgerBtn" aria-label="Abrir menu"><span></span><span></span><span></span></button>
      <div class="logo-row"><strong style="font-family:'Poppins',sans-serif;color:#10233F;">Voz School</strong></div>
      <div style="width:34px;"></div>
    </div>

    <main class="page">
      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>"><?= htmlspecialchars($flash['mensagem']) ?></div>
      <?php endif; ?>
