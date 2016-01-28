<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxPmtCart
{
	static public $DESCRIPTOR_DIVIDER = '_';

    var $_oDb;
    var $_oConfig;
    var $_oTemplate;
    var $_sLangsPrefix;

    /*
     * Constructor.
     */
    function __construct(&$oDb, &$oConfig, &$oTemplate)
    {
        $this->_oDb = &$oDb;
        $this->_oConfig = &$oConfig;
        $this->_oTemplate = &$oTemplate;
        $this->_sLangsPrefix = $this->_oConfig->getLangsPrefix();
    }
    function getHistoryBlock($iUserId, $iSellerId)
    {
        return $this->_oTemplate->displayHistoryBlock($iUserId, $iSellerId);
    }
    function getCartJs($bWrapped = true)
    {
        return $this->_oTemplate->displayCartJs($bWrapped);
    }
    function getAddToCartJs($iVendorId, $iModuleId, $iItemId, $iItemCount, $bNeedRedirect = false, $bWrapped = true)
    {
        return $this->_oTemplate->displayAddToCartJs($iVendorId, $iModuleId, $iItemId, $iItemCount, $bNeedRedirect, $bWrapped);
    }
    function getAddToCartLink($iVendorId, $iModuleId, $iItemId, $iItemCount, $bNeedRedirect = false)
    {
        return $this->_oTemplate->displayAddToCartLink($iVendorId, $iModuleId, $iItemId, $iItemCount, $bNeedRedirect);
    }
    function addToCart($iClientId, $iVendorId, $iModuleId, $iItemId, $iItemCount)
    {
        if($iVendorId == BX_PMT_EMPTY_ID || empty($iModuleId) || empty($iItemId) || empty($iItemCount))
            return array('code' => 1, 'message' => _t($this->_sLangsPrefix . 'err_wrong_data'));

        if(empty($iClientId))
            return array('code' => 2, 'message' => _t($this->_sLangsPrefix . 'err_required_login'));

        if($iClientId == $iVendorId)
            return array('code' => 3, 'message' => _t($this->_sLangsPrefix . 'err_purchase_from_yourself'));

        $aVendor = $this->_oDb->getVendorInfoProfile($iVendorId);
        if($aVendor['status'] != 'Active')
            return array('code' => 4, 'message' => _t($this->_sLangsPrefix . 'err_inactive_vendor'));

        $aVendorProviders = $this->_oDb->getVendorInfoProviders($iVendorId);
        if(empty($aVendorProviders))
            return array('code' => 5, 'message' => _t($this->_sLangsPrefix . 'err_not_accept_payments'));

		$sDd = self::$DESCRIPTOR_DIVIDER;
        $sCartItem = $iVendorId . $sDd . $iModuleId . $sDd . $iItemId . $sDd . $iItemCount;
        $sCartItems = $this->_oDb->getCartItems($iClientId);

        if(strpos($sCartItems, $iVendorId . $sDd . $iModuleId . $sDd . $iItemId . $sDd) !== false)
            $sCartItems = preg_replace("'" . $iVendorId . $sDd . $iModuleId . $sDd . $iItemId . $sDd ."([0-9])+'e", "'" . $iVendorId . $sDd . $iModuleId . $sDd . $iItemId . $sDd ."' . (\\1 + " . $iItemCount . ")",  $sCartItems);
        else
            $sCartItems = empty($sCartItems) ? $sCartItem : $sCartItems . ":" . $sCartItem;

        $this->_oDb->setCartItems($iClientId, $sCartItems);

        $aInfo = $this->getInfo($iClientId);
        $iTotalQuantity = 0;
        foreach($aInfo as $aCart)
           $iTotalQuantity += $aCart['items_count'];

        return array('code' => 0, 'message' => _t($this->_sLangsPrefix . 'inf_successfully_added'), 'total_quantity' => $iTotalQuantity, 'content' => $this->_oTemplate->displayToolbarSubmenu($aInfo));
    }
    function deleteFromCart($iClientId, $iVendorId, $iModuleId = 0, $iItemId = 0)
    {
        if($iVendorId == BX_PMT_EMPTY_ID)
            return array('code' => 1, 'message' => _t($this->_sLangsPrefix . 'err_wrong_data'));

        if(empty($iClientId))
            return array('code' => 2, 'message' => _t($this->_sLangsPrefix . 'err_required_login'));

        if(!empty($iModuleId) && !empty($iItemId))
            $sPattern = "'" . $iVendorId . "_" . $iModuleId . "_" . $iItemId . "_[0-9]+:?'";
        else
            $sPattern = "'" . $iVendorId . "_[0-9]+_[0-9]+_[0-9]+:?'";

        $sCartItems = $this->_oDb->getCartItems($iClientId);
        $sCartItems = trim(preg_replace($sPattern, "", $sCartItems), ":");
        $this->_oDb->setCartItems($iClientId, $sCartItems);

        return array('code' => 0, 'message' => _t($this->_sLangsPrefix . 'inf_successfully_deleted'));
    }
    function getInfo($iUserId, $iVendorId = BX_PMT_EMPTY_ID, $aItems = array())
    {
        if($iVendorId != BX_PMT_EMPTY_ID && !empty($aItems))
            return $this->_getInfo($iUserId, $iVendorId, $this->items2array($aItems));

        $aContent = $this->parseByVendor($iUserId);

        if($iVendorId != BX_PMT_EMPTY_ID)
            return isset($aContent[$iVendorId]) ? $this->_getInfo($iUserId, $iVendorId, $aContent[$iVendorId]) : array();

        $aResult = array();
        foreach($aContent as $iVendorId => $aVendorItems)
            $aResult[$iVendorId] = $this->_getInfo($iUserId, $iVendorId, $aVendorItems);

        return $aResult;
    }
    function getDescriptor($iVendorId, $iModuleId, $iItemId, $iItemCount)
    {
    	return $iVendorId . self::$DESCRIPTOR_DIVIDER . $iModuleId . self::$DESCRIPTOR_DIVIDER . $iItemId . self::$DESCRIPTOR_DIVIDER . $iItemCount;
    }
    function updateInfo($mixedPending)
    {
    	$aPending = is_array($mixedPending) ? $mixedPending : $this->_oDb->getPending(array('type' => 'id', 'id' => (int)$mixedPending));
		if((int)$aPending['processed'] == 1)
			return;

		$iClientId = (int)$aPending['client_id'];
		$sOrderId = $this->_oConfig->generateLicense();

        $sCartItems = $this->_oDb->getCartItems($iClientId);
        $aItems = $this->items2array($aPending['items']);

        foreach($aItems as $aItem) {
            $aItemInfo = BxDolService::call((int)$aItem['module_id'], 'register_cart_item', array($aPending['client_id'], $aPending['seller_id'], $aItem['item_id'], $aItem['item_count'], $sOrderId));
            if(!is_array($aItemInfo) || empty($aItemInfo))
                continue;

            $this->_oDb->insertTransaction(array(
                'pending_id' => $aPending['id'],
                'order_id' => $sOrderId,
                'client_id' => $aPending['client_id'],
                'seller_id' => $aPending['seller_id'],
                'module_id' => $aItem['module_id'],
                'item_id' => $aItem['item_id'],
                'item_count' => $aItem['item_count'],
                'amount' => $aItemInfo['price'] * $aItem['item_count'],
            ));

            $sCartItems = trim(preg_replace("'" . implode(self::$DESCRIPTOR_DIVIDER, $aItem) . ":?'", "", $sCartItems), ":");
        }

		$this->_oDb->setCartItems($iClientId, $sCartItems);
        $this->_oDb->updatePending($aPending['id'], array('processed' => 1));
    }
    function parseByVendor($iUserId)
    {
        $sItems = $this->_oDb->getCartItems($iUserId);
        return $this->_reparseBy($this->items2array($sItems), 'vendor_id');
    }
    function parseByModule($iUserId)
    {
        $sItems = $this->_oDb->getCartItems($iUserId);
        return $this->_reparseBy($this->items2array($sItems), 'module_id');
    }
    function _reparseBy($aItems, $sKey)
    {
        $aResult = array();
        foreach($aItems as $aItem)
            if(isset($aItem[$sKey]))
                $aResult[$aItem[$sKey]][] = $aItem;

        return $aResult;
    }
    /**
     * Enter description here...
     *
     * @param  integer $iClientId client's ID
     * @param  integer $iVendorId vendor's ID
     * @param  array   $aItems    item descriptors(quaternions) from shopping cart.
     * @return array   with full info about vendor and items.
     */
    function _getInfo($iClientId, $iVendorId, $aItems)
    {
        $iItemsCount = 0;
        $fItemsPrice = 0;
        $aItemsInfo = array();
        foreach($aItems as $aItem) {
            $aItemInfo = BxDolService::call((int)$aItem['module_id'], 'get_cart_item', array($iClientId, $aItem['item_id']));
            $aItemInfo['module_id'] = (int)$aItem['module_id'];
            $aItemInfo['quantity'] = (int)$aItem['item_count'];

            $iItemsCount += $aItem['item_count'];
            $fItemsPrice += $aItem['item_count'] * $aItemInfo['price'];
            $aItemsInfo[] = $aItemInfo;
        }

        $aVendor = $this->_oDb->getVendorInfoProfile((int)$iVendorId);
        return array(
        	'client_id' => $iClientId,
            'vendor_id' => $aVendor['id'],
            'vendor_username' => $aVendor['username'],
            'vendor_profile_name' => $aVendor['profile_name'],
            'vendor_profile_icon' => $aVendor['profile_icon'],
            'vendor_profile_url' => $aVendor['profile_url'],
            'vendor_currency_code' => $aVendor['currency_code'],
            'vendor_currency_sign' => $aVendor['currency_sign'],
            'items_count' => $iItemsCount,
            'items_price' => $fItemsPrice,
            'items' => $aItemsInfo
        );
    }

    /**
     * Static method.
     * Conver items to array with necessary structure.
     *
     * @param  string/array $mixed - string with cart items divided with (:) or an array of cart items.
     * @return array        with items.
     */
    public static function items2array($mixed)
    {
        $aResult = array();

        if(is_string($mixed))
           $aItems = explode(':', $mixed);
        else if(is_array($mixed))
           $aItems = $mixed;
        else
            $aItems = array();

        foreach($aItems as $sItem) {
            $aItem = explode(self::$DESCRIPTOR_DIVIDER, $sItem);
            $aResult[] = array('vendor_id' => $aItem[0], 'module_id' => $aItem[1], 'item_id' => $aItem[2], 'item_count' => $aItem[3]);
        }

        return $aResult;
    }
}
