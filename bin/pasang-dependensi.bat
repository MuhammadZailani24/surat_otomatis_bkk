@echo off
cd /d "%~dp0.."
where composer >NUL 2>NUL
if errorlevel 1 (
  echo Composer belum terpasang. Silakan pasang dari https://getcomposer.org/download/
  pause
  exit /b 1
)
composer install --no-dev --prefer-dist
if errorlevel 1 (
  echo Gagal memasang paket FPDI/FPDF. Periksa ekstensi PHP dan jaringan Internet.
  pause
  exit /b 1
)
echo Paket penggabung PDF sudah dipasang.
pause
