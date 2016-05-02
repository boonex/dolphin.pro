<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('Module', $aModule);
bx_import('BxDolPageView');

class BxPmtOrdersPage extends BxDolPageView
{
    var $_oPayments;
    var $_sType;

    function __construct($sType, &$oPayments)
    {
        parent::__construct('bx_pmt_orders');

        $this->_sType = $sType;
        $this->_oPayments = &$oPayments;

        $GLOBALS['oTopMenu']->setCurrentProfileID($this->_oPayments->_iUserId);
        $GLOBALS['oTopMenu']->setCustomVar('sys_payment_module_uri', $this->_oPayments->_oConfig->getUri());
    }
    function getBlockCode_Orders()
    {
        if(empty($this->_sType))
            $this->_sType = BX_PMT_ORDERS_TYPE_PROCESSED;

        return $this->_oPayments->getOrdersBlock($this->_sType);
    }
}

global $_page;
global $_page_cont;
global $logged;

$iIndex = 3;
$_page['name_index'] = $iIndex;
$_page['css_name'] = 'orders.css';
$_page['js_name'] = 'orders.js';

check_logged();

$sType = '';
if(!empty($aRequest))
    $sType = process_db_input(array_shift($aRequest), BX_TAGS_STRIP);

$oPayments = new BxPmtModule($aModule);
$oOrdersPage = new BxPmtOrdersPage($sType, $oPayments);
$_page_cont[$iIndex]['page_main_code'] = $oOrdersPage->getCode();
$_page_cont[$iIndex]['more_code'] = $oPayments->getMoreWindow();
$_page_cont[$iIndex]['manual_order_code'] = $oPayments->getManualOrderWindow();
$_page_cont[$iIndex]['js_code'] = $oPayments->getExtraJs('orders');

$oPayments->_oTemplate->setPageTitle(_t('_payment_pcpt_view_orders'));
PageCode($oPayments->_oTemplate);
