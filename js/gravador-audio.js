/**
 * Voz School — Gravador de áudio (MediaRecorder API)
 * Uso: <div data-gravador data-input="anexo_audio"> ... </div>
 * Precisa dos elementos internos com os data-attributes abaixo.
 */
(function () {
  function iniciarGravador(container) {
    const btn        = container.querySelector('[data-rec-btn]');
    const timeEl      = container.querySelector('[data-rec-time]');
    const audioEl     = container.querySelector('[data-rec-audio]');
    const removeBtn   = container.querySelector('[data-rec-remove]');
    const hiddenInput = document.getElementById(container.dataset.input);
    const dt = new DataTransfer();

    let mediaRecorder = null;
    let chunks = [];
    let startedAt = 0;
    let timerId = null;
    let stream = null;

    function formatarTempo(segundos) {
      const m = String(Math.floor(segundos / 60)).padStart(2, '0');
      const s = String(segundos % 60).padStart(2, '0');
      return `${m}:${s}`;
    }

    function atualizarTempo() {
      const passou = Math.floor((Date.now() - startedAt) / 1000);
      timeEl.textContent = formatarTempo(passou);
      if (passou >= 180) pararGravacao(); // limite de 3 minutos
    }

    async function iniciarGravacao() {
      if (!navigator.mediaDevices || !window.MediaRecorder) {
        alert('Seu navegador não suporta gravação de áudio. Você ainda pode anexar um arquivo de áudio, se preferir.');
        return;
      }
      try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      } catch (e) {
        alert('Não foi possível acessar o microfone. Verifique as permissões do navegador.');
        return;
      }
      chunks = [];
      mediaRecorder = new MediaRecorder(stream);
      mediaRecorder.ondataavailable = (e) => { if (e.data.size > 0) chunks.push(e.data); };
      mediaRecorder.onstop = finalizarGravacao;
      mediaRecorder.start();

      startedAt = Date.now();
      timerId = setInterval(atualizarTempo, 500);
      btn.classList.add('recording');
    }

    function pararGravacao() {
      if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
      }
      if (stream) {
        stream.getTracks().forEach((t) => t.stop());
      }
      clearInterval(timerId);
      btn.classList.remove('recording');
    }

    function finalizarGravacao() {
      const blob = new Blob(chunks, { type: 'audio/webm' });
      const url = URL.createObjectURL(blob);
      audioEl.src = url;
      audioEl.style.display = 'block';
      removeBtn.style.display = 'inline';

      const arquivo = new File([blob], `denuncia-audio-${Date.now()}.webm`, { type: 'audio/webm' });
      dt.items.clear();
      dt.items.add(arquivo);
      hiddenInput.files = dt.files;
    }

    function removerGravacao() {
      audioEl.removeAttribute('src');
      audioEl.style.display = 'none';
      removeBtn.style.display = 'none';
      dt.items.clear();
      hiddenInput.value = '';
      timeEl.textContent = '00:00';
    }

    btn.addEventListener('click', () => {
      if (btn.classList.contains('recording')) {
        pararGravacao();
      } else {
        iniciarGravacao();
      }
    });
    removeBtn.addEventListener('click', removerGravacao);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-gravador]').forEach(iniciarGravador);
  });
})();
