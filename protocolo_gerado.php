<?php
require_once __DIR__ . '/includes/auth.php';

$protocolo = $_SESSION['protocolo_gerado'] ?? null;
unset($_SESSION['protocolo_gerado']);

if (!$protocolo) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#164092">
<title>Denúncia enviada · Voz School</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-brand">
    <div>
      <svg class="shield" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z" fill="#5FBE85"/><path d="M9 12l2 2 4-4" stroke="#10233F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <h1>Sua denúncia foi enviada.</h1>
      <p class="lead">Obrigado por confiar no Voz School. Guarde o número de protocolo — ele é a única forma de acompanhar esta denúncia sem uma conta.</p>
    </div>
    <div class="brand-footer">Projeto desenvolvido pela turma 3º Ano C · SESI Ibura</div>
  </div>
  <div class="auth-form-side">
    <div class="auth-card protocolo-card" style="max-width:440px;">
      <h2 style="font-size:1.15rem;">Seu número de protocolo</h2>
      <div class="codigo"><?= htmlspecialchars($protocolo) ?></div>
      <p class="small">Anote este código. Você vai precisar dele, junto com o seu CPF, para consultar a resposta da equipe de apoio.</p>
      <a href="consultar_protocolo.php" class="btn btn-primary btn-block" style="margin-top:14px;">Consultar este protocolo agora</a>
      <a href="index.php?aba=cadastro" class="btn btn-outline btn-block" style="margin-top:10px;">Criar conta para acompanhar pelo sistema</a>
    </div>
  </div>
</div>
</body>
</html>
