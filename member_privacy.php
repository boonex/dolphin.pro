<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

bx_import('BxDolPrivacy');
bx_import('BxTemplPrivacyView');

check_logged();
if(!isLogged()) {
    login_form();
    exit;
}

$iId = getLoggedId();
$oPrivacyView = new BxTemplPrivacyView($iId);

if(isset($_POST['ps_action']) && $_POST['ps_action'] == 'get_chooser' && $iId != 0) {
    $sPageName = (int)$_POST['ps_page_name'];
    $iProfileId = (int)$_POST['ps_profile_id'];
    $iBlockId = (int)$_POST['ps_block_id'];

    $oPrivacy = new BxDolPrivacy('sys_page_compose_privacy', 'id', 'user_id');

    $sCode = "";
    $iMemberId = getLoggedId();
    if($iMemberId == $iProfileId) {
        $aSelect = $oPrivacy->getGroupChooser($iMemberId, $sPageName, 'view_block');

        $iCurGroupId = (int)$GLOBALS['MySQL']->getOne("SELECT `allow_view_block_to` FROM `sys_page_compose_privacy` WHERE `user_id`='" . $iMemberId . "' AND `block_id`='" . $iBlockId . "' LIMIT 1");
        if($iCurGroupId == 0)
            $iCurGroupId = (int)$aSelect['value'];

        $aItems = array();
        foreach($aSelect['values'] as $aValue) {
            if($aValue['key'] == $iCurGroupId)
                $sAlt = $aValue['value'];
            $aItems[] = array(
                'block_id' => $iBlockId,
                'group_id' => $aValue['key'],
                'class' => $aValue['key'] == $iCurGroupId ? 'dbPrivacyGroupActive' : 'dbPrivacyGroup',
                'title' => $aValue['value']
            );
        }

        $sCode = $GLOBALS['oSysTemplate']->parseHtmlByName('ps_page_menu.html', array('bx_repeat:items' => $aItems));
        $sCode = PopupBox('dbPrivacyMenu' . $iBlockId, _t('_ps_bcpt_block_privacy'), $sCode);
    }

    header('Content-Type:text/javascript; charset=utf-8');
    echo json_encode(array(
        'code' => !empty($sCode) ? 0 : 1,
        'data' => $sCode,
    ));
    exit;
} else if (isset($_POST['ps_action']) && $_POST['ps_action'] == 'view_block' && $iId != 0) {
    $iBlockId = (int)$_POST['ps_block_id'];
    $iGroupId = (int)$_POST['ps_group_id'];

    $iPrivacyId = (int)$GLOBALS['MySQL']->getOne("SELECT `id` FROM `sys_page_compose_privacy` WHERE `user_id`='" . $iId . "' AND `block_id`='" . $iBlockId . "' LIMIT 1");
    if($iPrivacyId != 0)
        $sSql = "UPDATE `sys_page_compose_privacy` SET `allow_view_block_to`='" . $iGroupId . "' WHERE `id`='" . $iPrivacyId . "'";
    else
        $sSql = "INSERT INTO `sys_page_compose_privacy`(`user_id`, `block_id`, `allow_view_block_to`) VALUES('" . $iId . "', '" . $iBlockId . "', '" . $iGroupId . "')";

    $sGroupTitle = "";
    if(($bResult = (int)$GLOBALS['MySQL']->query($sSql)) > 0) {
        $aGroup = $GLOBALS['MySQL']->getRow("SELECT `id`, `title` FROM `sys_privacy_groups` WHERE `id`= ? LIMIT ?", [$iGroupId, 1]);
        $sGroupTitle = !empty($aGroup['title']) ? $aGroup['title'] : _t('_ps_group_' . $aGroup['id'] . '_title');
    }

    header('Content-Type:text/javascript; charset=utf-8');
    echo json_encode(array(
        'code' => $bResult ? 0 : 1,
        'group' => $sGroupTitle,
    ));
    exit;
} else if(isset($_POST['ps_action']) && $_POST['ps_action'] == 'search') {
    echo $oPrivacyView->searchMembers(isset($_POST['ps_value']) ? $_POST['ps_value'] : '');
    exit;
} else if(isset($_POST['ps_action']) && $_POST['ps_action'] == 'members') {
    echo $oPrivacyView->getBlockCode_GetMembers(isset($_POST['ps_value']) ? (int)$_POST['ps_value'] : 0);
    exit;
} else if(isset($_POST['ps-add-members-add']) && !empty($_POST['ps-add-members-add'])) {
    $iGroupId = !empty($_POST['ps-add-member-group']) ? (int)$_POST['ps-add-member-group'] : 0;
    $aIds = !empty($_POST['ps-add-member-ids']) ? $_POST['ps-add-member-ids'] : array();
    $oPrivacyView->addMembers($iGroupId, $aIds);
} else if(isset($_POST['ps-del-members-delete']) && !empty($_POST['ps-del-members-delete'])) {
    $iGroupId = !empty($_POST['ps-del-member-group']) ? (int)$_POST['ps-del-member-group'] : 0;
    $aIds = !empty($_POST['ps-del-member-ids']) ? $_POST['ps-del-member-ids'] : array();
    $oPrivacyView->deleteMembers($iGroupId, $aIds);
} else if(isset($_POST['ps-my-groups-delete']) && !empty($_POST['ps-my-groups-delete'])) {
    $aIds = !empty($_POST['ps-my-groups-ids']) ? $_POST['ps-my-groups-ids'] : array();
    $oPrivacyView->deleteGroups($aIds);
} else if(isset($_POST['ps-default-group-save']) && !empty($_POST['ps-default-group-save'])) {
    $iId = !empty($_POST['ps-default-group-ids']) ? (int)$_POST['ps-default-group-ids'] : 0;
    $oPrivacyView->setDefaultGroup($iId);
} else if(isset($_POST['ps-default-values-save']) && !empty($_POST['ps-default-values-save'])) {
    $oPrivacyView->setDefaultValues($_POST);
}

if(!BxDolPrivacy::isPrivacyPage())
    $GLOBALS['oSysTemplate']->displayPageNotFound();

// --------------- page components
$iIndex = 82;
$_page['css_name'] = 'privacy_settings.css';
$_page['header'] = _t( "_ps_pcpt_privacy_settings" );
$_page['header_text'] = "";
$_page['name_index'] = $iIndex;

$sBlockAddMembers = $sBlockDeleteMembers = $sBlockMyGroups = $sBlockCreateGroup = "";
if(getParam('sys_ps_enable_create_group') == 'on') {
    $sBlockAddMembers = $oPrivacyView->getBlockCode_AddMembers();
    $sBlockDeleteMembers = $oPrivacyView->getBlockCode_DeleteMembers();
    $sBlockMyGroups = $oPrivacyView->getBlockCode_MyGroups();
    $sBlockCreateGroup = $oPrivacyView->getBlockCode_CreateGroup();
}

$sBlockDefaultGroup = "";
if(getParam('sys_ps_enabled_group_1') == 'on') {
    $sBlockDefaultGroup = $oPrivacyView->getBlockCode_DefaultGroup();
}

$sBlockDefaultValues = "";
if(getParam('sys_ps_enable_default_values') == 'on') {
    $sBlockDefaultValues = $oPrivacyView->getBlockCode_DefaultValues();
}

$_page_cont[$iIndex]['page_code_add_members'] = $sBlockAddMembers;
$_page_cont[$iIndex]['page_code_delete_members'] = $sBlockDeleteMembers;
$_page_cont[$iIndex]['page_code_my_groups'] = $sBlockMyGroups;
$_page_cont[$iIndex]['page_code_create_group'] = $sBlockCreateGroup;
$_page_cont[$iIndex]['page_code_default_group'] = $sBlockDefaultGroup;
$_page_cont[$iIndex]['page_code_default_values'] = $sBlockDefaultValues;
// --------------- [END] page components

PageCode();
// --------------- page components functions
