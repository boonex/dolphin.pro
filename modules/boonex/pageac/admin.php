<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'admin_design.inc.php');

bx_import('Module', $aModule);

global $_page;
global $_page_cont;

$iIndex = 9;
$_page['name_index'] = $iIndex;
$_page['header'] = _t('_bx_pageac');

if(!@isAdmin()) {
    send_headers_page_changed();
    login_form("", 1);
    exit;
}

$oModule = new BxPageACModule($aModule);

$_page_cont[$iIndex]['page_main_code'] = $oModule->_oTemplate->getTabs();

PageCodeAdmin();
