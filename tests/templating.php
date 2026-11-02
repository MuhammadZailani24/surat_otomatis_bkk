<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/penerbitan_service.php';
require __DIR__.'/../app/document_service.php';
if (!class_exists('ZipArchive') || !class_exists('DOMDocument')) {exit("Perlu ekstensi zip + dom untuk tes generator.\n");}
$testRecord=[
 'nomor_st'=>'SR.04.02/C.X.11/100/2026','nomor_sppd'=>'KU.01.02/C.X.11/100/2026',
 'nama_kegiatan'=>'Pemeriksaan Kapal Contoh','maksud_perjalanan'=>'Melaksanakan pemeriksaan kapal untuk penerbitan dokumen kesehatan',
 'tanggal_surat'=>'2026-10-31','tanggal_tugas'=>'2026-10-31','tanggal_ttd'=>'2026-10-31',
 'tanggal_pemeriksaan'=>'2026-10-31','tanggal_pernyataan'=>'2026-10-31',
 'tanggal_berangkat'=>'2026-10-31','tanggal_kembali'=>'2026-10-31',
 'tempat_berangkat'=>'Banjarmasin','tujuan'=>'Taboneo, Kab. Tanah Laut','lama_hari'=>1,
 'snapshot_pejabat_st'=>null,'snapshot_pejabat_ppk'=>null,
 'petugas'=>[1=>['nama'=>'Petugas Contoh Satu','nip'=>'123456789012345671','pangkat_golongan'=>'Penata Muda / IIIa','jabatan'=>'Petugas Pemeriksa'],
             2=>['nama'=>'Petugas Contoh Dua','nip'=>'123456789012345672','pangkat_golongan'=>'Penata / IIIc','jabatan'=>'Petugas Pemeriksa']],
 'kapal'=>[]
];
for($n=1;$n<=2;$n++) {
 $row=$testRecord;
 for($i=1;$i<=$n;$i++)$row['kapal'][$i]=[
 'nama_kapal'=>'Kapal Contoh '.$i,'nama_agen'=>'Agen Contoh '.$i,'posisi_labuh'=>'Taboneo',
 'nama_nakhoda'=>'Nakhoda Contoh '.$i,'jabatan_nakhoda'=>'Nakhoda',
 'kebangsaan'=>'Indonesia','grt'=>'2889 GT','nomor_imo'=>'1234567'
 ];
 $source=dirname(__DIR__)."/resources/templates/generated/paket_{$n}_kapal.docx";
 $tmp=tempnam(sys_get_temp_dir(),'bkk_');
 if (!$tmp || !copy($source,$tmp)) throw new RuntimeException('Tidak dapat menyalin template.');
 try {
   $keys=bkk_doc_fill_template($tmp,bkk_doc_values($row));
   $zip=new ZipArchive();$zip->open($tmp);
   $xml=$zip->getFromName('word/document.xml');$zip->close();
   if (!$xml || strpos($xml,'${')!==false) throw new RuntimeException("Masih ada placeholder pada template {$n}.");
   if (count($keys)<40) throw new RuntimeException('Placeholder tidak lengkap.');
   echo "PASS: {$n} kapal, ".count($keys)." variabel berhasil diisi.\n";
 } finally {if(is_file($tmp))unlink($tmp);}
}
echo "Selesai. Tes ini tidak membutuhkan database.\n";
