<?php
// Create an order from the current cart.
require dirname(__DIR__).'/config/bootstrap.php';
$u=require_login();
if(!store_ready())go('shop');
require dirname(__DIR__).'/includes/layout.php';
// Require a cart with available items.
$rows=cart_rows();
if(!$rows)go('cart');
$subtotal=0;
foreach($rows as $r)$subtotal+=(float)$r['price']*$r['qty'];
$error='';
// Validate delivery details before ordering.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $fields=[];
    foreach(['recipient'=>120,'phone'=>35,'region'=>120,'township'=>120,'address'=>500,'landmark'=>220,'notes'=>1000] as $k=>$limit){
        $fields[$k]=trim((string)($_POST[$k]??''));
        if(mb_strlen($fields[$k])>$limit)$error='Some fields are too long.';
    }
    if(!$fields['recipient']||!preg_match('/^[0-9+() -]{7,20}$/',$fields['phone'])||!$fields['region']||!$fields['township']||mb_strlen($fields['address'])<8)$error='Fill the recipient, valid phone, region, township and full address.';
    // Lock each watch before reducing stock so concurrent orders cannot oversell it.
    if(!$error){
        try{
            // Lock stock and save the order in one transaction.
            $db->beginTransaction();
            $items=[];
            $total=0;
            foreach($rows as $r){
                $fresh=query('SELECT w.id,w.name,w.price,w.stock,w.active,b.name AS brand,b.active AS brand_active FROM watches w JOIN brands b ON b.id=w.brand_id WHERE w.id=? FOR UPDATE',[$r['id']])->fetch();
                if(!$fresh||!$fresh['active']||!$fresh['brand_active']||(int)$fresh['stock']<$r['qty']||(float)$fresh['price']<=0)throw new RuntimeException('A watch in your cart is unavailable. Please review your cart.');
                $items[]=[$fresh,$r['qty']];
                $total+=(float)$fresh['price']*$r['qty'];
            }
            $number='TS'.date('ymd').strtoupper(bin2hex(random_bytes(5)));
            query('INSERT INTO orders(order_number,user_id,recipient,phone,region,township,address,landmark,notes,subtotal) VALUES(?,?,?,?,?,?,?,?,?,?)',[$number,$u['id'],$fields['recipient'],$fields['phone'],$fields['region'],$fields['township'],$fields['address'],$fields['landmark'],$fields['notes'],$total]);
            $orderId=(int)$db->lastInsertId();
            foreach($items as [$w,$qty]){
                query('INSERT INTO order_items(order_id,watch_id,name,brand_name,unit_price,quantity) VALUES(?,?,?,?,?,?)',[$orderId,$w['id'],$w['name'],$w['brand'],$w['price'],$qty]);
                query('UPDATE watches SET stock=stock-? WHERE id=?',[$qty,$w['id']]);
            }
            $db->commit();
            unset($_SESSION['cart']);
            flash('Order placed. Pay cash when your watch arrives.');
            go('customer/order-details.php?id='.$orderId);
        }
        catch(Throwable $e){
            if($db->inTransaction())$db->rollBack();
            error_log($e->getMessage());
            $error=$e instanceof PDOException?'Order could not be placed. Try again.':$e->getMessage();
        }
    }
}
page_start(tr('checkout'));
?><div class="page-head"><div class="container"><h1><?=h(tr('checkout'))?></h1><p><?=h(tr('cod'))?></p></div></div><main class="container section"><div class="split"><form class="panel" method="post"><h2>Delivery details</h2><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><input type="hidden" name="csrf" value="<?=csrf()?>"><?php
foreach(['recipient'=>'text','phone'=>'tel','region'=>'text','township'=>'text','address'=>'textarea','landmark'=>'text','notes'=>'textarea'] as $key=>$type):
?><div class="field"><label for="<?=$key?>"><?=h(tr($key))?></label><?php
if($type==='textarea'):
?><textarea id="<?=$key?>" name="<?=$key?>" <?=$key==='address'?'required':''?> maxlength="<?=$key==='notes'?1000:500?>"><?=h($_POST[$key]??'')?></textarea><?php
else:
?><input id="<?=$key?>" type="<?=$type?>" name="<?=$key?>" value="<?=h($_POST[$key]??($key==='recipient'?$u['name']:''))?>" maxlength="220" <?=in_array($key,['recipient','phone','region','township'])?'required':''?>><?php
endif
?></div><?php
endforeach
?><button class="btn btn-gold w-100"><?=h(tr('place_order'))?></button></form><aside class="panel"><h2>Order summary</h2><?php
foreach($rows as $r):
?><p><?=h($r['name'])?> × <?=$r['qty']?> <strong style="float:right"><?=money($r['price']*$r['qty'])?></strong></p><?php
endforeach
?><p class="order-total"><?=h(tr('subtotal'))?>: <?=money($subtotal)?></p><p class="notice"><?=h(tr('delivery_pending'))?>. You will see the final COD amount after it is set by the shop.</p></aside></div></main><?php
page_end();
