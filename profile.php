<?php
// Show and update a customer profile.
require dirname(__DIR__).'/config/bootstrap.php';
$u=require_login();
require dirname(__DIR__).'/includes/layout.php';
$error='';
// Validate and save a changed name.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $name=trim((string)($_POST['name']??''));
    if(mb_strlen($name)<2||mb_strlen($name)>120)$error='Name must be 2–120 characters.';
    else{
        query('UPDATE users SET name=? WHERE id=?',[$name,$u['id']]);
        flash('Profile updated.');
        go('customer/profile.php');
    }
}
// Count orders for the profile summary.
$orderCount=(int)query('SELECT COUNT(*) FROM orders WHERE user_id=?',[$u['id']])->fetchColumn();
$wishCount=(int)query('SELECT COUNT(*) FROM wishlist WHERE user_id=?',[$u['id']])->fetchColumn();
// Show the customer's latest orders.
$latest=query('SELECT order_number,id,status,subtotal,created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 3',[$u['id']])->fetchAll();
page_start(tr('profile'));
?><div class="page-head profile-head"><div class="container"><span class="eyebrow">MY ACCOUNT</span><h1><?=h(tr('profile'))?></h1><p>Manage your details and keep up with every order.</p></div></div><main class="container section profile-layout"><aside class="profile-sidebar"><div class="profile-identity"><span class="profile-avatar" aria-hidden="true"><?=h(mb_strtoupper(mb_substr($u['name'],0,1)))?></span><h2><?=h($u['name'])?></h2><p><?=h($u['email'])?></p><span class="verified-badge">✓ Verified account</span></div><nav class="profile-nav" aria-label="Account pages"><a class="active" aria-current="page" href="<?=url('customer/profile.php')?>">⌂ <span><?=h(tr('profile'))?></span></a><a href="<?=url('orders')?>">▣ <span><?=h(tr('orders'))?></span></a><a href="<?=url('wishlist')?>">♡ <span><?=h(tr('wishlist'))?></span></a></nav><form method="post" action="<?=url('auth/logout.php')?>"><input type="hidden" name="csrf" value="<?=csrf()?>"><button type="submit" class="btn btn-outline w-100"><?=h(tr('logout'))?></button></form></aside><div class="profile-content"><div class="profile-welcome"><div><span class="eyebrow">ACCOUNT OVERVIEW</span><h2>Welcome, <?=h($u['name'])?>.</h2><p>Your orders, saved watches and personal details in one place.</p></div><span class="profile-ornament" aria-hidden="true">T·S</span></div><div class="profile-stats"><a href="<?=url('orders')?>"><strong><?=$orderCount?></strong><span><?=h(tr('orders'))?> ↗</span></a><a href="<?=url('wishlist')?>"><strong><?=$wishCount?></strong><span><?=h(tr('wishlist'))?> ↗</span></a></div><section class="panel"><div class="profile-section-title"><h2>Personal details</h2><span class="verified-badge">✓ Gmail verified</span></div><p class="mini-meta">Your email address is used for sign in and verification codes.</p><p class="profile-email"><b>Email</b><span><?=h($u['email'])?></span></p><?php
if($error):
?><div class="flash error" role="alert"><?=h($error)?></div><?php
endif
?><div class="profile-name-row"><b>Name</b><span><?=h($u['name'])?></span><button class="text-link" type="button" id="profile-name-change" aria-expanded="<?=$error?'true':'false'?>" aria-controls="profile-name-form"><?=h(tr('change'))?></button></div><form method="post" id="profile-name-form" <?=$error?'':'hidden'?>><input type="hidden" name="csrf" value="<?=csrf()?>"><div class="field"><label for="profile-name">Name</label><input id="profile-name" name="name" value="<?=h($error?($_POST['name']??$u['name']):$u['name'])?>" maxlength="120" required></div><button class="btn btn-gold"><?=h(tr('save'))?></button></form></section><section class="panel"><div class="profile-section-title"><h2>Recent orders</h2><a class="text-link" href="<?=url('orders')?>"><?=h(tr('view_all'))?> ↗</a></div><?php
if($latest):
?><div class="recent-orders"><?php
foreach($latest as $order):
?><a href="<?=url('customer/order-details.php?id='.$order['id'])?>"><span><b><?=h($order['order_number'])?></b><small><?=h(date('d M Y',strtotime($order['created_at'])))?></small></span><span><?=h(ucfirst($order['status']))?> · <?=money($order['subtotal'])?> ↗</span></a><?php
endforeach
?></div><?php
else:
?><p class="mini-meta">Your orders will appear here after checkout.</p><a class="text-link" href="<?=url('shop')?>">Explore the collection ↗</a><?php
endif
?></section></div></main><?php
page_end();
