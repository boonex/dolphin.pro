<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');
bx_import('BxDolModuleDb');
bx_import('BxTemplSearchResult');

class BxDolInstallerUi extends BxDolDb
{
    var $_sDefVersion;
    var $_aCheckPathes;
    var $_aTypesConfig = array (
            'module' => array (
                'configfile' => '/install/config.php',
                'configvar' => 'aConfig',
                'configvarindex' => 'home_dir',
                'folder' => 'modules/',
                'subfolder' => '{configvar}',
            ),
            'update' => array (
                'configfile' => '/install/config.php',
                'configvar' => 'aConfig',
                'configvarindex' => 'home_dir',
                'folder' => 'modules/',
                'subfolder' => '{configvar}',
            ),
            'template' => array (
                'configfile' => '/scripts/BxTemplName.php',
                'configvar' => 'sTemplName',
                'folder' => 'templates/',
                'subfolder' => '{packagerootfolder}',
            ),
        );

    function __construct()
    {
        parent::__construct();

        $this->_sDefVersion = '0.0.0';
        $this->_aCheckPathes = array();
    }
    function getUploader($sResult, $sPackageTitleKey = '_adm_txt_modules_module', $bUnsetUpdate = false, $sAction = false)
    {
        $aForm = array(
            'form_attrs' => array(
                'id' => 'module_upload_form',
                'action' => $sAction ? $sAction : bx_html_attribute($_SERVER['PHP_SELF']),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ),
            'inputs' => array (
                'header1' => array(
                    'type' => 'block_header',
                    'caption' => _t('_adm_txt_modules_package_to_upload'),
                ),
                'module' => array(
                    'type' => 'file',
                    'name' => 'module',
                    'caption' => _t($sPackageTitleKey),
                ),
                'update' => array(
                    'type' => 'file',
                    'name' => 'update',
                    'caption' => _t('_adm_btn_modules_update'),
                ),
                'header2' => array(
                    'type' => 'block_header',
                    'caption' => _t('_adm_txt_modules_ftp_access'),
                ),
                'host' => array(
                    'type' => 'text',
                    'name' => 'host',
                    'caption' => _t('_adm_txt_modules_host'),
                    'value' => getParam('sys_ftp_login')
                ),
                'login' => array(
                    'type' => 'text',
                    'name' => 'login',
                    'caption' => _t('_adm_txt_modules_login'),
                    'value' => getParam('sys_ftp_login')
                ),
                'password' => array(
                    'type' => 'password',
                    'name' => 'password',
                    'caption' => _t('_Password'),
                    'value' => getParam('sys_ftp_password')
                ),
                'path' => array(
                    'type' => 'text',
                    'name' => 'path',
                    'caption' => _t('_adm_txt_modules_path_to_dolphin'),
                    'value' => !($sPath = getParam('sys_ftp_dir')) ? 'public_html/' : $sPath
                ),
                'submit_upload' => array(
                    'type' => 'submit',
                    'name' => 'submit_upload',
                    'value' => _t('_adm_box_cpt_upload'),
                )
            )
        );

        if ($bUnsetUpdate)
            unset($aForm['inputs']['update']);

        $oForm = new BxBaseFormView($aForm);
        $sContent = $oForm->getCode();

        if(!empty($sResult))
            $sContent = MsgBox(_t($sResult), 10) . $sContent;

        return $GLOBALS['oAdmTemplate']->parseHtmlByName('modules_uploader.html', array(
            'content' => $sContent
        ));
    }
    function getInstalled()
    {
        //--- Get Items ---//
        $oModules = new BxDolModuleDb();
        $aModules = $oModules->getModules();

        $aItems = array();
        foreach($aModules as $aModule) {
			if(strpos($aModule['dependencies'], $aModule['uri']) !== false)
        		continue;

            $sUpdate = '';
            if(in_array($aModule['path'], $this->_aCheckPathes)) {
            	$aCheckInfo = BxDolInstallerUi::checkForUpdates($aModule);
            	$sUpdate = $this->_parseUpdate($aCheckInfo);
            }

            $aItems[] = array(
                'name' => $aModule['uri'],
                'value' => $aModule['path'],
                'title'=> _t('_adm_txt_modules_title_module', $aModule['title'], !empty($aModule['version']) ? $aModule['version'] : $this->_sDefVersion, $aModule['vendor']),
            	'can_update' => isset($aModule['update_url']) && !empty($aModule['update_url']) ? 1 : 0,
                'update' => $sUpdate,
            );
        }

        //--- Get Controls ---//
        $aButtons = array(
            'modules-uninstall' => array('type' => 'submit', 'name' => 'modules-uninstall', 'value' => _t('_adm_btn_modules_uninstall'), 'onclick' => 'onclick="javascript: return ' . BX_DOL_ADM_MM_JS_NAME . '.onSubmitUninstall(this);"'),
            'modules-recompile-languages' => _t('_adm_btn_modules_recompile_languages')
        );

        $oZ = new BxDolAlerts('system', 'admin_modules_buttons', 0, 0, array(
        	'place' => 'installed',
		    'buttons' => &$aButtons,
		));
		$oZ->alert();

        $sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('modules_list.html', array(
            'type' => 'installed',
            'bx_repeat:items' => !empty($aItems) ? $aItems : MsgBox(_t('_Empty')),
            'controls' => BxTemplSearchResult::showAdminActionsPanel('modules-installed-form', $aButtons, 'pathes')
        ));

        $aTopMenu = array(
        	'modules-update' => array('title' => '_adm_btn_modules_update', 'href' => 'javascript:void(0)', 'onclick' => 'javascript:' . BX_DOL_ADM_MM_JS_NAME . '.checkForUpdates(this);')
        );

        $GLOBALS['oAdmTemplate']->addJsTranslation(array('_adm_txt_modules_data_will_be_lost'));
        return DesignBoxAdmin(_t('_adm_box_cpt_installed_modules'), $sContent, $aTopMenu);
    }
    function getNotInstalled($sResult)
    {
        //--- Get Items ---//
        $oModules = new BxDolModuleDb();
        $aModules = $oModules->getModules();

        $aInstalled = array();
        foreach($aModules as $aModule)
            $aInstalled[] = $aModule['path'];

        $aNotInstalled = array();
        $sPath = BX_DIRECTORY_PATH_ROOT . 'modules/';
        if($rHandleVendor = opendir($sPath)) {

            while(($sVendor = readdir($rHandleVendor)) !== false) {
                if(substr($sVendor, 0, 1) == '.' || !is_dir($sPath . $sVendor)) continue;

                if($rHandleModule = opendir($sPath . $sVendor)) {
                    while(($sModule = readdir($rHandleModule)) !== false) {
                        if(!is_dir($sPath . $sVendor . '/' . $sModule) || substr($sModule, 0, 1) == '.' || in_array($sVendor . '/' . $sModule . '/', $aInstalled))
                            continue;

                        $sConfigPath = $sPath . $sVendor . '/' . $sModule . '/install/config.php';
                        if(!file_exists($sConfigPath)) continue;

                        include($sConfigPath);
                        $aNotInstalled[$aConfig['title']] = array(
                            'name' => $aConfig['home_uri'],
                            'value' => $aConfig['home_dir'],
                            'title' => _t('_adm_txt_modules_title_module', $aConfig['title'], !empty($aConfig['version']) ? $aConfig['version'] : $this->_sDefVersion, $aConfig['vendor']),
                        	'can_update' => '0', 
                            'update' => ''
                        );
                    }
                    closedir($rHandleModule);
                }
            }
            closedir($rHandleVendor);
        }
        ksort($aNotInstalled);

        //--- Get Controls ---//
        $aButtons = array(
            'modules-install' => _t('_adm_btn_modules_install'),
            'modules-delete' => _t('_adm_btn_modules_delete')
        );

        $oZ = new BxDolAlerts('system', 'admin_modules_buttons', 0, 0, array(
        	'place' => 'uninstalled',
		    'buttons' => &$aButtons,
		));
		$oZ->alert();

        $sControls = BxTemplSearchResult::showAdminActionsPanel('modules-not-installed-form', $aButtons, 'pathes');

        if(!empty($sResult))
            $sResult = MsgBox(_t($sResult), 10);

        return $sResult . $GLOBALS['oAdmTemplate']->parseHtmlByName('modules_list.html', array(
            'type' => 'not-installed',
            'bx_repeat:items' => !empty($aNotInstalled) ? array_values($aNotInstalled) : MsgBox(_t('_Empty')),
            'controls' => $sControls
        ));
    }
    function getUpdates($sResult)
    {
        $aUpdates = array();
        $sPath = BX_DIRECTORY_PATH_ROOT . 'modules/';
        if($rHandleVendor = opendir($sPath)) {
            while(($sVendor = readdir($rHandleVendor)) !== false) {
                if(substr($sVendor, 0, 1) == '.' || !is_dir($sPath . $sVendor))
                    continue;

                if($rHandleModule = opendir($sPath . $sVendor . '/')) {
                    while(($sModule = readdir($rHandleModule)) !== false) {
                        if(!is_dir($sPath . $sVendor . '/' . $sModule) || substr($sModule, 0, 1) == '.')
                            continue;

                        if($rHandleUpdate = @opendir($sPath . $sVendor . '/' . $sModule . '/updates/')) {
                            while(($sUpdate = readdir($rHandleUpdate)) !== false) {
                                if(!is_dir($sPath . $sVendor . '/' . $sModule . '/updates/' . $sUpdate) || substr($sUpdate, 0, 1) == '.')
                                    continue;

                                $sConfigPath = $sPath . $sVendor . '/' . $sModule . '/updates/' . $sUpdate . '/install/config.php';
                                if(!file_exists($sConfigPath))
                                    continue;

                                include($sConfigPath);
                                $sName = $aConfig['title'] . $aConfig['module_uri'] . $aConfig['version_from'] . $aConfig['version_to'];
                                $aUpdates[$sName] = array(
                                    'name' => md5($sName),
                                    'value' => $aConfig['home_dir'],
                                    'title' => _t('_adm_txt_modules_title_update', $aConfig['title'], $aConfig['version_from'], $aConfig['version_to']),
                                	'can_update' => '0',
                                	'update' => ''
                                );
                            }
                            closedir($rHandleUpdate);
                        }
                    }
                    closedir($rHandleModule);
                }
            }
            closedir($rHandleVendor);
        }
        ksort($aUpdates);

        //--- Get Controls ---//
        $aButtons = array(
            'updates-install' => _t('_adm_btn_modules_install'),
            'updates-delete' => _t('_adm_btn_modules_delete')
        );
        $sControls = BxTemplSearchResult::showAdminActionsPanel('modules-updates-form', $aButtons, 'pathes');

        if(!empty($sResult))
            $sResult = MsgBox(_t($sResult), 10);

        return $sResult . $GLOBALS['oAdmTemplate']->parseHtmlByName('modules_list.html', array(
            'type' => 'updates',
            'bx_repeat:items' => !empty($aUpdates) ? array_values($aUpdates) : MsgBox(_t('_Empty')),
            'controls' => $sControls
        ));
    }

    //--- Get/Set methods ---//
    function setCheckPathes($aPathes)
    {
        $this->_aCheckPathes = is_array($aPathes) ? $aPathes : array();
    }

    //--- Actions ---//
    function actionUpload($sType, $aFile, $aFtpInfo)
    {
    	$sHost = htmlspecialchars_adv(clear_xss($aFtpInfo['host']));
        $sLogin = htmlspecialchars_adv(clear_xss($aFtpInfo['login']));
        $sPassword = htmlspecialchars_adv(clear_xss($aFtpInfo['password']));
        $sPath = htmlspecialchars_adv(clear_xss($aFtpInfo['path']));

        setParam('sys_ftp_host', $sHost);
        setParam('sys_ftp_login', $sLogin);
        setParam('sys_ftp_password', $sPassword);
        setParam('sys_ftp_dir', $sPath);

        $sErrMsg = false;

        $sName = time();
        $sAbsolutePath = BX_DIRECTORY_PATH_ROOT . "tmp/" . $sName . '.zip';
        $sPackageRootFolder = false;

        if (!class_exists('ZipArchive'))
            $sErrMsg = '_adm_txt_modules_zip_not_available';

        if (!$sErrMsg && $this->_isArchive($aFile['type']) && move_uploaded_file($aFile['tmp_name'], $sAbsolutePath)) {

            // extract uploaded zip package into tmp folder

            $oZip = new ZipArchive();
            if ($oZip->open($sAbsolutePath) !== TRUE)
                $sErrMsg = '_adm_txt_modules_cannot_unzip_package';

            if (!$sErrMsg) {
                $sPackageRootFolder = $oZip->numFiles > 0 ? $oZip->getNameIndex(0) : false;

                if (file_exists(BX_DIRECTORY_PATH_ROOT . 'tmp/' . $sPackageRootFolder)) // remove existing tmp folder with the same name
                    bx_rrmdir(BX_DIRECTORY_PATH_ROOT . 'tmp/' . $sPackageRootFolder);

                if ($sPackageRootFolder && !$oZip->extractTo(BX_DIRECTORY_PATH_ROOT . 'tmp/'))
                    $sErrMsg = '_adm_txt_modules_cannot_unzip_package';

                $oZip->close();
            }

            // upload files to the correct folder via FTP

            if (!$sErrMsg && $sPackageRootFolder) {

                $oFtp = new BxDolFtp(!empty($sHost) ? $sHost : $_SERVER['HTTP_HOST'], $sLogin, $sPassword, $sPath);

                if (!$oFtp->connect())
                    $sErrMsg = '_adm_txt_modules_cannot_connect_to_ftp';

                if (!$sErrMsg && !$oFtp->isDolphin())
                    $sErrMsg = '_adm_txt_modules_destination_not_valid';

                if (!$sErrMsg) {
                    $sConfigPath = BX_DIRECTORY_PATH_ROOT . "tmp/" . $sPackageRootFolder . $this->_aTypesConfig[$sType]['configfile'];
                    if (file_exists($sConfigPath)) {
                        include($sConfigPath);
                        $sConfigVar = !empty($this->_aTypesConfig[$sType]['configvarindex']) ? ${$this->_aTypesConfig[$sType]['configvar']}[$this->_aTypesConfig[$sType]['configvarindex']] : ${$this->_aTypesConfig[$sType]['configvar']};
                        $sSubfolder = $this->_aTypesConfig[$sType]['subfolder'];
                        $sSubfolder = str_replace('{configvar}', $sConfigVar, $sSubfolder);
                        $sSubfolder = str_replace('{packagerootfolder}', $sPackageRootFolder, $sSubfolder);
                        if (!$oFtp->copy(BX_DIRECTORY_PATH_ROOT . "tmp/" . $sPackageRootFolder . '/', $this->_aTypesConfig[$sType]['folder'] . $sSubfolder))
                            $sErrMsg = '_adm_txt_modules_ftp_copy_failed';
                    } else {
                        $sErrMsg = '_adm_txt_modules_wrong_package_format';
                    }
                }

            } else {
                $sErrMsg = '_adm_txt_modules_cannot_unzip_package';
            }

            // remove temporary files
            bx_rrmdir(BX_DIRECTORY_PATH_ROOT . 'tmp/' . $sPackageRootFolder);
            unlink($sAbsolutePath);

        } else {
            $sErrMsg = '_adm_txt_modules_cannot_upload_package';
        }

        return $sErrMsg ? $sErrMsg : '_adm_txt_modules_success_upload';
    }
    function actionInstall($aDirectories)
    {
        return $this->_perform($aDirectories, 'install');
    }
    function actionUninstall($aDirectories)
    {
        return $this->_perform($aDirectories, 'uninstall');
    }
    function actionRecompile($aDirectories)
    {
        return $this->_perform($aDirectories, 'recompile');
    }
    function actionUpdate($aDirectories)
    {
        return $this->_perform($aDirectories, 'update');
    }
    function actionDelete($aDirectories, $sType = 'module')
    {
    	$sFtpHost = getParam('sys_ftp_host');
		if(empty($sFtpHost))
			$sFtpHost = $_SERVER['HTTP_HOST'];

        $oFtp = new BxDolFtp($sFtpHost, getParam('sys_ftp_login'), getParam('sys_ftp_password'), getParam('sys_ftp_dir'));
        if (!$oFtp->connect())
            return '_adm_txt_modules_cannot_connect_to_ftp';

        $sDir = $this->_aTypesConfig[$sType]['folder'];
        foreach ($aDirectories as $sDirectory)
            if (!$oFtp->delete($sDir . $sDirectory))
                return '_adm_txt_modules_cannot_remove_package';

        return '_adm_txt_modules_success_delete';
    }
	function checkForUpdatesByPath($sPath)
    {
    	bx_import('BxDolModuleDb');
    	$oModuleDb = new BxDolModuleDb();
        $aModule = $oModuleDb->getModulesBy(array('type' => 'path', 'value' => $sPath));

        $aResult = self::checkForUpdates($aModule);
        $aResult['content'] = $this->_parseUpdate($aResult);
        return $aResult;
    }
    function downloadUpdate($sLink)
    {
    	$sName = time() . '.zip';
    	$sData = bx_file_get_contents($sLink);

		//--- write ZIP archive.
		$sTmpPath = BX_DIRECTORY_PATH_ROOT . 'tmp/';
		$sFilePath = $sTmpPath . $sName;
		if (!$rHandler = fopen($sFilePath, 'w'))
			return _t('_adm_txt_modules_cannot_download_package');

		if (!fwrite($rHandler, $sData))
			return _t('_adm_txt_modules_cannot_write_package');

    	fclose($rHandler);

    	//--- Unarchive package.
    	if(!class_exists('ZipArchive'))
            return _t('_adm_txt_modules_zip_not_available');

		$oZip = new ZipArchive();
		if($oZip->open($sFilePath) !== true)
        	return _t('_adm_txt_modules_cannot_unzip_package');

        $sPackageRootFolder = $oZip->numFiles > 0 ? $oZip->getNameIndex(0) : false;
		if($sPackageRootFolder && file_exists($sTmpPath . $sPackageRootFolder)) // remove existing tmp folder with the same name
        	bx_rrmdir($sTmpPath . $sPackageRootFolder);

		if($sPackageRootFolder && !$oZip->extractTo($sTmpPath))
        	return _t('_adm_txt_modules_cannot_unzip_package');

		$oZip->close();

		//--- Move unarchived package.
		$sHost = getParam('sys_ftp_host');
		$sLogin = getParam('sys_ftp_login');
		$sPassword = getParam('sys_ftp_password');
		$sPath = getParam('sys_ftp_dir');
		if(empty($sLogin) || empty($sPassword) || empty($sPath))
			return _t('_adm_txt_modules_no_ftp_info');

		bx_import('BxDolFtp');
		$oFtp = new BxDolFtp(!empty($sHost) ? $sHost : $_SERVER['HTTP_HOST'], $sLogin, $sPassword, $sPath);

		if(!$oFtp->connect())
        	return _t('_adm_txt_modules_cannot_connect_to_ftp');

		if(!$oFtp->isDolphin())
			return _t('_adm_txt_modules_destination_not_valid');

		$sConfigPath = $sTmpPath . $sPackageRootFolder . '/install/config.php';
		if(!file_exists($sConfigPath))
			return _t('_adm_txt_modules_wrong_package_format');

		include($sConfigPath);
		if(empty($aConfig) || empty($aConfig['home_dir']) || !$oFtp->copy($sTmpPath . $sPackageRootFolder . '/', 'modules/' . $aConfig['home_dir']))
			return _t('_adm_txt_modules_ftp_copy_failed');

        return true;
    }

    //--- Static methods ---//
    public static function checkForUpdates($aModule)
    {
    	if(empty($aModule['update_url']))
    		return array();

        $sData = bx_file_get_contents($aModule['update_url'], array(
            'uri' => $aModule['uri'],
            'path' => $aModule['path'],
            'version' => $aModule['version'],
            'domain' => $_SERVER['HTTP_HOST']
        ));

        $aValues = $aIndexes = array();
        $rParser = xml_parser_create('UTF-8');
        xml_parse_into_struct($rParser, $sData, $aValues, $aIndexes);
        xml_parser_free($rParser);

        $aInfo = array();
        if(isset($aIndexes['VERSION']))
            $aInfo['version'] = $aValues[$aIndexes['VERSION'][0]]['value'];
        if(isset($aIndexes['LINK']))
			$aInfo['link'] = $aValues[$aIndexes['LINK'][0]]['value'];
		if(isset($aIndexes['PACKAGE']))
			$aInfo['package'] = $aValues[$aIndexes['PACKAGE'][0]]['value'];

        return $aInfo;
    }

    //--- Protected methods ---//
    function _parseUpdate($aInfo) {
    	$bAvailable = !empty($aInfo) && is_array($aInfo);

    	return $GLOBALS['oAdmTemplate']->parseHtmlByName('modules_update.html', array(
    		'bx_if:show_available' => array(
    			'condition' => $bAvailable,
    			'content' => array(
					'text' => _t('_adm_txt_modules_update_text', empty($aInfo['version']) ? '' : $aInfo['version']),
		            'bx_if:show_update_view' => array(
		            	'condition' => !empty($aInfo['link']),
		            	'content' => array(
		            		'link' => !empty($aInfo['link']) ? $aInfo['link'] : '',
		            	)
		            ),
		            'bx_if:show_update_download' => array(
		            	'condition' => !empty($aInfo['package']),
		            	'content' => array(
		            		'js_object' => BX_DOL_ADM_MM_JS_NAME,
		            		'link' => !empty($aInfo['package']) ? $aInfo['package'] : '',
		            	)
		            )
				),
			),
			'bx_if:show_latest' => array(
            	'condition' => !$bAvailable,
                'content' => array()
			)
    	));
    }
    function _perform($aDirectories, $sOperation, $aParams = array())
    {
        $sConfigFile = 'install/config.php';
        $sInstallerFile = 'install/installer.php';
        $sInstallerClass = $sOperation == 'update' ? 'Updater' : 'Installer';

        $aPlanks = array();
        foreach($aDirectories as $sDirectory) {
            $sPathConfig = BX_DIRECTORY_PATH_MODULES . $sDirectory . $sConfigFile;
            $sPathInstaller = BX_DIRECTORY_PATH_MODULES . $sDirectory . $sInstallerFile;
            if(file_exists($sPathConfig) && file_exists($sPathInstaller)) {
                include($sPathConfig);
                require_once($sPathInstaller);

                $sClassName = $aConfig['class_prefix'] . $sInstallerClass;
                $oInstaller = new $sClassName($aConfig);
                $aResult = $oInstaller->$sOperation($aParams);

                bx_import('BxDolAlerts');
                $o = new BxDolAlerts('module', $sOperation, 0, 0, array('uri' => $aConfig['home_uri'], 'config' => $aConfig, 'installer' => $oInstaller, 'res' => $aResult));
                $o->alert();

                if(!$aResult['result'] && empty($aResult['message']))
                   continue;
            } else
                $aResult = array(
                    'operation_title' => _t('_adm_txt_modules_process_operation_failed', $sOperation, $sDirectory),
                    'message' => ''
                );

            $aPlanks[] = array(
                'operation_title' => $aResult['operation_title'],
                'bx_if:operation_result_success' => array(
                    'condition' => $aResult['result'],
                    'content' => array()
                ),
                'bx_if:operation_result_failed' => array(
                    'condition' => !$aResult['result'],
                    'content' => array()
                ),
                'message' => $aResult['message']
            );
        }

        return $GLOBALS['oAdmTemplate']->parseHtmlByName('modules_results.html', array(
            'bx_repeat:planks' => $aPlanks
        ));
    }
    function _isArchive($sType)
    {
        $bResult = false;
        switch($sType) {
            case 'application/zip':
            case 'application/x-zip-compressed':
                $bResult = true;
                break;
        }
        return $bResult;
    }
}
