<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

/**
 * Refresh user's status.
 */
function refreshIMUsers($iSndId, $iRspId)
{
    global $sModule;

    $iUpdateTime = (int)getSettingValue($sModule, "updateInterval");
    if(empty($iUpdateTime)) $iUpdateTime = 5;
    $iIdleTime = $iUpdateTime * 3;
    $iDeleteTime = $iUpdateTime * 6;
    $iCurrentTime = time();

    //--- update user's online state ---//
    getResult("UPDATE `" . MODULE_DB_PREFIX . "Contacts` SET `When`='" . $iCurrentTime . "' WHERE `SenderID`='" . $iSndId . "' AND `RecipientID` = '" . $iRspId . "'");
    //--- delete idle users ---//
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Contacts` WHERE `When`<=" . ($iCurrentTime - $iDeleteTime));
    //--- delete old messages ---//
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `When`<=" . ($iCurrentTime - $iDeleteTime));
}

/**
 * Add pending message function
 */
function addPend($iSndId, $iRspId, $sMsg)
{
    $sQuery = "INSERT INTO `" . MODULE_DB_PREFIX . "Pendings`(`SenderID`, `RecipientID`, `Message`, `When`) VALUES('" . $iSndId . "', '" . $iRspId . "', '" . $sMsg . "', '" . time() . "')";
    return getResult($sQuery);
}

function getUserOnlineStatus($sUser, $sRecipient)
{
    $sStatus = getValue("SELECT `Online` FROM `" . MODULE_DB_PREFIX . "Contacts` WHERE `SenderID`='" . $sUser . "' AND `RecipientID`='" . $sRecipient . "' LIMIT 1");
    if(empty($sStatus)) $sStatus = USER_STATUS_OFFLINE;
    return $sStatus;
}

function getContactId($sSender, $sRecipient)
{
    return getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Contacts` WHERE `SenderID`='" . $sSender . "' AND `RecipientID`='" . $sRecipient . "' LIMIT 1");
}

function removeFile($sFileId)
{
    global $sFilesPath;
    @getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Messages` WHERE `ID`='" . $sFileId . "'");
    @unlink($sFilesPath . $sFileId . ".file");
}
