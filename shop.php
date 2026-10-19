<?php
// Filter watches and select slides for the collection.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Read the requested brand slug safely.
$brand=substr(trim((string)($_GET['brand']??'')),0,140);
$brands=query('SELECT * FROM brands WHERE active=1 ORDER BY name')->fetchAll();
$chosen=null;
foreach($brands as $b)if($brand===$b['slug'])$chosen=$b;
if($brand!=='' && !$chosen){
    http_response_code(404);
    $brand='';
}
$params=[];
$where='w.active=1 AND b.active=1';
if($chosen){
    $where.=' AND b.id=?';
    $params[]=$chosen['id'];
}
// Load watches matching the active brand filter.
$watches=query("SELECT w.*,b.name AS brand,b.slug AS brand_slug FROM watches w JOIN brands b ON b.id=w.brand_id WHERE $where ORDER BY b.name,w.name",$params)->fetchAll();
/* On the all-brands page, show one pictured watch per brand in the slider. */
// Use available watch photos for the collection slides.
$withImages=array_values(array_filter($watches,fn($w)=>!empty($w['image_path'])));
if(!$chosen){
    $seen=[];
    $slides=[];
    foreach($withImages as $watch){
        if(!isset($seen[$watch['brand_slug']])){
            $slides[]=$watch;
            $seen[$watch['brand_slug']]=true;
        }
    }
}
else{
    $slides=$withImages;
}
$sampleSlides=['assets/images/slide-01.png','assets/images/slide-02.png','assets/images/slide-03.png'];
$useSamples=!$chosen && !$slides;
$headline=$chosen?$chosen['name']:'Discover your next watch';
page_start(tr('shop'));
?>
<section class="shop-hero" aria-label="Watch collection"><div class="shop-hero-inner"><div class="shop-hero-copy"><span class="eyebrow">THE COLLECTION</span><h1><?=h($headline)?></h1><p><?=count($watches)?> <?=count($watches)===1?'watch':'watches'?><?php
if(!$chosen):
?> · Casio · Citizen · Orient · Seiko<?php
endif
?></p><?php
if(!$chosen):
?><a class="shop-hero-cta" href="#shop-watches">Explore the collection ↓</a><?php
endif
?></div><div class="shop-carousel" data-shop-carousel><?php
if($useSamples):foreach($sampleSlides as $i=>$src):
?><div class="shop-slide <?=$i===0?'active':''?>"><img src="<?=url($src)?>" alt="Timeless Sight watch collection artwork"></div><?php
endforeach;
else:foreach($slides as $i=>$w):
?><div class="shop-slide <?=$i===0?'active':''?>"><a href="<?=url('watch/'.$w['id'])?>"><img src="<?=h(product_image($w['image_path']))?>" alt="<?=h($w['name'])?>"><span class="shop-slide-label"><?=h($w['brand'].' · '.$w['name'])?></span></a></div><?php
endforeach;
endif
?><?php
if($chosen && !$slides):
?><div class="shop-slide shop-slide-empty active"><strong aria-hidden="true">T·S</strong><span><?=h(tr('no_image'))?></span></div><?php
else:
?><div class="shop-carousel-controls"><button type="button" data-shop-step="-1" aria-label="Previous watch">‹</button><span class="shop-carousel-count">01 / <?=str_pad((string)($useSamples?3:count($slides)),2,'0',STR_PAD_LEFT)?></span><button type="button" data-shop-step="1" aria-label="Next watch">›</button></div><?php
endif
?></div></div></section>
<main class="container section" id="shop-watches"><?php
if(!$chosen):
?><div class="shop-heading"><h2>Browse watches</h2><p><?=count($watches)?> <?=count($watches)===1?'result':'results'?></p></div><?php
endif
?><nav class="chip-row" aria-label="Filter watches by brand"><a class="chip <?=$brand===''?'active':''?>" href="<?=url('shop')?>" <?=$brand===''?'aria-current="page"':''?>><?=h(tr('all'))?></a><?php
foreach($brands as $b):
?><a class="chip <?=$brand===$b['slug']?'active':''?>" href="<?=url('shop?brand='.rawurlencode($b['slug']))?>" <?=$brand===$b['slug']?'aria-current="page"':''?>><?=h($b['name'])?></a><?php
endforeach
?></nav><div class="watch-grid"><?php
foreach($watches as $w)product_card($w);
?></div><?php
if(!$watches):
?><p><?=h(tr('empty'))?></p><?php
endif
?></main><?php
page_end();
