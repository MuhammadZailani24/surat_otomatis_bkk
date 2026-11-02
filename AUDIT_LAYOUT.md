# Audit hasil dokumen dibandingkan 3 template asli

## Berhasil diperbaiki pada revisi ini
- Prefix nomor Surat Tugas dan SPPD tidak dapat diedit oleh operator. User mengisi nomor urut dan tahun, database tetap menyimpan nomor penuh.
- Tanggal Surat Pernyataan pada masing-masing kapal menggunakan input `tanggal_pernyataan`.
- Naskah tetap dari file ST, SPPD, Berita Acara/Surat Pernyataan diuji dengan `tests/preserve_text.py`; area tanggal pernyataan menjadi satu-satunya pengecualian tambahan yang disetujui.
- Contoh template gabungan dengan 1 kapal -> 6 halaman, dengan 2 kapal -> 8 halaman saat diekspor memakai LibreOffice (QA Linux).

## Keterbatasan yang masih ada (WAJIB diketahui sebelum terbit)
1. Ukuran kertas sumber **tidak sama**: ST legal 8.5 x 14 inci, SPPD dan BA A4. Master Word gabungan saat ini masih mengutamakan jumlah halaman stabil, bukan kesamaan page geometry per bagian.
2. Upaya memasang section dengan ukuran kertas asli menghasilkan halaman kosong ekstra dalam pengujian; perubahan tersebut **dibatalkan**.
3. Ekspor PDF dengan LibreOffice tidak dijamin identik pixel dengan Microsoft Word. Nama panjang dan format font di Windows juga dapat memindahkan baris.
4. Seluruh teks yang tidak ditandai sudah diperiksa, tetapi **klaim 100% persis posisi belum dibuktikan**.
5. Sebelum pakai resmi: cocokkan pratinjau terhadap setiap halaman sumber, terutama kop, tabel perjalanan dinas, BA, halaman pernyataan, QR, dan tanda tangan.

## Tahapan berikutnya untuk presisi asli
Mengisi salinan ketiga DOCX original secara independen (tanpa menyatukan style/section), menghasilkan masing-masing PDF, kemudian menggabungkan PDF dengan pemroses yang mempertahankan ukuran tiap halaman. Untuk Word gabungan, perlu penggabung OOXML yang memelihara section, style, numbering, media dan hubungan gambar. Implementasi tersebut **belum ada pada rilis ini**.
