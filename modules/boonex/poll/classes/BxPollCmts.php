<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplCmtsView');

class BxPollCmts extends BxTemplCmtsView
{
	var $_oModule;

    /**
     * Constructor
     */
    function __construct($sSystem, $iId)
    {
        parent::__construct($sSystem, $iId);

        $this->_oModule = BxDolModule::getInstance('BxPollModule');
    }

	function getBaseUrl()
    {
    	$aEntry = $this->_oModule->_oDb->getPollInfo($this->getId());
    	if(empty($aEntry) || !is_array($aEntry))
    		return '';

    	$aEntry = array_shift($aEntry);
    	if(empty($aEntry) || !is_array($aEntry))
    		return '';

    	return BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . '&action=show_poll_info&id=' . $aEntry['id_poll']; 
    }
}
