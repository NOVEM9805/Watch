<?php
// Create the first administrator with a private setup key.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Close setup after an administrator exists.
$existing=(int)query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
if ($existing) {
    http_response_code(403);
    page_start('Admin setup',true);
    echo '<main class="container section"><div class="panel"><h1>Admin setup is closed</h1><p>An administrator account already exists. <a class="text-link" href="'.h(url('auth/admin-login.php')).'">Sign in</a>, activate your pending account, or ask an administrator to add another account.</p></div></main>';
    page_end();
    exit;
}
$error='';
if (!is_file(dirname(__DIR__).'/.env')) $error='The .env file is missing. Copy .env.example to .env, then set your own ADMIN_SETUP_KEY and Gmail mail settings as described in INSTALL_MM.md.';
// Validate the setup key and first account details.
if ($_SERVER['REQUEST_METHOD']==='POST') {
    post_only();
    $email=strtolower(trim((string)($_POST['email']??'')));
    $name=trim((string)($_POST['name']??''));
    $password=(string)($_POST['password']??'');
    $key=(string)($_POST['setup_key']??'');
    if (throttled('admin_setup',$_SERVER['REMOTE_ADDR']??'local',5)) $error='Too many attempts. Try again in 15 minutes.';
    else {
        record_attempt('admin_setup',$_SERVER['REMOTE_ADDR']??'local');
        if (!is_file(dirname(__DIR__).'/.env')) $error='The .env file is missing. Copy .env.example to .env and configure Gmail mail settings.';
        elseif (strlen(cfg('ADMIN_SETUP_KEY'))<24 || !hash_equals(cfg('ADMIN_SETUP_KEY'),$key)) $error='Invalid setup key. Set your own private 24+ character ADMIN_SETUP_KEY in .env first.';
        elseif (mb_strlen($name)<2 || mb_strlen($name)>120 || !filter_var($email,FILTER_VALIDATE_EMAIL) || !str_ends_with($email,'@gmail.com')) $error='Enter a name and a valid Gmail address.';
        elseif (strlen($password)<12 || strlen($password)>128 || $password!==($_POST['confirm']??'')) $error='Password must be 12–128 characters and match the confirmation.';
        else {
            // Prevent simultaneous first-admin creation.
            $locked=(int)query("SELECT GET_LOCK('timeless_sight_first_admin',5)")->fetchColumn();
            if (!$locked) $error='Setup is busy. Please retry.';
            else {
                $id=0;
                $stage='account';
                try {
                    if ((int)query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()) throw new RuntimeException('An administrator already exists.');
                    if (query('SELECT id FROM users WHERE email=?',[$email])->fetch()) throw new RuntimeException('This email already has an account. Use a different Gmail address.');
                    query("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,'admin')",[$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
                    $id=(int)$db->lastInsertId();
                    $stage='mail';
                    send_otp($id,$email,'register');
                    $_SESSION['pending']=['id'=>$id,'purpose'=>'register'];
                    $_SESSION['auth_audience']='admin';
                    $_SESSION['next']='/admin/dashboard.php';
                    clear_attempts('admin_setup',$_SERVER['REMOTE_ADDR']??'local');
                }
                catch(Throwable $e) {
                    if ($id) query('DELETE FROM users WHERE id=? AND verified_at IS NULL',[$id]);
                    error_log('Admin setup: '.$e->getMessage());
                    $error=$e instanceof RuntimeException && in_array($e->getMessage(),['An administrator already exists.','This email already has an account. Use a different Gmail address.'],true) ? $e->getMessage() : ($stage==='mail'?mail_error_message($e):'Account could not be created. Check the database and Apache error log.');
                }
                finally {
                    query("SELECT RELEASE_LOCK('timeless_sight_first_admin')");
                }
                if (!$error) go('auth/verify.php');
            }
        }
    }
}
page_start('Admin setup',true);
?><main class="admin-auth"><form class="admin-auth-card" method="post" autocomplete="off"><span class="eyebrow">Timeless Sight · First administrator</span><h1>Create administrator account</h1><p class="form-note">This page works only before the first administrator is created. First copy .env.example to .env. Choose your own private ADMIN_SETUP_KEY with at least 24 characters and put the same value in the field below. This key protects first-time setup; it is separate from your Gmail password. Set up Gmail delivery for the verification code.</p><?php
if($error):
?><div class="flash error" role="alert"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="setup_key">Private setup key</label><input id="setup_key" name="setup_key" type="password" required autocomplete="off"></div><div class="field"><label for="name">Name</label><input id="name" name="name" required maxlength="120" value="<?=h($_POST['name']??'')?>"></div><div class="field"><label for="email">Admin Gmail</label><input id="email" name="email" type="email" required autocomplete="off" value="<?=h($_POST['email']??'')?>"></div><div class="field"><label for="password">Password (12+ characters)</label><input id="password" name="password" type="password" required minlength="12" autocomplete="off"></div><div class="field"><label for="confirm">Confirm password</label><input id="confirm" name="confirm" type="password" required minlength="12" autocomplete="off"></div><button class="btn btn-gold w-100">Create admin and send verification code</button><p class="form-note">Already have an admin? <a class="text-link" href="<?=url('auth/admin-login.php')?>">Admin sign in</a></p></form></main><?php
page_end();
