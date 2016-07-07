<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

class BxSctrConfig extends BxDolConfig
{
    var $_oDb;
    var $_oSession;
    var $_bEnabled;
    var $_sSessionKeyOpen;
    var $_sSessionKeyData;
    var $_sSessionDataDivider;
	var $_aJsClasses;
    var $_aJsObjects;

    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_sSessionKeyOpen = 'bx_sctr_open';
        $this->_sSessionKeyData = 'bx_sctr_data';
        $this->_sSessionDataDivider = '#';
        $this->_aJsClasses = array('main' => 'BxSctrMain');
        $this->_aJsObjects = array('main' => 'oBxSctrMain');
    }

    function init(&$oDb)
    {
        $this->_oDb = &$oDb;
        $this->_oSession = BxDolSession::getInstance();

        $this->_bEnabled = getParam('bx_sctr_enable') == 'on';
    }

    function isEnabled()
    {
    	global $oSysTemplate;
		return $this->_bEnabled && in_array($oSysTemplate->getCode(), array('uni', 'alt', 'evo'));
    }

	function getOpenKey()
    {
		return $this->_sSessionKeyOpen;
    }

    function isOpen()
    {
    	return (int)$this->_oSession->getValue($this->getOpenKey()) != 0;
    }

    function doOpen()
    {
    	$this->_oSession->setValue($this->getOpenKey(), 1);
    }

    function doClose()
    {
    	$this->_oSession->unsetValue($this->getOpenKey());
    	$this->cancelSession();
    }

	function getSessionKey()
    {
		return $this->_sSessionKeyData;
    }

    function isSession()
    {
		$sData = $this->_oSession->getValue($this->getSessionKey());
		return !empty($sData);
    }

    function getSessionData()
    {
		$sData = $this->_oSession->getValue($this->getSessionKey());
		return explode($this->_sSessionDataDivider, $sData);
    }

	function setSessionData($aData)
    {
    	$sData = implode($this->_sSessionDataDivider, $aData);
		$this->_oSession->setValue($this->getSessionKey(), $sData);
    }

	function cancelSession()
	{
	    $this->_oSession->unsetValue($this->getSessionKey());
	}

	function getJsClass($sType = 'main')
    {
        if(empty($sType))
            return $this->_aJsClasses;

        return $this->_aJsClasses[$sType];
    }

    function getJsObject($sType = 'main')
    {
        if(empty($sType))
            return $this->_aJsObjects;

        return $this->_aJsObjects[$sType];
    }
}
