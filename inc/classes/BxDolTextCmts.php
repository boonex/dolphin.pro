<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxTemplCmtsView');

class BxDolTextCmts extends BxTemplCmtsView
{
    var $_oModule;

    function __construct($sSystem, $iId, $iInit = 1)
    {
        parent::__construct($sSystem, $iId, $iInit);

        $this->_oModule = null;
    }

    function getBaseUrl()
    {
    	$aEntry = $this->_oModule->_oDb->getEntries(array('sample_type' => 'id', 'id' => $this->getId()));
    	if(empty($aEntry) || !is_array($aEntry))
    		return '';

    	return BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'view/' . $aEntry['uri']; 
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
