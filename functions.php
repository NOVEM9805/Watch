<?php
// Shared helpers for accounts, email, catalog, and shopping.
declare(strict_types=1);
// Escape text for safe HTML output.
function h(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}
// Run a prepared database statement.
function query(string $sql,array $params=[]): PDOStatement {
    global $db;
    $s=$db->prepare($sql);
    $s->execute($params);
    return $s;
}
// Return the current form protection token.
function csrf(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
// Reject a form with an invalid token.
function check_csrf(): void {
    if (!hash_equals(csrf(),(string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Invalid form token. Reload and try again.');
    }
}
// Check the form token before any handler changes account or order data.
// Require a POST request and valid form token.
function post_only(): void {
    if ($_SERVER['REQUEST_METHOD']!=='POST') {
        http_response_code(405);
        exit('Method not allowed');
    }
    check_csrf();
}
// Queue a one-time status message.
function flash(string $message, string $kind='success'): void {
    $_SESSION['flash']=['message'=>$message,'kind'=>$kind];
}
// Read and clear a queued status message.
function take_flash(): ?array {
    $x=$_SESSION['flash']??null;
    unset($_SESSION['flash']);
    return $x;
}
// Load the active verified account from the session.
function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    $u=query('SELECT id,name,email,role,verified_at,session_version FROM users WHERE id=?',[(int)$_SESSION['uid']])->fetch();
    if ($u && $u['verified_at'] && (int)($_SESSION['session_version']??0)===(int)$u['session_version']) return $u;
    unset($_SESSION['uid'],$_SESSION['session_version']);
    return null;
}
// Accept redirects only inside this installation's URL path.
// Limit redirects to local site paths.
function safe_next(?string $next): string {
    global $base;
    if (!$next || $next[0]!=='/' || str_starts_with($next,'//') || str_contains($next,'\\') || preg_match('/[\r\n]/',$next)) return '/';
    if ($base && ($next===$base || str_starts_with($next,$base.'/'))) $next=substr($next,strlen($base)) ?: '/';
    return $next;
}
// Require a customer session before continuing.
function require_login(): array {
    $u=current_user();
    if($u && $u['role']==='admin')go('admin/dashboard.php');
    if (!$u) {
        $dest=safe_next(parse_url($_SERVER['REQUEST_URI']??'/', PHP_URL_PATH));
        go('auth/login.php?next='.rawurlencode($dest));
    }
    return $u;
}
// Require an administrator session.
function require_admin(): array {
    $u=current_user();
    if(!$u)go('auth/admin-login.php');
    if($u['role']!=='admin'){
        http_response_code(403);
        exit('Access denied');
    }
    return $u;
}
// Return the current interface language.
function language(): string {
    return ($_SESSION['lang']??'en')==='mm'?'mm':'en';
}
// Look up a translated interface label.
function tr(string $key): string {
    static $texts=[];
    $l=language();
    if (!isset($texts[$l])) $texts[$l]=require dirname(__DIR__).'/lang/'.$l.'.php';
    return $texts[$l][$key]??$key;
}
// Check whether the catalog accepts orders.
function store_ready(): bool {
    static $ready=null;
    if($ready===null)$ready=setting('catalog_ready')==='1';
    return $ready;
}
// Format a price in Myanmar kyat.
function money($n): string {
    return number_format((float)$n,0).' MMK';
}
// Read a saved shop setting.
function setting(string $key): string {
    $v=query('SELECT setting_value FROM settings WHERE setting_key=?',[$key])->fetchColumn();
    return $v===false?'':(string)$v;
}
// Insert or update a shop setting.
function save_setting(string $key,string $value): void {
    query('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',[$key,$value]);
}
// Send a verified user to the requested page.
function verified_redirect(): never {
    $next=safe_next($_SESSION['next']??'/');
    unset($_SESSION['next']);
    header('Location: '.url(ltrim($next,'/')),true,303);
    exit;
}
// Email a fresh time-limited verification code.
function send_otp(int $userId,string $email,string $purpose): void {
    // Use database time for both the cooldown and stored challenge timestamps.
    $seconds=query('SELECT TIMESTAMPDIFF(SECOND,created_at,NOW()) FROM otp_challenges WHERE user_id=? AND purpose=? ORDER BY id DESC LIMIT 1',[$userId,$purpose])->fetchColumn();
    if ($seconds!==false && (int)$seconds<60) throw new RuntimeException('Wait one minute before requesting another code.');
    $code=(string)random_int(100000,999999);
    // Send first; only replace a working code after mail transport succeeds.
    send_mail($email,'Timeless Sight verification code',"Your verification code is $code. It expires in 10 minutes. If you did not request it, ignore this message.");
    query('UPDATE otp_challenges SET consumed_at=NOW() WHERE user_id=? AND purpose=? AND consumed_at IS NULL',[$userId,$purpose]);
    query('INSERT INTO otp_challenges(user_id,purpose,code_hash,expires_at) VALUES(?,?,?,DATE_ADD(NOW(), INTERVAL 10 MINUTE))',[$userId,$purpose,password_hash($code,PASSWORD_DEFAULT)]);
}
// Deliver email through local logs or Gmail SMTP.
function send_mail(string $to,string $subject,string $body): void {
    if (cfg('MAIL_MODE')==='log' && cfg('APP_ENV')==='local') {
        file_put_contents(dirname(__DIR__).'/storage/logs/mail.log',date('c')." $to $subject $body\n",FILE_APPEND|LOCK_EX);
        return;
    }
    if (cfg('MAIL_MODE')!=='smtp' || !cfg('MAIL_USERNAME') || !cfg('MAIL_APP_PASSWORD') || !is_file(dirname(__DIR__).'/vendor/autoload.php')) throw new RuntimeException('Email is not configured. See INSTALL_MM.md.');
    require_once dirname(__DIR__).'/vendor/autoload.php';
    $m=new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host=cfg('MAIL_HOST','smtp.gmail.com');
        $m->Port=(int)cfg('MAIL_PORT','587');
        $m->SMTPAuth=true;
        $m->Username=cfg('MAIL_USERNAME');
        $m->Password=cfg('MAIL_APP_PASSWORD');
        $m->SMTPSecure=PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $m->setFrom(cfg('MAIL_FROM_ADDRESS') ?: cfg('MAIL_USERNAME'),cfg('MAIL_FROM_NAME','Timeless Sight'));
        $m->addAddress($to);
        $m->Subject=$subject;
        $m->Body=$body;
        $m->send();
    }
    catch (Throwable $e) {
        error_log('Mail delivery failed: '.$e->getMessage());
        throw new RuntimeException('Email could not be sent. Try again later.');
    }
}
// Translate mail failures into helpful form errors.
function mail_error_message(Throwable $error): string {
    if($error->getMessage()==='Wait one minute before requesting another code.') return $error->getMessage();
    if(cfg('APP_ENV')==='local' && cfg('MAIL_MODE')==='log') return 'Local email log failed. Check that storage/logs is writable, then view the Apache error log.';
    if(cfg('MAIL_MODE')!=='smtp') return 'Email mode is not configured. Set MAIL_MODE=smtp in .env.';
    if(!is_file(dirname(__DIR__).'/vendor/autoload.php')) return 'PHPMailer is missing. Run composer install inside the website folder.';
    if(!cfg('MAIL_USERNAME') || !cfg('MAIL_APP_PASSWORD')) return 'Gmail settings are missing in .env. Add MAIL_USERNAME and MAIL_APP_PASSWORD.';
    return 'Verification email could not be delivered. Check the Gmail App Password and Apache error log.';
}
// Validate and consume an email code.
function verify_code(int $uid,string $purpose,string $code): bool {
    // The database checks expiry so server time zones cannot invalidate a fresh code.
    $row=query('SELECT *,expires_at<=NOW() AS expired FROM otp_challenges WHERE user_id=? AND purpose=? AND consumed_at IS NULL ORDER BY id DESC LIMIT 1',[$uid,$purpose])->fetch();
    if (!$row || (int)$row['expired'] || (int)$row['attempts']>=5) return false;
    query('UPDATE otp_challenges SET attempts=attempts+1 WHERE id=?',[$row['id']]);
    if (!preg_match('/^[0-9]{6}$/',$code) || !password_verify($code,$row['code_hash'])) return false;
    query('UPDATE otp_challenges SET consumed_at=NOW() WHERE id=?',[$row['id']]);
    return true;
}
// Build the URL for an uploaded watch photo.
function product_image(?string $image): string {
    return $image ? url(ltrim($image,'/')) : '';
}
// Validate and save an uploaded product image.
function upload_image(string $field,string $folder): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    $f=$_FILES[$field];
    if ($f['error']!==UPLOAD_ERR_OK || $f['size']>4*1024*1024 || $f['size']<1) throw new RuntimeException('Image must be under 4 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
    if (!$ext || !getimagesize($f['tmp_name'])) throw new RuntimeException('Upload a JPG, PNG or WEBP image.');
    $name=bin2hex(random_bytes(18)).'.'.$ext;
    $target=dirname(__DIR__).'/uploads/'.$folder.'/'.$name;
    if (!move_uploaded_file($f['tmp_name'],$target)) throw new RuntimeException('Image upload failed.');
    return '/uploads/'.$folder.'/'.$name;
}
// Count units currently in the session cart.
function cart_count(): int {
    return array_sum(array_map('intval',$_SESSION['cart']??[]));
}
// Load available catalog rows for cart items.
function cart_rows(): array {
    $cart=$_SESSION['cart']??[];
    if (!$cart)return [];
    $ids=array_keys($cart);
    $marks=implode(',',array_fill(0,count($ids),'?'));
    $rows=query("SELECT w.*,b.name AS brand FROM watches w JOIN brands b ON b.id=w.brand_id WHERE w.active=1 AND b.active=1 AND w.id IN ($marks)",$ids)->fetchAll();
    foreach($rows as &$r) $r['qty']=max(1,min(20,(int)$cart[$r['id']]));
    unset($r);
    return $rows;
}
// Check whether an action is rate limited.
function throttled(string $action,string $identity,int $limit=6): bool {
    $key=hash('sha256',$action.'|'.strtolower($identity).'|'.($_SERVER['REMOTE_ADDR']??'local'));
    $row=query('SELECT attempts,window_start FROM auth_throttle WHERE throttle_key=?',[$key])->fetch();
    return $row && strtotime($row['window_start'])>time()-900 && (int)$row['attempts']>=$limit;
}
// Record a sign-in or verification attempt.
function record_attempt(string $action,string $identity): void {
    $key=hash('sha256',$action.'|'.strtolower($identity).'|'.($_SERVER['REMOTE_ADDR']??'local'));
    query('INSERT INTO auth_throttle(throttle_key,attempts) VALUES(?,1) ON DUPLICATE KEY UPDATE attempts=IF(window_start<DATE_SUB(NOW(),INTERVAL 15 MINUTE),1,attempts+1),window_start=IF(window_start<DATE_SUB(NOW(),INTERVAL 15 MINUTE),NOW(),window_start)',[$key]);
}
// Clear the attempt counter after success.
function clear_attempts(string $action,string $identity): void {
    $key=hash('sha256',$action.'|'.strtolower($identity).'|'.($_SERVER['REMOTE_ADDR']??'local'));
    query('DELETE FROM auth_throttle WHERE throttle_key=?',[$key]);
}
