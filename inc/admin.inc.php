<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );

function login_form($text = "", $member = 0, $bAjaxMode = false, $sLoginFormParams = '')
{
    global $site;
    global $_page_cont;
    global $_page;

    if($member == 1) {
        LoginFormAdmin();
        exit;
    }

    if ($bAjaxMode)
        $sLoginFormParams .= ' no_join_text';

    $sLoginFormContent = getMemberLoginFormCode('login_box_form', $sLoginFormParams);

    if($bAjaxMode) {
        $iDesignBox = 11;
        $sContent = $sLoginFormContent;
        $sCaptionItems = '<div class="dbTopMenu"><i class="bx-popup-element-close sys-icon times"></i></div>';

        $sJoinFormContent = empty($_REQUEST['add_join_form']) ? '' : getMemberJoinFormCode();
        if(!empty($sJoinFormContent)) {
            $iDesignBox = 3;
            $sContent = $GLOBALS['oSysTemplate']->parseHtmlByName('login_join_popup.html', array(
                'login_form' => $sLoginFormContent,
                'join_form' => $sJoinFormContent,
                'top_menu' => $sCaptionItems,
            ));
        }

        $sCaption = _t('_Login');
        $sMemberLoginFormAjx = $GLOBALS['oFunctions']->transBox(
            DesignBoxContent($sCaption, $sContent, $iDesignBox, $sCaptionItems), true
        );

        header('Content-Type: text/html; charset=utf-8');
        echo $sMemberLoginFormAjx;
        exit;
    }

	$_page['name_index'] = 0;
    $_page['header'] = $site['title'] . ' ' . _t('_Login');
    $_page['header_text'] = _t('_Login');
    $_page_cont[0]['page_main_code'] = '<div class="controlsDiv">' . ($text ? "<h3>$text</h3>" : '') . $sLoginFormContent . '</div>';

    PageCode();
    exit;
}

function activation_mail( $ID, $text = 1 )
{
    global $ret;

    $ID = (int)$ID;
    $p_arr = db_arr( "SELECT `Email` FROM `Profiles` WHERE `ID` = '$ID'" );
    if ( !$p_arr ) {
        $ret['ErrorCode'] = 7;
        return false;
    }

    bx_import('BxDolEmailTemplates');
    $rEmailTemplate = new BxDolEmailTemplates();
    $aTemplate = $rEmailTemplate -> getTemplate('t_Confirmation', $ID);
    $recipient  = $p_arr['Email'];

    $sConfirmationCode	= base64_encode( base64_encode( crypt( $recipient, CRYPT_EXT_DES ? "secret_co" : "se" ) ) );
    $sConfirmationLink	= BX_DOL_URL_ROOT . "profile_activate.php?ConfID={$ID}&ConfCode=" . urlencode( $sConfirmationCode );

    $aPlus = array();
    $aPlus['ConfCode'] = $sConfirmationCode;
    $aPlus['ConfirmationLink'] = $sConfirmationLink;

    $mail_ret = sendMail( $recipient, $aTemplate['Subject'], $aTemplate['Body'], $ID, $aPlus, 'html', false, true );

    if ( $mail_ret ) {
        if ( $text ) {
            $page_text .= '<div class="Notice">' . _t("_EMAIL_CONF_SENT") . "</div>";

            $page_text .= "<center><form method=get action=\"" . BX_DOL_URL_ROOT . "profile_activate.php\">";
            $page_text .= "<table class=text2 cellspacing=0 cellpadding=0><td><b>"._t("_ENTER_CONF_CODE").":</b>&nbsp;</td><td><input type=hidden name=\"ConfID\" value=\"{$ID}\">";
            $page_text .= '<input class=no type="text" name="ConfCode" size=30></td><td>&nbsp;</td>';
            $page_text .= '<td><input class=no type="submit" value="'._t("_Submit").'"></td></table>';
            $page_text .= '</form></center><br />';
        } else
            return true;
    } else {
        if ( $text )
            $page_text .= "<br /><br />"._t("_EMAIL_CONF_NOT_SENT");
        else {
            $ret['ErrorCode'] = 10;
            return false;
        }
    }
    return ($text) ? $page_text : true;
}

function mem_expiration_letter( $ID, $membership_name, $expire_days )
{
    $ID = (int)$ID;

    if ( !$ID )
        return false;

    $p_arr = db_arr( "SELECT `Email` FROM `Profiles` WHERE `ID` = $ID", 0 );
    if ( !$p_arr )
        return false;

    bx_import('BxDolEmailTemplates');
    $rEmailTemplate = new BxDolEmailTemplates();
    $aTemplate = $rEmailTemplate -> getTemplate( 't_MemExpiration', $ID ) ;

    $recipient  = $p_arr['Email'];

    $aPlus = array();
    $aPlus['MembershipName'] = $membership_name;
    $aPlus['ExpireDays'] = $expire_days;

    $mail_ret = sendMail( $recipient, $aTemplate['Subject'], $aTemplate['Body'], $ID, $aPlus  );

    if ($mail_ret)
        return true;
    else
        return false;
}

function getID( $str, $with_email = 1 )
{
    if ( $with_email ) {
        bx_import('BxDolForm');
        if (BxDolFormCheckerHelper::checkEmail($str)) {
            $str = process_db_input($str);
            $mail_arr = db_arr( "SELECT `ID` FROM `Profiles` WHERE `Email` = '$str'" );
            if ( (int)$mail_arr['ID'] ) {
                return (int)$mail_arr['ID'];
            }
        }
    }
    
    $iID = (int)db_value( "SELECT `ID` FROM `Profiles` WHERE `NickName` = ?", [$str]);

    if(!$iID) {
        $aProfile = getProfileInfo($str);
        $iID = isset($aProfile['ID']) ? $aProfile['ID'] : 0;
    }
    return $iID;
}

// check encrypted password (ex., from Cookie)
function check_login($ID, $passwd, $iRole = BX_DOL_ROLE_MEMBER, $error_handle = true)
{
    $ID = (int)$ID;

    if (!$ID) {
        if ($error_handle)
            login_form(_t("_PROFILE_ERR"), $member);
        return false;
    }

    switch ($iRole) {
        case BX_DOL_ROLE_MEMBER: $member = 0; break;
        case BX_DOL_ROLE_ADMIN:  $member = 1; break;
    }

    $aProfile = getProfileInfo($ID);

    // If no such members
    if (!$aProfile) {
        if ($error_handle)
            login_form(_t("_PROFILE_ERR"), $member);
        return false;
    }

    // If password is incorrect
    if (strcmp($aProfile['Password'], $passwd) !== 0) {
        if ($error_handle)
            login_form(_t("_INVALID_PASSWD"), $member);
        return false;
    }

    if (!((int)$aProfile['Role'] & $iRole)) {
        if ($error_handle)
          login_form(_t("_INVALID_ROLE"), $member);
        return false;
    }

    if(((int)$aProfile['Role'] & BX_DOL_ROLE_ADMIN) || ((int)$aProfile['Role'] & BX_DOL_ROLE_MODERATOR)) {
        if( 'on' != getParam('ext_nav_menu_enabled') ) {
            update_date_lastnav($ID);
        }

        return true;
    }

    // if IP is banned
    if ((2 == getParam('ipBlacklistMode') && bx_is_ip_blocked()) || ('on' == getParam('sys_dnsbl_enable') && 'block' == getParam('sys_dnsbl_behaviour') && bx_is_ip_dns_blacklisted('', 'login'))) {
        if ($error_handle) {
                $GLOBALS['_page']['name_index'] = 55;
                $GLOBALS['_page']['css_name'] = '';
                $GLOBALS['_ni'] = $GLOBALS['_page']['name_index'];
                $GLOBALS['_page_cont'][$GLOBALS['_ni']]['page_main_code'] = MsgBox(_t('_Sorry, your IP been banned'));
                PageCode();
        }
        return false;
    }

    // if profile is banned
    if (isLoggedBanned($aProfile['ID'])) {
        if ($error_handle) {
            $GLOBALS['_page']['name_index'] = 55;
            $GLOBALS['_page']['css_name'] = '';
            $GLOBALS['_ni'] = $GLOBALS['_page']['name_index'];
            $GLOBALS['_page_cont'][$GLOBALS['_ni']]['page_main_code'] = MsgBox(_t('_member_banned'));
            PageCode();
        }
        return false;
    }

    if( 'on' != getParam('ext_nav_menu_enabled') ) {
        update_date_lastnav($ID);
    }

    return true;
}

function check_logged()
{
    $aAccTypes = array(
       1 => 'admin',
       0 => 'member'
    );

    $bLogged = false;
    foreach($aAccTypes as $iKey => $sValue)
        if($GLOBALS['logged'][$sValue] = member_auth($iKey, false)) {
            $bLogged = true;
            break;
        }

    if((isset($_COOKIE['memberID']) || isset($_COOKIE['memberPassword'])) && !$bLogged)
        bx_logout(false);

	if($bLogged)
		$GLOBALS['oSysTemplate']->addCssStyle('.bx-hide-when-logged-in', array(
			'display' => 'none'
		));
}

// 0 - member, 1 - admin
function member_auth($member = 0, $error_handle = true, $bAjx = false)
{
       global $site;

       switch ($member) {
        case 0:
               $mem	    = 'member';
               $login_page = BX_DOL_URL_ROOT . "member.php";
            $iRole      = BX_DOL_ROLE_MEMBER;
        break;
        case 1:
               $mem	    = 'admin';
               $login_page = BX_DOL_URL_ADMIN . "index.php";
            $iRole      = BX_DOL_ROLE_ADMIN;
        break;
    }

    if (empty($_COOKIE['memberID']) || !isset($_COOKIE['memberPassword'])) {
        if ($error_handle) {
            $text = _t("_LOGIN_REQUIRED_AE1");
            if ($member == 0)
               $text .= "<br />"._t("_LOGIN_REQUIRED_AE2", $site['images'], BX_DOL_URL_ROOT, $site['title']);

            $bAjxMode = (isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;
            if ($member=1 && $bAjx==true) $bAjxMode = true;
            login_form($text, $member, $bAjxMode);
        }
        return false;
    }

    return check_login(process_pass_data($_COOKIE['memberID']), process_pass_data($_COOKIE['memberPassword' ]), $iRole, $error_handle);
}

// check unencrypted password
function check_password($sUsername, $sPassword, $iRole = BX_DOL_ROLE_MEMBER, $error_handle = true)
{
    $iId = getID($sUsername);
    if (!$iId) return false;

    $aUser = getProfileInfo($iId);
    $sPassCheck = encryptUserPwd($sPassword, $aUser['Salt']);

    return check_login($iId, $sPassCheck, $iRole, $error_handle);
}

function update_date_lastnav($iId)
{
    $iId = (int) $iId;

    // update the date of last navigate;
    $sQuery = "UPDATE `Profiles` SET `DateLastNav` = NOW() WHERE `ID` = '{$iId}'";
    db_res($sQuery);
}

function profile_delete($ID, $isDeleteSpammer = false)
{
    //global $MySQL;
    global $dir;

    //recompile global profiles cache
    $GLOBALS['MySQL']->cleanCache('sys_browse_people');

    $ID = (int)$ID;

    if ( !$ID )
        return false;

    if ( !($aProfileInfo = getProfileInfo( $ID )) )
        return false;

    $iLoggedInId = getLoggedId();

    db_res( "DELETE FROM `sys_admin_ban_list` WHERE `ProfID`='". $ID . "' LIMIT 1");
    db_res( "DELETE FROM `sys_greetings` WHERE `ID` = '{$ID}' OR `Profile` = '{$ID}'" );
    db_res( "DELETE FROM `sys_block_list` WHERE `ID` = '{$ID}' OR `Profile` = '{$ID}'" );
    db_res( "DELETE FROM `sys_messages` WHERE Recipient = {$ID} OR `Sender` = {$ID}" );
    db_res( "DELETE FROM `sys_fave_list` WHERE ID = {$ID} OR Profile = {$ID}" );
    db_res( "DELETE FROM `sys_friend_list` WHERE ID = {$ID} OR Profile = {$ID}" );
    db_res( "DELETE FROM `sys_acl_levels_members` WHERE `IDMember` = {$ID}" );
    db_res( "DELETE FROM `sys_tags` WHERE `ObjID` = {$ID} AND `Type` = 'profile'" );
    db_res( "DELETE FROM `sys_sbs_entries` WHERE `subscriber_id` = {$ID} AND `subscriber_type` = '1'" );

    // delete profile votings
    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolVoting.php' );
    $oVotingProfile = new BxDolVoting ('profile', 0, 0);
    $oVotingProfile->deleteVotings ($ID);

    // delete profile comments
    require_once (BX_DIRECTORY_PATH_CLASSES . 'BxDolCmts.php');
    $oCmts = new BxDolCmts('profile', $ID);
    $oCmts->onObjectDelete();
    // delete all comments in all comments' systems, this user posted
    $oCmts->onAuthorDelete($ID);

    $iPossibleCoupleID = (int)db_value( "SELECT `ID` FROM `Profiles` WHERE `Couple` = '{$ID}'" );
    if ($iPossibleCoupleID) {
        db_res( "DELETE FROM `Profiles` WHERE `ID` = '{$iPossibleCoupleID}'" );
        //delete cache file
        deleteUserDataFile( $iPossibleCoupleID );
    }

    // delete associated locations
    if (BxDolModule::getInstance('BxWmapModule'))
        BxDolService::call('wmap', 'response_entry_delete', array('profiles', $ID));

	//delete all subscriptions
	$oSubscription = BxDolSubscription::getInstance();
	$oSubscription->unsubscribe(array('type' => 'object_id', 'unit' => 'profile', 'object_id' => $ID));

    db_res( "DELETE FROM `Profiles` WHERE `ID` = '{$ID}'" );

    if ($isDeleteSpammer) {
        bx_import('BxDolStopForumSpam');
        $oBxDolStopForumSpam = new BxDolStopForumSpam();
        $oBxDolStopForumSpam->submitSpammer(array('username' => $aProfileInfo['NickName'], 'email' => $aProfileInfo['Email'], 'ip' => bx_member_ip_get_last($ID)));
    }

    // delete moxiemanager files
    $sMoxieFilesPath  = BX_DIRECTORY_PATH_ROOT . 'media/moxie/files/' . substr($aProfileInfo['NickName'], 0, 1) . '/' . substr($aProfileInfo['NickName'], 0, 2) . '/' . substr($aProfileInfo['NickName'], 0, 3) . '/' . $aProfileInfo['NickName'];
    bx_rrmdir($sMoxieFilesPath);

    // create system event
    $oZ = new BxDolAlerts('profile', 'delete',  $ID, 0, array('profile_info' => $aProfileInfo, 'logged_in' => $iLoggedInId, 'delete_spammer' => $isDeleteSpammer));
    $oZ->alert();

    //delete cache file
    deleteUserDataFile( $ID );
}

function get_user_online_status ($ID)
{
    $iOnline = 0;

    if($ID && is_numeric($ID) ) {
        $aMemberInfo  = getProfileInfo($ID);
        // check user status;
        if($aMemberInfo['UserStatus'] != 'offline') {
            $min     = (int)getParam( "member_online_time" );
            $iOnline = $GLOBALS['MySQL']->fromMemory ("member_online_status.$ID.$min", 'getOne', "SELECT count(ID) as count_id FROM Profiles WHERE DateLastNav > SUBDATE(NOW(), INTERVAL {$min} MINUTE) AND ID={$ID}");
        }
    }

    return  $iOnline;
}

/**
 * Add / delete profile to / from ban list table
 * 
 * @param int $iProfileId - id of member
 * @param boolean $bBan - add / delete member
 * @param integer $iDuration - ban duration (in days)
 * @return int / boolean - number of rows affected / false
 */
function bx_admin_profile_ban_control($iProfileId, $bBan = true, $iDuration = 0)
{
    $iProfileId = (int)$iProfileId;
	$iDuration = 86400 * (!empty($iDuration) ? $iDuration : (int)getParam('ban_duration'));

    if($bBan)
    	$sqlQuery = "REPLACE INTO `sys_admin_ban_list` SET `ProfID`='" . $iProfileId . "', `Time`='" . $iDuration . "',  `DateTime`=NOW()";
    else
        $sqlQuery = "DELETE FROM `sys_admin_ban_list` WHERE `ProfID`='" . $iProfileId . "'";

    return $GLOBALS['MySQL']->query($sqlQuery);
}

/**
 * Perform change of status with clearing profile(s) cache and sending mail about activation
 * 
 * @param mixed  $mixedIds      - array of IDs or single int ID of profile(s)
 * @param string $sStatus       - given status
 * @param boolean $bSendActMail - send email about activation or not (works with 'Active' status only
 * @return boolean              - TRUE on success / FALSE on failure
 */
function bx_admin_profile_change_status($mixedIds, $sStatus, $bSendActMail = FALSE)
{
    if (!$mixedIds || (is_array($mixedIds) && empty($mixedIds)))
        return FALSE;
    if (!is_array($mixedIds))
        $mixedIds = array((int)$mixedIds);
    
    $sStatus = strip_tags($sStatus);
    
    $oEmailTemplate = new BxDolEmailTemplates();

    foreach ($mixedIds as $iId) {
        $iId = (int)$iId;
        $aProfile = getProfileInfo($iId);

        $aIds = array($iId);
        if((int)$aProfile['Couple'] > 0)
            $aIds[] = $aProfile['Couple'];

        if (!$GLOBALS['MySQL']->query("UPDATE `Profiles` SET `Status` = '$sStatus' WHERE `ID` IN (" . $GLOBALS['MySQL']->implode_escape($aIds) . ")"))
            break;

        createUserDataFile($iId);
        reparseObjTags('profile', $iId);

        if ($sStatus == 'Active' && $bSendActMail) {
            if (BxDolModule::getInstance('BxWmapModule'))
                BxDolService::call('wmap', 'response_entry_add', array('profiles', $iId));

            $aProfile = getProfileInfo($iId);
            $aMail = $oEmailTemplate->parseTemplate('t_Activation', array(), $iId);
            sendMail($aProfile['Email'], $aMail['subject'], $aMail['body'], $iId, array(), 'html', FALSE, TRUE);
        }

        $oAlert = new BxDolAlerts('profile', 'change_status', $iId, 0, array('status' => $sStatus));
        $oAlert->alert();
    }

    return TRUE;
}

/**
 * Perform change of featured status with clearing profile(s) cache
 * @param  int       $iProfileId - profile id
 * @param  boolean   $bFeature   - mark as featured / unfeatured
 * @return boolean               - TRUE on success / FALSE on failure
 */
function bx_admin_profile_featured_control($iProfileId, $bFeature = TRUE)
{
    $iProfileId = (int)$iProfileId;
    $iFeatured  = $bFeature ? 1 : 0;
    if ($GLOBALS['MySQL']->query("UPDATE `Profiles` SET `Featured` = $iFeatured WHERE `ID` = $iProfileId"))
    {
        createUserDataFile($iProfileId);
        return TRUE;
    }
    return FALSE;
}
