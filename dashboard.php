<?php
// Show catalog and order summaries for administrators.
require dirname(__DIR__).'/config/bootstrap.php';
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
// Collect totals for the dashboard cards.
$stats=[];
foreach(['Brands'=>'brands','Watches'=>'watches','Orders'=>'orders'] as $label=>$table)$stats[$label]=(int)query('SELECT COUNT(*) FROM '.$table)->fetchColumn();
$stats['Customers']=(int)query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
// Load the latest orders for quick access.
$orders=query('SELECT o.*,u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 8')->fetchAll();
// Render the admin dashboard.
page_start('Dashboard',true);
?><main class="container section"><?php
admin_nav();
?><h1>Admin dashboard</h1><div class="stats"><?php
foreach($stats as $label=>$count):
?><div class="stat"><span><?=h($label)?></span><b><?=$count?></b></div><?php
endforeach
?></div><h2>Recent orders</h2><div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Subtotal</th></tr></thead><tbody><?php
foreach($orders as $o):
?><tr><td><a class="text-link" href="<?=url('admin/order.php?id='.$o['id'])?>"><?=h($o['order_number'])?></a></td><td><?=h($o['email'])?></td><td><?=h($o['status'])?></td><td><?=money($o['subtotal'])?></td></tr><?php
endforeach
?></tbody></table></div></main><?php
page_end();
