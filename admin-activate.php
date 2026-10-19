<?php
// Verify the first sign-in for an invited administrator.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
$sessionUser=current_user();
// Send signed-in users to their own area.
if($sessionUser)go($sessionUser['role']==='admin'?'admin/dashboard.php':'customer/profile.php');
$error='';
// Check the initial administrator credentials.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $email=strtolower(trim((string)($_POST['email']??'')));
    $pass=(string)($_POST['password']??'');
    if(throttled('admin_activation',$email,5))$error='Too many attempts. Try again later.';
    else{
        record_attempt('admin_activation',$email);
        $u=query("SELECT id,password_hash FROM users WHERE email=? AND role='admin' AND verified_at IS NULL",[$email])->fetch();
        if(!$u||!password_verify($pass,$u['password_hash']))$error='Account not found or initial password is incorrect.';
        else{
            try{
                // Start email confirmation for this administrator.
                send_otp((int)$u['id'],$email,'register');
                $_SESSION['pending']=['id'=>(int)$u['id'],'purpose'=>'register'];
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
page_start('Activate admin',true);
?><main class="admin-auth"><form class="admin-auth-card" method="post" autocomplete="off"><span class="eyebrow">ADMINISTRATOR ACCESS</span><h1>Activate your account.</h1><p>Use the Gmail and initial password provided by your administrator. Confirm the emailed code to activate access.</p><?php
if($error):
?><div class="flash error" role="alert"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="email">Admin Gmail</label><input id="email" type="email" name="email" required autocomplete="off"></div><div class="field"><label for="pass">Initial password</label><input id="pass" type="password" name="password" required autocomplete="off"></div><button class="btn btn-gold w-100">Send verification code</button><p><a class="text-link" href="<?=url('auth/admin-login.php')?>">Back to admin sign in</a></p></form></main><?php
page_end();
