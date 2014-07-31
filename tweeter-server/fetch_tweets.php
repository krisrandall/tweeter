<?php

header('Access-Control-Allow-Origin: *');


//ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');

require_once('config.php');



$max_id = htmlentities($_GET['max_id']); // for fetching older tweets - this is the oldest id to return
$result_type = htmlentities('recent'); // could also be 'popular' or 'mixed'
$count = intval('5'); // number of tweets to return -- NB: don't display the last one !
// NB: Testing reveals that the Twitter API randomly fails (actually returns a single result instead of the whole set, when using a higher count - certainly 10 or greater - so far lower numbers seem fine)

/** Perform a GET request and echo the response **/
/** Note: Set the GET field BEFORE calling buildOauth(); **/
$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = mysql_real_escape_string("?q=$q&max_id=$max_id&result_type=$result_type&count=$count");
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$t_res =  $twitter->setGetfield($getfield)
             ->buildOauth($url, $requestMethod)
             ->performRequest();
$result_object = json_decode($t_res);


// lets display all the tweets nicely :

//echo "<h1>".$result_object->statuses[0]->text."</h1>";


$returned = 0;
# Example output
if(!empty($result_object)) {

	
    foreach($result_object->statuses as $tweet) {

		$returned++;
		
		if ($max_id!=$tweet->id) {


			if (!isset($min_id_returned))
				$min_id_returned = $tweet->id;
			else 
				$min_id_returned = $tweet->id; //min($min_id_returned, $tweet->id);
				
				
			// check for the existance of a #c:? in the tweet which specifies one of the 9 different colours to show
			$col = stristr($tweet->text, '#c:');
			if ($col) {
				$col = substr($col, 3, 1); // gets the single digit following #c which is our colour bubble index
				$col = "c$col";
				
				// strip that baby out of there now, don't want to display it, it is secret meta info
				$start_pos_meta = strpos($tweet->text, '#c:');
				$end_pos_meta = $start_pos_meta + 4;
				$tweet->text = substr($tweet->text, 0, $start_pos_meta).substr($tweet->text, $end_pos_meta);
			} else {
				// convert the name of the poster into one of 1 to F via a hash,
				// so their is (some) variety in the bubbles for different users
				// and constancy for each user
				$col = substr(hash('md5', $tweet->user->name), 0, 1); 
				$col = "c$col ({$tweet->user->name})";
			}
			
			
			echo "
				<div class=\"tweet $col\">
			
			
					<table>
						<tr>
							<td rowspan=\"3\">
								<img src=\"".strip_tags($tweet->user->profile_image_url)."\">
							</td>
							<td>
								<em>{$tweet->user->name}</em>
							</td>
							<td align=\"right\">
							
							</td>
						</tr>
						<tr>
							<!-- td rowspan -->
							<td colspan=\"2\" class=\"tweet_text\">
								{$tweet->text}
							</td>
							<!-- td colspan -->
						</tr>
						<tr>
							<!-- td rowspan 00>
							<td colspan=\"2\">
								<!-- gap -->
							</td>
							<!-- td colspan -->
						</tr>
					</table>
			
			
				</div> ";
	 
	    }
		
			
		}

}


if ($returned==$count) {
	echo "<a href=\"tweeter-server/fetch_tweets.php?max_id=$min_id_returned\">Get next $count posts</a>";
} else {
	// Thats the end of them ...
	//echo "$returned<$count";
}

/*
echo '<pre>';
var_dump( $result_object );
echo '</pre>';
*/



// for sending a tweet see : https://dev.twitter.com/docs/api/1.1/post/statuses/update
