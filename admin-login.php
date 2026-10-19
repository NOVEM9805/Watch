<?php
// Handle the separate administrator sign-in flow.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
$sessionUser=current_user();
// Redirect users who already have a session.
if($sessionUser)go($sessionUser['role']==='admin'?'admin/dashboard.php':'customer/profile.php');
$error='';
// Check submitted administrator credentials.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $email=strtolower(trim((string)($_POST['email']??'')));
    $pass=(string)($_POST['password']??'');
    if(throttled('admin_login',$email))$error='Too many attempts. Try again in 15 minutes.';
    else {
        $u=query("SELECT * FROM users WHERE email=? AND role='admin'",[$email])->fetch();
        if(!$u||!password_verify($pass,$u['password_hash'])||!$u['verified_at']){
            record_attempt('admin_login',$email);
            $error='Invalid admin login details or account not yet verified.';
        }
        else{
            try{
                // Require an email code before completing sign-in.
                send_otp((int)$u['id'],$u['email'],'login');
                clear_attempts('admin_login',$email);
                $_SESSION['pending']=['id'=>(int)$u['id'],'purpose'=>'login'];
                $_SESSION['auth_audience']='admin';
                $_SESSION['next']='/admin/dashboard.php';
                go('auth/verify.php');
            }
            catch(Throwable $e){
                $error=mail_error_message($e);
            }
        }
    }
}
page_start('Admin sign in',true);
?><main class="admin-auth"><form class="admin-auth-card" method="post" autocomplete="off"><span class="eyebrow">ADMINISTRATOR ACCESS</span><h1>Welcome back.</h1><p>Sign in with your administrator Gmail and password. A verification code follows.</p><?php
if($error):
?><div class="flash error" role="alert"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="email">Admin Gmail</label><input id="email" type="email" name="email" required autocomplete="off" value="<?=h($_POST['email']??'')?>"></div><div class="field"><label for="pass">Password</label><input id="pass" type="password" name="password" required autocomplete="off"></div><button class="btn btn-gold w-100">Continue to verification</button><div class="auth-options"><a class="text-link" href="<?=url('auth/admin-forgot.php')?>">Forgot password?</a><a class="text-link" href="<?=url('auth/admin-activate.php')?>">Activate a new admin account</a></div><?php
if(!(int)query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()):
?><p><a class="text-link" href="<?=url('auth/admin-setup.php')?>">Create the first administrator</a></p><?php
endif
?></form></main><?php
page_end();
