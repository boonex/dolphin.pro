<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */
class BxPmtOrders
{
    var $_oDb;
    var $_oConfig;
    var $_oTemplate;

    var $_iUserId;
    var $_sLangsPrefix;

    /*
     * Constructor.
     */
    function __construct($iUserId, &$oDb, &$oConfig, &$oTemplate)
    {
        $this->_oDb = &$oDb;
        $this->_oConfig = &$oConfig;
        $this->_oTemplate = &$oTemplate;

        $this->_iUserId = $iUserId;
        $this->_sLangsPrefix = $this->_oConfig->getLangsPrefix();
    }
    function getExtraJs()
    {
        $sJsClass = $this->_oConfig->getJsClass('orders');
        $sJsObject = $this->_oConfig->getJsObject('orders');
        ob_start();
?>
        var <?=$sJsObject; ?> = new <?=$sJsClass; ?>({
            sActionUrl: '<?=BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(); ?>',
            sObjName: '<?=$sJsObject; ?>'
        });
<?php
        $sJsContent = ob_get_clean();

        return $this->_oTemplate->parseHtmlByTemplateName('script', array('content' => $sJsContent));
    }
    function getOrder($sType, $iId)
    {
        return $this->_oTemplate->displayOrder($sType, $iId);
    }
    function getOrders($sType, $aParams)
    {
        return $this->_oTemplate->displayOrders($sType, $aParams);
    }
    function getOrdersBlock($sType, $iUserId = BX_PMT_EMPTY_ID)
    {
    	$sLangsPrefix = $this->_oConfig->getLangsPrefix();

    	if(!isLogged())
            return MsgBox(_t($sLangsPrefix . 'err_required_login'));

        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();

        $sJsObject = $this->_oConfig->getJsObject('orders');
        $aTopMenu = array(
            'pmt-orders-processed-lnk' => array('href' => $sBaseUrl . 'orders/processed/', 'title' => _t($sLangsPrefix . 'btn_orders_processed'), 'active' => $sType == BX_PMT_ORDERS_TYPE_PROCESSED ? 1 : 0),
            'pmt-orders-pending-lnk' => array('href' => $sBaseUrl . 'orders/pending/', 'title' => _t($sLangsPrefix . 'btn_orders_pending'), 'active' => $sType == BX_PMT_ORDERS_TYPE_PENDING ? 1 : 0),
            'pmt-payment-settings-lnk' => array('href' =>  $sBaseUrl . 'details/', 'title' => _t($sLangsPrefix . 'btn_settings'))
        );

        $sTitle = $this->_sLangsPrefix . ($sType == 'processed' ? 'bcpt_processed_orders' : 'bcpt_pending_orders');
        $sContent = $this->_oTemplate->displayOrdersBlock($sType, $iUserId != BX_PMT_EMPTY_ID ? $iUserId : $this->_iUserId);
        return array($sContent, $aTopMenu, array(), _t($sTitle), 'getBlockCaptionMenu');
    }
    function report($sType, $aOrders)
    {
        $sMethodName = "report" . ucfirst($sType) . "Orders";
        if(!$this->_oDb->$sMethodName($aOrders))
            return array('code' => 3, 'message' => $this->_sLangsPrefix . 'err_unknown');

        return array('code' => 10, 'message' => $this->_sLangsPrefix . 'inf_successfully_reported');
    }
    function cancel($sType, $aOrders)
    {
        $sMethodName = "cancel" . ucfirst($sType) . "Orders";

        if($sType == BX_PMT_ORDERS_TYPE_PROCESSED)
            foreach($aOrders as $iOrderId) {
                $aOrder = $this->_oDb->getProcessed(array('type' => 'id', 'id' => $iOrderId));
                BxDolService::call(
                    (int)$aOrder['module_id'],
                    'unregister_cart_item',
                    array(
                        $aOrder['client_id'],
                        $aOrder['seller_id'],
                        $aOrder['item_id'],
                        $aOrder['item_count'],
                        $aOrder['order_id']
                    )
                );
            }

        if(!$this->_oDb->$sMethodName($aOrders))
            return array('code' => 3, 'message' => $this->_sLangsPrefix . 'err_unknown');

        return array('code' => 0, 'message' => '');
    }
    function getMoreWindow()
    {
        return $this->_oTemplate->displayMoreWindow();
    }
    function getManualOrderWindow()
    {
        $sJsObject = $this->_oConfig->getJsObject('orders');
        $aModulesInfo = $this->_oDb->getModules();

        $aModules = array(
            array('key' => '0', 'value' => _t($this->_sLangsPrefix . 'ocpt_select'))
        );
        foreach($aModulesInfo as $aModule)
           $aModules[] = array('key' => $aModule['id'], 'value' => $aModule['title']);

        $aForm = array(
            'form_attrs' => array(
                'id' => 'pmt-manual-order-form',
                'name' => 'text_data',
                'action' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'act_manual_order_submit/',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'target' => 'pmt-manual-order-iframe'
            ),
            'inputs' => array (
                'vendor' => array(
                    'type' => 'hidden',
                    'name' => 'vendor',
                    'value' => $this->_iUserId
                ),
                'client' => array(
                    'type' => 'text',
                    'name' => 'client',
                    'caption' => _t($this->_sLangsPrefix . "fcpt_client"),
                    'value' => ''
                ),
                'order' => array(
                    'type' => 'text',
                    'name' => 'order',
                    'caption' => _t($this->_sLangsPrefix . "fcpt_order"),
                    'value' => ''
                ),
                'module_id' => array(
                    'type' => 'select',
                    'name' => 'module_id',
                    'caption' => _t($this->_sLangsPrefix . "fcpt_module"),
                    'value' => '',
                    'values' => $aModules,
                    'attrs' => array(
                        'onchange' => 'javascript:' . $sJsObject . '.selectModule(this);'
                    )
                ),
                'add' => array(
                    'type' => 'submit',
                    'name' => 'add',
                    'colspan' => true,
                    'value' => _t($this->_sLangsPrefix . "btn_add"),
                ),
            )
        );

        return $this->_oTemplate->displayManualOrderWindow($aForm);
    }
    function addManualOrder($aData)
    {
        $iVendorId = isset($aData['vendor']) ? (int)$aData['vendor'] : $this->_iUserId;

        if(!isset($aData['client']) || empty($aData['client']))
            return array('code' => 2, 'message' => $this->_sLangsPrefix . 'err_wrong_client');

        $iClientId = 0;
        if(($iClientId = $this->_oDb->userExists(process_db_input($aData['client'], BX_TAGS_STRIP))) === false)
            return array('code' => 2, 'message' => $this->_sLangsPrefix . 'err_wrong_client');

        if($iVendorId == $iClientId)
            return array('code' => 3, 'message' => $this->_sLangsPrefix . 'err_purchase_from_yourself');

        if(!isset($aData['order']) || empty($aData['order']))
            return array('code' => 4, 'message' => $this->_sLangsPrefix . 'err_wrong_order');

        $sOrder = trim(process_db_input($aData['order'], BX_TAGS_STRIP));
        if(empty($sOrder))
            return array('code' => 4, 'message' => $this->_sLangsPrefix . 'err_wrong_order');

        $iModuleId = (int)$aData['module_id'];
        if($iModuleId <= 0)
            return array('code' => 5, 'message' => $this->_sLangsPrefix . 'err_wrong_module');

        if(!isset($aData['items']) || !is_array($aData['items']) || empty($aData['items']))
            return array('code' => 6, 'message' => $this->_sLangsPrefix . 'err_empty_items');

        $aCartInfo = array('vendor_id' => $iVendorId, 'items_price' => 0, 'items' => array());
        foreach($aData['items'] as $iItemId) {
            $iItemId = (int)$iItemId;

            $sKeyPrice = 'item-price-' . $iItemId;
            $sKeyQuantity = 'item-quantity-' . $iItemId;
            if(!isset($aData[$sKeyQuantity]) || (int)$aData[$sKeyQuantity] <= 0)
                return array('code' => 7, 'message' => $this->_sLangsPrefix . 'err_wrong_quantity');

            $aCartInfo['items_price'] += (float)$aData[$sKeyPrice] * (int)$aData[$sKeyQuantity];
            $aCartInfo['items'][] = array(
                'vendor_id' => $iVendorId,
                'module_id' => $iModuleId,
                'id' => $iItemId,
                'quantity' => (int)$aData[$sKeyQuantity]
            );
        }
        $iPendingId = $this->_oDb->insertPending($iClientId, 'manual', $aCartInfo);
        $this->_oDb->updatePending($iPendingId, array(
            'order' => $sOrder,
            'error_code' => 0,
            'error_msg' => 'Manually processed'
        ));

        return (int)$iPendingId;
    }
}
