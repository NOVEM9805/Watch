<?php
// Send password reset codes to verified administrators.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
$error='';
// Validate a reset request and apply rate limits.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $email=strtolower(trim((string)($_POST['email']??'')));
    if(throttled('admin_forgot',$email,4))$error='Too many requests. Try later.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Enter a valid Gmail address.';
    else{
        record_attempt('admin_forgot',$email);
        // Look up only verified administrator accounts.
        $u=query("SELECT id,email FROM users WHERE email=? AND role='admin' AND verified_at IS NOT NULL",[$email])->fetch();
        if($u){
            try{
                // Email a reset code and save pending verification.
                send_otp((int)$u['id'],$u['email'],'reset');
                $_SESSION['pending']=['id'=>(int)$u['id'],'purpose'=>'reset'];
                $_SESSION['auth_audience']='admin';
                go('auth/verify.php');
            }
            catch(Throwable $e){
                $error=mail_error_message($e);
            }
        }
        else{
            flash('If an admin account exists, a code will be sent.');
            go('auth/admin-login.php');
        }
    }
}
page_start('Admin password reset',true);
?><main class="admin-auth"><form class="admin-auth-card" method="post" autocomplete="off"><h1>Reset password</h1><p>We will send a verification code to your administrator Gmail.</p><?php
if($error):
?><div class="flash error" role="alert"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="email">Admin Gmail</label><input id="email" type="email" name="email" required autocomplete="off"></div><button class="btn btn-gold w-100">Send code</button><p><a class="text-link" href="<?=url('auth/admin-login.php')?>">Back to admin sign in</a></p></form></main><?php
page_end();
