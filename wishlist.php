<?php
// Show and remove saved customer watches.
require dirname(__DIR__).'/config/bootstrap.php';
$u=require_login();
require dirname(__DIR__).'/includes/layout.php';
// Remove a saved watch after form validation.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    query('DELETE FROM wishlist WHERE user_id=? AND watch_id=?',[$u['id'],(int)($_POST['watch_id']??0)]);
    go('wishlist');
}
// Load currently available saved watches.
$rows=query('SELECT w.*,b.name AS brand FROM wishlist x JOIN watches w ON w.id=x.watch_id JOIN brands b ON b.id=w.brand_id WHERE x.user_id=? AND w.active=1 ORDER BY x.watch_id DESC',[$u['id']])->fetchAll();
// Render the wishlist and empty state.
page_start(tr('wishlist'));
?><div class="page-head"><div class="container"><h1><?=h(tr('wishlist'))?></h1></div></div><main class="container section"><div class="watch-grid"><?php
foreach($rows as $w):
?><div><?php
product_card($w);
?><form method="post" style="margin-top:8px"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="watch_id" value="<?=$w['id']?>"><button class="btn btn-outline"><?=h(tr('remove'))?></button></form></div><?php
endforeach
?></div><?php
if(!$rows):
?><p><?=h(tr('empty'))?></p><?php
endif
?></main><?php
page_end();
