<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_SECURITY_EXCEPTIONS', true);
$aBxSecurityExceptions = array(
    'POST.body',
    'REQUEST.body',
);

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'prof.inc.php' );

bx_import('BxTemplFormView');
bx_import('BxDolEmailTemplates');
bx_import('BxDolPaginate');
bx_import('BxDolSubscription');
bx_import('BxTemplSearchResult');

$logged['admin'] = member_auth(1, true, true);

$oSubscription = BxDolSubscription::getInstance();

if(getPostFieldIfSet('queue_message') && getPostFieldIfSet('msgs_id')) {
    set_time_limit(1800);
    $sActionResult = QueueMessage();
}
if (getPostFieldIfSet('add_message'))
    $action = 'add';
if(getPostFieldIfSet('delete_message') && getPostFieldIfSet('msgs_id'))
    $sActionResult = DeleteMessage() ? _t('_adm_mmail_Message_was_deleted') : _t('_adm_mmail_Message_was_not_deleted');
if(getPostFieldIfSet('preview_message'))
    $action = 'preview';
if(bx_get('action') == 'empty' )
    $sActionResult = EmptyQueue() ? _t('_adm_mmail_Queue_empty') : _t('_adm_mmail_Queue_emptying_failed');

if (isset($_POST['adm-ms-delete'])) {
    foreach($_POST['members'] as $iMemberId)
        $oSubscription->unsubscribe(array(
            'type' => 'visitor',
            'id' => $iMemberId
        ));
}

$aPages = array (
    'massmailer' => array (
        'index' => 13,
        'title' => _t('_adm_mmail_title'),
        'url' => BX_DOL_URL_ADMIN . 'notifies.php?mode=massmailer',
        'func' => 'PageCodeMassmailer',
        'func_params' => array(),
    ),
    'manage_subscribers' => array (
        'index' => 9,
        'title' => _t('_adm_page_cpt_manage_subscribers'),
        'url' => BX_DOL_URL_ADMIN . 'notifies.php?mode=manage_subscribers',
        'func' => 'PageCodeManageSubscribers',
        'func_params' => array($oSubscription),
    ),
    'settings' => array (
        'index' => 9,
        'title' => _t('_Settings'),
        'url' => BX_DOL_URL_ADMIN . 'notifies.php?mode=settings',
        'func' => 'PageCodeSettings',
        'func_params' => array(),
    ),
);

if (!isset($_GET['mode']) || !isset($aPages[$_GET['mode']]))
    $sMode = 'massmailer';
else
    $sMode = $_GET['mode'];

$aTopItems = array();
foreach ($aPages as $k => $r)
    $aTopItems['dbmenu_' . $k] = array(
        'href' => $r['url'],
        'title' => $r['title'],
        'active' => $k == $sMode ? 1 : 0
    );

$iNameIndex = $aPages[$sMode]['index'];
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('forms_adv.css', 'manage_subscribers.css'),
    'header' => _t('_adm_mmail_title')
);

$_page_cont[$iNameIndex] = call_user_func_array($aPages[$sMode]['func'], $aPages[$sMode]['func_params']);

PageCodeAdmin();

function PageCodeMassmailer ()
{
    global $sActionResult, $action;
    return array(
        'page_code_status' => PrintStatus($sActionResult),
        'page_code_new_message' => getEmailMessage($action),
        'page_code_preview_message' => $action == 'preview' && $_POST['body'] ? PreviewMessage() : '',
        'page_code_all_messages' => getAllMessagesBox(),
        'page_code_queue_message' => getQueueMessage()
    );
}

function PageCodeManageSubscribers($oSubscription)
{
    $iStart = bx_get('start') !== false ? (int)bx_get('start') : 0;
    $iPerPage = 20;
    $oPaginate = new BxDolPaginate(array(
        'start' => $iStart,
        'per_page' => $iPerPage,
        'count' => $oSubscription->getSubscribersCount(),
        'page_url' => BX_DOL_URL_ADMIN . 'notifies.php?mode=manage_subscribers&start={start}',

    ));

    $sControls = BxTemplSearchResult::showAdminActionsPanel('adm-ms-form', array(
        'adm-ms-delete' => _t('_adm_btn_ms_delete')
    ), 'members');

    $aSubscribers = $oSubscription->getSubscribers(BX_DOL_SBS_TYPE_VISITOR, $iStart, $iPerPage);

    $s = $GLOBALS['oAdmTemplate']->parseHtmlByName('manage_subscribers.html', array(
        'bx_repeat:items' => is_array($aSubscribers) && !empty($aSubscribers) ? $aSubscribers : MsgBox(_t('_Empty')),
        'paginate' => $oPaginate->getPaginate(),
        'controls' => $sControls
    ));

    return array('page_main_code' => DesignBoxAdmin(_t('_adm_page_cpt_manage_subscribers'), $s, $GLOBALS['aTopItems'], '', 1));
}

function PageCodeSettings()
{
    global $aPages;

    bx_import('BxDolAdminSettings');
    $oSettings = new BxDolAdminSettings(4);

    $sResults = false;
    if (isset($_POST['save']) && isset($_POST['cat']))
        $sResult = $oSettings->saveChanges($_POST);

    $s = $oSettings->getForm();
    if ($sResult)
        $s = $sResult . $s;

    return array('page_main_code' => DesignBoxAdmin(_t('_Settings'), $s, $GLOBALS['aTopItems'], '', 11));
}

function PrintStatus($sActionResult)
{
    $sSubjC = _t('_Subject');
    $sEmailsC = _t('_adm_mmail_emails');
    $sEmptyQueueC = _t('_adm_mmail_Empty_Queue');
    $sCupidStatusC = _t('_adm_mmail_Cupid_mails_status');

    $sSingleEmailsTRs = '';

    // Select count of emails in queue per one message
    $iCount = (int)$GLOBALS['MySQL']->getOne("SELECT COUNT(`id`) AS `count` FROM `sys_sbs_queue`");
    if ($iCount <= 0)
        $sSingleEmailsTRs .= "<tr><td align=center><b class='sys-adm-failed'>" . _t('_adm_mmail_no_emails_in_queue') . "</b></td></tr>";
    else
       $sSingleEmailsTRs .= "<tr><td align='center'>" . _t('_adm_mmail_mails_in_queue', $iCount) . "</td></tr>";

    $sEmptyQueueTable = '';
    // If queue is not empty then show link to clear it
    if($iCount > 0) {
        $sEmptyQueueTable = "
        <div class=\"bx-def-hr\"></div>
        <table class=\"text\" width=\"50%\" style=\"height: 30px;\">
            <tr class=\"table\">
                <td align=\"center\" colspan=\"3\">
                    <a href=\"" . BX_DOL_URL_ADMIN . "notifies.php?action=empty\">{$sEmptyQueueC}</a>
                </td>
            </tr>
        </table>
        <div class=\"bx-def-hr\"></div>";
    }

    ob_start();
?>
<center>
    <table cellspacing=2 cellpadding=2 class=text border=0>
        <tr class=header align="center"><td><?=_t('_adm_mmail_Queue_status');?>:</td></tr>
        <?=$sSingleEmailsTRs;?>
    </table>
    <?=$sEmptyQueueTable;?>
</center>
<?php
    $sResult = ob_get_clean();

    if(!empty($sActionResult))
       $sResult = MsgBox($sActionResult, 3) . $sResult;

    return DesignBoxAdmin(_t('_adm_mmail_title'), $sResult, $GLOBALS['aTopItems'], '', 11);
}

function getAllMessagesBox()
{
    $aMessages = $GLOBALS['MySQL']->getAll("SELECT `id`, `subject`, (`id`= ? OR `subject`= ? ) AS `selected` FROM `sys_sbs_messages`", [
        getPostFieldIfSet('msgs_id'),
        getPostFieldIfSet('Subj')
    ]);

    $sAllMessagesOptions = '';
    foreach($aMessages as $aMessage)
        $sAllMessagesOptions .= "<option value=\"" . $aMessage['id'] . "\" " . ($aMessage['selected'] ? "selected=\"selected\"" : "") . ">" . $aMessage['subject'] . "</option>";

    ob_start();
?>
<form name="form_messages" method="POST" action="<?=$GLOBALS['site']['url_admin'] . 'notifies.php';?>">
    <input type="hidden" name="action" value="view">
    <center class="text"><?= _t('_Messages'); ?>:&nbsp;
        <select name=msgs_id onChange="javascript: document.forms['form_messages'].submit();">
            <option value=0><?=_t('_None');?></option>
            <?=$sAllMessagesOptions;?>
        </select>
    </center>
</form>
<?php
    $sResult = ob_get_clean();

    return DesignBoxContent(_t('_adm_mmail_All_Messages'), $sResult, 11);
}

function getEmailMessage($sAction)
{
    $sErrorC = _t('_Error Occured');
    $sApplyChangesC = _t('_Save');
    $sSubjectC = _t('_Subject');
    $sBodyC = _t('_adm_mmail_Body');
    $sTextBodyC = _t('_adm_mmail_Text_email_body');
    $sPreviewMessageC = _t('_Preview');
    $sDeleteC = _t('_Delete');

    $sMessageID = (int)getPostFieldIfSet('msgs_id');

    $sSubject = $sBody = "";
    if(isset($_POST['body']) && getPostFieldIfSet('action') != 'delete' ) {
        $sSubject = process_pass_data( $_POST['subject'] );
        $sBody = process_pass_data( $_POST['body'] );
    } elseif ( $sMessageID )
        list($sSubject, $sBody) = $GLOBALS['MySQL']->getRow("SELECT `subject`, `body` FROM `sys_sbs_messages` WHERE `id`= ? LIMIT 1", [$sMessageID], PDO::FETCH_NUM);

    $sSubject = htmlspecialchars($sSubject);

    $aForm = array(
        'form_attrs' => array(
            'name' => 'sys_sbs_messages',
            'action' => $GLOBALS['site']['url_admin'] . 'notifies.php',
            'method' => 'post',
        ),
        'params' => array (
            'db' => array(
                'table' => 'sys_sbs_messages',
                'key' => 'ID',
                'submit_name' => 'add_message',
            ),
        ),
        'inputs' => array(
            'subject' => array(
                'type' => 'text',
                'name' => 'subject',
                'value' => $sSubject,
                'caption' => $sSubjectC,
                'required' => true,
                'checker' => array (
                    'func' => 'length',
                    'params' => array(2,128),
                    'error' => $sErrorC,
                ),
                'db' => array (
                    'pass' => 'Xss',
                ),
            ),
            'body' => array(
                'type' => 'textarea',
                'name' => 'body',
                'value' => $sBody,
                'caption' => $sBodyC,
                'required' => true,
                'html' => 2,
                'html_no_link_conversion' => true,
                'attrs' => array('style' => "height:400px;"),
                'checker' => array (
                    'func' => 'length',
                    'params' => array(10,32000),
                    'error' => $sErrorC,
                ),
                'db' => array (
                    'pass' => 'XssHtml',
                ),
            ),
            'msgs_id' => array(
                'type' => 'hidden',
                'name' => 'msgs_id',
                'value' => $sMessageID,
            ),
            'control' => array (
                'type' => 'input_set',
                array(
                    'type' => 'submit',
                    'name' => 'add_message',
                    'caption' => $sApplyChangesC,
                    'value' => $sApplyChangesC,
                ),
                array(
                    'type' => 'submit',
                    'name' => 'preview_message',
                    'caption' => $sPreviewMessageC,
                    'value' => $sPreviewMessageC,
                ),
            )
        ),
    );
    if($sMessageID) {
        $aForm['inputs']['control'][] = array (
            'type' => 'submit',
            'name' => 'delete_message',
            'caption' => $sDeleteC,
            'value' => $sDeleteC,
        );
    }

    $sResult = '';
    $oForm = new BxTemplFormView($aForm);
    $oForm->initChecker();
    if ($oForm->isSubmittedAndValid()) {
        if ($sAction == 'add') {
            if ($sMessageID > 0) {
                $oForm->update($sMessageID);
            } else {
                $sMessageID = $oForm->insert();
            }
        }

        $sResult = $sMessageID > 0 ? MsgBox(_t('_Success'), 3) : MsgBox($sErrorC);
    }

    return DesignBoxContent(_t('_adm_mmail_Email_message'), $sResult . $oForm->getCode(), 11);
}

function getQueueMessage()
{
    global $aPreValues;

    if ( isset($_POST['msgs_id']) ) {
        $aSexValues = $aAgeValues = array(
			'all' => _t('_All'),
			'selectively' => _t('_Selectively'),
		);

		$aSexesValues = getFieldValues('Sex');
        foreach($aSexesValues as $sKey => $sValue)
            $aSexesValues[$sKey] = _t($sValue);

        $aStartAgesOptions = array();
        $aEndAgesOptions = array();
        $gl_search_start_age = (int)getParam('search_start_age');
        $gl_search_end_age = (int)getParam('search_end_age');
        for ( $i = $gl_search_start_age ; $i <= $gl_search_end_age ; $i++ ) {
            $aStartAgesOptions[$i] = $i;
        }
        for ( $i = $gl_search_start_age ; $i <= $gl_search_end_age ; $i++ ) {
            $aEndAgesOptions[$i] = $i;
        }

        $aCountryOptions = array('all' => _t('_All'));
        foreach ( $aPreValues['Country'] as $key => $value ) {
            $aCountryOptions[$key] = _t($value['LKey']);
        }

        $aMembershipOptions = array('all' => _t('_All'));
        $memberships_arr = getMemberships();
        foreach ( $memberships_arr as $membershipID => $membershipName ) {
            if ($membershipID == MEMBERSHIP_ID_NON_MEMBER) continue;
            $aMembershipOptions[$membershipID] = $membershipName;
        }

        $iRecipientMembers = (int)$GLOBALS['MySQL']->getOne("SELECT COUNT(`ID`) AS `count` FROM `Profiles` WHERE `Status`<>'Unconfirmed' AND `EmailNotify` = 1 LIMIT 1");
        $aForm = array(
            'form_attrs' => array(
                'name' => 'form_queue',
                'class' => 'form_queue_form',
                'action' => $GLOBALS['site']['url_admin'] . 'notifies.php',
                'method' => 'post',
            ),
            'inputs' => array (
                'Send1' => array(
                    'type' => 'checkbox',
                    'name' => 'send_to_subscribers',
                    'label' => _t('_adm_mmail_Send_to_subscribers'),
                    'value' => 'non',
                    'checked' => true
                ),
                'Send2' => array(
                    'type' => 'checkbox',
                    'name' => 'send_to_members',
                    'label' => _t('_adm_mmail_Send_to_members'),
                    'value' => 'memb',
                    'checked' => true,
                    'attrs' => array(
                        'onClick' => 'setControlsState();',
                    ),
                    'info' => _t('_adm_mmail_Send_to_members_info', $iRecipientMembers),
                ),
                'Sex' => array (
                    'type' => 'select',
                    'name' => 'sex',
                	'caption' => _t('_adm_mmail_Sex'),
					'value' => 'all',
                    'values' => $aSexValues,
                	'attrs' => array(
                        'onClick' => 'setSexState();',
                    ),
                ),
                'Sexes' => array (
                    'type' => 'checkbox_set',
                    'name' => 'sexes',
                    'values' => $aSexesValues,
                    'value' => array_keys($aSexesValues)
                ),
                'Age' => array (
                    'type' => 'select',
                    'name' => 'age',
                    'caption' => _t('_adm_mmail_Age'),
                    'value' => 'all',
                	'values' => $aAgeValues,
                	'attrs' => array(
                        'onClick' => 'setAgeState();',
                    ),
                ),
                'StartAge' => array (
                    'type' => 'select',
                    'name' => 'age_start',
                    'caption' => _t('_from'),
                    'values' => $aStartAgesOptions,
                    'value' => $gl_search_start_age,
                ),
                'EndAge' => array (
                    'type' => 'select',
                    'name' => 'age_end',
                    'caption' => _t('_to'),
                    'values' => $aEndAgesOptions,
                    'value' => $gl_search_end_age,
                ),
                'Country' => array (
                    'type' => 'select',
                    'name' => 'country',
                    'caption' => _t('_Country'),
                    'values' => $aCountryOptions,
                    'value' => 'all',
                ),
                'Membership' => array (
                    'type' => 'select',
                    'name' => 'membership',
                    'caption' => _t('_adm_mmi_membership_levels'),
                    'values' => $aMembershipOptions,
                    'value' => 'all',
                ),
                'msgs_id' => array (
                    'type' => 'hidden',
                    'name' => 'msgs_id',
                    'value' => (int)$_POST['msgs_id'],
                ),
                'submit' => array (
                    'type' => 'submit',
                    'name' => 'queue_message',
                    'value' => _t('_Submit'),
                )
            )
        );

        $oForm = new BxTemplFormView($aForm);
        $sTmplResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('notifies_filter.html', array());
        return DesignBoxContent(_t('_adm_mmail_Queue_message'), $oForm->getCode() . $sTmplResult, 11);
    }
}

function QueueMessage()
{
    global $MySQL;

    $iEmails = 0;
    $sReturn = "";
    $iMsgId = (int)$_POST['msgs_id'];

    $aOriginalMessage = $MySQL->getRow("SELECT `id`, `subject`, `body` FROM `sys_sbs_messages` WHERE `id`= ? LIMIT 1", [$iMsgId]);
    if(!is_array($aOriginalMessage) || empty($aOriginalMessage)) {
        return _t('_adm_mmail_Failed_to_queue_emails_X', $iMsgId);
    }

    //--- Send to all subscribers
    $oEmailTemplates = new BxDolEmailTemplates();
    if($_POST['send_to_subscribers'] == 'non') {
        $sSql = "SELECT
                    `tsu`.`name` AS `user_name`,
                    `tsu`.`email` AS `user_email`,
                    `tst`.`template` AS `template_name`
                FROM `sys_sbs_types` AS `tst`
                INNER JOIN `sys_sbs_entries` AS `tse` ON `tst`.`id`=`tse`.`subscription_id` AND `tse`.`subscriber_type`='" . BX_DOL_SBS_TYPE_VISITOR . "'
                INNER JOIN `sys_sbs_users` AS `tsu` ON `tse`.`subscriber_id`=`tsu`.`id`
                WHERE
                    `tst`.`unit`='system' AND
                    `tst`.`action`='mass_mailer'";
        $aSubscribers = $MySQL->getAll($sSql);

        foreach($aSubscribers as $aSubscriber) {
            if(empty($aSubscriber['user_email']))
                continue;

            $aMessage = $oEmailTemplates->parseTemplate($aSubscriber['template_name'], array(
                'RealName' => $aSubscriber['user_name'],
                'Email' => $aSubscriber['user_email'],
                'MessageSubject' => $aOriginalMessage['subject'],
                'MessageText' => $aOriginalMessage['body']
            ));

            $mixedResult = $MySQL->query("INSERT INTO `sys_sbs_queue`(`email`, `subject`, `body`) VALUES('" . $aSubscriber['user_email'] . "', '" . process_db_input($aMessage['subject'], BX_TAGS_STRIP) . "', '" . process_db_input($aMessage['body'], BX_TAGS_VALIDATE) . "')");
            if($mixedResult === false) {
                $sReturn .= _t('_adm_mmail_Email_not_added_to_queue_X', $aSubscriber['user_email']);
                continue;
            }
            $iEmails++;
        }
    }

    //--- Send to all profiles
    if($_POST['send_to_members'] == 'memb') {
        //--- Sex filter
        $sex_filter_sql = '';
        $sex = $_POST['sex'];
        if($sex != 'all' && !empty($_POST['sexes']) && is_array($_POST['sexes']))
            $sex_filter_sql = "AND `Sex` IN ('" . implode("','", $_POST['sexes']) . "')";

        //--- Age filter
        $age_filter_sql = '';
        $age = $_POST['age'];
        if($age != 'all') {
	        $age_start = (int)$_POST['age_start'];
	        $age_end = (int)$_POST['age_end'];
	        if ( $age_start && $age_end ) {
	            $date_start = (int)( date( "Y" ) - $age_start );
	            $date_end = (int)( date( "Y" ) - $age_end - 1 );
	            $date_start = $date_start . date( "-m-d" );
	            $date_end = $date_end . date( "-m-d" );
	            $age_filter_sql = "AND (TO_DAYS(`DateOfBirth`) BETWEEN TO_DAYS('{$date_end}') AND (TO_DAYS('{$date_start}')+1))";
	        }
        }

        //--- Country filter
        $country_filter_sql = '';
        if($_POST['country'] != 'all') {
            $country = process_db_input($_POST['country']);
            $country_filter_sql = "AND `Country` = '{$country}'";
        }

        //--- Membership filter
        $membershipID = $_POST['membership'] != 'all' ? (int)$_POST['membership'] : -1;

        $aMembers = $MySQL->getAll("SELECT `ID` AS `id`, `Email` AS `email` FROM `Profiles` WHERE `Status` <> 'Unconfirmed' AND `EmailNotify` = 1 AND (`Couple` = '0' OR `Couple` > `ID`) {$sex_filter_sql} {$age_filter_sql} {$country_filter_sql}");
        foreach($aMembers as $aMember) {
            if(empty($aMember['email']))
                continue;

            //--- Dynamic membership filter            
            if ($membershipID != -1) {
                $membership_info = getMemberMembershipInfo($aMember['id']);
                if ($membership_info['ID'] != $membershipID )
                    continue;
            }

            $aMessage = $oEmailTemplates->parseTemplate('t_AdminEmail', array(
                'MessageSubject' => $aOriginalMessage['subject'],
                'MessageText' => $aOriginalMessage['body']
            ), $aMember['id']);

            $mixedResult = $MySQL->query("INSERT INTO `sys_sbs_queue`(`email`, `subject`, `body`) VALUES('" . $aMember['email'] . "', '" . process_db_input($aMessage['subject'], BX_TAGS_STRIP) . "', '" . process_db_input($aMessage['body'], BX_TAGS_VALIDATE) . "')");
            if($mixedResult === false) {
                $sReturn .= _t('_adm_mmail_Email_not_added_to_queue_X', $aMember['email']);
                continue;
            }
            $iEmails++;
        }
    }

    $sReturn .= _t('_adm_mmail_X_emails_was_succ_added_to_queue', (int)$iEmails);
    return $sReturn;
}

function PreviewMessage()
{
    $oEmailTemplate = new BxDolEmailTemplates();
    $aMessage = $oEmailTemplate->parseTemplate('t_AdminEmail', array(
        'MessageText' => process_pass_data($_POST['body'])
    ));

    return DesignBoxContent(_t('_Preview'), $aMessage['body'], 11);
}

function DeleteMessage()
{
    $mixedResult = $GLOBALS['MySQL']->query("DELETE FROM `sys_sbs_messages` WHERE `id`='". (int)$_POST['msgs_id'] . "' LIMIT 1");
    if($mixedResult === false)
        return $mixedResult;

    $_POST['msgs_id'] = 0;
    return true;
}

function EmptyQueue()
{
    return db_res("TRUNCATE TABLE `sys_sbs_queue`");
}
