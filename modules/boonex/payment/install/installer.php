<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import("BxDolInstaller");

class BxPmtInstaller extends BxDolInstaller
{
    function BxPmtInstaller($aConfig)
    {
        parent::BxDolInstaller($aConfig);
    }

	function install($aParams)
    {
        $aResult = parent::install($aParams);

        if($aResult['result'])
            BxDolService::call($this->_aConfig['home_uri'], 'update_dependent_modules');

        return $aResult;
    }
}
