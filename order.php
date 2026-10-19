<?php
// View and update one customer order.
require dirname(__DIR__).'/config/bootstrap.php';
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
$id=(int)($_GET['id']??0);
$error='';
// Validate status and delivery fee changes.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $status=(string)($_POST['status']??'');
    $fee=(string)($_POST['delivery_fee']??'');
    if(!in_array($status,['placed','confirmed','packing','shipped','delivered','cancelled'],true)||($fee!==''&&(!ctype_digit($fee)||(int)$fee>10000000)))$error='Check status and delivery fee.';
    else{
        try{
            // Keep status changes and stock restoration atomic.
            $db->beginTransaction();
            $old=query('SELECT * FROM orders WHERE id=? FOR UPDATE',[$id])->fetch();
            if(!$old)throw new RuntimeException('Order not found.');
            if($old['status']==='cancelled'&&$status!=='cancelled')throw new RuntimeException('Cancelled orders cannot be reopened. Place a new order.');
            if($status==='cancelled'&&$old['status']!=='cancelled'){
                $items=query('SELECT watch_id,quantity FROM order_items WHERE order_id=?',[$id])->fetchAll();
                foreach($items as $item)if($item['watch_id'])query('UPDATE watches SET stock=stock+? WHERE id=?',[$item['quantity'],$item['watch_id']]);
            }
            query('UPDATE orders SET status=?,delivery_fee=? WHERE id=?',[$status,$fee===''?null:$fee,$id]);
            $db->commit();
            flash('Order updated.');
            go('admin/order.php?id='.$id);
        }
        catch(Throwable $e){
            if($db->inTransaction())$db->rollBack();
            $error=$e->getMessage();
        }
    }
}
// Load this order and its customer for display.
$o=query('SELECT o.*,u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?',[$id])->fetch();
if(!$o){
    http_response_code(404);
    exit('Order not found');
}
$items=query('SELECT * FROM order_items WHERE order_id=?',[$id])->fetchAll();
page_start('Order '.$o['order_number'],true);
?><main class="container section"><?php
admin_nav();
?><h1>Order <?=h($o['order_number'])?></h1><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><div class="split"><div class="panel"><h2>Customer and delivery</h2><p><?=h($o['email'])?> · <?=h($o['recipient'])?> · <?=h($o['phone'])?></p><p><?=h($o['address'])?>, <?=h($o['township'])?>, <?=h($o['region'])?></p><p>Landmark: <?=h($o['landmark'])?></p><p>Note: <?=h($o['notes'])?></p><h2>Items</h2><?php
foreach($items as $i):
?><p><?=h($i['name'])?> × <?=$i['quantity']?> — <?=money($i['unit_price']*$i['quantity'])?></p><?php
endforeach
?><p>Subtotal: <?=money($o['subtotal'])?></p><p>Total: <?=$o['delivery_fee']===null?'Delivery fee pending':money($o['subtotal']+$o['delivery_fee'])?></p></div><form method="post" class="panel"><h2>Update order</h2><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label>Status</label><select name="status"><?php
foreach(['placed','confirmed','packing','shipped','delivered','cancelled'] as $s):
?><option value="<?=$s?>" <?=$o['status']===$s?'selected':''?>><?=ucfirst($s)?></option><?php
endforeach
?></select></div><div class="field"><label>Delivery fee (MMK; leave blank until confirmed)</label><input type="number" name="delivery_fee" min="0" step="1" value="<?=h((string)($o['delivery_fee']??''))?>"></div><button class="btn btn-gold">Save order</button></form></div></main><?php
page_end();
