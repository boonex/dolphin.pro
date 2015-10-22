<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxMbpPageJoin extends BxDolPageView
{
	var $_oObject;

    function BxMbpPageJoin(&$oObject)
    {
    	parent::BxDolPageView('bx_mbp_join');

    	$this->_oObject = $oObject;
    }

	function getBlockCode_Select()
    {
        return $this->_oObject->getSelectLevelBlock();
    }
}
