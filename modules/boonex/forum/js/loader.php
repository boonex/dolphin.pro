<?php
/**
*                            Orca Interactive Forum Script
*                              ---------------
*     Started             : Mon Mar 23 2006
*     Copyright           : (C) 2007 BoonEx Group
*     Website             : http://www.boonex.com
* This file is part of Orca - Interactive Forum Script
* Creative Commons Attribution 3.0 License
**/

chdir ('..');

require_once ('./inc/header.inc.php');

require_once ($gConf['dir']['classes'].'BxJsGzipLoader.php');

$aJsGzip = array ('BxError.js', 'BxXmlRequest.js', 'BxXslTransform.js', 'util.js', 'BxHistory.js', 'BxForum.js', 'BxAdmin.js', 'BxLogin.js', 'BxEditor.js');
new BxJsGzipLoader ('ja', $aJsGzip, $gConf['dir']['js'], $gConf['dir']['cache']);

//new BxJsGzipLoader ('d', $gConf['dir']['base'] . 'js/', '', $gConf['dir']['cache']);
