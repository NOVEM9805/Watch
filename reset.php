<?php
// Set a new password after a verified reset code.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Require a recent verified reset session.
$allowed=$_SESSION['reset_allowed']??null;
$uid=(int)($allowed['id']??0);
if(!$uid||($allowed['expires']??0)<time()){
    unset($_SESSION['reset_allowed']);
    go(($_SESSION['auth_audience']??'')==='admin'?'auth/admin-forgot.php':'auth/forgot.php');
}
$resetRole=(string)query('SELECT role FROM users WHERE id=?',[$uid])->fetchColumn();
$isAdmin=$resetRole==='admin';
$error='';
// Check the submitted new password.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $pass=(string)($_POST['password']??'');
    if(strlen($pass)<12||strlen($pass)>128||$pass!==($_POST['confirm']??''))$error='Password must be 12–128 characters and both passwords must match.';
    else{
        // Change the password and invalidate old sessions.
        query('UPDATE users SET password_hash=?,session_version=session_version+1 WHERE id=?',[password_hash($pass,PASSWORD_DEFAULT),$uid]);
        unset($_SESSION['reset_allowed'],$_SESSION['uid']);
        session_regenerate_id(true);
        flash('Password updated. Log in with your new password.');
        go($isAdmin?'auth/admin-login.php':'auth/login.php');
    }
}
page_start(tr('reset'),$isAdmin);
?><main class="<?=$isAdmin?'admin-auth':'container'?>"><form class="<?=$isAdmin?'admin-auth-card':'form-panel'?>" method="post" autocomplete="off"><h1><?=h(tr('reset'))?></h1><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label><?=h(tr('password'))?></label><input type="password" name="password" autocomplete="off" minlength="12" required></div><div class="field"><label><?=h(tr('confirm_password'))?></label><input type="password" name="confirm" autocomplete="off" minlength="12" required></div><button class="btn btn-gold w-100"><?=h(tr('save'))?></button></form></main><?php
page_end();
