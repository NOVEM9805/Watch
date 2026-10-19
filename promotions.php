<?php
// Manage promotion slides shown on the storefront.
require dirname(__DIR__).'/config/bootstrap.php';
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
$error='';
// Handle slide deletion and save requests.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $id=(int)($_POST['id']??0);
    if(($_POST['action']??'')==='delete'){
        query('DELETE FROM promotions WHERE id=?',[$id]);
        flash('Slide removed.');
        go('admin/promotions.php');
    }
    $title=trim((string)($_POST['title']??''));
    $subtitle=trim((string)($_POST['subtitle']??''));
    $link=trim((string)($_POST['link_url']??'/shop'));
    $order=(int)($_POST['sort_order']??0);
    $active=isset($_POST['active'])?1:0;
    if(mb_strlen($title)<2||mb_strlen($title)>160||mb_strlen($subtitle)>240||!preg_match('#^/[a-zA-Z0-9/?&=._%-]*$#',$link)||str_starts_with($link,'//'))$error='Use a title and a local page link such as /shop.';
    else{
        try{
            $old=$id?query('SELECT image_path FROM promotions WHERE id=?',[$id])->fetch():null;
            if($id&&!$old)throw new RuntimeException('Slide not found.');
            // Keep the existing image when no new one is uploaded.
            $image=upload_image('image','promotions')??($old['image_path']??null);
            if(!$image)throw new RuntimeException('Choose an image.');
            if($id)query('UPDATE promotions SET title=?,subtitle=?,link_url=?,sort_order=?,active=?,image_path=? WHERE id=?',[$title,$subtitle,$link,$order,$active,$image,$id]);
            else query('INSERT INTO promotions(title,subtitle,link_url,sort_order,active,image_path) VALUES(?,?,?,?,?,?)',[$title,$subtitle,$link,$order,$active,$image]);
            flash('Slide saved.');
            go('admin/promotions.php');
        }
        catch(Throwable $e){
            error_log($e->getMessage());
            $error=$e instanceof PDOException?'Could not save slide.':$e->getMessage();
        }
    }
}
$edit=isset($_GET['edit'])?query('SELECT * FROM promotions WHERE id=?',[(int)$_GET['edit']])->fetch():null;
// Load slides in their display order.
$rows=query('SELECT * FROM promotions ORDER BY sort_order,id')->fetchAll();
page_start('Promotions',true);
?><main class="container section"><?php
admin_nav();
?><h1>Promotions / Events</h1><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><div class="split"><div class="table-wrap"><table><thead><tr><th>Image</th><th>Title</th><th>Order</th><th>Visible</th><th></th></tr></thead><tbody><?php
foreach($rows as $r):
?><tr><td><img src="<?=h(product_image($r['image_path']))?>" alt=""></td><td><?=h($r['title'])?></td><td><?=$r['sort_order']?></td><td><?=$r['active']?'Yes':'No'?></td><td><a class="text-link" href="<?=url('admin/promotions.php?edit='.$r['id'])?>">Edit</a> <form class="inline-form" method="post" onsubmit="return confirm('Remove this slide?')"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="danger" type="submit">Delete</button></form></td></tr><?php
endforeach
?></tbody></table></div><form class="panel" method="post" enctype="multipart/form-data"><h2><?=$edit?'Edit slide':'Add slide'?></h2><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$edit['id']??0?>"><div class="field"><label>Title</label><input name="title" required maxlength="160" value="<?=h($edit['title']??'')?>"></div><div class="field"><label>Short text</label><input name="subtitle" maxlength="240" value="<?=h($edit['subtitle']??'')?>"></div><div class="field"><label>Link (local path)</label><input name="link_url" value="<?=h($edit['link_url']??'/shop')?>" required></div><div class="field"><label>Order</label><input type="number" name="sort_order" value="<?=h((string)($edit['sort_order']??0))?>"></div><div class="field"><label>Banner image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?=$edit?'':'required'?>></div><label><input type="checkbox" name="active" <?=!$edit||$edit['active']?'checked':''?>> Visible</label><p><button class="btn btn-gold">Save slide</button></p></form></div></main><?php
page_end();
