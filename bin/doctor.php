<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/penerbitan_service.php';
require __DIR__.'/../app/document_service.php';
echo "DIAGNOSTIK SURAT BKK - Word Gabungan Presisi\n\n";
echo 'Versi PHP: '.PHP_VERSION."\n";
echo 'ZipArchive: '.(class_exists('ZipArchive')?'OK':'TIDAK ADA')."\n";
echo 'DOMDocument: '.(class_exists('DOMDocument')?'OK':'TIDAK ADA')."\n";
echo 'PDO MySQL: '.(in_array('mysql',PDO::getAvailableDrivers(),true)?'OK':'TIDAK ADA')."\n";
echo 'proc_open: '.(function_exists('proc_open')?'OK':'TIDAK ADA')."\n";
try { echo 'LibreOffice: '.bkk_doc_soffice()."\n"; }
catch(Throwable $e) {echo 'LibreOffice: ERROR: '.$e->getMessage()."\n";}
try { echo 'Renderer Word presisi (pdftoppm): '.bkk_word_renderer_path()."\n"; }
catch(Throwable $e) {echo 'Renderer Word presisi: BELUM ADA. Jalankan bin\\pasang-renderer-word.bat'."\n";}
$autoload=dirname(__DIR__).'/vendor/autoload.php';
if (is_file($autoload)) require_once $autoload;
$fpdi=class_exists('setasign\\Fpdi\\Fpdi');
$pdfunite=(is_file('/usr/bin/pdfunite') || is_file('C:/poppler/Library/bin/pdfunite.exe') || (getenv('BKK_PDFUNITE') && is_file(getenv('BKK_PDFUNITE'))));
echo 'Penggabung PDF: '.($fpdi ? 'FPDI OK' : ($pdfunite ? 'pdfunite OK' : 'BELUM ADA. Jalankan composer install'))."\n";
foreach (['st_original.docx','sppd_asli.docx','ba_kapal_1.docx','ba_kapal_2.docx'] as $name) {
 $path=dirname(__DIR__).'/resources/templates/precision/'.$name;
 echo 'Template original '.$name.': '.(is_file($path)?'OK':'TIDAK ADA')."\n";
}
try { db()->query('SELECT 1'); echo "MySQL: TERHUBUNG\n"; }
catch(Throwable $e) {echo "MySQL: TIDAK TERHUBUNG (periksa konfigurasi database)\n";}
