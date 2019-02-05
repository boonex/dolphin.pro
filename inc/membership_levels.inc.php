<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'languages.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );

//MESSAGE CONSTANTS PASSED TO _t_ext() FUNCTION BY checkAction()
//NOTE: checkAction() RETURNS LANGUAGE DEPENDANT MESSAGES

define('CHECK_ACTION_MESSAGE_NOT_ALLOWED',			"_ACTION_NOT_ALLOWED");
define('CHECK_ACTION_MESSAGE_NOT_ACTIVE',			"_ACTION_NOT_ACTIVE");
define('CHECK_ACTION_MESSAGE_LIMIT_REACHED',		"_ACTION_LIMIT_REACHED");
define('CHECK_ACTION_MESSAGE_MESSAGE_EVERY_PERIOD',	"_ACTION_EVERY_PERIOD");
define('CHECK_ACTION_MESSAGE_NOT_ALLOWED_BEFORE',	"_ACTION_NOT_ALLOWED_BEFORE");
define('CHECK_ACTION_MESSAGE_NOT_ALLOWED_AFTER',	"_ACTION_NOT_ALLOWED_AFTER");

//NODES OF THE $args ARRAY THAT IS PASSED TO THE _t_ext() FUNCTION BY checkAction()

define('CHECK_ACTION_LANG_FILE_ACTION',		1);
define('CHECK_ACTION_LANG_FILE_MEMBERSHIP',	2);
define('CHECK_ACTION_LANG_FILE_LIMIT',		3);
define('CHECK_ACTION_LANG_FILE_PERIOD',		4);
define('CHECK_ACTION_LANG_FILE_AFTER',		5);
define('CHECK_ACTION_LANG_FILE_BEFORE',		6);
define('CHECK_ACTION_LANG_FILE_SITE_EMAIL',	7);

//ACTION ID's

define('ACTION_ID_SEND_VKISS',		 1);
define('ACTION_ID_VIEW_PROFILES',	 2);
define('ACTION_ID_VOTE',			 3);
define('ACTION_ID_SEND_MESSAGE',	 4);
define('ACTION_ID_GET_EMAIL',		 5);
define('ACTION_ID_COMMENTS_POST', 6);
define('ACTION_ID_COMMENTS_VOTE', 7);
define('ACTION_ID_COMMENTS_EDIT_OWN', 8);
define('ACTION_ID_COMMENTS_REMOVE_OWN', 9);
define('ACTION_ID_SEND_FRIEND_REQUEST', 10);

//PREDEFINED MEMBERSHIP ID's

define('MEMBERSHIP_ID_NON_MEMBER', 1);
define('MEMBERSHIP_ID_STANDARD', 2);
define('MEMBERSHIP_ID_PROMOTION', 3);

//INDICES FOR checkAction() RESULT ARRAY

define('CHECK_ACTION_RESULT', 0);
define('CHECK_ACTION_MESSAGE', 1);
define('CHECK_ACTION_PARAMETER', 3);

//CHECK_ACTION_RESULT NODE VALUES

define('CHECK_ACTION_RESULT_ALLOWED',				0);
define('CHECK_ACTION_RESULT_NOT_ALLOWED',			1);
define('CHECK_ACTION_RESULT_NOT_ACTIVE',			2);
define('CHECK_ACTION_RESULT_LIMIT_REACHED',			3);
define('CHECK_ACTION_RESULT_NOT_ALLOWED_BEFORE',	4);
define('CHECK_ACTION_RESULT_NOT_ALLOWED_AFTER',		5);

/**
 * Returns number of members with a given membership at a given time
 *
 * @param int $iMembershipId		- members of what membership should be counted.
 * 								  if 0, then all members are counted ($except is not considered);
 * 								  if MEMBERSHIP_ID_NON_MEMBER is specified, function returns -1
 *
 * @param unix_timestamp $time	- date/time to use when counting members.
 * 								  if not specified, uses the present moment
 * @param boolean $except		- if true, counts all members that DON'T have specified membership
 *
 *
 */
/*function getMembersCount($iMembershipId = 0, $time = '', $except = false)  //'A' just old unused function
{
    $iMembershipId = (int)$iMembershipId;
    $time = ($time == '') ? time() : (int)$time;
    $except = $except ? true : false;

    if($iMembershipId == MEMBERSHIP_ID_NON_MEMBER || $iMembershipId < 0) return -1;

    $resProfiles = db_res("SELECT COUNT(*) FROM Profiles");

    $totalProfiles = mysql_fetch_row($resProfiles);
    $totalProfiles = (int)$totalProfiles[0];

    if($iMembershipId == 0) return $totalProfiles;

    $queryWhereMembership = '';

    if($iMembershipId != MEMBERSHIP_ID_STANDARD) $queryWhereMembership = "IDLevel = $iMembershipId AND";

    $query = "
        SELECT	COUNT(DISTINCT IDMember)
        FROM	`sys_acl_levels_members`
        WHERE	$queryWhereMembership
                (DateExpires IS NULL OR UNIX_TIMESTAMP(DateExpires) > $time) AND
                (DateStarts IS NULL OR UNIX_TIMESTAMP(DateStarts) <= $time)";

    $resProfileMemLevels = db_res($query);

    $membershipProfiles = mysql_fetch_row($resProfileMemLevels);
    $membershipProfiles = (int)$membershipProfiles[0];

    if($iMembershipId == MEMBERSHIP_ID_STANDARD) $membershipProfiles = $totalProfiles - $membershipProfiles;

    if($except) $membershipProfiles = $totalProfiles - $membershipProfiles;

    return $membershipProfiles;
}*/

/**
 * This is an internal function - do NOT use it outside of membership_levels.inc.php!
 */
function getMemberMembershipInfo_current($iMemberId, $time = '')
{
    $iMemberId = (int)$iMemberId;
    $time = ($time == '') ? time() : (int)$time;

    /**
     * Fetch the last purchased/assigned membership that is still active for the given member.
     * NOTE. Don't use cache here, because it's causing an error, if a number of memberrship levels are purchased at the same time.
     * fromMemory returns the same DateExpires because buyMembership function is called in cycle in the same session.
     */
    $aMemLevel = $GLOBALS['MySQL']->getRow("
        SELECT  `sys_acl_levels_members`.IDLevel as ID,
                `sys_acl_levels`.Name as Name,
                UNIX_TIMESTAMP(`sys_acl_levels_members`.DateStarts) as DateStarts,
                UNIX_TIMESTAMP(`sys_acl_levels_members`.DateExpires) as DateExpires,
                `sys_acl_levels_members`.`TransactionID` AS `TransactionID`
        FROM    `sys_acl_levels_members`
                RIGHT JOIN Profiles
                ON `sys_acl_levels_members`.IDMember = Profiles.ID
                    AND (`sys_acl_levels_members`.DateStarts IS NULL
                        OR `sys_acl_levels_members`.DateStarts <= FROM_UNIXTIME(?))
                    AND (`sys_acl_levels_members`.DateExpires IS NULL
                        OR `sys_acl_levels_members`.DateExpires > FROM_UNIXTIME(?))
                LEFT JOIN `sys_acl_levels`
                ON `sys_acl_levels_members`.IDLevel = `sys_acl_levels`.ID

        WHERE   Profiles.ID = ?

        ORDER BY `sys_acl_levels_members`.DateStarts DESC

        LIMIT 0, 1", [$time, $time, $iMemberId]);

    /**
     * no such member found
     */
    if (!$aMemLevel || !count($aMemLevel)) {
        //fetch info about Non-member membership
        $aMemLevel =& $GLOBALS['MySQL']->fromCache('sys_acl_levels' . MEMBERSHIP_ID_NON_MEMBER, 'getRow', "SELECT ID, Name FROM `sys_acl_levels` WHERE ID = ?", [MEMBERSHIP_ID_NON_MEMBER]);
        if(!$aMemLevel || !count($aMemLevel)) {
            //this should never happen, but just in case
            echo "<br /><b>getMemberMembershipInfo()</b> fatal error: <b>Non-Member</b> membership not found.";
            exit();
        }
        return $aMemLevel;
    }

    /**
     * no purchased/assigned memberships for the member or all of them have expired -- the member is assumed to have Standard membership
     */
    if(is_null($aMemLevel['ID'])) {
        $aMemLevel = $GLOBALS['MySQL']->fromCache('sys_acl_levels' . MEMBERSHIP_ID_STANDARD, 'getRow', "SELECT ID, Name FROM `sys_acl_levels` WHERE ID = ?", [MEMBERSHIP_ID_STANDARD]);
        if (!$aMemLevel || !count($aMemLevel)) {
            //again, this should never happen, but just in case
            echo "<br /><b>getMemberMembershipInfo()</b> fatal error: <b>Standard</b> membership not found.";
            exit();
        }
    }

    return $aMemLevel;
}

/**
 * This is an internal function - do NOT use it outside of membership_levels.inc.php!
 */
function getMemberMembershipInfo_latest($iMemberId, $iTime = '')
{
    $iTime = $iTime == '' ? time() : (int)$iTime;

    $aMembershipCurrent = getMemberMembershipInfo_current($iMemberId, $iTime);
    if(in_array($aMembershipCurrent['ID'], array(MEMBERSHIP_ID_STANDARD, MEMBERSHIP_ID_NON_MEMBER)))
        return $aMembershipCurrent;

    $aMembership = $aMembershipCurrent;
    while($aMembership['ID'] != MEMBERSHIP_ID_STANDARD) {
        $aMembershipLast = $aMembership;
        if((int)$aMembership['DateExpires'] == 0)
            break;

        $aMembership = getMemberMembershipInfo_current($iMemberId, $aMembership['DateExpires']);
    }

    return $aMembershipLast;
}


/**
 * Retrieves information about membership for a given member at a given moment.
 *
 * If there are no memberships purchased/assigned to the
 * given member or all of them have expired at the given point,
 * the member is assumed to be a standard member, and the function
 * returns	information about the Standard membership. This will
 * also happen if a member wasnt actually registered in the database
 * at that point - the function will still return info about Standard
 * membership, not the Non-member one.
 *
 * If there is no profile with the given $iMemberId,
 * the function returns information about the Non-member
 * predefined membership.
 *
 * The Standard and Non-member memberships have their
 * DateStarts and DateExpires attributes set to NULL.
 *
 * @param int $iMemberId	- ID of a member to get info about
 * @param int $time		- specifies the time to use when determining membership;
 * 						  if not specified, the function takes the current time
 *
 * @return array(	'ID'			=> membership id,
 * 					'Name'			=> membership name,
 * 					'DateStarts'	=> (UNIX timestamp) date/time purchased,
 * 					'DateExpires'	=> (UNIX timestamp) date/time expires )
 *
 */
function getMemberMembershipInfo($iMemberId, $iTime = '', $bCheckUserStatus = false)
{
    $iTime = ($iTime == '') ? time() : (int)$iTime;

    if ($bCheckUserStatus && ($aProfile = getProfileInfo($iMemberId)) && $aProfile['Status'] != 'Active')
        $aMembershipCurrent =& $GLOBALS['MySQL']->fromCache('sys_acl_levels' . MEMBERSHIP_ID_NON_MEMBER, 'getRow', "SELECT ID, Name FROM `sys_acl_levels` WHERE ID = ".MEMBERSHIP_ID_NON_MEMBER);
    else    
        $aMembershipCurrent = getMemberMembershipInfo_current($iMemberId, $iTime);

    if(in_array($aMembershipCurrent['ID'], array(MEMBERSHIP_ID_STANDARD, MEMBERSHIP_ID_NON_MEMBER)))
        return $aMembershipCurrent;

    $aMembership = $aMembershipCurrent;
    do {
        $iDateStarts = $aMembership['DateStarts'];
        $aMembership = getMemberMembershipInfo_current($iMemberId, ((int)$iDateStarts < 1 ? 0 : $iDateStarts - 1));
    } while($aMembership['ID'] == $aMembershipCurrent['ID'] && (int)$aMembership['DateStarts']);

    $aMembership = $aMembershipCurrent;
    do {
        $iDateExpires = $aMembership['DateExpires'];
        $aMembership = getMemberMembershipInfo_current($iMemberId, $iDateExpires);
    } while($aMembership['ID'] == $aMembershipCurrent['ID'] && (int)$aMembership['DateExpires']);

    $aMembershipCurrent['DateStarts'] = $iDateStarts;
    $aMembershipCurrent['DateExpires'] = $iDateExpires;

    return $aMembershipCurrent;
}

/**
 * Checks if a given action is allowed for a given member and updates action information if the
 * action is performed.
 *
 * @param int $iMemberId			- ID of a member that is going to perform an action
 * @param int $actionID			- ID of the action itself
 * @param boolean $performAction	- if true, then action information is updated, i.e. action
 * 								  is 'performed'
 *
 * @return array(	CHECK_ACTION_RESULT => CHECK_ACTION_RESULT_ constant,
 * 					CHECK_ACTION_MESSAGE => CHECK_ACTION_MESSAGE_ constant,
 * 					CHECK_ACTION_PARAMETER => additional action parameter (string) )
 *
 *
 * NOTES:
 *
 * $result[CHECK_ACTION_MESSAGE] contains a message with detailed information about the result,
 * already processed by the language file
 *
 * if $result[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED then this node contains
 * an empty string
 *
 * The error messages themselves are stored in the language file. Additional variables are
 * passed to the languages.inc.php function _t_ext() as an array and can be used there in the form of
 * {0}, {1}, {2} ...
 *
 * Additional variables passed to the lang. file on errors (can be used in error messages):
 *
 * 	For all errors:
 *
 * 		$arg0[CHECK_ACTION_LANG_FILE_ACTION]	= name of the action
 * 		$arg0[CHECK_ACTION_LANG_FILE_MEMBERSHIP]= name of the current membership
 *
 * 	CHECK_ACTION_RESULT_LIMIT_REACHED:
 *
 * 		$arg0[CHECK_ACTION_LANG_FILE_LIMIT]		= limit on number of actions allowed for the member
 * 		$arg0[CHECK_ACTION_LANG_FILE_PERIOD]	= period that the limit is set for (in hours, 0 if unlimited)
 *
 * 	CHECK_ACTION_RESULT_NOT_ALLOWED_BEFORE:
 *
 * 		$arg0[CHECK_ACTION_LANG_FILE_BEFORE]	= date/time since when the action is allowed
 *
 * 	CHECK_ACTION_RESULT_NOT_ALLOWED_AFTER:
 *
 * 		$arg0[CHECK_ACTION_LANG_FILE_AFTER]		= date/time since when the action is not allowed
 *
 * $result[CHECK_ACTION_PARAMETER] contains an additional parameter that can be considered
 * when performing the action (like the number of profiles to show in search result)
*/
function checkAction($iMemberId, $actionID, $performAction = false, $iForcedProfID = 0, $isCheckMemberStatus = true)
{
    global $logged;
    global $site;

    //output array initialization

    $result = array();
    $arrLangFileParams = array();

    $dateFormat = "F j, Y, g:i a";	//used when displaying error messages

    //input validation

    $iMemberId = (int)$iMemberId;
    $actionID = (int)$actionID;
    $performAction = $performAction ? true : false;

    //get current member's membership information

    $arrMembership = getMemberMembershipInfo($iMemberId, '', $isCheckMemberStatus);

    $arrLangFileParams[CHECK_ACTION_LANG_FILE_MEMBERSHIP] = $arrMembership['Name'];
    $arrLangFileParams[CHECK_ACTION_LANG_FILE_SITE_EMAIL] = $site['email'];

    //profile active check

    if($arrMembership['ID'] != MEMBERSHIP_ID_NON_MEMBER || $logged['admin'] || $logged['moderator']) {
        $iDestID = $iMemberId;
        if ( (isAdmin() || isModerator()) && $iForcedProfID>0) {
            $iDestID = $iForcedProfID;
            $performAction = false;
        }
    }

    //get permissions for the current action

    $resMembershipAction = db_res("
        SELECT	Name,
                IDAction,
                AllowedCount,
                AllowedPeriodLen,
                UNIX_TIMESTAMP(AllowedPeriodStart) as AllowedPeriodStart,
                UNIX_TIMESTAMP(AllowedPeriodEnd) as AllowedPeriodEnd,
                AdditionalParamValue
        FROM	`sys_acl_actions`
                LEFT JOIN `sys_acl_matrix`
                ON	`sys_acl_matrix`.IDAction = `sys_acl_actions`.ID
                    AND `sys_acl_matrix`.IDLevel = {$arrMembership['ID']}
        WHERE	`sys_acl_actions`.ID = $actionID");

    //no such action

    if($resMembershipAction->rowCount() < 1) {
        echo "<br /><b>checkAction()</b> fatal error. Unknown action ID: $actionID<br />";
        exit();
    }

    $arrAction = $resMembershipAction->fetch();

    $result[CHECK_ACTION_PARAMETER]	= $arrAction['AdditionalParamValue'];
    $arrLangFileParams[CHECK_ACTION_LANG_FILE_ACTION] = _t('_mma_' . str_replace(' ', '_', $arrAction['Name']));

    //action is not allowed for the current membership

    if(is_null($arrAction['IDAction'])) {
        $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_NOT_ALLOWED;
        $result[CHECK_ACTION_MESSAGE] = _t_ext(CHECK_ACTION_MESSAGE_NOT_ALLOWED, $arrLangFileParams);
        return $result;
    }

    //Check fixed period limitations if present (also for non-members)

    if($arrAction['AllowedPeriodStart'] && time() < $arrAction['AllowedPeriodStart']) {

        $arrLangFileParams[CHECK_ACTION_LANG_FILE_BEFORE] = date($dateFormat, $arrAction['AllowedPeriodStart']);

        $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_NOT_ALLOWED_BEFORE;
        $result[CHECK_ACTION_MESSAGE] = _t_ext(CHECK_ACTION_MESSAGE_NOT_ALLOWED_BEFORE, $arrLangFileParams);

        return $result;
    }

    if($arrAction['AllowedPeriodEnd'] && time() > $arrAction['AllowedPeriodEnd']) {

        $arrLangFileParams[CHECK_ACTION_LANG_FILE_AFTER] = date($dateFormat, $arrAction['AllowedPeriodEnd']);

        $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_NOT_ALLOWED_AFTER;
        $result[CHECK_ACTION_MESSAGE] = _t_ext(CHECK_ACTION_MESSAGE_NOT_ALLOWED_AFTER, $arrLangFileParams);

        return $result;
    }

    //if non-member, allow action without performing further checks

    if ($arrMembership['ID'] == MEMBERSHIP_ID_NON_MEMBER) {
        $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_ALLOWED;
        return $result;
    }

    //check other limitations (for members only)

    $allowedCnt = (int)$arrAction['AllowedCount'];		//number of allowed actions
                                                        //if not specified or 0, number of
                                                        //actions is unlimited

    $periodLen = (int)$arrAction['AllowedPeriodLen'];	//period for AllowedCount in hours
                                                        //if not specified, AllowedCount is
                                                        //treated as total number of actions
                                                        //permitted

    //number of actions is limited

    if($allowedCnt > 0) {
        //get current action info for the member

        $actionTrack = db_res("SELECT ActionsLeft,
                                      UNIX_TIMESTAMP(ValidSince) as ValidSince
                               FROM `sys_acl_actions_track`
                               WHERE IDAction = $actionID AND IDMember = $iMemberId");

        $actionsLeft = $performAction ? $allowedCnt - 1 : $allowedCnt;
        $validSince = time();
        $actionTrack = $actionTrack->fetch();
        
        //member is requesting/performing this action for the first time,
        //and there is no corresponding record in sys_acl_actions_track table

        if (!$actionTrack) {
            //add action to sys_acl_actions_track table
            db_res("
                INSERT INTO `sys_acl_actions_track` (IDAction, IDMember, ActionsLeft, ValidSince)
                VALUES ($actionID, $iMemberId, $actionsLeft, FROM_UNIXTIME($validSince))");

            $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_ALLOWED;
            return $result;
        }

        //action record in sys_acl_actions_track table is out of date         

        $periodEnd = (int)$actionTrack['ValidSince'] + $periodLen * 3600; //ValidSince is in seconds, PeriodLen is in hours

        if($periodLen > 0 && $periodEnd < time()) {
            db_res("
                UPDATE	`sys_acl_actions_track`
                SET		ActionsLeft = $actionsLeft, ValidSince = FROM_UNIXTIME($validSince)
                WHERE	IDAction = $actionID AND IDMember = $iMemberId");

            $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_ALLOWED;
            return $result;
        }

        //action record is up to date

        $actionsLeft = (int)$actionTrack['ActionsLeft'];

        //action limit reached for now

        if($actionsLeft <= 0 ) {
            $arrLangFileParams[CHECK_ACTION_LANG_FILE_LIMIT] = $allowedCnt;
            $arrLangFileParams[CHECK_ACTION_LANG_FILE_PERIOD] = $periodLen;

            $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_LIMIT_REACHED;
            $result[CHECK_ACTION_MESSAGE] = '<div style="width: 80%">' .
                _t_ext(CHECK_ACTION_MESSAGE_LIMIT_REACHED, $arrLangFileParams) .
                ($periodLen > 0 ? _t_ext(CHECK_ACTION_MESSAGE_MESSAGE_EVERY_PERIOD, $arrLangFileParams) : '') .
                '.</div>';

            return $result;
        }

        if($performAction) {
            $actionsLeft--;

            db_res("
                UPDATE `sys_acl_actions_track`
                SET ActionsLeft = $actionsLeft
                WHERE IDAction = $actionID AND IDMember = $iMemberId");
        }
    }

    $result[CHECK_ACTION_RESULT] = CHECK_ACTION_RESULT_ALLOWED;
    return $result;
}

/**
 * Buy a membership for a member
 *
 * @param int $iMemberId			- member that is going to get the membership
 * @param int $iMembershipId		- bought membership
 * @param int $sTransactionId	- internal key of the transaction (ID from Transactions table)
 * @param boolean $bStartsNow	- if true, the membership will start immediately;
 *								  if false, the membership will start after the current
 *								  membership expires
 *
 * @return boolean				- true in case of success, false in case of failure
 *
 */
function buyMembership($iMemberId, $iMembershipId, $sTransactionId, $bStartsNow = false)
{
    $iMemberId = (int)$iMemberId;
    $iMembershipId = (int)$iMembershipId;

    $aMembership = db_arr("SELECT
            `tl`.`ID` AS `ID`,
            `tlp`.`Days` AS `Days`,
            `tl`.`Active` AS `Active`,
            `tl`.`Purchasable` AS `Purchasable`
        FROM `sys_acl_levels` AS `tl`
        LEFT JOIN `sys_acl_level_prices` AS `tlp` ON `tl`.`ID`=`tlp`.`IDLevel`
        WHERE `tlp`.`id`='" . $iMembershipId . "'"
    );
    if(!is_array($aMembership) || empty($aMembership))
        return false;

    $iMembershipId = (int)$aMembership['ID'];

    //check for predefined non-purchasable memberships
    if(in_array($iMembershipId, array(MEMBERSHIP_ID_NON_MEMBER, MEMBERSHIP_ID_STANDARD, MEMBERSHIP_ID_PROMOTION)))
        return false;

    //check if membership is active and purchasable
    if($aMembership['Active'] != 'yes' || $aMembership['Purchasable'] != 'yes')
        return false;

    return setMembership($iMemberId, $iMembershipId, $aMembership['Days'], $bStartsNow, $sTransactionId);
}

/**
 * Set a membership for a member
 *
 * @param int $iMemberId			- member that is going to get the membership
 * @param int $iMembershipId		- membership that is going to be assigned to the member
 * 								  if $iMembershipId == MEMBERSHIP_ID_STANDARD then $days
 *								  and $bStartsNow parameters are not used, so Standard
 *								  membership is always set immediately and `forever`
 *
 * @param int $days				- number of days to set membership for
 *								  if 0, then the membership is set forever
 *
 * @param boolean $bStartsNow	- if true, the membership will start immediately;
 *								  if false, the membership will start after the current
 *								  membership expires
 *
 * @return boolean				- true in case of success, false in case of failure
 *
 *
 */
function setMembership($iMemberId, $iMembershipId, $iDays = 0, $bStartsNow = false, $sTransactionId = '', $isSendMail = true)
{
    $iMemberId = (int)$iMemberId;
    $iMembershipId = (int)$iMembershipId;
    $iDays = (int)$iDays;
    $bStartsNow = $bStartsNow ? true : false;

    $SECONDS_IN_DAY = 86400;

    if(!$iMemberId)
        $iMemberId = -1;

    if(empty($sTransactionId))
        $sTransactionId = 'NULL';

    //check if member exists
    $aProfileInfo = getProfileInfo($iMemberId);
    if(!$aProfileInfo)
        return false;

    //check if membership exists
    $iRes = (int)db_value("SELECT COUNT(`ID`) FROM `sys_acl_levels` WHERE `ID`='" . $iMembershipId . "' LIMIT 1");
    if($iRes != 1)
        return false;

    if($iMembershipId == MEMBERSHIP_ID_NON_MEMBER)
        return false;

    $aMembershipCurrent = getMemberMembershipInfo($iMemberId);
    $aMembershipLatest = getMemberMembershipInfo_latest($iMemberId);

    /**
     * Setting Standard membership level
     */
    if($iMembershipId == MEMBERSHIP_ID_STANDARD) {
        if($aMembershipCurrent['ID'] == MEMBERSHIP_ID_STANDARD)
            return true;

        //delete any present and future memberships
        $res = db_res("DELETE FROM `sys_acl_levels_members` WHERE `IDMember`='" . $iMemberId . "' AND (`DateExpires` IS NULL OR `DateExpires`>NOW())");
        if(db_affected_rows($res) <= 0)
            return false;
    }

    if($iDays < 0)
        return false;

    $iDateStarts = time();
    if(!$bStartsNow) {
        /**
         * make the membership starts after the latest membership expires
         * or return false if latest membership isn't Standard and is lifetime membership
         */
        if(!is_null($aMembershipLatest['DateExpires']))
            $iDateStarts = $aMembershipLatest['DateExpires'];
        else if(is_null($aMembershipLatest['DateExpires']) && $aMembershipLatest['ID'] != MEMBERSHIP_ID_STANDARD)
            return false;
    } else {
        // delete previous profile's membership level and actions traces
        db_res("DELETE FROM `sys_acl_levels_members` WHERE `IDMember`='" . $iMemberId . "'"); 
        clearActionsTracksForMember($iMemberId);
    }

    /**
     * set lifetime membership if 0 days is used.
     */
    $iDateExpires = $iDays != 0 ? (int)$iDateStarts + $iDays * $SECONDS_IN_DAY : 'NULL';
    $res = db_res("INSERT `sys_acl_levels_members` (`IDMember`, `IDLevel`, `DateStarts`, `DateExpires`, `TransactionID`) VALUES ('" . $iMemberId . "', '" . $iMembershipId . "', FROM_UNIXTIME(" . $iDateStarts . "), FROM_UNIXTIME(" . $iDateExpires . "), '" . $sTransactionId . "')");
    if(db_affected_rows($res) <= 0)
       return false;

    //Set Membership Alert
    bx_import('BxDolAlerts');
    $oZ = new BxDolAlerts('profile', 'set_membership', '', $iMemberId, array('mlevel'=> $iMembershipId, 'days' => $iDays, 'starts_now' => $bStartsNow, 'txn_id' => $sTransactionId));
    $oZ->alert();

    //Notify user about changed membership level
    bx_import('BxDolEmailTemplates');
    $oEmailTemplate = new BxDolEmailTemplates();
    $aTemplate = $oEmailTemplate->getTemplate('t_MemChanged', $iMemberId);

    $aMembershipInfo = getMembershipInfo($iMembershipId);
    $aTemplateVars = array(
        'MembershipLevel' => $aMembershipInfo['Name']
    );

    if ($isSendMail)
        sendMail( $aProfileInfo['Email'], $aTemplate['Subject'], $aTemplate['Body'], $iMemberId, $aTemplateVars);

	//Notify admin about changed user's membership level
    $aTemplate = $oEmailTemplate->parseTemplate('t_UserMemChanged', $aTemplateVars, $iMemberId);
    sendMail($GLOBALS['site']['email'], $aTemplate['Subject'], $aTemplate['Body']);

    return true;
}

/**
 * Get the list of existing memberships
 *
 * @param bool $purchasableOnly	- if true, fetches only purchasable memberships;
 * 								  'purchasable' here means that:
 *								  1. MemLevels.Purchasable = 'yes'
 *								  2. MemLevels.Active = 'yes'
 * 								  3. there is at least one pricing option for the membership
 *
 * @return array( membershipID_1 => membershipName_1,  membershipID_2 => membershipName_2, ...) - if no such memberships, then just array()
 *
 *
 */
function getMemberships($purchasableOnly = false)
{
    $result = array();

    $queryPurchasable = '';

    if($purchasableOnly) {
        $queryPurchasable = "INNER JOIN `sys_acl_level_prices` ON `sys_acl_level_prices`.IDLevel = `sys_acl_levels`.ID WHERE Purchasable = 'yes' AND Active = 'yes'";
    }

    $resMemLevels = db_res("SELECT DISTINCT `sys_acl_levels`.ID, `sys_acl_levels`.Name FROM `sys_acl_levels` $queryPurchasable");

    while($r = $resMemLevels->fetch()) {
        $result[(int)$r['ID']] = $r['Name'];
    }

    return $result;
}

/**
 * Get pricing options for the given membership
 *
 * @param int $iMembershipId	- membership to get prices for
 *
 * @return array( days1 => price1, days2 => price2, ...) - if no prices set, then just array()
 *
 *
 */
function getMembershipPrices($iMembershipId)
{
    $iMembershipId = (int)$iMembershipId;
    $result = array();

    $resMemLevelPrices = db_res("SELECT Days, Price FROM `sys_acl_level_prices` WHERE IDLevel = $iMembershipId ORDER BY Days ASC");

    while(list($days, $price) = $resMemLevelPrices->fetch()) {
        $result[(int)$days] = (float)$price;
    }

    return $result;
}

/**
 * Get info about a given membership
 *
 * @param int $iMembershipId	- membership to get info about
 *
 * @return array(	'Name' => name,
 * 					'Active' => active,
 *					'Purchasable' => purchasable,
 *					'Removable' => removable)
 *
 *
 */
function getMembershipInfo($iMembershipId)
{
    $iMembershipId = (int)$iMembershipId;
    $result = array();

    $resMemLevels = db_res("SELECT Name, Active, Purchasable, Removable FROM `sys_acl_levels` WHERE ID = ?", [$iMembershipId]);

    if($resMemLevels->rowCount() > 0) {
        $result = $resMemLevels->fetch();
    }

    return $result;
}

/**
 * Define action, during defining all names are translated the following way:
 *  my action => BX_MY_ACTION
 *
 * @param $aActions array of actions from sys_acl_actions table, with default array keys (starting from 0) and text values
 */
function defineMembershipActions ($aActionsAll, $sPrefix = 'BX_')
{
    $aActions = array ();
    foreach ($aActionsAll as $sName)
        if (!defined($sPrefix . strtoupper(str_replace(' ', '_', $sName))))
            $aActions[] = $sName;
    if (!$aActions)
        return;

    $sPlaceholders = implode(',', array_fill(0, count($aActions), '?'));
    $res = db_res("SELECT `ID`, `Name` FROM `sys_acl_actions` WHERE `Name` IN({$sPlaceholders})", $aActions);
    while ($r = $res->fetch()) {
        define ($sPrefix . strtoupper(str_replace(' ', '_', $r['Name'])), $r['ID']);
    }
}

function membershipActionsCmp($a, $b)
{
    $sNameField = isset($a['Name']) ? 'Name' : 'title';
    return strcmp($a[$sNameField], $b[$sNameField]);
}

function translateMembershipActions (&$aActions)
{
    reset($aActions);
    $a = current ($aActions);
    $sNameField = isset($a['Name']) ? 'Name' : 'title';

    foreach ($aActions as $i => $a)
        $aActions[$i][$sNameField] = _t('_mma_' . str_replace(' ', '_', $a[$sNameField]));

    usort ($aActions, 'membershipActionsCmp');
}

function markMembershipAsExpiring($iMemberId, $iLevelId, $sTransactionId)
{
	$res = db_res("UPDATE `sys_acl_levels_members` SET `Expiring`='1' WHERE `IDMember`= ? AND `IDLevel`= ? AND `TransactionID`= ? LIMIT 1", [
        $iMemberId,
        $iLevelId,
        $sTransactionId
    ]);
	return db_affected_rows($res) > 0;
}

function unmarkMembershipAsExpiring($iMemberId, $iLevelId, $sTransactionId)
{
	$res = db_res("UPDATE `sys_acl_levels_members` SET `Expiring`='0' WHERE `IDMember`= ? AND `IDLevel`= ? AND `TransactionID`= ? LIMIT 1", [
        $iMemberId,
        $iLevelId,
        $sTransactionId
    ]);
	return db_affected_rows($res) > 0;
}

function unmarkMembershipAsExpiringAll()
{
	$res = db_res("UPDATE `sys_acl_levels_members` SET `Expiring`='0' WHERE 1");
	return db_affected_rows($res) > 0;
}

function clearActionsTracksForMember($iMemberId)
{
	return db_res("DELETE FROM `sys_acl_actions_track` WHERE `IDMember` = ?", [$iMemberId]);
}
