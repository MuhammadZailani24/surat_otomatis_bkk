<?php
declare(strict_types=1);

function bkk_input(string $name, mixed $value, string $type = 'text', int $max = 200, bool $required = true): void {
    echo '<input type="',h($type),'" name="',h($name),'" value="',h($value),'" maxlength="',h($max),'" ',($required ? 'required ' : ''),'>';
}
function bkk_textarea(string $name, mixed $value, bool $required = true): void {
    echo '<textarea name="',h($name),'" rows="3" maxlength="5000" ',($required ? 'required' : ''),'>',h($value),'</textarea>';
}
function bkk_pejabat_select(string $name, mixed $selected, array $pejabat): void {
    echo '<select name="',h($name),'"><option value="">— Tidak dipilih (mengikuti template asli) —</option>';
    foreach ($pejabat as $item) {
        echo '<option value="',h($item['id']),'" ',((string)$selected === (string)$item['id'] ? 'selected' : ''),'>',h($item['nama']), ' — ',h($item['jabatan']),'</option>';
    }
    echo '</select>';
}
function bkk_form_default(): array {
    $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Makassar')))->format('Y-m-d');
    return ['nomor_st'=>'','nomor_sppd'=>'','nama_kegiatan'=>'','maksud_perjalanan'=>'',
        'tanggal_surat'=>$today,'tanggal_tugas'=>$today,'tanggal_ttd'=>$today,
        'tanggal_pemeriksaan'=>$today,'tanggal_berangkat'=>$today,'tanggal_kembali'=>$today,
        'tanggal_pernyataan'=>$today,'tempat_berangkat'=>'Banjarmasin','tujuan'=>'',
        'pejabat_st_id'=>'','pejabat_ppk_id'=>'','jumlah_kapal'=>1,
        'petugas'=>[1=>['nama'=>'','nip'=>'','pangkat_golongan'=>'','jabatan'=>''],
                    2=>['nama'=>'','nip'=>'','pangkat_golongan'=>'','jabatan'=>'']],
        'kapal'=>[1=>['kapal_id'=>'','keagenan_id'=>'','nama_kapal'=>'','nama_agen'=>'',
            'posisi_labuh'=>'','kebangsaan'=>'','grt'=>'','nomor_imo'=>'',
            'nama_nakhoda'=>'','jabatan_nakhoda'=>'Nakhoda','status_pemeriksaan'=>'belum_ditetapkan','rekomendasi'=>''],
            2=>['kapal_id'=>'','keagenan_id'=>'','nama_kapal'=>'','nama_agen'=>'',
            'posisi_labuh'=>'','kebangsaan'=>'','grt'=>'','nomor_imo'=>'',
            'nama_nakhoda'=>'','jabatan_nakhoda'=>'Nakhoda','status_pemeriksaan'=>'belum_ditetapkan','rekomendasi'=>'']]];
}
function bkk_select_kapal_master(int $n, array $ship, array $kapal): void {
    ?>
    <label>Ambil dari master kapal (opsional)
    <select name="kapal[<?=$n?>][kapal_id]" data-prefill="kapal" data-slot="<?=$n?>">
      <option value="">— Isi manual / pilih master —</option>
      <?php foreach ($kapal as $k): ?><option value="<?=h($k['id'])?>"
      data-nama_kapal="<?=h($k['nama_kapal'])?>" data-nomor_imo="<?=h($k['nomor_imo'])?>"
      data-kebangsaan="<?=h($k['kebangsaan'])?>" data-grt="<?=h($k['grt'])?>"
      <?= (string)($ship['kapal_id'] ?? '') === (string)$k['id'] ? 'selected' : '' ?>><?=h($k['nama_kapal'])?></option><?php endforeach; ?>
    </select></label>
    <?php
}
function bkk_select_agen_master(int $n, array $ship, array $agen): void {
    ?>
    <label>Ambil dari master keagenan (opsional)
    <select name="kapal[<?=$n?>][keagenan_id]" data-prefill="agen" data-slot="<?=$n?>">
      <option value="">— Isi manual / pilih master —</option>
      <?php foreach ($agen as $a): ?><option value="<?=h($a['id'])?>" data-nama_agen="<?=h($a['nama_agen'])?>"
      <?= (string)($ship['keagenan_id'] ?? '') === (string)$a['id'] ? 'selected' : '' ?>><?=h($a['nama_agen'])?></option><?php endforeach; ?>
    </select></label>
    <?php
}
function bkk_render_kapal_fields(int $i, array $ship, array $kapal, array $agen): void {
    ?>
    <section class="inner-card ship-section" id="ship-<?=$i?>" <?= $i === 2 ? 'hidden' : '' ?>>
    <div class="section-header"><h3>Kapal <?=$i?></h3><span class="pill">BA + Surat Pernyataan <?=$i?></span></div>
    <p class="muted">Setiap kapal akan mendapat Berita Acara dan Surat Pernyataan sendiri pada tahap generator dokumen.</p>
    <div class="field-grid">
      <?php bkk_select_kapal_master($i,$ship,$kapal); bkk_select_agen_master($i,$ship,$agen); ?>
      <label>Nama kapal *<?php bkk_input("kapal[{$i}][nama_kapal]",$ship['nama_kapal'] ?? '', 'text',180); ?></label>
      <label>Nama agen *<?php bkk_input("kapal[{$i}][nama_agen]",$ship['nama_agen'] ?? '', 'text',180); ?></label>
      <label>Posisi / lokasi labuh *<?php bkk_input("kapal[{$i}][posisi_labuh]",$ship['posisi_labuh'] ?? '', 'text',200); ?></label>
      <label>Kebangsaan<?php bkk_input("kapal[{$i}][kebangsaan]",$ship['kebangsaan'] ?? '', 'text',100,false); ?></label>
      <label>GRT<?php bkk_input("kapal[{$i}][grt]",$ship['grt'] ?? '', 'text',40,false); ?></label>
      <label>Nomor IMO<?php bkk_input("kapal[{$i}][nomor_imo]",$ship['nomor_imo'] ?? '', 'text',30,false); ?></label>
      <label>Nama nakhoda *<?php bkk_input("kapal[{$i}][nama_nakhoda]",$ship['nama_nakhoda'] ?? '', 'text',150); ?></label>
      <label>Jabatan nakhoda *<?php bkk_input("kapal[{$i}][jabatan_nakhoda]",$ship['jabatan_nakhoda'] ?? 'Nakhoda','text',100); ?></label>
      <label>Status pemeriksaan
      <select name="kapal[<?=$i?>][status_pemeriksaan]">
        <?php foreach (['belum_ditetapkan'=>'Belum ditetapkan','memenuhi'=>'Memenuhi syarat','tidak_memenuhi'=>'Tidak memenuhi syarat'] as $status=>$title): ?>
        <option value="<?=h($status)?>" <?= ($ship['status_pemeriksaan'] ?? 'belum_ditetapkan') === $status ? 'selected' : '' ?>><?=h($title)?></option>
        <?php endforeach; ?>
      </select></label>
      <label class="full">Catatan/rekomendasi pemeriksaan<?php bkk_textarea("kapal[{$i}][rekomendasi]",$ship['rekomendasi'] ?? '',false); ?></label>
    </div>
    </section>
    <?php
}
/** Display fixed institutional prefix, while operator edits only sequence and year. */
function bkk_nomor_ui(string $field, mixed $value, string $prefix): void {
    $full = trim((string)$value);
    $sequence = ''; $year = (new DateTimeImmutable('now', new DateTimeZone('Asia/Makassar')))->format('Y');
    if (preg_match('~^'.preg_quote($prefix,'~').'/([^/]+)/([0-9]{4})$~D', $full, $m)) {
        $sequence = $m[1]; $year = $m[2];
    } else if ($full !== '') {
        // Legacy values are kept visible to prevent silently discarding existing data.
        $sequence = $full;
    }
    echo '<div class="nomor-components"><span class="nomor-prefix">',h($prefix),'/</span>';
    echo '<input aria-label="Nomor urut" inputmode="numeric" placeholder="Nomor" name="',h($field),'_urut" value="',h($sequence),'" maxlength="50" required>';
    echo '<span>/</span><input aria-label="Tahun surat" inputmode="numeric" placeholder="Tahun" name="',h($field),'_tahun" value="',h($year),'" maxlength="4" pattern="[0-9]{4}" required></div>';
}
function bkk_render_form(array $record, ?int $id, array $errors): void {
    $pdo = db();
    $pejabat = $pdo->query('SELECT * FROM pejabat WHERE active=1 ORDER BY nama')->fetchAll();
    $kapal = $pdo->query('SELECT * FROM kapal WHERE active=1 ORDER BY nama_kapal')->fetchAll();
    $agen = $pdo->query('SELECT * FROM keagenan WHERE active=1 ORDER BY nama_agen')->fetchAll();
    $defaults = bkk_form_default();
    $record = array_replace_recursive($defaults, $record);
    ?>
    <div class="page-heading"><div><span class="page-eyebrow">FORMULIR PENERBITAN</span><h1><?= $id ? 'Ubah data draft' : 'Buat surat baru' ?></h1>
    <p>Isi data sekali. Informasi ini akan digunakan pada seluruh dokumen dalam satu paket.</p></div>
    <a class="back-action" href="<?=h(route_url('penerbitan'))?>"><?=bkk_ui_icon('arrow-left',17)?> Kembali ke daftar</a></div>
    <div class="form-intro"><span class="intro-icon"><?=bkk_ui_icon('shield',21)?></span><p><strong>Template asli tetap terjaga.</strong> Kode nomor surat muncul otomatis. Data nama, NIP, dan jabatan diisi manual, kemudian disimpan sebagai draft sebelum diterbitkan.</p></div>
    <nav class="form-jump" aria-label="Langsung ke bagian formulir">
      <a href="#bagian-administrasi"><span>01</span> Administrasi</a><a href="#bagian-petugas"><span>02</span> Petugas</a><a href="#bagian-kegiatan"><span>03</span> Kegiatan</a><a href="#bagian-kapal"><span>04</span> Kapal</a>
    </nav>
    <?php if ($errors): ?><div class="alert error"><strong>Perbaiki data berikut:</strong><ul><?php foreach ($errors as $error): ?><li><?=h($error)?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" id="form-penerbitan" action="<?=h(route_url('penerbitan'))?>">
      <input type="hidden" name="_csrf" value="<?=h(csrf())?>">
      <input type="hidden" name="action" value="save_draft">
      <input type="hidden" name="id" value="<?=h($id ?? '')?>">
      <section class="card section-card form-section" id="bagian-administrasi"><div class="section-header"><div class="section-title-cluster"><span class="section-number">01</span><div><h2>Administrasi surat</h2><p>Nomor surat dan tanggal penting</p></div></div><span class="pill pill-green">Langkah 1 dari 4</span></div>
      <div class="field-grid">
        <label>Nomor Surat Tugas (nomor dan tahun) *<?php bkk_nomor_ui('nomor_st',$record['nomor_st'],'SR.04.02/C.X.11'); ?></label>
        <label>Nomor SPPD (nomor dan tahun) *<?php bkk_nomor_ui('nomor_sppd',$record['nomor_sppd'],'KU.01.02/C.X.11'); ?></label>
        <label>Tanggal surat *<?php bkk_input('tanggal_surat',$record['tanggal_surat'],'date'); ?></label>
        <label>Tanggal berlaku ST *<?php bkk_input('tanggal_tugas',$record['tanggal_tugas'],'date'); ?></label>
        <label>Tanggal tanda tangan ST *<?php bkk_input('tanggal_ttd',$record['tanggal_ttd'],'date'); ?></label>
        <label>Tanggal pemeriksaan *<?php bkk_input('tanggal_pemeriksaan',$record['tanggal_pemeriksaan'],'date'); ?></label>
        <label>Tanggal Surat Pernyataan (untuk setiap kapal) *<?php bkk_input('tanggal_pernyataan',$record['tanggal_pernyataan'],'date'); ?></label>
        <details class="optional-fields full"><summary><?=bkk_ui_icon('badge',18)?> Referensi pejabat (opsional) <?=bkk_ui_icon('chevron-down',17)?></summary>
          <p>Nama penandatangan di dokumen tetap mengikuti template asli. Bagian ini hanya menyimpan referensi.</p>
          <div class="field-grid">
            <label>Referensi pejabat ST<?php bkk_pejabat_select('pejabat_st_id',$record['pejabat_st_id'],$pejabat); ?></label>
            <label>Referensi pejabat PPK<?php bkk_pejabat_select('pejabat_ppk_id',$record['pejabat_ppk_id'],$pejabat); ?></label>
          </div>
        </details>
      </div></section>
      <section class="card section-card form-section" id="bagian-petugas"><div class="section-header"><div class="section-title-cluster"><span class="section-number">02</span><div><h2>Identitas petugas</h2><p>Dua orang petugas, diisi secara manual</p></div></div><span class="pill">2 petugas</span></div>
        <div class="two-panels">
        <?php foreach ([1,2] as $i): $p=$record['petugas'][$i]; ?>
        <div class="inner-card"><h3>Petugas <?=$i?></h3>
          <label>Nama lengkap *<?php bkk_input("petugas[{$i}][nama]",$p['nama'] ?? '', 'text',150); ?></label>
          <label>NIP *<?php bkk_input("petugas[{$i}][nip]",$p['nip'] ?? '', 'text',30); ?></label>
          <label>Pangkat / golongan *<?php bkk_input("petugas[{$i}][pangkat_golongan]",$p['pangkat_golongan'] ?? '', 'text',120); ?></label>
          <label>Jabatan *<?php bkk_input("petugas[{$i}][jabatan]",$p['jabatan'] ?? '', 'text',200); ?></label>
        </div>
        <?php endforeach; ?></div></section>
      <section class="card section-card form-section" id="bagian-kegiatan"><div class="section-header"><div class="section-title-cluster"><span class="section-number">03</span><div><h2>Kegiatan & perjalanan dinas</h2><p>Uraian penugasan dan periode perjalanan</p></div></div></div>
        <div class="field-grid">
          <label class="full">Nama kegiatan *<?php bkk_textarea('nama_kegiatan',$record['nama_kegiatan']); ?></label>
          <label class="full">Maksud perjalanan dinas *<?php bkk_textarea('maksud_perjalanan',$record['maksud_perjalanan']); ?></label>
          <label>Tempat berangkat *<?php bkk_input('tempat_berangkat',$record['tempat_berangkat'],'text',150); ?></label>
          <label>Tempat tujuan *<?php bkk_input('tujuan',$record['tujuan'],'text',200); ?></label>
          <label>Tanggal berangkat *<?php bkk_input('tanggal_berangkat',$record['tanggal_berangkat'],'date'); ?></label>
          <label>Tanggal kembali *<?php bkk_input('tanggal_kembali',$record['tanggal_kembali'],'date'); ?></label>
          <div class="full hint-box">Lama perjalanan dihitung otomatis oleh server, termasuk hari berangkat dan hari kembali. <b id="lama-hari">—</b></div>
        </div></section>
      <section class="card section-card form-section" id="bagian-kapal"><div class="section-header"><div class="section-title-cluster"><span class="section-number">04</span><div><h2>Data kapal & pemeriksaan</h2><p>Setiap kapal memiliki BA dan Surat Pernyataan sendiri</p></div></div><span class="pill">Per kapal</span></div>
        <label>Jumlah kapal dalam surat tugas
        <select name="jumlah_kapal" id="jumlah-kapal"><option value="1" <?= (int)$record['jumlah_kapal'] === 1 ? 'selected' : '' ?>>1 kapal</option><option value="2" <?= (int)$record['jumlah_kapal'] === 2 ? 'selected' : '' ?>>2 kapal</option></select></label>
        <?php foreach ([1,2] as $i) bkk_render_kapal_fields($i,$record['kapal'][$i],$kapal,$agen); ?>
      </section>
      <div class="form-actions sticky-actions"><div class="save-explainer"><strong>Data siap disimpan?</strong><span>Ini hanya menyimpan draft, belum menerbitkan dokumen.</span></div><div class="save-buttons"><a href="<?=h(route_url('penerbitan'))?>" class="btn btn-outline">Batal</a><button type="submit" class="btn btn-primary" data-loading-label="Menyimpan draft..."><?=bkk_ui_icon('check',18)?> <?= $id ? 'Simpan perubahan' : 'Simpan draft' ?></button></div></div>
    </form>
    <script src="assets/penerbitan.js" defer></script>
    <?php
}
function bkk_render_detail(array $r, array $user): void {
    $shipCount = count($r['kapal']);
    $published = $r['status'] === 'diterbitkan';
    $draft = $r['status'] === 'draft';
    $canEdit = bkk_is_editable($r,$user);
    $authorized = bkk_doc_authorized($r,$user);
    $docx = $authorized ? bkk_doc_archive($r,'docx') : null;
    $pdf = $authorized ? bkk_doc_archive($r,'pdf') : null;
    $ready = (bool)($docx && $pdf);
    ?>
    <div class="page-heading"><div><span class="page-eyebrow">PAKET DOKUMEN</span><h1>Rincian penerbitan</h1><p class="mono-id"><?=h($r['kode_paket'])?> <span class="pill <?=$published?'pill-green':'pill-gray'?>"><?= $published?'Sudah terbit':($draft?'Draft':'Tidak aktif') ?></span></p></div>
      <div class="button-row"><a class="back-action" href="<?=h(route_url('penerbitan'))?>"><?=bkk_ui_icon('arrow-left',17)?> Semua surat</a>
      <?php if ($canEdit): ?><a class="btn btn-outline" href="<?=h(route_url('penerbitan',['edit'=>$r['id']]))?>"><?=bkk_ui_icon('edit',17)?> Edit data</a><?php endif; ?></div></div>
    <div class="workflow-track" aria-label="Tahapan penerbitan">
      <div class="workflow-node completed"><span><?=bkk_ui_icon('check',18)?></span><div><strong>1. Isi data</strong><small>Tersimpan</small></div></div>
      <div class="workflow-connector <?=$ready?'completed':''?>"></div>
      <div class="workflow-node <?=$ready?'completed':'current'?>"><span><?=$ready?bkk_ui_icon('check',18):bkk_ui_icon('filecheck',18)?></span><div><strong>2. Preview</strong><small><?=$ready?'Sudah dibuat':'Perlu dibuat'?></small></div></div>
      <div class="workflow-connector <?=$published?'completed':''?>"></div>
      <div class="workflow-node <?=$published?'completed':($ready?'current':'upcoming')?>"><span><?=$published?bkk_ui_icon('check',18):bkk_ui_icon('download',18)?></span><div><strong>3. Terbitkan</strong><small><?=$published?'Selesai':'Menunggu verifikasi'?></small></div></div>
    </div>
    <?php if ($authorized): ?>
    <section class="card document-workbench" aria-labelledby="workbench-title">
      <?php if (!$ready): ?>
        <div class="workbench-header"><div class="workbench-icon"><?=bkk_ui_icon('filecheck',26)?></div><div><span class="page-eyebrow">LANGKAH BERIKUTNYA</span><h2 id="workbench-title">Buat preview dokumen</h2><p>Data sudah tersimpan. Buat PDF dan Word, lalu periksa seluruh halaman sebelum diterbitkan.</p></div></div>
        <?php if ($draft): ?>
          <form method="post" action="<?=h(route_url('dokumen'))?>" class="workbench-main-form">
            <input type="hidden" name="_csrf" value="<?=h(csrf())?>"><input type="hidden" name="id" value="<?=h($r['id'])?>"><input type="hidden" name="action" value="generate">
            <button type="submit" class="btn btn-primary btn-large" data-loading-label="Sedang membuat dokumen..."><?=bkk_ui_icon('filecheck',19)?> Buat preview Word & PDF <?=bkk_ui_icon('arrow',17)?></button>
          </form>
        <?php else: ?><p class="muted">File arsip tidak tersedia. Hubungi admin sebelum mengunduh kembali.</p><?php endif; ?>
      <?php elseif ($draft): ?>
        <div class="workbench-header"><div class="workbench-icon workbench-icon-ready"><?=bkk_ui_icon('check',26)?></div><div><span class="page-eyebrow">PREVIEW SIAP</span><h2 id="workbench-title">Periksa sebelum terbit</h2><p>Pastikan nama, tanggal, nomor surat, tata letak, dan tanda tangan sudah sesuai template.</p></div></div>
        <div class="workbench-action-grid">
          <a class="btn btn-outline btn-large" href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'pdf','preview'=>1]))?>" target="_blank" rel="noopener noreferrer"><?=bkk_ui_icon('eye',19)?> Lihat preview PDF</a>
          <form method="post" action="<?=h(route_url('dokumen'))?>" data-confirm-publish>
            <input type="hidden" name="_csrf" value="<?=h(csrf())?>"><input type="hidden" name="id" value="<?=h($r['id'])?>"><input type="hidden" name="action" value="publish">
            <button type="submit" class="btn btn-primary btn-large" data-loading-label="Menerbitkan paket..."><?=bkk_ui_icon('check',19)?> Konfirmasi & terbitkan <?=bkk_ui_icon('arrow',17)?></button>
          </form>
        </div>
        <p class="workbench-caption"><?=bkk_ui_icon('shield',16)?> Penerbitan mengunci draft; preview bukan tanda tangan elektronik.</p>
        <details class="action-disclosure"><summary><?=bkk_ui_icon('files',18)?> Unduh hasil sementara & opsi lainnya <span class="disclosure-chevron"><?=bkk_ui_icon('chevron-down',17)?></span></summary>
          <div class="disclosure-body"><div class="download-choices">
            <a href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'pdf']))?>"><?=bkk_ui_icon('download',19)?> PDF gabungan <small>Siap dicetak</small></a>
            <a href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'docx']))?>"><?=bkk_ui_icon('download',19)?> Word gabungan <small>Tampilan presisi PDF</small></a>
            <a href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'word_asli']))?>"><?=bkk_ui_icon('download',19)?> Word editable (ZIP) <small>Template per dokumen</small></a>
          </div>
          <form method="post" action="<?=h(route_url('dokumen'))?>" class="minor-action-form"><input type="hidden" name="_csrf" value="<?=h(csrf())?>"><input type="hidden" name="id" value="<?=h($r['id'])?>"><input type="hidden" name="action" value="generate"><button type="submit" class="text-button" data-loading-label="Membuat ulang preview...">Buat ulang preview setelah revisi data</button></form>
          </div>
        </details>
      <?php else: ?>
        <div class="workbench-header"><div class="workbench-icon workbench-icon-ready"><?=bkk_ui_icon('check',26)?></div><div><span class="page-eyebrow"><?= $published ? 'PENERBITAN SELESAI' : 'ARSIP DOKUMEN' ?></span><h2 id="workbench-title">Dokumen tersedia untuk diunduh</h2><p><?= $published ? 'Paket sudah diterbitkan dan tersimpan dalam arsip.' : 'Dokumen ini tidak berstatus terbit. Periksa status sebelum digunakan.' ?> Pilih format yang diperlukan.</p></div></div>
        <div class="workbench-action-grid">
          <a class="btn btn-primary btn-large" href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'pdf']))?>"><?=bkk_ui_icon('download',19)?> Unduh PDF gabungan</a>
          <a class="btn btn-outline btn-large" href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'docx']))?>"><?=bkk_ui_icon('download',19)?> Unduh Word gabungan</a>
        </div>
        <details class="action-disclosure"><summary><?=bkk_ui_icon('files',18)?> Format editable & bantuan teknis <span class="disclosure-chevron"><?=bkk_ui_icon('chevron-down',17)?></span></summary>
          <div class="disclosure-body">
            <div class="download-choices"><a href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'word_asli']))?>"><?=bkk_ui_icon('download',19)?> Unduh Word editable (ZIP) <small>File Word individual sesuai template</small></a>
            <a href="<?=h(route_url('dokumen',['id'=>$r['id'],'format'=>'pdf','preview'=>1]))?>" target="_blank" rel="noopener noreferrer"><?=bkk_ui_icon('eye',19)?> Lihat PDF <small>Buka di tab baru</small></a></div>
            <p class="muted">Word gabungan mengikuti PDF sebagai gambar per halaman. Untuk mengubah teks, gunakan Word individual dari ZIP.</p>
            <form method="post" action="<?=h(route_url('dokumen'))?>" class="minor-action-form"><input type="hidden" name="_csrf" value="<?=h(csrf())?>"><input type="hidden" name="id" value="<?=h($r['id'])?>"><input type="hidden" name="action" value="repair_word"><button class="text-button" type="submit" data-loading-label="Memperbarui Word...">Perbaiki Word gabungan dari PDF (jika diperlukan)</button></form>
          </div>
        </details>
      <?php endif; ?>
    </section>
    <?php endif; ?>
    <div class="detail-section-label"><div><h2>Ringkasan informasi</h2><p>Data yang digunakan saat pembuatan dokumen</p></div></div>
    <div class="cards"><article class="card"><div>Nomor ST</div><strong><?=h($r['nomor_st'])?></strong></article><article class="card"><div>Nomor SPPD</div><strong><?=h($r['nomor_sppd'])?></strong></article>
    <article class="card"><div>Petugas</div><strong>2 orang</strong></article><article class="card"><div>Output</div><strong><?=h($shipCount)?> BA + Surat Pernyataan</strong></article></div>
    <div class="two-panels"><section class="card"><h2>Administrasi dan Kegiatan</h2>
      <?php foreach (['nama_kegiatan'=>'Kegiatan','maksud_perjalanan'=>'Maksud perjalanan','tempat_berangkat'=>'Berangkat dari','tujuan'=>'Tujuan','tanggal_surat'=>'Tanggal surat','tanggal_tugas'=>'Tanggal tugas','tanggal_pemeriksaan'=>'Tanggal BA','tanggal_pernyataan'=>'Tanggal pernyataan','tanggal_berangkat'=>'Tanggal berangkat','tanggal_kembali'=>'Tanggal kembali','lama_hari'=>'Lama perjalanan (hari)'] as $key=>$name): ?>
      <div class="detail-line"><span><?=h($name)?></span><b><?=h(str_starts_with($key,'tanggal_') ? bkk_date_id((string)$r[$key]) : $r[$key])?></b></div><?php endforeach; ?>
    </section><section class="card"><h2>Petugas (Diisi Manual)</h2>
    <?php foreach ($r['petugas'] as $i=>$p): ?><h3>Petugas <?=h($i)?></h3><div class="detail-line"><span>Nama</span><b><?=h($p['nama'])?></b></div><div class="detail-line"><span>NIP</span><b><?=h($p['nip'])?></b></div>
    <div class="detail-line"><span>Pangkat/Golongan</span><b><?=h($p['pangkat_golongan'])?></b></div><div class="detail-line"><span>Jabatan</span><b><?=h($p['jabatan'])?></b></div><?php endforeach; ?>
    </section></div>
    <?php foreach ($r['kapal'] as $i=>$ship): ?><section class="card section-card"><div class="section-header"><h2>Kapal <?=h($i)?>: <?=h($ship['nama_kapal'])?></h2><span class="pill">1 BA + 1 Surat Pernyataan</span></div>
    <div class="details-grid">
    <?php foreach (['nama_agen'=>'Keagenan','posisi_labuh'=>'Posisi labuh','kebangsaan'=>'Kebangsaan','grt'=>'GRT','nomor_imo'=>'IMO','nama_nakhoda'=>'Nakhoda','jabatan_nakhoda'=>'Jabatan nakhoda','status_pemeriksaan'=>'Status pemeriksaan','rekomendasi'=>'Rekomendasi'] as $key=>$name): ?>
    <div><span class="muted"><?=h($name)?></span><p><?=h($ship[$key])?></p></div><?php endforeach; ?>
    </div></section><?php endforeach; ?>
    <?php
}
function render_penerbitan_page(array $user): void {
    $viewId = valid_positive_id($_GET['view'] ?? null);
    $editId = valid_positive_id($_GET['edit'] ?? null);
    $isNew = isset($_GET['new']);
    if ($viewId) {
        $record = bkk_penerbitan_load($viewId);
        if (!$record) { http_response_code(404); echo '<div class="alert error">Draft tidak ditemukan.</div>'; return; }
        bkk_render_detail($record, $user);
        return;
    }
    if ($editId || $isNew) {
        $record = $editId ? bkk_penerbitan_load($editId) : bkk_form_default();
        if (!$record) { http_response_code(404); echo '<div class="alert error">Draft tidak ditemukan.</div>'; return; }
        if ($editId && !bkk_is_editable($record, $user)) {
            http_response_code(403); echo '<div class="alert error">Hanya pembuat draft atau admin yang dapat mengedit draft.</div>'; return;
        }
        $old = $_SESSION['penerbitan_old'] ?? null;
        $errors = $_SESSION['penerbitan_errors'] ?? [];
        unset($_SESSION['penerbitan_old'], $_SESSION['penerbitan_errors']);
        if (is_array($old)) $record = array_replace_recursive($record, $old);
        bkk_render_form($record,$editId,$errors);
        return;
    }
    $q = trim((string)($_GET['q'] ?? ''));
    $sql = 'SELECT p.id,p.kode_paket,p.nomor_st,p.nomor_sppd,p.nama_kegiatan,p.status,p.created_at,p.created_by,u.nama AS pembuat,(SELECT COUNT(*) FROM penerbitan_kapal k WHERE k.penerbitan_id=p.id) AS jumlah_kapal FROM penerbitan p JOIN users u ON u.id = p.created_by';
    if ($q !== '') {
        $sql .= ' WHERE p.nomor_st LIKE ? OR p.nomor_sppd LIKE ? OR p.nama_kegiatan LIKE ?';
    }
    $sql .= ' ORDER BY p.id DESC LIMIT 150';
    $stmt = db()->prepare($sql);
    $term = '%' . str_replace(['\\','%','_'],['\\\\','\\%','\\_'],$q) . '%';
    $stmt->execute($q !== '' ? [$term,$term,$term] : []);
    $rows = $stmt->fetchAll();
    ?>
    <div class="page-heading"><div><span class="page-eyebrow">ARSIP & DRAFT</span><h1>Daftar penerbitan</h1><p>Semua draft dan surat yang telah diterbitkan ada di sini.</p></div><a class="btn btn-primary" href="<?=h(route_url('penerbitan',['new'=>1]))?>"><?=bkk_ui_icon('plus',19)?> Buat surat baru</a></div>
    <div class="list-guidance"><?=bkk_ui_icon('help',20)?><span><strong>Baru pertama kali?</strong> Klik <b>Buat surat baru</b> → isi data → simpan → preview → terbitkan.</span></div>
    <section class="card listing-card"><form method="get" class="search-bar" role="search"><input type="hidden" name="page" value="penerbitan"><label class="search-input-label" for="cari-penerbitan"><?=bkk_ui_icon('search',19)?><input id="cari-penerbitan" name="q" value="<?=h($q)?>" placeholder="Cari nomor atau nama kegiatan..."></label><button class="btn btn-primary" type="submit">Cari surat</button><?php if ($q !== ''): ?><a href="<?=h(route_url('penerbitan'))?>" class="search-reset">Reset</a><?php endif; ?></form>
    <div class="table-scroll"><table><thead><tr><th>Kode</th><th>Nomor ST</th><th>Nomor SPPD</th><th>Kegiatan</th><th>Kapal</th><th>Pembuat</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?=h($r['kode_paket'])?></td><td><?=h($r['nomor_st'])?></td><td><?=h($r['nomor_sppd'])?></td><td><?=h($r['nama_kegiatan'])?></td><td><?=h($r['jumlah_kapal'])?></td><td><?=h($r['pembuat'])?></td><td><span class="pill <?= $r['status']==='diterbitkan' ? 'pill-green' : 'pill-gray' ?>"><?=h($r['status']==='diterbitkan'?'Terbit':'Draft')?></span></td><td class="action-cell"><a class="btn tiny secondary" href="<?=h(route_url('penerbitan',['view'=>$r['id']]))?>">Buka <?=bkk_ui_icon('arrow',14)?></a></td></tr><?php endforeach; ?>
    </tbody></table></div><?php if (!$rows): ?><div class="empty-state"><h3>Belum ada surat</h3><p><?= $q !== '' ? 'Tidak ada surat yang cocok. Coba kata kunci lain.' : 'Buat surat pertama untuk mulai mengisi template otomatis.' ?></p><?php if ($q === ''): ?><a class="btn btn-primary" href="<?=h(route_url('penerbitan',['new'=>1]))?>">Buat surat baru</a><?php endif; ?></div><?php endif; ?></section>
    <?php
}
