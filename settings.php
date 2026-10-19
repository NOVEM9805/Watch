<?php
// Edit storefront contact details and page text.
require dirname(__DIR__).'/config/bootstrap.php';
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
$fields=['contact_email'=>'Contact email','contact_phone'=>'Contact phone','social_facebook'=>'Facebook URL','social_tiktok'=>'TikTok URL','social_telegram'=>'Telegram URL','social_x'=>'X URL','about_text'=>'About text','shipping_text'=>'Shipping information','returns_text'=>'Returns and warranty information'];
$error='';
// Validate submitted site settings.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    foreach($fields as $key=>$label)if(mb_strlen((string)($_POST[$key]??''))>4000)$error='Text is too long.';
    // Allow only supported HTTPS social links.
    $socialHosts=['social_facebook'=>['facebook.com','www.facebook.com','m.facebook.com'],'social_tiktok'=>['tiktok.com','www.tiktok.com'],'social_telegram'=>['t.me','www.t.me'],'social_x'=>['x.com','www.x.com','twitter.com','www.twitter.com']];
    foreach($socialHosts as $social=>$hosts){
        $link=trim((string)($_POST[$social]??''));
        if($link && (!filter_var($link,FILTER_VALIDATE_URL) || strtolower((string)parse_url($link,PHP_URL_SCHEME))!=='https' || !in_array(strtolower((string)parse_url($link,PHP_URL_HOST)),$hosts,true)))$error='Enter the correct https:// social profile URL for each channel.';
    }
    if(!$error){
        foreach($fields as $key=>$label)save_setting($key,trim((string)($_POST[$key]??'')));
        save_setting('catalog_ready',isset($_POST['catalog_ready'])?'1':'0');
        flash('Settings updated.');
        go('admin/settings.php');
    }
}
// Render the editable settings form.
page_start('Settings',true);
?><main class="container section"><?php
admin_nav();
?><h1>Store information</h1><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><form method="post" class="panel" style="max-width:700px"><input type="hidden" name="csrf" value="<?=csrf()?>"><?php
foreach($fields as $key=>$label):
?><div class="field"><label for="<?=$key?>"><?=h($label)?></label><?php
if(str_ends_with($key,'_text')):
?><textarea id="<?=$key?>" name="<?=$key?>"><?=h(setting($key))?></textarea><?php
else:
?><input id="<?=$key?>" name="<?=$key?>" value="<?=h(setting($key))?>"></div><?php
continue;
endif
?></div><?php
endforeach
?><div class="notice"><label><input type="checkbox" name="catalog_ready" value="1" <?=store_ready()?'checked':''?>> Enable customer orders</label><p>Orders are enabled with sample prices and stock for testing. Review actual prices, inventory and photos before accepting real orders. Uncheck this box to pause checkout.</p></div><button class="btn btn-gold">Save settings</button></form></main><?php
page_end();
