<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplCmtsView');

class BxSitesCmts extends BxTemplCmtsView
{
	var $_oModule;

    /**
     * Constructor
     */
    function __construct($sSystem, $iId)
    {
        parent::__construct($sSystem, $iId);

        $this->_oModule = BxDolModule::getInstance('BxSitesModule');
    }

	function getBaseUrl()
    {
    	$aEntry = $this->_oModule->_oDb->getSiteById($this->getId());
    	if(empty($aEntry) || !is_array($aEntry))
    		return '';

    	return BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'view/' . $aEntry['entryUri']; 
    }

    /**
     * get full comments block with initializations
     */
    function getCommentsShort($sType)
    {
        return array(
            'cmt_actions' => $this->getActions(0, $sType),
            'cmt_object' => $this->getId(),
            'cmt_addon' => $this->getCmtsInit()
        );
    }
}
