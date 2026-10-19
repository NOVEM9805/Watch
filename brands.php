<?php
// Manage brand details and open each brand's watches.
require dirname(__DIR__).'/config/bootstrap.php';
require_admin();
require dirname(__DIR__).'/includes/layout.php';
require dirname(__DIR__).'/includes/admin.php';
// Keep validation errors on this page.
$error='';
// Validate and save submitted brand details.
if($_SERVER['REQUEST_METHOD']==='POST'){
    post_only();
    $id=(int)($_POST['id']??0);
    $name=trim((string)($_POST['name']??''));
    $slug=strtolower(trim((string)($_POST['slug']??'')));
    $desc=trim((string)($_POST['description']??''));
    $active=isset($_POST['active'])?1:0;
    if(mb_strlen($name)<2||mb_strlen($name)>120||!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$slug)||strlen($slug)>140||mb_strlen($desc)>3000)$error='Check the name, URL slug and description.';
    else{
        try{
            if($id){
                query('UPDATE brands SET name=?,slug=?,description=?,active=? WHERE id=?',[$name,$slug,$desc,$active,$id]);
            }
            else query('INSERT INTO brands(name,slug,description,active) VALUES(?,?,?,?)',[$name,$slug,$desc,$active]);
            flash('Brand saved.');
            go('admin/brands.php');
        }
        catch(PDOException $e){
            $error='That brand name or slug is already in use.';
        }
    }
}
$edit=isset($_GET['edit'])?query('SELECT * FROM brands WHERE id=?',[(int)$_GET['edit']])->fetch():null;
// List brands with their watch counts.
$rows=query('SELECT b.*,COUNT(w.id) AS watches FROM brands b LEFT JOIN watches w ON w.brand_id=b.id GROUP BY b.id ORDER BY b.name')->fetchAll();
page_start('Brands',true);
?><main class="container section"><?php
admin_nav();
?><h1>Brands</h1><?php
if($error):
?><div class="flash error"><?=h($error)?></div><?php
endif
?><div class="split"><div class="table-wrap"><table><thead><tr><th>Brand</th><th>Slug</th><th>Watches</th><th>Visible</th><th></th></tr></thead><tbody><?php
foreach($rows as $r):
?><tr><td><a class="text-link" href="<?=url('admin/watches.php?brand='.$r['id'])?>"><?=h($r['name'])?> →</a></td><td><?=h($r['slug'])?></td><td><?=$r['watches']?></td><td><?=$r['active']?'Yes':'No'?></td><td><a class="text-link" href="<?=url('admin/brands.php?edit='.$r['id'])?>">Edit</a></td></tr><?php
endforeach
?></tbody></table></div><form class="panel" method="post"><h2><?=$edit?'Edit brand':'Add brand'?></h2><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$edit['id']??0?>"><div class="field"><label>Name</label><input name="name" required maxlength="120" value="<?=h($edit['name']??'')?>"></div><div class="field"><label>URL slug (lowercase, hyphens)</label><input name="slug" required pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?=h($edit['slug']??'')?>"></div><div class="field"><label>Description</label><textarea name="description"><?=h($edit['description']??'')?></textarea></div><label><input type="checkbox" name="active" <?=!$edit||$edit['active']?'checked':''?>> Visible in shop</label><p><button class="btn btn-gold">Save brand</button></p></form></div></main><?php
page_end();
