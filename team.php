<?php
// Create and list administrator accounts.
require dirname(__DIR__).'/config/bootstrap.php';
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
$error='';
// Validate a new administrator invitation.
if($_SERVER['REQUEST_METHOD']==='POST') {
    post_only();
    $name=trim((string)($_POST['name']??''));
    $email=strtolower(trim((string)($_POST['email']??'')));
    $pass=(string)($_POST['password']??'');
    if(throttled('admin_invite',$_SERVER['REMOTE_ADDR']??'local',8)) $error='Too many attempts. Try later.';
    elseif(mb_strlen($name)<2||mb_strlen($name)>120||!filter_var($email,FILTER_VALIDATE_EMAIL)||!str_ends_with($email,'@gmail.com')||strlen($pass)<12||strlen($pass)>128||$pass!==($_POST['confirm']??'')) $error='Enter a valid name, Gmail and matching password of 12–128 characters.';
    elseif(query('SELECT id FROM users WHERE email=?',[$email])->fetch()) $error='That email already has an account.';
    else {
        record_attempt('admin_invite',$_SERVER['REMOTE_ADDR']??'local');
        try {
            // Save the invited administrator account.
            query("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,'admin')",[$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
            flash('Admin created. They must open Admin activation with the same Gmail and password and confirm the email code.');
            go('admin/team.php');
        }
        catch(PDOException $e) {
            error_log($e->getMessage());
            $error='Could not create the account. Check whether the email already exists.';
        }
    }
}
// List current administrators and verification dates.
$admins=query("SELECT name,email,verified_at,created_at FROM users WHERE role='admin' ORDER BY created_at DESC")->fetchAll();
page_start('Administrators',true);
?><main class="container section"><?php
admin_nav();
?><h1>Administrators</h1><?php
if($error):
?><div class="flash error" role="alert"><?=h($error)?></div><?php
endif
?><div class="split"><div class="panel"><h2>Admin accounts</h2><div class="table-wrap"><table><thead><tr><th>Name</th><th>Gmail</th><th>Status</th></tr></thead><tbody><?php
foreach($admins as $a):
?><tr><td><?=h($a['name'])?></td><td><?=h($a['email'])?></td><td><?=$a['verified_at']?'Verified':'Awaiting email verification'?></td></tr><?php
endforeach
?></tbody></table></div></div><form class="panel" method="post"><h2>Add administrator</h2><p class="form-note">Create a new Gmail account record. Give the new admin their initial password privately. They open Admin activation, enter the same details, and verify their Gmail code.</p><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="name">Name</label><input id="name" name="name" required maxlength="120"></div><div class="field"><label for="email">Gmail address</label><input id="email" name="email" type="email" required></div><div class="field"><label for="password">Initial password</label><input id="password" name="password" type="password" required minlength="12"></div><div class="field"><label for="confirm">Confirm password</label><input id="confirm" name="confirm" type="password" required minlength="12"></div><button class="btn btn-gold">Add administrator</button></form></div></main><?php
page_end();
