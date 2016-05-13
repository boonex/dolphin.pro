<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');

/**
 * Membership module by BoonEx
 *
 * This module is needed to integrate the default Membership/ACL engine with Payment module.
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
 * Get the content of the link for Dashboard item in member menu.
 * @see BxMbpModule::serviceGetMemberMenuLink
 * BxDolService::call('membership', 'get_member_menu_link', array($iMemberId));
 * @note is needed for internal usage.
 *
 * Get single item. Is used in Shopping Cart to get one product by specified id.
 * @see BxMbpModule::serviceGetCartItem
 * BxDolService::call('membership', 'get_cart_item', array($iClientId, $iItemId));
 * @note is needed for internal usage.
 *
 * Get items. Is used in Orders Administration to get all products of the requested seller(vendor).
 * @see BxMbpModule::serviceGetItems
 * BxDolService::call('membership', 'get_items', array($iVendorId));
 * @note is needed for internal usage.
 *
 * Register purchased membership level.
 * @see BxMbpModule::serviceRegisterCartItem
 * BxDolService::call('membership', 'register_cart_item', array($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrderId));
 * @note is needed for internal usage.
 *
 * Unregister the membership level purchased earlier.
 * @see BxMbpModule::serviceUnregisterCartItem
 * BxDolService::call('membership', 'unregister_cart_item', array($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrderId));
 * @note the service does nothing because membership level cannot be canceled manually. It should expire by itself.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxMbpModule extends BxDolModule
{
    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_oConfig->init($this->_oDb);
    }

	/**
     * Admin Settings Methods
     */
    function getSettingsForm($mixedResult)
    {
        $sUri = $this->_oConfig->getUri();

        $iId = (int)$this->_oDb->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name`='Membership'");
        if(empty($iId))
            return MsgBox('_membership_txt_empty');

        bx_import('BxDolAdminSettings');
        $oSettings = new BxDolAdminSettings($iId, BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'admin');
        $sResult = $oSettings->getForm();

        if($mixedResult !== true && !empty($mixedResult))
            $sResult = $mixedResult . $sResult;

        return $sResult;
    }
    function setSettings($aData)
    {
        $sUri = $this->_oConfig->getUri();

        $iId = (int)$this->_oDb->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name`='Membership'");
        if(empty($iId))
           return MsgBox(_t('_membership_txt_empty'));

        bx_import('BxDolAdminSettings');
        $oSettings = new BxDolAdminSettings($iId);
        return $oSettings->saveChanges($_POST);
    }

    /**
     * Page blocks' methods
     */
    function getCurrentLevelBlock()
    {
        $aUserLevel = getMemberMembershipInfo($this->getUserId());
        return $this->_oTemplate->displayCurrentLevel($aUserLevel);
    }
    function getAvailableLevelsBlock()
    {
        if (!$this->isLogged())
            return array(MsgBox(_t('_membership_err_required_login')));

        if (!isProfileActive())
            return array(MsgBox(_t('_membership_err_not_active')));

        $aMembership = $this->_oDb->getMembershipsBy(array('type' => 'price_all'));
        if(empty($aMembership))
            return array(MsgBox(_t('_membership_txt_empty')));

        return $this->_oTemplate->displayAvailableLevels($aMembership);
    }
	function getSelectLevelBlock($bDynamic = false)
    {
    	if(!$this->_oConfig->isDisableFreeJoin())
			return '';

        $aMembership = $this->_oDb->getMembershipsBy(array('type' => 'price_all', 'include_standard' => $this->_oConfig->isStandardOnPaidJoin()));
        if(empty($aMembership))
            return array(MsgBox(_t('_membership_err_no_payment_options')));

        return $this->_oTemplate->displaySelectLevelBlock($aMembership, $bDynamic);
    }

    /**
     * Action Methods
     */
    function actionIndex()
    {
    	if(!isLogged()) {
    		$this->_oTemplate->getPageCodeError('_membership_err_required_login');
    		return;
    	}

    	bx_import('PageMy', $this->_aModule);
    	$oPage = new BxMbpPageMy($this);

    	$aParams = array(
    		'index' => 1,
    		'css' => array('explanation.css'),
            'title' => array(
                'page' => _t('_membership_pcaption_membership')
            ),
            'content' => array(
                'page_main_code' => $oPage->getCode()
            )
        );
        $this->_oTemplate->getPageCode($aParams);
    }

	function actionJoin()
    {
    	bx_import('PageJoin', $this->_aModule);
    	$oPage = new BxMbpPageJoin($this);

    	$aParams = array(
    		'index' => 1,
    		'css' => array('explanation.css'),
            'title' => array(
                'page' => _t('_membership_pcaption_join')
            ),
            'content' => array(
                'page_main_code' => $oPage->getCode()
            )
        );
        $this->_oTemplate->getPageCode($aParams);
    }

    function actionJoinSubmit()
    {
    	if($this->_oConfig->isCaptchaOnPaidJoin()) {
    		bx_import('BxDolCaptcha');
	        $oCaptcha = BxDolCaptcha::getObjectInstance();
	        if(!$oCaptcha) {
	        	$this->_oTemplate->getPageCodeError('_sys_txt_captcha_not_available');
    			return;
	        }

	        if(!$oCaptcha->check ()) {
	        	$this->_oTemplate->getPageCodeError('_Captcha check failed');
    			return;
	        }
    	} 

    	$sDescriptor = bx_get('descriptor');
    	if($sDescriptor === false) {
    		$this->_oTemplate->getPageCodeError('_membership_err_need_select_level');
    		return;
    	}
    	else if($sDescriptor == $this->_oConfig->getStandardDescriptor()) {
    		header('Location: ' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'join_form');
    		exit;
    	}

    	$sProvider = bx_get('provider');
    	if($sProvider === false) {
    		$this->_oTemplate->getPageCodeError('_membership_err_need_select_provider');
    		return;
    	}

    	$sRedirect = BX_DOL_URL_ROOT . 'join.php';

    	bx_import('BxDolPayments');
    	$aResult = BxDolPayments::getInstance()->initializeCheckout(0, $sProvider, $sDescriptor);
    	if(is_array($aResult) && !empty($aResult['redirect']))
			$sRedirect = $aResult['redirect'];

    	header('Location: ' . $sRedirect);
    	exit;
    }

    function actionJoinForm()
    {
    	if(!$this->_oConfig->isStandardOnPaidJoin()) {
    		$this->_oTemplate->getPageCodeError('_membership_err_access_denied');
    		return;
    	}

    	bx_import('ProfileFields', $this->_aModule);
    	$oProfileFields = new BxMbpProfileFields(1, $this);

    	bx_import('BxDolJoinProcessor');
    	$oJoin = new BxDolJoinProcessor(array('profile_fields' => $oProfileFields));

    	$aParams = array(
    		'index' => 1,
            'title' => array(
                'page' => _t('_membership_pcaption_join')
            ),
            'content' => array(
                'page_main_code' => DesignBoxContent(_t('_membership_bcaption_join'), $oJoin->process(), 11)
            )
        );
        $this->_oTemplate->getPageCode($aParams);
    }

	function actionAdmin()
    {
        $GLOBALS['iAdminPage'] = 1;
        require_once(BX_DIRECTORY_PATH_INC . 'admin_design.inc.php');

        $sUri = $this->_oConfig->getUri();

        check_logged();
        if(!@isAdmin()) {
            send_headers_page_changed();
            login_form("", 1);
            exit;
        }

        //--- Process actions ---//
        $mixedResultSettings = '';
        if(isset($_POST['save']) && isset($_POST['cat'])) {
            $mixedResultSettings = $this->setSettings($_POST);
        }
        //--- Process actions ---//

        $sContent = DesignBoxAdmin(_t('_' . $sUri . '_bcaption_settings'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $this->getSettingsForm($mixedResultSettings))));

        $aParams = array(
            'title' => array(
                'page' => _t('_membership_pcaption_admin')
            ),
            'content' => array(
                'page_main_code' => $sContent
            )
        );
        $this->_oTemplate->getPageCodeAdmin($aParams);
    }

	/**
     * System Methods
     */
    function serviceIsDisableFreeJoin()
    {
    	return $this->_oConfig->isDisableFreeJoin();
    }
    function serviceGetUpgradeUrl()
    {
        return BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'index/';
    }

    function serviceGetMemberMenuLink($iMemberId)
    {
        $sTitle = _t('_membership_mmenu_item_membership');

        $aLinkInfo = array(
            'item_img_src' => 'star',
            'item_img_alt' => $sTitle,
            'item_link' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'index',
            'item_onclick' => '',
            'item_title' => $sTitle,
            'extra_info' => 0,
        );

        $oMemberMenu = bx_instance('BxDolMemberMenu');
        return $oMemberMenu->getGetExtraMenuLink($aLinkInfo);
    }

    function serviceSelectLevelBlock()
    {
    	return $this->getSelectLevelBlock();
    }
	/**
	 * Integration with Payment module
	 */
	function serviceGetPaymentData()
    {
        return $this->_aModule;
    }

    /**
     * Is used in Orders Administration to get all products of the requested seller(vendor).
     *
     * @param  integer $iVendorId seller ID.
     * @return array   of products.
     */
    function serviceGetItems($iVendorId)
    {
        $aItems = $this->_oDb->getMembershipsBy(array('type' => 'price_all'));

        $aResult = array();
        foreach($aItems as $aItem)
            $aResult[] = array(
                'id' => $aItem['price_id'],
                'vendor_id' => 0,
                'title' => $aItem['mem_name'] . ' ' . _t('_membership_txt_on_N_days', $aItem['price_days']),
                'description' => $aItem['mem_description'],
                'url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'index',
                'price' => $aItem['price_amount'],
            	'duration' => $aItem['price_days']
            );
        return $aResult;
    }
    /**
     * Is used in Shopping Cart to get one product by specified id.
     *
     * @param  integer $iClientId client's ID.
     * @param  integer $iItemId   product's ID.
     * @return array   with product description.
     */
    function serviceGetCartItem($iClientId, $iItemId)
    {
        return $this->_getCartItem($iClientId, $iItemId);
    }
    /**
     * Register purchased product.
     *
     * @param  integer $iClientId  client's ID.
     * @param  integer $iSellerId  seller's ID.
     * @param  integer $iItemId    product's ID.
     * @param  integer $iItemCount product count purchased at the same time.
     * @param  string  $sOrderId   internal order ID generated for the payment.
     * @return array   with product description.
     */
    function serviceRegisterCartItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrderId)
    {
        $bResult = true;
        for($i=0; $i<$iItemCount; $i++)
            $bResult &= buyMembership($iClientId, $iItemId, $sOrderId);

        return $bResult ? $this->_getCartItem($iClientId, $iItemId) : false;
    }
    /**
     * Unregister the product purchased earlier.
     *
     * @param integer $iClientId  client's ID.
     * @param integer $iSellerId  seller's ID.
     * @param integer $iItemId    product's ID.
     * @param integer $iItemCount product count.
     * @param string  $sOrderId   internal order ID.
     */
    function serviceUnregisterCartItem($iClientId, $iSellerId, $iItemId, $iItemCount, $sOrderId) {}

    /**
     * Check whether prolongation is available for membership levels marked as 'Expiring'
     */
    function serviceProlongSubscriptions()
    {
    	bx_import('BxDolPayments');
    	$oPayment = BxDolPayments::getInstance();

    	$aMemberships = $this->_oDb->getExpiringMemberships();
    	foreach($aMemberships as $aMembership) {
    		$aResult = $oPayment->prolongSubscription($aMembership['transaction_id']);
    		if(!isset($aResult['code']) || (int)$aResult['code'] != 0)
    			continue;

    		unmarkMembershipAsExpiring($aMembership['member_id'], $aMembership['level_id'], $aMembership['transaction_id']);
    	}
    }

    function _getCartItem($iClientId, $iItemId)
    {
        $aItem = $this->_oDb->getMembershipsBy(array('type' => 'price_id', 'id' => $iItemId));

        if(empty($aItem) || !is_array($aItem))
           return array();

        return array(
			'id' => $iItemId,
			'title' => $aItem['mem_name'] . ' ' . _t('_membership_txt_on_N_days', $aItem['price_days']),
			'description' => $aItem['mem_description'],
			'url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'index',
			'price' => $aItem['price_amount'],
			'duration' => $aItem['price_days']
        );
    }
}
