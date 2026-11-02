<?php
declare(strict_types=1);

require_once __DIR__.'/word_exact_service.php';

/**
 * Tahap 3: satu master DOCX (1 / 2 kapal) diperkaya placeholder dari template asli.
 * Menggunakan ekstensi PHP ZipArchive + DOM (standar XAMPP), tanpa Composer.
 */
function bkk_doc_root(): string { return dirname(__DIR__) . '/storage/generated'; }
function bkk_doc_ensure_environment(): void {
    if (!class_exists('ZipArchive') || !class_exists('DOMDocument')) {
        throw new RuntimeException('Ekstensi PHP zip dan dom wajib aktif di XAMPP (php.ini).');
    }
    if (!is_dir(bkk_doc_root()) && !mkdir(bkk_doc_root(), 0770, true) && !is_dir(bkk_doc_root())) {
        throw new RuntimeException('Folder storage/generated tidak bisa dibuat.');
    }
}
function bkk_doc_indo_days(string $sqlDate): array {
    $d = new DateTimeImmutable($sqlDate);
    $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return [$days[(int)$d->format('w')], $d->format('j'), $months[(int)$d->format('n')], $d->format('Y')];
}
function bkk_doc_terbilang(int $n): string {
    $base = [0=>'nol',1=>'satu',2=>'dua',3=>'tiga',4=>'empat',5=>'lima',6=>'enam',7=>'tujuh',8=>'delapan',9=>'sembilan',10=>'sepuluh',11=>'sebelas'];
    if ($n<=11) return $base[$n];
    if ($n<20) return bkk_doc_terbilang($n-10).' belas';
    if ($n<100) return bkk_doc_terbilang(intdiv($n,10)).' puluh'.($n%10?' '.bkk_doc_terbilang($n%10):'');
    if ($n<200) return 'seratus'.($n>100?' '.bkk_doc_terbilang($n-100):'');
    if ($n<1000) return bkk_doc_terbilang(intdiv($n,100)).' ratus'.($n%100?' '.bkk_doc_terbilang($n%100):'');
    return (string)$n;
}
/** Ambil hanya bagian nomor dan tahun yang ditandai kuning, tanpa mengganti kode tetap instansi. */
function bkk_doc_nomor_bagian(string $input, string $prefix, string $label): array {
    $clean=trim($input);
    $head=$prefix.'/';
    if (!str_starts_with($clean,$head)) {
        throw new DomainException($label.' harus diawali '.$head.' sesuai template asli. Silakan Edit Draft.');
    }
    $suffix=substr($clean,strlen($head));
    if (!preg_match('~^([^/\s]+)/([0-9]{4})$~D',$suffix,$matches)) {
        throw new DomainException($label.' harus mengikuti format '.$prefix.'/NOMOR/TAHUN. Silakan Edit Draft.');
    }
    return [$matches[1],$matches[2]];
}
function bkk_doc_pejabat(?string $json, string $fallbackName, string $fallbackNip): array {
    $data = json_decode($json ?: '', true);
    return [
        'nama'=> is_array($data) && !empty($data['nama']) ? (string)$data['nama'] : $fallbackName,
        'nip'=> is_array($data) && !empty($data['nip']) ? (string)$data['nip'] : $fallbackNip,
    ];
}
function bkk_doc_values(array $r): array {
    foreach ([1,2] as $i) {
        if (empty($r['petugas'][$i])) throw new RuntimeException('Dua data petugas diperlukan untuk membuat dokumen.');
    }
    if (count($r['kapal']) < 1 || count($r['kapal']) > 2) throw new RuntimeException('Jumlah kapal harus 1 atau 2.');
    [$day,$date,$month,$year] = bkk_doc_indo_days($r['tanggal_pemeriksaan']);
    [, $stmtDate, $stmtMonth,$stmtYear] = bkk_doc_indo_days($r['tanggal_pernyataan']);
    $st = bkk_doc_pejabat($r['snapshot_pejabat_st'] ?? null, 'Maman Sudirman, ST, M.Kes', '197001121994031004');
    $ppk = bkk_doc_pejabat($r['snapshot_pejabat_ppk'] ?? null, 'Khairul Ikhwan, SKM', '198909262010121007');
    [$stNomor,$stTahun] = bkk_doc_nomor_bagian((string)$r['nomor_st'], 'SR.04.02/C.X.11', 'Nomor ST');
    [$sppdNomor,$sppdTahun] = bkk_doc_nomor_bagian((string)$r['nomor_sppd'], 'KU.01.02/C.X.11', 'Nomor SPPD');
    $v = [
        'nomor_surat'=>$stNomor, 'tahun'=>$stTahun,
        'sppd_urut'=>$sppdNomor, 'sppd_tahun'=>$sppdTahun,
        'nomor_st'=>(string)$r['nomor_st'], 'nomor_sppd'=>(string)$r['nomor_sppd'],
        'nama_kegiatan'=>(string)$r['nama_kegiatan'], 'maksud_perjalanan'=>(string)$r['maksud_perjalanan'],
        'tanggal_surat'=>bkk_date_id($r['tanggal_surat']),
        'tanggal_berlaku'=>bkk_date_id($r['tanggal_tugas']),
        'tanggal_ttd'=>bkk_date_id($r['tanggal_ttd']),
        'tanggal_berangkat'=>bkk_date_id($r['tanggal_berangkat']),
        'tanggal_kembali'=>bkk_date_id($r['tanggal_kembali']),
        'tanggal_pernyataan_ba'=>"{$stmtDate} / {$stmtMonth} / {$stmtYear}",
        'tempat_berangkat'=>(string)$r['tempat_berangkat'], 'tujuan'=>(string)$r['tujuan'],
        'lama_hari_text'=>(string)$r['lama_hari'].' ('.bkk_doc_terbilang((int)$r['lama_hari']).') hari',
        'hari_ba'=>$day, 'tanggal_angka_ba'=>$date, 'bulan_ba'=>$month,'tahun_ba'=>$year,
        'nama_pejabat_st'=>$st['nama'], 'nip_pejabat_st'=>$st['nip'],
        'nama_ppk'=>$ppk['nama'], 'nip_ppk'=>$ppk['nip'],
    ];
    foreach ([1,2] as $i) {
        $p=$r['petugas'][$i];
        $v["nama_petugas_{$i}"]=(string)$p['nama'];
        $v["nip_{$i}"]=(string)$p['nip'];
        $v["pangkat_{$i}"]=(string)$p['pangkat_golongan'];
        $v["jabatan_{$i}"]=(string)$p['jabatan'];
        // Pada BA asli, titik penutup nama petugas kedua berada di luar highlight kuning.
        $v["ba_ttd_petugas_{$i}"]=$i===2 ? rtrim((string)$p['nama'], " .\t") : (string)$p['nama'];
        $v["sppd_identitas_{$i}"]=$p['nama'].' / '.$p['nip'];
        $v["ba_identitas_{$i}"]=$p['nama'].' / '.$p['nip'];
    }
    foreach ([1,2] as $i) {
        $s=$r['kapal'][$i] ?? null;
        $v["nama_kapal_{$i}"]=$s['nama_kapal'] ?? '-';
        $v["agen_{$i}"]=$s['nama_agen'] ?? '-';
        $v["posisi_{$i}"]=$s['posisi_labuh'] ?? '-';
        $v["nama_kapal_ba_{$i}"]=$s['nama_kapal'] ?? '-';
        $v["nama_nakhoda_{$i}"]=$s['nama_nakhoda'] ?? '-';
        $v["jabatan_nakhoda_{$i}"]=$s['jabatan_nakhoda'] ?? 'Nakhoda';
        $v["kebangsaan_{$i}"]=$s['kebangsaan'] ?: '-';
        $v["grt_{$i}"]=preg_replace('/\s*GT\s*$/i','',trim((string)($s['grt'] ?? ''))) ?: '-';
        $v["nomor_imo_{$i}"]=$s['nomor_imo'] ?: '-';
        $v["posisi_labuh_{$i}"]=$s['posisi_labuh'] ?? '-';
        $v["ba_ttd_nakhoda_{$i}"]='('.((string)($s['nama_nakhoda'] ?? '-')).')';
    }
    return $v;
}

/** Return placeholders found across runs of a paragraph, replacing them via DOM text nodes. */
function bkk_doc_fill_xml(string $xml, array $values, array &$used): string {
    $dom = new DOMDocument('1.0','UTF-8');
    $dom->preserveWhiteSpace=true;
    if (!$dom->loadXML($xml, LIBXML_NONET)) throw new RuntimeException('XML dokumen tidak valid.');
    $xp=new DOMXPath($dom);
    $xp->registerNamespace('w','http://schemas.openxmlformats.org/wordprocessingml/2006/main');
    foreach ($xp->query('//w:p') as $paragraph) {
        $nodes=[];
        foreach ($xp->query('.//w:t', $paragraph) as $node) $nodes[]=$node;
        if (!$nodes) continue;
        $all='';foreach($nodes as $node) $all.=$node->textContent;
        if (!preg_match_all('/\$\{([a-z][a-z0-9_]*)\}/i', $all, $found, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) continue;
        for ($i=count($found)-1;$i>=0;$i--) {
            $key=$found[$i][1][0];
            if (!array_key_exists($key,$values)) throw new RuntimeException('Placeholder belum dipetakan: '.$key);
            $used[$key]=true;
            $start=$found[$i][0][1];$end=$start+strlen($found[$i][0][0]);
            $offset=0;$written=false;
            foreach ($nodes as $node) {
                $text=$node->textContent;
                $a=$offset;$b=$a+strlen($text);$offset=$b;
                if ($b<=$start || $a>=$end) continue;
                $before=($a<=$start && $start<$b)?substr($text,0,$start-$a):'';
                $after=($a<$end && $end<=$b)?substr($text,$end-$a):'';
                $node->nodeValue=$before.(!$written?(string)$values[$key]:'').$after;
                $written=true;
            }
        }
    }
    return $dom->saveXML();
}
function bkk_doc_fill_template(string $source,array $values): array {
    bkk_doc_ensure_environment();
    $zip=new ZipArchive();
    if ($zip->open($source)!==true) throw new RuntimeException('Template DOCX tidak dapat dibuka.');
    try {
        $used=[];
        for($i=0;$i<$zip->numFiles;$i++) {
            $filename=$zip->getNameIndex($i);
            if (!preg_match('~^word/(document|header\d+|footer\d+)\.xml$~',$filename)) continue;
            $xml=$zip->getFromName($filename);
            if ($xml===false) throw new RuntimeException('Bagian DOCX hilang: '.$filename);
            $updated=bkk_doc_fill_xml($xml,$values,$used);
            if (!$zip->addFromString($filename,$updated)) throw new RuntimeException('Tidak dapat memperbarui: '.$filename);
        }
        if (!$used) throw new RuntimeException('Tidak ada placeholder yang berhasil ditemukan.');
    } finally {$zip->close();}
    // Panggil hanya terhadap salinan sementara, bukan template sumber.
    return array_keys($used);
}
function bkk_doc_soffice(): string {
    $configured=trim((string)(getenv('BKK_SOFFICE') ?: ''));
    if ($configured!=='' && is_file($configured)) return $configured;
    $candidates=['C:/Program Files/LibreOffice/program/soffice.exe','C:/Program Files (x86)/LibreOffice/program/soffice.exe','/usr/bin/libreoffice','/usr/bin/soffice'];
    foreach ($candidates as $candidate) if (is_file($candidate)) return $candidate;
    throw new RuntimeException('LibreOffice belum ditemukan. Instal LibreOffice atau atur BKK_SOFFICE ke soffice.exe.');
}
function bkk_doc_to_pdf(string $source,string $folder): string {
    $executable=bkk_doc_soffice();
    $profile=sys_get_temp_dir().'/bkk_lo_'.bin2hex(random_bytes(6));
    $url='file:///'.str_replace(' ','%20',ltrim(str_replace('\\','/',$profile),'/'));
    if (preg_match('~^[A-Za-z]:/~',$profile)) $url='file:///'.str_replace(' ','%20',str_replace('\\','/',$profile));
    $cmd=[$executable,'-env:UserInstallation='.$url,'--headless','--convert-to','pdf:writer_pdf_Export','--outdir',$folder,$source];
    $pipes=[];
    $process=proc_open($cmd,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$folder);
    if (!is_resource($process)) throw new RuntimeException('Gagal menjalankan konversi LibreOffice.');
    fclose($pipes[0]);$stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    $code=proc_close($process);
    $pdf=$folder.'/'.pathinfo($source,PATHINFO_FILENAME).'.pdf';
    if ($code!==0 || !is_file($pdf) || filesize($pdf)<1000) {
        error_log('BKK LibreOffice: '.$stdout.' '.$stderr);
        throw new RuntimeException('Konversi PDF gagal. Periksa instalasi LibreOffice, izin folder dan log PHP.');
    }
    return $pdf;
}
function bkk_doc_safe_file(string $path): ?string {
    $root=realpath(bkk_doc_root());$file=realpath($path);
    if ($root===false || $file===false) return null;
    $prefix=rtrim(str_replace('\\','/',$root),'/').'/';
    $real=str_replace('\\','/',$file);
    if (DIRECTORY_SEPARATOR==='\\') {
        $prefix=strtolower($prefix);$real=strtolower($real);
    }
    return str_starts_with($real,$prefix) ? $file : null;
}
function bkk_doc_archive(array $r,string $type): ?array {
    $stmt=db()->prepare('SELECT * FROM dokumen_arsip WHERE penerbitan_id=? AND jenis=?');
    $stmt->execute([(int)$r['id'],$type]);
    $archive=$stmt->fetch();
    if (!$archive) return null;
    $path=bkk_doc_safe_file((string)$archive['path_file']);
    if (!$path) return null;
    if (!hash_equals((string)$archive['sha256'],hash_file('sha256',$path))) return null;
    $archive['safe_path']=$path;
    return $archive;
}
/**
 * Generator PRESISI:
 * 1. Salin template asli yang telah diberi placeholder di area kuning.
 * 2. Isi masing-masing salinan TANPA menyalin style antar-dokumen.
 * 3. Konversi DOCX ke PDF secara mandiri dengan LibreOffice.
 * 4. Gabung PDF per halaman (FPDI), mempertahankan ukuran Legal/A4.
 * 5. Sediakan Word gabungan (best effort) dan ZIP Word mandiri berlayout asli.
 */
function bkk_doc_merge_pdf(array $inputs, string $output): void {
    // FPDI + FPDF dipasang sekali via Composer. Alternatif lokal Poppler pdfunite bila tersedia.
    $autoload=dirname(__DIR__).'/vendor/autoload.php';
    if (is_file($autoload)) require_once $autoload;
    if (class_exists('setasign\\Fpdi\\Fpdi')) {
        $pdf=new \setasign\Fpdi\Fpdi();
        foreach ($inputs as $file) {
            $pages=$pdf->setSourceFile($file);
            for ($i=1;$i<=$pages;$i++) {
                $page=$pdf->importPage($i);
                $size=$pdf->getTemplateSize($page);
                $pdf->AddPage($size['orientation'], [$size['width'],$size['height']]);
                $pdf->useTemplate($page);
            }
        }
        $pdf->Output('F',$output);
    } else {
        $pdfunite=trim((string)(getenv('BKK_PDFUNITE') ?: ''));
        if ($pdfunite==='') {
            foreach (['/usr/bin/pdfunite','C:/poppler/Library/bin/pdfunite.exe'] as $cand) {
                if (is_file($cand)) {$pdfunite=$cand;break;}
            }
        }
        if ($pdfunite==='' || !is_file($pdfunite)) {
            throw new RuntimeException('Mesin penggabung PDF belum dipasang. Jalankan "composer install" di folder surat-bkk untuk memasang setasign/fpdi dan setasign/fpdf.');
        }
        $args=array_merge([$pdfunite],$inputs,[$output]);
        $pipes=[];$process=proc_open($args,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,dirname($output));
        if (!is_resource($process)) throw new RuntimeException('Gagal memulai penggabungan PDF.');
        fclose($pipes[0]);$stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);
        fclose($pipes[1]);fclose($pipes[2]);
        if (proc_close($process)!==0) {
            error_log('BKK merge: '.$stdout.' '.$stderr);
            throw new RuntimeException('Penggabungan PDF gagal.');
        }
    }
    if (!is_file($output) || filesize($output)<1000) throw new RuntimeException('Hasil PDF gabungan tidak valid.');
}
function bkk_doc_generate(array $r): void {
    if ($r['status']!=='draft') throw new DomainException('Hanya draft yang bisa diproses.');
    bkk_doc_ensure_environment();
    $num=count($r['kapal']);
    if ($num<1 || $num>2) throw new RuntimeException('Jumlah kapal tidak valid.');
    $templates=dirname(__DIR__).'/resources/templates/precision';
    $files=[
        '01_surat_tugas'=>'st_original.docx',
        '02_sppd'=>'sppd_asli.docx',
        '03_berita_acara_kapal_1'=>'ba_kapal_1.docx',
    ];
    if ($num===2) $files['04_berita_acara_kapal_2']='ba_kapal_2.docx';
    $values=bkk_doc_values($r);
    $root=bkk_doc_root().'/'.(int)$r['id'];
    if (!is_dir($root) && !mkdir($root,0770,true) && !is_dir($root)) throw new RuntimeException('Folder output tidak bisa dibuat.');
    $nonce=bin2hex(random_bytes(5));
    $base='paket_bkk_'.(int)$r['id'].'_'.$nonce;
    $work=$root.'/temp_'.$nonce;
    if (!mkdir($work,0770,true)) throw new RuntimeException('Tidak dapat membuat direktori sementara.');
    $docx=$root.'/'.$base.'.docx';
    $pdfFinal=$root.'/'.$base.'.pdf';
    $zipFinal=$root.'/'.$base.'_word_asli.zip';
    $complete=false;
    try {
        $pdfs=[];$words=[];
        foreach ($files as $label=>$templateName) {
            $src=$templates.'/'.$templateName;
            if (!is_file($src)) throw new RuntimeException('Template presisi tidak ditemukan: '.$templateName);
            $dest=$work.'/'.$label.'.docx';
            if (!copy($src,$dest)) throw new RuntimeException('Salin DOCX gagal: '.$label);
            bkk_doc_fill_template($dest,$values);
            $words[$label]=$dest;
            $pdfs[]=bkk_doc_to_pdf($dest,$work);
        }
        bkk_doc_merge_pdf($pdfs,$pdfFinal);
        // Arsip Word asli: 1 DOCX legal + 1 DOCX SPPD + 1/2 DOCX BA (masing-masing 2 halaman)
        $z=new ZipArchive();
        if ($z->open($zipFinal,ZipArchive::CREATE | ZipArchive::OVERWRITE)!==true) throw new RuntimeException('Tidak dapat membuat ZIP Word asli.');
        try { foreach ($words as $label=>$path) $z->addFile($path,$label.'.docx'); }
        finally {$z->close();}
        // Word GABUNGAN PRESISI VISUAL: susunan gambar per halaman PDF final.
        // Dengan demikian Legal/A4, kop, tabel dan tanda tangan tak berubah.
        // Dokumen Word gabungan ini tidak dapat diedit per teks (berbasis gambar).
        bkk_word_from_pdf($pdfFinal,$docx);
        $previous=db()->prepare('SELECT path_file FROM dokumen_arsip WHERE penerbitan_id=?');
        $previous->execute([(int)$r['id']]);
        $oldPaths=$previous->fetchAll(PDO::FETCH_COLUMN);
        $pdo=db();$pdo->beginTransaction();
        try {
            foreach (['docx'=>$docx,'pdf'=>$pdfFinal] as $type=>$path) {
                $stmt=$pdo->prepare('INSERT INTO dokumen_arsip (penerbitan_id,jenis,nama_file,path_file,sha256) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE nama_file=VALUES(nama_file),path_file=VALUES(path_file),sha256=VALUES(sha256),created_at=CURRENT_TIMESTAMP');
                $stmt->execute([(int)$r['id'],$type,basename($path),$path,hash_file('sha256',$path)]);
            }
            audit_log('generate_preview','PDF per template + Word presisi mengikuti halaman PDF + ZIP DOCX editable ('.$num.' kapal)',(int)$r['id']);
            $pdo->commit();$complete=true;
            foreach ($oldPaths as $path) {
                $old=bkk_doc_safe_file((string)$path);
                if ($old && is_file($old)) {
                    if (str_ends_with(strtolower($old),'.pdf')) {
                        $oldZip=bkk_doc_safe_file(substr($old,0,-4).'_word_asli.zip');
                        if ($oldZip && is_file($oldZip)) @unlink($oldZip);
                    }
                    @unlink($old);
                }
            }
        } catch (Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    } finally {
        foreach (glob($work.'/*') ?: [] as $file) if (is_file($file)) @unlink($file);
        @rmdir($work);
        if (!$complete) {foreach ([$docx,$pdfFinal,$zipFinal] as $p) if (is_file($p)) @unlink($p);}
    }
}
function bkk_doc_publish(array $r): void {
    if ($r['status']!=='draft') throw new DomainException('Hanya draft yang dapat diterbitkan.');
    if (!bkk_doc_archive($r,'pdf') || !bkk_doc_archive($r,'docx')) throw new DomainException('Buat dan periksa preview Word/PDF terlebih dahulu.');
    $stmt=db()->prepare("UPDATE penerbitan SET status='diterbitkan' WHERE id=? AND status='draft'");
    $stmt->execute([(int)$r['id']]);
    if ($stmt->rowCount()!==1) throw new DomainException('Status surat sudah berubah, muat ulang halaman.');
    audit_log('terbitkan_dokumen','Paket surat dinyatakan diterbitkan oleh operator (tanpa TTE)',(int)$r['id']);
}
function bkk_doc_invalidate(int $id): void {
    $stmt=db()->prepare('SELECT path_file FROM dokumen_arsip WHERE penerbitan_id=?');
    $stmt->execute([$id]);
    $paths=$stmt->fetchAll(PDO::FETCH_COLUMN);
    db()->prepare('DELETE FROM dokumen_arsip WHERE penerbitan_id=?')->execute([$id]);
    foreach($paths as $p){
        $safe=bkk_doc_safe_file((string)$p);
        if ($safe && str_ends_with(strtolower($safe),'.pdf')) {
            $oldZip=bkk_doc_safe_file(substr($safe,0,-4).'_word_asli.zip');
            if ($oldZip && is_file($oldZip)) @unlink($oldZip);
        }
        if($safe && is_file($safe))@unlink($safe);
    }
}
function bkk_doc_authorized(array $r,array $user): bool {
    return $user['role']==='admin' || (int)$r['created_by']===(int)$user['id'];
}
function bkk_doc_stream(array $r,array $user): never {
    if (!bkk_doc_authorized($r,$user)) {http_response_code(403);exit('Tidak berwenang mengunduh arsip ini.');}
    $type=(string)($_GET['format']??'');
    if (!in_array($type,['pdf','docx','word_asli'],true)){http_response_code(400);exit('Format tidak dikenal.');}
    $file=bkk_doc_archive($r,$type==='word_asli'?'pdf':$type);
    if(!$file){http_response_code(404);exit('Dokumen belum dibuat atau arsip tidak valid.');}
    if ($type==='word_asli') {
        $path=preg_replace('/\.pdf$/i','_word_asli.zip',$file['safe_path']);
        $safe=is_string($path)?bkk_doc_safe_file($path):null;
        if (!$safe || !is_file($safe)) {http_response_code(404);exit('Paket Word per template belum tersedia.');}
        header('Content-Type: application/zip');
        header('Content-Length: '.filesize($safe));
        header('Content-Disposition: attachment; filename="word_asli_bkk_'.(int)$r['id'].'.zip"');
        header('Cache-Control: private, no-store');readfile($safe);exit;
    }
    $inline=$type==='pdf' && (string)($_GET['preview']??'')==='1';
    header('Content-Type: '.($type==='pdf'?'application/pdf':'application/vnd.openxmlformats-officedocument.wordprocessingml.document'));
    header('Content-Length: '.filesize($file['safe_path']));
    header('Content-Disposition: '.($inline?'inline':'attachment').'; filename="paket_bkk_'.(int)$r['id'].'.'.$type.'"');
    header('Cache-Control: private, no-store');
    readfile($file['safe_path']);exit;
}
function bkk_doc_post(array $user): never {
    csrf_validate();
    $id=valid_positive_id($_POST['id']??null);
    if (!$id || !$r=bkk_penerbitan_load($id)) {http_response_code(404);exit('Draft tidak ditemukan.');}
    if (!bkk_doc_authorized($r,$user)) {http_response_code(403);exit('Tidak mempunyai izin untuk dokumen ini.');}
    $action=(string)($_POST['action']??'');
    try {
        if($action==='generate') bkk_doc_generate($r);
        elseif($action==='repair_word') bkk_doc_rebuild_word($r);
        elseif($action==='publish') bkk_doc_publish($r);
        else {http_response_code(400);exit('Aksi tidak valid.');}
        flash($action==='generate'?'Preview Word gabungan presisi dan PDF berhasil dibuat. Periksa hasil sebelum diterbitkan.':($action==='repair_word'?'Word gabungan presisi berhasil dibuat ulang dari PDF, tanpa mengubah PDF.':'Paket berhasil diterbitkan dan diarsipkan.'));
    } catch(Throwable $e) {
        error_log('Dokumen Tahap 3: '.$e->getMessage());
        flash($e instanceof DomainException?$e->getMessage():'Generator gagal: '.$e->getMessage(),'error');
    }
    go('penerbitan',['view'=>$id]);
}
