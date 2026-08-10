<?php
/** Botão flutuante de suporte com perguntas frequentes. Espera $usuario definido. */
$tipo = $usuario['tipo'] ?? 1;

$faq = $tipo === 1 ? [
    ['P: Minha denúncia é mesmo anônima?', 'Sim. Se você marcar a opção "anônima" ao registrar, seu nome não aparece para a equipe de apoio em nenhuma tela.'],
    ['P: Posso enviar um áudio contando o que aconteceu?', 'Sim, na tela de nova denúncia clique em "Gravar áudio" e conte com suas palavras — não precisa digitar tudo.'],
    ['P: Posso denunciar sem ter uma conta?', 'Sim, use a opção "Denunciar sem cadastro" na tela inicial informando seu CPF. Você recebe um protocolo para acompanhar. Para ver a resposta da equipe dentro do sistema, crie uma conta com o mesmo CPF depois.'],
    ['P: Quanto tempo leva para ter uma resposta?', 'A equipe de apoio analisa os casos assim que possível. Você pode acompanhar o status em "Minhas denúncias" a qualquer momento.'],
] : [
    ['P: Como vejo os anexos de uma denúncia?', 'Abra o caso na fila — imagem e áudio enviados pelo aluno aparecem na seção de anexos, se houver.'],
    ['P: Onde escrevo o retorno para o aluno?', 'No detalhe da denúncia, no campo "Retorno para o(a) denunciante". Ele fica visível mesmo em casos anônimos.'],
    ['P: Como reabro um caso arquivado?', 'Altere o status para "Pendente" ou "Em andamento" no mesmo formulário de atualização.'],
];
?>
<button class="suporte-fab" id="suporteFab" aria-label="Abrir suporte" type="button">
  <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-8.9 8.4 8.9 8.9 0 0 1-3.7-.8L3 20l1-5.2A8.4 8.4 0 1 1 21 11.5z"/></svg>
</button>

<div class="suporte-panel" id="suportePanel">
  <div class="cabecalho">
    <h4>Precisa de ajuda?</h4>
    <p>Perguntas frequentes sobre o Voz School</p>
  </div>
  <div class="corpo">
    <?php foreach ($faq as [$pergunta, $resposta]): ?>
      <details class="suporte-item">
        <summary><?= htmlspecialchars($pergunta) ?></summary>
        <p><?= htmlspecialchars($resposta) ?></p>
      </details>
    <?php endforeach; ?>
  </div>
  <div class="rodape">Em situação urgente, procure imediatamente a coordenação do SESI Ibura.</div>
</div>

<script>
  (function () {
    const fab = document.getElementById('suporteFab');
    const panel = document.getElementById('suportePanel');
    fab.addEventListener('click', () => panel.classList.toggle('show'));
    document.addEventListener('click', (e) => {
      if (!panel.contains(e.target) && !fab.contains(e.target)) panel.classList.remove('show');
    });
  })();
</script>
