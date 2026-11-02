# Audit sumber dan batas presisi

## Penyebab visual sebelumnya

Sebelumnya, dokumen ST (Legal) dan SPPD/BA (A4) digabung menjadi satu badan DOCX. Meskipun naskah tetap tidak berubah, source-specific styles dan tabel tidak seluruhnya terisolasi. Ini membuat tinggi row, posisi kop BA, baris Surat Pernyataan, ruang tanda tangan, dan page break menjadi berubah.

## Perbaikan

1. Empat template kerja `precision/*.docx` dibuat dari **byte file sumber DOCX masing-masing** (bukan rekonstruksi HTML atau pembuatan ulang paragraf).
2. Pada ST, hanya `${...}` yang sudah ada di dokumen asli yang diisi.
3. Pada SPPD, hanya fragmen teks yang ditandai highlight kuning yang dijadikan placeholder. Awalan nomor SPPD tetap literal.
4. Pada BA, hanya fragmen yang ditandai highlight kuning dan **tanggal pernyataan halaman kedua** (perubahan tambahan atas permintaan pengguna) yang menjadi placeholder.
5. File dikonversi ke PDF **secara terpisah** sehingga legal/A4, tab stop, font, section, media, padding tabel, dan margin asli dari tiap file tidak saling memengaruhi.
6. Semua halaman PDF kemudian diimpor satu demi satu dan digabung memakai FPDI; ukuran dan orientasi diambil dari halaman PDF asli.
7. Untuk Word yang bisa diedit persis seperti asal, aplikasi juga memberi ZIP berisi DOCX **masing-masing**.

## Pengujian yang dilaksanakan

- File DOCX terisi dan diekspor ke PDF dengan LibreOffice Linux untuk: ST, SPPD, BA1, BA2.
- Tidak ada placeholder `${...}` yang tertinggal pada contoh.
- ST: 1 halaman Legal; SPPD: 4 halaman A4; BA1: 2 halaman A4; BA2: 2 halaman A4 pada mesin uji.
- PDF gabungan dengan data contoh menggunakan **7 halaman** untuk 1 kapal dan **9 halaman** untuk 2 kapal. Ukuran tiap halaman tetap mengikuti sumber.
- Halaman kedua BA (Surat Pernyataan) diperiksa pada render: paragraf bilingual memanjang normal, bukan menjadi kolom sempit; QR, tanggal, judul dan area tanda tangan kembali berada pada halaman yang sama.
- Source code PHP dicek dengan `php -l`. Pengujian penuh menggunakan `FPDI + Apache + MySQL` di XAMPP Windows pengguna **belum dilakukan**.

## Batas yang masih berlaku

1. **Word gabungan belum pixel-perfect.** Merging OOXML dengan multiple styles bisa tetap mengubah page break, terutama BA kedua. Untuk presisi teks editable, gunakan ZIP Word per-template; untuk paket gabungan siap cetak, gunakan PDF.
2. Sumber SPPD dapat dirender **4 halaman oleh LibreOffice** meski tampak 3 pada Word/parsing lain. Jika target instansi adalah jumlah halaman spesifik dari Word, format dan font harus diuji pada Word Windows dan printer instansi.
3. Ukuran nama/kegiatan yang lebih panjang dapat mengubah aliran teks meski template tidak dimodifikasi.
4. PDF sumber dari LibreOffice tidak selalu identik piksel dengan PDF ekspor Microsoft Word. Tanpa menggunakan mesin rendering Microsoft Word yang sama, **tidak mungkin menjamin 100% identik pada setiap perangkat**.
5. Template asli memuat aturan, QR, nama pejabat, dan redaksi tertentu yang sengaja **tidak dikoreksi atau diganti** tanpa instruksi.
