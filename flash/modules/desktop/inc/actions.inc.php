<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$sId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : "0";
$sNick = isset($_REQUEST['nick']) ? process_db_input($_REQUEST['nick']) : "";
$sPassword = isset($_REQUEST['password']) ? process_db_input($_REQUEST['password']) : "";
$sStatus = isset($_REQUEST['status']) ? process_db_input($_REQUEST['status']) : "";
$sMails = isset($_REQUEST['mails']) ? process_db_input($_REQUEST['mails']) : "";

$sSkin = isset($_REQUEST['skin']) ? process_db_input($_REQUEST['skin']) : "";
$sLanguage = isset($_REQUEST['language']) ? process_db_input($_REQUEST['language']) : "english";

switch ($sAction) {
    case 'getPlugins':
        $sFolder = "/plugins/";
        $sContents = "";
        $sPluginsPath = $sModulesPath . $sModule . $sFolder;
        if(is_dir($sPluginsPath)) {
            if($rDirHandle = opendir($sModulesPath . $sModule . $sFolder))
                while(false !== ($sPlugin = readdir($rDirHandle)))
                    if(strpos($sPlugin, ".swf") === strlen($sPlugin)-4)
                        $sContents .= parseXml(array(1 => '<plugin><![CDATA[#1#]]></plugin>'), $sModulesUrl . $sModule . $sFolder . $sPlugin);
            closedir($rDirHandle);
        }
        $sContents = makeGroup($sContents, "plugins");
        break;
    /**
    * gets skins
    */
    case 'getSkins':
        $sContents = printFiles($sModule, "skins", false, true);
        break;

    /**
    * Sets default skin.
    */
    case 'setSkin':
        setCurrentFile($sModule, $sSkin, "skins");
        break;

    /**
    * gets languages
    */
    case 'getLanguages':
        $sContents = printFiles($sModule, "langs", false, true);
        break;

    /**
    * Sets default language.
    */
    case 'setLanguage':
        setCurrentFile($sModule, $sLanguage, "langs");
        break;

    /**
    * Gets configuration for Ray Presence
    */
    case "config":
        require_once($dir['inc'] . "profiles.inc.php");
        require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModule.php');
        $oChat = BxDolModule::getInstance("BxChatModule");
        $oMessenger = BxDolModule::getInstance("BxMsgModule");

        $sFileName = $sModulesPath . $sModule . "/xml/config.xml";
        $rHandle = fopen($sFileName, "rt");
        $sContents = fread($rHandle, filesize($sFileName));
        fclose($rHandle);
        $sContents = str_replace("#music#", getUserMusicLink(), $sContents);
        $sContents = str_replace("#video#", getUserVideoLink(), $sContents);
        $sContents = str_replace("#im#", getUserImLink($sId), $sContents);
        $sContents = str_replace("#chat#", getUserChatLink($sId), $sContents);
        $sContents = str_replace("#siteUrl#", $sRootURL, $sContents);
        $sContents = str_replace("#xmlUrl#", $sRayXmlUrl, $sContents);
        $sContents = str_replace("#desktopUrl#", $sModulesUrl . $sModule . "/", $sContents);
        break;

    case 'userAuthorize':
        $sResult = loginUser($sId, $sPassword);
        $sContents = parseXml($aXmlTemplates['result'], $sResult == TRUE_VAL ? TRUE_VAL : "msgUserAuthenticationFailure");
        if($sResult == TRUE_VAL) {
            $sContents .= parseXml($aXmlTemplates['status'], getUserStatus($sId));
            $sContents .= getAvailableStatuses();
            saveUsers(array('online'=>array(), 'offline'=>array()));
        }
        break;

    case 'login':
        $sContents = parseXml($aXmlTemplates['result'], "msgUserAuthenticationFailure", FAILED_VAL);
        $sId = getIdByNick($sNick);
        $sPassword = encryptPassword($sId, $sPassword);
        if(loginUser($sNick, $sPassword, true) == TRUE_VAL) {
            $aUserInfo = getUserInfo($sId);
            login($sId, $sPassword);
            $sContents = parseXml($aXmlTemplates['result'], $sId, SUCCESS_VAL, $sPassword);
        }
        break;

    case 'logout':
        logout($sId);
        $sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        break;

    case "getUsers":
        $bInit = true;
    case "updateUsers":
        if(!isset($bInit)) $bInit = false;
        updateOnline($sId);
        $aSavedUsers = getSavedUsers();
        $sContents = getOnlineUsersInfo($sId, $bInit);
        $aUsers = getSavedUsers();
        $sContents .= getMails($sId, $sMails, array_unique(array_intersect($aSavedUsers['online'], $aUsers['online'])));
        $sContents .= getIms($sId);
        break;

    /**
    * Updates user's information in RayPresenceUsers table.
    * For Users, who didn't login into Ray Presence, but logged in into site.
    * @param id - user's ID.
    * @param status - user's status.
    */
    case "updateOnlineStatus":
        updateOnline($sId, $sStatus);
        break;

    /**
    * Declines received IM message.
    * @param id - user's ID.
    */
    case "declineIm":
        declineIm($sId);
        break;
}
