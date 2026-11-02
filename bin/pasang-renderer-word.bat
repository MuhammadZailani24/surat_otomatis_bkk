@echo off
setlocal
cd /d "%~dp0.."
echo Menyiapkan renderer Word presisi...
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0pasang-renderer-word.ps1"
if errorlevel 1 (
  echo.
  echo Pemasangan gagal. Periksa koneksi internet atau pasang Poppler manual.
  pause
  exit /b 1
)
echo.
"C:\xampp\php\php.exe" "%~dp0doctor.php"
pause
