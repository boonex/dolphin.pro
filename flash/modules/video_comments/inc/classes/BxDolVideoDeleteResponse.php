<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolAlerts');

global $sModule;
$sModule = "video_comments";

global $sIncPath;
global $sModulesPath;

require_once($sIncPath . "constants.inc.php");
require_once($sIncPath . "db.inc.php");
require_once($sIncPath . "xml.inc.php");
require_once($sIncPath . "functions.inc.php");
require_once($sIncPath . "apiFunctions.inc.php");
require_once($sIncPath . "customFunctions.inc.php");

global $sFilesPath;
$sModuleIncPath = $sModulesPath . $sModule . "/inc/";
require_once($sModuleIncPath . "header.inc.php");
require_once($sModuleIncPath . "constants.inc.php");
require_once($sModuleIncPath . "functions.inc.php");
require_once($sModuleIncPath . "customFunctions.inc.php");

class BxDolVideoDeleteResponse extends BxDolAlertsResponse
{
    function response ($oAlert)
    {
        global $sFilesPath;
        global $sModule;

        if($oAlert->sAction == "commentRemoved")
            deleteFileByCommentId($oAlert->aExtras['comment_id']);
    }
}
