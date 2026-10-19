<?php
// Render the dedicated administrator page layout.
declare(strict_types=1);
// Open the admin page with its header and flash notice.
function admin_page_start(string $title): void {
    $u=current_user();
    // Consume a pending one-time notice.
    $flash=take_flash();
?><!doctype html><html lang="<?=language()==='mm'?'my':'en'?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> · Timeless Sight Admin</title><link rel="icon" type="image/svg+xml" href="<?=url('assets/images/favicon.svg')?>"><link rel="stylesheet" href="<?=url('assets/css/style.css')?>"><script defer src="<?=url('assets/js/main.js')?>"></script></head><body class="admin-body" data-base="<?=h(url(''))?>"><header class="admin-header"><a class="admin-logo" href="<?=url('admin/dashboard.php')?>"><span class="brand-symbol">T<span>·</span>S</span><span>TIMELESS SIGHT <small>ADMIN</small></span></a><div class="admin-user"><?php
if($u):
?><span><?=h($u['name'])?></span><form action="<?=url('auth/logout.php')?>" method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><button class="admin-signout" type="submit">Sign out</button></form><?php
endif
?></div></header><?php
if($flash):
?><div class="flash <?=h($flash['kind'])?>" role="status"><?=h($flash['message'])?></div><?php
endif
?><?php
}
// Close the administrator page markup.
function admin_page_end(): void {
?></body></html><?php
}
