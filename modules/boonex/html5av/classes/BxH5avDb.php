<?php

bx_import('BxDolModuleDb');

class BxH5avDb extends BxDolModuleDb
{
    var $_oConfig;

    function BxH5avDb(&$oConfig)
    {
        parent::BxDolModuleDb();
        $this->_oConfig = $oConfig;
    }

}
