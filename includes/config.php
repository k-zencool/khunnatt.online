<?php
/**
 * includes/config.php
 *
 * Single entry point for:
 *   - Environment / database constants
 *   - Secure PDO singleton factory
 *   - Session bootstrap & language resolution
 *
 * Load order: every public PHP file must require_once this first.
 */

declare(strict_types=1);

// ── Error reporting (flip to 0 on production) ─────────────────────────────────
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ── Database — reads Docker env vars; falls back to shared-host values ─────────
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'khunnatt_db');
define('DB_USER',    getenv('DB_USER')    ?: 'khunnatt_user');
define('DB_PASS',    getenv('DB_PASS')    ?: 'khunnatt_pass');
define('DB_CHARSET', 'utf8mb4');

// ── Site identity ──────────────────────────────────────────────────────────────
define('SITE_URL',  'https://khunnatt.online');
define('SITE_NAME', 'KHUN NATT');

// ── Supported languages & resolution priority: URL → session → default ─────────
define('SUPPORTED_LANGS', ['th', 'en']);
define('DEFAULT_LANG',    'th');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => true,        // HTTPS only (Nginx handles TLS in prod)
        'httponly' => true,        // no JS access to session cookie
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Allow ?lang=en or ?lang=th to switch and persist for the session
if (
    isset($_GET['lang']) &&
    in_array($_GET['lang'], SUPPORTED_LANGS, true)
) {
    $_SESSION['lang'] = $_GET['lang'];
}

define('LANG', $_SESSION['lang'] ?? DEFAULT_LANG);

// ── PDO singleton factory ──────────────────────────────────────────────────────
/**
 * Returns a shared PDO instance (created once per request lifecycle).
 *
 * Uses real prepared statements (ATTR_EMULATE_PREPARES = false) to prevent
 * second-order SQL injection and ensure the DB driver handles type binding.
 *
 * @throws never — connection failures are logged and result in a 503 response.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw on error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // array rows by default
        PDO::ATTR_EMULATE_PREPARES   => false,                     // real prepared stmts
        PDO::MYSQL_ATTR_FOUND_ROWS   => true,                      // rowCount() on UPDATE
        PDO::ATTR_PERSISTENT         => false,                     // no persistent conns
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('[DB] Connection failed: ' . $e->getMessage());
        http_response_code(503);
        // Show a generic error — never leak DSN / credentials to the browser
        exit('<h1>Service temporarily unavailable. Please try again later.</h1>');
    }

    return $pdo;
}
