<div class="border infobox">
	<div class="container-1" style="padding: 1em">
	<div class="headlineContainer"><h2>%TITLE%</h2><br/></div>
	<?php
		$ch = curl_init("%PATH%");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($ch);
		curl_close($ch);
		echo $output;
	?>
	</div>
</div>