<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

$GLOBALS['iAdminPage'] = 1;

require_once(BX_DIRECTORY_PATH_INC . 'admin_design.inc.php');

bx_import('Module', $aModule);

global $_page;
global $_page_cont;
global $logged;

check_logged();

$iIndex = 9;
$_page['name_index'] = $iIndex;
$_page['header'] = _t('_wall_pc_admin');
$_page['css_name'] = array('forms_adv.css');

if(!@isAdmin()) {
    send_headers_page_changed();
    login_form("", 1);
    exit;
}

$oWall = new BxWallModule($aModule);

//--- Process actions ---//
$mixedResultSettings = '';
if(isset($_POST['save']) && isset($_POST['cat']))
    $mixedResultSettings = $oWall->setSettings($_POST);
//--- Process actions ---//

$_page_cont[$iIndex]['page_main_code'] = DesignBoxAdmin(_t('_wall_bc_settings'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oWall->getSettingsForm($mixedResultSettings))));

PageCodeAdmin();
