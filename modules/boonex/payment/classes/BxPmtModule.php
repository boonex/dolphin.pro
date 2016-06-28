<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxDolAdminSettings');

define('BX_PMT_ORDERS_TYPE_PENDING', 'pending');
define('BX_PMT_ORDERS_TYPE_PROCESSED', 'processed');
define('BX_PMT_ORDERS_TYPE_SUBSCRIPTION', 'subscription');
define('BX_PMT_ORDERS_TYPE_HISTORY', 'history');

define('BX_PMT_EMPTY_ID', -1);
define('BX_PMT_ADMINISTRATOR_ID', 0);
define('BX_PMT_ADMINISTRATOR_USERNAME', 'administrator');

/**
 * Payment module by BoonEx
 *
 * This module is needed to work with payment providers and organize the process
 * of some item purchasing. Shopping Cart and Orders Manager are included.
 *
 * Integration notes:
 * To integrate your module with this one, you need:
 * 1. Get 'Add To Cart' button using serviceGetAddToCartLink service.
 * 2. Add info about your module in the 'bx_pmt_modules' table.
 * 3. Realize the following service methods in your Module class.
 *   a. serviceGetItems($iVendorId) - Is used in Orders Administration to get all products of the requested seller(vendor).
 *   b. serviceGetCartItem($iClientId, $iItemId) - Is used in Shopping Cart to get one product by specified id.
 *   c. serviceRegisterCartItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrderId) - Register purchased product.
 *   d. serviceUnregisterCartItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrderId) - Unregister the product purchased earlier.
 * @see You may see an example of integration in Membership module.
 *
 *
 * Profile's Wall:
 * no spy events
 *
 *
 *
 * Spy:
 * no spy events
 *
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 *
 * Service methods:
 *
 * Is used to get "Add to cart" link for some item(s) in your module.
 * @see BxPmtModule::serviceGetAddToCartLink
 * BxDolService::call('payment', 'get_add_to_cart_link', array($iVendorId, $mixedModuleId, $iItemId, $iItemCount));
 *
 * Check transaction(s) in database which satisty all conditions.
 * @see BxPmtModule::serviceGetTransactionsInfo
 * BxDolService::call('payment', 'get_transactions_info', array($aConditions));
 *
 * Get total count of items in Shopping Cart.
 * @see BxPmtModule::serviceGetCartItemCount
 * BxDolService::call('payment', 'get_cart_item_count', array($iUserId, $iOldCount));
 * @note is needed for internal usage(integration with member tool bar).
 *
 * Get Shopping cart content.
 * @see BxPmtModule::serviceGetCartItems
 * BxDolService::call('payment', 'get_cart_items');
 * @note is needed for internal usage(integration with member tool bar).
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxPmtModule extends BxDolModule
{
    var $_iUserId;
    var $_oDetails;
    var $_oCart;
    var $_oOrders;
    var $_aOrderTypes;
    var $_sGeneralPrefix;
    var $_sLangsPrefix;
    var $_sEmailTemplatesPrefix;

    protected $_sSessionKeyPending;
    protected $_sRequestKeyPending;

    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_oConfig->init($this->_oDb);

        $this->_iUserId = $this->getUserId();
        $this->_sGeneralPrefix = $this->_oConfig->getGeneralPrefix();
        $this->_sLangsPrefix = $this->_oConfig->getLangsPrefix();
        $this->_sEmailTemplatesPrefix = $this->_oConfig->getEmailTemplatesPrefix();

        $this->_sSessionKeyPending = $this->_sGeneralPrefix . 'pending_id';
        $this->_sRequestKeyPending = $this->_sGeneralPrefix . 'pending_id';

        $sClassPrefix = $this->_oConfig->getClassPrefix();

        bx_import('Cart', $aModule);
        $sClassName = $sClassPrefix . 'Cart';
        $this->_oCart = new $sClassName($this->_oDb, $this->_oConfig, $this->_oTemplate);

        bx_import('Details', $aModule);
        $sClassName = $sClassPrefix . 'Details';
        $this->_oDetails = new $sClassName($this->_oDb, $this->_oConfig);

        bx_import('Orders', $aModule);
        $sClassName = $sClassPrefix . 'Orders';
        $this->_oOrders = new $sClassName($this->_iUserId, $this->_oDb, $this->_oConfig, $this->_oTemplate);

        $this->_aOrderTypes = array(
        	BX_PMT_ORDERS_TYPE_PENDING, 
        	BX_PMT_ORDERS_TYPE_PROCESSED, 
        	BX_PMT_ORDERS_TYPE_SUBSCRIPTION, 
        	BX_PMT_ORDERS_TYPE_HISTORY
        );
    }


    /**
     *
     * Public Methods of Common Usage
     *
     */
    function getExtraJs($sType)
    {
        $sResult = "";
        switch($sType) {
            case 'orders':
                $sResult = $this->_oOrders->getExtraJs();
                break;
        }
        return $sResult;
    }


    /**
     *
     * Manage Orders Methods
     *
     */
    function getMoreWindow()
    {
        return $this->_oOrders->getMoreWindow();
    }
    function getManualOrderWindow()
    {
        return $this->_oOrders->getManualOrderWindow();
    }
    function getOrdersBlock($sType, $iUserId = BX_PMT_EMPTY_ID)
    {
        return $this->_oOrders->getOrdersBlock($sType, $iUserId);
    }
	function serviceGetOrdersUrl()
    {
    	if(!$this->isLogged())
            return '';

    	return BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'orders/';
    }
    function actionGetItems($iModuleId)
    {
        $aItems = BxDolService::call((int)$iModuleId, 'get_items', array($this->_iUserId));
        if(is_array($aItems) && !empty($aItems)) {
            $aResult = array('code' => 0, 'message' => '', 'data' => $this->_oTemplate->displayItems($aItems));
            if(isset($aItems[0]['vendor_id']))
                $aResult['vendor_id'] = $aItems[0]['vendor_id'];
        } else
            $aResult = array('code' => 1, 'message' => MsgBox(_t($this->_sLangsPrefix . 'msg_no_results')));

        header('Content-Type:text/javascript; charset=utf-8');
        return json_encode($aResult);
    }
    function actionGetOrder()
    {
        $aData = &$_POST;

        header('Content-Type:text/javascript; charset=utf-8');

        if(!isset($aData['type']) || !in_array($aData['type'], $this->_aOrderTypes))
           return json_encode(array('code' => 1, 'message' => MsgBox(_t($this->_sLangsPrefix . 'err_wrong_data'))));

        $iId = 0;
        if(isset($aData['id']))
            $iId = (int)$aData['id'];

        $sData = $this->_oOrders->getOrder($aData['type'], $iId);
        return json_encode(array('code' => 0, 'message' => '', 'data' => $sData));
    }
    function actionGetOrders()
    {
        $aData = &$_POST;

        header('Content-Type:text/javascript; charset=utf-8');

        if(!isset($aData['type']) || !in_array($aData['type'], $this->_aOrderTypes))
           return json_encode(array('code' => 1, 'message' => $this->_sLangsPrefix . 'err_wrong_data'));

        $iStart = 0;
        if(isset($aData['start']))
            $iStart = (int)$aData['start'];

        $iPerPage = 0;
        if(isset($aData['per_page']))
            $iPerPage = (int)$aData['per_page'];

        $sFilter = "";
        if(isset($aData['filter']))
            $sFilter = process_db_input($aData['filter'], BX_TAGS_STRIP);

        $aParams = array(
            'start' => $iStart,
            'per_page' => $iPerPage,
            'filter' => $sFilter,
            'seller_id' => (int)$aData['seller_id']
        );
        if($aData['type'] == BX_PMT_ORDERS_TYPE_HISTORY)
            $aParams['user_id'] = (int)$this->_iUserId;

        $sData = $this->_oOrders->getOrders($aData['type'], $aParams);
        return json_encode(array('code' => 0, 'message' => '', 'data' => $sData));
    }
    function actionManualOrderSubmit()
    {
        $aResult = array(
            'js_object' => $this->_oConfig->getJsObject('orders'),
            'parent_id' => 'pmt-mo-content'
        );

        if(!$this->isLogged())
            return $this->_onResultInline(array_merge($aResult, array('code' => 1, 'message' => $this->_sLangsPrefix . 'err_required_login')));

        $mixedResult = $this->_oOrders->addManualOrder($_POST);
        if(is_array($mixedResult))
            return $this->_onResultInline(array_merge($aResult, $mixedResult));

        $this->_oCart->updateInfo((int)$mixedResult);
        return $this->_onResultInline(array_merge($aResult, array('code' => 0, 'message' => '')));
    }
    function actionOrdersSubmit($sType)
    {
        $aResult = array(
            'js_object' => $this->_oConfig->getJsObject('orders'),
            'parent_id' => 'pmt-form-' . $sType
        );

        if(!$this->isLogged())
            return $this->_onResultInline(array_merge($aResult, array('code' => 1, 'message' => $this->_sLangsPrefix . 'err_required_login')));

        $aData = &$_POST;

        if(!isset($aData['orders']) || !is_array($aData['orders']) || empty($aData['orders']))
            return $this->_onResultInline(array_merge($aResult, array('code' => 2, 'message' => $this->_sLangsPrefix . 'err_nothing_selected')));

        $mixedResult = true;
        $sType = $aData['type'];
        if(isset($aData['pmt-report']) && !empty($aData['pmt-report']))
            $mixedResult = $this->_oOrders->report($sType, $aData['orders']);
        else if(isset($aData['pmt-cancel']) && !empty($aData['pmt-cancel']))
            $mixedResult = $this->_oOrders->cancel($sType, $aData['orders']);
        else if(isset($aData['pmt-process']) && !empty($aData['pmt-process']) && $sType == BX_PMT_ORDERS_TYPE_PENDING)
            foreach($aData['orders'] as $iOrderId) {
                $sKey = 'order-data-' . $iOrderId;
                if(!isset($aData[$sKey]) || empty($aData[$sKey])) {
                    $mixedResult = array('code' => 4, 'message' => $this->_sLangsPrefix . 'err_empty_orders');
                    break;
                }
                $this->_oDb->updatePending($iOrderId, array(
                    'order' => $aData[$sKey],
                    'error_code' => 1,
                    'error_msg' => 'Manually processed'
                ));
                $this->_oCart->updateInfo((int)$iOrderId);
            } else
            $mixedResult = array('code' => 3, 'message' => $this->_sLangsPrefix . 'err_unknown');

        if(is_array($mixedResult))
            return $this->_onResultInline(array_merge($aResult, $mixedResult));

        return $this->_onResultInline(array_merge($aResult, array('code' => 0, 'message' => '')));
    }



    /**
     *
     * Payment Details Methods
     *
     */
    function getDetailsForm($iUserId = BX_PMT_EMPTY_ID)
    {
    	return $this->_oDetails->getFormBlock($iUserId);
    }
    function serviceGetCurrencyInfo()
    {
        return array(
            'sign' => $this->_oConfig->getCurrencySign(),
            'code' => $this->_oConfig->getCurrencyCode()
        );
    }
    function serviceGetAdmins()
    {
        $aIds = $this->_oDb->getAdminsIds();

        $aResult = array(
            array('key' => '', 'value' => _t($this->_sLangsPrefix . 'txt_select_one'))
        );
        foreach($aIds as $iId)
            $aResult[] = array(
                'key' => $iId,
                'value' => getNickName($iId)
            );

        return $aResult;
    }

    /**
     *
     * Admin Settings Methods
     *
     */
    function getSettingsForm($mixedResult)
    {
    	$sCategory = $this->_oConfig->getOptionsCategory();
    	$sLangsPrefix = $this->_oConfig->getLangsPrefix();

        $iId = (int)$this->_oDb->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name`='" . $sCategory . "'");
        if(empty($iId))
           return MsgBox(_t($sLangsPrefix . 'msg_no_results'));

        $oSettings = new BxDolAdminSettings($iId);
        $sResult = $oSettings->getForm();

        if($mixedResult !== true && !empty($mixedResult))
            $sResult = $mixedResult . $sResult;

        return $sResult;
    }
    function setSettings($aData)
    {
    	$sCategory = $this->_oConfig->getOptionsCategory();
    	$sLangsPrefix = $this->_oConfig->getLangsPrefix();

        $iId = (int)$this->_oDb->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name`='" . $sCategory . "'");
        if(empty($iId))
           return MsgBox(_t($sLangsPrefix . 'err_wrong_data'));

        $oSettings = new BxDolAdminSettings($iId);
        return $oSettings->saveChanges($_POST);
    }
	function serviceUpdateDependentModules($sModuleUri = 'all', $bInstall = true)
    {
    	$aModules = $sModuleUri == 'all' ? $this->_oDb->getModulesBy() : array($this->_oDb->getModuleByUri($sModuleUri));

        foreach($aModules as $aModule) {
			if(!BxDolRequest::serviceExists($aModule, 'get_payment_data')) 
				continue;

			$aData = BxDolService::call($aModule['uri'], 'get_payment_data');
			if($bInstall)
				$this->_oDb->insertData($aData);
			else
				$this->_oDb->deleteData($aData);
        }
    }


    /**
     *
     * Cart Processing Methods
     *
     */
    function getCartHistory($iVendorId)
    {
        $aTopMenu = array(
            'pmt-cart' => array('href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'cart/', 'title' => _t($this->_sLangsPrefix . 'btn_cart')),
            'pmt-cart-history' => array('href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'history/' . ($iVendorId == BX_PMT_ADMINISTRATOR_ID ? 'site/' : ''), 'title' => _t($this->_sLangsPrefix . 'btn_history'), 'active' => 1)
        );

        $sResult = $this->_oCart->getHistoryBlock($this->_iUserId, $iVendorId);
        return array($sResult, $aTopMenu, array(), true, 'getBlockCaptionMenu');
    }
    function getCartContent($mixedVendor = null)
    {
        if(!$this->isLogged())
            return MsgBox(_t($this->_sLangsPrefix . 'err_required_login'));

        $iVendorId = BX_PMT_EMPTY_ID;
        if(is_string($mixedVendor))
            $iVendorId = $this->_oDb->getVendorId($mixedVendor);
        else if(is_int($mixedVendor))
            $iVendorId = $mixedVendor;

        $aCartInfo = $this->_oCart->getInfo($this->_iUserId, $iVendorId);
        if($iVendorId == BX_PMT_EMPTY_ID)
            unset($aCartInfo[$this->_oConfig->getAdminId()]);

        $aTopMenu = array(
            'pmt-cart' => array('href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'cart/', 'title' => _t($this->_sLangsPrefix . 'btn_cart'), 'active' => 1),
            'pmt-cart-history' => array('href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'history/' . ($iVendorId == BX_PMT_ADMINISTRATOR_ID ? 'site/' : ''), 'title' => _t($this->_sLangsPrefix . 'btn_history'))
        );

        $sResult = !empty($aCartInfo) ? $this->_oTemplate->displayCartContent($aCartInfo, $iVendorId) : MsgBox(_t($this->_sLangsPrefix . 'msg_no_results'));
        return array($sResult, $aTopMenu, array(), true, 'getBlockCaptionMenu');
    }
    function serviceGetCartJs($bWrapped = true)
    {
        return $this->_oCart->getCartJs($bWrapped);
    }
    function serviceGetAddToCartJs($iVendorId, $mixedModuleId, $iItemId, $iItemCount, $bNeedRedirect = false, $bWrapped = true)
    {
        if(is_string($mixedModuleId)) {
            $aModuleInfo = $this->_oDb->getModuleByUri($mixedModuleId);
            $iModuleId = isset($aModuleInfo['id']) ? (int)$aModuleInfo['id'] : 0;
        } else
           $iModuleId = (int)$mixedModuleId;

        if(empty($iModuleId))
            return "";

        return $this->_oCart->getAddToCartJs($iVendorId, $iModuleId, $iItemId, $iItemCount, $bNeedRedirect, $bWrapped);
    }
    function serviceGetAddToCartLink($iVendorId, $mixedModuleId, $iItemId, $iItemCount, $bNeedRedirect = false)
    {
        if(is_string($mixedModuleId)) {
            $aModuleInfo = $this->_oDb->getModuleByUri($mixedModuleId);
            $iModuleId = isset($aModuleInfo['id']) ? (int)$aModuleInfo['id'] : 0;
        } else
           $iModuleId = (int)$mixedModuleId;

        if(empty($iModuleId))
            return "";

        return $this->_oCart->getAddToCartLink($iVendorId, $iModuleId, $iItemId, $iItemCount, $bNeedRedirect);
    }
    function serviceGetCartItemCount($iUserId, $iOldCount = 0)
    {
        if(!$this->isLogged())
            return array('count' => 0, 'messages' => array());

        $aInfo = $this->_oCart->getInfo($this->_iUserId);

        $iCount = 0;
        foreach($aInfo as $iVendorId => $aVendorCart)
            $iCount += $aVendorCart['items_count'];

        return array(
            'count' => $iCount,
            'messages' => array()
        );
    }
    function serviceGetCartItems()
    {
        if(!$this->isLogged())
            return MsgBox(_t($this->_sLangsPrefix . 'err_required_login'));

        $aInfo = $this->_oCart->getInfo($this->_iUserId);
        if(empty($aInfo))
            return MsgBox(_t($this->_sLangsPrefix . 'msg_no_results'));

        return $this->_oTemplate->displayToolbarSubmenu($aInfo);
    }
    function serviceGetCartUrl()
    {
    	if(!$this->isLogged())
            return '';

    	return BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'cart/';
    }
	function serviceGetCartItemDescriptor($iVendorId, $iModuleId, $iItemId, $iItemCount)
	{
		return $this->_oCart->getDescriptor($iVendorId, $iModuleId, $iItemId, $iItemCount);
	}
    function actionCartSubmit()
    {
    	if(!$this->isLogged()) {
    		$this->_oTemplate->getPageCodeError($this->_sLangsPrefix . 'err_required_login');
            return;
    	}

        $aData = &$_POST;

        if(isset($aData['pmt-delete']) && !empty($aData['items']))
            foreach($aData['items'] as $sItem) {
                list($iVendorId, $iModuleId, $iItemId, $iItemCount) = explode('_', $sItem);
                $this->_oCart->deleteFromCart($this->_iUserId, $iVendorId, $iModuleId, $iItemId);
            }
		else if(isset($aData['pmt-checkout']) && !empty($aData['items'])) {
			$sError = $this->initializeCheckout((int)$aData['vendor_id'], $aData['provider'], $aData['items']);
			if(!empty($sError)){
	    		$this->_oTemplate->getPageCodeError($sError, false);
	            return;
	    	}
		}

        header('Location: ' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'cart/');
        exit;
    }
    function actionAddToCart($iVendorId, $iModuleId, $iItemId, $iItemCount)
    {
        $aResult = $this->_oCart->addToCart($this->_iUserId, $iVendorId, $iModuleId, $iItemId, $iItemCount);

        header('Content-Type:text/javascript; charset=utf-8');
        return json_encode($aResult);
    }
    function serviceAddToCart($iVendorId, $iModuleId, $iItemId, $iItemCount)
    {
        return $this->_oCart->addToCart($this->_iUserId, $iVendorId, $iModuleId, $iItemId, $iItemCount);
    }
    /**
     * Isn't used yet.
     */
    function actionDeleteFromCart($iVendorId, $iModuleId, $iItemId)
    {
        $aResult = $this->_oCart->deleteFromCart($this->_iUserId, $iVendorId, $iModuleId, $iItemId);

        header('Content-Type:text/javascript; charset=utf-8');
        return json_encode($aResult);
    }
    /**
     * Isn't used yet.
     */
    function actionEmptyCart($iVendorId)
    {
        $aResult = $this->_oCart->deleteFromCart($this->_iUserId, $iVendorId);

        header('Content-Type:text/javascript; charset=utf-8');
        return json_encode($aResult);
    }



    /**
     *
     * Payment Processing Methods
     *
     */
	function serviceGetProviders($iVendorId, $sProvider = '')
	{
		$aResult = array();

		$aVendorProviders = $this->_oDb->getVendorInfoProviders($iVendorId, $sProvider);
		foreach($aVendorProviders as $aProvider) {
			$aProvider['caption_cart'] = _t($this->_sLangsPrefix . 'txt_cart_' . $aProvider['name']);

			$aResult[] = $aProvider;
		}

		return $aResult;
	}
	function serviceInitializeCheckout($iVendorId, $sProvider, $aItems = array())
	{
		if(!is_array($aItems))
			$aItems = array($aItems);

		return $this->initializeCheckout((int)$iVendorId, $sProvider, $aItems);
	}
    function initializeCheckout($iVendorId, $sProvider, $aItems = array())
    {
        if($iVendorId == BX_PMT_EMPTY_ID)
            return MsgBox(_t($this->_sLangsPrefix . 'err_unknown_vendor'));

        $aProvider = $this->_oDb->getVendorInfoProviders($iVendorId, $sProvider);
        $sClassPath = !empty($aProvider['class_file']) ? BX_DIRECTORY_PATH_ROOT . $aProvider['class_file'] : $this->_oConfig->getClassPath() . $aProvider['class_name'] . '.php';
        if(empty($aProvider) || !file_exists($sClassPath))
            return MsgBox(_t($this->_sLangsPrefix . 'err_incorrect_provider'));

        require_once($sClassPath);
        $oProvider = new $aProvider['class_name']($this->_oDb, $this->_oConfig, $aProvider);

        $aInfo = $this->_oCart->getInfo($this->_iUserId, $iVendorId, $aItems);
        if(empty($aInfo) || $aInfo['vendor_id'] == BX_PMT_EMPTY_ID || empty($aInfo['items']))
            return MsgBox(_t($this->_sLangsPrefix . 'err_empty_order'));

		/*
		 * Process FREE (price = 0) items for LOGGED IN members
		 * WITHOUT processing via payment provider.
		 */
		$bProcessedFree = false;
		foreach($aInfo['items'] as $iIndex => $aItem)
			if((int)$aInfo['client_id'] != 0 && (float)$aItem['price'] == 0) {
				$aItemInfo = BxDolService::call((int)$aItem['module_id'], 'register_cart_item', array($aInfo['client_id'], $aInfo['vendor_id'], $aItem['id'], $aItem['quantity'], $this->_oConfig->generateLicense()));
	            if(is_array($aItemInfo) && !empty($aItemInfo))
	            	$bProcessedFree = true;

	            $aInfo['items_count'] -= 1;
	            unset($aInfo['items'][$iIndex]);

	            $sCartItems = $this->_oDb->getCartItems($aInfo['client_id']);
	            $sCartItems = trim(preg_replace("'" . implode(BxPmtCart::$DESCRIPTOR_DIVIDER, array($aInfo['vendor_id'], $aItem['module_id'], $aItem['id'], $aItem['quantity'])) . ":?'", "", $sCartItems), ":");
	            $this->_oDb->setCartItems($aInfo['client_id'], $sCartItems);
			}

		if(empty($aInfo['items']))
            return MsgBox(_t($this->_sLangsPrefix . ($bProcessedFree ? 'inf_successfully_processed_free' : 'err_empty_order')));

        $iPendingId = $this->_oDb->insertPending($this->_iUserId, $aProvider['name'], $aInfo);
        if(empty($iPendingId))
            return MsgBox(_t($this->_sLangsPrefix . 'err_access_db'));

		/*
		 * Perform Join WITHOUT processing via payment provider
		 * if a client ISN'T logged in and has only ONE FREE item in the card.
		 */
		if((int)$aInfo['client_id'] == 0 && (int)$aInfo['items_count'] == 1) {
			reset($aInfo['items']);
			$aItem = current($aInfo['items']);

			if(!empty($aItem) && $aItem['price'] == 0) {
				$this->_oDb->updatePending($iPendingId, array(
		            'order' => $this->_oConfig->generateLicense(),
		            'error_code' => '1',
		            'error_msg' => ''
		        ));

				$this->performJoin($iPendingId);
			}
		}

		$sError = $oProvider->initializeCheckout($iPendingId, $aInfo);
		if(!empty($sError))
			return MsgBox($sError);

        return ''; 
    }
    function actionFinalizeCheckout($sProvider, $mixedVendorId = "")
    {
        $aData = &$_REQUEST;

        $aProvider = is_numeric($mixedVendorId) && (int)$mixedVendorId != BX_PMT_EMPTY_ID ? $this->_oDb->getVendorInfoProviders((int)$mixedVendorId, $sProvider) : $this->_oDb->getProviders($sProvider);
        $sClassPath = !empty($aProvider['class_file']) ? BX_DIRECTORY_PATH_ROOT . $aProvider['class_file'] : $this->_oConfig->getClassPath() . $aProvider['class_name'] . '.php';
        if(empty($aProvider) || !file_exists($sClassPath)) {
        	$this->_onResultPage(array('message' => _t($this->_sLangsPrefix . 'err_incorrect_provider')));
            exit;
        }

        require_once($sClassPath);
        $oProvider = new $aProvider['class_name']($this->_oDb, $this->_oConfig, $aProvider);

        $aResult = $oProvider->finalizeCheckout($aData);
        if((int)$aResult['code'] == 1) {
        	$aPending = $this->_oDb->getPending(array('type' => 'id', 'id' => (int)$aResult['pending_id']));

        	//--- Check "Pay Before Join" situation
        	if((int)$aPending['client_id'] == 0)
        		$this->performJoin((int)$aPending['id'], $aResult);

        	//--- Register payment for purchased items in associated modules 
            $this->_oCart->updateInfo($aPending);

            if($oProvider->needRedirect()) {
                header('Location: ' . $this->_oConfig->getReturnUrl());
                exit;
            }
        }

        $this->_onResultPage($aResult);
        exit;
    }
    function actionCheckoutFinished($sProvider, $mixedVendorId = "")
    {
    	$aProvider = is_numeric($mixedVendorId) && (int)$mixedVendorId != BX_PMT_EMPTY_ID ? $this->_oDb->getVendorInfoProviders((int)$mixedVendorId, $sProvider) : $this->_oDb->getProviders($sProvider);
    	$sClassPath = !empty($aProvider['class_file']) ? BX_DIRECTORY_PATH_ROOT . $aProvider['class_file'] : $this->_oConfig->getClassPath() . $aProvider['class_name'] . '.php';
        if(empty($aProvider) || !file_exists($sClassPath)) {
        	$this->_onResultPage(array('message' => _t($this->_sLangsPrefix . 'err_incorrect_provider')));
            exit;
        }

        require_once($sClassPath);
        $oProvider = new $aProvider['class_name']($this->_oDb, $this->_oConfig, $aProvider);
        $aResult = $oProvider->checkoutFinished();

        $this->_onResultPage($aResult);
        exit;
    }
    
    /**
     * Check transaction(s) in database which satisty all conditions.
     *
     * @param array $aConditions an array of pears('key' => 'value'). Available keys are the following:
     * a. order_id - internal order ID (string)
     * b. client_id - client's ID (integer)
     * c. seller_id - seller's ID (integer)
     * d. module_id - modules's where the purchased product is located. (integer)
     * e. item_id - item id in the database. (integer)
     * f. date - the date when the payment was processed(UNIXTIME STAMP)
     *
     * @return array of transactions. Each transaction has full info(client ID, seller ID, external transaction ID, date and so on)
     */
    function serviceGetTransactionsInfo($aConditions)
    {
        return $this->_oDb->getProcessed(array('type' => 'mixed', 'conditions' => $aConditions));
    }

	function serviceGetOption($sOption)
    {
		$sOptionPrefix = $this->_oConfig->getOptionsPrefix();
    	return $this->_oDb->getParam($sOptionPrefix . $sOption);
    }

    function serviceResponse($oAlert)
    {
    	if($oAlert->sUnit != 'profile' || !in_array($oAlert->sAction, array('join', 'delete')))
    		return;

		switch($oAlert->sAction) {
			case 'join':
				$this->_onProfileJoin($oAlert->iObject);
				break;

			case 'delete':
				$this->_oDb->onProfileDelete($oAlert->iObject);
				break;
		}
    }

    /**
     *
     * Join Methods
     *
     */
    function performJoin($iPendingId, $aPayment = array())
    {
		$oSession = BxDolSession::getInstance();
		$oSession->setValue($this->_sSessionKeyPending, (int)$iPendingId);

		if(!empty($aPayment['payer_name']) && !empty($aPayment['payer_email'])) {
			bx_import('BxDolEmailTemplates');
			$oEmailTemplates = new BxDolEmailTemplates();

			$aTemplate = $oEmailTemplates->parseTemplate($this->_sEmailTemplatesPrefix . 'paid_need_join', array(
				'RealName' => $aPayment['payer_name'],
				'JoinLink' => bx_append_url_params($this->_oConfig->getJoinUrl(), array($this->_sRequestKeyPending => (int)$iPendingId))
			));

			sendMail($aPayment['payer_email'], $aTemplate['Subject'], $aTemplate['Body'], 0, array(), 'html', false, true);
		}

		header('Location: ' . $this->_oConfig->getJoinUrl());
		exit;
	}
	function actionJoin()
    {
    	$oSession = BxDolSession::getInstance();
    	$iPendingId = (int)$oSession->getValue($this->_sSessionKeyPending);

    	if(empty($iPendingId) && bx_get($this->_sRequestKeyPending) !== false) {
    		$iPendingId = (int)bx_get($this->_sRequestKeyPending);

    		$oSession->setValue($this->_sSessionKeyPending, $iPendingId);
    	}

		if(empty($iPendingId)) {
			$this->_oTemplate->getPageCodeError($this->_sLangsPrefix . 'err_not_allowed');
			return;
		}

		$aPending = $this->_oDb->getPending(array('type' => 'id', 'id' => $iPendingId));
	    if(empty($aPending['order']) || (int)$aPending['error_code'] != 1) {
			$this->_oTemplate->getPageCodeError($this->_sLangsPrefix . 'err_not_processed');
			return;
		}

		if((int)$aPending['processed'] == 1) {
			$this->_oTemplate->getPageCodeError($this->_sLangsPrefix . 'err_already_processed');
			return;
		}

		//--- 'System' -> 'Join after Payment' for Alerts Engine ---//
		$bOverride = false;
		$sOverrideError = '';

		bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('system', 'join_after_payment', 0, 0, array('override' => &$bOverride, 'override_error' => &$sOverrideError));
        $oAlert->alert();

        if($bOverride)
        	return;
		//--- 'System' -> 'Join after Payment' for Alerts Engine ---//

    	bx_import('ProfileFields', $this->_aModule);
    	$oProfileFields = new BxPmtProfileFields(1, $this);

    	bx_import('BxDolJoinProcessor');
    	$oJoin = new BxDolJoinProcessor(array('profile_fields' => $oProfileFields));

    	$sBlockCaption = _t($this->_sLangsPrefix . 'bcpt_join');
    	$sBlockContent = (!empty($sOverrideError) ? MsgBox(_t($sOverrideError)) : '') . $oJoin->process();

        $aParams = array(
        	'index' => 1,
        	'css' => array('join.css'),
        	'js' => array('join.js', 'jquery.form.min.js'),
            'title' => array(
                'page' => _t($this->_sLangsPrefix . 'pcpt_join') 
            ),
            'content' => array(
                'page_main_code' => DesignBoxContent($sBlockCaption, $sBlockContent, 11)
            )
        );
        $this->_oTemplate->getPageCode($aParams);
    }



    /**
     *
     * Private Methods of Common Usage
     *
     */
    function _onProfileJoin($iProfileId)
    {
    	$oSession = BxDolSession::getInstance();
		$iPendingId = (int)$oSession->getValue($this->_sSessionKeyPending);

        if(empty($iProfileId) || empty($iPendingId))
        	return;
 
		$aPending = $this->_oDb->getPending(array('type' => 'id', 'id' => $iPendingId));
		if(empty($aPending) || (isset($aPending['client_id']) && (int)$aPending['client_id'] != 0))
			return;

		if(!$this->_oDb->updatePending($iPendingId, array('client_id' => $iProfileId)))
			return;

		$this->_oCart->updateInfo($iPendingId);

		$oSession->unsetValue($this->_sSessionKeyPending);
    }
    function _onResultAlert($aResult)
    {
        echo $this->_oTemplate->parseHtmlByTemplateName('on_result', array('message' => $aResult['message']));
    }
    function _onResultInline($aResult)
    {

        return $this->_oTemplate->parseHtmlByTemplateName('on_result_inline', array(
            'js_object' => $aResult['js_object'],
            'params' => json_encode(array(
                'code' => $aResult['code'],
                'message' => MsgBox(_t($aResult['message'])),
                'parent_id' => $aResult['parent_id']
            ))
        ));
    }
    function _onResultPage($aResult)
    {
        global $_page;
        global $_page_cont;

        $_page['name_index'] = isset($aResult['page_index']) ? (int)$aResult['page_index'] : 0;
        $_page['header'] = !empty($aResult['page_caption']) ? $aResult['page_caption'] : _t($this->_sLangsPrefix . 'pcpt_payment_result');
        $_page['header_text'] = !empty($aResult['block_caption']) ? $aResult['block_caption'] : _t($this->_sLangsPrefix . 'bcpt_payment_result');

        $sContent = $this->_oTemplate->parseHtmlByName('default_padding.html', array(
        	'content' => !empty($aResult['message']) ? MsgBox($aResult['message']) : ''
        ));

        $_page_cont[$_page['name_index']]['page_main_code'] = $sContent;
        PageCode($GLOBALS['oSysTemplate']);
    }
}
