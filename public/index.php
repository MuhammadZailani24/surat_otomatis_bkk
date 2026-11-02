<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/masters.php';
require __DIR__ . '/../app/master_module.php';
require __DIR__ . '/../app/penerbitan_service.php';
require __DIR__ . '/../app/penerbitan_view.php';
require __DIR__ . '/../app/document_service.php';
require __DIR__ . '/../app/ui.php';

// Pengunjung baru diarahkan ke landing; pemakai yang sudah masuk tetap ke dashboard.
$page = (string)($_GET['page'] ?? (!empty($_SESSION['uid']) ? 'dashboard' : 'landing'));
$allowed = ['landing','login','logout','dashboard','kapal','keagenan','pejabat','penerbitan','dokumen'];
if (!in_array($page, $allowed, true)) { http_response_code(404); exit('Halaman tidak ditemukan.'); }

if ($page === 'landing') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit('Metode tidak diizinkan.'); }
    bkk_ui_landing(!empty($_SESSION['uid']) ? auth_user() : null);
    exit;
}
if ($page === 'login') {
    if (auth_user()) go('dashboard');
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_validate();
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $stmt = db()->prepare('SELECT id,password_hash,active FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $u = $stmt->fetch();
        if ($u && $u['active'] && password_verify($password, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            go('dashboard');
        }
        usleep(250000);
        $error = 'Username atau kata sandi tidak sesuai.';
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405); exit('Metode tidak diizinkan.');
    }
    bkk_ui_login($error);
    exit;
}

require_login();
$user = current_user();
if ($page === 'dokumen') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') bkk_doc_post($user);
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit('Metode tidak diizinkan.'); }
    $id = valid_positive_id($_GET['id'] ?? null);
    $record = $id ? bkk_penerbitan_load($id) : null;
    if (!$record) { http_response_code(404); exit('Dokumen tidak ditemukan.'); }
    bkk_doc_stream($record, $user);
}
if ($page === 'logout') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Metode tidak diizinkan.'); }
    csrf_validate();
    $_SESSION = [];
    session_destroy();
    go('landing');
}
$master = master_config($page);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($master) handle_master_post($page, $master);
    if ($page === 'penerbitan') handle_penerbitan_post($user);
    http_response_code(405); exit('Metode tidak diizinkan.');
}
$counts = [];
$recent = [];
if ($page === 'dashboard') {
    foreach (['kapal','keagenan','pejabat','penerbitan'] as $table) {
        $counts[$table] = (int)db()->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }
    $recent = db()->query('SELECT id,kode_paket,nama_kegiatan,status,created_at FROM penerbitan ORDER BY id DESC LIMIT 5')->fetchAll();
}
$nav = [
    'dashboard'=>['Dashboard','home'],
    'penerbitan'=>['Penerbitan Dokumen','files'],
    'kapal'=>['Data Kapal','ship'],
    'keagenan'=>['Data Keagenan','building'],
    'pejabat'=>['Data Pejabat','badge'],
];
$heading = $nav[$page][0] ?? 'Surat BKK';
bkk_ui_head($heading);
?>
<body class="app-body">
<a class="skip-link" href="#main-content">Langsung ke konten</a>
<header class="top">
  <div class="top-inner">
    <div class="top-start"><button class="mobile-menu-btn" type="button" data-menu-toggle aria-controls="app-sidebar" aria-expanded="false" aria-label="Buka menu navigasi"><?=bkk_ui_icon('menu',23)?></button>
      <a class="brand brand-on-dark" href="<?=h(route_url('dashboard'))?>" aria-label="Surat BKK — Dashboard"><?=bkk_ui_brand()?></a></div>
    <div class="top-end"><a class="header-shortcut" href="<?=h(route_url('penerbitan',['new'=>1]))?>" title="Buat surat baru"><?=bkk_ui_icon('plus',17)?> <span>Buat surat</span></a>
      <?=bkk_ui_theme_button()?>
      <span class="user-pill"><span class="avatar-letter"><?=h(strtoupper(substr(trim((string)$user['nama']),0,1)))?></span><span class="user-label"><b><?=h($user['nama'])?></b><small><?=h(ucfirst((string)$user['role']))?></small></span></span>
      <form method="post" class="inline" action="<?=h(route_url('logout'))?>"><input type="hidden" name="_csrf" value="<?=h(csrf())?>"><button class="logout-btn" type="submit" title="Keluar dari aplikasi" aria-label="Keluar dari aplikasi"><?=bkk_ui_icon('logout',19)?><span>Keluar</span></button></form>
    </div>
  </div>
</header>
<div class="app-layout">
  <div class="sidebar-overlay" data-menu-close hidden></div>
  <aside class="side" id="app-sidebar" aria-label="Menu aplikasi">
    <div class="sidebar-inner">
      <div class="side-label">MENU UTAMA</div>
      <?php foreach (['dashboard','penerbitan'] as $key): ?>
      <a class="nav-item <?= $page===$key?'active':'' ?>" href="<?=h(route_url($key))?>" <?= $page===$key?'aria-current="page"':'' ?>><?=bkk_ui_icon($nav[$key][1],20)?><span><?=h($nav[$key][0])?></span><?= $page===$key?'<span class="nav-active-dot"></span>':'' ?></a>
      <?php endforeach; ?>
      <div class="side-label side-label-space">MASTER DATA</div>
      <?php foreach (['kapal','keagenan','pejabat'] as $key): ?>
      <a class="nav-item <?= $page===$key?'active':'' ?>" href="<?=h(route_url($key))?>" <?= $page===$key?'aria-current="page"':'' ?>><?=bkk_ui_icon($nav[$key][1],20)?><span><?=h($nav[$key][0])?></span><?= $page===$key?'<span class="nav-active-dot"></span>':'' ?></a>
      <?php endforeach; ?>
      <div class="sidebar-spacer"></div>
      <div class="sidebar-help"><span class="sidebar-help-icon"><?=bkk_ui_icon('help',20)?></span><strong>Butuh panduan?</strong><p>Pelajari alur penggunaan aplikasi dari halaman informasi.</p><a href="<?=h(route_url('landing'))?>">Buka panduan <?=bkk_ui_icon('arrow',15)?></a></div>
      <div class="sidebar-bottom"><span class="system-online"></span> Aplikasi Surat BKK <span class="sidebar-version">v6 · Lebih mudah</span></div>
    </div>
  </aside>
  <main class="content" id="main-content">
    <div class="breadcrumb">RUANG KERJA <span>/</span> <?=h($heading)?></div>
    <?php if (isset($_SESSION['flash'])): [$type,$message]=$_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="alert <?=h($type)?> toast-alert" role="status"><span class="alert-leading"><?=bkk_ui_icon($type==='error'?'help':'check',19)?></span><span><?=h($message)?></span><button type="button" class="dismiss-alert" data-dismiss-alert aria-label="Tutup pemberitahuan"><?=bkk_ui_icon('x',17)?></button></div>
    <?php endif; ?>
    <?php if ($page === 'dashboard'): ?>
      <section class="dashboard-hero"><div class="dashboard-hero-copy"><div class="dashboard-kicker"><span class="status-spark"></span> RUANG KERJA ADMINISTRASI</div><h1>Selamat datang, <?=h(explode(' ', trim((string)$user['nama']))[0] ?: 'Petugas')?> <span class="wave">✦</span></h1><p>Kelola data penerbitan dan siapkan dokumen dengan lebih mudah. Semua dimulai dari satu draft.</p>
        <a class="btn btn-cream" href="<?=h(route_url('penerbitan',['new'=>1]))?>"><?=bkk_ui_icon('plus',19)?> Buat Penerbitan Baru</a></div><div class="dashboard-art" aria-hidden="true"><img src="assets/img/dashboard-docs.svg" alt="" width="310" height="215"></div></section>
      <div class="dashboard-section-label"><h2>Ringkasan Data</h2><span>Gambaran data tersimpan di aplikasi</span></div>
      <div class="cards dashboard-cards">
        <?php $cardConfig=[
            'penerbitan'=>['Paket Penerbitan','files','mint'],
            'kapal'=>['Data Kapal','ship','gold'],
            'keagenan'=>['Keagenan','building','sky'],
            'pejabat'=>['Data Pejabat','badge','lavender']
        ]; foreach ($cardConfig as $table=>$d): ?>
        <article class="card stat-card"><div class="stat-header"><span><?=h($d[0])?></span><span class="stat-icon stat-<?=h($d[2])?>"><?=bkk_ui_icon($d[1],22)?></span></div><strong class="count"><?=h($counts[$table])?></strong><a href="<?=h(route_url($table))?>">Lihat data <?=bkk_ui_icon('arrow',16)?></a></article>
        <?php endforeach; ?>
      </div>
      <div class="dashboard-bottom">
        <section class="card recent-card"><div class="section-header"><div><h2>Penerbitan Terbaru</h2><p class="muted">Lima dokumen terakhir yang dikerjakan</p></div><a href="<?=h(route_url('penerbitan'))?>" class="text-action">Lihat semua <?=bkk_ui_icon('arrow',16)?></a></div>
        <?php if ($recent): ?><div class="recent-list"><?php foreach ($recent as $r): ?><a class="recent-item" href="<?=h(route_url('penerbitan',['view'=>$r['id']]))?>"><span class="recent-file-icon"><?=bkk_ui_icon('files',19)?></span><span class="recent-content"><strong><?=h($r['kode_paket'])?></strong><small><?=h($r['nama_kegiatan'])?></small></span><span class="pill <?=($r['status']==='diterbitkan'?'pill-green':'pill-gray')?>"><?=h(ucfirst((string)$r['status']))?></span></a><?php endforeach; ?></div>
        <?php else: ?><div class="empty-state dashboard-empty"><span class="empty-icon"><?=bkk_ui_icon('files',31)?></span><h3>Belum ada penerbitan</h3><p>Buat draft pertama untuk mulai mengelola dokumen.</p><a class="btn btn-primary" href="<?=h(route_url('penerbitan',['new'=>1]))?>">Buat Draft <?=bkk_ui_icon('arrow',17)?></a></div><?php endif; ?></section>
        <section class="card quick-guide"><div class="section-header"><h2>Alur Cepat</h2><span class="pill pill-green">4 langkah</span></div>
          <div class="guide-step"><span>01</span><p><strong>Isi data penerbitan</strong><small>Nomor, petugas, kegiatan, dan kapal</small></p></div>
          <div class="guide-step"><span>02</span><p><strong>Simpan sebagai draft</strong><small>Periksa kembali seluruh data</small></p></div>
          <div class="guide-step"><span>03</span><p><strong>Buat preview dokumen</strong><small>Periksa Word dan PDF hasil generator</small></p></div>
          <div class="guide-step"><span>04</span><p><strong>Konfirmasi penerbitan</strong><small>Terbitkan dan simpan arsip final</small></p></div>
          <a class="guide-bottom-link" href="<?=h(route_url('landing'))?>#panduan">Panduan lengkap <?=bkk_ui_icon('arrow',16)?></a>
        </section>
      </div>
    <?php elseif ($master): render_master_page($page,$master,$user);
    elseif ($page === 'penerbitan'): render_penerbitan_page($user);
    endif; ?>
    <footer class="app-footer">Surat BKK · Balai Kekarantinaan Kesehatan Kelas I Banjarmasin <span>© <?=date('Y')?></span></footer>
  </main>
</div>
</body></html>
