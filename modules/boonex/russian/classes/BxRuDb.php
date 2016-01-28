<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

class BxRuDb extends BxDolModuleDb
{
    var $_oConfig;

    function __construct(&$oConfig)
    {
        parent::__construct();
        $this->_oConfig = $oConfig;
    }

}
