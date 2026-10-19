<?php
// Request a customer password reset code.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
if(($signedIn=current_user()) && $signedIn['role']==='admin')go('admin/dashboard.php');
$error='';
// Validate the email and rate-limit requests.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $email=strtolower(trim((string)($_POST['email']??'')));
    if(throttled('forgot',$email,4))$error='Too many requests. Try again later.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Enter a valid email address.';
    else{
        record_attempt('forgot',$email);
        // Look up verified customer accounts.
        $u=query("SELECT id,email FROM users WHERE email=? AND role='customer' AND verified_at IS NOT NULL",[$email])->fetch();
        if($u){
            try{
                // Send the reset code when an account is found.
                send_otp((int)$u['id'],$u['email'],'reset');
                $_SESSION['pending']=['id'=>(int)$u['id'],'purpose'=>'reset'];
                $_SESSION['auth_audience']='customer';
                go('auth/verify.php');
            }
            catch(Throwable $e){
                /* Reopen an unexpired code instead of sending another email. */
                if($e->getMessage()==='Wait one minute before requesting another code.' && query("SELECT 1 FROM otp_challenges WHERE user_id=? AND purpose='reset' AND consumed_at IS NULL AND expires_at>NOW() AND attempts<5 ORDER BY id DESC LIMIT 1",[(int)$u['id']])->fetchColumn()){
                    $_SESSION['pending']=['id'=>(int)$u['id'],'purpose'=>'reset'];
                    $_SESSION['auth_audience']='customer';
                    go('auth/verify.php');
                }
                $error=mail_error_message($e);
            }
        }
        else{
            flash('If an account exists, a code will be sent to it.');
            go('auth/login.php');
        }
    }
}
page_start(tr('forgot'));
?><main class="container"><form class="form-panel" method="post" autocomplete="off"><h1><?=h(tr('forgot'))?></h1><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="email"><?=h(tr('email'))?></label><input id="email" type="email" name="email" required autocomplete="off" value="<?=h($_POST['email']??'')?>"></div><button class="btn btn-gold w-100"><?=h(tr('send_code'))?></button></form></main><?php
page_end();
