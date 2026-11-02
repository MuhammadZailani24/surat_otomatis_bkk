<?php
declare(strict_types=1);
// Uji tanpa MySQL: jalankan via CMD: php tests/word_presisi.php hasil.pdf hasil.docx
require_once __DIR__.'/../app/document_service.php';
if(PHP_SAPI!=='cli' || $argc!==3) {
    fwrite(STDERR,"Gunakan: php tests/word_presisi.php <sumber.pdf> <hasil.docx>\n");
    exit(2);
}
try {
    bkk_word_from_pdf((string)$argv[1],(string)$argv[2]);
    echo "OK: Word gabungan visual berhasil dibuat: {$argv[2]}\n";
} catch(Throwable $e) {
    fwrite(STDERR,"ERROR: {$e->getMessage()}\n");
    exit(1);
}
