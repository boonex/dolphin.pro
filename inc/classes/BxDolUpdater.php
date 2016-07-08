<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolInstaller');

class BxDolUpdater extends BxDolInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
        $this->_sModulePath = $this->_sBasePath . $aConfig['module_dir'];

        $this->_aActions = array_merge($this->_aActions, array(
            'check_module_exists' => array(
                'title' => _t('_adm_txt_modules_check_module_exists'),
            ),
            'check_module_version' => array(
                'title' => _t('_adm_txt_modules_check_module_version'),
            ),
            'check_module_hash' => array(
                'title' => _t('_adm_txt_modules_check_module_hash'),
            ),
            'update_files' => array(
                'title' => _t('_adm_txt_modules_update_files'),
            ),
        ));
    }
    function update($aParams)
    {
        global $MySQL;

        $aResult = array(
            'operation_title' => _t('_adm_txt_modules_operation_update', $this->_aConfig['title'], $this->_aConfig['version_from'], $this->_aConfig['version_to'])
        );

        //--- Check for module to update ---//
        $aModuleInfo = $MySQL->getRow("SELECT `id`, `version` FROM `sys_modules` WHERE `path`= ? AND `uri`= ? LIMIT 1", [$this->_aConfig['module_dir'], $this->_aConfig['module_uri']]);
        if(!$aModuleInfo)
            return array_merge($aResult, array(
                'message' => $this->_displayResult('check_module_exists', false, '_adm_txt_modules_module_not_found'),
                'result' => false
            ));

        //--- Check version ---//
        if($aModuleInfo['version'] != $this->_aConfig['version_from'])
            return array_merge($aResult, array(
                'message' => $this->_displayResult('check_module_version', false, '_adm_txt_modules_wrong_version'),
                'result' => false
            ));

        //--- Check hash ---//
        $aFilesOrig = $MySQL->getAllWithKey("SELECT `file`, `hash` FROM `sys_modules_file_tracks` WHERE `module_id`= ?", "file", [$aModuleInfo['id']]);

        $aFiles = array();
        $this->_hash($this->_sModulePath, $aFiles);
        foreach($aFiles as $aFile)
            if(!isset($aFilesOrig[$aFile['file']]) || $aFilesOrig[$aFile['file']]['hash'] != $aFile['hash'])
                return array_merge($aResult, array(
                    'message' => $this->_displayResult('check_module_hash', false, '_adm_txt_modules_module_was_modified'),
                    'result' => false
                ));

        //--- Perform action and check results ---//
        $aResult = array_merge($aResult, $this->_perform('install', 'Update'));
        if($aResult['result']) {
            $MySQL->query("UPDATE `sys_modules` SET `version`='" . $this->_aConfig['version_to'] . "' WHERE `id`='" . $aModuleInfo['id'] . "'");
            $MySQL->query("DELETE FROM `sys_modules_file_tracks` WHERE `module_id`='" . $aModuleInfo['id'] . "'");

            $aFiles = array();
            $this->_hash(BX_DIRECTORY_PATH_ROOT . 'modules/' . $this->_aConfig['module_dir'], $aFiles);
            foreach($aFiles as $aFile)
                $MySQL->query("INSERT IGNORE INTO `sys_modules_file_tracks`(`module_id`, `file`, `hash`) VALUES('" . $aModuleInfo['id'] . "', '" . $aFile['file'] . "', '" . $aFile['hash'] . "')");
        }

        return $aResult;
    }

    //--- Action Methods ---//
    function actionUpdateFiles($bInstall = true)
    {
        $sPath = $this->_sHomePath . 'source/';
        if(!file_exists($sPath))
            return BX_DOL_INSTALLER_FAILED;

		$sFtpHost = getParam('sys_ftp_host');
		if(empty($sFtpHost))
			$sFtpHost = $_SERVER['HTTP_HOST'];

        $oFtp = new BxDolFtp($sFtpHost, getParam('sys_ftp_login'), getParam('sys_ftp_password'), getParam('sys_ftp_dir'));
        if($oFtp->connect() == false)
            return BX_DOL_INSTALLER_FAILED;

        return $oFtp->copy($sPath . '*', 'modules/' . $this->_aConfig['module_dir']) ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionUpdateLanguages($bInstall = true)
    {
        global $MySQL;

        $aLanguages = $MySQL->getAll("SELECT `ID` AS `id`, `Name` AS `name`, `Title` AS `title` FROM `sys_localization_languages`");

        //--- Process languages' key=>value pears ---//
        $sModuleConfig = $this->_sHomePath .'install/config.php';
        if(!file_exists($sModuleConfig))
            return array('code' => BX_DOL_INSTALLER_FAILED, 'content' => '_adm_txt_modules_module_config_not_found');

        include($sModuleConfig);
        $iCategoryId = (int)$MySQL->getOne("SELECT `ID` FROM `sys_localization_categories` WHERE `Name`='" . $aConfig['language_category'] . "' LIMIT 1");

        foreach($aLanguages as $aLanguage)
            $this->_updateLanguage($bInstall, $aLanguage, $iCategoryId);

        //--- Recompile all language files ---//
        $aResult = array();
        foreach($aLanguages as $aLanguage) {
            $bResult = compileLanguage($aLanguage['id']);

            if(!$bResult)
                $aResult[] = $aLanguage['title'];
        }
        return empty($aResult) ? BX_DOL_INSTALLER_SUCCESS : array('code' => BX_DOL_INSTALLER_FAILED, 'content' => $aResult);
    }

    //--- Protected methods ---//
    function _updateLanguage($bInstall, $aLanguage, $iCategoryId = 0)
    {
        global $MySQL;

        $sPath = $this->_sHomePath . 'install/langs/' . $aLanguage['name'] . '.php';
        if(!file_exists($sPath)) return false;

        include($sPath);

        //--- Process delete ---//
        if(isset($aLangContentDelete) && is_array($aLangContentDelete))
            foreach($aLangContentDelete as $sKey)
                $MySQL->query("DELETE FROM `tk`, `ts` USING `sys_localization_keys` AS `tk` LEFT JOIN `sys_localization_strings` AS `ts` ON `tk`.`ID`=`ts`.`IDKey` WHERE `tk`.`Key`='" . $sKey . "' AND `ts`.`IDLanguage`='" . $aLanguage['id'] . "'");

        //--- Process add ---//
        if(isset($aLangContentAdd) && is_array($aLangContentAdd))
            foreach($aLangContentAdd as $sKey => $sValue) {
                $mixedResult = $MySQL->query("INSERT IGNORE INTO `sys_localization_keys`(`IDCategory`, `Key`) VALUES('" . $iCategoryId . "', '" . $sKey . "')");
                if($mixedResult === false || $mixedResult <= 0)
                    continue;

                $iLangKeyId = (int)$MySQL->lastId();
                $MySQL->query("INSERT INTO `sys_localization_strings`(`IDKey`, `IDLanguage`, `String`) VALUES('" . $iLangKeyId . "', '" . $aLanguage['id'] . "', '" . addslashes($sValue) . "')");
            }

        //--- Process Update ---//
        if(isset($aLangContentUpdate) && is_array($aLangContentUpdate))
            foreach($aLangContentUpdate as $sKey => $sValue) {
                $iLangKeyId = (int)$MySQL->getOne("SELECT `ID` FROM `sys_localization_keys` WHERE `Key`='" . $sKey . "'");
                if($iLangKeyId == 0)
                    continue;

                $MySQL->query("UPDATE `sys_localization_strings` SET `String`='" . addslashes(clear_xss($sValue)) . "' WHERE `IDKey`='" . $iLangKeyId . "' AND `IDLanguage`='" . $aLanguage['id'] . "'");
            }

        return true;
    }
}
