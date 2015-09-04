<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConfig');

class BxMbpConfig extends BxDolConfig
{
    var $_oDb;
    var $_sCurrencySign;
    var $_sCurrencyCode;
    var $_sIconsFolder;

	var $_aJsClasses;
    var $_aJsObjects;
    var $_sAnimationEffect;
    var $_iAnimationSpeed;

    /**
     * Constructor
     */
    function BxMbpConfig($aModule)
    {
        parent::BxDolConfig($aModule);

        $this->_oDb = null;
        $this->_sIconsFolder = 'media/images/membership/';
        
        $this->_aJsClasses = array(
        	'join' => 'BxMbpJoin',
        );
        $this->_aJsObjects = array(
        	'join' => 'oMbpJoin',
        );

		$this->_sAnimationEffect = 'fade';
	    $this->_iAnimationSpeed = 'slow';
    }
    function init(&$oDb)
    {
        $this->_oDb = &$oDb;

        $this->_sCurrencySign = $this->_oDb->getParam('pmt_default_currency_sign');
        $this->_sCurrencyCode = $this->_oDb->getParam('pmt_default_currency_code');
    }

    function getCurrencySign()
    {
        return $this->_sCurrencySign;
    }
    function getCurrencyCode()
    {
        return $this->_sCurrencyCode;
    }
    function getIconsUrl()
    {
        return BX_DOL_URL_ROOT . $this->_sIconsFolder;
    }
    function getIconsPath()
    {
        return BX_DIRECTORY_PATH_ROOT . $this->_sIconsFolder;
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

        return isset($this->_aJsObjects[$sType]) ? $this->_aJsObjects[$sType] : '';
    }
	function getAnimationEffect()
	{
	    return $this->_sAnimationEffect;
	}
	function getAnimationSpeed()
	{
	    return $this->_iAnimationSpeed;
	}
}
