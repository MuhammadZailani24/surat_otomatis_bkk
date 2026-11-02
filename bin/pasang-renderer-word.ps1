# Installer opsional Poppler dari rilis GitHub (bukan bagian dari paket aplikasi).
# Digunakan hanya untuk merender PDF menjadi halaman PNG; tidak mengubah template.
$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'
$root = Split-Path -Parent $PSScriptRoot
$target = Join-Path $root 'tools\poppler'
if (Test-Path $target) {
    $exists = Get-ChildItem -Path $target -Filter 'pdftoppm.exe' -File -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($exists) { Write-Host "Renderer sudah ada: $($exists.FullName)"; exit 0 }
}
Write-Host 'Mengunduh Poppler untuk Windows dari rilis GitHub publik ...'
Write-Host 'Sumber: https://github.com/oschwartz10612/poppler-windows/releases'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$api='https://api.github.com/repos/oschwartz10612/poppler-windows/releases/latest'
$release = Invoke-RestMethod -Uri $api -Headers @{'User-Agent'='SuratBKK-RendererInstaller';'Accept'='application/vnd.github+json'} -TimeoutSec 90
$asset = $release.assets | Where-Object { $_.name -match '\.zip$' } | Select-Object -First 1
if (-not $asset -or -not $asset.browser_download_url) { throw 'Asset ZIP Poppler tidak ditemukan pada rilis tersebut.' }
Write-Host "Rilis: $($release.tag_name) / $($asset.name)"
$stage = Join-Path $env:TEMP ('bkk-poppler-'+[Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Force -Path $stage | Out-Null
try {
    $zip = Join-Path $stage 'poppler.zip'
    Invoke-WebRequest -Uri $asset.browser_download_url -OutFile $zip -TimeoutSec 240 -UseBasicParsing
    if ((Get-Item $zip).Length -lt 1000000) { throw 'Hasil download Poppler terlalu kecil/tidak valid.' }
    New-Item -ItemType Directory -Force -Path $target | Out-Null
    Expand-Archive -Path $zip -DestinationPath $target -Force
    $exe = Get-ChildItem -Path $target -Filter 'pdftoppm.exe' -File -Recurse | Select-Object -First 1
    if (-not $exe) { throw 'File pdftoppm.exe tidak ditemukan sesudah ekstraksi.' }
    Write-Host "SUKSES: $($exe.FullName)"
    Write-Host 'LibreOffice, Word, database dan akun Anda tidak diubah.'
    Write-Host 'Kini jalankan lagi tombol Buat Preview atau Perbarui Word dari PDF.'
} finally {
    Remove-Item -LiteralPath $stage -Force -Recurse -ErrorAction SilentlyContinue
}
