<?php
/**
 * db_connect.php
 * Supabase / PostgreSQL PDO connection helper.
 * Reads credentials from environment variables (set in Render dashboard)
 * or from a local .env file for development.
 */

// ── Load .env file for local development ────────────────────────────────────
$_envFile = dirname(__DIR__) . '/.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (empty($_line) || $_line[0] === '#' || strpos($_line, '=') === false) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $k = trim($_k); $v = trim($_v);
        putenv("$k=$v");
        $_ENV[$k] = $v;
    }
}
unset($_envFile, $_line, $_k, $_v, $k, $v);

// ── CORS + JSON headers ──────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
$origin = getenv('FRONTEND_URL') ?: '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Secure session configuration ─────────────────────────────────────────────
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
         || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => 86400 * 7,   // 7 days
    'path'     => '/',
    'secure'   => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Database connection ───────────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '5432';
    $db   = getenv('DB_NAME') ?: 'postgres';
    $user = getenv('DB_USER') ?: 'postgres';
    $pass = getenv('DB_PASS') ?: '';

    $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Must be TRUE for PgBouncer transaction-mode pooler (port 6543)
            // which doesn't support server-side prepared statements.
            PDO::ATTR_EMULATE_PREPARES   => true,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    return $pdo;
}

// ── JSON response helpers ─────────────────────────────────────────────────────
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function jsonError(string $message, int $status = 400): void {
    jsonResponse(['success' => false, 'error' => $message], $status);
}
