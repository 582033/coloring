<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunra\PhpSimple\HtmlDomParser;

$url = "http://www.coloring-book.info/coloring/coloring_page.php?id=315";
$c = file_get_contents($url);

$dom = HtmlDomParser::str_get_html( $c );
foreach($dom->find('img') as $img){
    $img_src = $img->src;
    if( strstr($img_src, 'thumbs') ){
        preg_match('/^(.*)\/thumbs\/(.*_m.jpg)$/', $img_src, $match);
        $title = rawurlencode($match[1]);
        $pic = str_replace('_m', '', $match[2]);
        echo "http://www.coloring-book.info/coloring/{$title}/{$pic}\n";
    }
}

