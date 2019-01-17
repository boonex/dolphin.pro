<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolEmailTemplates.php');

bx_import('BxTemplFormView');

// --------------- page variables and login

$_page['name_index'] = 29;
$_page['css_name']   = array('forms_adv.css');

$_page['header']      = _t("_Tell a friend");
$_page['header_text'] = _t("_Tell a friend");

$profileID = 0;
if (isset($_GET['ID'])) {
    $profileID = (int)$_GET['ID'];
} else {
    if (isset($_POST['ID'])) {
        $profileID = (int)$_POST['ID'];
    }
}

$iSenderID   = getLoggedId();
$aSenderInfo = getProfileInfo($iSenderID);

// --------------- page components
$bAjaxMode =  isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

$sCaption = ($profileID) ? _t('_TELLAFRIEND2', $site['title']) : _t('_TELLAFRIEND', $site['title']);

$aForm = array(
    'form_attrs' => array(
        'id'       => 'invite_friend',
        'name'     => 'invite_friend',
        'action'   => BX_DOL_URL_ROOT . 'tellfriend.php',
        'method'   => 'post',
        'onsubmit' => "return bx_ajax_form_check(this)",
    ),
    'params'     => array(
        'db' => array(
            'submit_name' => 'do_submit',
            // we need alternative hidden field name here, instead of submit, becuase AJAX submit doesn't pass submit button value
        ),
    ),
    'inputs'     => array(
        'header1'         => array(
            'type'    => 'block_header',
            'caption' => $sCaption,
        ),
        'do_submit'       => array(
            'type'  => 'hidden',
            'name'  => 'do_submit', // hidden submit field for AJAX submit
            'value' => 1,
        ),
        'id'              => array(
            'type'  => 'hidden',
            'name'  => 'ID',
            'value' => $profileID,
        ),
        'sender_name'     => array(
            'type'    => 'text',
            'name'    => 'sender_name',
            'caption' => _t("_Your name"),
            'value'   => getNickName($aSenderInfo['ID']),
        ),
        'sender_email'    => array(
            'type'    => 'text',
            'name'    => 'sender_email',
            'caption' => _t('_Your email'),
            'required' => true,
            'value'   => $aSenderInfo['Email'],
            'checker' => array(
                'func'  => 'email',
                'error' => _t('_Incorrect Email'),
            ),
        ),
        'recipient_email' => array(
            'type'    => 'text',
            'name'    => 'recipient_email',
            'caption' => _t('_Friend email'),
            'required' => true,
            'value'   => '',
            'checker' => array(
                'func'  => 'email',
                'error' => _t('_sys_adm_form_err_required_field'),
            ),
        ),
        'captcha' => array(
                'type' => 'captcha',
                'caption' => _t('_Enter what you see'),
                'name' => 'securityImageValue',
                'required' => true,
                'dynamic' => $bAjaxMode,
                'checker' => array(
                    'func' => 'captcha',
                    'error' => _t( '_Incorrect Captcha' ),
                ),
            ),
        'submit_send'     => array(
            'type'  => 'submit',
            'name'  => 'submit_send',
            'value' => _t("_Send Letter"),
        ),
    )
);

if(isLogged())
    unset($aForm['inputs']['captcha']);

// generate form or form result content
$oForm = new BxTemplFormView($aForm);
$oForm->initChecker();
if ($oForm->isSubmittedAndValid())
    $sPageCode = MsgBox(_t(SendTellFriend($iSenderID) ? '_Email was successfully sent' : '_Email sent failed'));
else
    $sPageCode = $oForm->getCode();

// output AJAX form submission result
if (bx_get('BxAjaxSubmit')) {
    header('Content-type:text/html;charset=utf-8');
    echo $sPageCode;
    exit;
}

$sPageCode = $GLOBALS['oSysTemplate']->parseHtmlByName('default_margin.html', array('content' => $sPageCode));

// output ajax popup
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $aVarsPopup = array(
        'title'   => $_page['header'],
        'content' => $sPageCode,
    );
    header('Content-type:text/html;charset=utf-8');
    echo $GLOBALS['oFunctions']->transBox($GLOBALS['oSysTemplate']->parseHtmlByName('popup.html', $aVarsPopup), true);
    exit;
}

// output regular page
$_ni                           = $_page['name_index'];
$_page_cont[$_ni]['page_code'] = $sPageCode;
PageCode();

// --------------- page components functions

/**
 * send "tell a friend" email
 */

function SendTellFriend($iSenderID = 0)
{
    global $profileID;

    $sSenderEmail = clear_xss(bx_get('sender_email'));
    if(strlen(trim($sSenderEmail)) <= 0)
        return 0;

	$sSenderName = strmaxtextlen(bx_get('sender_name'), 16);
    $sSenderLink = $iSenderID != 0 ? getProfileLink($iSenderID) : BX_DOL_URL_ROOT;

    $sRecipientEmail = clear_xss(bx_get('recipient_email'));
    if(strlen(trim($sRecipientEmail)) <= 0)
        return 0;

    $sLinkAdd = $iSenderID > 0 ? 'idFriend=' . $iSenderID : '';
    $rEmailTemplate = new BxDolEmailTemplates();
    if ($profileID) {
        $aTemplate = $rEmailTemplate->getTemplate('t_TellFriendProfile', getLoggedId());
        $Link      = getProfileLink($profileID, $sLinkAdd);
    }
	else {
        $aTemplate = $rEmailTemplate->getTemplate('t_TellFriend', getLoggedId());
        $Link      = BX_DOL_URL_ROOT;
        if (strlen($sLinkAdd) > 0)
            $Link .= '?' . $sLinkAdd;
    }

    return sendMail($sRecipientEmail, $aTemplate['Subject'], $aTemplate['Body'], '', array(
        'Link' => $Link,
        'SenderName' => $sSenderName,
    	'SenderLink' => $sSenderLink
    ));
}
