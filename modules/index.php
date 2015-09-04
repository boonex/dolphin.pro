<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once("../inc/header.inc.php");

    $GLOBALS['aRequest'] = explode('/', $_GET['r']);

    if ($GLOBALS['aRequest'][1] == 'admin' || $GLOBALS['aRequest'][1] == 'administration')
        $GLOBALS['iAdminPage'] = 1;

    require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");
    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php');

    $sName = process_db_input(array_shift($GLOBALS['aRequest']), BX_TAGS_STRIP);

    $oDb = new BxDolModuleDb();
    $GLOBALS['aModule'] = $oDb->getModuleByUri($sName);

    if(empty($GLOBALS['aModule']))
        BxDolRequest::moduleNotFound($sName);
    include(BX_DIRECTORY_PATH_MODULES . $GLOBALS['aModule']['path'] . 'request.php');
