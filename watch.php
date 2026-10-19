<?php
// Show a watch and handle cart, wishlist and reviews.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
$id=(int)($_GET['id']??0);
// Load only a visible watch and active brand.
$w=query('SELECT w.*,b.name AS brand FROM watches w JOIN brands b ON b.id=w.brand_id WHERE w.id=? AND w.active=1 AND b.active=1',[$id])->fetch();
if(!$w){
    http_response_code(404);
    exit('Watch not found');
}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $action=$_POST['action']??'';
    // Count watches already in the session cart against current stock.
    // Check stock before adding to the cart.
    if($action==='cart'){
        $qty=(int)($_POST['qty']??0);
        $inCart=(int)($_SESSION['cart'][$id]??0);
        $available=min(20,(int)$w['stock']);
        if($qty<1||$qty>max(0,$available-$inCart)){
            $error='Choose a quantity within the available stock (including items already in your cart).';
        }
        else{
            $_SESSION['cart'][$id]=$qty+$inCart;
            flash('Added to cart.');
            go('cart');
        }
    }
    if($action==='wishlist'){
        $u=require_login();
        query('INSERT IGNORE INTO wishlist(user_id,watch_id) VALUES(?,?)',[$u['id'],$id]);
        flash('Saved to wishlist.');
        go('watch/'.$id);
    }
    // Allow reviews only for delivered purchases.
    if($action==='review'){
        $u=require_login();
        $rating=(int)($_POST['rating']??0);
        $body=trim((string)($_POST['body']??''));
        $eligible=query("SELECT 1 FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.user_id=? AND o.status='delivered' AND oi.watch_id=? LIMIT 1",[$u['id'],$id])->fetchColumn();
        if(!$eligible){
            $error='Reviews are available after delivery.';
        }
        elseif($rating<1||$rating>5||mb_strlen($body)<5||mb_strlen($body)>1000){
            $error='Choose 1–5 stars and write 5–1000 characters.';
        }
        else{
            query('INSERT INTO reviews(user_id,watch_id,rating,body) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),body=VALUES(body)',[$u['id'],$id,$rating,$body]);
            flash('Review saved.');
            go('watch/'.$id);
        }
    }
}
// Keep the saved state visible on this watch page after adding to the wishlist.
$saved=current_user()?(bool)query('SELECT 1 FROM wishlist WHERE user_id=? AND watch_id=?',[(int)current_user()['id'],$id])->fetchColumn():false;
$maxQty=max(0,min(20,(int)$w['stock'])-(int)($_SESSION['cart'][$id]??0));
$reviews=query('SELECT r.*,u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.watch_id=? ORDER BY r.created_at DESC',[$id])->fetchAll();
page_start($w['name']);
?><main class="container section"><p class="mini-meta"><a href="<?=url('shop')?>"><?=h(tr('shop'))?></a> / <a href="<?=url('shop?brand='.rawurlencode(strtolower($w['brand'])))?>"><?=h($w['brand'])?></a> / <?=h($w['name'])?></p><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><div class="grid-2"><div class="detail-image"><?php
if($w['image_path']):
?><img src="<?=h(product_image($w['image_path']))?>" alt="<?=h($w['name'])?>"><?php
else:
?><span class="no-photo"><b>TS</b><small><?=h(tr('no_image'))?></small></span><?php
endif
?></div><div class="detail-info"><span class="eyebrow"><?=h($w['brand'])?> · <?=h($w['model'])?></span><h1><?=h($w['name'])?></h1><?php
if(!store_ready()):
?><p class="catalog-warning"><?=h(tr('sample_catalog'))?></p><?php
endif
?><p class="detail-price"><?=(float)$w['price']>0?money($w['price']):h(tr('price_pending'))?></p><p><?=nl2br(h($w['description']))?></p><p class="mini-meta"><?=(int)$w['stock']>0?h(tr('stock')).': '.(int)$w['stock'].(!store_ready()?' ('.h(tr('sample_price')).')':''):h(tr('out_stock'))?></p><div class="detail-actions"><form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="cart"><label class="sr-only" for="qty"><?=h(tr('quantity'))?></label><input id="qty" type="number" name="qty" value="1" min="1" max="<?=$maxQty?>" <?=$maxQty===0?'disabled':''?>><button class="btn btn-gold" <?=$maxQty===0?'disabled':''?>><?=h(tr('add_cart'))?></button></form><form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="wishlist"><button class="btn btn-outline" aria-label="<?=h($saved?tr('wishlist_saved'):tr('wishlist'))?>"><?=$saved?'♥':'♡'?> <?=h($saved?tr('wishlist_saved'):tr('wishlist'))?></button></form></div><p class="notice"><?=h(tr('cod'))?> · <?=h(tr('delivery_pending'))?></p></div></div><section class="section"><h2><?=h(tr('reviews'))?></h2><?php
foreach($reviews as $r):
?><div class="review"><strong><?=h($r['name'])?> · <?=str_repeat('★',(int)$r['rating'])?></strong><p><?=nl2br(h($r['body']))?></p></div><?php
endforeach
?><?php
if(!$reviews):
?><p><?=h(tr('empty'))?></p><?php
endif
?><?php
if(current_user()):
?><form method="post" class="form-panel" style="margin:25px 0"><h3><?=h(tr('write_review'))?></h3><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="review"><fieldset class="star-rating"><legend>Rating</legend><?php
foreach([5,4,3,2,1] as $stars):
?><input type="radio" id="rating-<?=$stars?>" name="rating" value="<?=$stars?>" <?=$stars===5?'required':''?>><label for="rating-<?=$stars?>" aria-label="<?=$stars?> <?=$stars===1?'star':'stars'?>">★</label><?php
endforeach
?></fieldset><div class="field"><label><?=h(tr('write_review'))?></label><textarea name="body" required minlength="5" maxlength="1000"></textarea></div><button class="btn"><?=h(tr('save'))?></button><p class="form-note">Available after your order is delivered.</p></form><?php
endif
?></section></main><?php
page_end();
