<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
require_once(BX_DIRECTORY_PATH_INC . "utils.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "membership_levels.inc.php");
require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstallerUtils.php");

function isModuleAvailable($sModuleName, $sUserId = "", $sAction = "")
{
    $bResult = BxDolInstallerUtils::isModuleInstalled($sModuleName);
    if($bResult && !empty($sUserId) && !empty($sAction)) {
        $aResult = checkAction($sUserId, $sAction);
        $bResult = $aResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }
    return $bResult;
}

function getUserVideoLink()
{
    global $sRootURL;
    if(isModuleAvailable("videos"))
        return $sModulesUrl . "video/videoslink.php?id=#user#";
    return "";
}

function getUserMusicLink()
{
    global $sRootURL;
    if(isModuleAvailable("sounds"))
        return $sModulesUrl . "mp3/soundslink.php?id=#user#";
    return "";
}

function getUserChatLink($sUserId)
{
    return isModuleAvailable("chat", $sUserId, ACTION_ID_USE_CHAT) ? "#desktopUrl#chat.html?id=#owner#&password=#password#" : "";
}

function getUserImLink($sUserId)
{
    return isModuleAvailable("messenger", $sUserId, ACTION_ID_USE_MESSENGER) ? "im/sender=#owner#&password=#password#&recipient=#user#/#nick#/10,10,550,500/true" : "";
}

function getUsersMedia($aUsers)
{
    if(count($aUsers) == 0) return null;
    $sUsers = "('" . implode("','", $aUsers) . "')";
    $sSql = "SELECT `users`.`ID`, COUNT(DISTINCT `music`.`ID`) AS `CountMusic`, COUNT(DISTINCT `video`.`ID`) AS `CountVideo` FROM `Profiles` AS `users` LEFT JOIN `" . DB_PREFIX . "Mp3Files` AS `music` ON `users`.`ID`=`music`.`Owner` AND `music`.`Status`='approved' LEFT JOIN `" . DB_PREFIX . "VideoFiles` AS `video` ON `users`.`ID`=`video`.`Owner` AND `video`.`Status`='approved' WHERE `users`.`ID` IN " . $sUsers . " GROUP BY `users`.`ID`";
    $rResult = getResult($sSql);
    return $rResult;
}

function getActiveUsers($sUserId)
{
    global $sModule;
    require_once(BX_DIRECTORY_PATH_INC . "db.inc.php");
    $iUpdateInterval = getSettingValue($sModule, "updateInterval");
    $iMin = (int)getParam("member_online_time");
    $sOnlineFactor = "`UserStatus`!='" . USER_STATUS_OFFLINE . "' AND `DateLastNav`>SUBDATE(NOW(), INTERVAL " . $iMin . " MINUTE)";
    $sRetrieveFactor = "`DateLastNav`>SUBDATE(NOW(), INTERVAL " . ($iMin*60 + $iUpdateInterval*3) . " SECOND)";
    $rResult = getResult("SELECT `ID`, IF(" . $sOnlineFactor . ", 1, 0) AS `Online` FROM `Profiles` WHERE `ID`<>'" . $sUserId . "' AND " . $sRetrieveFactor . " ORDER BY `ID`");

    $aOnline = array();
    $aOffline = array();
    while(($aUser = $rResult->fetch()) != null)
        if($aUser['Online']) $aOnline[] = $aUser['ID'];
        else $aOffline[] = $aUser['ID'];

    return array('online' => $aOnline, 'offline' => $aOffline);
}

/**
 * Get user's identifier using user's nickname.
 */
function getIdByNick($sNick)
{
   return (int)getValue("SELECT `ID` FROM `Profiles` WHERE `NickName` = '" . $sNick . "' LIMIT 1");
}

function encryptPassword($sId, $sPassword)
{
    $aUser = getProfileInfo($sId);
    return encryptUserPwd($sPassword, $aUser['Salt']);
}

function login($sId, $sPassword)
{
    $aUrl = parse_url($GLOBALS['site']['url']);
    $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';
    $sHost = '';

    setcookie("memberID", $sId, 0, $sPath, $sHost);
    setcookie("memberPassword", $sPassword, 0, $sPath, $sHost, false, true /* http only */);
    updateOnline($sId);
}

function logout($sId)
{
    setcookie("memberID", '', time() - 86400);
    setcookie("memberPassword", '', time() - 86400);
    updateOnline($sId, "", false);
}

function getUserStatus($sId)
{
    return getValue("SELECT `UserStatus` FROM `Profiles` WHERE `ID`='" . $sId . "'");
}

function updateOnline($sId = "", $sStatus = "", $bOnline = true)
{
    $sOnlineUpdate = $bOnline ? "NOW()" : "(NOW()-" . ((int)getParam("member_online_time") * 120) . ")";
    $sStatusUpdate = empty($sStatus) ? "" : ", `UserStatus`='" . $sStatus . "'";
    getResult("UPDATE `Profiles` SET `DateLastNav`=" . $sOnlineUpdate . $sStatusUpdate . " WHERE `ID`='" . $sId . "'");
    if(!empty($sStatusUpdate))
        createUserDataFile($sId);
}

/**
 * Gets new user's mails except already got mails($sGotMails) by specified user id
 */
function getMails($sId, $sGotMails, $aFullUsers)
{
    global $aXmlTemplates;

    $sNotIn = empty($sGotMails) ? "" : " AND `ID` NOT IN(" . $sGotMails . ")";
    $sQuery = "SELECT `ID`, `Sender`, SUBSTRING(`Text`, 1, 150) AS `Body` FROM `sys_messages` WHERE `Recipient` = '" . $sId . "' AND `New`='1'" . $sNotIn . " AND NOT FIND_IN_SET('recipient', `Trash`)";
    $rResult = getResult($sQuery);

    $aMails = array();
    $aSenders = array();
    for($i=0; $i<$rResult->rowCount(); $i++) {
        $aMail = $rResult->fetch();
        if(!in_array($aMail['Sender'], $aFullUsers)) $aSenders[] = $aMail['Sender'];
        $aMails[] = $aMail;
    }
    $aSenders = array_unique($aSenders);

    $aMediaUsers = array();
    $rMedia = getUsersMedia($aSenders);
    if($rMedia != null) {
        for($i=0; $i<$rMedia->rowCount(); $i++) {
            $aUser = $rMedia->fetch();
            $sUserId = $aUser['ID'];
            $aMediaUsers[$sUserId] = getUserInfo($sUserId);
            $aMediaUsers[$sUserId]['music'] = $aUser['CountMusic'] > 0 ? TRUE_VAL : FALSE_VAL;
            $aMediaUsers[$sUserId]['video'] = $aUser['CountVideo'] > 0 ? TRUE_VAL : FALSE_VAL;
        }
    }

    $sResult = "";
    for($i=0; $i<count($aMails); $i++) {
        $sSenderId = $aMails[$i]['Sender'];
        $aMails[$i]['Body'] = strip_tags($aMails[$i]['Body']);
        if(is_array($aMediaUsers[$sSenderId])) {
            $aUser = $aMediaUsers[$sSenderId];
            $sResult .= parseXml($aXmlTemplates["message"], $aMails[$i]['ID'], $sSenderId, $aMails[$i]['Body'], $aUser['nick'], $aUser['sex'], $aUser['age'], $aUser['photo'], $aUser['profile'], $aUser['music'], $aUser['video']);
        } else $sResult .= parseXml($aXmlTemplates["message"], $aMails[$i]['ID'], $sSenderId, $aMails[$i]['Body']);
    }
    return makeGroup($sResult, "mails");
}

function getIms($sId)
{
    global $aXmlTemplates;

    $rResult = getResult("SELECT * FROM `" . DB_PREFIX ."ImPendings` WHERE `RecipientID`='" . $sId . "' ORDER BY `ID` DESC");
    $sResult = "";
    for($i=0; $i<$rResult->rowCount(); $i++) {
        $aIm = $rResult->fetch();
        $sResult .= parseXml($aXmlTemplates["message"], $aIm['ID'], $aIm['SenderID'], $aIm['Message']);
    }
    return makeGroup($sResult, "ims");
}

function declineIm($sId)
{
    getResult("DELETE FROM `" . DB_PREFIX . "ImPendings` WHERE `ID`='" . $sId . "'");
}

require_once(BX_DIRECTORY_PATH_INC . "languages.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");
require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolUserStatusView.php");

function getAvailableStatuses()
{
    global $aXmlTemplates;
    $oStatuses = new BxDolUserStatusView();
    $sContents = "";
    foreach($oStatuses->aStatuses as $sKey => $aStatus)
        $sContents .= parseXml($aXmlTemplates["status"], $sKey, getTemplateIcon($aStatus["icon"]), _t($aStatus["title"]));
    return makeGroup($sContents, "statuses");
}
