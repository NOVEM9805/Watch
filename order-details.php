<?php
// Display details for one customer order.
require dirname(__DIR__).'/config/bootstrap.php';
// Require the customer account.
$u=require_login();
require dirname(__DIR__).'/includes/layout.php';
// Load only an order owned by this customer.
$o=query('SELECT * FROM orders WHERE id=? AND user_id=?',[(int)($_GET['id']??0),$u['id']])->fetch();
if(!$o){
    http_response_code(404);
    exit('Order not found');
}
// Retrieve the watches saved in this order.
$items=query('SELECT * FROM order_items WHERE order_id=?',[$o['id']])->fetchAll();
page_start(tr('track'));
?><div class="page-head"><div class="container"><h1><?=h(tr('track'))?> · <?=h($o['order_number'])?></h1></div></div><main class="container section"><div class="split"><div class="panel"><h2><?=h(tr('status'))?>: <?=h(ucfirst($o['status']))?></h2><p class="mini-meta"><?=h($o['created_at'])?></p><?php
foreach($items as $i):
?><p><?=h($i['brand_name'].' '.$i['name'])?> × <?=(int)$i['quantity']?> <strong style="float:right"><?=money($i['unit_price']*$i['quantity'])?></strong></p><?php
endforeach
?><hr><p><?=h(tr('subtotal'))?>: <?=money($o['subtotal'])?></p><p><?=h(tr('delivery_fee'))?>: <?=$o['delivery_fee']===null?h(tr('delivery_pending')):money($o['delivery_fee'])?></p><?php
if($o['delivery_fee']!==null):
?><p class="order-total"><?=h(tr('total'))?>: <?=money($o['subtotal']+$o['delivery_fee'])?></p><?php
endif
?></div><div class="panel"><h2>Delivery to</h2><p><?=h($o['recipient'])?> · <?=h($o['phone'])?></p><p><?=h($o['address'])?><br><?=h($o['township'])?>, <?=h($o['region'])?></p><p><?=h($o['landmark'])?></p><p><?=h($o['notes'])?></p><p class="notice"><?=h(tr('cod'))?></p></div></div></main><?php
page_end();
