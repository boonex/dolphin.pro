<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolEmailTemplates.php');

// --------------- page variables

$_page['name_index'] = bx_get('fullpage') == 1 ? 7 : 44;
$logged['member']    = member_auth(0, false);

// --------------- page components

//define ajax mode
$bAjxMod = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
&& $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;

if ($bAjxMod) {
    header('Content-Type: text/html; charset=utf-8');
    echo MsgBox(getMainCode());
    exit;
}

$_ni                                = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = DesignBoxContent(_t('_Send virtual kiss'), getMainCode(),
    $oTemplConfig->PageVkiss_db_num);
$_page_cont[$_ni]['body_onload']    = '';
// --------------- [END] page components

PageCode();

// --------------- page components functions

/**
 * page code function
 */
function getMainCode()
{
    global $_page;

    $member['ID']       = getLoggedId();
    $member['Password'] = getLoggedPassword();

    if (false != bx_get('ConfCode') && false != bx_get('sendto')) {
        $recipientID = (int)bx_get('sendto');
    } else {
        //check post value
        $recipientID = isset($_POST['sendto']) ? $_POST['sendto'] : -1;
    }

    $recipient              = getProfileInfo($recipientID);
    $isCheckVisitorGreeting = true;

    if (!$recipient || $recipientID == $member['ID']) {
        return MsgBox(_t('_Error Occured'));
    }

    ob_start();
    ?>
    <table width="100%" cellpadding="4" cellspacing="4" border="0">
        <tr>
            <td align="center" class="text2">__content__<br /></td>
        </tr>
    </table>
    <?php
    $sResTmpl = ob_get_clean();

    $ret       = '';
    $sKissKey  = '_Send virtual kiss';
    $sJQueryJS = genAjaxyPopupJS($recipientID);

    $_page['header'] = _t($sKissKey);

    if ($_GET['ConfCode'] && $_GET['from'] &&
        (strcmp($_GET['ConfCode'], base64_encode(base64_encode(crypt($_GET['from'], CRYPT_EXT_DES ? "vkiss_sec" : "vk")))) === 0)
    ) {
        $member['ID']           = (int)$_GET['from'];
        $isCheckVisitorGreeting = false;
    }

    //
    // Check if member can send messages
    $check_res = checkAction($member['ID'], ACTION_ID_SEND_VKISS);
    if ($check_res[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED) {
        $_page['header_text'] = _t($sKissKey . '3');
        $ret                  = $GLOBALS['oSysTemplate']->parseHtmlByContent($sResTmpl,
            array('content' => $check_res[CHECK_ACTION_MESSAGE]));

        return $ret . $sJQueryJS;
    }

    $action_result = "";
    // Perform sending
    $send_result = MemberSendVKiss($member, $recipient, $isCheckVisitorGreeting);
    switch ($send_result) {
        case 1:
            $action_result .= _t_err("_VKISS_BAD");
            break;
        case 7:
            $action_result .= _t_err("_VKISS_BAD_COUSE_B");
            break;
        case 10:
            $action_result .= _t_err("_VKISS_BAD_COUSE_C");
            break;
        case 13:
            $action_result .= _t_err("_VKISS_BAD_COUSE_A3");
            break;
        case 23:
            $action_result .= _t_err("_VKISS_BAD_COUSE_X");
            break;
        case 24:
            $action_result .= _t_err("_VKISS_BAD_COUSE_Y");
            break;
        default:
            $action_result .= _t("_VKISS_OK");
            break;
    }
    if ($send_result == 0) {
        $_page['header_text'] = _t($sKissKey . '2');
    } else {
        $_page['header_text'] = _t($sKissKey . '3');
    }

    $ret = $GLOBALS['oSysTemplate']->parseHtmlByContent($sResTmpl, array('content' => $action_result));

    return $ret . $sJQueryJS;
}

/**
 * Send virtual kiss
 */
function MemberSendVKiss($member, $recipient, $isCheckVisitorGreeting = true)
{
    global $logged;

    // Check if recipient is active
    if ('Active' != $recipient['Status']) {
        return 7;
    }

    // block members
    if ($recipient['ID'] && $member['ID'] && isBlocked((int)$recipient['ID'], (int)$member['ID'])) {
        return 24;
    }

    // Get sender info
    $sender = getProfileInfo($member['ID']);

    // Send email notification
    $rEmailTemplate = new BxDolEmailTemplates();
    if ($logged['member'] || !$isCheckVisitorGreeting) {
        $aTemplate = $rEmailTemplate->getTemplate('t_VKiss', $_COOKIE['memberID']);
    } else {
        $aTemplate = $rEmailTemplate->getTemplate('t_VKiss_visitor');
    }

    $sConfCode = urlencode(base64_encode(base64_encode(crypt($recipient['ID'], CRYPT_EXT_DES ? "vkiss_sec" : "vk"))));

    // parse the email template ;
    $sProfileLink = $sender
        ? '<a href="' . getProfileLink($member['ID']) . '">' . getNickName($sender['ID']) . '</a>'
        : '<b>' . _t("_Visitor") . '</b>';

    $sKissLink = $sender
        ? '<a href="' . BX_DOL_URL_ROOT . 'greet.php?fullpage=1&sendto=' . $member['ID'] . '&from=' . $recipient['ID'] . '&ConfCode=' . $sConfCode . '">' . BX_DOL_URL_ROOT . 'greet.php?sendto=' . $member['ID'] . '&from=' . $recipient['ID'] . '&ConfCode=' . $sConfCode . '</a>'
        : '<a href="' . BX_DOL_URL_ROOT . 'communicator.php">' . BX_DOL_URL_ROOT . 'communicator.php</a>';

    $aRepl = array(
        '<ConfCode>'         => $sConfCode,
        '<ProfileReference>' => $sProfileLink,
        '<VKissLink>'        => $sKissLink,
        '<RealName>'         => getNickName($recipient['ID']),
        '<SiteName>'         => BX_DOL_URL_ROOT,
    );

    $aTemplate['Body'] = str_replace(array_keys($aRepl), array_values($aRepl), $aTemplate['Body']);
    $mail_ret          = sendMail($recipient['Email'], $aTemplate['Subject'], $aTemplate['Body'], $recipient['ID']);

    // Send message into the member's site personal mailbox;

    $aTemplate['Subject'] = process_db_input($aTemplate['Subject'], BX_TAGS_NO_ACTION);
    $aTemplate['Body']    = process_db_input($aTemplate['Body'], BX_TAGS_NO_ACTION);

    $sender['ID'] = (!$sender['ID']) ? 0 : $sender['ID'];

    $sQuery =
        "
        INSERT INTO
            `sys_messages`
        SET
            `Date` = NOW(),
            `Sender` = '{$sender['ID']}',
            `Recipient` = '{$recipient['ID']}',
            `Subject` = '{$aTemplate['Subject']}',
            `Text`  = '{$aTemplate['Body']}',
            `New` = '1',
            `Type` = 'greeting'
    ";
    db_res($sQuery);

    if (!$mail_ret) {
        return 10;
    }

    // Insert kiss into database
    $kiss_arr = db_arr("SELECT `ID` FROM `sys_greetings` WHERE `ID` = {$member['ID']} AND `Profile` = {$recipient['ID']} LIMIT 1",
        0);
    if (!$kiss_arr) {
        $result = db_res("INSERT INTO `sys_greetings` ( `ID`, `Profile`, `Number`, `When`, `New` ) VALUES ( {$member['ID']}, {$recipient['ID']}, 1, NOW(), '1' )",
            0);
    } else {
        $result = db_res("UPDATE `sys_greetings` SET `Number` = `Number` + 1, `New` = '1' WHERE `ID` = {$member['ID']} AND `Profile` = {$recipient['ID']}",
            0);
    }

    if(!$result)
		return 1;

	// If success then perform actions
	checkAction( $member['ID'], ACTION_ID_SEND_VKISS, true );

	$oAlert = new BxDolAlerts('greeting', 'add', 0, $member['ID'], array('Recipient' => $recipient['ID']));
	$oAlert->alert();

    return 0;
}
