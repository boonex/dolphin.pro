<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolInstaller');

class BxStoreInstaller extends BxDolInstaller
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

        if($aResult['result'] && BxDolRequest::serviceExists('spy', 'update_handlers'))
            BxDolService::call('spy', 'update_handlers', array($this->_aConfig['home_uri'], true));

		if($aResult['result'] && BxDolRequest::serviceExists('payment', 'update_dependent_modules'))
            BxDolService::call('payment', 'update_dependent_modules', array($this->_aConfig['home_uri'], true));

		if($aResult['result'] && BxDolRequest::serviceExists('payflow', 'update_dependent_modules'))
            BxDolService::call('payflow', 'update_dependent_modules', array($this->_aConfig['home_uri'], true));

        return $aResult;
    }

    function uninstall($aParams)
    {
        if(BxDolRequest::serviceExists('wall', 'update_handlers'))
            BxDolService::call('wall', 'update_handlers', array($this->_aConfig['home_uri'], false));

        if(BxDolRequest::serviceExists('spy', 'update_handlers'))
            BxDolService::call('spy', 'update_handlers', array($this->_aConfig['home_uri'], false));

		if(BxDolRequest::serviceExists('payment', 'update_dependent_modules'))
            BxDolService::call('payment', 'update_dependent_modules', array($this->_aConfig['home_uri'], false));

		if(BxDolRequest::serviceExists('payflow', 'update_dependent_modules'))
            BxDolService::call('payflow', 'update_dependent_modules', array($this->_aConfig['home_uri'], false));

        return parent::uninstall($aParams);
    }
}
