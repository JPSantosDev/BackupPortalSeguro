(function () {
  const overlay = document.getElementById('tutorialOverlay');
  if (!overlay) return;

  const steps = overlay.querySelectorAll('.tutorial-step');
  const dots  = overlay.querySelectorAll('.tutorial-dots span');
  const btnProximo = document.getElementById('tutorialProximo');
  const btnPular = document.getElementById('tutorialPular');
  const total = parseInt(btnProximo.dataset.total, 10);
  let atual = 0;

  function mostrar(indice) {
    steps.forEach((el, i) => { el.style.display = i === indice ? 'block' : 'none'; });
    dots.forEach((el, i) => { el.classList.toggle('active', i === indice); });
    btnProximo.textContent = indice === total - 1 ? 'Começar a usar' : 'Próximo';
  }

  function fechar() {
    overlay.remove();
    fetch('process_tutorial.php', { method: 'POST' }).catch(() => {});
  }

  btnProximo.addEventListener('click', () => {
    if (atual < total - 1) {
      atual++;
      mostrar(atual);
    } else {
      fechar();
    }
  });

  btnPular.addEventListener('click', fechar);
})();
