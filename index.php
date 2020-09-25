<?php
/* Getting working proxy servers according to the list of URl sites with proxy lists.
 * Author:   X-Vlad
 * Website: https://github.com/X-Vlad/Proxy-Grabber/
 * Date:    20.09.2020
*/

set_time_limit(600);
ini_set('memory_limit', '512M');

require __DIR__ . '/vendor/autoload.php';

use Curl\MultiCurl;

$proxy_file = "proxy_" . date('d_m_Y-H_i_s') . ".txt";
$proxy_count = 0;

$multi_curl = new MultiCurl();
$multi_curl->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/' . mt_rand(76, 85) . '.0.' . mt_rand(3678, 4183) . '.' . mt_rand(76, 85) . ' Safari/537.36');

$multi_curl->success(function ($multi_instance) {
    global $proxy_count, $proxy_file;

    $result = $multi_instance->response;

    $pattern = "/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}:[0-9]{2,5}/"; 
    preg_match_all($pattern, $result, $matches);

    if (isset($matches[0]) and count($matches[0]) > 0){
        $proxy_count += count($matches[0]);

        file_put_contents($proxy_file, implode("\r\n", $matches[0]) . "\r\n", FILE_APPEND);

        echo 'URL "' . $multi_instance->url . '" successfully processed. Found ' . count($matches[0]) . ' proxies.' . "\r\n";
    } else {
        echo 'URL "' . $multi_instance->url . '" successfully processed. No proxy found!' . "\r\n";
    }
});

$multi_curl->error(function ($multi_instance) {
    echo 'URL "' . $multi_instance->url . '" not working.' . "\r\n";
});

$url_list = file('url.txt'); // url.txt

if (PHP_SAPI !== 'cli') echo "<pre>";

if (is_array($url_list) and count($url_list) > 0){
    $count_url = 0;

    foreach ($url_list as $url){
        if (strlen($url) > 12){
            $url = str_replace(array("\r", "\n"), '', $url);
            $multi_curl->addGet($url);
            $count_url++;
        }
    }

    echo "Added {$count_url} links.\r\n";
    echo "The process of collecting proxies has started...\r\n";
    
    $multi_curl->start();
    
    if ($proxy_count > 0){
        echo "Proxies collection process completed successfully! Found {$proxy_count} proxies. Saved to file: {$proxy_file}";
    } else {
        echo "Proxies collection process completed successfully! No proxy found!";
    }
} else {
    echo "File url.txt is empty!";
}

if (PHP_SAPI !== 'cli') echo "<pre>";
