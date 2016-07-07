<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstaller.php");

class BxAdsInstaller extends BxDolInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }

    function install($aParams)
    {
        $aResult = parent::install($aParams);

        if (!$aResult['result'])
            return $aResult;

        if (BxDolRequest::serviceExists('wall', 'update_handlers'))
            BxDolService::call('wall', 'update_handlers', array($this->_aConfig['home_uri'], true));

        if (BxDolRequest::serviceExists('spy', 'update_handlers'))
            BxDolService::call('spy', 'update_handlers', array($this->_aConfig['home_uri'], true));

        BxDolService::call('ads', 'map_install');

        return $aResult;
    }

    function uninstall($aParams)
    {
        if (BxDolRequest::serviceExists('wall', 'update_handlers'))
            BxDolService::call('wall', 'update_handlers', array($this->_aConfig['home_uri'], false));

        if (BxDolRequest::serviceExists('spy', 'update_handlers'))
            BxDolService::call('spy', 'update_handlers', array($this->_aConfig['home_uri'], false));

        $aResult = parent::uninstall($aParams);

        if ($aResult['result'] && BxDolModule::getInstance('BxWmapModule'))
            BxDolService::call('wmap', 'part_uninstall', array('ads'));

        return $aResult;
    }
}
