<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxPmtProvider
{
    var $_oDb;
    var $_oConfig;

    var $_iId;
    var $_sName;
    var $_sCaption;
    var $_sPrefix;
    var $_aOptions;
    var $_bRedirectOnResult;

    /**
     * Constructor
     */
    function __construct($oDb, $oConfig, $aConfig)
    {
        $this->_oDb = $oDb;
        $this->_oConfig = $oConfig;

        $this->_iId = (int)$aConfig['id'];
        $this->_sName = $aConfig['name'];
        $this->_sCaption = _t($aConfig['caption']);
        $this->_sPrefix = $aConfig['option_prefix'];
        $this->_aOptions = !empty($aConfig['options']) ? $aConfig['options'] : array();
        $this->_bRedirectOnResult = false;
    }
    function initializeCheckout($iPendingId, $aCartInfo, $bRecurring = false, $iRecurringDays = 0) {}
    function finalizeCheckout(&$aData) {}
    function checkoutFinished() {}

    /**
     * Is used on success only.
     */
	function needRedirect()
    {
        return $this->_bRedirectOnResult;
    }

    protected function getOptionsByPending($iPendingId)
    {
        $aPending = $this->_oDb->getPending(array(
            'type' => 'id',
            'id' => (int)$iPendingId
        ));
        return $this->_oDb->getOptions((int)$aPending['seller_id'], $this->_iId);
    }
    protected function getOption($sName)
    {
        return isset($this->_aOptions[$this->_sPrefix . $sName]) ? $this->_aOptions[$this->_sPrefix . $sName]['value'] : "";
    }
}
