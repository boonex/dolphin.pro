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
$_page['header'] = _t('_payment_pcpt_admin');
$_page['css_name'] = '';

if(!@isAdmin()) {
    send_headers_page_changed();
    login_form("", 1);
    exit;
}

$oPayments = new BxPmtModule($aModule);
$aDetailsBox = $oPayments->getDetailsForm(BX_PMT_ADMINISTRATOR_ID);
$aPendingOrdersBox = $oPayments->getOrdersBlock('pending', BX_PMT_ADMINISTRATOR_ID);
$aProcessedOrdersBox = $oPayments->getOrdersBlock('processed', BX_PMT_ADMINISTRATOR_ID);

$mixedResultSettings = '';
if(isset($_POST['save']) && isset($_POST['cat'])) {
    $mixedResultSettings = $oPayments->setSettings($_POST);
}

$oPayments->_oTemplate->addAdminJs(array('orders.js'));
$oPayments->_oTemplate->addAdminCss(array('orders.css'));
$_page_cont[$iIndex]['page_main_code'] = $oPayments->getExtraJs('orders');
$_page_cont[$iIndex]['page_main_code'] .= DesignBoxAdmin(_t('_payment_bcpt_settings'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oPayments->getSettingsForm($mixedResultSettings))));
$_page_cont[$iIndex]['page_main_code'] .= DesignBoxAdmin(_t('_payment_bcpt_details'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $aDetailsBox[0])));
$_page_cont[$iIndex]['page_main_code'] .= DesignBoxAdmin(_t('_payment_bcpt_pending_orders'), $aPendingOrdersBox[0]);
$_page_cont[$iIndex]['page_main_code'] .= DesignBoxAdmin(_t('_payment_bcpt_processed_orders'), $aProcessedOrdersBox[0]);
$_page_cont[$iIndex]['page_main_code'] .= $oPayments->getMoreWindow();
$_page_cont[$iIndex]['page_main_code'] .= $oPayments->getManualOrderWindow();
PageCodeAdmin();
