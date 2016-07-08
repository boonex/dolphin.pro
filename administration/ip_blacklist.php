<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );

bx_import('BxDolPaginate');
bx_import('BxDolAdminIpBlockList');

$logged['admin'] = member_auth( 1, true, true );

$oBxDolAdminIpBlockList = new BxDolAdminIpBlockList();

$sResult = '';
switch(bx_get('action')) {
    case 'apply_delete':
        $oBxDolAdminIpBlockList->ActionApplyDelete();
        break;
}

$iNameIndex = 3;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('ip_blacklist.css'),
    'js_name' => array(),
    'header' => _t('_adm_ipbl_title'),
    'header_text' => _t('_adm_ipbl_title')
);

$aPages = array (
    'manage' => array (
        'title' => _t('_adm_txt_manage'),
        'url' => BX_DOL_URL_ADMIN . 'ip_blacklist.php?mode=manage',
        'func' => 'PageCodeManage',
        'func_params' => array(),
    ),
    'list' => array (
        'title' => _t('_adm_txt_list'),
        'url' => BX_DOL_URL_ADMIN . 'ip_blacklist.php?mode=list',
        'func' => 'PageCodeIpMembers',
        'func_params' => array(),
    ),
    'settings' => array (
        'title' => _t('_Settings'),
        'url' => BX_DOL_URL_ADMIN . 'ip_blacklist.php?mode=settings',
        'func' => 'PageCodeSettings',
        'func_params' => array(),
    ),
);

if (!isset($_GET['mode']) || !isset($aPages[$_GET['mode']]))
    $sMode = 'manage';
else
    $sMode = $_GET['mode'];

$aTopItems = array();
foreach ($aPages as $k => $r)
    $aTopItems['dbmenu_' . $k] = array(
        'href' => $r['url'],
        'title' => $r['title'],
        'active' => $k == $sMode ? 1 : 0
    );

$_page_cont[$iNameIndex]['page_main_code'] = call_user_func_array($aPages[$sMode]['func'], $aPages[$sMode]['func_params']);

PageCodeAdmin();

function PageCodeManage ()
{
    global $oBxDolAdminIpBlockList;

    $s = DesignBoxAdmin(_t('_adm_ipbl_manage'), $oBxDolAdminIpBlockList->getManagingForm(), $GLOBALS['aTopItems'], '', 11);

    $s .= DesignBoxAdmin(_t('_adm_ipbl_Type' . (int)getParam('ipListGlobalType') . '_desc'), $oBxDolAdminIpBlockList->GenIPBlackListTable(), '', '', 11);

    return $s;
}

function PageCodeIpMembers ()
{
    global $oBxDolAdminIpBlockList;

    $s = getParam('enable_member_store_ip') ? $oBxDolAdminIpBlockList->GenStoredMemIPs() : MsgBox(_t('_Empty'));

    return DesignBoxAdmin(_t('_adm_ipbl_Stored_members_caption'), $s, $GLOBALS['aTopItems'], '', 11);
}

function PageCodeSettings ()
{
    bx_import('BxDolAdminSettings');
    $oSettings = new BxDolAdminSettings(22);

    $sResults = false;
    if (isset($_POST['save']) && isset($_POST['cat']))
        $sResult = $oSettings->saveChanges($_POST);

    $s = $oSettings->getForm();
    if ($sResult)
        $s = $sResult . $s;

    return DesignBoxAdmin(_t('_Settings'), $s, $GLOBALS['aTopItems'], '', 11);
}
