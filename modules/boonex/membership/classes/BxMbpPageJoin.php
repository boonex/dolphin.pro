<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxMbpPageJoin extends BxDolPageView
{
	var $_oObject;

    function __construct(&$oObject)
    {
    	parent::__construct('bx_mbp_join');

    	$this->_oObject = $oObject;
    }

	function getBlockCode_Select()
    {
        return $this->_oObject->getSelectLevelBlock();
    }
}
