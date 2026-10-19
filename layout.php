<?php
// Render shared customer pages and product cards.
declare(strict_types=1);
// Open the customer page or delegate to the admin layout.
function page_start(string $title, bool $admin=false): void {
    global $adminPage;
    $adminPage=$admin;
    if($admin){
        require_once __DIR__.'/admin-layout.php';
        admin_page_start($title);
        return;
    }
    $u=current_user();
    $flash=take_flash();
    $name='Timeless Sight';
?><!doctype html><html lang="<?=language()==='mm'?'my':'en'?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Discover watches by brand at Timeless Sight."><title><?=h($title)?> · <?=$name?></title><link rel="icon" type="image/svg+xml" href="<?=url('assets/images/favicon.svg')?>"><link rel="stylesheet" href="<?=url('assets/css/style.css')?>"><script defer src="<?=url('assets/js/main.js')?>"></script></head><body data-base="<?=h(url(''))?>">
 <div class="topline">TIMELESS SIGHT</div>
 <header class="site-header"><a class="brandmark" href="<?=url('')?>"><span class="brand-symbol">T<span>·</span>S</span><span>TIMELESS <em>SIGHT</em></span></a>
 <button class="mobile-toggle" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
 <nav class="main-nav" aria-label="Main"><a href="<?=url('')?>"><?=h(tr('home'))?></a><a href="<?=url('shop')?>"><?=h(tr('shop'))?></a><a href="<?=url('customer/about.php')?>"><?=h(tr('about'))?></a><a href="<?=url('customer/contact.php')?>"><?=h(tr('contact'))?></a><form class="language-switch mobile-language" action="<?=url('customer/language.php')?>" method="post" aria-label="<?=h(tr('language'))?>"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="back" value="<?=h($_SERVER['REQUEST_URI']??'/')?>"><button type="submit" name="lang" value="en" class="<?=language()==='en'?'selected':''?>" aria-label="English" <?=language()==='en'?'aria-pressed="true"':'aria-pressed="false"'?>>EN</button><button type="submit" name="lang" value="mm" class="<?=language()==='mm'?'selected':''?>" aria-label="မြန်မာ" <?=language()==='mm'?'aria-pressed="true"':'aria-pressed="false"'?>>မြန်မာ</button></form></nav>
 <div class="header-actions"><div class="live-search"><label class="sr-only" for="site-search"><?=h(tr('search'))?></label><input id="site-search" type="search" placeholder="<?=h(tr('search'))?>" autocomplete="off" aria-controls="search-results" aria-expanded="false"><div id="search-results" class="suggestions" role="listbox" hidden></div></div><form class="language-switch desktop-language" action="<?=url('customer/language.php')?>" method="post" aria-label="<?=h(tr('language'))?>"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="back" value="<?=h($_SERVER['REQUEST_URI']??'/')?>"><button type="submit" name="lang" value="en" class="<?=language()==='en'?'selected':''?>" aria-label="English" <?=language()==='en'?'aria-pressed="true"':'aria-pressed="false"'?>>EN</button><button type="submit" name="lang" value="mm" class="<?=language()==='mm'?'selected':''?>" aria-label="မြန်မာ" <?=language()==='mm'?'aria-pressed="true"':'aria-pressed="false"'?>>မြန်မာ</button></form><a class="header-wishlist" aria-label="<?=h(tr('wishlist'))?>" href="<?=url('wishlist')?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg></a><a class="header-account" aria-label="<?=h(tr('account'))?>" href="<?=url($u?'customer/profile.php':'auth/login.php')?>"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></a><a class="header-cart" aria-label="<?=h(tr('cart'))?>" href="<?=url('cart')?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h2l2.4 11h11.8l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span class="cart-count"><?=cart_count()?></span></a></div></header>
 <?php
if($flash):
?><div class="flash <?=h($flash['kind'])?>" role="status"><?=h($flash['message'])?></div><?php
endif;
?>
 <?php
}
// Close the current page layout.
function page_end(): void {
    global $adminPage;
    if($adminPage){
        admin_page_end();
        return;
    }
?><footer class="site-footer"><div><a class="brandmark" href="<?=url('')?>">TIMELESS <em>SIGHT</em></a><p>Watches for every moment.</p></div><div><strong>Explore</strong><a href="<?=url('shop')?>"><?=h(tr('shop'))?></a><a href="<?=url('customer/about.php')?>"><?=h(tr('about'))?></a><a href="<?=url('customer/contact.php')?>"><?=h(tr('contact'))?></a><a href="mailto:<?=h(setting('contact_email'))?>"><?=h(setting('contact_email'))?></a></div><div><strong>Help</strong><a href="<?=url('customer/shipping.php')?>"><?=h(tr('shipping'))?></a><a href="<?=url('customer/returns.php')?>"><?=h(tr('returns'))?></a><a href="<?=url('orders')?>"><?=h(tr('orders'))?></a></div></footer></body></html><?php
}
// Render a watch preview with price and photo.
function product_card(array $w): void {
?><article class="watch-card"><a class="watch-picture" href="<?=url('watch/'.$w['id'])?>"><?php
if($w['image_path']):
?><img loading="lazy" src="<?=h(product_image($w['image_path']))?>" alt="<?=h($w['name'])?>"><?php
else:
?><span class="no-photo"><b>TS</b><small><?=h(tr('no_image'))?></small></span><?php
endif
?></a><div class="watch-card-body"><span class="eyebrow"><?=h($w['brand'])?></span><a class="watch-name" href="<?=url('watch/'.$w['id'])?>"><?=h($w['name'])?></a><div class="card-bottom"><strong><?=(float)$w['price']>0?money($w['price']):h(tr('price_pending'))?><?php
if(!store_ready() && (float)$w['price']>0):
?><small class="price-note"><?=h(tr('sample_price'))?></small><?php
endif
?></strong><a href="<?=url('watch/'.$w['id'])?>" aria-label="<?=h(tr('details').' '.$w['name'])?>">↗</a></div></div></article><?php
}
// Render a section title with an optional browse link.
function section_heading(string $title,string $link=''): void {
?><div class="section-heading"><h2><?=h($title)?></h2><?php
if($link):
?><a href="<?=url($link)?>"><?=h(tr('view_all'))?> ↗</a><?php
endif
?></div><?php
}
