<?php
declare(strict_types=1);

/**
 * Pembuatan lima akun operator Tim Kerja pada aplikasi Surat BKK v4.
 * Jalankan dari terminal Windows: C:\xampp\php\php.exe bin\create-timker.php
 * Password tiap akun diminta pada terminal. Tidak ada password bawaan.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya boleh dijalankan dari terminal.');
}
require_once __DIR__ . '/../app/bootstrap.php';

$accounts = [
    'timker1' => 'Tim Kerja 1',
    'timker2' => 'Tim Kerja 2',
    'timker3' => 'Tim Kerja 3',
    'timker4' => 'Tim Kerja 4',
    'timker5' => 'Tim Kerja 5',
];

function readConsole(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $line = fgets(STDIN);
    if ($line === false) {
        throw new RuntimeException('Input terminal tidak tersedia.');
    }
    return rtrim($line, "\r\n");
}

fwrite(STDOUT, "\n=== PENAMBAHAN 5 AKUN OPERATOR SURAT BKK ===\n");
fwrite(STDOUT, "Setiap password diatur sendiri (minimal 12 karakter).\n");
fwrite(STDOUT, "Akun yang sudah ada akan dilewati; password lama tidak diubah.\n");
fwrite(STDOUT, "Gunakan terminal pribadi agar password tidak terlihat orang lain.\n\n");

try {
    $pdo = db();
    $pdo->beginTransaction();
    $check = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $insert = $pdo->prepare(
        "INSERT INTO users (nama, username, password_hash, role, active) VALUES (?, ?, ?, 'operator', 1)"
    );
    $created = 0;
    $skipped = 0;

    foreach ($accounts as $username => $name) {
        $check->execute([$username]);
        if ($check->fetchColumn() !== false) {
            fwrite(STDOUT, "[LEWATI] {$username} sudah terdaftar.\n\n");
            $skipped++;
            continue;
        }

        $valid = false;
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $password = readConsole("Password {$username} (minimal 12 karakter): ");
            $confirmation = readConsole("Ulangi password {$username}: ");
            if (strlen($password) < 12) {
                fwrite(STDOUT, "[SALAH] Password minimal 12 karakter.\n");
                continue;
            }
            if (!hash_equals($password, $confirmation)) {
                fwrite(STDOUT, "[SALAH] Kedua password tidak sama.\n");
                continue;
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            unset($password, $confirmation);
            $insert->execute([$name, $username, $passwordHash]);
            unset($passwordHash);
            fwrite(STDOUT, "[OK] {$username} ditambahkan sebagai operator.\n\n");
            $created++;
            $valid = true;
            break;
        }
        if (!$valid) {
            throw new RuntimeException("Password untuk {$username} gagal divalidasi tiga kali; seluruh transaksi dibatalkan.");
        }
    }
    $pdo->commit();
    fwrite(STDOUT, "SELESAI. Akun baru: {$created}; akun dilewati: {$skipped}.\n");
    fwrite(STDOUT, "Login di http://localhost/surat-bkk/public/index.php?page=login\n");
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "GAGAL: {$error->getMessage()}\n");
    exit(1);
}
