<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

require_once(BX_DIRECTORY_PATH_INC . "db.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "utils.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

/**
 * Authorize user by specified ID and Password or Login and Password.
 * @param $sName - user login/ID
 * @param $sPassword - user password
 * @param $bLogin - search for login (true) or ID (false)
 * @return true/false
 */
function loginUser($sName, $sPassword, $bLogin = false)
{
    /**
     * You might change this query, if your profiles table has different structure.
     */
    $sField = $bLogin ? "NickName" : "ID";
    $sId = getValue("SELECT `ID` FROM `Profiles` WHERE `" . $sField . "`='" . $sName . "' AND `Password`='" . $sPassword . "' LIMIT 1");

    return !empty($sId) ? TRUE_VAL : FALSE_VAL;
}

/**
 * Authorize administrator by specified Login and Password.
 * @param $sLogin - administrator login
 * @param $sPassword - administrator password
 * @return true/false
 */
function loginAdmin($sLogin, $sPassword)
{
    /**
     * You might change this query. This query searches for a record in sys_admins' db with specified login and password.
     * If your admin table has different structure/format, your should change the query.
     */
    //--- Stanalone version.
    //global $sAdminLogin;
    //global $sAdminPassword;
    //$bResult = $sLogin == $sAdminLogin && $sPassword == $sAdminPassword ? TRUE_VAL : FALSE_VAL;

    //--- Dolphin version.
    $sName = getValue("SELECT `NickName` FROM `Profiles` WHERE `NickName`='$sLogin' AND `Password`='$sPassword' AND (`Role` & 2) LIMIT 1");
    $bResult = !empty($sName) ? TRUE_VAL : FALSE_VAL;
    //end of changes

    return $bResult;
}


/**
 * Gets user's information from database by user's id
 * @param $sId - user ID
 * @return $aInfo - user info
 */
function getUserInfo($sId, $bNick = false)
{
    global $sWomanImageUrl;
    global $sManImageUrl;
    global $sProfileUrl;
    global $sRootURL;

    //get info by ID on these fields
    $sNick = "";
    $sSex = "";
    $sAge = "0";
    $sDesc = "";
    $sPhoto = "";
    $sProfile = "";

    //You should change this query to retrieve user's data correctly
    $sWherePart = ($bNick ? "`NickName`" : "`ID`") . " = '" . $sId . "'";
    $aUser = getArray("SELECT * FROM `Profiles` WHERE " . $sWherePart . " LIMIT 1");

    /**
    * Define photo.
    * If this user has a photo you should define it's uri here.
    * Otherwise a "no_photo" image is used.
    */
    $oBaseFunctions = bx_instance("BxBaseFunctions");
    $sSex = !empty($aUser['Sex']) ? $aUser['Sex'] : "male";
    $sPhoto = $oBaseFunctions->getMemberAvatar($sId);
    if(empty($sPhoto))
        $sPhoto = $sSex == "male" ? $sManImageUrl : $sWomanImageUrl;
    $sNick = $GLOBALS['oFunctions']->getUserTitle($sId);
    $sAge = isset($aUser['DateOfBirth']) ? getAge($aUser['DateOfBirth']) : "25";
    $sDesc = $GLOBALS['oFunctions']->getUserInfo($sId);

    $sProfile = getParam('enable_modrewrite') == "on" ? $sRootURL . $aUser['NickName'] : $sProfileUrl . "?ID=" . $sId;

    /**
    * Return user info.
    * NOTE. Do not change the return statement order.
    */
    return array("id" => (int)$aUser["ID"], "nick" => $sNick, "sex" => $sSex, "age" => $sAge, "desc" => $sDesc, "photo" => $sPhoto, "profile" => $sProfile);
}

/**
 * Gets user's age
 * Used only in getUserInfo() function
 */
function getAge($sDob)
{
    $aDob = explode('-', $sDob);
    $iDobYear = $aDob[0];
    $iDobMonth = $aDob[1];
    $iDobDay = $aDob[2];
    $iAge = date('Y') - $iDobYear;
    if($iDobMonth > date('m'))
        $iAge--;
    else if($iDobMonth == date('m') && $iDobDay > date('d'))
        $iAge--;
    return $iAge;
}

/**
 * Searches for user by field $sField with value $sValue
 * @param $sValue - value to search for
 * @param $sField - field to search
 * @return $sId - found user ID
 */
function searchUser($sValue, $sField = "ID")
{
    if($sField == "ID")
       $sField = "ID";//you migth change this value on your user's ID column name
    else
       $sField = "NickName";//you migth change this value on your user's nick column name

    //Search for user and type result of this search
    $sId = getValue("SELECT `ID` FROM `Profiles` WHERE `" . $sField . "` = '" . $sValue . "' LIMIT 1");
    return $sId;
}

/**
 * Gets user's friend's IDs
 * @param $sId - user ID
 * @return $aUsers - friends array
 */
function getFriends($sId)
{
   $aUsers = array();
   $rResult = getResult("SELECT `Profile` FROM `sys_friend_list` WHERE `ID` = '" . $sId . "' AND `Check` = 1 UNION SELECT `ID` FROM `sys_friend_list` WHERE `Profile` = '" . $sId . "' AND `Check` = 1");
   while($aUser = $rResult->fetch()) $aUsers[] = $aUser['Profile'];
   return $aUsers;
}
