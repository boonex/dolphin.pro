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

        $this->_aActions['check_requirements'] = array(
			'title' => 'Check requirements:',
		);
    }

	function actionCheckRequirements()
	{
		$aPhpSettings = array (
			'php module: pdo' => array('op' => 'module', 'val' => 'pdo'),
            'php module: pdo_mysql' => array('op' => 'module', 'val' => 'pdo_mysql')
		);

		bx_import('BxDolAdminTools');
		$oAdmTools = new BxDolAdminTools();

		$aResult = array();
		foreach ($aPhpSettings as $sName => $aData) {
            $aCheckResult = $oAdmTools->checkPhpSetting($sName, $aData);
            if($aCheckResult['res'])
            	continue;

			$aResult[] = $aData['val'];
		}

		return empty($aResult) ? BX_DOL_INSTALLER_SUCCESS : array('code' => BX_DOL_INSTALLER_FAILED, 'data' => $aResult);
	}

	function actionCheckRequirementsFailed($mixedResult)
	{
		if(empty($mixedResult['data']))
			return $this->actionOperationFailed($mixedResult);

		$sResult = 'The following PHP modules are required:<br />';
        foreach($mixedResult['data'] as $sModule)
            $sResult .= '-- ' . $sModule . '<br />';

        return $sResult;
	}
}
