<?php
// Show the shop contact details and social links.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Define the supported social link destinations.
$socials=['social_facebook'=>['Facebook',['facebook.com','www.facebook.com','m.facebook.com']],'social_tiktok'=>['TikTok',['tiktok.com','www.tiktok.com']],'social_telegram'=>['Telegram',['t.me','www.t.me']],'social_x'=>['X',['x.com','www.x.com','twitter.com','www.twitter.com']]];
// Render the contact page.
page_start(tr('contact'));
?>
<div class="page-head"><div class="container"><span class="eyebrow">GET IN TOUCH</span><h1><?=h(tr('contact'))?></h1><p>Questions about a watch, delivery or an order? Reach out to our team.</p></div></div>
<main class="container section contact-layout"><section class="panel"><span class="eyebrow">DIRECT CONTACT</span><h2>We are here to help.</h2><p>Contact Timeless Sight to ask about a model, check availability or get help with an existing order. Include your order number when asking about a purchase.</p><div class="contact-method"><strong><?=h(tr('email'))?></strong><a href="mailto:<?=h(setting('contact_email'))?>"><?=h(setting('contact_email'))?></a></div><div class="contact-method"><strong><?=h(tr('phone'))?></strong><a href="tel:<?=h(preg_replace('/[^0-9+]/','',setting('contact_phone')))?>"><?=h(setting('contact_phone'))?></a></div></section><section class="panel"><span class="eyebrow">FOLLOW THE SHOP</span><h2>Social channels</h2><p>Find Timeless Sight on the channels below for collection updates.</p><div class="social-list"><?php
foreach($socials as $key=>[$label,$domains]):$link=trim(setting($key));
// Show a social link only when its HTTPS host is allowed.
$valid=$link&&filter_var($link,FILTER_VALIDATE_URL)&&parse_url($link,PHP_URL_SCHEME)==='https'&&in_array(strtolower((string)parse_url($link,PHP_URL_HOST)),$domains,true);
?><div class="social-row"><strong><?=h($label)?></strong><?php
if($valid):
?><a class="text-link" href="<?=h($link)?>" target="_blank" rel="noopener noreferrer">Visit <?=h($label)?> ↗</a><?php
else:
?><span class="mini-meta">Link coming soon</span><?php
endif
?></div><?php
endforeach
?></div></section></main><?php
page_end();
