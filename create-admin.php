<?php
// Create a verified administrator from the server command line.
// Run on the server CLI only: php tools/create-admin.php admin@gmail.com
// Reject web requests to this maintenance tool.
if(PHP_SAPI!=='cli'){
    http_response_code(403);
    exit;
}
require dirname(__DIR__).'/config/bootstrap.php';
// Read and validate the supplied Gmail address.
$email=strtolower(trim((string)($argv[1]??'')));
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||!str_ends_with($email,'@gmail.com')){
    fwrite(STDERR,"Usage: php tools/create-admin.php admin@gmail.com\n");
    exit(1);
}
echo 'Admin name: ';
$name=trim((string)fgets(STDIN));
echo 'Password (12+ chars; input visible): ';
$pass=rtrim((string)fgets(STDIN),"\r\n");
if(mb_strlen($name)<2||strlen($pass)<12||strlen($pass)>128){
    fwrite(STDERR,"Invalid name/password.\n");
    exit(1);
}
if(query('SELECT id FROM users WHERE email=?',[$email])->fetch()){
    fwrite(STDERR,"Email already exists.\n");
    exit(1);
}
// Save the administrator with a hashed password.
query('INSERT INTO users(name,email,password_hash,role,verified_at) VALUES(?,?,?,\'admin\',NOW())',[$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
echo "Admin created. Login still requires an email code.\n";
