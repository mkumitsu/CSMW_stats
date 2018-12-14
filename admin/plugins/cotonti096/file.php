<?php
$ch = curl_init("%PATH%");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch);
curl_close($ch);

require_once $cfg['system_dir'] . '/header.php';
$t = new XTemplate(cot_tplfile('plugin'));

$t->assign(array(
	'PLUGIN_TITLE' => "%HEADLINE%",
	'PLUGIN_BODY' => $output
));

$t->parse('MAIN');
$t->out('MAIN');

require_once $cfg['system_dir'] . '/footer.php';
?>
