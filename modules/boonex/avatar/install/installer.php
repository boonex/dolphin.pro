<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolInstaller');

class BxAvaInstaller extends BxDolInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }

    function install($aParams)
    {
        $aResult = parent::install($aParams);

        if($aResult['result'] && BxDolRequest::serviceExists('wall', 'update_handlers'))
            BxDolService::call('wall', 'update_handlers', array($this->_aConfig['home_uri'], true));

        return $aResult;
    }

    function uninstall($aParams)
    {
        if(BxDolRequest::serviceExists('wall', 'update_handlers'))
            BxDolService::call('wall', 'update_handlers', array($this->_aConfig['home_uri'], false));

        $aResult = parent::uninstall($aParams);

        if ($aResult['result']) {
            foreach ($this->_aConfig['install_permissions']['writable'] as $sDir) {
                $sPath = BX_DIRECTORY_PATH_MODULES . $this->_aConfig['home_dir'] . $sDir;
                if (is_dir($sPath))
                    bx_clear_folder($sPath);
            }
            bx_import('BxDolCacheUtilities');
            $oCacheUtilities = new BxDolCacheUtilities();
            $oCacheUtilities->clear('users');
        }
        return $aResult;
    }
}
