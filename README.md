# Surat BKK — Versi 5.0: Word Gabungan Presisi Visual

## Mengapa Word gabungan sekarang rapi

Pada versi lama, tombol Word gabungan memuat satu master DOCX yang menyatukan *style*, tabel, header dan section dari sumber berbeda. Ini membuat surat bergeser. Versi baru **tidak lagi menggunakan master DOCX gabungan**.

- Pengisian ST/SPPD/BA tetap menggunakan 3 template asli (dengan placeholder).
- Word individual per template tetap bisa diunduh sebagai ZIP dan **teksnya bisa diedit**.
- PDF gabungan tetap dibuat dengan alur per-template yang sudah rapi.
- **Word gabungan baru** dibuat dengan cara memasukkan gambar setiap halaman dari **PDF final** ke satu dokumen Word, menggunakan ukuran halaman masing-masing (Legal/A4) dan margin nol. Kop, tabel, QR, tanda tangan, dan semua teks tampil dengan posisi yang mengikuti PDF.

**Batas transparan:** Word gabungan presisi ini berisi gambar halaman dan **tidak bisa diedit per kata atau paragraf**. Hal ini sengaja dipilih agar susunan satu dokumen Word tidak berubah ketika Microsoft Word membuka sumber yang memiliki style berbeda. Jika perlu mengubah teks, gunakan Word ZIP per-template, ubah draft di website, lalu terbitkan ulang. Sebuah DOCX gabungan yang 100% identik dan sekaligus seluruh teksnya editable belum dapat dijamin dari format-template yang berbeda.

## Pembaruan sekali pakai di XAMPP (tanpa kehilangan akun atau data)

1. **Backup** folder `C:\xampp\htdocs\surat-bkk` dan export database MySQL dari phpMyAdmin.
2. Ekstrak ZIP pembaruan, salin isi folder `surat-bkk` ke direktori instalasi lama (timpa file yang sama). Jangan hapus folder `storage`, `vendor`, atau file tambahan milikmu.
3. **Jangan impor ulang SQL**; database, akun admin dan `timker1`–`timker5` tetap digunakan.
4. Pasang renderer halaman PDF menjadi PNG (sekali): klik dua kali `bin\pasang-renderer-word.bat`. Script PowerShell yang disertakan mengunduh **Poppler untuk Windows** dari rilis resmi proyek distribusi https://github.com/oschwartz10612/poppler-windows/releases. Koneksi internet diperlukan sekali. Installer ini tidak memodifikasi Office, LibreOffice, atau database.
5. Pastikan LibreOffice dan paket FPDI/FPDF Composer yang sebelumnya sudah digunakan tetap tersedia. Jalankan `C:\xampp\php\php.exe bin\doctor.php` untuk memeriksa.
6. Restart Apache, refresh browser (Ctrl+F5), buka Detail Draft.

## Menerbitkan dokumen baru

- Pilih **Buat / Perbarui Preview Word + PDF**; generator akan membuat PDF presisi, ZIP Word asli, dan satu Word gabungan presisi visual.
- Gunakan tombol **Unduh Word Gabungan (Presisi PDF)** untuk satu `.docx` yang menampilkan halaman seperti PDF.
- Tombol **Unduh Word Sesuai Template (ZIP)** tetap tersedia sebagai file Word editable.

## Memperbaiki Word untuk surat yang sudah terbit

Buka Detail Draft/Surat yang sudah diterbitkan, klik **Perbarui Word Gabungan Presisi dari PDF**. Ini **hanya membuat ulang arsip DOCX** dari PDF yang sudah disimpan. PDF terbitan dan ZIP Word original tetap sama, nomor surat/tanggal dan database transaksi tidak diubah. Setelah berhasil, klik **Unduh Word Gabungan (Presisi PDF)**. Tidak perlu mengedit atau menerbitkan surat ulang.

## Jika renderer gagal

- Buka CMD: `cd /d C:\xampp\htdocs\surat-bkk`
- Jalankan: `C:\xampp\php\php.exe bin\doctor.php`
- Jika tertulis `Renderer Word presisi: BELUM ADA`, jalankan `bin\pasang-renderer-word.bat`.
- Jika perusahaan membatasi GitHub, admin dapat memasang sendiri Poppler kemudian mengarahkan variabel lingkungan `BKK_PDFTOPPM` ke lokasi `pdftoppm.exe`; restart Apache.
- Jika halaman Word berubah: lakukan pengujian dengan **Word Desktop** (bukan preview browser); PDF sumber tetap acuan posisi. Kualitas gambar 200 dpi, sehingga ukuran DOCX mungkin lebih besar daripada sebelumnya.

## Pengujian lingkungan pengembangan

- DOCX contoh 7 halaman dengan campuran 1 Legal + 6 A4 dirender menjadi 7 halaman oleh LibreOffice 25.2.
- Semua ukuran halaman terdeteksi identik (dalam pembulatan twip), dan batas objek visual berbeda maksimal sekitar 1 piksel pada render 72 dpi.
- PHP syntax lulus. **Pengujian langsung pada XAMPP/Windows serta script download Poppler masih perlu dilakukan pada komputermu.**
- Generator Word presisi membuat dokumen gambar halaman; bukan penggabungan teks DOCX editable.

---

# SURAT BKK — REVISI PRESISI DOKUMEN BERDASARKAN DOCX ASLI

## Perubahan inti

Generator sebelumnya membuat PDF dari **satu DOCX gabungan**. Penyatuan Word menyebabkan style tabel, indents, header, dan page setup dari sumber bercampur; contohnya baris Surat Pernyataan menjadi sempit dan kalimat pecah. Versi ini **tidak menggunakan DOCX gabungan untuk PDF**.

**Alur PDF baru:** salin dokumen sumber per jenis (ST, SPPD, BA), ganti hanya teks placeholder yang semula berwarna kuning (dan tanggal Surat Pernyataan yang disetujui operator), konversi masing-masing DOCX ke PDF menggunakan LibreOffice, kemudian gabungkan PDF per halaman dengan FPDI. Akibatnya setiap dokumen membawa sendiri ukuran kertas, margin, layout, kop, tabel, QR, dan tanda tangan yang berasal dari template aslinya.

**Perbedaan output Word:**

- Tombol **Unduh Word Sesuai Template (ZIP)** berisi satu file DOCX Surat Tugas, satu DOCX SPPD, dan satu/two DOCX BA + Surat Pernyataan, masing-masing langsung turunan file sumber yang dapat diedit Microsoft Word. **Ini adalah rekomendasi untuk kebutuhan pengeditan yang menjaga format sumber.**
- Tombol **Unduh Word Gabungan (Cek Layout)** masih menghasilkan satu DOCX gabungan dengan section Legal/A4, namun **penggabungan DOCX dapat mengubah pemisahan halaman dan style**. **Jangan klaim Word gabungan persis 100% sama**. Periksa sebelum dipakai. Kebutuhan satu DOCX gabungan yang identik secara visual dengan dokumen terpisah belum sepenuhnya teratasi.
- Tombol **Unduh PDF Gabungan** kini menggunakan file PDF terpisah yang digabung di tingkat PDF, sehingga tidak tercampur style antarbagian. Ini menjadi hasil utama untuk mencetak/menyimpan arsip. **Jaminan 100% pixel-perfect terhadap Microsoft Word tetap tidak mungkin** saat dokumen dirender lewat LibreOffice atau panjang isian berbeda.

## Penggunaan XAMPP — upgrade tanpa kehilangan data

1. **Backup** `C:\xampp\htdocs\surat-bkk` dan ekspor database melalui phpMyAdmin.
2. Ekstrak paket ZIP; salin **isi** folder `surat-bkk` lalu timpa kode di `C:\xampp\htdocs\surat-bkk`.
3. **Jangan hapus database. Jangan import ulang `schema.sql`.** Login menggunakan akun admin lama.
4. Pastikan LibreOffice tetap terpasang di `C:\Program Files\LibreOffice\program\soffice.exe`.
5. Agar dapat menggabungkan PDF, instal **Composer** satu kali jika belum ada dari https://getcomposer.org/download/ (pilih installer Windows, arahkan ke `C:\xampp\php\php.exe`).
6. Buka CMD di `C:\xampp\htdocs\surat-bkk`, lalu jalankan:

```bat
composer install --no-dev --prefer-dist
C:\xampp\php\php.exe bin\doctor.php
```

Alternatif untuk langkah Composer: jalankan `bin\pasang-dependensi.bat` setelah Composer terpasang. Perintah tersebut memasang `setasign/fpdf` dan `setasign/fpdi`. **Koneksi internet diperlukan sekali untuk memasang dependensi**. Di Linux, `pdfunite` yang terinstal juga dapat dipakai. Jika sudah ada Poppler untuk Windows, dapat menunjuk `BKK_PDFUNITE` ke file exe `pdfunite.exe`.

7. Restart Apache pada XAMPP; buka **Penerbitan → Detail Draft → Buat/Perbarui Preview Word + PDF**.
8. Utamakan **Lihat Preview PDF**, periksa urutan seluruh halaman. Pilih **Unduh Word Sesuai Template (ZIP)** untuk mengecek dokumen Word sumber secara terpisah.
9. Bila sumber sudah benar dan telah disetujui, klik **Konfirmasi & Terbitkan Paket**. Dokumen sudah diterbitkan tidak otomatis berubah; revisi formal sesuai prosedur instansi.

## Aturan data tidak berubah

- Pegawai 1 dan 2 **selalu input manual**; tidak ada tabel master pegawai.
- Nomor Surat Tugas: prefix tetap `SR.04.02/C.X.11/`, operator mengisi nomor dan tahun.
- Nomor SPPD: prefix tetap `KU.01.02/C.X.11/`, operator mengisi nomor dan tahun.
- Tanggal pada Surat Pernyataan berasal dari input `tanggal_pernyataan`.
- Jika ada dua kapal, maka ada BA dan Surat Pernyataan masing-masing kapal.
- Kalimat, ejaan, tanda baca **termasuk typo** dan teks yang tidak kuning tetap mengikuti **file original yang diberikan operator**. Nama pejabat, tempat, tautan, pernyataan, pilihan pemeriksaan dan QR yang tidak kuning **tidak disubstitusi**. Tidak ada tanda tangan elektronik otomatis.
- Pada ST asli, kedua baris kapal tetap tercantum; ketika pengguna hanya memilih 1 kapal, placeholder kapal 2 berisi `-` tanpa menghapus redaksi sumber.

## Ukuran halaman hasil uji (LibreOffice pada Linux)

- Surat Tugas sumber: **Legal 612×1008 pt**, 1 halaman.
- SPPD sumber: **A4 595×842 pt**, hasil konversi LibreOffice **4 halaman** (sumber terlihat 3 halaman pada parser yang berbeda; Word Windows bisa berbeda).
- BA dan Surat Pernyataan sumber: **A4 595×842 pt**, 2 halaman per kapal.
- PDF gabungan: **7 halaman contoh 1 kapal** atau **9 halaman contoh 2 kapal**, sesuai konversi LibreOffice pada pengujian ini. Ini bukan jumlah halaman tetap.
- Pencocokan ukuran kertas sudah lolos, dan halaman Surat Pernyataan hasil PDF per template telah diperiksa visual. Ini **bukan klaim posisi 100% identik pada semua komputer**.

## Struktur file utama

```text
surat-bkk/
├── app/document_service.php                # Pengisian template, konversi dan PDF merge
├── app/penerbitan_view.php                  # Tombol Word ZIP serta preview PDF
├── resources/templates/
│   ├── ST_2_PETUGAS.docx                  # Salinan original (tidak diubah)
│   ├── SPPD_FIKS.docx                    # Salinan original (tidak diubah)
│   ├── BA_FIKS.docx                      # Salinan original (tidak diubah)
│   ├── precision/
│   │   ├── st_original.docx              # Original + placeholder ST
│   │   ├── sppd_asli.docx                # Original + placeholder di warna kuning
│   │   ├── ba_kapal_1.docx               # Original BA, isian kapal 1
│   │   └── ba_kapal_2.docx               # Original BA, isian kapal 2
│   └── generated/                         # Master Word gabungan (best effort)
├── bin/doctor.php
├── bin/pasang-dependensi.bat
├── composer.json                          # FPDI, FPDF
└── storage/generated/                     # Arsip terlindungi
```

## Pemeriksaan kesetiaan sumber

Laporan di `AUDIT_TEMPLATE.md` menjelaskan pengujian. Ketiga template sumber **tidak diedit**. Template `precision` diturunkan dari dokumen original dan perubahan teks diberikan **hanya** untuk run kuning/placeholder serta tanggal Surat Pernyataan yang diminta. Ukuran halaman/margin/elemen grafis sumber tidak ditulis ulang. Konversi LibreOffice tidak menghapus atau mengesahkan tanda tangan / QR.

Tidak ada migrasi database untuk pembaruan ini. Jangan membuat akun ulang. Untuk server publik, perlu hardening HTTPS, akses arsip, backup dan TTE sesuai kebijakan instansi.

---

# PEMBARUAN TAMPILAN v4 — LANDING PAGE & MODE GELAP

Pembaruan ini **hanya menyentuh tampilan dan navigasi pengguna**. Logika database, generator dokumen, template DOCX, alur preview, dan arsip **tidak diubah** dari revisi layout PDF presisi.

## Fitur baru

- **Landing page publik** sebelum masuk: penjelasan aplikasi, fitur, alur 4 langkah, ketentuan verifikasi, dan tombol menuju login.
- **Login baru** dengan tampilan dua panel, visual dokumen, kembali ke landing, dan tombol tampil/sembunyikan kata sandi.
- **Dashboard baru** dengan banner, ikon, statistik, lima penerbitan terakhir, petunjuk 4 langkah, dan akses cepat membuat draft.
- **Mode gelap/terang** pada landing, login, dan aplikasi. Pilihan disimpan di `localStorage` browser; tidak mempengaruhi isi Word atau PDF.
- **Tampilan responsif** untuk komputer, tablet, dan HP; menu sidebar berbentuk panel buka/tutup pada layar kecil.
- **Ilustrasi vektor lokal (SVG)**, tidak membutuhkan internet/CDN, serta tidak mengandung logo resmi yang direproduksi dari template.
- Seluruh halaman lama seperti **Master Kapal, Master Keagenan, Master Pejabat, dan Form Penerbitan** tetap memakai fungsi yang sama tetapi memperoleh gaya visual baru.

## Instalasi tanpa menghapus data

1. Backup `C:\xampp\htdocs\surat-bkk` dan ekspor database MySQL melalui phpMyAdmin.
2. Ekstrak ZIP. Salin **isi folder `surat-bkk`** dan timpa file yang sama di `C:\xampp\htdocs\surat-bkk`.
3. **Jangan hapus database**, jangan impor ulang `database/schema.sql`, dan jangan hapus akun `admin`.
4. Tidak ada dependensi baru. LibreOffice, FPDI, dan Composer tetap mengikuti konfigurasi generator sebelumnya.
5. Restart Apache di XAMPP, kemudian tekan **Ctrl + F5** pada browser untuk menyegarkan cache CSS/JS.
6. Buka `http://localhost/surat-bkk/public/` untuk melihat landing page. Klik **Masuk Aplikasi**, lalu gunakan akun dan password lama.
7. Uji penggantian mode gelap, navigasi menu, buat/edit draft, preview PDF, dan proses penerbitan dengan draft contoh.

**Catatan:** Pengguna lama yang langsung membuka `index.php?page=login` tetap dapat login tanpa melalui landing. Link `index.php?page=dashboard` tetap meminta autentikasi. Saat logout pengguna kembali ke landing.

## Berkas tampilan yang ditambahkan/diubah

```
app/ui.php                              # Komponen halaman publik, login, icon
public/index.php                       # Router + dashboard UI, fungsi penerbitan tetap
public/assets/style.css                # Design system terang/gelap dan responsive
public/assets/ui.js                    # Toggle tema, menu mobile, password visibility
public/assets/img/favicon.svg         # Identitas grafis aplikasi
public/assets/img/hero-document-flow.svg
public/assets/img/login-workflow.svg
public/assets/img/dashboard-docs.svg
```

## Hasil pengujian di lingkungan pengembangan

- PHP lint: seluruh 13 berkas PHP **PASS**.
- Unit test validasi tanggal, jumlah kapal, NIP, dan nomor surat **PASS**.
- Landing dan login HTTP **200**; halaman dashboard tanpa login **303 → login**.
- Tampilan landing/login diperiksa pada desktop dan HP tanpa horizontal overflow.
- Toggle mode gelap dan tombol tampilkan kata sandi diperiksa dengan browser headless.
- **Belum diuji** secara langsung terhadap MySQL/Apache XAMPP pada Windows milik pengguna. Ekstensi PHP untuk koneksi MySQL tidak tersedia pada lingkungan pengembangan ini.

Penting: Tampilan tidak mengubah hasil layout Word/PDF. Word gabungan tetap berpotensi memiliki perbedaan layout sesuai catatan di bagian sebelumnya.
