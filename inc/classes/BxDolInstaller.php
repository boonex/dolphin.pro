<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolInstallerUtils');
bx_import('BxDolParams');
bx_import('BxDolPageViewAdmin');
bx_import('BxDolPFM');
bx_import('BxDolModuleDb');

define("BX_DOL_INSTALLER_SUCCESS", 0);
define("BX_DOL_INSTALLER_FAILED", 1);

/**
 * Base class for Installer classes in modules engine.
 *
 * The class contains different check functions which are used during the installation process.
 * An object of the class is created automatically with Dolphin's modules installer.
 * Installation/Uninstallation process can be controlled with config.php file located in  [module]/install/ folder.
 *
 *
 * Example of usage:
 * @see any module included in the default Dolphin's package.
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxDolInstaller extends BxDolInstallerUtils
{
    var $_aConfig;
    var $_sBasePath;
    var $_sHomePath;
    var $_sModulePath;

    var $_aActions;
    var $_aNonHashable;

    function __construct($aConfig)
    {
        parent::__construct();

        $this->_aConfig = $aConfig;
        $this->_sBasePath = BX_DIRECTORY_PATH_MODULES;
        $this->_sHomePath = $this->_sBasePath . $aConfig['home_dir'];
        $this->_sModulePath = $this->_sBasePath . $aConfig['home_dir'];

        $this->_aActions = array(
            'check_script_version' => array(
                'title' => _t('_adm_txt_modules_check_script_version'),
            ),
            'check_dependencies' => array(
                'title' => _t('_adm_txt_modules_check_dependencies'),
            ),
            'show_introduction' => array(
                'title' => _t('_adm_txt_modules_show_introduction'),
            ),
            'check_permissions' => array(
                'title' => _t('_adm_txt_modules_check_permissions'),
            ),
            'change_permissions' => array(
                'title' => _t('_adm_txt_modules_change_permissions'),
            ),
            'execute_sql' => array(
                'title' => _t('_adm_txt_modules_execute_sql'),
            ),
            'update_languages' => array(
                'title' => _t('_adm_txt_modules_update_languages'),
            ),
            'recompile_global_paramaters' => array(
                'title' => _t('_adm_txt_modules_recompile_global_paramaters'),
            ),
            'recompile_main_menu' => array(
                'title' => _t('_adm_txt_modules_recompile_main_menu'),
            ),
            'recompile_member_menu' => array(
                'title' => _t('_adm_txt_modules_recompile_member_menu'),
            ),
            'recompile_site_stats' => array(
                'title' => _t('_adm_txt_modules_recompile_site_stats'),
            ),
            'recompile_page_builder' => array(
                'title' => _t('_adm_txt_modules_recompile_page_builder'),
            ),
            'recompile_profile_fields' => array(
                'title' => _t('_adm_txt_modules_recompile_profile_fields'),
            ),
            'recompile_comments' => array(
                'title' => _t('_adm_txt_modules_recompile_comments'),
            ),
            'recompile_member_actions' => array(
                'title' => _t('_adm_txt_modules_recompile_member_actions'),
            ),
            'recompile_tags' =>  array(
                'title' => _t('_adm_txt_modules_recompile_tags'),
            ),
            'recompile_votes' => array(
                'title' => _t('_adm_txt_modules_recompile_votes'),
            ),
            'recompile_categories' =>  array(
                'title' => _t('_adm_txt_modules_recompile_categories'),
            ),
            'recompile_search' => array(
                'title' => _t('_adm_txt_modules_recompile_search'),
            ),
            'recompile_injections' => array(
                'title' => _t('_adm_txt_modules_recompile_injections'),
            ),
            'recompile_permalinks' => array(
                'title' => _t('_adm_txt_modules_recompile_permalinks'),
            ),
            'recompile_alerts' => array(
                'title' => _t('_adm_txt_modules_recompile_alerts'),
            ),
            'clear_db_cache' => array(
                'title' => _t('_adm_txt_modules_clear_db_cache'),
            ),
            'show_conclusion' => array(
                'title' => _t('_adm_txt_modules_show_conclusion'),
            ),
        );
        $this->_aNonHashable = array(
            'install',
            'updates'
        );
    }

    function install($aParams)
    {
        $oModuleDb = new BxDolModuleDb();
        $sTitle = _t('_adm_txt_modules_operation_install', $this->_aConfig['title']);

        //--- Check whether the module was already installed ---//
        if($oModuleDb->isModule($this->_aConfig['home_uri']))
            return array(
                'operation_title' => $sTitle,
                'message' => _t('_adm_txt_modules_already_installed'),
                'result' => false
            );

        //--- Check mandatory settings ---//
        if($oModuleDb->isModuleParamsUsed($this->_aConfig['home_uri'], $this->_aConfig['home_dir'], $this->_aConfig['db_prefix'], $this->_aConfig['class_prefix']))
            return array(
                'operation_title' => $sTitle,
                'message' => _t('_adm_txt_modules_params_used'),
                'result' => false
            );

        //--- Check version compatibility ---//
        $bCompatible = false;
        if(isset($this->_aConfig['compatible_with']) && is_array($this->_aConfig['compatible_with']))
            foreach($this->_aConfig['compatible_with'] as $iKey => $sVersion) {
                $sVersion = '/^' . str_replace(array('.', 'x'), array('\.', '[0-9]+'), $sVersion) . '$/is';
                $bCompatible = $bCompatible || (preg_match($sVersion, $GLOBALS['site']['ver'] . '.' . $GLOBALS['site']['build']) > 0);
            }
        if(!$bCompatible)
            return array(
                'operation_title' => $sTitle,
                'message' => $this->_displayResult('check_script_version', false, '_adm_txt_modules_wrong_version_script'),
                'result' => false
            );

        //--- Check actions ---//
        $aResult = $this->_perform('install', 'Installation');
        if($aResult['result']) {
            $sDependencies = "";
            if(isset($this->_aConfig['install']['check_dependencies']) && (int)$this->_aConfig['install']['check_dependencies'] == 1 && isset($this->_aConfig['dependencies']) && is_array($this->_aConfig['dependencies']))
                $sDependencies = implode(',', array_keys($this->_aConfig['dependencies']));

            db_res("INSERT IGNORE INTO `sys_modules`(`title`, `vendor`, `version`, `update_url`, `path`, `uri`, `class_prefix`, `db_prefix`, `dependencies`, `date`) VALUES ('" . $this->_aConfig['title'] . "', '" . $this->_aConfig['vendor'] . "', '" . $this->_aConfig['version'] . "', '" . $this->_aConfig['update_url'] . "', '" . $this->_aConfig['home_dir'] . "', '" . $this->_aConfig['home_uri'] . "', '" . $this->_aConfig['class_prefix'] . "', '" . $this->_aConfig['db_prefix'] . "', '" . $sDependencies . "', UNIX_TIMESTAMP())");
            $iModuleId = (int)db_last_id();

            $aFiles = array();
            $this->_hash($this->_sModulePath, $aFiles);
            foreach($aFiles as $aFile)
                db_res("INSERT IGNORE INTO `sys_modules_file_tracks`(`module_id`, `file`, `hash`) VALUES('" . $iModuleId . "', '" . $aFile['file'] . "', '" . $aFile['hash'] . "')");

            $GLOBALS['MySQL']->cleanMemory('sys_modules_' . $this->_aConfig['home_uri']);
            $GLOBALS['MySQL']->cleanMemory('sys_modules_' . $iModuleId);
            $GLOBALS['MySQL']->cleanMemory('sys_modules');
        }
        else
            $this->_perform('uninstall', 'Uninstallation');

        $aResult['operation_title'] = $sTitle;
        return $aResult;
    }
    function uninstall($aParams)
    {
        $oModuleDb = new BxDolModuleDb();
        $sTitle = _t('_adm_txt_modules_operation_uninstall', $this->_aConfig['title']);

        //--- Check whether the module was already installed ---//
        if(!$oModuleDb->isModule($this->_aConfig['home_uri']))
            return array(
                'operation_title' => $sTitle,
                'message' => _t('_adm_txt_modules_already_uninstalled'),
                'result' => false
            );

        //--- Check for dependent modules ---//
        $bDependent = false;
        $aDependents = $oModuleDb->getDependent($this->_aConfig['home_uri']);
        if(is_array($aDependents) && !empty($aDependents)) {
            $bDependent = true;

            $sMessage = '<br />-- -- ' . _t('_adm_txt_modules_has_dependents') . '<br />';
            foreach($aDependents as $aDependent)
                $sMessage .= '-- -- ' . $aDependent['title'] . '<br />';
        }

        if($bDependent)
            return array(
                'operation_title' => $sTitle,
                'message' => $this->_displayResult('check_dependencies', false, $sMessage),
                'result' => false
            );

        $aResult = $this->_perform('uninstall', 'Uninstallation');
        if($aResult['result']) {
            $iModuleId = (int)$oModuleDb->getOne("SELECT `id` FROM `sys_modules` WHERE `vendor`='" . $this->_aConfig['vendor'] . "' AND `path`='" . $this->_aConfig['home_dir'] . "' LIMIT 1");
            $oModuleDb->query("DELETE FROM `sys_modules` WHERE `vendor`='" . $this->_aConfig['vendor'] . "' AND `path`='" . $this->_aConfig['home_dir'] . "' LIMIT 1");
            $oModuleDb->query("DELETE FROM `sys_modules_file_tracks` WHERE `module_id`='" . $iModuleId . "'");

            $GLOBALS['MySQL']->cleanMemory ('sys_modules_' . $this->_aConfig['home_uri']);
            $GLOBALS['MySQL']->cleanMemory ('sys_modules_' . $iModuleId);
            $GLOBALS['MySQL']->cleanMemory ('sys_modules');
        }

        $aResult['operation_title'] = $sTitle;
        return $aResult;
    }
    function recompile($aParams)
    {
        $aResult = array('message' => '', 'result' => false);

        $aLanguages = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Name` AS `name`, `Title` AS `title` FROM `sys_localization_languages` WHERE 1");
        if(isAdmin() && !empty($aLanguages)) {
            $this->_updateLanguage(false, current($aLanguages));

            $bResult = false;
            foreach($aLanguages as $aLanguage) {
                $bResult = $this->_updateLanguage(true, $aLanguage) && compileLanguage($aLanguage['id']);
                $aResult['message'] .= $aLanguage['title'] . ': <span class="' . ($bResult ? 'modules-action-success' : 'modules-action-failed') . '">' . _t($bResult ? '_adm_txt_modules_process_action_success' : '_adm_txt_modules_process_action_failed') . '</span><br />';

                $aResult['result'] |= $bResult;
            }
        }

        $aResult['operation_title'] = _t('_adm_txt_modules_operation_recompile', $this->_aConfig['title']);
        return $aResult;
    }
    function _hash($sPath, &$aFiles)
    {
        if(file_exists($sPath) && is_dir($sPath) && ($rSource = opendir($sPath))) {
            while(($sFile = readdir($rSource)) !== false) {
                if($sFile == '.' || $sFile =='..' || $sFile[0] == '.' || in_array($sFile, $this->_aNonHashable))
                    continue;

                if(is_dir($sPath . $sFile))
                    $this->_hash($sPath . $sFile . '/', $aFiles);
                else
                    $aFiles[] = $this->_info($sPath . $sFile);
            }
            closedir($rSource);
        } else
            $aFiles[] = $this->_info($sPath);
    }
    function _info($sPath)
    {
        return array(
            'file' => str_replace($this->_sModulePath, '', $sPath),
            'hash' => md5(file_get_contents($sPath))
        );
    }
    function _perform($sOperationName, $sOperationTitle)
    {
        if(!defined('BX_SKIP_INSTALL_CHECK') && !$GLOBALS['logged']['admin'])
            return array('message' => '', 'result' => false);

        $sMessage = '';
        foreach($this->_aConfig[$sOperationName] as $sAction => $iEnabled) {
            $sMethod = 'action' . str_replace (' ', '', ucwords(str_replace ('_', ' ', $sAction)));
            if($iEnabled == 0 || !method_exists($this, $sMethod))
                continue;

            $mixedResult = $this->$sMethod($sOperationName == 'install' || $sOperationName == 'update');

            //--- On Success ---//
            if((is_int($mixedResult) && (int)$mixedResult == BX_DOL_INSTALLER_SUCCESS) || (isset($mixedResult['code']) && (int)$mixedResult['code'] == BX_DOL_INSTALLER_SUCCESS)) {
                $sMessage .= $this->_displayResult($sAction, true, isset($mixedResult['content']) ? $mixedResult['content'] : '');
                continue;
            }

            //--- On Failed ---//
            $sMethodFailed = $sMethod . 'Failed';
            return array('message' => $this->_displayResult($sAction, false, method_exists($this, $sMethodFailed) ? $this->$sMethodFailed($mixedResult) : $this->actionOperationFailed($mixedResult)), 'result' => false);
        }

        $sMessage .= $sOperationTitle . ' finished';
        return array('message' => $sMessage, 'result' => true);
    }

    function _displayResult($sAction, $bResult, $sResult = '')
    {
        $sMessage = '-- ' . $this->_aActions[$sAction]['title'] . ' ';
        if(!empty($sResult) && substr($sResult, 0, 1) == '_')
            $sResult = _t($sResult) . '<br />';

        if(!$bResult)
            return $sMessage . '<span style="color:red; font-weight:bold;">' . $sResult . '</span>';

        if(empty($sResult))
            $sResult = _t('_adm_txt_modules_process_action_success') . '<br />';
        return $sMessage . '<span style="color:green; font-weight:bold;">' . $sResult . '</span>';
    }

    //--- Action Methods ---//
    function actionOperationFailed($mixedResult)
    {
        return _t('_adm_txt_modules_process_action_failed');
    }
    function actionCheckDependencies($bInstall = true)
    {
        $sContent = '';

        if($bInstall) {
            if(!isset($this->_aConfig['dependencies']) || !is_array($this->_aConfig['dependencies']))
                return BX_DOL_INSTALLER_SUCCESS;

            $oModulesDb = new BxDolModuleDb();
            foreach($this->_aConfig['dependencies'] as $sModuleUri => $sModuleTitle)
                if($sModuleUri != $this->_aConfig['home_uri'] && !$oModulesDb->isModule($sModuleUri))
                    $sContent .= '-- -- ' . $sModuleTitle . '<br />';

            if(!empty($sContent))
                $sContent = '<br />-- -- ' . _t('_adm_txt_modules_wrong_dependency_install') . '<br />' . $sContent;
        }

        return empty($sContent) ? BX_DOL_INSTALLER_SUCCESS : array('code' => BX_DOL_INSTALLER_FAILED, 'content' => $sContent);
    }
    function actionCheckDependenciesFailed($mixedResult)
    {
        return $mixedResult['content'];
    }
    function actionShowIntroduction($bInstall = true)
    {
        $sFile = $this->_aConfig[($bInstall ? 'install_info' : 'uninstall_info')]['introduction'];
        $sPath = $this->_sHomePath . 'install/info/' . $sFile;

        return file_exists($sPath) ? array("code" => BX_DOL_INSTALLER_SUCCESS, "content" => "<pre>" . file_get_contents($sPath) . "</pre>") : BX_DOL_INSTALLER_FAILED;
    }
    function actionShowConclusion($bInstall = true)
    {
        $sFile = $this->_aConfig[($bInstall ? 'install_info' : 'uninstall_info')]['conclusion'];
        $sPath = $this->_sHomePath . 'install/info/' . $sFile;

        return file_exists($sPath) ? array("code" => BX_DOL_INSTALLER_SUCCESS, "content" => "<pre>" . file_get_contents($sPath) . "</pre>") : BX_DOL_INSTALLER_FAILED;
    }
    function actionCheckPermissions($bInstall = true)
    {
        $aPermissions = $bInstall ? $this->_aConfig['install_permissions'] : $this->_aConfig['uninstall_permissions'];

        $aResult = array();
        foreach($aPermissions as $sPermissions => $aFiles) {
            $sCheckFunction = 'is' . ucfirst($sPermissions);
            $sCptPermissions = _t('_adm_txt_modules_' . $sPermissions);
            foreach($aFiles as $sFile)
                if(!BxDolInstallerUtils::$sCheckFunction(bx_ltrim_str($this->_sModulePath . $sFile, BX_DIRECTORY_PATH_ROOT)))
                    $aResult[] = array('path' => $this->_sModulePath . $sFile, 'permissions' => $sCptPermissions);
        }

        return empty($aResult) ? BX_DOL_INSTALLER_SUCCESS : array('code' => BX_DOL_INSTALLER_FAILED, 'content' => $aResult);
    }
    function actionCheckPermissionsFailed($mixedResult)
    {
        $sResult = '<br />-- -- ' . _t('_adm_txt_modules_wrong_permissions_check') . '<br />';
        foreach($mixedResult['content'] as $aFile)
            $sResult .= '-- -- ' . _t('_adm_txt_modules_wrong_permissions_msg', $aFile['path'], $aFile['permissions']) . '<br />';
        return $sResult;
    }
	function actionChangePermissions($bInstall = true)
    {
        $aPermissions = $bInstall ? $this->_aConfig['install_permissions'] : $this->_aConfig['uninstall_permissions'];

        $aResult = $aChangeItems = array();
        foreach($aPermissions as $sPermissions => $aFiles) {
            $sCheckFunction = 'is' . ucfirst($sPermissions);
            foreach($aFiles as $sFile) {
            	$sPath = bx_ltrim_str($this->_sModulePath . $sFile, BX_DIRECTORY_PATH_ROOT);
            	if(BxDolInstallerUtils::$sCheckFunction($sPath))
            		continue;

				$aResult[] = array('path' => $this->_sModulePath . $sFile, 'permissions' => $sPermissions);
				$aChangeItems[] = array('file' => $sFile, 'path' => $sPath, 'permissions' => $sPermissions);
            }
        }

        if(empty($aChangeItems))
        	return BX_DOL_INSTALLER_SUCCESS;

		$sFtpHost = getParam('sys_ftp_host');
		if(empty($sFtpHost))
			$sFtpHost = $_SERVER['HTTP_HOST'];

		bx_import('BxDolFtp');
		$oFile = new BxDolFtp($sFtpHost, getParam('sys_ftp_login'), getParam('sys_ftp_password'), getParam('sys_ftp_dir'));

		if(!$oFile->connect())
			return array('code' => BX_DOL_INSTALLER_FAILED, 'content_msg' => '_adm_txt_modules_wrong_permissions_change_cannot_connect_to_ftp', 'content_data' => $aResult);

		if(!$oFile->isDolphin())
			return array('code' => BX_DOL_INSTALLER_FAILED, 'content_msg' => '_adm_txt_modules_wrong_permissions_change_destination_not_valid', 'content_data' => $aResult);

        $aResult = array();
        foreach($aChangeItems as $aChangeItem)
			if(!$oFile->setPermissions($aChangeItem['path'], $aChangeItem['permissions']))
				$aResult[] = array('path' => $this->_sModulePath . $aChangeItem['file'], 'permissions' => $aChangeItem['permissions']);

        return empty($aResult) ? BX_DOL_INSTALLER_SUCCESS : array('code' => BX_DOL_INSTALLER_FAILED, 'content_msg' => '_adm_txt_modules_wrong_permissions_change', 'content_data' => $aResult);
    }
    function actionChangePermissionsFailed($mixedResult)
    {
    	if(empty($mixedResult['content_msg']) && empty($mixedResult['content_data']))
			return $this->actionOperationFailed($mixedResult);

		$sResult = '';
		if(!empty($mixedResult['content_msg']))
			$sResult .= _t($mixedResult['content_msg']);

		if(!empty($mixedResult['content_data'])) {
	        $sResult .= ' ' . _t('_adm_txt_modules_wrong_permissions_change_list') . '<br />';
	        foreach($mixedResult['content_data'] as $aFile)
	            $sResult .= '-- ' . _t('_adm_txt_modules_wrong_permissions_msg', $aFile['path'], $aFile['permissions']) . '<br />';
		}

        return $sResult;
    }
    function actionExecuteSql($bInstall = true)
    {
        if($bInstall)
            $this->actionExecuteSql(false);

        $sPath = $this->_sHomePath . 'install/sql/' . ($bInstall ? 'install' : 'uninstall') . '.sql';
        if(!file_exists($sPath) || !($rHandler = fopen($sPath, "r")))
            return BX_DOL_INSTALLER_FAILED;

        $sQuery = "";
        $sDelimiter = ';';
        $aResult = array();
        while(!feof($rHandler)) {
            $sStr = trim(fgets($rHandler));

            if(empty($sStr) || $sStr[0] == "" || $sStr[0] == "#" || ($sStr[0] == "-" && $sStr[1] == "-"))
                continue;

            //--- Change delimiter ---//
            if(strpos($sStr, "DELIMITER //") !== false || strpos($sStr, "DELIMITER ;") !== false) {
                $sDelimiter = trim(str_replace('DELIMITER', '', $sStr));
                continue;
            }

            $sQuery .= $sStr;

            //--- Check for multiline query ---//
            if(substr($sStr, -strlen($sDelimiter)) != $sDelimiter)
                continue;

            //--- Execute query ---//
            $sQuery = str_replace("[db_prefix]", $this->_aConfig['db_prefix'], $sQuery);
            if($sDelimiter != ';')
                $sQuery = str_replace($sDelimiter, "", $sQuery);

            try {
                $rResult = db_res(trim($sQuery));
            } catch (Exception $e) {
                $aResult[] = array('query' => $sQuery, 'error' => $e->getMessage());
            }


            $sQuery = "";
        }
        fclose($rHandler);

        return empty($aResult) ? BX_DOL_INSTALLER_SUCCESS : array('code' => BX_DOL_INSTALLER_FAILED, 'content' => $aResult);
    }
    function actionExecuteSqlFailed($mixedResult)
    {
        $sResult = '<br />-- -- ' . _t('_adm_txt_modules_wrong_mysql_query') . '<br />';
        foreach($mixedResult['content'] as $aQuery) {
            $sResult .= '-- -- ' . _t('_adm_txt_modules_wrong_mysql_query_msg', $aQuery['error']) . '<br />';
            $sResult .= '<pre>' . $aQuery['query'] . '</pre>';
        }
        return $sResult;
    }
    function actionUpdateLanguages($bInstall = true)
    {
        $aLanguages = array();
        $rLanguages = db_res("SELECT `ID` AS `id`, `Name` AS `name`, `Title` AS `title` FROM `sys_localization_languages`");
        while($aLanguage = $rLanguages->fetch())
           $aLanguages[] = $aLanguage;

        //--- Process Language Category ---//
        $iCategoryId = 100;
        $sCategoryName = isset($this->_aConfig['language_category']) ? $this->_aConfig['language_category'] : '';
        if($bInstall && !empty($sCategoryName)) {
            $res = db_res("INSERT IGNORE INTO `sys_localization_categories` SET `Name`= ?", [$sCategoryName]);
            if(db_affected_rows($res) <= 0 )
                $iCategoryId = (int)db_value("SELECT `ID` FROM `sys_localization_categories` WHERE `Name`='" . $sCategoryName . "' LIMIT 1");
            else
                $iCategoryId = db_last_id();
        } else if(!$bInstall && !empty($sCategoryName)) {
            db_res("DELETE FROM `sys_localization_categories` WHERE `Name`= ?", [$sCategoryName]);
        }

        //--- Process languages' key=>value pears ---//
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
    function actionUpdateLanguagesFailed($mixedResult)
    {
        $sResult = '<br />-- -- ' . _t('_adm_txt_modules_cannot_recompile_lang') . '<br />';
        foreach($mixedResult['content'] as $sLanguage)
            $sResult .= '-- -- ' . $sLanguage . '<br />';
        return $sResult;
    }
    function actionRecompileGlobalParamaters($bInstall = true)
    {
        global $MySQL;
        ob_start();
        $bResult = $MySQL->oParams->cache();
        ob_get_clean();

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileMainMenu($bInstall = true)
    {
        ob_start();
        $oBxDolMenu = new BxDolMenu();
        $bResult = $oBxDolMenu->compile();
        ob_get_clean();

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileMemberMenu($bInstall = true)
    {
        bx_import('BxDolMemberMenu');
        $oMemberMenu = new BxDolMemberMenu();
        $bResult = $oMemberMenu -> deleteMemberMenuCaches();

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileSiteStats($bInstall = true)
    {
        $bResult = $GLOBALS['MySQL']->cleanCache('sys_stat_site');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompilePageBuilder($bInstall = true)
    {
        ob_start();
        $oPVCacher = new BxDolPageViewCacher('sys_page_compose', 'sys_page_compose.inc');
        $bResult = $oPVCacher -> createCache();
        ob_get_clean();

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileProfileFields($bInstall = true)
    {
        ob_start();
        $oBxDolPFMCacher = new BxDolPFMCacher();
        $bResult = $oBxDolPFMCacher -> createCache();
        ob_get_clean();

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileComments($bInstall = true)
    {
        $bResult = $GLOBALS['MySQL']->cleanCache('sys_objects_cmts');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileMemberActions($bInstall = true)
    {
        $bResult = $GLOBALS['MySQL']->cleanCache('sys_objects_actions');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileTags($bInstall = true)
    {
        $bResult = $GLOBALS['MySQL']->cleanCache('sys_objects_tag');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileVotes($bInstall = true)
    {
        $bResult = $GLOBALS['MySQL']->cleanCache('sys_objects_vote');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileCategories($bInstall = true)
    {
       $bResult = $GLOBALS['MySQL']->cleanCache('sys_objects_categories');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    //TODO: Remove the method and 'recompile_search' in the config.php of all modules.
    function actionRecompileSearch($bInstall = true)
    {
        return BX_DOL_INSTALLER_SUCCESS;
    }
    function actionRecompileInjections($bInstall = true)
    {
        $bResult = $GLOBALS['MySQL']->cleanCache('sys_injections.inc');
        $bResult = $bResult && $GLOBALS['MySQL']->cleanCache('sys_injections_admin.inc');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompilePermalinks($bInstall = true)
    {
        $bResult = true;
        ob_start();
        $oPermalinks = new BxDolPermalinks();
        $bResult = $bResult && $oPermalinks->cache();

        $oMenu = new BxDolMenu();
        $bResult = $bResult && $oMenu->compile();

        $bResult = $bResult && $GLOBALS['MySQL']->cleanCache ('sys_menu_member');
        ob_get_clean();

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionRecompileAlerts($bInstall = true)
    {
        ob_start();
        $bResult = $GLOBALS['MySQL']->cleanCache('sys_alerts');
        ob_end_clean();

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }
    function actionClearDbCache($bInstall = true)
    {
        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        $bResult = $oCache->removeAllByPrefix ('db_');

        return $bResult ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED;
    }

    //--- Get/Set Methods ---//
    function getVendor()
    {
        return $this->_aConfig['vendor'];
    }
    function getName()
    {
        return $this->_aConfig['name'];
    }
    function getTitle()
    {
        return $this->_aConfig['title'];
    }
    function getHomeDir()
    {
        return $this->_aConfig['home_dir'];
    }

    //--- Protected methods ---//

    function _updateLanguage($bInstall, $aLanguage, $iCategoryId = 0)
    {
        if(empty($iCategoryId))
            $iCategoryId = (int)db_value("SELECT `ID` FROM `sys_localization_categories` WHERE `Name`='" . $this->_aConfig['language_category'] . "' LIMIT 1");

        $sPath = $this->_sHomePath . 'install/langs/' . $aLanguage['name'] . '.php';
        if(!file_exists($sPath))
            return false;

        include($sPath);
        if(!(isset($aLangContent) && is_array($aLangContent)))
            return false;

        //--- Installation ---//
        if($bInstall)
            foreach($aLangContent as $sKey => $sValue) {
                $iLangKeyId = (int)db_value("SELECT `ID` FROM `sys_localization_keys` WHERE `IDCategory`='" . $iCategoryId . "' AND `Key`='" . $sKey . "' LIMIT 1");
                if($iLangKeyId == 0) {
                    $res = db_res("INSERT INTO `sys_localization_keys`(`IDCategory`, `Key`) VALUES('" . $iCategoryId . "', '" . $sKey . "')");
                    if(db_affected_rows($res) <= 0)
                        continue;

                    $iLangKeyId = db_last_id();
                }
                db_res("INSERT IGNORE INTO `sys_localization_strings`(`IDKey`, `IDLanguage`, `String`) VALUES('" . $iLangKeyId . "', '" . $aLanguage['id'] . "', '" . addslashes($sValue) . "')");
            }
        //--- Uninstallation ---//
        else
            foreach($aLangContent as $sKey => $sValue)
                db_res("DELETE FROM `sys_localization_keys`, `sys_localization_strings` USING `sys_localization_keys`, `sys_localization_strings` WHERE `sys_localization_keys`.`ID`=`sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key`='" . $sKey . "'");

        return true;
    }

    function _addLanguage($aLanguage, $aLangInfo)
    {
        $oDb = new BxDolModuleDb();

        if (getLangIdByName($aLangInfo['Name'])) // language already exists
            return false;

        $sLangName = $aLangInfo['Name'];
        $sLangFlag = $aLangInfo['Flag'];
        $sLangTitle = $aLangInfo['Title'];
        $sLangDir = isset($aLangInfo['Direction']) && $aLangInfo['Direction'] ? $aLangInfo['Direction'] : 'LTR';
        $sLangCountryCode = isset($aLangInfo['LanguageCountry']) && $aLangInfo['LanguageCountry'] ? $aLangInfo['LanguageCountry'] : $aLangInfo['Name'] . '_' . strtoupper($aLangInfo['Flag']);

        if (!$oDb->res("INSERT INTO `sys_localization_languages` VALUES (?, ?, ?, ?, ?, ?)", [
            NULL,
            $sLangName,
            $sLangFlag,
            $sLangTitle,
            $sLangDir,
            $sLangCountryCode
        ])) {
            return false;
        }
        $iLangKey = $oDb->lastId();

        foreach ($aLanguage as $sKey => $sValue) {
            $sDbKey = $sKey;
            $sDbValue = $sValue;

            $iExistedKey = $oDb->getOne("SELECT `ID` FROM `sys_localization_keys` WHERE `Key` = ?", [$sDbKey]);
            if (!$iExistedKey) { // key is missing, insert new key
                if (!$oDb->res("INSERT INTO `sys_localization_keys` VALUES (NULL, ?, ?)", [BX_DOL_LANGUAGE_CATEGORY_SYSTEM, $sDbKey]))
                    continue;
                $iExistedKey = $oDb->lastId();
            }

            $oDb->res("INSERT INTO `sys_localization_strings` VALUES(?, ?, ?)", [$iExistedKey, $iLangKey, $sDbValue]);
        }

        return true;
    }

    function _removeLanguage($aLanguage, $aLangInfo)
    {
        $oDb = new BxDolModuleDb();

        if (!($iLangKey = getLangIdByName($aLangInfo['Name']))) // language doesn't exists, so it is already removed somehow
            return true;

        if (!$oDb->res("DELETE FROM `sys_localization_languages` WHERE ID = {$iLangKey}"))
            return false;

        $oDb->res("DELETE FROM `sys_localization_strings` WHERE `IDLanguage` = {$iLangKey}");

        return true;
    }

    function _recompileLanguageForAllModules($iLangId)
    {
        $oDb = new BxDolModuleDb();

        $aLanguage = $GLOBALS['MySQL']->getRow("SELECT `ID` AS `id`, `Name` AS `name`, `Title` AS `title` FROM `sys_localization_languages` WHERE `ID` = ?", [$iLangId]);
        if (!$aLanguage)
            return false;

        // save class properties
        $aSave['config'] = $this->_aConfig;
        $aSave['home_path'] = $this->_sHomePath;
        $aSave['module_path'] = $this->_sModulePath;

        $aModules = $oDb->getModules();
        foreach ($aModules as $a) {
            $aConfig = false;
            $bInclude = @include(BX_DIRECTORY_PATH_MODULES . $a['path'] . 'install/config.php');
            if (!$bInclude || !$aConfig)
                continue;

            $this->_aConfig = $aConfig;
            $this->_sHomePath = $this->_sBasePath . $aConfig['home_dir'];
            $this->_sModulePath = $this->_sBasePath . $aConfig['home_dir'];

            $b = $this->_updateLanguage(true, $aLanguage);
        }

        // restore class properties
        $this->_aConfig = $aSave['config'];
        $this->_sHomePath = $aSave['home_path'];
        $this->_sModulePath = $aSave['module_path'];

        return true;
    }

    function _getPermissions($sFilePath)
    {
        clearstatcache();
        $hPerms = @fileperms($sFilePath);
        if($hPerms == false)
            return false;
        return substr( decoct( $hPerms ), -3 );
    }
}
