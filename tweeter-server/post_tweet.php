<?php
/*
 * Send the URL param ?tweet=URLENCODED
 */
header('Access-Control-Allow-Origin: *');


//ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');

require_once('config.php');

$tweet = htmlentities(urldecode($_GET['tweet']));


$url = 'https://api.twitter.com/1.1/statuses/update.json';

$postArray = array ( 'status'=>$tweet );

$requestMethod = 'POST';
$twitter = new TwitterAPIExchange($settings);
$t_res =  $twitter->setPostFields($postArray)
             ->buildOauth($url, $requestMethod)
             ->performRequest();
$result_object = json_decode($t_res);

var_dump($result_object); // send the result back to the client

?>