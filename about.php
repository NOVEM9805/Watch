<?php
// Render the bilingual About us page.
require dirname(__DIR__).'/config/bootstrap.php';
require dirname(__DIR__).'/includes/layout.php';
// Select the page copy for the current language.
$mm=language()==='mm';
// Open the customer layout for this page.
page_start(tr('about'));
?>
<div class="page-head about-head"><div class="container"><span class="eyebrow">TIMELESS SIGHT · OUR STORY</span><h1><?=h($mm?'ကျွန်ုပ်တို့အကြောင်း':'A watch for every moment.')?></h1><p><?=h($mm?'နေ့စဉ်ဘဝနဲ့ အမှတ်တရအချိန်တွေအတွက် နာရီကို စိတ်ကြိုက်ရွေးချယ်နိုင်တဲ့ နေရာ။':'A considered collection for everyday moments and memorable occasions.')?></p></div></div>
<main class="container section"><div class="about-layout"><section class="panel about-intro"><span class="eyebrow"><?=h($mm?'ကျွန်ုပ်တို့ရဲ့အကြောင်း':'WHO WE ARE')?></span><h2><?=h($mm?'အချိန်တိုင်းနဲ့ လိုက်ဖက်တဲ့ ရွေးချယ်မှု':'Time, made personal.')?></h2><?php
if($mm):
?><p>Timeless Sight မှာ Casio၊ Seiko၊ Citizen နဲ့ Orient နာရီတွေကို တစ်နေရာတည်းမှာ ကြည့်ရှုရွေးချယ်နိုင်ပါတယ်။ နေ့စဉ်ဝတ်ဖို့ နာရီတစ်လုံးဖြစ်ဖြစ်၊ အထူးနေ့အတွက် ရွေးချယ်တာဖြစ်ဖြစ် မိမိနှစ်သက်တဲ့ ပုံစံကို ရှာတွေ့နိုင်ဖို့ စီစဉ်ထားပါတယ်။</p><p>Brand အလိုက် ရှာနိုင်ပြီး model တစ်ခုချင်းစီရဲ့ အချက်အလက်၊ ဈေးနှုန်းနဲ့ ရရှိနိုင်မှုကို ကြည့်နိုင်ပါတယ်။ နှစ်သက်တဲ့နာရီကို wishlist ထဲမှာ သိမ်းထားပြီး နောက်မှ ပြန်နှိုင်းယှဉ်နိုင်ပါတယ်။</p><?php
else:
?><?php
// Show saved English paragraphs from site settings.
foreach(preg_split('/\R\s*\R/',setting('about_text')) as $paragraph):
?><p><?=h($paragraph)?></p><?php
endforeach
?><?php
endif
?><a class="btn btn-gold" href="<?=url('shop')?>"><?=h(tr('shop_now'))?> ↗</a></section><div class="about-aside"><div class="about-feature"><span>01 · <?=h($mm?'ရွေးချယ်ပါ':'EXPLORE')?></span><h3><?=h($mm?'Brand လေးခု၊ နာရီပုံစံမျိုးစုံ':'Find your style')?></h3><p><?=h($mm?'နေ့စဉ်ဝတ်နာရီ၊ sport ပုံစံနဲ့ အခမ်းအနားဝတ်ပုံစံတွေကို brand အလိုက် ရှာကြည့်နိုင်ပါတယ်။':'Explore everyday, sport and dress inspired watches across four familiar brands.')?></p></div><div class="about-feature"><span>02 · <?=h($mm?'မှာယူပါ':'ORDER')?></span><h3><?=h($mm?'မှာယူမှုကို သိရှိနိုင်ပါသည်':'A clear way to order')?></h3><p><?=h($mm?'ငွေသားဖြင့်ပေးချေမည့် order တင်ပြီးနောက် order အခြေအနေကို account မှာ ကြည့်နိုင်ပါတယ်။ ပို့ခကို အတည်ပြုပြီးမှ order details မှာ ပြပေးပါတယ်။':'Place a cash on delivery order and follow its progress from your account. The delivery fee is shown once confirmed for your address.')?></p></div><div class="about-feature"><span>03 · <?=h($mm?'ဆက်သွယ်ပါ':'CONTACT')?></span><h3><?=h($mm?'မေးမြန်းရန် လွယ်ကူပါသည်':'Here to help')?></h3><p><?=h($mm?'နာရီတစ်လုံးအကြောင်း၊ ရရှိနိုင်မှု သို့မဟုတ် order အကြောင်း မေးချင်ရင် ဆက်သွယ်နိုင်ပါတယ်။':'Questions about a model, availability or an existing order? Get in touch with us directly.')?></p><a class="text-link" href="<?=url('customer/contact.php')?>"><?=h(tr('contact'))?> ↗</a></div></div></div><div class="about-bottom"><div><span class="eyebrow">TIMELESS SIGHT</span><h2><?=h($mm?'သင့်အတွက် နာရီကို ရှာဖွေပါ':'Explore a watch that feels like yours.')?></h2></div><a class="btn btn-gold" href="<?=url('shop')?>"><?=h(tr('shop_now'))?> ↗</a></div></main><?php
page_end();
