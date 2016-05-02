<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolCron.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');

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

class BxDolCronVideoComments extends BxDolCron
{
    function processing()
    {
        global $sModule;
        global $sFfmpegPath;
        global $sModulesPath;
        global $sFilesPath;

        $iFilesCount = getSettingValue($sModule, "processCount");
        if(!is_numeric($iFilesCount)) $iFilesCount = 2;
        $iFailedTimeout = getSettingValue($sModule, "failedTimeout");
        if(!is_numeric($iFailedTimeout)) $iFailedTimeout = 1;
        $iFailedTimeout *= 86400;
        $sDbPrefix = DB_PREFIX . ucfirst($sModule);

        $iCurrentTime = time();

        //remove all tokens older than 10 minutes
        getResult("DELETE FROM `" . $sDbPrefix . "Tokens` WHERE `Date`<'" . ($iCurrentTime - 600). "'");

        getResult("UPDATE `" . $sDbPrefix . "Files` SET `Date`='" . $iCurrentTime . "', `Status`='" . VC_STATUS_FAILED . "' WHERE `Status`='" . VC_STATUS_PROCESSING . "' AND `Date`<'" . ($iCurrentTime - $iFailedTimeout) . "'");
        $rResult = getResult("SELECT * FROM `" . $sDbPrefix . "Files` WHERE `Status`='" . VC_STATUS_PENDING . "' ORDER BY `ID` LIMIT " . $iFilesCount);
        for($i=0; $i<$rResult->rowCount(); $i++) {
            $aFile = $rResult->fetch();
            if(!_convert($aFile['ID']))
                getResult("UPDATE `" . $sDbPrefix . "Files` SET `Status`='" . VC_STATUS_FAILED . "' WHERE `ID`='" . $aFile['ID'] . "'");
        }
    }
}
