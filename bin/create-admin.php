<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Hanya CLI.'); }
if ($argc !== 3) { fwrite(STDERR, "Penggunaan: php bin/create-admin.php <username> <nama>\nPassword diminta secara interaktif.\n"); exit(1); }
[$script, $username, $nama] = $argv;
if (!preg_match('/^[a-zA-Z0-9_.-]{3,80}$/', $username)) { exit("Username tidak valid.\n"); }
fwrite(STDOUT, "Masukkan password admin (min. 12 karakter): ");
$password = trim((string)fgets(STDIN));
if (strlen($password) < 12) { exit("Password terlalu singkat.\n"); }
$stmt=db()->prepare('INSERT INTO users (nama,username,password_hash,role) VALUES (?,?,?,\'admin\')');
$stmt->execute([$nama,$username,password_hash($password,PASSWORD_DEFAULT)]);
exit("Admin berhasil dibuat.\n");
