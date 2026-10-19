<?php
// Manage watches within the selected brand.
require dirname(__DIR__).'/config/bootstrap.php';
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
$error='';
$brands=query('SELECT id,name FROM brands ORDER BY name')->fetchAll();
/* A watch is always edited within a selected brand. */
$brandId=(int)($_GET['brand']??$_POST['filter_brand']??0);
// Reject an unknown brand before editing watches.
$selectedBrand=$brandId?query('SELECT id,name FROM brands WHERE id=?',[$brandId])->fetch():false;
if(!$selectedBrand)go('admin/brands.php');
// Validate and save a watch for this brand.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $id=(int)($_POST['id']??0);
    $brand=$brandId;
    $name=trim((string)($_POST['name']??''));
    $model=trim((string)($_POST['model']??''));
    $description=trim((string)($_POST['description']??''));
    $price=(string)($_POST['price']??'0');
    $stock=filter_var($_POST['stock']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>100000]]);
    $featured=isset($_POST['featured'])?1:0;
    $active=isset($_POST['active'])?1:0;
    if(!query('SELECT id FROM brands WHERE id=?',[$brand])->fetch()||mb_strlen($name)<2||mb_strlen($name)>180||mb_strlen($model)>100||mb_strlen($description)>4000||!is_numeric($price)||(float)$price<0||(float)$price>9999999999||$stock===false)$error='Check watch details, price and stock.';
    else{
        try{
            $old=$id?query('SELECT image_path FROM watches WHERE id=?',[$id])->fetch():null;
            if($id&&!$old)throw new RuntimeException('Watch not found.');
            /* Prevent editing a watch through a different brand URL. */
            if($id && !query('SELECT 1 FROM watches WHERE id=? AND brand_id=?',[$id,$brand])->fetchColumn())throw new RuntimeException('Watch does not belong to this brand.');
            $image=upload_image('image','watches')??($old['image_path']??null);
            if($id)query('UPDATE watches SET brand_id=?,name=?,model=?,description=?,price=?,stock=?,image_path=?,featured=?,active=? WHERE id=?',[$brand,$name,$model,$description,$price,$stock,$image,$featured,$active,$id]);
            else query('INSERT INTO watches(brand_id,name,model,description,price,stock,image_path,featured,active) VALUES(?,?,?,?,?,?,?,?,?)',[$brand,$name,$model,$description,$price,$stock,$image,$featured,$active]);
            flash('Watch saved.');
            go('admin/watches.php?brand='.$brand);
        }
        catch(Throwable $e){
            error_log($e->getMessage());
            $error=$e instanceof PDOException?'Could not save watch.':$e->getMessage();
        }
    }
}
$edit=isset($_GET['edit'])?query('SELECT * FROM watches WHERE id=? AND brand_id=?',[(int)$_GET['edit'],$brandId])->fetch():null;
$filter=$brandId?'WHERE w.brand_id=?':'';
// List watches belonging to the selected brand.
$rows=query('SELECT w.*,b.name AS brand FROM watches w JOIN brands b ON b.id=w.brand_id '.$filter.' ORDER BY b.name,w.name',$brandId?[$brandId]:[])->fetchAll();
page_start('Watches',true);
?><main class="container section"><?php
admin_nav();
?><p><a class="text-link" href="<?=url('admin/brands.php')?>">← Brands</a></p><h1><?=h($selectedBrand['name'])?> watches</h1><p class="notice">Example prices and stock are populated for demonstration. Review every value, add product photos, before accepting real orders.</p><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><div class="split"><div class="table-wrap"><table><thead><tr><th>Watch</th><th>Brand</th><th>Price</th><th>Stock</th><th>Visible</th><th></th></tr></thead><tbody><?php
foreach($rows as $w):
?><tr><td><?=h($w['name'])?></td><td><?=h($w['brand'])?></td><td><?=money($w['price'])?></td><td><?=$w['stock']?></td><td><?=$w['active']?'Yes':'No'?></td><td><a class="text-link" href="<?=url('admin/watches.php?brand='.$w['brand_id'].'&edit='.$w['id'])?>">Edit</a></td></tr><?php
endforeach
?></tbody></table></div><form class="panel" method="post" enctype="multipart/form-data"><h2><?=$edit?'Edit watch':'Add watch'?></h2><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$edit['id']??0?>"><input type="hidden" name="filter_brand" value="<?=$brandId?>"><div class="field"><label>Watch name</label><input name="name" required maxlength="180" value="<?=h($edit['name']??'')?>"></div><div class="field"><label>Model</label><input name="model" maxlength="100" value="<?=h($edit['model']??'')?>"></div><div class="field"><label>Description</label><textarea name="description"><?=h($edit['description']??'')?></textarea></div><div class="field"><label>Price (MMK)</label><input type="number" min="0" step="1" name="price" required value="<?=h((string)($edit['price']??0))?>"></div><div class="field"><label>Stock</label><input type="number" min="0" name="stock" required value="<?=h((string)($edit['stock']??0))?>"></div><div class="field"><label>Watch image (JPG, PNG, WEBP; max 4 MB)</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp"><?php
if(!empty($edit['image_path'])):
?><img style="width:120px;margin-top:8px" src="<?=h(product_image($edit['image_path']))?>" alt="Current image"><?php
endif
?></div><p><label><input type="checkbox" name="featured" <?=!empty($edit['featured'])?'checked':''?>> Featured</label> &nbsp; <label><input type="checkbox" name="active" <?=!$edit||$edit['active']?'checked':''?>> Visible</label></p><button class="btn btn-gold">Save watch</button></form></div></main><?php
page_end();
