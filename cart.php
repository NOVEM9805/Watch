<?php
// Update quantities and display the shopping cart.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Validate cart quantity changes.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $id=(int)($_POST['id']??0);
    $qty=(int)($_POST['qty']??0);
    if($id>0 && isset($_SESSION['cart'][$id])){
        if($qty<=0)unset($_SESSION['cart'][$id]);
        else{
            /* Recheck live stock when the cart quantity changes. */
            $stock=query('SELECT stock FROM watches WHERE id=? AND active=1',[$id])->fetchColumn();
            $max=min(20,max(0,(int)$stock));
            if($qty>$max){
                flash('Only '.$max.' available. Please adjust the quantity.','error');
            }
            else $_SESSION['cart'][$id]=$qty;
        }
    }
    go('cart');
}
// Load current cart items from the catalog.
$rows=cart_rows();
// Calculate the cart subtotal for display.
$subtotal=0;
foreach($rows as $r)$subtotal+=$r['price']*$r['qty'];
page_start(tr('cart'));
?><div class="page-head"><div class="container"><h1><?=h(tr('cart'))?></h1></div></div><main class="container section"><?php
if(!$rows):
?><p><?=h(tr('empty'))?></p><a class="btn" href="<?=url('shop')?>"><?=h(tr('continue'))?></a><?php
else:
?><div class="split"><div class="table-wrap"><table><thead><tr><th>Watch</th><th><?=h(tr('price'))?></th><th><?=h(tr('quantity'))?></th><th><?=h(tr('subtotal'))?></th></tr></thead><tbody><?php
foreach($rows as $r):
?><tr><td><a href="<?=url('watch/'.$r['id'])?>"><?=h($r['name'])?></a></td><td><?=(float)$r['price']>0?money($r['price']):h(tr('price_pending'))?></td><td><form method="post" class="cart-quantity-form"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$r['id']?>"><input aria-label="<?=h(tr('quantity'))?>" type="number" name="qty" min="0" max="<?=min(20,(int)$r['stock'])?>" value="<?=$r['qty']?>"><button class="btn btn-outline" type="submit"><?=h(tr('save'))?></button></form></td><td><?=(float)$r['price']>0?money($r['price']*$r['qty']):h(tr('price_pending'))?></td></tr><?php
endforeach
?></tbody></table></div><aside class="panel"><h2><?=h(tr('subtotal'))?></h2><h3><?=money($subtotal)?></h3><p class="mini-meta"><?=h(tr('delivery_pending'))?></p><?php
if(store_ready()):
?><a class="btn btn-gold w-100" href="<?=url('order')?>"><?=h(tr('checkout'))?></a><?php
else:
?><p class="catalog-warning"><?=h(tr('orders_paused'))?></p><?php
endif
?></aside></div><?php
endif
?></main><?php
page_end();
