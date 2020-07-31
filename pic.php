<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

//curl多线程请求
class multCurl {
    public function __construct(){
        $this->client = new Client();
    }

    public function request($request_list){
        $promises = [];
        foreach($request_list as $obj){
            $request = isset($obj['request']) ? $obj['request'] : 'GET';
            if( !isset($obj['url']) ){
                continue;
            }
            $alias = isset($obj['alias']) ? $obj['alias'] : $obj['url'];
            $requestData = isset($obj['data']) ? $obj['data'] : [];
            if( $request == 'GET' ){
                $url = $obj['url'];
                if( $requestData ) $url .= "?" . http_build_query($requestData);
                $result = $this->client->requestAsync($request, $url);
            }
            else{
                $result = $this->client->requestAsync($request, $obj['url'], $requestData);
            }
            $promises[$alias] = $result;
        }

        $results = Promise\unwrap($promises);

        $body = [];
        foreach($results as $alias => $res){
            $response = $res->getBody()->getContents();
            $body[$alias] = $response;
        }
        //echo "<pre>"; print_r($body);exit;

        return $body;
    }
}

class crawler {
    public function __construct($id){
        $this->client = new Client();
        $this->url = "http://www.coloring-book.info/coloring/coloring_page.php?id={$id}";
    }

    public function get(){
        //$c = file_get_contents($this->url);
        $res = $this->client->request('GET', $this->url);
        $c = $res->getBody()->getContents();
        $dom = HtmlDomParser::str_get_html( $c );

        $picList = [];
        foreach($dom->find('img') as $k => $img){
            $imgSrc = $img->src;
            if( strstr($imgSrc, 'thumbs') ){
                preg_match('/^(.*)\/thumbs\/(.*_m.jpg)$/', $imgSrc, $match);
                $title = rawurlencode($match[1]);
                $pic = str_replace('_m', '', $match[2]);
                $url = "http://www.coloring-book.info/coloring/{$title}/{$pic}";
                $picList[] = [
                    'name' => $pic,
                    'url' => $url,
                ];
            }
        }
        return $picList;
    }
}


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
    //echo $filePath;
    file_put_contents($filePath, $picContent);
}
