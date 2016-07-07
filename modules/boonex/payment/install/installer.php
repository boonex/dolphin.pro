<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import("BxDolInstaller");

class BxPmtInstaller extends BxDolInstaller
{
	protected $_sParamDefaultPayment;

    function __construct($aConfig)
    {
        parent::__construct($aConfig);

        $this->_sParamDefaultPayment = 'sys_default_payment';
    }

	function install($aParams)
    {
        $aResult = parent::install($aParams);

        if($aResult['result'] && getParam($this->_sParamDefaultPayment) == '')
        	setParam($this->_sParamDefaultPayment, $this->_aConfig['home_uri']);

        if($aResult['result'])
            BxDolService::call($this->_aConfig['home_uri'], 'update_dependent_modules');

        return $aResult;
    }

	function uninstall($aParams)
    {
        $aResult = parent::uninstall($aParams);

        if($aResult['result'] && getParam($this->_sParamDefaultPayment) == $this->_aConfig['home_uri'])
        	setParam($this->_sParamDefaultPayment, '');

        return $aResult;
    }
}
