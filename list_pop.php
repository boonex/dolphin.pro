<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolEmailTemplates.php' );

$_page['name_index'] = 44;
$_ni = $_page['name_index'];

// check logged
$logged['member'] = member_auth(0);

//get logged profile's id
$iProfileId = getLoggedId();

//-- process some internal vars --//
$iTargetId  = (int)bx_get('ID');
$sTargetsId = isset($_POST['list_id']) ?  $_POST['list_id'] : '';
$sAction    = false != bx_get('action')? bx_get('action')   : '';
//--

//define ajax mode
$bAjxMod = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    &&  $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;

$sJQueryJS = '';
if($bAjxMod) {
	$sJQueryJS = genAjaxyPopupJS($iTargetId);

	header('Content-Type: text/html; charset=utf-8');
}
//-- process actions --//

switch ($sAction) {
    //generate member menu position settings
    case 'extra_menu' :
        $sPageCaption = _t( '_Member menu position' );
        $_page['header'] = $sPageCaption;

        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent( $sPageCaption
            , PageListMemberMenuSettings($iProfileId, $sAction)
            , $oTemplConfig -> PageListPop_db_num);
        break;

    //block profile
    case 'block':
        if ($bAjxMod) {
            echo PageListBlock($iProfileId, $iTargetId) . $sJQueryJS;
            exit;
        }

        $sPageCaption = _t( '_Block list' );
        $_page['header'] = $sPageCaption;
        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent( $sPageCaption
            , PageListBlock($iProfileId, $iTargetId)
            , $oTemplConfig -> PageListPop_db_num );
        break;

    //unblock profile
    case 'unblock':
        if ($bAjxMod) {
            echo PageListUnBlock($iProfileId, $iTargetId) . $sJQueryJS;
            exit;
        }

        $sPageCaption = _t('_Unblock');
        $_page['header'] = $sPageCaption;
        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent( $sPageCaption
            , PageListUnBlock($iProfileId, $iTargetId)
            , $oTemplConfig -> PageListPop_db_num );
        break;

    //add to hot list
    case 'hot':
        if ($bAjxMod) {
            echo PageListHot($iProfileId, $iTargetId) . $sJQueryJS;
            exit;
        }

        $sPageCaption = _t('_sys_cnts_bcpt_fave');
        $_page['header'] = $sPageCaption;
        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent($sPageCaption, PageListHot($iProfileId, $iTargetId), $oTemplConfig->PageListPop_db_num);
        break;

    //remove from hot list
    case 'remove_hot':
        if ($bAjxMod) {
            echo PageListHotRemove($iProfileId, $iTargetId) . $sJQueryJS;
            exit;
        }

        $sPageCaption = _t('_sys_cnts_bcpt_fave_remove');
        $_page['header'] = $sPageCaption;
        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent($sPageCaption, PageListHotRemove($iProfileId, $iTargetId), $oTemplConfig->PageListPop_db_num);
        break;

    //add to friends list
    case 'friend':
        if ($bAjxMod) {
            echo PageListFriend($iProfileId, $iTargetId) . $sJQueryJS;
            exit;
        }

        $sPageCaption = _t('_Friend list');
        $_page['header'] = $sPageCaption;
        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent( $sPageCaption
            , PageListFriend($iProfileId, $iTargetId)
            , $oTemplConfig -> PageListPop_db_num );
        break;

    //remove from friends list
    case 'remove_friend':
        if ($bAjxMod) {
            echo PageListFriendRemove($iProfileId, $iTargetId) . $sJQueryJS;
            exit;
        }

        $sPageCaption = _t('_Remove friend');
        $_page['header'] = $sPageCaption;
        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent( $sPageCaption
            , PageListFriendRemove($iProfileId, $iTargetId)
            , $oTemplConfig -> PageListPop_db_num );
        break;

    //report about spam
    case 'spam':
        $mTarget = $sTargetsId ? $sTargetsId : $iTargetId;

        if ($bAjxMod) {
            echo PageListSpam($iProfileId, $mTarget) . $sJQueryJS;
            exit;
        }

        $sPageCaption = _t('_Spam report');
        $GLOBALS['_page']['header'] = $sPageCaption;
        $GLOBALS['_page_cont'][$_ni]['page_main_code'] = DesignBoxContent( $sPageCaption
            , PageListSpam($iProfileId, $mTarget)
            , $oTemplConfig -> PageListPop_db_num);
        break;

    //changes profile status
    case 'change_status':
        if ( $bAjxMod && isset($_POST['status']) ) {
            echo ActionChangeStatus($iProfileId, $_POST['status']);
        }
        return;

    //change profile's status message
    case 'change_status_message':
        if ($bAjxMod) {
            echo ActionChangeStatusMessage();
        }
        return;

    default:
            if ($bAjxMod AND $iTargetId)
            {
                $mixedRes = PageListControl($sAction, $iProfileId, $iTargetId);
                if ($mixedRes)
                {
                    echo $mixedRes . genAjaxyPopupJS($iTargetId);
                    exit;
                }
            }
            $GLOBALS['_page']['header'] = _t('_Error occured');
            $GLOBALS['_page_cont'][$GLOBALS['_ni']]['page_main_code'] = _t('_Error occured');
        break;
}

//--

PageCode();

//-- FUNCTIONS --//

/**
 * Change status message
 *
 * @param $iMemberIdForce integer
 * @return void
 */
function ActionChangeStatusMessage($iMemberIdForce = 0)
{
    if ($iMemberIdForce) {
        $iMemberID = (int) $iMemberIdForce;
    } else {
        $iMemberID = getLoggedId();
    }

    if( $iMemberID && isset($_POST['status_message']) ) {
        $sNewStatusMessage = process_db_input($_POST['status_message']
            , BX_TAGS_STRIP, BX_SLASHES_AUTO);

        $sQuery = "UPDATE `Profiles` SET `UserStatusMessage`='{$sNewStatusMessage}'
            , `UserStatusMessageWhen` = UNIX_TIMESTAMP() WHERE `ID` = '{$iMemberID}'";

        if( db_res($sQuery, 0) ) {
            //send system alert
            bx_import('BxDolAlerts');
            $oZ = new BxDolAlerts('profile', 'edit_status_message'
                , $iMemberID, $iMemberID, array ($sNewStatusMessage));

            $oZ -> alert();

            createUserDataFile($iMemberID);
        }
    }
}

/**
 * Change profile status
 *
 * @param $iProfileId integer
 * @param $sStatus text
 * @return text
 */
function ActionChangeStatus($iProfileId, $sStatus = '')
{
    $iProfileId = (int) $iProfileId;
    $sOutputCode = '';

    $oUserStatus = new BxDolUserStatusView();
    if ( $oUserStatus -> getRegisteredStatus($sStatus) ) {
        //process status
        $sStatus = process_db_input($sStatus, BX_TAGS_STRIP, BX_SLASHES_AUTO);

        $sQuery = "UPDATE `Profiles` SET `UserStatus`='{$sStatus}', `DateLastNav` = NOW()
            WHERE `ID` = '{$iProfileId}'";

        if( db_res($sQuery, 0) ) {
            // send system event
            bx_import('BxDolAlerts');
            $oZ = new BxDolAlerts('profile', 'edit_status', $iProfileId, $iProfileId);
            $oZ -> alert();

            bx_import('BxTemplMemberMenu');
            $oMemberMenu = new BxTemplMemberMenu();
            $oMemberMenu -> deleteMemberMenuKeyFile($iProfileId);

            createUserDataFile($iProfileId);

            $sOutputCode  = $oUserStatus -> getStatusIcon($iProfileId);
        }
    }

    return $sOutputCode;
}

/**
 * Send report about spam
 *
 * @param $iProfileId integer
 * @param $mMembers mixed
 * @return text - html presentation data
 */
function PageListSpam($iProfileId, $mMembers = '')
{
    $iProfileId = (int) $iProfileId;

    //define list of members
    if( is_int($mMembers) ) {
        $sActionResult = _sendSpamReport($iProfileId, $mMembers);
    } else {
        //work with string
        $aMembers = explode(',', $mMembers);

        for($i = 0, $iCountMembers = count($aMembers); $i <= $iCountMembers; $i++) {
            if($aMembers[$i]) {
                if( '' != $sActionResult = _sendSpamReport($iProfileId, $aMembers[$i]) ) {
                    break;
                }
            }
        }
    }

    if(!$sActionResult) {
        $sActionResult = MsgBox( _t('_Report about spam was sent') );
    }

    return $sActionResult;
}

/**
 * Delete from friends list
 *
 * @param $iProfileId integer
 * @param $iMemberId integer
 * @return text - html presentation data
 */
function PageListFriendRemove($iProfileId, $iMemberId = 0)
{
    $sOutputCode = '';
    $iProfileId = (int) $iProfileId;
    $iMemberId = (int) $iMemberId;

    if(!$iMemberId || !getProfileInfo($iMemberId))
        return MsgBox( _t('_Failed to apply changes'));

    bx_import('BxTemplCommunicator');
    $oCommunicator = new BxTemplCommunicator(array('member_id' => $iProfileId));

    $aParams = array($iMemberId);
    $oCommunicator -> execFunction( '_deleteRequest', 'sys_friend_list', $aParams, array(1, 1));

       return MsgBox( _t('_Friend was removed') );
}

/**
 * Put to friends list
 *
 * @param $iProfileId integer
 * @param $iMemberId integer
 * @return text - html presentation data
 */
function PageListFriend($iProfileId, $iMemberId = 0)
{
    $sOutputCode = '';
    $iProfileId = (int) $iProfileId;
    $iMemberId = (int) $iMemberId;

    if( !$iMemberId || !getProfileInfo($iMemberId) ) {
        return MsgBox( _t('_Failed to apply changes') );
    }

    $iUseriId = getLoggedId();
    $aResult = checkAction($iUseriId, ACTION_ID_SEND_FRIEND_REQUEST, $bPerformAction);
    if($aResult[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED)
        return MsgBox($aResult[CHECK_ACTION_MESSAGE]);

    // block members
    if ( isBlocked($iMemberId, $iProfileId) ) {
        return MsgBox( _t('_You have blocked by this profile') );
    }

    //check friends pair
    $aFriendsInfo = db_assoc_arr("SELECT * FROM `sys_friend_list`
        WHERE (`ID`='{$iProfileId}' AND `Profile`='{$iMemberId}')
            OR (`ID`='{$iMemberId}' AND `Profile` = '{$iProfileId}')");

    //-- process friend request --//
    if($aFriendsInfo) {
        if( isset($aFriendsInfo['Check']) && $aFriendsInfo['Check'] == 1) {
            $sOutputCode = MsgBox( _t('_already_in_friend_list') );
        } else if( isset($aFriendsInfo['ID'], $aFriendsInfo['Check']) ) {
            if ($iProfileId == $aFriendsInfo['ID'] && $aFriendsInfo['Check'] == 0) {
                $sOutputCode = MsgBox( _t('_pending_friend_request') );
            } else {
                //make paier as friends
                $sQuery = "UPDATE `sys_friend_list` SET `Check` = '1'
                    WHERE `ID` = '{$iMemberId}' AND `Profile` = '{$iProfileId}';";

                if ( db_res($sQuery, 0) ) {
                    $sOutputCode = MsgBox( _t('_User was added to friend list') );

                    //send system alert
                    bx_import('BxDolAlerts');
                    $oZ = new BxDolAlerts('friend', 'accept', $iMemberId, $iProfileId);
                    $oZ->alert();
                } else {
                    $sOutputCode = MsgBox( _t('_Failed to apply changes') );
                }
            }
        } else {
            $sOutputCode = MsgBox( _t('_Failed to apply changes') );
        }
    } else {
        //create new friends request
        $sQuery = "INSERT INTO `sys_friend_list` SET
            `ID` = '{$iProfileId}', `Profile` = '{$iMemberId}', `Check` = '0'";

        if ( db_res($sQuery, 0) ) {
            $sOutputCode = MsgBox( _t('_User was invited to friend list') );

            //send system alert
            bx_import('BxDolAlerts');
            $oZ = new BxDolAlerts('friend', 'request', $iMemberId, $iProfileId);
            $oZ -> alert();

            // send email notification
            $oEmailTemplate = new BxDolEmailTemplates();
            $aTemplate = $oEmailTemplate -> getTemplate('t_FriendRequest', $iMemberId);

            $aRecipient = getProfileInfo($iMemberId);
            $aPlus = array(
                'Recipient'     => getNickName($aRecipient['ID']),
                'SenderLink'	=> getProfileLink($iProfileId),
                'Sender'		=> getNickName($iProfileId),

                'RequestLink'	=> BX_DOL_URL_ROOT
                    . 'communicator.php?communicator_mode=friends_requests',
            );

            sendMail( $aRecipient['Email'], $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus );
         } else {
            $sOutputCode = MsgBox( _t('_Failed to apply changes') );
        }
    }
    //--

    return $sOutputCode;
}

/**
 * Put to hot list
 *
 * @param integer $iId profile initiating the action
 * @param integer $iProfileId target profile
 * @return text - html presentation data
 */
function PageListHot($iId, $iProfileId = 0)
{
    $sOutputCode = '';
    $iId = (int) $iId;
    $iProfileId = (int) $iProfileId;

    if(!$iProfileId || !getProfileInfo($iProfileId))
        return MsgBox(_t('_Failed to apply changes'));

    $sQuery = "INSERT IGNORE INTO `sys_fave_list` SET `ID` = '{$iId}', `Profile` = '{$iProfileId}'";
    if((int)$GLOBALS['MySQL']->query($sQuery) > 0) {
        $sOutputCode = MsgBox( _t('_User was added to favourites') );

        //send system alert
        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts('fave', 'add', $iProfileId, $iId);
        $oZ -> alert();
    } else
        $sOutputCode = MsgBox(_t('_Failed to apply changes'));

    return $sOutputCode;
}

/**
 * Remove to hot list
 *
 * @param integer $iId profile initiating the action
 * @param integer $iProfileId target profile
 * @return text - html presentation data
 */
function PageListHotRemove($iId, $iProfileId = 0)
{
    $sOutputCode = '';
    $iId = (int)$iId;
    $iProfileId = (int)$iProfileId;

    if(!$iProfileId || !getProfileInfo($iProfileId))
        return MsgBox(_t('_Failed to apply changes'));

    $sQuery = "DELETE FROM `sys_fave_list` WHERE `ID`='{$iId}' AND `Profile`='{$iProfileId}'";
    if((int)$GLOBALS['MySQL']->query($sQuery) > 0) {
        $sOutputCode = MsgBox(_t('_User was removed from favourites'));

        //send system alert
        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts('fave', 'remove', $iProfileId, $iId);
        $oZ -> alert();
    } else
        $sOutputCode = MsgBox(_t('_Failed to apply changes'));

    return $sOutputCode;
}

/**
 * Unblock profile
 *
 * @param $sourceID
 * @param $targetID
 * @return unknown_type
 */
function PageListUnBlock($iProfileId, $iMemberId = 0)
{
    $sOutputCode = '';
    $iProfileId = (int) $iProfileId;
    $iMemberId = (int) $iMemberId;

    if( !$iMemberId || !getProfileInfo($iMemberId) ) {
        return MsgBox( _t('_Failed to apply changes') );
    }

    bx_import('BxDolCommunicator');
    $oCommunicator = new BxDolCommunicator(array('member_id' => $iMemberId));
    if( $oCommunicator -> _deleteRequest('sys_block_list', $iProfileId) ){
        $sOutputCode = MsgBox( _t('_User was removed from block list') );
    } else {
        $sOutputCode = MsgBox( _t('_Failed to apply changes') );
    }

    return $sOutputCode;
}

/**
 * Block profile
 *
 * @param $iProfileId integer
 * @param $iMemberId integer
 * @return text - html presentation data
 */
function PageListBlock($iProfileId, $iMemberId = 0)
{
    $sOutputCode = '';
    $iProfileId = (int) $iProfileId;
    $iMemberId = (int) $iMemberId;

    if( !$iMemberId || !getProfileInfo($iMemberId) ) {
        return MsgBox( _t('_Failed to apply changes') );
    }

    $sQuery = "REPLACE INTO `sys_block_list`
        SET `ID` = '{$iProfileId}', `Profile` = '{$iMemberId}'";

    if( db_res($sQuery, 0) ) {
        $sOutputCode = MsgBox( _t('_User was added to block list') );

        // send system alert
        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts('block', 'add', $iMemberId, $iProfileId);
        $oZ->alert();

        //delete from friends
        $sQuery = "DELETE FROM `sys_friend_list` WHERE
            (`ID`='{$iProfileId}' AND `Profile`='{$iMemberId}') OR (`ID`='{$iMemberId}'
                AND `Profile`='{$iProfileId}')";

        db_res($sQuery);
    } else {
        $sOutputCode = MsgBox( _t('_Failed to apply changes') );
    }

    return $sOutputCode;
}

/**
 * Change member's menu position ;
 *
 * @param $iProfileId integer
 * @param $sMenuPosition string
 * @param $sAction string
 * @return text - html presentation data
 */
function PageListMemberMenuSettings($iProfileId, $sAction)
{
    $iProfileId = (int) $iProfileId;

    // define default menu position;
    if ( isset($_COOKIE['menu_position']) ) {
        $sDefaultValue = clear_xss($_COOKIE['menu_position']);
    } else {
        $sDefaultValue = getParam('ext_nav_menu_top_position');
    }

    //get form
    $aForm = array (
        'form_attrs' => array (
            'action' => BX_DOL_URL_ROOT . 'list_pop.php?action=' . clear_xss($sAction),
            'method' => 'post',
            'name' => 'menu_position_form'
        ),
        'params' => array (
            'db' => array(
                'submit_name' => 'do_submit',
            ),
        ),
        'inputs' => array(
            array(
                'type' => 'radio_set',
                'name' => 'menu_settings',
                'caption' => 'Position',
                'dv' => '<br />',
                'values' => array(
                    'top'    => _t('_Top'),
                    'bottom' => _t('_Bottom'),
                    'static' => _t('_Static'),
                ),
                'required' => true,
                'checker' => array (
                    'func' => 'length',
                    'params' => array(3, 6),
                    'error' => _t('_Error occured'),
                ),
                'value' => $sDefaultValue,
                'db' => array (
                    'pass' => 'Xss',
                ),
            ),
            array(
                'type' => 'submit',
                'name' => 'do_submit',
                'value' => _t('_Save Changes'),
            )
        ),
    );

    $oForm = new BxTemplFormView($aForm);
    $oForm -> initChecker();

    if ( $oForm -> isSubmittedAndValid() ) {
        $sCode  = MsgBox( _t('_Saved') );
        $sCode .=
        '
            <script type="text/javascript">
                opener.location.reload();
                window.close();
            </script>
        ';

        //change menu position
        setcookie("menu_position", $oForm -> getCleanValue('menu_settings')
            , time() + 60 * 60 * 24 * 180);

        //clear member menu cache
        bx_import('BxDolMemberMenu');
        $oMemberMenu = new BxDolMemberMenu();
        $oMemberMenu -> deleteMemberMenuKeyFile($iProfileId);
    } else {
        $sCode = $oForm->getCode();
    }

    return $sCode;
}

/**
 * Perform admin or moderator actions
 *
 * @param $sAction string
 * @param $iViewerId integer
 * @param $iTargetId integer
 * @return mixed - HTML code or FALSE
 */
function PageListControl($sAction, $iViewerId, $iTargetId)
{
    $sAction   = clear_xss($sAction);
    $iViewerId = (int)$iViewerId;
    $iTargetId = (int)$iTargetId;
    
    $mixedRes = FALSE;
    $sMsg = '_Error';
    if (isAdmin($iViewerId) OR (isModerator($iViewerId) AND $iViewerId != $iTargetId))
    {
        switch ($sAction)
        {
            case 'activate':
            case 'deactivate':
                $mixedRes = _setStatus($iTargetId, $sAction);
                break;
            case 'ban':
                if (bx_admin_profile_ban_control($iTargetId))
                    $sMsg = '_Success';
                $mixedRes = MsgBox(_t($sMsg));
                break;
            case 'unban':
                if (bx_admin_profile_ban_control($iTargetId, FALSE))
                    $sMsg = '_Success';
                $mixedRes = MsgBox(_t($sMsg));
                break;
            case 'featured':
            case 'unfeatured':
                $mixedRes = _setFeature($iTargetId, $sAction);
                break;
            case 'delete':
                profile_delete($iTargetId);
                $mixedRes = MsgBox(_t('_Success')) . genAjaxyPopupJS($iTargetId, 'ajaxy_popup_result_div', BX_DOL_URL_ROOT . 'browse.php');
                break;
            case 'delete_spam':
                profile_delete($iTargetId, TRUE);
                $mixedRes = MsgBox(_t('_Success')) . genAjaxyPopupJS($iTargetId, 'ajaxy_popup_result_div', BX_DOL_URL_ROOT . 'browse.php');
                break;
            default:            
        }        
    }
    return $mixedRes;
}

/**
 * Change profile status
 *
 * @param $iTargetId integer
 * @param $sAction string
 * @return HTML - code for ajax popup
 */
function _setStatus($iTargetId, $sAction)
{
    $sStatus = 'Approval';
    $bSendActMail = FALSE;
    $sMsg = '_Error';
    if ($sAction == 'activate')
    {
        $sStatus = 'Active';
        $bSendActMail = TRUE;
    }
    if (bx_admin_profile_change_status($iTargetId, $sStatus, $bSendActMail))
        $sMsg = '_Success';
    return MsgBox(_t($sMsg));
}

/**
 * Change featured status
 *
 * @param $iTargetId integer
 * @param $sType string
 * @return HTML - code for ajax popup
 */
function _setFeature($iTargetId, $sType)
{
    $bFeature = $sType == 'featured' ? TRUE : FALSE;
    $sMsg = '_Error';
    if (bx_admin_profile_featured_control($iTargetId, $bFeature))
        $sMsg = '_Success';
    return MsgBox(_t($sMsg));
}

/**
 * Send spam report
 *
 * @param $iProfileId integer
 * @param $iMemberId integer
 * @return text - error message
 */
function _sendSpamReport($iProfileId, $iMemberId)
{
    global $site;

    $iProfileId = (int) $iProfileId;
    $iMemberId = $iMemberId ? (int) $iMemberId : -1;

    if( !$iMemberId || !getProfileInfo($iMemberId) ) {
        return MsgBox( _t('_Failed to apply changes') );
    }

    //get email template
    $oEmailTemplate = new BxDolEmailTemplates();
    $aTemplate = $oEmailTemplate -> getTemplate('t_SpamReport');

    //-- get reporter information --//
    $aReporter = getProfileInfo($iProfileId);

    $aPlus = array();
    $aPlus['reporterID'] = $iProfileId;
    $aPlus['reporterNick'] = getNickName($aReporter['ID']);
    //--

    //-- get spamer info --//
    $aSpamerInfo = getProfileInfo($iMemberId);
    $aPlus['spamerID'] = $iMemberId;
    $aPlus['spamerNick'] = getNickName($aSpamerInfo['ID']);
    //--

    //send message about spam
    if( !sendMail( $site['email'], $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus ) ) {
        return MsgBox( _t('_Report about spam failed to sent') );
    }
}

//--
