# Audit teknis Word gabungan — Versi 5

## Masalah pada versi sebelumnya

`resources/templates/generated/paket_{1,2}_kapal.docx` berisi gabungan beragam style sumber sehingga mengubah format BA/SPPD. Word dapat menerapkan page setup dan style yang berbeda ketika banyak DOCX digabungkan.

## Perbaikan

`app/word_exact_service.php` menerima **PDF gabungan final** yang dibuat tanpa menggabungkan DOCX. File tersebut kemudian dirender per halaman memakai `pdftoppm` (200 dpi), dan tiap halaman dimasukkan ke DOCX sebagai floating anchored image di 0,0 dengan section baru setiap halaman. Section menyimpan ukuran Legal/A4 masing-masing; margin nol. Hasilnya adalah **satu Word dengan halaman identik secara visual dengan PDF yang sama** (selain batas rasterisasi sekitar 1 piksel), tanpa mengganti kata pada dokumen.

`app/document_service.php` melakukan pengisian template asli dan penggabungan PDF seperti versi 4. Word gabungan diciptakan dari PDF final, bukan master gabungan. `bkk_doc_rebuild_word` menyediakan update Word pada arsip draft maupun terbit dengan sumber PDF tersimpan.

## Sifat/kompromi

- **Bisa diunduh satu file `.docx`.**
- **Rapi saat dibuka/dicetak** dengan penempatan halaman yang sama.
- **Tidak dapat mengubah satu kata di file Word visual tersebut** karena isi halaman tersimpan sebagai PNG, bukan text run.
- **File Word editable asli** tetap ada di ZIP per-template.
- Ukuran DOCX lebih besar; resolusi 200 dpi.
- Tanda tangan elektronik tidak ditambahkan atau diubah, PDF tetap tidak dimodifikasi.
- Konversi Word kembali ke PDF mungkin mengalami perbedaan antialiasing halus pada huruf, meski koordinat halaman sama.

## Ekstensi/dependensi

- PHP XAMPP: ZipArchive dan PDO/DOM (seperti sebelumnya).
- FPDI (seperti versi sebelumnya) untuk menghitung ukuran halaman.
- Poppler `pdftoppm.exe`: instal sekali via `bin/pasang-renderer-word.bat`, atau `BKK_PDFTOPPM` ke exe yang ada.
- LibreOffice tetap digunakan untuk membuat PDF sumber.

## QA

Tes prototipe pada 7 halaman dari template asli: output Word 7 halaman, ukuran Legal/A4 sesuai, bounding box konten berbeda maksimal ±1 piksel pada 72 dpi. Uji langsung pada Windows Microsoft Word masih diperlukan. Installer PowerShell belum diuji pada Windows.
