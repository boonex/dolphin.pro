<?php

/**
 *
 * Overwrite necessary variables or add new in this file
 *
 *******************************************************************************/

$aPathInfo = pathinfo(__FILE__);
require_once ($aPathInfo['dirname'] . '/../base/config.php');

$gConf['url']['base'] = $site['url'] . 'forum/store/';	// base url
$gConf['db']['prefix'] = 'bx_store_'; // tables names prefix
