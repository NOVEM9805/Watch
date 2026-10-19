<?php
// Switch the storefront language.
require dirname(__DIR__).'/config/bootstrap.php';
// Require a valid form submission.
post_only();
// Store the chosen language in the session.
$_SESSION['lang']=($_POST['lang']??'en')==='mm'?'mm':'en';
// Redirect only to a safe page on this site.
$back=safe_next((string)($_POST['back']??'/'));
header('Location: '.url(ltrim($back,'/')),true,303);
exit;
