<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolCron');
bx_import('BxDolModuleDb');
bx_import('BxDolInstallerUi');
bx_import('BxDolEmailTemplates');

class BxDolCronModules extends BxDolCron
{
    function __construct()
    {
        parent::__construct();
    }

	function processing()
    {
    	$oModules = new BxDolModuleDb();
        $aModules = $oModules->getModules();

        $aResult = array();
        foreach($aModules as $aModule) {
        	$aCheckInfo = BxDolInstallerUi::checkForUpdates($aModule);
        	if(isset($aCheckInfo['version']))
        		$aResult[] = _t('_adm_txt_modules_update_text_ext', $aModule['title'], $aCheckInfo['version']);
        }
        if(empty($aResult))
        	return;

    	$aAdmins = $GLOBALS['MySQL']->getAll("SELECT * FROM `Profiles` WHERE `Role`&" . BX_DOL_ROLE_ADMIN . "<>0 AND `EmailNotify`='1'");
        if(empty($aAdmins))
        	return; 

		$oEmailTemplate = new BxDolEmailTemplates();
        $sMessage = implode('<br />', $aResult);

		foreach($aAdmins as $aAdmin) {
        	$aTemplate = $oEmailTemplate->getTemplate('t_ModulesUpdates', $aAdmin['ID']);

			sendMail(
				$aAdmin['Email'], 
		        $aTemplate['Subject'], 
		        $aTemplate['Body'], 
		        $aAdmin['ID'], 
		        array(
		        	'MessageText' => $sMessage
				)
			);
		}
    }
}
