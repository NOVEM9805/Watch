<?php
// List customer accounts for administrators.
require dirname(__DIR__).'/config/bootstrap.php';
// Restrict this list to administrators.
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
// Include each customer's order count.
$rows=query("SELECT u.id,u.name,u.email,u.verified_at,u.created_at,COUNT(o.id) AS orders FROM users u LEFT JOIN orders o ON o.user_id=u.id WHERE u.role='customer' GROUP BY u.id ORDER BY u.id DESC LIMIT 200")->fetchAll();
// Render the customer list in the admin layout.
page_start('Customers',true);
?><main class="container section"><?php
admin_nav();
?><h1>Customers</h1><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Verified</th><th>Orders</th><th>Registered</th></tr></thead><tbody><?php
foreach($rows as $r):
?><tr><td><?=h($r['name'])?></td><td><?=h($r['email'])?></td><td><?=$r['verified_at']?'Yes':'No'?></td><td><?=$r['orders']?></td><td><?=h($r['created_at'])?></td></tr><?php
endforeach
?></tbody></table></div></main><?php
page_end();
