<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../../../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

//require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $aModule['class_prefix'] . 'Module.php');
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php');
require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/custom_rss/classes/BxCRSSModule.php');

check_logged();

$oModuleDb = new BxDolModuleDb();
$aModule = $oModuleDb->getModuleByUri('custom_rss');

$oBxCRSSModule = new BxCRSSModule($aModule);

$sAction = bx_get('action');
$sCodeResult = '';

switch ($sAction) {
    case 'a':
    default:
        $sCodeResult = $oBxCRSSModule->GenCustomRssBlock((int)bx_get('ID'));
        break;
}

echo $sCodeResult;
