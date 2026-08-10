<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

function pillLabel(string $status): string {
    return match ($status) {
        'pendente' => 'Pendente',
        'em_andamento' => 'Em andamento',
        'resolvida' => 'Resolvida',
        'arquivada' => 'Arquivada',
        default => $status,
    };
}

$resultado = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $protocolo = strtoupper(trim($_POST['protocolo'] ?? ''));
    $cpf = somenteDigitos($_POST['cpf'] ?? '');

    if ($protocolo === '' || !cpfValido($cpf)) {
        $erro = 'Informe o protocolo e um CPF válido.';
    } else {
        $pdo = getConexao();
        $stmt = $pdo->prepare('SELECT d.*, t.nome AS tipo_nome FROM denuncias d
                                JOIN tipos_denuncia t ON t.id = d.tipo_denuncia_id
                                WHERE d.protocolo = ? AND d.cpf_denunciante = ?');
        $stmt->execute([$protocolo, $cpf]);
        $resultado = $stmt->fetch();
        if (!$resultado) {
            $erro = 'Não encontramos nenhuma denúncia com este protocolo e CPF. Se você já criou uma conta, acompanhe pelo login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#164092">
<title>Consultar protocolo · Voz School</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-brand">
    <div>
      <svg class="shield" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z" fill="#5FBE85"/><path d="M9 12l2 2 4-4" stroke="#10233F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <h1>Acompanhe sua denúncia.</h1>
      <p class="lead">Informe o protocolo recebido e o CPF usado no envio para ver o status atual e a resposta da equipe de apoio.</p>
    </div>
    <div class="brand-footer">Projeto desenvolvido pela turma 3º Ano C · SESI Ibura</div>
  </div>
  <div class="auth-form-side">
    <div class="auth-card" style="max-width:460px;">
      <h2 style="font-size:1.2rem;">Consultar protocolo</h2>

      <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

      <?php if (!$resultado): ?>
        <form method="POST" novalidate>
          <div class="field">
            <label for="protocolo">Número de protocolo</label>
            <input type="text" id="protocolo" name="protocolo" required placeholder="VS26XXXXXX" style="text-transform:uppercase;" value="<?= htmlspecialchars($_POST['protocolo'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="cpf">CPF usado no envio</label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00" inputmode="numeric" maxlength="14">
          </div>
          <button type="submit" class="btn btn-primary btn-block">Consultar</button>
        </form>
      <?php else: ?>
        <div class="flex-between" style="margin-bottom:10px;">
          <strong><?= htmlspecialchars($resultado['tipo_nome']) ?></strong>
          <span class="pill pill-<?= $resultado['status'] ?>"><?= pillLabel($resultado['status']) ?></span>
        </div>
        <p><?= nl2br(htmlspecialchars($resultado['descricao'])) ?></p>
        <div class="meta-row"><span class="k">Enviada em</span><span class="v"><?= date('d/m/Y H:i', strtotime($resultado['criado_em'])) ?></span></div>

        <?php if (!empty($resultado['resposta'])): ?>
          <div class="alert alert-info mt-24" style="margin-bottom:0;">
            <strong>Retorno da equipe de apoio:</strong><br>
            <?= nl2br(htmlspecialchars($resultado['resposta'])) ?>
          </div>
        <?php else: ?>
          <div class="alert alert-info mt-24" style="margin-bottom:0;">Ainda não há retorno da equipe de apoio para este caso.</div>
        <?php endif; ?>

        <a href="consultar_protocolo.php" class="btn btn-outline btn-block" style="margin-top:16px;">Consultar outro protocolo</a>
      <?php endif; ?>

      <div class="cpf-shortcut" style="margin-top:16px;">
        <span><a href="index.php">Voltar para o login</a> · <a href="denuncia_cpf.php">Fazer nova denúncia sem cadastro</a></span>
      </div>
    </div>
  </div>
</div>
<script>
  const cpfEl = document.getElementById('cpf');
  if (cpfEl) {
    cpfEl.addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '').slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      this.value = v;
    });
  }
</script>
</body>
</html>
