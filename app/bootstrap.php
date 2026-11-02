<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = getenv('BKK_DB_HOST') ?: '127.0.0.1';
        $name = getenv('BKK_DB_NAME') ?: 'db_surat_bkk';
        $user = getenv('BKK_DB_USER') ?: 'root';
        $pass = getenv('BKK_DB_PASS') ?: '';
        $pdo = new PDO(
            "mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $pdo;
}

// Perbaikan Tahap 2: dapat menerima int dari PDO tanpa TypeError di halaman Edit.
function h(mixed $value): string {
    if ($value === null) return '';
    if (!is_scalar($value)) return '';
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function text_length(string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}
function route_url(string $page, array $params = []): string {
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}
function go(string $page, array $params = []): never {
    header('Location: ' . route_url($page, $params), true, 303);
    exit;
}
function redirect(string $page): never { go($page); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_validate(): void {
    if (!hash_equals(csrf(), (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('Token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = [$type, $message];
}
function auth_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    $stmt = db()->prepare('SELECT id, nama, username, role, active FROM users WHERE id=?');
    $stmt->execute([$_SESSION['uid']]);
    $u = $stmt->fetch();
    if (!$u || !$u['active']) { unset($_SESSION['uid']); return null; }
    return $u;
}
function require_login(): void { if (!auth_user()) go('login'); }
function current_user(): array { return auth_user() ?? (go('login')); }
function require_admin(): void {
    if (current_user()['role'] !== 'admin') {
        http_response_code(403); exit('Akses admin diperlukan.');
    }
}
function audit_log(string $action, string $description = '', ?int $penerbitanId = null): void {
    $uid = (int)($_SESSION['uid'] ?? 0);
    $stmt = db()->prepare('INSERT INTO log_aktivitas (user_id,penerbitan_id,aksi,keterangan) VALUES (?,?,?,?)');
    $stmt->execute([$uid ?: null, $penerbitanId, $action, $description]);
}
