<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import("BxDolInstaller");

class BxMbpInstaller extends BxDolInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
        $this->_aActions['check_payment'] = array(
			'title' => _t('_adm_txt_modules_check_dependencies'),
		);
    }

	function actionCheckPayment($bInstall = true)
	{
		if(!$bInstall)
			return BX_DOL_INSTALLER_SUCCESS;

		$aError = array('code' => BX_DOL_INSTALLER_FAILED, 'content' => _t('_adm_txt_modules_wrong_dependency_install_payment'));

		$sPayment = getParam('sys_default_payment');
		if(empty($sPayment))
			return $aError;

		$oModuleDb = new BxDolModuleDb();
		$aPayment = $oModuleDb->getModuleByUri($sPayment);
		if(empty($aPayment) || !is_array($aPayment))
			return $aError;

    	return BX_DOL_INSTALLER_SUCCESS;
    }

	function actionCheckPaymentFailed($mixedResult)
    {
        return $mixedResult['content'];
    }

	function install($aParams)
    {
        $aResult = parent::install($aParams);

		if($aResult['result'] && BxDolRequest::serviceExists('payment', 'update_dependent_modules'))
            BxDolService::call('payment', 'update_dependent_modules', array($this->_aConfig['home_uri'], true));

		if($aResult['result'] && BxDolRequest::serviceExists('payflow', 'update_dependent_modules'))
            BxDolService::call('payflow', 'update_dependent_modules', array($this->_aConfig['home_uri'], true));

        return $aResult;
    }

    function uninstall($aParams)
    {
		if(BxDolRequest::serviceExists('payment', 'update_dependent_modules'))
            BxDolService::call('payment', 'update_dependent_modules', array($this->_aConfig['home_uri'], false));

		if(BxDolRequest::serviceExists('payflow', 'update_dependent_modules'))
            BxDolService::call('payflow', 'update_dependent_modules', array($this->_aConfig['home_uri'], false));

        return parent::uninstall($aParams);
    }
}
