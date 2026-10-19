<?php
// Sign customers in with credentials and an email code.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
if($loggedIn=current_user())go($loggedIn['role']==='admin'?'admin/dashboard.php':'customer/profile.php');
$error='';
// Keep the post-login destination inside this site.
$next=safe_next((string)($_GET['next']??'/'));
// Check submitted customer login details.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $email=strtolower(trim((string)($_POST['email']??'')));
    $pass=(string)($_POST['password']??'');
    $next=safe_next((string)($_POST['next']??'/'));
    if(throttled('login',$email))$error='Too many attempts. Try again in 15 minutes.';
    else {
        $u=query('SELECT * FROM users WHERE email=?',[$email])->fetch();
        if(!$u||!password_verify($pass,$u['password_hash'])||!$u['verified_at']){
            record_attempt('login',$email);
            $error='Invalid login details or unverified account.';
        }
        elseif($u['role']==='admin'){
            $error='This account uses the administrator sign in page.';
        }
        else{
            try{
                // Send a verification code before signing in.
                send_otp((int)$u['id'],$u['email'],'login');
                clear_attempts('login',$email);
                $_SESSION['pending']=['id'=>(int)$u['id'],'purpose'=>'login'];
                $_SESSION['auth_audience']='customer';
                $_SESSION['next']=$next;
                go('auth/verify.php');
            }
            catch(Throwable $e){
                $error=$e->getMessage();
            }
        }
    }
}
page_start(tr('login'));
?><main class="container"><form class="form-panel" method="post" autocomplete="off"><h1><?=h(tr('login'))?></h1><p class="form-note">A code is sent to your Gmail after your password is checked.</p><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="next" value="<?=h($next)?>"><div class="field"><label for="email"><?=h(tr('email'))?></label><input id="email" type="email" name="email" required autocomplete="off" value="<?=h($_POST['email']??'')?>"></div><div class="field"><label for="pass"><?=h(tr('password'))?></label><input id="pass" type="password" name="password" required autocomplete="off"></div><button class="btn btn-gold w-100"><?=h(tr('login'))?></button><p><a class="text-link" href="<?=url('auth/register.php?next='.rawurlencode($next))?>"><?=h(tr('signup'))?></a> · <a class="text-link" href="<?=url('auth/forgot.php')?>"><?=h(tr('forgot'))?></a></p></form></main><?php
page_end();
