<?php

bx_import('BxDolModuleDb');

class BxH5avDb extends BxDolModuleDb
{
    var $_oConfig;

    function __construct(&$oConfig)
    {
        parent::__construct();
        $this->_oConfig = $oConfig;
    }

}
