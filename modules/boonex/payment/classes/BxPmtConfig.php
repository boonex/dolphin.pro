<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConfig');

class BxPmtConfig extends BxDolConfig
{
    var $_oDb;

    var $_iSiteId;
    var $_iAdminId;
    var $_sAdminUsername;
    var $_sCurrencySign;
    var $_sCurrencyCode;
    var $_sJoinUrl;
    var $_sReturnUrl;
    var $_sDataReturnUrl;
    var $_iOrdersPerPage;
    var $_iHistoryPerPage;
	var $_sDateFormatOrders;

    var $_sAnimationEffect;
    var $_iAnimationSpeed;

    var $_aPrefixes;
    var $_aJsClasses;
    var $_aJsObjects;

    var $_sOptionsCategory;

    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_iAdminId = BX_PMT_ADMINISTRATOR_ID;
        $this->_sAdminUsername = BX_PMT_ADMINISTRATOR_USERNAME;

        $this->_sJoinUrl = $this->getBaseUri() . 'join/';
        $this->_sReturnUrl = $this->getBaseUri() . 'cart/';
        $this->_sDataReturnUrl = $this->getBaseUri() . 'act_finalize_checkout/';

        $this->_iOrdersPerPage = 10;
        $this->_iHistoryPerPage = 10;

        $this->_sDateFormatOrders = getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB);

        $this->_sAnimationEffect = 'fade';
        $this->_iAnimationSpeed = 'slow';

		$this->_aPrefixes = array(
			'general' => 'bx_pmt_',
        	'langs' => '_payment_',
        	'options' => 'pmt_'
        );
        $this->_aJsClasses = array(
        	'cart' => 'BxPmtCart',
        	'orders' => 'BxPmtOrders'
        );
        $this->_aJsObjects = array(
        	'cart' => 'oPmtCart',
        	'orders' => 'oPmtOrders'
        );

        $this->_sOptionsCategory = 'Payment';
    }
    function init(&$oDb)
    {
        $this->_oDb = &$oDb;

        $sOptionPrefix = $this->getOptionsPrefix();
        $this->_iSiteId = (int)$this->_oDb->getParam($sOptionPrefix . 'site_admin');
        $this->_sCurrencySign = $this->_oDb->getParam($sOptionPrefix . 'default_currency_sign');
        $this->_sCurrencyCode = $this->_oDb->getParam($sOptionPrefix . 'default_currency_code');
    }
    function getAdminId()
    {
        return $this->_iAdminId;
    }
    function getAdminUsername()
    {
        return $this->_sAdminUsername;
    }
    function getSiteId()
    {
        if(empty($this->_iSiteId))
            return $this->_oDb->getFirstAdminId();

        return $this->_iSiteId;
    }
    function getCurrencySign()
    {
        return $this->_sCurrencySign;
    }
    function getCurrencyCode()
    {
        return $this->_sCurrencyCode;
    }
	function getJoinUrl()
    {
        return BX_DOL_URL_ROOT . $this->_sJoinUrl;
    }
    function getReturnUrl($bSsl = false)
    {
    	$sResult = BX_DOL_URL_ROOT . $this->_sReturnUrl;
    	if($bSsl && strpos($sResult, 'https://') === false)
    		$sResult = 'https://' . bx_ltrim_str($sResult, 'http://');

        return $sResult;
    }
    function getDataReturnUrl($bSsl = false)
    {
    	$sResult = BX_DOL_URL_ROOT . $this->_sDataReturnUrl;
    	if($bSsl && strpos($sResult, 'https://') === false)
    		$sResult = 'https://' . bx_ltrim_str($sResult, 'http://');

        return $sResult;
    }
    function getDateFormat($sType)
    {
        $sResult = "";

        switch($sType) {
            case 'orders':
                $sResult = $this->_sDateFormatOrders;
                break;
        }

        return $sResult;
    }
    function getPerPage($sType)
    {
        $iResult = 0;

        switch($sType) {
            case 'orders':
                $iResult = $this->_iOrdersPerPage;
                break;
            case 'history':
                $iResult = $this->_iHistoryPerPage;
                break;
        }

        return $iResult;
    }
    function getAnimationEffect()
    {
        return $this->_sAnimationEffect;
    }
    function getAnimationSpeed()
    {
        return $this->_iAnimationSpeed;
    }
	function getGeneralPrefix()
	{
		return $this->_aPrefixes['general'];
	}
	function getEmailTemplatesPrefix()
	{
		return $this->_aPrefixes['general'];
	}
	function getLangsPrefix()
	{
		return $this->_aPrefixes['langs'];
	}
	function getOptionsPrefix()
	{
		return $this->_aPrefixes['options'];
	}
	function getOptionsCategory()
	{
		return $this->_sOptionsCategory;
	}
	function getJsClass($sType = '')
    {
        if(empty($sType))
            return $this->_aJsClasses;

        return isset($this->_aJsClasses[$sType]) ? $this->_aJsClasses[$sType] : '';
    }
    function getJsObject($sType = '')
    {
    	if(empty($sType))
            return $this->_aJsClasses;

        $sResult = '';
        switch($sType) {
            case 'cart':
                $sResult = $this->_aJsObjects['cart'];
                break;
            case 'orders':
            case 'history':
                $sResult = $this->_aJsObjects['orders'];
                break;
        }

        return $sResult;
    }

	function generateLicense()
    {
        list($fMilliSec, $iSec) = explode(' ', microtime());
        $fSeed = (float)$iSec + ((float)$fMilliSec * 100000);
        srand($fSeed);

        $sResult = '';
        for($i=0; $i < 16; ++$i) {
            switch(rand(1,2)) {
                case 1:
                    $c = chr(rand(ord('A'),ord('Z')));
                    break;
                case 2:
                    $c = chr(rand(ord('0'),ord('9')));
                    break;
            }
            $sResult .= $c;
        }

        return $sResult;
    }
}
