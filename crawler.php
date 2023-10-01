<?php
require 'vendor/autoload.php';
include "db_conn.php";

use GuzzleHttp\Client;
use DOMDocument;

function getAllLinks ($url) {
    $client = new Client();
    
    try {

        $response = $client->get($url);
        
        if ($response->getStatusCode() === 200) {
            $html = $response->getBody()->getContents();
            
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $linkElements = $xpath->evaluate("/html/body//a");
            
            for($i=0;$i<$linkElements->length;$i++){
                $href = $linkElements->item($i);
                $url = $href->getAttribute('href');
                $url = filter_var($url, FILTER_SANITIZE_URL);
                if(!filter_var($url,FILTER_VALIDATE_URL) === false) {
                    $urlList[] = $url;
                }
            }
            
            return array_unique($urlList);
        } else {
            echo 'Error: Unable to fetch the web page. Status code: ' . $response->getStatusCode() . PHP_EOL;
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }
};

$crawlerPage = function ($url, $inputData) use ($conn) {
    try {
        $client = new Client();
        $videoUrlCount = 0;
        $imageUrlCount = 0;
        $linkUrlCount = 0;

            $response = $client->get($url);
            
            if ($response->getStatusCode() === 200) {
                $html = $response->getBody()->getContents();
                
                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new DOMXPath($dom);
        
                $titleUrl = $dom->getElementsByTagName('title')->item(0)->textContent;
                $imageElements = $dom->getElementsByTagName('img');
                $linkElements = $xpath->evaluate("/html/body//a");
                $videoElements = $dom->getElementsByTagName('video');
                $iframeElements = $dom->getElementsByTagName('iframe');
        
                $imageUrlCount += $imageElements->length;
                $linkUrlCount += $linkElements->length;
                $linkUrlCount += $imageElements->length;
                $videoUrlCount += $videoElements->length;
                $videoUrlCount += $iframeElements->length;

                $sql = "INSERT INTO `urls`(`id`, `url`, `title`, `count-image`, `count-link`, `count-video`)
                VALUES (NULL, '$url', '$titleUrl', '$imageUrlCount', '$linkUrlCount', '$videoUrlCount')";

                mysqli_query($conn, $sql);

            } else {
                echo 'Error: Unable to fetch the web page. Status code: ' . $response->getStatusCode() . PHP_EOL;
            }
            foreach ($inputData as $index => $value) {
                $imageCount = 0;
                $linkCount = 0;
                $videoCount = 0;
                
            $response = $client->get($value);
            
            if ($response->getStatusCode() === 200) {
                $html = $response->getBody()->getContents();
                
                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new DOMXPath($dom);
        
                $title = $dom->getElementsByTagName('title')->item(0)->textContent;
                $imageElements = $dom->getElementsByTagName('img');
                $linkElements = $xpath->evaluate("/html/body//a");
                $videoElements = $dom->getElementsByTagName('video');
                $iframeElements = $dom->getElementsByTagName('iframe');
        
                $imageCount += $imageElements->length;
                $linkCount += $linkElements->length;
                $linkCount += $imageElements->length;
                $videoCount += $videoElements->length;
                $videoCount += $iframeElements->length;

                $sql = "INSERT INTO `urls`(`id`, `url`, `title`, `count-image`, `count-link`, `count-video`)
                VALUES (NULL, '$value', '$title', '$imageCount', '$linkCount', '$videoCount')";

                mysqli_query($conn, $sql);

            } else {
                echo 'Error: Unable to fetch the web page. Status code: ' . $response->getStatusCode() . PHP_EOL;
            }
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }
};

$url = 'https://www.farsnews.ir/';
$dataFromFunctionGetAllLinks = getAllLinks($url);

$crawlerPage($url, $dataFromFunctionGetAllLinks);

?>