<?php
declare(strict_types=1);

/**
 * Word Gabungan Presisi Visual.
 *
 * Sumber kebenaran tata letak: PDF final yang sudah digabung secara presisi.
 * Setiap halaman PDF dirasterisasi sebagai PNG resolusi tinggi kemudian
 * ditempatkan pada halaman Word berukuran SAMA tanpa margin.
 *
 * PERHATIAN: DOCX ini SATU DOKUMEN dan tampil sesuai PDF, tetapi isi surat
 * adalah gambar halaman, bukan teks yang bisa diedit langsung. Untuk
 * mengedit teks, operator tetap memakai ZIP DOCX hasil template asli.
 */
function bkk_word_renderer_path(): string {
    $custom=trim((string)(getenv('BKK_PDFTOPPM') ?: ''));
    if ($custom !== '' && is_file($custom)) return $custom;
    $root=dirname(__DIR__);
    $paths=[
        $root.'/tools/poppler/Library/bin/pdftoppm.exe',
        $root.'/tools/poppler/bin/pdftoppm.exe',
        '/usr/bin/pdftoppm', '/usr/local/bin/pdftoppm',
        'C:/poppler/Library/bin/pdftoppm.exe',
    ];
    foreach (glob($root.'/tools/poppler/*/Library/bin/pdftoppm.exe') ?: [] as $path) $paths[]=$path;
    foreach (glob($root.'/tools/poppler/*/bin/pdftoppm.exe') ?: [] as $path) $paths[]=$path;
    foreach ($paths as $path) if (is_file($path)) return $path;
    throw new RuntimeException(
        'Renderer Word presisi belum tersedia (pdftoppm.exe). '
        .'Jalankan bin\\pasang-renderer-word.bat satu kali, lalu ulangi generator. '
        .'PDF dan Word ZIP lama tidak perlu diubah.'
    );
}

function bkk_word_pages_metadata(string $pdfPath): array {
    $autoload=dirname(__DIR__).'/vendor/autoload.php';
    if (is_file($autoload)) require_once $autoload;
    if (!class_exists('setasign\\Fpdi\\Fpdi')) {
        throw new RuntimeException('FPDI belum terpasang. Jalankan composer install untuk generator PDF dan Word.');
    }
    $fpdi=new \setasign\Fpdi\Fpdi();
    $count=$fpdi->setSourceFile($pdfPath);
    if ($count<1 || $count>40) throw new RuntimeException('Jumlah halaman PDF tidak didukung (maksimal 40).');
    $pages=[];
    for($i=1;$i<=$count;$i++) {
        $id=$fpdi->importPage($i);
        $size=$fpdi->getTemplateSize($id);
        // FPDI+FPDF menggunakan mm. DOCX memakai twip (1/20 pt).
        $w=(int)round(((float)$size['width'])*1440/25.4);
        $h=(int)round(((float)$size['height'])*1440/25.4);
        if($w<1000 || $h<1000 || $w>32000 || $h>32000) {
            throw new RuntimeException('Ukuran halaman PDF tidak wajar pada halaman '.$i);
        }
        $pages[]=['w'=>$w,'h'=>$h];
    }
    return $pages;
}

function bkk_word_rasterize(string $pdfPath,string $tempDir,int $expected): array {
    $binary=bkk_word_renderer_path();
    $prefix=$tempDir.'/page';
    $cmd=[$binary,'-f','1','-l',(string)$expected,'-r','200','-png','-q',$pdfPath,$prefix];
    $pipes=[];
    $process=proc_open($cmd,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$tempDir);
    if(!is_resource($process)) throw new RuntimeException('Tidak dapat menjalankan PDF renderer.');
    fclose($pipes[0]);
    $stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);
    if(proc_close($process)!==0) {
        error_log('BKK Word rasterizer: '.$stdout.' '.$stderr);
        throw new RuntimeException('Gagal membuat gambar halaman Word. Periksa pdftoppm.exe di bin/doctor.php.');
    }
    $files=glob($prefix.'-*.png') ?: [];
    natsort($files);$files=array_values($files);
    if(count($files)!==$expected) {
        throw new RuntimeException('Jumlah halaman Word hasil render '.count($files).' tidak sama dengan PDF '.$expected.'.');
    }
    foreach($files as $file) {
        $info=@getimagesize($file);
        if(!$info || $info[2]!==IMAGETYPE_PNG || filesize($file)<1000) {
            throw new RuntimeException('Gambar halaman hasil render tidak valid.');
        }
    }
    return $files;
}

function bkk_word_section_xml(int $w,int $h,bool $nextPage): string {
    $type=$nextPage?'<w:type w:val="nextPage"/>':'';
    return '<w:sectPr>'.$type.'<w:pgSz w:w="'.$w.'" w:h="'.$h.'"/>'
         .'<w:pgMar w:top="0" w:right="0" w:bottom="0" w:left="0" w:header="0" w:footer="0" w:gutter="0"/>'
         .'</w:sectPr>';
}

function bkk_word_picture_xml(int $n,int $w,int $h): string {
    // 1 twip = 635 EMU, offset halaman = 0 sehingga layout persis sumber PDF.
    $cx=$w*635;$cy=$h*635;$rid=$n+1;
    return '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="1" w:lineRule="exact"/></w:pPr>'
        .'<w:r><w:rPr><w:sz w:val="2"/></w:rPr><w:drawing>'
        .'<wp:anchor distT="0" distB="0" distL="0" distR="0" simplePos="0" relativeHeight="0" behindDoc="1" locked="0" layoutInCell="1" allowOverlap="1">'
        .'<wp:simplePos x="0" y="0"/>'
        .'<wp:positionH relativeFrom="page"><wp:posOffset>0</wp:posOffset></wp:positionH>'
        .'<wp:positionV relativeFrom="page"><wp:posOffset>0</wp:posOffset></wp:positionV>'
        .'<wp:extent cx="'.$cx.'" cy="'.$cy.'"/><wp:effectExtent l="0" t="0" r="0" b="0"/>'
        .'<wp:wrapNone/><wp:docPr id="'.$n.'" name="Halaman '.$n.'"/><wp:cNvGraphicFramePr/>'
        .'<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic>'
        .'<pic:nvPicPr><pic:cNvPr id="'.$n.'" name="Halaman '.$n.'.png"/><pic:cNvPicPr/></pic:nvPicPr>'
        .'<pic:blipFill><a:blip r:embed="rId'.$rid.'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
        .'<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'.$cx.'" cy="'.$cy.'"/></a:xfrm>'
        .'<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
        .'</pic:pic></a:graphicData></a:graphic></wp:anchor></w:drawing></w:r></w:p>';
}

function bkk_word_write_docx(string $target,array $images,array $pages): void {
    if(count($images)!==count($pages) || count($images)===0) throw new RuntimeException('Metadata halaman Word tidak lengkap.');
    $body='';$rels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    foreach($pages as $index=>$page) {
        $n=$index+1;
        $body.=bkk_word_picture_xml($n,$page['w'],$page['h']);
        if($n<count($pages)) {
            $body.='<w:p><w:pPr>'.bkk_word_section_xml($page['w'],$page['h'],true).'</w:pPr></w:p>';
        }
        $rels.='<Relationship Id="rId'.($n+1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/page'.sprintf('%03d',$n).'.png"/>';
    }
    $rels.='</Relationships>';
    $last=$pages[count($pages)-1];
    $body.=bkk_word_section_xml($last['w'],$last['h'],false);
    $document='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
        .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
        .'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
        .'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
        .'xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        .'<w:body>'.$body.'</w:body></w:document>';
    $rootRel='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
        .'</Relationships>';
    $types='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        .'<Default Extension="xml" ContentType="application/xml"/>'
        .'<Default Extension="png" ContentType="image/png"/>'
        .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
        .'</Types>';
    $zip=new ZipArchive();
    if($zip->open($target,ZipArchive::CREATE | ZipArchive::OVERWRITE)!==true) {
        throw new RuntimeException('Tidak dapat membuat paket Word gabungan.');
    }
    try {
        foreach(['[Content_Types].xml'=>$types,'_rels/.rels'=>$rootRel,
            'word/document.xml'=>$document,'word/_rels/document.xml.rels'=>$rels] as $name=>$contents) {
            if(!$zip->addFromString($name,$contents)) throw new RuntimeException('Tidak dapat menulis '.$name);
        }
        foreach($images as $index=>$png) {
            if(!$zip->addFile($png,'word/media/page'.sprintf('%03d',$index+1).'.png')) {
                throw new RuntimeException('Tidak dapat menambahkan gambar halaman Word '.($index+1));
            }
        }
    } finally {$zip->close();}
    if(!is_file($target) || filesize($target)<1000) throw new RuntimeException('Word gabungan kosong atau tidak valid.');
}

function bkk_word_from_pdf(string $pdf,string $output): void {
    if(!is_file($pdf) || !is_readable($pdf)) throw new RuntimeException('PDF final tidak dapat dibaca.');
    bkk_doc_ensure_environment();
    $pages=bkk_word_pages_metadata($pdf);
    $temp=dirname($output).'/word_pages_'.bin2hex(random_bytes(5));
    if(!mkdir($temp,0770,true)) throw new RuntimeException('Folder sementara Word tidak dapat dibuat.');
    try {
        $images=bkk_word_rasterize($pdf,$temp,count($pages));
        bkk_word_write_docx($output,$images,$pages);
    } finally {
        foreach(glob($temp.'/*') ?: [] as $path) if(is_file($path)) @unlink($path);
        @rmdir($temp);
    }
}

/** Perbaiki arsip Word yang TERLANJUR diterbitkan; PDF dan ZIP asli tidak disentuh. */
function bkk_doc_rebuild_word(array $r): void {
    $pdf=bkk_doc_archive($r,'pdf');
    if(!$pdf) throw new DomainException('PDF final yang valid tidak ditemukan. Buat preview atau periksa arsip.');
    $root=dirname($pdf['safe_path']);
    $new=$root.'/paket_bkk_'.(int)$r['id'].'_word_presisi_'.bin2hex(random_bytes(5)).'.docx';
    $old=bkk_doc_archive($r,'docx');
    try {
        bkk_word_from_pdf($pdf['safe_path'],$new);
        $stmt=db()->prepare("INSERT INTO dokumen_arsip (penerbitan_id,jenis,nama_file,path_file,sha256) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE nama_file=VALUES(nama_file),path_file=VALUES(path_file),sha256=VALUES(sha256),created_at=CURRENT_TIMESTAMP");
        $stmt->execute([(int)$r['id'],'docx',basename($new),$new,hash_file('sha256',$new)]);
        audit_log('perbaiki_word_presisi','Word gabungan dirender ulang dari PDF final; PDF dan Word ZIP asli tidak berubah.',(int)$r['id']);
        if($old && $old['safe_path']!==$new && is_file($old['safe_path'])) @unlink($old['safe_path']);
    } catch(Throwable $e) {
        if(is_file($new)) @unlink($new);
        throw $e;
    }
}
