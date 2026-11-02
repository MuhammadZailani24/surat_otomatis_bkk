# Surat BKK v6 — Penyederhanaan Tampilan & Interaksi

Pembaruan ini melanjutkan **surat-bkk-word-gabungan-presisi-v5**. Tujuan utamanya adalah memperjelas tombol dan alur kerja operator serta meningkatkan kenyamanan penggunaan pada layar desktop dan HP.

## Apa yang berubah

- **Halaman Detail Penerbitan:** alur `Isi data → Preview → Terbitkan`, dengan tindakan utama yang berubah sesuai status.
- **Draft belum memiliki dokumen:** hanya tindakan utama **Buat preview Word & PDF**.
- **Draft dengan preview:** tindakan utama **Lihat preview PDF** dan **Konfirmasi & terbitkan**; tindakan unduhan sementara dan regenerasi berada dalam bagian opsional.
- **Dokumen sudah terbit:** tombol utama **Unduh PDF gabungan** dan **Unduh Word gabungan**; ZIP Word editable, preview tambahan, dan perbaikan Word dipindahkan ke opsi teknis.
- **Form penerbitan:** navigasi cepat empat bagian, teks instruksi ringkas, penanda bagian, referensi pejabat opsional dalam panel yang dapat dibuka, serta tombol Simpan Draft yang jelas.
- **Daftar penerbitan:** satu tombol **Buka** per baris; Edit berada pada detail draft untuk pengguna berhak. Search lebih jelas dengan Reset saat filter aktif.
- **Master kapal, agen, pejabat:** tombol tambah/edit dan formulir lebih mudah ditemukan.
- **Interaksi:** hover pada kartu, menu, tombol, opsi unduhan, focus indicator, animasi singkat, umpan balik ketika menyimpan/membuat preview, pesan sukses yang dapat ditutup, dan dukungan *reduced motion*.
- **Mode gelap:** tetap ada dan mempertahankan preferensi browser.

## Tidak diubah

- `app/document_service.php` dan `app/word_exact_service.php` — generator PDF, Word gabungan presisi, dan ZIP Word asli.
- Template DOCX yang telah diaudit.
- Struktur database, SQL, login, hak akses, password, akun admin/operator, data kapal/penerbitan, penomoran surat, dan arsip.
- Alamat URL serta metode POST internal untuk membuat preview, menerbitkan, dan mengunduh dokumen.

## Instalasi untuk XAMPP

1. **Backup** `C:\xampp\htdocs\surat-bkk` dan ekspor database `db_surat_bkk` terlebih dahulu.
2. Ekstrak ZIP, salin **isi folder `surat-bkk`** ke `C:\xampp\htdocs\surat-bkk`, timpa file lama.
3. Jangan impor ulang SQL. Jangan menghapus folder `storage` yang berisi arsip. Jika folder `storage` pada ZIP kosong, jangan menggantinya dengan folder kosong yang menghapus data lama.
4. Restart Apache di XAMPP; buka `http://localhost/surat-bkk/public/` dan tekan `Ctrl + F5` untuk membersihkan cache CSS/JS.
5. Login dengan akun lama, uji alur draft → preview → terbit serta download PDF/Word. Gunakan data uji/draft sebelum menguji penerbitan resmi.

Pembaruan ini **tidak memerlukan instalasi Composer, LibreOffice, maupun Poppler tambahan** selama versi 5 sudah berfungsi pada komputer yang sama.

## Pengujian

- Seluruh file PHP: lint sintaks berhasil.
- Pengujian validasi data penerbitan dan format nomor: lulus.
- Pemeriksaan layout sintetis (tanpa MySQL) pada desktop dan layar 390 px: tidak ada overflow horizontal setelah perbaikan.
- Render demo empat kondisi (form, draft belum preview, draft preview siap, terbit) diperiksa.

**Batas:** tidak ada akses ke database MySQL dan instalasi XAMPP Windows pengguna. Alur database dan generator dokumen lengkap perlu diuji di komputer tempat aplikasi berjalan.
