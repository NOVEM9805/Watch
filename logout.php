<?php
// End the current customer or administrator session.
require dirname(__DIR__).'/config/bootstrap.php';
// Accept logout only from the signed-out form.
post_only();
// Remember which sign-in page to return to.
$admin=current_user()['role']??'';
// Remove session data and renew the session ID.
$_SESSION=[];
session_regenerate_id(true);
go($admin==='admin'?'auth/admin-login.php':'');
