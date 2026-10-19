<?php
// Load site settings, sessions and the database connection.
declare(strict_types=1);
$root = dirname(__DIR__);
// Read local configuration from the optional .env file.
$vars = is_file($root.'/.env') ? parse_ini_file($root.'/.env', false, INI_SCANNER_RAW) : [];
if ($vars === false) {
    throw new RuntimeException('Invalid .env file');
}
// Read one configuration value with a fallback.
function cfg(string $key, string $default = ''): string {
    global $vars;
    return (string)($vars[$key] ?? $default);
}
$base = rtrim(parse_url(cfg('APP_URL','http://localhost/timeless-sight'), PHP_URL_PATH) ?: '', '/');
// Build a URL inside this installation.
function url(string $path=''): string {
    global $base;
    return $base.'/'.ltrim($path,'/');
}
// Redirect to a local page and stop execution.
function go(string $path): never {
    header('Location: '.url($path), true, 303);
    exit;
}
// Start a scoped, HTTP-only browser session.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('timeless_session');
    session_set_cookie_params(['lifetime'=>0,'path'=>$base ?: '/', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
try {
    // Create the shared database connection.
    $db = new PDO('mysql:host='.cfg('DB_HOST','127.0.0.1').';port='.cfg('DB_PORT','3306').';dbname='.cfg('DB_NAME','timeless_sight').';charset=utf8mb4',cfg('DB_USER','root'),cfg('DB_PASS'),[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false
    ]);
}
catch (PDOException $e) {
    http_response_code(503);
    exit('Database unavailable. Check INSTALL_MM.md and .env.');
}
require_once $root.'/includes/functions.php';
// Route guard applies before storefront POST handlers as well as page rendering.
$script=realpath((string)($_SERVER['SCRIPT_FILENAME']??''));
$publicStore=$script && ($script===realpath($root.'/index.php') || str_starts_with($script,$root.'/customer/') || $script===realpath($root.'/api/search.php'));
if($publicStore){
    $sessionUser=current_user();
    if($sessionUser && $sessionUser['role']==='admin')go('admin/dashboard.php');
}
