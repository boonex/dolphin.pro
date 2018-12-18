<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplCmtsView');

class BxAdsCmts extends BxTemplCmtsView
{
	var $_oModule;

    /**
     * Constructor
     */
    function __construct($sSystem, $iId)
    {
        parent::__construct($sSystem, $iId);

        $this->_oModule = BxDolModule::getInstance('BxAdsModule');
    }

    function getBaseUrl()
    {
    	$aEntry = $this->_oModule->_oDb->getAdInfo($this->getId());
    	if(empty($aEntry) || !is_array($aEntry))
    		return '';

    	return $this->_oModule->genUrl($aEntry['ID'], $aEntry['EntryUri'], 'entry'); 
    }

    function isPostReplyAllowed($isPerformAction = false)
    {
        if (!parent::isPostReplyAllowed($isPerformAction))
            return false;

        $oMain = BxDolModule::getInstance('BxAdsModule');
        $aAdPost = $oMain->_oDb->getAdInfo($this->getId());
        return $oMain->isAllowedComments($aAdPost);
    }
}
