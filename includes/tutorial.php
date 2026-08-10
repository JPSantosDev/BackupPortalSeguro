<?php
/**
 * Modal de tutorial exibido no primeiro acesso do usuário.
 * Espera $usuario (array de usuarioLogado()) já definido.
 */
if (!isset($usuario) || (int) $usuario['tutorial_visto'] === 1) {
    return;
}

$tipo = $usuario['tipo'];

if ($tipo === 1) {
    $passos = [
        ['icone' => 'shield', 'titulo' => 'Bem-vindo(a) ao Voz School', 'texto' => 'Este é o canal de denúncia de bullying do SESI Ibura. Aqui você pode relatar situações com segurança e acompanhar o retorno da equipe de apoio.'],
        ['icone' => 'plus', 'titulo' => 'Registrar uma denúncia', 'texto' => 'Em "Nova denúncia" você descreve o que aconteceu e pode anexar uma foto ou gravar um áudio contando a situação com sua própria voz.'],
        ['icone' => 'lock', 'titulo' => 'Anônima se você preferir', 'texto' => 'Você escolhe se quer se identificar ou manter sigilo. Também é possível denunciar sem criar conta, usando apenas o CPF — mas para acompanhar a resposta depois, é preciso ter uma conta.'],
        ['icone' => 'list', 'titulo' => 'Acompanhe o andamento', 'texto' => 'Em "Minhas denúncias" você vê o status de cada relato e a resposta da equipe de apoio assim que ela for registrada.'],
    ];
} elseif ($tipo === 2) {
    $passos = [
        ['icone' => 'shield', 'titulo' => 'Bem-vindo(a), atendente', 'texto' => 'Aqui você recebe e trata as denúncias enviadas pelos alunos do SESI Ibura.'],
        ['icone' => 'list', 'titulo' => 'Fila de denúncias', 'texto' => 'Acompanhe os casos pendentes, em andamento, resolvidos ou arquivados. Cada denúncia pode conter foto e áudio enviados pelo aluno.'],
        ['icone' => 'plus', 'titulo' => 'Dando retorno', 'texto' => 'Ao abrir um caso, atualize o status e escreva uma resposta — ela fica visível para o aluno, mesmo em denúncias anônimas.'],
    ];
} else {
    $passos = [
        ['icone' => 'shield', 'titulo' => 'Bem-vindo(a), administrador(a)', 'texto' => 'Você tem a visão geral do sistema Voz School do SESI Ibura.'],
        ['icone' => 'list', 'titulo' => 'Painel geral', 'texto' => 'Acompanhe estatísticas de denúncias por status e a atividade recente da escola.'],
        ['icone' => 'plus', 'titulo' => 'Configurações', 'texto' => 'Cadastre os tipos de denúncia disponíveis para os alunos e gerencie contas de atendentes e administradores.'],
    ];
}

$icones = [
    'shield' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5.2 3.4 9.8 8 11 4.6-1.2 8-5.8 8-11V5l-8-3z"/><path d="M9 12l2 2 4-4"/></svg>',
    'plus'   => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>',
    'lock'   => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>',
    'list'   => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>',
];
?>
<div class="tutorial-overlay" id="tutorialOverlay">
  <div class="tutorial-modal">
    <?php foreach ($passos as $i => $p): ?>
      <div class="tutorial-step" data-step="<?= $i ?>" style="<?= $i === 0 ? '' : 'display:none;' ?>">
        <div class="step-icon"><?= $icones[$p['icone']] ?></div>
        <h3><?= htmlspecialchars($p['titulo']) ?></h3>
        <p><?= htmlspecialchars($p['texto']) ?></p>
      </div>
    <?php endforeach; ?>

    <div class="tutorial-dots">
      <?php foreach ($passos as $i => $p): ?>
        <span class="<?= $i === 0 ? 'active' : '' ?>" data-dot="<?= $i ?>"></span>
      <?php endforeach; ?>
    </div>

    <div class="tutorial-footer">
      <button type="button" class="tutorial-skip" id="tutorialPular">Pular</button>
      <button type="button" class="btn btn-primary btn-sm" id="tutorialProximo" data-total="<?= count($passos) ?>">Próximo</button>
    </div>
  </div>
</div>
<script src="js/tutorial.js"></script>
