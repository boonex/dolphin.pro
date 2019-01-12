<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'images.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'params.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'tags.inc.php' );

// user roles
define('BX_DOL_ROLE_GUEST',     0);
define('BX_DOL_ROLE_MEMBER',    1);
define('BX_DOL_ROLE_ADMIN',     2);
define('BX_DOL_ROLE_AFFILIATE', 4);
define('BX_DOL_ROLE_MODERATOR', 8);

/**
 * The following functions are needed to check whether user is logged in or not, active or not and get his ID.
 */
function isLogged()
{
    return getLoggedId() != 0;
}
function isLoggedActive()
{
    return isProfileActive();
}
function getLoggedId()
{
    return isset($_COOKIE['memberID']) && (!empty($GLOBALS['logged']['member']) || !empty($GLOBALS['logged']['admin'])) ? (int)$_COOKIE['memberID'] : 0;
}
function getLoggedPassword()
{
    return isset($_COOKIE['memberPassword']) && ($GLOBALS['logged']['member'] || $GLOBALS['logged']['admin']) ? $_COOKIE['memberPassword'] : '';
}

/**
 * The following functions are needed to check the ROLE of user.
 */
function isMember($iId = 0)
{
    return isRole(BX_DOL_ROLE_MEMBER, $iId);
}
if(!function_exists("isAdmin")) {
    function isAdmin($iId = 0)
    {
        return isRole(BX_DOL_ROLE_ADMIN, $iId);
    }
}
function isAffiliate($iId = 0)
{
    return isRole(BX_DOL_ROLE_AFFILIATE, $iId);
}
function isModerator($iId = 0)
{
    return isRole(BX_DOL_ROLE_MODERATOR, $iId);
}
function isRole($iRole, $iId = 0)
{
    $aProfile = getProfileInfo($iId);
    if($aProfile === false)
        return false;

    if(!((int)$aProfile['Role'] & $iRole))
        return false;

    return true;
}

$aUser = array(); //global cache array

function ShowZodiacSign( $date )
{
    global $site;

    if ( $date == "0000-00-00" )
        return "";

    if ( strlen($date) ) {
        $m = substr( $date, -5, 2 );
        $d = substr( $date, -2, 2 );

        switch ( $m ) {
            case '01': if ( $d <= 20 ) $sign = "capricorn"; else $sign = "aquarius";
                break;
            case '02': if ( $d <= 20 ) $sign = "aquarius"; else $sign = "pisces";
                break;
            case '03': if ( $d <= 20 ) $sign = "pisces"; else $sign = "aries";
                break;
            case '04': if ( $d <= 20 ) $sign = "aries"; else $sign = "taurus";
                break;
            case '05': if ( $d <= 20 ) $sign = "taurus"; else $sign = "gemini";
                break;
            case '06': if ( $d <= 21 ) $sign = "gemini"; else $sign = "cancer";
                break;
            case '07': if ( $d <= 22 ) $sign = "cancer"; else $sign = "leo";
                break;
            case '08': if ( $d <= 23 ) $sign = "leo"; else $sign = "virgo";
                break;
            case '09': if ( $d <= 23 ) $sign = "virgo"; else $sign = "libra";
                break;
            case '10': if ( $d <= 23 ) $sign = "libra"; else $sign = "scorpio";
                break;
            case '11': if ( $d <= 22 ) $sign = "scorpio"; else $sign = "sagittarius";
                break;
            case '12': if ( $d <= 21 ) $sign = "sagittarius"; else $sign = "capricorn";
        }
        $sIcon = $sign . '.png';
        return '<img src="' . $site['zodiac'] . $sIcon . '" alt="' . $sign . '" title="' . $sign . '" />';
    } else {
        return "";
    }
}

function age( $birth_date )
{
    if ( $birth_date == "0000-00-00" )
        return _t("_uknown");

    $bd = explode( "-", $birth_date );
    $age = date("Y") - $bd[0] - 1;

    $arr[1] = "m";
    $arr[2] = "d";

    for ( $i = 1; $arr[$i]; $i++ ) {
        $n = date( $arr[$i] );
        if ( $n < $bd[$i] )
            break;
        if ( $n > $bd[$i] ) {
            ++$age;
            break;
        }
    }

    return $age;
}

/**
 * Print code for membership status
 * $memberID - member ID
 * $offer_upgrade - will this code be printed at [c]ontrol [p]anel
 */
function GetMembershipStatus($memberID, $bOfferUpgrade = true, $bViewActions = true)
{
    $ret = '';

    $aMembership = getMemberMembershipInfo($memberID);
    $sViewActions = $bViewActions ? "<a onclick=\"javascript:loadHtmlInPopup('explanation_popup', '" . BX_DOL_URL_ROOT . "explanation.php?explain=membership&amp;type=" . $aMembership['ID'] . "');\" href=\"javascript:void(0);\">" . _t("_VIEW_MEMBERSHIP_ACTIONS") . "</a>" : "";

    // Show colored membership name
    if($aMembership['ID'] == MEMBERSHIP_ID_STANDARD) {
        $ret .= $aMembership['Name'];

        if($bOfferUpgrade && BxDolRequest::serviceExists('membership', 'get_upgrade_url'))
            $sViewActions = _t('_MEMBERSHIP_UPGRADE_FROM_STANDARD', BxDolService::call('membership', 'get_upgrade_url')) . '<span class="sys-bullet"></span>' . $sViewActions;

        $ret .= '<br />' . $sViewActions;
    } else {
        $ret .= '<font color="red">' . $aMembership['Name'] . '</font>';

        $sExpiration = '';
        if(!is_null($aMembership['DateExpires']))
            $sExpiration = _t("_MEMBERSHIP_EXPIRES", defineTimeInterval($aMembership['DateExpires']));
        else
            $sExpiration = _t("_MEMBERSHIP_EXPIRES_NEVER");

        $ret .= '<br />' . $sViewActions . '<span class="sys-bullet"></span>' . $sExpiration;
    }

    return $ret;
}

function deleteUserDataFile( $userID )
{
     global $aUser;

    $bUseCacheSystem = ( getParam('enable_cache_system') == 'on' ) ? true : false;
    if (!$bUseCacheSystem) return false;

    $userID = (int)$userID;
    $fileName = BX_DIRECTORY_PATH_CACHE . 'user' . $userID . '.php';
    if( file_exists($fileName) ) {
        unlink($fileName);
    }
}

function createUserDataFile( $userID )
{
    global $aUser;

    $bUseCacheSystem = ( getParam('enable_cache_system') == 'on' ) ? true : false;
    if (!$bUseCacheSystem) return false;

    $userID = (int)$userID;
    $fileName = BX_DIRECTORY_PATH_CACHE . 'user' . $userID . '.php';
    if( $userID > 0 ) {

        $aPreUser = getProfileInfoDirect ($userID);

        if( isset( $aPreUser ) and is_array( $aPreUser ) and $aPreUser) {
            $sUser = '<'.'?php';
            $sUser .= "\n\n";
            $sUser .= '$aUser[' . $userID . '] = array();';
            $sUser .= "\n";
            $sUser .= '$aUser[' . $userID . '][\'datafile\'] = true;';
            $sUser .= "\n";

            $replaceWhat = array( '\\',   '\''   );
            $replaceTo   = array( '\\\\', '\\\'' );

            foreach( $aPreUser as $key =>  $value )
                $sUser .= '$aUser[' . $userID . '][\'' . $key . '\']' . ' = ' . '\'' . str_replace( $replaceWhat, $replaceTo, $value )  . '\'' . ";\n";

            if( $file = fopen( $fileName, "w" ) ) {
                fwrite( $file, $sUser );
                fclose( $file );
                @chmod ($fileName, 0666);

                @include( $fileName );
                return true;
            } else
                return false;
        }
    } else
        return false;
}

/**
 * Check whether the requested profile is active or not.
 */
function isProfileActive($iId = 0)
{
    $aProfile = getProfileInfo($iId);
    if($aProfile === false || empty($aProfile))
        return false;

    return $aProfile['Status'] == 'Active';
}
function getProfileInfoDirect ($iProfileID)
{
    return $GLOBALS['MySQL']->getRow("SELECT * FROM `Profiles` WHERE `ID`= ? LIMIT 1", [$iProfileID]);
}

function getProfileInfo($iProfileID = 0, $checkActiveStatus = false, $forceCache = false)
{
    global $aUser;

    $iProfileID = !empty($iProfileID) ? (int)$iProfileID : getLoggedId();
    if(!$iProfileID)
        return false;

    if(!isset( $aUser[$iProfileID]) || !is_array($aUser[$iProfileID]) || $forceCache) {
        $sCacheFile = BX_DIRECTORY_PATH_CACHE . 'user' . $iProfileID . '.php';
        if( !file_exists( $sCacheFile ) || $forceCache ) {
            if( !createUserDataFile( $iProfileID ) ) {
                return getProfileInfoDirect ($iProfileID);
            }
        }

        @include( $sCacheFile );
    }

    if( $checkActiveStatus and $aUser[$iProfileID]['Status'] != 'Active' )
        return false;

    return $aUser[$iProfileID];
}

/* osed only for xmlrpc */
function getNewLettersNum( $iID )
{
    $sqlQuery =
    "
        SELECT
            COUNT(`Recipient`)
        FROM
            `sys_messages`
        INNER JOIN
            `Profiles` ON (`Profiles`.`ID` = `sys_messages`.`Sender`)
        WHERE
            `Recipient`='$iID'
                AND
            `New`='1'
                  AND
        NOT FIND_IN_SET('Recipient', `Trash`)
    ";
    return (int)db_value($sqlQuery);
}

/*function for inner using only
    $ID - profile ID
    $iFrStatus - friend status (1 - approved, 0 - wait)
    $iOnline - filter for last nav moment (in minutes)
    $sqlWhere - add sql Conditions, should beginning from AND
*/
function getFriendNumber($iID, $iFrStatus = 1, $iOnline = 0, $sqlWhere = '')
{
    $sqlAdd = "AND p.`Status`='Active'";

    if ($iOnline > 0)
        $sqlAdd = " AND (p.`DateLastNav` > SUBDATE(NOW(), INTERVAL " . $iOnline . " MINUTE))";

    if (strlen($sqlWhere) > 0)
        $sqlAdd .= $sqlWhere;

    $sqlQuery = "SELECT COUNT(`f`.`ID`)
    FROM
    (SELECT `ID` AS `ID` FROM `sys_friend_list` WHERE `Profile` = '{$iID}' AND `Check` = {$iFrStatus}
    UNION
    SELECT `Profile` AS `ID` FROM `sys_friend_list` WHERE `ID` = '{$iID}' AND `Check` = {$iFrStatus})
    AS `f`
    INNER JOIN `Profiles` AS `p` ON `p`.`ID` = `f`.`ID`
    WHERE 1 {$sqlAdd}";

    return (int)db_value($sqlQuery);
}

/**
 * Get number of friend requests sent to the specified profile.
 * It doesn't count pending friend requests which was sent by specified profile.
 * @param $iID specified profile
 * @return number of friend requests
 */
function getFriendRequests($iID)
{
    $iID = (int)$iID;
    $sqlQuery = "SELECT count(*) FROM `sys_friend_list` WHERE `Profile` = {$iID} AND `Check` = '0'";
    $iCount = (int)db_value($sqlQuery);
    if ($iCount > 0) {
        $sqlQuery = "SELECT count(*) FROM `sys_friend_list` as f LEFT JOIN `Profiles` as p ON p.`ID` = f.`ID` WHERE f.`Profile` = {$iID} AND f.`Check` = '0' AND p.`Status`='Active'";
        $iCount = (int)db_value($sqlQuery);
    }
    return $iCount;
}

/**
 * Get number of mutual friends.
 * @param $iId first profile ID
 * @param $iProfileId second profile ID
 * @return number of mutual friends
 */
function getMutualFriendsCount($iId, $iProfileId)
{
    $iId = (int)$iId;
    $iProfileId = (int)$iProfileId;

    $sQuery = "
        SELECT COUNT(*)
        FROM `Profiles` AS p
        INNER JOIN (SELECT `ID` AS `ID`, `When` FROM `sys_friend_list` WHERE `Profile` = '{$iId}' AND `Check` =1
            UNION SELECT `Profile` AS `ID`, `When` FROM `sys_friend_list` WHERE `ID` = '{$iId}' AND `Check` =1) AS `f1`
            ON (`f1`.`ID` = `p`.`ID`)
        INNER JOIN (SELECT `ID` AS `ID`, `When` FROM `sys_friend_list` WHERE `Profile` = '{$iProfileId}' AND `Check` =1
            UNION SELECT `Profile` AS `ID`, `When` FROM `sys_friend_list` WHERE `ID` = '{$iProfileId}' AND `Check` =1) AS `f2`
            ON (`f2`.`ID` = `p`.`ID`)
    ";
    return (int)db_value($sQuery);
}

function isFaved($iId, $iProfileId)
{
    $iId = (int)$iId;
    $iProfileId = (int)$iProfileId;
    return (int)db_value("SELECT count(*) FROM `sys_fave_list` WHERE `ID`='{$iId}' AND `Profile`='{$iProfileId}'") > 0;
}

/**
 * Checks whether friend request is available.
 * @param int $iId - inviter ID
 * @param int $iProfileId - invited ID
 */
function isFriendRequest($iId, $iProfileId)
{
    $iId = (int)$iId;
    $iProfileId = (int)$iProfileId;
    return (int)db_value("SELECT count(*) FROM `sys_friend_list` WHERE `ID`='{$iId}' AND `Profile`='{$iProfileId}' AND `Check` = '0'") > 0;
}

function getMyFriendsEx($iID, $sWhereParam = '', $sSortParam = '', $sqlLimit = '')
{
    $sOrderBy = '';
    $sWhereParam = "AND p.`Status`='Active' " . $sWhereParam;

    switch($sSortParam) {

        case 'activity' :
        case 'last_nav' : // DateLastNav
            $sOrderBy = 'ORDER BY p.`DateLastNav`';
            break;
        case 'activity_desc' :
        case 'last_nav_desc' : // DateLastNav
            $sOrderBy = 'ORDER BY p.`DateLastNav` DESC';
            break;
        case 'date_reg' : // DateReg
            $sOrderBy = 'ORDER BY p.`DateReg`';
            break;
        case 'date_reg_desc' : // DateReg
            $sOrderBy = 'ORDER BY p.`DateReg` DESC';
            break;
        case 'image' : // Avatar
            $sOrderBy = 'ORDER BY p.`Avatar` DESC';
            break;
        case 'rate' : // Rate and RateCount
            $sOrderBy = 'ORDER BY p.`Rate` DESC, p.`RateCount` DESC';
            break;
        default : // DateLastNav
            $sOrderBy = 'ORDER BY p.`DateLastNav` DESC';
            break;
    }

    $sLimit = ($sqlLimit == '') ? '' : /*"LIMIT 0, " .*/ $sqlLimit;
    $iOnlineTime = (int)getParam( "member_online_time" );
    $sqlQuery = "SELECT `p`.*, `f`.`ID`,
                if(`DateLastNav` > SUBDATE(NOW( ), INTERVAL $iOnlineTime MINUTE ), 1, 0) AS `is_online`,
                UNIX_TIMESTAMP(p.`DateLastLogin`) AS 'TS_DateLastLogin', UNIX_TIMESTAMP(p.`DateReg`) AS 'TS_DateReg' 	FROM (
                SELECT `ID` AS `ID` FROM `sys_friend_list` WHERE `Profile` = '{$iID}' AND `Check` =1
                UNION
                SELECT `Profile` AS `ID` FROM `sys_friend_list` WHERE `ID` = '{$iID}' AND `Check` =1
            ) AS `f`
            INNER JOIN `Profiles` AS `p` ON `p`.`ID` = `f`.`ID`
            WHERE 1 {$sWhereParam}
            {$sOrderBy}
            {$sLimit}";

    $aFriends = array();

    $vProfiles = db_res($sqlQuery);
    while ($aProfiles = $vProfiles->fetch()) {
        $aFriends[$aProfiles['ID']] = array($aProfiles['ID'], $aProfiles['TS_DateLastLogin'], $aProfiles['TS_DateReg'], $aProfiles['Rate'], $aProfiles['DateLastNav'], $aProfiles['is_online']);
    }

    return $aFriends;
}

/*
* The function returns NickName by given ID. If no ID specified, it tryes to get if from _COOKIE['memberID'];
*/
function getUsername( $ID = '' )
{
    if ( !$ID && !empty($_COOKIE['memberID']) )
        $ID = (int)$_COOKIE['memberID'];

    if ( !$ID )
        return '';

    $aProfile = getProfileInfo($ID);
    if (!$aProfile)
        return false;

    return $aProfile['NickName'];
}

/*
* The function returns NickName by given ID. If no ID specified, it tryes to get if from _COOKIE['memberID'];
*/
function getNickName( $ID = '' )
{
    if ( !$ID && !empty($_COOKIE['memberID']) )
        $ID = (int)$_COOKIE['memberID'];

    if ( !$ID )
        return '';

    return $GLOBALS['oFunctions']->getUserTitle ($ID);
}

/*
 * The function returns Password by given ID.
 */
function getPassword( $ID = '' )
{
    if ( !(int)$ID )
        return '';

    $arr = getProfileInfo( $ID );
    return $arr['Password'];
}

function getProfileLink( $iID, $sLinkAdd = '' )
{
    $aProfInfo = getProfileInfo($iID);
    $iID = $aProfInfo['Couple'] > 0 && $aProfInfo['ID'] > $aProfInfo['Couple'] ? $aProfInfo['Couple'] : $iID;

    $sLink = '';
    if(getParam('enable_modrewrite') == 'on')
        $sLink = rawurlencode(getUsername($iID)) . ($sLinkAdd ? '?' . $sLinkAdd : '');
    else
        $sLink = 'profile.php?ID=' . $iID . ($sLinkAdd ? '&' . $sLinkAdd : '');

    return BX_DOL_URL_ROOT . $sLink;
}

function isLoggedBanned($iCurUserID = 0)
{
    $iCCurUserID = ($iCurUserID>0) ? $iCurUserID : (int)$_COOKIE['memberID'];
    if ($iCCurUserID) {
        $CheckSQL = "
            SELECT *
            FROM `sys_admin_ban_list`
            WHERE `ProfID`='{$iCCurUserID}'
        ";
        $res = db_res($CheckSQL);
        if (db_affected_rows($res)>0) {
            return true;
        }
    }
    return false;
}
function bx_login($iId, $bRememberMe = false, $bAlert = true)
{
    $sPassword = getPassword($iId);

    $aUrl = parse_url($GLOBALS['site']['url']);
    $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';
    $sHost = '';
    $iCookieTime = $bRememberMe ? time() + 24*60*60*30 : 0;
    setcookie("memberID", $iId, $iCookieTime, $sPath, $sHost);
    $_COOKIE['memberID'] = $iId;
    setcookie("memberPassword", $sPassword, $iCookieTime, $sPath, $sHost, false, true /* http only */);
    $_COOKIE['memberPassword'] = $sPassword;

    db_res("UPDATE `Profiles` SET `DateLastLogin`=NOW(), `DateLastNav`=NOW() WHERE `ID`='" . $iId . "'");
    createUserDataFile($iId);

    if($bAlert) {
	    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');
	    $oZ = new BxDolAlerts('profile', 'login',  $iId);
	    $oZ->alert();
    }

    return getProfileInfo($iId);
}
function bx_logout($bNotify = true)
{
    if($bNotify && isMember()) {
        require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');
        $oZ = new BxDolAlerts('profile', 'logout', (int)$_COOKIE['memberID']);
        $oZ->alert();
    }

    $aUrl = parse_url($GLOBALS['site']['url']);
    $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';

    setcookie('memberID', '', time() - 96 * 3600, $sPath);
    setcookie('memberPassword', '', time() - 96 * 3600, $sPath);

    unset($_COOKIE['memberID']);
    unset($_COOKIE['memberPassword']);

    bx_import('BxDolSession');
    BxDolSession::getInstance()->destroy();

    if (ini_get('session.use_cookies')) {
        $aParams = session_get_cookie_params();
        setcookie(session_name(), '', time() - 96 * 3600,
            $aParams['path'], $aParams['domain'],
            $aParams['secure'], $aParams['httponly']
        );
    }

    if (version_compare(PHP_VERSION, '5.4.0') >= 0 && PHP_SESSION_ACTIVE == session_status())
        session_destroy();
}

function setSearchStartAge($iMin)
{
    if ($iMin <= 0)
        return false;

    $GLOBALS['MySQL']->query("update `sys_profile_fields` set `Min` = $iMin where `Name` = 'DateOfBirth'");

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPFM.php');
    $oCacher = new BxDolPFMCacher();
    $oCacher -> createCache();

    return true;
}

function setSearchEndAge($iMax)
{
    if ($iMax <= 0)
        return false;

    $GLOBALS['MySQL']->query("update `sys_profile_fields` set `Max` = $iMax where `Name` = 'DateOfBirth'");

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPFM.php');
    $oCacher = new BxDolPFMCacher();
    $oCacher -> createCache();

    return true;
}

/**
 * Check profile existing, membership/acl, profile status and privacy.
 * If some of visibility options are not allowed then appropritate page is shown and exit called.
 * @param $iViewedId viewed member id
 * @param $iViewerId viewer member id
 * @return nothing
 */
function bx_check_profile_visibility ($iViewedId, $iViewerId = 0, $bReturn = false)
{
    global $logged, $site, $_page, $_page_cont, $p_arr;

    // check if profile exists
    if (!$iViewedId) {
        if ($bReturn)
            return false;
        $GLOBALS['oSysTemplate']->displayPageNotFound ();
        exit;
    }

    // check if viewer can view profile
    $bPerform = $iViewedId == $iViewerId ? FALSE : TRUE;
    $check_res = checkAction( $iViewerId, ACTION_ID_VIEW_PROFILES, $bPerform, $iViewedId );
    if ($check_res[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED
        && !$logged['admin'] && !$logged['moderator'] && $iViewerId != $iViewedId)
    {
        if ($bReturn)
            return false;
        $_page['header'] = "{$site['title']} "._t("_Member Profile");
        $_page['header_text'] = "{$site['title']} "._t("_Member Profile");
        $_page['name_index'] = 0;
        $_page_cont[0]['page_main_code'] = MsgBox($check_res[CHECK_ACTION_MESSAGE]);
        
        header("HTTP/1.0 403 Forbidden");
        PageCode();
        exit;
    }

    bx_import('BxTemplProfileGenerator');
    $oProfile = new BxTemplProfileGenerator( $iViewedId );
    $p_arr  = $oProfile -> _aProfile;

    // check if viewed member is active
    if (!($p_arr['ID'] && ($logged['admin'] || $logged['moderator'] || $oProfile->owner || $p_arr['Status'] == 'Active'))) {
        if ($bReturn)
            return false;
        header("HTTP/1.1 404 Not Found");
        $GLOBALS['oSysTemplate']->displayMsg(_t("_Profile NA"));
        exit;
    }

    // check privacy
    if (!$logged['admin'] && !$logged['moderator'] && $iViewerId != $iViewedId) {
        $oPrivacy = new BxDolPrivacy('Profiles', 'ID', 'ID');
        if (!$oPrivacy->check('view', $iViewedId, $iViewerId)) {
            if ($bReturn)
                return false;
            bx_import('BxDolProfilePrivatePageView');
            $oProfilePrivateView = new BxDolProfilePrivatePageView($oProfile, $site, $dir);
            $_page['name_index'] = 7;
            $_page_cont[7]['page_main_code'] = $oProfilePrivateView->getCode();

            header("HTTP/1.0 403 Forbidden");
            PageCode();
            exit;
        }
    }
    if ($bReturn)
        return true;
}

check_logged();
