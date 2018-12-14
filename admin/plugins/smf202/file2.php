<?php
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;
	
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	echo '
		<h3 class="catbg">
			<span>%HEADLINE%</span>
		</h3>
		
		<span class="upperframe"><span></span></span>
		<div class="roundframe">
			'.$output.'
		</div>
		<span class="lowerframe"><span></span></span>
	';
}
?>