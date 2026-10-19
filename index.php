<?php
// Render the customer home page.
require __DIR__.'/config/bootstrap.php';
require __DIR__.'/includes/layout.php';
// Load active homepage promotions in display order.
$slides=query('SELECT * FROM promotions WHERE active=1 ORDER BY sort_order,id')->fetchAll();
// Load visible brands and their watch counts.
$brands=query('SELECT b.*,COUNT(w.id) AS watch_count FROM brands b LEFT JOIN watches w ON w.brand_id=b.id AND w.active=1 WHERE b.active=1 GROUP BY b.id ORDER BY b.name')->fetchAll();
// Load featured watches for the homepage.
$featured=query('SELECT w.*,b.name AS brand FROM watches w JOIN brands b ON b.id=w.brand_id WHERE w.active=1 AND b.active=1 AND w.featured=1 ORDER BY w.id LIMIT 8')->fetchAll();
$new=query('SELECT w.*,b.name AS brand FROM watches w JOIN brands b ON b.id=w.brand_id WHERE w.active=1 AND b.active=1 ORDER BY w.created_at DESC,w.id DESC LIMIT 4')->fetchAll();
page_start(tr('home'));
?>
<?php
if($slides):
?><section class="hero" aria-label="Promotions"><?php
foreach($slides as $i=>$s):
?><div class="slide <?=$i===0?'active':''?>"><img src="<?=h(product_image($s['image_path']))?>" alt=""><div class="hero-shade"></div><div class="hero-content"><span class="eyebrow">TIMELESS SIGHT · COLLECTION</span><h1><?=h($s['title'])?></h1><p><?=h($s['subtitle'])?></p><a class="btn btn-gold" href="<?=url(ltrim($s['link_url'],'/'))?>"><?=h(tr('shop_now'))?> ↗</a></div></div><?php
endforeach
?><div class="hero-controls"><?php
foreach($slides as $i=>$s):
?><button type="button" class="<?=$i===0?'active':''?>" aria-label="Slide <?=$i+1?>"></button><?php
endforeach
?></div><?php
if(count($slides)>1):
?><div class="hero-arrows"><button type="button" data-slide-step="-1" aria-label="Previous slide">‹</button><button type="button" data-slide-step="1" aria-label="Next slide">›</button></div><?php
endif
?></section><?php
else:
?><section class="page-head"><div class="container"><h1>Timeless Sight</h1><p><?=h(tr('shop_now'))?></p><a class="btn btn-gold" href="<?=url('shop')?>"><?=h(tr('shop_now'))?></a></div></section><?php
endif
?>
<main class="container"><section class="section"><?php
section_heading(tr('brands'),'shop');
?><div class="brand-grid"><?php
foreach($brands as $b):
?><a class="brand-tile" href="<?=url('shop?brand='.rawurlencode($b['slug']))?>"><strong><?=h($b['name'])?></strong><span><?=$b['watch_count']?> WATCHES ↗</span></a><?php
endforeach
?></div></section>
<section class="section"><?php
section_heading(tr('featured'),'shop');
?><div class="watch-grid"><?php
foreach($featured as $w)product_card($w);
?></div></section>
<section class="section"><?php
section_heading(tr('new'),'shop');
?><div class="watch-grid"><?php
foreach($new as $w)product_card($w);
?></div></section></main><?php
page_end();
