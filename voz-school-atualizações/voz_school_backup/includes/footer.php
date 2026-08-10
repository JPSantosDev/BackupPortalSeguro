    </main>
  </div>
</div>

<script>
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  function toggleSidebar(open) {
    sidebar.classList.toggle('open', open);
    overlay.classList.toggle('show', open);
  }
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', () => toggleSidebar(true));
    overlay.addEventListener('click', () => toggleSidebar(false));
  }
</script>
</body>
</html>
