'use strict';
(() => {
  // Pilihan tema disimpan hanya di browser pengguna; tidak mengubah dokumen Word/PDF.
  const root = document.documentElement;
  const updateThemeButtons = () => {
    const dark = root.dataset.theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
      button.setAttribute('aria-pressed', String(dark));
      button.setAttribute('aria-label', dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
      button.setAttribute('title', dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
      const caption = button.querySelector('.theme-caption');
      if (caption) caption.textContent = dark ? 'Mode terang' : 'Mode gelap';
      const glyph = button.querySelector('.theme-glyph');
      if (glyph) {
        glyph.innerHTML = dark
          ? '<svg class="ui-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2m10-10h-2M4 12H2m17.1-7.1-1.4 1.4M6.3 17.7l-1.4 1.4m14.2 0-1.4-1.4M6.3 6.3 4.9 4.9"/></svg>'
          : '<svg class="ui-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/></svg>';
      }
    });
  };
  updateThemeButtons();
  document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click', () => {
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    try { localStorage.setItem('bkk-theme', next); } catch (_) { /* Local storage may be disabled. */ }
    updateThemeButtons();
  }));

  // Sidebar pada layar kecil.
  const menuButton = document.querySelector('[data-menu-toggle]');
  const overlay = document.querySelector('[data-menu-close]');
  const closeMenu = () => {
    document.body.classList.remove('menu-open');
    menuButton?.setAttribute('aria-expanded','false');
    menuButton?.setAttribute('aria-label','Buka menu navigasi');
  };
  if (menuButton) {
    menuButton.addEventListener('click', () => {
      const opening = !document.body.classList.contains('menu-open');
      document.body.classList.toggle('menu-open', opening);
      menuButton.setAttribute('aria-expanded', String(opening));
      menuButton.setAttribute('aria-label', opening ? 'Tutup menu navigasi' : 'Buka menu navigasi');
    });
    overlay?.addEventListener('click', closeMenu);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
    document.querySelectorAll('#app-sidebar a').forEach(link => link.addEventListener('click',closeMenu));
  }

  // Umpan balik tindakan: tombol hanya dinonaktifkan saat formulir benar-benar dikirim.
  // Konfirmasi sebelum terbit: pembatalan tidak menonaktifkan tombol.
  document.querySelectorAll('[data-confirm-publish]').forEach(form => {
    form.addEventListener('submit', event => {
      if (!window.confirm('Sudah memeriksa semua halaman dan data? Surat akan dikunci setelah diterbitkan.')) event.preventDefault();
    });
  });
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', event => {
      if (event.defaultPrevented || !form.checkValidity()) return;
      const button = event.submitter;
      if (!button || !button.dataset.loadingLabel) return;
      button.classList.add('is-loading');
      button.setAttribute('aria-busy','true');
      // Jangan disable tombol: nilai submitter tetap ikut apabila dipakai handler.
      button.textContent = button.dataset.loadingLabel;
      button.style.pointerEvents = 'none';
    });
  });
  document.querySelectorAll('[data-dismiss-alert]').forEach(button => button.addEventListener('click', () => {
    button.closest('.toast-alert')?.remove();
  }));

  const passToggle = document.querySelector('[data-password-toggle]');
  if (passToggle) {
    passToggle.addEventListener('click', () => {
      const input = document.getElementById('password');
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      passToggle.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
  }
})();
