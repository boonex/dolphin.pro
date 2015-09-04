<?php
if( isset($_REQUEST['gConf']) ) die; // globals hack prevention

$gConf['dir']['xsl'] = $gConf['dir']['layouts'] . 'base/xsl/';	// xsl dir

$gConf['url']['icon'] = $gConf['url']['layouts'] . 'base/icons/';	// icons url
$gConf['url']['img'] = $gConf['url']['layouts'] . 'base/img/';	// img url
$gConf['url']['css'] = $gConf['url']['layouts']  . 'base/css/';	// css url
$gConf['url']['xsl'] = $gConf['url']['layouts'] . 'base/xsl/';	// xsl url
