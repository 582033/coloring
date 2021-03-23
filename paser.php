<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class singleCurl {
    public function __construct(){
        $this->client = new Client();
    }

    public function request($picUrl){
        $picContent = $this->client->get($picUrl)->getBody()->getContents();
        return $picContent;
    }
}

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
