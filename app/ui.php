<?php
declare(strict_types=1);

/** Tampilan saja. Tidak mengubah alur database, otorisasi, atau generator. */
function bkk_ui_icon(string $name, int $size = 20): string {
    $paths = [
        'chevron-down'=>'<path d="m6 9 6 6 6-6"/>',
        'arrow-left'=>'<path d="M19 12H5m6-6-6 6 6 6"/>',
        'home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/><path d="M9 21v-8h6v8"/>',
        'files'=>'<rect width="13" height="16" x="8" y="5" rx="2"/><path d="M16 5V3a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h3"/><path d="M11 10h7M11 14h7"/>',
        'ship'=>'<path d="M12 2v18M7 7h10l3 10-8 4-8-4L7 7Z"/><path d="M3 22c1.5 0 2.5-1 4-1s2.5 1 5 1 3.5-1 5-1 2.5 1 4 1"/>',
        'building'=>'<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V3h8v4M8 12h2M14 12h2M8 16h2M14 16h2"/>',
        'badge'=>'<path d="M12 2a7 7 0 1 0 0 14 7 7 0 0 0 0-14Z"/><path d="m8 14-2 8 6-3 6 3-2-8"/>',
        'plus'=>'<path d="M12 5v14M5 12h14"/>',
        'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'check'=>'<path d="m4 12 5 5L20 6"/>',
        'sun'=>'<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2m10-10h-2M4 12H2m17.1-7.1-1.4 1.4M6.3 17.7l-1.4 1.4m14.2 0-1.4-1.4M6.3 6.3 4.9 4.9"/>',
        'moon'=>'<path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/>',
        'menu'=>'<path d="M4 7h16M4 12h16M4 17h16"/>',
        'x'=>'<path d="M5 5l14 14M19 5 5 19"/>',
        'logout'=>'<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>',
        'shield'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'download'=>'<path d="M12 3v12m-4-4 4 4 4-4M4 17v3h16v-3"/>',
        'edit'=>'<path d="m16 4 4 4-12 12H4v-4L16 4ZM14 6l4 4"/>',
        'search'=>'<circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/>',
        'help'=>'<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 1 1 5.8 1c-.8 1-2.9 1.4-2.9 3.5M12 17h.01"/>',
        'lock'=>'<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'eye'=>'<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'filecheck'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 15l3 3 5-6"/>',
    ];
    if (!isset($paths[$name])) return '';
    $size = max(12, min(72, $size));
    return '<svg class="ui-icon" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$paths[$name].'</svg>';
}
function bkk_ui_brand(bool $small=false): string {
    return '<span class="brand-emblem" aria-hidden="true">'.bkk_ui_icon('filecheck',22).'</span><span class="brand-words"><strong>SURAT<span>BKK</span></strong><small>Balai Kekarantinaan Kesehatan · Banjarmasin</small></span>';
}
function bkk_ui_theme_button(): string {
    return '<button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false" title="Ganti tema warna"><span class="theme-glyph" aria-hidden="true">'.bkk_ui_icon('moon',19).'</span><span class="theme-caption">Mode gelap</span></button>';
}
function bkk_ui_head(string $title, string $description='Sistem otomatisasi penerbitan surat BKK Kelas I Banjarmasin'): void { ?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?=h($description)?>">
<meta name="theme-color" content="#0c5949">
<title><?=h($title)?> | Surat BKK</title>
<script>try{const t=localStorage.getItem('bkk-theme');document.documentElement.dataset.theme=(t==='dark'||t==='light')?t:'light'}catch(e){document.documentElement.dataset.theme='light'}</script>
<link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
<link rel="stylesheet" href="assets/style.css?v=6">
<script src="assets/ui.js?v=6" defer></script>
</head>
<?php }
function bkk_ui_landing(?array $user): void {
    bkk_ui_head('Beranda', 'Kenali aplikasi Surat BKK, fitur, dan cara menerbitkan Surat Tugas, SPPD, Berita Acara dan Surat Pernyataan.'); ?>
<body class="landing-body">
<a class="skip-link" href="#konten">Langsung ke konten</a>
<header class="landing-header"><div class="site-container landing-header-inner">
  <a class="brand brand-dark" href="<?=h(route_url('landing'))?>" aria-label="Surat BKK — Beranda"><?=bkk_ui_brand()?></a>
  <nav class="landing-links" aria-label="Navigasi informasi"><a href="#fitur">Fitur</a><a href="#panduan">Cara menggunakan</a><a href="#tentang">Tentang aplikasi</a></nav>
  <div class="landing-actions"><?=bkk_ui_theme_button()?>
    <a class="btn btn-primary" href="<?=h(route_url($user?'dashboard':'login'))?>"><?= $user ? 'Buka dashboard' : 'Masuk aplikasi' ?> <?=bkk_ui_icon('arrow',17)?></a>
  </div>
</div></header>
<main id="konten">
<section class="landing-hero site-container">
  <div class="landing-copy">
    <div class="eyebrow"><span class="status-spark"></span> SISTEM ADMINISTRASI DOKUMEN BERBASIS WEB</div>
    <h1>Urusan surat lebih <em>rapi, cepat, dan terarah.</em></h1>
    <p class="hero-subtitle">Buat Surat Tugas, SPPD, Berita Acara, dan Surat Pernyataan melalui satu alur kerja. Masukkan data sekali, periksa dokumen, lalu terbitkan.</p>
    <div class="hero-actions"><a class="btn btn-primary btn-large" href="<?=h(route_url($user?'dashboard':'login'))?>"><?= $user?'Buka Dashboard':'Mulai Masuk Aplikasi' ?> <?=bkk_ui_icon('arrow',19)?></a><a class="btn btn-outline btn-large" href="#panduan">Lihat cara penggunaan</a></div>
    <div class="hero-trust"><span><?=bkk_ui_icon('shield',17)?> Akses khusus pengguna terdaftar</span><span><?=bkk_ui_icon('filecheck',17)?> Menggunakan template dokumen BKK</span></div>
  </div>
  <div class="hero-visual"><div class="hero-visual-halo"></div><img src="assets/img/hero-document-flow.svg" width="680" height="570" alt="Ilustrasi alur data surat tugas, SPPD, dan berita acara menjadi satu paket dokumen" loading="eager"><div class="floating-label"><span class="floating-check"><?=bkk_ui_icon('check',15)?></span><span><b>Dokumen siap diperiksa</b><small>Preview sebelum diterbitkan</small></span></div></div>
</section>
<section class="metric-band"><div class="site-container metric-inner">
 <div><span class="metric-number">01</span><span><strong>Satu kali pengisian</strong><small>Identitas dan informasi kegiatan</small></span></div>
 <div><span class="metric-number">03</span><span><strong>Template utama</strong><small>ST, SPPD, dan BA/Pernyataan</small></span></div>
 <div><span class="metric-number">02</span><span><strong>Format keluaran</strong><small>Dokumen Word dan PDF</small></span></div>
</div></section>
<section class="landing-section site-container" id="fitur"><div class="section-intro"><div class="eyebrow">APA YANG BISA DILAKUKAN?</div><h2>Satu ruang kerja untuk seluruh proses surat.</h2><p>Fitur dibuat untuk membantu operator mengolah dokumen tanpa bolak-balik membuka banyak file.</p></div>
<div class="feature-grid">
  <article class="feature-card"><div class="feature-icon feature-green"><?=bkk_ui_icon('edit',26)?></div><h3>Form input terarah</h3><p>Input manual nama, NIP, pangkat, jabatan, nomor surat, data kapal, dan tanggal dalam formulir yang terstruktur.</p><span class="feature-index">01 / INPUT</span></article>
  <article class="feature-card"><div class="feature-icon feature-amber"><?=bkk_ui_icon('files',26)?></div><h3>Dokumen otomatis</h3><p>Sistem mengisi Surat Tugas, SPPD dua petugas, serta Berita Acara dan Surat Pernyataan untuk setiap kapal.</p><span class="feature-index">02 / PROSES</span></article>
  <article class="feature-card"><div class="feature-icon feature-blue"><?=bkk_ui_icon('filecheck',26)?></div><h3>Preview & arsip</h3><p>Periksa PDF gabungan sebelum menerbitkan. Unduh dokumen dan simpan riwayat penerbitan secara teratur.</p><span class="feature-index">03 / HASIL</span></article>
</div></section>
<section class="how-section" id="panduan"><div class="site-container"><div class="section-intro center-intro"><div class="eyebrow">PANDUAN SINGKAT</div><h2>Empat langkah, dari draft sampai terbit.</h2><p>Ikuti alur berikut ketika menggunakan aplikasi Surat BKK.</p></div>
<div class="steps-grid">
  <article class="step-card"><span class="step-number">01</span><div class="step-line"></div><h3>Masuk ke akun</h3><p>Gunakan username dan kata sandi yang diberikan administrator.</p></article>
  <article class="step-card"><span class="step-number">02</span><div class="step-line"></div><h3>Isi data penerbitan</h3><p>Pilih menu Penerbitan Dokumen, buat draft, lalu isi dua petugas dan data kapal.</p></article>
  <article class="step-card"><span class="step-number">03</span><div class="step-line"></div><h3>Periksa preview</h3><p>Jalankan generator dan cocokkan setiap halaman dengan template sumber.</p></article>
  <article class="step-card"><span class="step-number">04</span><div class="step-line"></div><h3>Terbitkan & unduh</h3><p>Setelah seluruh isian benar, konfirmasi penerbitan dan simpan hasilnya.</p></article>
</div></div></section>
<section class="landing-section site-container" id="tentang"><div class="about-panel"><div><div class="eyebrow">TENTANG APLIKASI</div><h2>Fokus pada ketepatan dokumen, bukan mengubah isinya.</h2><p>Aplikasi menggunakan template yang disediakan BKK Kelas I Banjarmasin. Isi surat tetap mengikuti naskah sumber; operator mengisi data yang diperlukan untuk setiap penugasan.</p><div class="about-points"><span><?=bkk_ui_icon('check',18)?> Nomor surat dengan kode tetap</span><span><?=bkk_ui_icon('check',18)?> Identitas petugas diisi manual</span><span><?=bkk_ui_icon('check',18)?> BA dan Pernyataan per kapal</span></div></div><div class="about-side"><div class="about-side-icon"><?=bkk_ui_icon('shield',44)?></div><strong>Verifikasi tetap di tangan petugas.</strong><p>Preview tidak menggantikan persetujuan atau tanda tangan elektronik resmi. Dokumen Word gabungan perlu dicek tata letaknya.</p></div></div></section>
<section class="cta-section site-container"><div class="cta-inner"><div><span class="cta-kicker">SIAP MENGELOLA SURAT?</span><h2>Mulai dari satu draft yang rapi.</h2><p>Masuk ke aplikasi untuk membuat dan memeriksa paket dokumen.</p></div><a class="btn btn-cream btn-large" href="<?=h(route_url($user?'dashboard':'login'))?>"><?= $user?'Buka dashboard':'Ke halaman login' ?> <?=bkk_ui_icon('arrow',19)?></a></div></section>
</main>
<footer class="landing-footer"><div class="site-container footer-inner"><span><b>Surat BKK</b> · Sistem pendukung administrasi</span><span>BKK Kelas I Banjarmasin · <?=date('Y')?></span></div></footer>
</body></html>
<?php }
function bkk_ui_login(string $error): void {
    bkk_ui_head('Login', 'Halaman masuk untuk pengguna terdaftar aplikasi Surat BKK.'); ?>
<body class="auth-body">
<a class="skip-link" href="#login-form">Langsung ke form login</a>
<div class="auth-layout"><aside class="auth-story">
  <a class="brand brand-on-dark" href="<?=h(route_url('landing'))?>"><?=bkk_ui_brand()?></a>
  <div class="auth-story-copy"><span class="auth-kicker">RUANG KERJA DIGITAL BKK</span><h1>Lebih fokus pada tugas, lebih sedikit pekerjaan berulang.</h1><p>Satu tempat untuk menyiapkan Surat Tugas, SPPD, Berita Acara, dan Surat Pernyataan.</p>
  <div class="auth-steps"><span><?=bkk_ui_icon('edit',18)?> Input data</span><span><?=bkk_ui_icon('files',18)?> Buat dokumen</span><span><?=bkk_ui_icon('check',18)?> Verifikasi hasil</span></div></div>
  <img src="assets/img/login-workflow.svg" alt="Ilustrasi dokumen Surat BKK" class="auth-art" width="540" height="290">
  <p class="auth-footer-label">Balai Kekarantinaan Kesehatan Kelas I Banjarmasin</p>
</aside><main class="auth-main"><div class="auth-toolbar"><a href="<?=h(route_url('landing'))?>" class="back-link">← Kembali ke beranda</a><?=bkk_ui_theme_button()?></div>
  <div class="login-box" id="login-form"><div class="login-eyebrow"><span class="status-spark"></span> AKSES PENGGUNA</div>
  <h2>Selamat datang kembali.</h2><p class="login-subtitle">Masuk menggunakan akun petugas yang sudah terdaftar untuk mengelola surat.</p>
  <?php if ($error): ?><div class="alert error" role="alert"><?=h($error)?></div><?php endif; ?>
  <form method="post" action="<?=h(route_url('login'))?>"><input type="hidden" name="_csrf" value="<?=h(csrf())?>">
    <label for="username">Username</label><div class="input-wrap"><span class="input-icon"><?=bkk_ui_icon('badge',19)?></span><input id="username" name="username" required autocomplete="username" placeholder="Masukkan username" autofocus></div>
    <label for="password">Kata sandi</label><div class="input-wrap"><span class="input-icon"><?=bkk_ui_icon('lock',19)?></span><input id="password" name="password" required type="password" autocomplete="current-password" placeholder="Masukkan kata sandi"><button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan kata sandi"><?=bkk_ui_icon('eye',19)?></button></div>
    <button class="btn btn-primary btn-login" type="submit">Masuk ke Dashboard <?=bkk_ui_icon('arrow',19)?></button>
  </form><div class="login-help"><?=bkk_ui_icon('shield',18)?> Halaman ini hanya untuk pengguna yang memiliki izin akses.</div>
  </div>
  <p class="auth-copyright">© <?=date('Y')?> Surat BKK · Pengembangan aplikasi internal</p>
</main></div>
</body></html>
<?php }
