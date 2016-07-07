<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxTemplCmtsView');

class BxDolFilesCmtsAlbums extends BxTemplCmtsView
{
	var $_oModule;

    function __construct($sSystem, $iId, $iInit = 1)
    {
        parent::__construct($sSystem, $iId, $iInit);

        $this->_oModule = null;
    }

	function getBaseUrl()
    {
    	$aEntry = $this->_oModule->oAlbums->getAlbumInfo(array('fileid' => $this->getId()));
    	if(empty($aEntry) || !is_array($aEntry))
    		return '';

    	return BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'browse/album/' . $aEntry['Uri'] . '/owner/' . getUsername($aEntry['Owner']); 
    }
}
