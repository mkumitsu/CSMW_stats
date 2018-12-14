<?php
/**
 * @package ezstats3_wowp
 * @author ezzemm
 * @version 1
 */
/*
Plugin Name: ezStats3 for World of Warplanes
Plugin URI: http://www.ezstats.org
Description: Leaderboard for World of Warplanes. Use following term for the displaying of ezStats within your article: [ezstats3_wowp]
Author: ezzemm
Author URI: http://www.ezstats.org
*/

function include_ezstats3_wowp($text) {
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	$text = str_replace("[ezstats3_wowp]", $output, $text);
	return $text;
}

add_filter('the_content', 'include_ezstats3_wowp');
?>