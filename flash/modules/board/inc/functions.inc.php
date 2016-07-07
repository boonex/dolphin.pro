<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
/**
 * Get information about avaliable rooms in XML format.
 * @comment - Refreshed
 */
function getBoards($sMode = 'new', $sId = "")
{
    global $aXmlTemplates;
    global $sModule;
    global $sFilesPath;
    global $sFileExtension;

    $iCurrentTime = time();
    $iUpdateInterval = (int)getSettingValue($sModule, "updateInterval");
    $iNewTime = $iUpdateInterval * 2;
    $iIdleTime = $iUpdateInterval * 3;
    $iDeleteTime = $iUpdateInterval * 6;
    $sBoards = "";
    switch ($sMode) {
        case 'update':
            getResult("UPDATE `" . MODULE_DB_PREFIX . "Boards` SET `When`='" . $iCurrentTime . "', `Status`='" . BOARD_STATUS_NORMAL . "' WHERE `OwnerID`='" . $sId . "' AND (`Status`='" . BOARD_STATUS_NORMAL . "' OR (`Status`='" . BOARD_STATUS_NEW . "' AND `When`<='" . ($iCurrentTime - $iNewTime) . "'))");

            //--- delete old boards ---//
            $rFiles = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Boards` WHERE `Status`='" . BOARD_STATUS_DELETE . "' AND `When`<=(" . ($iCurrentTime - $iDeleteTime) . ")");
            while($aFile = $rFiles->fetch()) @unlink($sFilesPath . $aFile['ID'] . $sFileExtension);
            getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Boards`, `" . MODULE_DB_PREFIX . "Users` USING `" . MODULE_DB_PREFIX . "Boards` LEFT JOIN `" . MODULE_DB_PREFIX . "Users` ON `" . MODULE_DB_PREFIX . "Boards`.`ID`=`" . MODULE_DB_PREFIX . "Users`.`Board` WHERE `" . MODULE_DB_PREFIX . "Boards`.`Status`='" . BOARD_STATUS_DELETE . "' AND `" . MODULE_DB_PREFIX . "Boards`.`When`<=(" . ($iCurrentTime - $iDeleteTime) . ")");

            getResult("UPDATE `" . MODULE_DB_PREFIX . "Boards` SET `Status`='" . BOARD_STATUS_DELETE . "' WHERE `When`<'" . ($iCurrentTime - $iIdleTime) . "' AND `Status`<>'" . BOARD_STATUS_DELETE . "'");

            $rResult = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "Boards` WHERE `OwnerID`<>'" . $sId . "' AND `Status`<>'" . BOARD_STATUS_NORMAL . "'");
            while($aBoard = $rResult->fetch())
                switch($aBoard['Status']) {
                    case BOARD_STATUS_DELETE:
                        $sBoards .= parseXml($aXmlTemplates['board'], $aBoard['ID'], BOARD_STATUS_DELETE);
                        break;
                    case BOARD_STATUS_NEW:
                        $sBoards .= parseXml($aXmlTemplates['board'], $aBoard['ID'], BOARD_STATUS_NORMAL, $aBoard['OwnerID'], empty($aBoard['Password']) ? FALSE_VAL : TRUE_VAL, stripslashes($aBoard['Name']));
                        break;
                }

            $rResult = getResult("SELECT `boards`.`ID` FROM `" . MODULE_DB_PREFIX . "Boards` AS `boards` INNER JOIN `" . MODULE_DB_PREFIX . "Users` AS `users` ON `boards`.`ID`=`users`.`Board` WHERE `boards`.`OwnerID`<>'" . $sId . "'");
            while($aBoard = $rResult->fetch()) {
                $sFile = $sFilesPath . $aBoard['ID'] . $sFileExtension;
                if(!file_exists($sFile)) continue;
                $iModifiedTime = filemtime($sFile);
                if($iModifiedTime >= ($iCurrentTime - $iUpdateInterval))
                    $sBoards .= parseXml($aXmlTemplates['board'], $aBoard['ID'], BOARD_STATUS_UPDATED);
            }
            break;

        case 'updateUsers':
            $sSql = "SELECT `r`.`ID` AS `BoardID`, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`<>'" . BOARD_STATUS_DELETE . "',`ru`.`User`,'') SEPARATOR ',') AS `In`, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`='" . BOARD_STATUS_DELETE . "',`ru`.`User`,'') SEPARATOR ',') AS `Out` FROM `" . MODULE_DB_PREFIX . "Boards` AS `r` INNER JOIN `" . MODULE_DB_PREFIX . "Users` AS `ru` WHERE `r`.`ID`=`ru`.`Board` AND `r`.`Status`='" . BOARD_STATUS_NORMAL . "' AND `ru`.`When`>=" . ($iCurrentTime - $iUpdateInterval) . " GROUP BY `r`.`ID`";
            $rResult = getResult($sSql);
            while($aBoard = $rResult->fetch())
                $sBoards .= parseXml($aXmlTemplates['board'], $aBoard['BoardID'], $aBoard['In'], $aBoard['Out']);
            break;

        case 'all':
            $iRunTime = isset($_REQUEST['_t']) ? floor($_REQUEST['_t']/1000) : 0;
            $iCurrentTime -= $iRunTime;
            $rResult = getResult("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Users`");
            if($rResult->rowCount() == 0) getResult("TRUNCATE TABLE `" . MODULE_DB_PREFIX . "Users`");
            $iBoardsCount = getValue("SELECT COUNT(`ID`) FROM `" . MODULE_DB_PREFIX . "Boards`");

            $sSql = "SELECT `r`.`ID` AS `BoardID`, `r`.*, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`='" . BOARD_STATUS_NORMAL . "' AND `ru`.`User`<>'" . $sId . "',`ru`.`User`,'') SEPARATOR ',') AS `In`, GROUP_CONCAT(DISTINCT IF(`ru`.`Status`='" . BOARD_STATUS_DELETE . "' AND `ru`.`User`<>'" . $sId . "',`ru`.`User`,'') SEPARATOR ',') AS `Out` FROM `" . MODULE_DB_PREFIX . "Boards` AS `r` LEFT JOIN `" . MODULE_DB_PREFIX . "Users` AS `ru` ON `r`.`ID`=`ru`.`Board` GROUP BY `r`.`ID` ORDER BY `r`.`ID`";
            $rResult = getResult($sSql);
            while($aBoard = $rResult->fetch()) {
                $sBoards .= parseXml($aXmlTemplates['board'], $aBoard['BoardID'], BOARD_STATUS_NORMAL, $aBoard['OwnerID'], empty($aBoard['Password']) ? FALSE_VAL : TRUE_VAL, $aBoard['In'], stripslashes($aBoard['Name']));
            }
            if($rResult->rowCount() === 0) {
                getResult("TRUNCATE TABLE `" . MODULE_DB_PREFIX . "Boards`");
                getResult("TRUNCATE TABLE `" . MODULE_DB_PREFIX . "Users`");
            }
            break;
    }
    return $sBoards;
}

/**
 * Actions with specified room
 */
function doBoard($sSwitch, $sUserId = "", $iBoardId = 0, $sTitle = "", $sPassword = "")
{
    $iCurrentTime = time();
    switch ($sSwitch) {
        case 'insert':
            $iBoardId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Boards` WHERE `Name`='" . $sTitle . "' AND `OwnerID`='" . $sUserId . "'");
            if(empty($iBoardId)) {
                getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Boards` (`ID`, `Name`, `Password`, `OwnerID`, `When`) VALUES ('" . $iBoardId . "', '" . $sTitle . "', '" . $sPassword . "', '" . $sUserId . "', '" . $iCurrentTime . "')");
                $iBoardId = getLastInsertId();
            }
            return $iBoardId;
            break;

        case 'update':
            getResult("UPDATE `" . MODULE_DB_PREFIX . "Boards` SET `Name`='" . $sTitle . "', `Password`='" . $sPassword . "', `When`='" . $iCurrentTime . "', `Status`='" . BOARD_STATUS_NEW . "' WHERE `ID`='" . $iBoardId . "'");
            break;

        case 'delete':
            getResult("UPDATE `" . MODULE_DB_PREFIX . "Boards` SET `When`='" . $iCurrentTime . "', `Status`='" . BOARD_STATUS_DELETE . "' WHERE `ID` = '" . $iBoardId . "'");
            break;

        case 'enter':
            $sId = getValue("SELECT `ID` FROM `" . MODULE_DB_PREFIX . "Users` WHERE `Board`='" . $iBoardId . "' AND `User`='" . $sUserId . "' LIMIT 1");
            if(empty($sId))	getResult("INSERT INTO `" . MODULE_DB_PREFIX . "Users`(`Board`, `User`, `When`) VALUES('" . $iBoardId . "', '" . $sUserId . "', '" . $iCurrentTime . "')");
            else getResult("UPDATE `" . MODULE_DB_PREFIX . "Users` SET `When`='" . $iCurrentTime . "', `Status`='" . BOARD_STATUS_NORMAL . "' WHERE `ID`='" . $sId . "'");
            break;

        case 'exit':
            getResult("UPDATE `" . MODULE_DB_PREFIX . "Users` SET `When`='" . $iCurrentTime . "', `Status`='" . BOARD_STATUS_DELETE . "' WHERE `Board`='" . $iBoardId . "' AND `User`='" . $sUserId . "' LIMIT 1");
            break;
    }
}

/**
 * ===> The rest of functions is for XML version only. <===
 * Update user's status
 * @comment - Refreshed
 */
function refreshUsersInfo($sId = "", $sMode = 'all')
{
    global $sModule;
    global $aXmlTemplates;
    global $sFileExtension;
    global $sFilesPath;

    $iUpdateInterval = (int)getSettingValue($sModule, "updateInterval");
    $iIdleTime = $iUpdateInterval * 3;
    $iDeleteTime = $iUpdateInterval * 6;

    $iCurrentTime = time();
    //--- refresh current user's track ---//
    getResult("UPDATE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `Status`='" . USER_STATUS_OLD . "', `When`='" . $iCurrentTime . "' WHERE `ID`='" . $sId . "' AND (`Status`<>'" . USER_STATUS_NEW . "' || (" . $iCurrentTime . "-`When`)>" . $iUpdateInterval . ") LIMIT 1");

    //--- refresh other users' states ---//
    getResult("UPDATE `" . MODULE_DB_PREFIX . "CurrentUsers` SET `When`=" . $iCurrentTime . ", `Status`='" . USER_STATUS_IDLE . "' WHERE `Status`<>'" . USER_STATUS_IDLE . "' AND `When`<=(" . ($iCurrentTime - $iIdleTime) . ")");
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "Users` WHERE `Status`='" . BOARD_STATUS_DELETE . "' AND `When`<=(" . ($iCurrentTime - $iDeleteTime) . ")");

    //--- delete idle users, whose track was not refreshed more than delete time ---//
    getResult("DELETE FROM `" . MODULE_DB_PREFIX . "CurrentUsers`, `" . MODULE_DB_PREFIX . "Users` USING `" . MODULE_DB_PREFIX . "CurrentUsers` LEFT JOIN `" . MODULE_DB_PREFIX . "Users` ON `" . MODULE_DB_PREFIX . "CurrentUsers`.`ID`=`" . MODULE_DB_PREFIX . "Users`.`User` WHERE `" . MODULE_DB_PREFIX . "CurrentUsers`.`Status`='" . USER_STATUS_IDLE . "' AND `" . MODULE_DB_PREFIX . "CurrentUsers`.`When`<=" . ($iCurrentTime - $iDeleteTime));

    //--- Get information about users in the chat ---//
    switch($sMode) {
        case 'update':
            $rRes = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "CurrentUsers` ORDER BY `When`");
            while($aUser = $rRes->fetch()) {
                switch($aUser['Status']) {
                    case USER_STATUS_NEW:
                        $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], $aUser['Status'], $aUser['Nick'], $aUser['Sex'], $aUser['Age'], $aUser['Photo'], $aUser['Profile'], $aUser['Desc']);
                        break;
                    case USER_STATUS_IDLE:
                        $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], $aUser['Status']);
                        break;
                }
            }
            break;

        case 'all':
            $iRunTime = isset($_REQUEST['_t']) ? floor($_REQUEST['_t']/1000) : 0;
            $iCurrentTime -= $iRunTime;
            $rRes = getResult("SELECT * FROM `" . MODULE_DB_PREFIX . "CurrentUsers` WHERE `Status`<>'" . USER_STATUS_IDLE . "' ORDER BY `When`");
            while($aUser = $rRes->fetch())
                $sContent .= parseXml($aXmlTemplates['user'], $aUser['ID'], USER_STATUS_NEW, $aUser['Nick'], $aUser['Sex'], $aUser['Age'], $aUser['Photo'], $aUser['Profile'], $aUser['Desc']);
            break;
    }
    return makeGroup($sContent, "users");
}
