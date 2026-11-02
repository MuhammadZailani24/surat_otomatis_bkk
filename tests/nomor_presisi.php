<?php
declare(strict_types=1);
require __DIR__.'/../app/document_service.php';
$st=bkk_doc_nomor_bagian('SR.04.02/C.X.11/123/2026','SR.04.02/C.X.11','Nomor ST');
$sppd=bkk_doc_nomor_bagian('KU.01.02/C.X.11/456/2026','KU.01.02/C.X.11','Nomor SPPD');
if ($st!==['123','2026'] || $sppd!==['456','2026']) throw new RuntimeException('Nomor tidak terpetakan dengan benar.');
try {bkk_doc_nomor_bagian('SR.09.99/C.X.11/123/2026','SR.04.02/C.X.11','Nomor ST');throw new RuntimeException('Awalan salah diterima!');}
catch(DomainException $e) {}
echo "PASS: nomor manual mempertahankan prefix resmi; awalan salah ditolak.\n";
