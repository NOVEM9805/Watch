<?php
// Return matching brands and watches as JSON.
require dirname(__DIR__).'/config/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
// Read and limit the search text.
$q=trim((string)($_GET['q']??''));
if($q==='' || mb_strlen($q)>60){
    echo '[]';
    exit;
}
$prefix=str_replace(['\\','%','_'],['\\\\','\\%','\\_'],$q).'%';
// Search active brands by name prefix.
$brands=query("SELECT name,slug FROM brands WHERE active=1 AND name LIKE ? ESCAPE '\\\\' ORDER BY name LIMIT 8",[$prefix])->fetchAll();
// Search active watches by name prefix.
$watches=query("SELECT w.id,w.name FROM watches w JOIN brands b ON b.id=w.brand_id WHERE w.active=1 AND b.active=1 AND w.name LIKE ? ESCAPE '\\\\' ORDER BY w.name LIMIT 12",[$prefix])->fetchAll();
$results=[];
foreach($brands as $b)$results[]=['name'=>$b['name'],'type'=>'Brand','url'=>url('shop?brand='.rawurlencode($b['slug']))];
foreach($watches as $w)$results[]=['name'=>$w['name'],'type'=>'Watch','url'=>url('watch/'.$w['id'])];
usort($results,fn($a,$b)=>strnatcasecmp($a['name'],$b['name']));
echo json_encode(array_slice($results,0,12),JSON_UNESCAPED_UNICODE);
