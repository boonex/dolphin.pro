<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolInstaller');

class BxOAuthInstaller extends BxDolInstaller
{
    function BxOAuthInstaller($aConfig)
    {
        parent::BxDolInstaller($aConfig);
    }

    function install($aParams)
    {
        $aResult = parent::install($aParams);
        return $aResult;
    }

    function uninstall($aParams)
    {
        return parent::uninstall($aParams);
    }
}
