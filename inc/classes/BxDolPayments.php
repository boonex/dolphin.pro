<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPaymentsQuery');

/**
 * Payments objects.
 */
class BxDolPayments
{
	protected $_oDb;

	protected $_aObjects;
	protected $_sActiveUri;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_oDb = new BxDolPaymentsQuery();

        $this->_aObjects = $this->_oDb->getObjects();
        $this->_sActiveUri = getParam('sys_default_payment');
    }

	static public function getInstance()
    {
        if(!isset($GLOBALS['bxDolClasses']['BxDolPayments']))
        	$GLOBALS['bxDolClasses']['BxDolPayments'] = new BxDolPayments();

		return $GLOBALS['bxDolClasses']['BxDolPayments'];
    }

	public function setActiveUri($sActive)
    {
		$this->_sActiveUri = $sActive;
    }

    public function getActiveUri()
    {
    	return $this->_sActiveUri;
    }

    public function isActive()
    {
    	if(empty($this->_sActiveUri))
    		return false;

		bx_import('BxDolModuleDb');
    	$oModuleDb = new BxDolModuleDb();
    	if(!$oModuleDb->isModule($this->_sActiveUri))
    		return false;

    	return true;
    }

	public function getPayments()
    {
        $aPayments = array(
			'' => _t('_sys_select_one')
        );
		foreach($this->_aObjects as $aObject) {
			if(empty($aObject) || !is_array($aObject))
				continue;

			$aPayments[$aObject['uri']] = _t($aObject['title']);
		}

        return $aPayments;
    }

    public function getProviders($iVendorId, $sProvider = '')
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_providers'))
    		return array();

    	$aSrvParams = array($iVendorId, $sProvider);
        return BxDolService::call($this->_sActiveUri, 'get_providers', $aSrvParams);
    }

	public function getOption($sOption)
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_option'))
    		return '';

    	return BxDolService::call($this->_sActiveUri, 'get_option', array($sOption));
    }

    public function getOrdersUrl()
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_orders_url'))
    		return '';

    	return BxDolService::call($this->_sActiveUri, 'get_orders_url');
    }

    public function getCartUrl()
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_cart_url'))
    		return '';

    	return BxDolService::call($this->_sActiveUri, 'get_cart_url');
    }

    public function getCartItems()
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_cart_items'))
    		return MsgBox(_t('_Empty'));

    	return BxDolService::call($this->_sActiveUri, 'get_cart_items');
    }

    public function getCartItemCount($iUserId, $iOldCount = 0)
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_cart_item_count'))
    		return array('count' => 0, 'messages' => array());

		$aSrvParams = array($iUserId, $iOldCount);
    	return BxDolService::call($this->_sActiveUri, 'get_cart_item_count', $aSrvParams);
    }

    public function getCartItemDescriptor($iVendorId, $iModuleId, $iItemId, $iItemCount)
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_cart_item_descriptor'))
    		return '';

    	$aSrvParams = array($iVendorId, $iModuleId, $iItemId, $iItemCount);
		return BxDolService::call($this->_sActiveUri, 'get_cart_item_descriptor', $aSrvParams);
    }
    
    public function getCartJs($bWrapped = true)
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_cart_js'))
			return '';

		$aSrvParams = array($bWrapped);
		return BxDolService::call($this->_sActiveUri, 'get_cart_js', $aSrvParams);
    }

    public function getAddToCartJs($iVendorId, $mixedModuleId, $iItemId, $iItemCount, $bNeedRedirect = false, $bWrapped = true)
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_add_to_cart_js'))
			return array();

		$aSrvParams = array($iVendorId, $mixedModuleId, $iItemId, $iItemCount, $bNeedRedirect, $bWrapped);
		return BxDolService::call($this->_sActiveUri, 'get_add_to_cart_js', $aSrvParams);
    }

    public function getAddToCartLink($iVendorId, $mixedModuleId, $iItemId, $iItemCount, $bNeedRedirect = false)
    {
		if(!BxDolRequest::serviceExists($this->_sActiveUri, 'get_add_to_cart_link'))
			return '';

		$aSrvParams = array($iVendorId, $mixedModuleId, $iItemId, $iItemCount, $bNeedRedirect);
		return BxDolService::call($this->_sActiveUri, 'get_add_to_cart_link', $aSrvParams);
    }

    public function initializeCheckout($iVendorId, $sProvider, $aItems = array())
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'initialize_checkout'))
			return '';

		$aSrvParams = array($iVendorId, $sProvider, $aItems);
		return BxDolService::call($this->_sActiveUri, 'initialize_checkout', $aSrvParams);
    }

	public function prolongSubscription($sOrderId)
    {
    	if(!BxDolRequest::serviceExists($this->_sActiveUri, 'prolong_subscription'))
			return '';

		$aSrvParams = array($sOrderId);
		return BxDolService::call($this->_sActiveUri, 'prolong_subscription', $aSrvParams);
    }
}
