<?php
// Confirm email codes for registration, sign-in and reset.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Require an active verification request.
$pending=$_SESSION['pending']??null;
if(!$pending||!in_array($pending['purpose'],['register','login','reset'],true))go(($_SESSION['auth_audience']??'')==='admin'?'auth/admin-login.php':'auth/login.php');
$uid=(int)$pending['id'];
$purpose=$pending['purpose'];
$u=query('SELECT id,email,role FROM users WHERE id=?',[$uid])->fetch();
if(!$u)go(($_SESSION['auth_audience']??'')==='admin'?'auth/admin-login.php':'auth/login.php');
$isAdmin=$u['role']==='admin';
$error='';
// Handle code submission and resend requests.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    if(throttled('otp',(string)$uid,10))$error='Too many attempts. Try again later.';
    elseif(($_POST['action']??'')==='resend'){
        try{
            send_otp($uid,$u['email'],$purpose);
            flash('A new code was sent.');
            go('auth/verify.php');
        }
        catch(Throwable $e){
            $error=mail_error_message($e);
        }
    }
    else{
        record_attempt('otp',(string)$uid);
        // Check the submitted code before completing the flow.
        if(!verify_code($uid,$purpose,trim((string)($_POST['code']??''))))$error='Incorrect or expired code.';
        else{
            clear_attempts('otp',(string)$uid);
            unset($_SESSION['pending']);
            if($purpose==='reset'){
                $_SESSION['reset_allowed']=['id'=>$uid,'expires'=>time()+600];
                go('auth/reset.php');
            }
            if($purpose==='register')query('UPDATE users SET verified_at=NOW() WHERE id=?',[$uid]);
            session_regenerate_id(true);
            $_SESSION['uid']=$uid;
            $_SESSION['session_version']=(int)query('SELECT session_version FROM users WHERE id=?',[$uid])->fetchColumn();
            verified_redirect();
        }
    }
}
page_start(tr('verify'),$isAdmin);
?><main class="<?=$isAdmin?'admin-auth':'container'?>"><div class="<?=$isAdmin?'admin-auth-card':'form-panel'?>"><h1><?=h(tr('verify'))?></h1><p>We sent a six-digit code to <strong><?=h($u['email'])?></strong>. It expires in 10 minutes.</p><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="code"><?=h(tr('code'))?></label><input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code"></div><button class="btn btn-gold w-100"><?=h(tr('verify'))?></button></form><form method="post" style="margin-top:16px"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="resend"><button class="btn btn-outline" type="submit"><?=h(tr('resend'))?></button></form></div></main><?php
page_end();
