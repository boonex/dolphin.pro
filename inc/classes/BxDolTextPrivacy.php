<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');

class BxDolTextPrivacy extends BxDolPrivacy
{
    var $_oModule;

    function __construct(&$oModule)
    {
        parent::__construct($oModule->_oDb->getPrefix() . 'entries', 'id', 'author_id');

        $this->_oModule = $oModule;
    }
}
