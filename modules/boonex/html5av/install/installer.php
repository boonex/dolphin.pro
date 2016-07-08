<?php

bx_import('BxDolInstaller');

class BxH5avInstaller extends BxDolInstaller
{

    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }

    function install($aParams)
    {
        return parent::install($aParams);
    }

    function uninstall($aParams)
    {
        $aResult = parent::uninstall($aParams);
        return $aResult;
    }

}
