<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplCmtsView');

class BxDolCmtsProfile extends BxTemplCmtsView
{
    /**
     * Constructor
     */
    function __construct($sSystem, $iId, $iInit = 1)
    {
        parent::__construct($sSystem, $iId, $iInit);
    }

	function getBaseUrl()
    {
    	$aEntry = getProfileInfo($this->getId());
    	if(empty($aEntry) || !is_array($aEntry))
    		return '';

    	return getProfileLink($aEntry['ID']); 
    }

    function isRemoveAllowedAll()
    {
        if($this->_iId == $this->_getAuthorId() && getParam('enable_cmts_profile_delete') == 'on')
           return true;

        return parent::isRemoveAllowedAll();
    }
}
