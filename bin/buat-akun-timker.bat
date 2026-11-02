@echo off
setlocal
cd /d "%~dp0\.."
set "PHP_EXE=C:\xampp\php\php.exe"
if not exist "%PHP_EXE%" (
  echo PHP XAMPP tidak ditemukan di %PHP_EXE%
  echo Edit lokasi PHP_EXE pada file bat bila XAMPP berada di folder lain.
  pause
  exit /b 1
)
"%PHP_EXE%" "bin\create-timker.php"
echo.
pause
