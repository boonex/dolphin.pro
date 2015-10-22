<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxMbpPageMy extends BxDolPageView
{
	var $_oObject;

    function BxMbpPageMy(&$oObject)
    {
    	parent::BxDolPageView('bx_mbp_my_membership');

    	$this->_oObject = $oObject;

    	$GLOBALS['oTopMenu']->setCurrentProfileID($this->_oObject->getUserId());
    }

	function getBlockCode_Current()
    {
        return $this->_oObject->getCurrentLevelBlock();
    }

    function getBlockCode_Available()
    {
        return $this->_oObject->getAvailableLevelsBlock();
    }
}
