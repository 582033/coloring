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

//获取图片所有url, 并拼装成需要的
$picsParams = [];
foreach($picList as $picInfo){
    $picsParams[] = [
        'request' => 'GET',
        'alias' => $picInfo['name'],
        'url' => $picInfo['url'],
    ];
}
//echo "<pre>"; print_r($picsParams);exit;

echo "正在获取图片内容...\n";
//多线程获取图片内容
$curl = new multCurl;
$response = $curl->request($picsParams);
//echo "<pre>"; print_r($res);exit;
foreach($response as $picName => $picContent){
    echo "存储图片 {$picName} ...\n";
    $filePath = "{$savePath}/{$picName}";
    echo $filePath;
    file_put_contents($filePath, $picContent);
}
