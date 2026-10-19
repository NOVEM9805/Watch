<?php
// Register new customer accounts with email confirmation.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
if($loggedIn=current_user())go($loggedIn['role']==='admin'?'admin/dashboard.php':'customer/profile.php');
$error='';
// Validate account details and rate-limit requests.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $name=trim((string)($_POST['name']??''));
    $email=strtolower(trim((string)($_POST['email']??'')));
    $pass=(string)($_POST['password']??'');
    if(throttled('register',$email,5))$error='Too many requests. Try again in 15 minutes.';
    elseif(mb_strlen($name)<2||mb_strlen($name)>120||!filter_var($email,FILTER_VALIDATE_EMAIL)||!str_ends_with($email,'@gmail.com'))$error='Enter your name and a valid Gmail address.';
    elseif(strlen($pass)<12||strlen($pass)>128||$pass!==($_POST['confirm']??''))$error='Password must be 12–128 characters and both passwords must match.';
    else {
        record_attempt('register',$email);
        try{
            // Check whether this email already has an account.
            $existing=query('SELECT id,password_hash,verified_at,role FROM users WHERE email=?',[$email])->fetch();
            if($existing){
                if($existing['role']==='admin')throw new RuntimeException('Administrator accounts use the admin sign in page.');
                if($existing['verified_at']||!password_verify($pass,$existing['password_hash']))throw new RuntimeException('This account already exists. Try login or password reset.');
                $id=(int)$existing['id'];
            }
            else{
                query('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)',[$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
                $id=(int)$db->lastInsertId();
            }
            $_SESSION['pending']=['id'=>$id,'purpose'=>'register'];
            $_SESSION['auth_audience']='customer';
            $_SESSION['next']=safe_next((string)($_GET['next']??'/'));
            // Send the registration code before verification.
            send_otp($id,$email,'register');
            go('auth/verify.php');
        }
        catch(Throwable $e){
            error_log($e->getMessage());
            $error=in_array($e->getMessage(),['This account already exists. Try login or password reset.','Administrator accounts use the admin sign in page.'],true)?$e->getMessage():mail_error_message($e);
        }
    }
}
page_start(tr('signup'));
?><main class="container"><form class="form-panel" method="post" autocomplete="off"><h1><?=h(tr('signup'))?></h1><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="name"><?=h(tr('recipient'))?></label><input id="name" name="name" required maxlength="120" value="<?=h($_POST['name']??'')?>"></div><div class="field"><label for="email"><?=h(tr('email'))?></label><input id="email" type="email" name="email" required autocomplete="off" value="<?=h($_POST['email']??'')?>"></div><div class="field"><label for="pass"><?=h(tr('password'))?></label><input id="pass" type="password" name="password" required minlength="12" autocomplete="off"></div><div class="field"><label for="confirm"><?=h(tr('confirm_password'))?></label><input id="confirm" type="password" name="confirm" required minlength="12" autocomplete="off"></div><button class="btn btn-gold w-100"><?=h(tr('signup'))?></button><p><a class="text-link" href="<?=url('auth/login.php')?>"><?=h(tr('login'))?></a></p></form></main><?php
page_end();
