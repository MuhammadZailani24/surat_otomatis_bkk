'use strict';
(() => {
  const form = document.getElementById('form-penerbitan');
  if (!form) return;
  const jumlah = document.getElementById('jumlah-kapal');
  const kapal2 = document.getElementById('ship-2');
  function toggleShips() {
    const second = jumlah.value === '2';
    kapal2.hidden = !second;
    kapal2.querySelectorAll('input, select, textarea').forEach(el => { el.disabled = !second; });
  }
  jumlah.addEventListener('change', toggleShips);
  toggleShips();

  form.querySelectorAll('select[data-prefill]').forEach(select => {
    select.addEventListener('change', () => {
      const option = select.selectedOptions[0];
      if (!option || !option.value) return;
      const slot = select.dataset.slot;
      const mapping = select.dataset.prefill === 'kapal'
        ? ['nama_kapal', 'nomor_imo', 'kebangsaan', 'grt'] : ['nama_agen'];
      mapping.forEach(field => {
        const target = form.querySelector(`[name="kapal[${slot}][${field}]"]`);
        if (target) target.value = option.dataset[field] || '';
      });
    });
  });
  const depart = form.elements['tanggal_berangkat'];
  const returnDate = form.elements['tanggal_kembali'];
  const output = document.getElementById('lama-hari');
  function updateDuration() {
    if (!depart.value || !returnDate.value) { output.textContent = 'Isi tanggal terlebih dahulu'; return; }
    const start = new Date(`${depart.value}T00:00:00Z`).getTime();
    const end = new Date(`${returnDate.value}T00:00:00Z`).getTime();
    const days = Math.round((end - start) / 86400000) + 1;
    output.textContent = days > 0 ? `${days} hari` : 'Tanggal kembali tidak valid';
  }
  depart.addEventListener('change', updateDuration);
  returnDate.addEventListener('change', updateDuration);
  updateDuration();
})();
