<?php
// ClanSphere 2009 - www.clansphere.net

$ch = curl_init('%PATH%');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch);
curl_close($ch);

$data = array();
$data['ezstats3_wowp']['headline'] = "%HEADLINE%";
$data['ezstats3_wowp']['content'] = $output;

echo cs_subtemplate(__FILE__, $data, 'ezstats3_wowp');
?>