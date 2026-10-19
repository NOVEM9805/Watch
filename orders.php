<?php
// Show a customer's order history.
require dirname(__DIR__).'/config/bootstrap.php';
// Require an authenticated customer.
$u=require_login();
require dirname(__DIR__).'/includes/layout.php';
// Retrieve only this customer's orders.
$rows=query('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC',[$u['id']])->fetchAll();
// Render the order history and empty state.
page_start(tr('orders'));
?><div class="page-head"><div class="container"><h1><?=h(tr('orders'))?></h1></div></div><main class="container section"><?php
if(!$rows):
?><p><?=h(tr('empty'))?></p><?php
else:
?><div class="table-wrap"><table><thead><tr><th>Order</th><th>Date</th><th><?=h(tr('status'))?></th><th><?=h(tr('subtotal'))?></th><th><?=h(tr('total'))?></th></tr></thead><tbody><?php
foreach($rows as $o):
?><tr><td><a class="text-link" href="<?=url('customer/order-details.php?id='.$o['id'])?>"><?=h($o['order_number'])?></a></td><td><?=h($o['created_at'])?></td><td><?=h($o['status'])?></td><td><?=money($o['subtotal'])?></td><td><?=$o['delivery_fee']===null?h(tr('delivery_pending')):money($o['subtotal']+$o['delivery_fee'])?></td></tr><?php
endforeach
?></tbody></table></div><?php
endif
?></main><?php
page_end();
