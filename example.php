<?php
require_once "vendor/autoload.php";

use Goutte\Client;

define('BASE_URL', 'https://vnexpress.net');

function getAllLinkMenu($url)
{
    $client = new Client();
    $crawl = $client->request('GET', $url);
    $links = $crawl->filter('nav ul li a')->each(function ($node, $i) {
        return $node->attr('href');
    });
    return $links;
};
function filterUrl(&$links)
{

    //remove first link and last link
    unset($links[5]);
    array_pop($links);
    array_shift($links);
    $links = array_map(function ($endpoint) {
        return BASE_URL . $endpoint;
    }, $links);
}
// $client = new Client();

// $crawl = $client->request('GET', 'https://vnexpress.net/');
// $file = fopen('scrap-vnexpress.csv', 'w');
// $header = array('title', 'short-description');
// fputcsv($file, $header);
// $posts = $crawl->filterXPath('//*[@id="wrap-main-nav"]/nav/ul/li')->each(function ($node, $i) {
//     try{$client = new Client();
//     $crawler = $client->click($node->filter('a')->link());
//     $section1 = $crawler->filter('body > section.section.section_topstory > div > div > div > div > div > div > ul > li:nth-child(1) > h2 > a');
//     print_r($section1);
//     exit;}
//     catch (\Throwable $th){
//         exit;
//     }
// });
// foreach ($posts as $post) 
// {
//     fputcsv($file, $post);
// }
function getPostOnePage($link)
{
    $client = new Client();
    $crawl = $client->request('GET', $link);
    $posts = $crawl->filter('article')->each(function ($node) {
        try {
            $post['title'] = $node->filter('.title-news')->text();
            $post['description'] = $node->filter('.description')->text();
            $post['link'] = $node->filter('h3 a')->attr('href');
        } catch (Exception $e) {
            $post = null;
        }
        return $post;
    });
    return $posts;
}
function getFullPosts($posts){
    echo('get full post');
    $finalPosts = array();
    $i=0;
    foreach($posts as $post){
        echo $i++; 
        try{
            $client = new Client();
            $crawl = $client->request('GET',$post['link']);
            $post['detail'] = $crawl->filter('.fck_detail')->html();
            $post['date']= $crawl->filter('span.date')->text();
            $post['auth']= $crawl->filter('p.Normal strong')->text();
            $post['location'] = $crawl->filter('.location-stamp')->text();
        }
        catch(Exception $e){
        }
        array_push($finalPosts,$post);
    }
    return $finalPosts;
}
$links = getAllLinkMenu(BASE_URL);
filterUrl($links);
$posts = array();
foreach ($links as $link) {
    $posts = array_merge(getPostOnePage($link), $posts);
}
$keys = array_keys($posts,null);
foreach($keys as $key){
    unset($posts[$key]);
}
$finalPosts = getFullPosts($posts);
$file = fopen('vnexpress.vn','w');
fputcsv($file,["title","description","link","detail","date","auth","auth"]);
foreach($finalPosts as $post){
    fputcsv($file,$post);
}
fclose($file);