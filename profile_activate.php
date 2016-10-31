<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolEmailTemplates.php' );

// --------------- page variables

$_page['name_index'] = 40;

$ID = bx_get('ConfID');
$ConfCode = bx_get('ConfCode');

if (!$ID && !$ConfCode)
    exit;

$logged['member']	= member_auth(0, false);

$_page['header'] = _t("_Email confirmation");
$_page['header_text'] = _t("_Email confirmation Ex");

// --------------- page components

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompPageMainCode($ID, $ConfCode);

// --------------- [END] page components

PageCode();

// --------------- page components functions

/**
 * page code function
 */
function PageCompPageMainCode($iID, $sConfCode)
{
    global $site;

    $ID = (int)$iID;
    $ConfCode = clear_xss($sConfCode);
    $p_arr = getProfileInfo($ID);

    if (!$p_arr) {
        $_page['header'] = _t("_Error");
        $_page['header_text'] = _t("_Profile Not found");
        return MsgBox(_t('_Profile Not found Ex'));
    }

    $aCode = array(
        'message_status' => '',
        'message_info' => '',
        'bx_if:form' => array(
            'condition' => false,
            'content' => array(
                'form' => ''
            )
        ),
        'bx_if:next' => array(
            'condtion' => false,
            'content' => array(
                'next_url' => '',
            )
        )
    );

    if ($p_arr['Status'] == 'Unconfirmed') {
        $ConfCodeReal = base64_encode( base64_encode( crypt( $p_arr['Email'], CRYPT_EXT_DES ? "secret_co" : "se" ) ) );
        if (strcmp($ConfCode, $ConfCodeReal) !== 0) {
            $aForm = array(
                'form_attrs' => array (
                    'action' =>  BX_DOL_URL_ROOT . 'profile_activate.php',
                    'method' => 'post',
                    'name' => 'form_change_status'
                ),

                'inputs' => array(
                    'conf_id' => array (
                        'type'     => 'hidden',
                        'name'     => 'ConfID',
                        'value'    => $ID,
                    ),
                    'conf_code' => array (
                        'type'     => 'text',
                        'name'     => 'ConfCode',
                        'value'    => '',
                        'caption'  => _t("_Confirmation code")
                    ),
                    'submit' => array (
                        'type'     => 'submit',
                        'name'     => 'submit',
                        'value'    => _t("_Submit"),
                    ),
                ),
            );
            $oForm = new BxTemplFormView($aForm);
            $aCode['message_status'] = _t("_Profile activation failed");
            $aCode['message_info'] = _t("_EMAIL_CONF_FAILED_EX");
            $aCode['bx_if:form']['condition'] = true;
            $aCode['bx_if:form']['content']['form'] = $oForm->getCode();
        } else {
            $aCode['bx_if:next']['condition'] = true;
            $aCode['bx_if:next']['content']['next_url'] = BX_DOL_URL_ROOT . 'member.php';

            $send_act_mail = FALSE;
            if (getParam('autoApproval_ifJoin') == 'on' && !(getParam('sys_dnsbl_enable') && 'approval' == getParam('sys_dnsbl_behaviour') && bx_is_ip_dns_blacklisted('', 'join'))) {
                $status = 'Active';
                $send_act_mail = TRUE;
                $aCode['message_info'] = _t( "_PROFILE_CONFIRM" );
            } else {
                $status = 'Approval';
                $aCode['message_info'] = _t("_EMAIL_CONF_SUCCEEDED", $site['title']);
            }

            $update = bx_admin_profile_change_status($ID, $status, $send_act_mail);

            // Promotional membership
            if (getParam('enable_promotion_membership') == 'on') {
                $memership_days = getParam('promotion_membership_days');
                setMembership( $p_arr['ID'], MEMBERSHIP_ID_PROMOTION, $memership_days, true );
            }

            // check couple profile;
            if ($p_arr['Couple']) {
                $update = bx_admin_profile_change_status($p_arr['Couple'], $status);

                //Promotional membership
                if (getParam('enable_promotion_membership') == 'on') {
                    $memership_days = getParam('promotion_membership_days');
                    setMembership( $p_arr['Couple'], MEMBERSHIP_ID_PROMOTION, $memership_days, true );
                }
            }
            if (getParam('newusernotify')) {
                $oEmailTemplates = new BxDolEmailTemplates();
                $aTemplate = $oEmailTemplates->getTemplate('t_UserConfirmed', $p_arr['ID']);

                sendMail($site['email_notify'], $aTemplate['Subject'], $aTemplate['Body'], $p_arr['ID']);
            }
        }
    } else
        $aCode['message_info'] = _t('_ALREADY_ACTIVATED');
    return $GLOBALS['oSysTemplate']->parseHtmlByName('profile_activate.html', $aCode);
}
