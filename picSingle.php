<?php
require_once __DIR__ . '/paser.php';

if( !isset($argv[1]) ){
    exit("params error, exam:php pic.php <pic_id>\n");
}
$id = (int)$argv[1];
if( !$id || $id <= 0 ){
    exit('params id error');
}
$savePath = "./files";

//创建目录
if( !file_exists($savePath) ){
    mkdir($savePath);
    echo "存储目录{$savePath}创建成功\n";
}

echo "正在获取所有图片链接...\n";
//获取id页面内的所有图片url
$crawler = new crawler($id);
$picList = $crawler->get();
//echo "<pre>"; print_r($picList);exit;

echo "正在获取图片内容...\n";
$curl = new singleCurl;
foreach($picList as $picInfo){
    $picName = $picInfo['name'];
    $picUrl = $picInfo['url'];
    echo "存储图片 {$picName} ...\n";
    $picContent = $curl->request($picUrl);
    $filePath = "{$savePath}/{$picName}";
    file_put_contents($filePath, $picContent);
}
