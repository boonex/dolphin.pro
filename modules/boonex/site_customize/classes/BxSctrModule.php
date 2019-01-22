<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_PLUGINS . 'myadmin/zip.lib.php');
require_once(BX_DIRECTORY_PATH_PLUGINS . 'myadmin/unzip.lib.php');

bx_import('BxDolModule');

define('BX_SCTR_DIR_IMAGES', 'data/images/');
define('BX_SCTR_SMALL_PREFIX', 's_');
define('BX_SCTR_THEME_PREFIX', 't_');
define('BX_SCTR_THUMB_EXT', '.jpg');
define('BX_SCTR_THEME_CONF', 'conf.php');
define('BX_SCTR_THEME_THUMB', 'thumb.jpg');
define('BX_SCTR_IMAGES_DELETE', 0);
define('BX_SCTR_IMAGES_COPY', 1);

function bx_sctr_import($sClassPostfix, $aModuleOverwright = array())
{
    global $aModule;
    $a = $aModuleOverwright ? $aModuleOverwright : $aModule;
    if (!$a || $a['uri'] != 'site_customize') {
        $oMain = BxDolModule::getInstance('BxSctrModule');
        $a = $oMain->_aModule;
    }
    bx_import ($sClassPostfix, $a) ;
}

/**
 * Profile customizer module
 *
 * This module allow users to customize profile page,
 * users can change backgrounds, fonts, borders and create new themes for it.
 *
 *
 *
 * Service methods:
 *
 * Get block for for customized page
 * @see BxSctrModule::serviceGetCustomizeBlock
 * BxDolService::call('profile_customize', 'get_customize_block', array($sPage, $sTarget));
 *
 * Get custom styles for current profile page
 * @see BxSctrModule::serviceGetProfileStyle
 * BxDolService::call('profile_customize', 'get_profile_style', array($iProfileId));
 *
 *
 * Example for add new customize block:
 *
 * You necessary to add new record in table `bx_profile_custom_units`
 *
 *  name - unique name for customize block
 *  caption - this caption show in select menu
 *  css_name - name of css style(class, id or element name) which need to customize
 *  type - one of the following types: "background", "font", "border"
 *
 */
class BxSctrModule extends BxDolModule
{
	var $iUserId;
    var $_aCssMatch;

    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);
        $this->iUserId = isLogged() && isAdmin() ? getLoggedId() : 0;

        $this->_oConfig->init($this->_oDb);
        $this->_oTemplate->initialize($this, $this->_oDb);

        $this->_aCssMatch = $this->_oDb->getUnits();
    }

    function actionCustomizePage($sPage = '', $sTarget = '')
    {
    	$this->_oConfig->cancelSession();

    	header('Content-Type: text/html; charset=utf-8');
        echo $this->_getCustomizeBlock($sPage, $sTarget);
    }

    function actionOpen($iOpen)
    {
    	if (!$this->iUserId)
            return;

		if((int)$iOpen != 0)
			$this->_oConfig->doOpen();
		else 
			$this->_oConfig->doClose();
    }

    function actionSave($isReset = '')
    {
        if (!$this->iUserId)
            return;

		$sPage = $sTarget = '';
        if (isset($_POST['page']) && isset($_POST['trg'])) {
            $sPage = process_db_input($_POST['page'], BX_TAGS_STRIP);
            $sTarget = process_db_input($_POST['trg'], BX_TAGS_STRIP);

            unset($_POST['page']);
            unset($_POST['trg']);

            $aTmpStyle = $this->_oDb->getSiteTmp();

            if (!$isReset) {
                if (!empty($aTmpStyle) && isset($aTmpStyle[$sPage][$sTarget])) {
                    foreach ($aTmpStyle[$sPage][$sTarget] as $sKey => $sValue) {
                        if ($sKey != 'image')
                            unset($aTmpStyle[$sPage][$sTarget][$sKey]);
                    }
                }
                $aVars = $_POST;
                if (isset($_FILES['image'])) {
                    $sImage = $this->_addImage('image');
                    if (strlen($sImage) > 0) {
                        if (isset($aTmpStyle[$sPage][$sTarget]['image']))
                            $this->_deleteImage($aTmpStyle[$sPage][$sTarget]['image']);

                        $aTmpStyle[$sPage][$sTarget]['image'] = $sImage;
                        if (!isset($aVars['useimage']))
                            $aVars['useimage'] = 'on';
                    }
                }

                foreach ($aVars as $sKey => $sValue) {
                    if ($sValue != '' && $sValue != 'default' && $sValue != '-1')
                        $aTmpStyle[$sPage][$sTarget][$sKey] = process_db_input($sValue, BX_TAGS_STRIP);
                }
            }
            else if(!empty($aTmpStyle) && isset($aTmpStyle[$sPage][$sTarget])) {
                $this->_parseImages($aTmpStyle[$sPage][$sTarget], BX_SCTR_IMAGES_DELETE);
                unset($aTmpStyle[$sPage][$sTarget]);
            }

            $this->_oDb->updateSiteTmp($aTmpStyle);
        }

    	if(isset($_POST['action']) && !empty($_POST['action'])) {
			$sAction = process_db_input($_POST['action'], BX_TAGS_STRIP);
            unset($_POST['action']);

            $iTheme = 0;
            if(isset($_POST['theme']) && !empty($_POST['theme'])) {
            	$iTheme = (int)$_POST['theme'];
            	unset($_POST['theme']);
			}

            if($sAction == 'reset')
            	$this->_oConfig->setSessionData(array($sPage, $sTarget, 0, ''));
			else
            	$this->_oConfig->setSessionData(array($sPage, $sTarget, $iTheme, $sAction));
		}

        echo 'Ok';
    }

    function actionPublish($isSave = '')
    {
        if (!$this->iUserId)
            return;

        $sComplete = '';

        if ($isSave && isset($_POST['name_theme']) && $_POST['name_theme']) {
            $sName = process_db_input($_POST['name_theme'], BX_TAGS_STRIP);
            $aTheme = $this->_oDb->getThemeByName($sName);

            if (empty($aTheme)) {
                $iThemeId = $this->_oDb->addTheme($sName, ($this->isAdmin() ? 0 : $this->iUserId), $this->_getThemeFromTmp());

                if ($iThemeId != -1) {
                    $sThumb = 'thumbnail';

                    if (isset($_FILES[$sThumb]) && is_uploaded_file($_FILES[$sThumb]['tmp_name'])) {
                        if (strpos($_FILES[$sThumb]['type'], 'image') !== false) {
                            $sDestDir = $this->_getImagesDir();
                            $sExt = '.' . pathinfo($_FILES[$sThumb]['name'], PATHINFO_EXTENSION);
                            $sTmpName = 'tmp_' . time() . $this->iUserId . $sExt;
                            $sThumbName = BX_SCTR_THEME_PREFIX . $iThemeId . BX_SCTR_THUMB_EXT;

                            if (move_uploaded_file($_FILES[$sThumb]['tmp_name'],  $sDestDir . $sTmpName)) {
                                imageResize($sDestDir . $sTmpName, $sDestDir . $sThumbName, 64, 64, true);
                                unlink($sDestDir . $sTmpName);
                            }
                        } else
                            unlink($_FILES[$sThumb]['tmp_name']);
                    }

                    $sComplete = _t('_bx_sctr_complete');
                } else
                    $sComplete = _t('_bx_sctr_err_add_theme');
            } else
                $sComplete = sprintf(_t('_bx_sctr_err_already_exist'), $sName);
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $this->_oTemplate->getPublishForm($sComplete);
    }

    function actionSaveTheme($iThemeId)
    {
        $iThemeId = (int)$iThemeId;

        if (!$this->iUserId)
            return;

        $sCss = '';
        $aTheme = $this->_oDb->getThemeStyle($iThemeId);

        if (!empty($aTheme)) {
            $aStyles = $this->_oDb->getSite();
            if (!empty($aStyles)) {
                $this->_parseImages(unserialize($aStyles['css']), BX_SCTR_IMAGES_DELETE);
                $this->_parseImages(unserialize($aStyles['tmp']), BX_SCTR_IMAGES_DELETE);
            }

            $this->_parseImages($aTheme, BX_SCTR_IMAGES_COPY);
            $this->_parseImages($aTheme, BX_SCTR_IMAGES_COPY);

            $this->_oDb->updateSiteTmp($aTheme);
            $this->_oDb->updateSiteCss($aTheme);

            $this->_oConfig->cancelSession();
        }
    }

    function actionDeleteTheme($iThemeId)
    {
        if(!$this->iUserId)
            return;

        $this->_deleteTheme((int)$iThemeId);

        header('Content-Type: text/html; charset=utf-8');
        echo $this->serviceGetCustomizeBlock('themes');
    }

    function actionResetAll()
    {
        if (!$this->iUserId)
            return;

        $aStyles = $this->_oDb->getSite();
        if (!empty($aStyles)) {
            $this->_parseImages(unserialize($aStyles['css']), BX_SCTR_IMAGES_DELETE);
            $this->_parseImages(unserialize($aStyles['tmp']), BX_SCTR_IMAGES_DELETE);
            $this->_oDb->resetSite();
        }
    }

    /**
     * Admin actions
     */

    function actionAdministration($sType = '', $iUnitId = '')
    {
        $iUnitId = (int)$iUnitId;

        if (!$this->isAdmin()) {
            // TODO: show access denied
            return;
        }

        $this->_oTemplate->addAdminCss(array('forms_adv.css', 'main.css', 'admin.css'));
        $this->_oTemplate->addAdminJs(array('main.js'));

        $this->_oTemplate->pageCodeAdmin (_t('_bx_sctr_administration'), $sType, $iUnitId, $this->_checkActions());
    }

    /**
     * Service methods
     */
    function serviceGetCustomizeButton()
    {
        if(!isAdmin() || !$this->_oConfig->isEnabled())
            return '';

    	return array(
            'evalResult' => 'oBxSctrMain.showBlock();',
            'evalResultCssClassWrapper' => 'bx-phone-hide'
        );
    }
    function serviceGetCustomizeBlock($sPage = '', $sTarget = '')
    {
    	if(!$this->_oConfig->isEnabled())
    		return '';

    	return $this->_oTemplate->parseHtmlByName('customize_block_wrp.html', array(
    		'name_id' => 'site_customize',
    		'bx_if:hide' => array(
        		'condition' => !$this->_oConfig->isOpen(),
        		'content' => array()
        	),
    		'content' => $this->_getCustomizeBlock($sPage, $sTarget)
    	));
    }

    function serviceGetSiteStyle()
    {
    	if(!$this->_oConfig->isEnabled())
    		return '';

		list($sPage, $sTarget, $iTheme, $sAction) = $this->_oConfig->getSessionData();

		$sCss = '';
        switch ($sAction) {
            case 'save':
                $aStyles = $this->_oDb->getSite();
                if (!empty($aStyles)) {
                    $this->_parseImages(unserialize($aStyles['css']), BX_SCTR_IMAGES_DELETE);
                    $this->_parseImages(unserialize($aStyles['tmp']), BX_SCTR_IMAGES_COPY);
                    $this->_oDb->saveSite();
                    $sCss .= $this->_getCssFromArray($this->_oDb->getSiteCss());
                }

                $this->_oConfig->setSessionData(array($sPage, $sTarget, $iTheme, ''));
                break;

            case 'preview':
                $sCss .= $this->_getCssFromArray($this->_oDb->getSiteTmp());
                break;

            case 'theme':
                $sCss .= $this->_getCssFromArray($this->_oDb->getThemeStyle($iTheme));
                break;

            default:
                $sCss .= $this->_getCssFromArray($this->_oDb->getSiteCss());
        }

        return '<style type="text/css">' . $sCss . '</style>'; 
    }

    function isAdmin()
    {
        return isAdmin($this->iUserId);
    }

    function _getImagesPath()
    {
        return BX_DOL_URL_MODULES . $this->_aModule['path'] . BX_SCTR_DIR_IMAGES;
    }

    function _getImagesDir()
    {
        return BX_DIRECTORY_PATH_MODULES . $this->_aModule['path'] . BX_SCTR_DIR_IMAGES;
    }

    function _addImage($sName)
    {
        if (isset($_FILES[$sName]) && is_uploaded_file($_FILES[$sName]['tmp_name'])) {
            if (strpos($_FILES[$sName]['type'], 'image') !== false) {
                $sExt = pathinfo($_FILES[$sName]['name'], PATHINFO_EXTENSION);
                $sFileName = $this->_oDb->addImage($sExt);
                if ($sFileName) {
                    $sDestDir = $this->_getImagesDir();
                    if (move_uploaded_file($_FILES[$sName]['tmp_name'],  $sDestDir . $sFileName)) {
                        imageResize($sDestDir . $sFileName, $sDestDir . BX_SCTR_SMALL_PREFIX . $sFileName, 64, 64);
                        return $sFileName;
                    }
                }
            } else
                unlink($_FILES[$sName]['tmp_name']);
        }

        return '';
    }

    function _deleteImage($sFileName)
    {
        if ($this->_oDb->deleteImage($sFileName)) {
            $sDestDir = $this->_getImagesDir();
            if (file_exists($sDestDir . $sFileName))
                unlink($sDestDir . $sFileName);
            if (file_exists($sDestDir . BX_SCTR_SMALL_PREFIX . $sFileName))
                unlink($sDestDir . BX_SCTR_SMALL_PREFIX . $sFileName);
        }
    }

    function _convertFormVarToTmpStyle($sPage, $sTarget, $aVars, &$aResult, $bFiles = false)
    {
        foreach ($aVars as $sKey => $sValue) {
            if ($bFiles)
                $sValue = $this->_addImage($sKey);

            if ($sValue != '' && $sValue != 'default' && $sValue != '-1')
                $aResult[$sPage][$sTarget][$sKey] = $sValue;
        }
    }

    function _getCssFromArray($aTmpStyle)
    {
        $bPageBackgroundChanged = false;
        $sCss = '';

        if (empty($aTmpStyle))
            return '';

        foreach ($aTmpStyle as $sKey => $aValue) {
            if (!isset($this->_aCssMatch[$sKey]))
                continue;

            foreach ($aValue as $sValKey => $aParam) {
                if (!isset($this->_aCssMatch[$sKey][$sValKey]['css_name']))
                    continue;

                $sPartCss = $this->_aCssMatch[$sKey][$sValKey]['css_name'] . ' {';

                $sMethod = '_compile' . ucfirst($sKey);
                $s = method_exists($this, $sMethod) ? call_user_func_array(array($this, $sMethod), array($aParam)) : '';
                if ('bgbody' == $sValKey && 'background' == $sKey && '' != $s && 'background-image: none;' != $s)
                    $bPageBackgroundChanged = true;
                $sPartCss .= $s;

                $sPartCss .= ' }';
                $sCss .= $sPartCss;
            }
        }

        if ($bPageBackgroundChanged)
            $sCss .= ' html div.sys_root_bg {display:none;} ';

        return $sCss;
    }

    function _getThemeFromTmp()
    {
        $aTmpStyle = $this->_oDb->getSiteTmp();

        if (empty($aTmpStyle))
            return '';

        $this->_parseImages($aTmpStyle, BX_SCTR_IMAGES_COPY);

        return serialize($aTmpStyle);
    }

    function _parseImages($aCss, $iOperation)
    {
        if (empty($aCss))
            return;

        foreach ($aCss as $sKey => $mixedValue) {
            if (!is_array($mixedValue)) {
                if ($sKey == 'image') {
                    switch ($iOperation) {
                        case BX_SCTR_IMAGES_DELETE:
                            $this->_deleteImage($mixedValue);
                            break;

                        case BX_SCTR_IMAGES_COPY:
                            $this->_oDb->copyImage($mixedValue);
                            break;
                    }
                }
            } else
                $this->_parseImages($mixedValue, $iOperation);
        }
    }

    function _getImages($aCss)
    {
        $aResult = array();

        if (empty($aCss))
            return $aResult;

        foreach ($aCss as $sKey => $mixedValue) {
            if (!is_array($mixedValue)) {
                if ($sKey == 'image')
                    $aResult[] = $mixedValue;
            } else
                $aResult = array_merge($aResult, $this->_getImages($mixedValue));
        }

        return $aResult;
    }

    function _importImages($aCss, $oZip, $aImages)
    {
        $aResult = array();

        if (empty($aCss))
            return $aResult;

        foreach ($aCss as $sKey => $mixedValue) {
            if (!is_array($mixedValue)) {
                if ($sKey == 'image') {
                    $sExt = pathinfo($mixedValue, PATHINFO_EXTENSION);
                    $sFileName = $this->_oDb->addImage($sExt);
                    if ($sFileName) {
                        $sDestDir = $this->_getImagesDir();
                        $oFile = fopen($sDestDir . $sFileName, 'w', false);
                        if ($oFile) {
                            fwrite($oFile, $oZip->GetData($aImages[$mixedValue]));
                            fclose($oFile);
                            imageResize($sDestDir . $sFileName, $sDestDir . BX_SCTR_SMALL_PREFIX . $sFileName, 64, 64);
                            $aResult[$sKey] = $sFileName;
                        }
                    }
                } else
                    $aResult[$sKey] = $mixedValue;
            } else
                $aResult[$sKey] = $this->_importImages($mixedValue, $oZip, $aImages);
        }

        return $aResult;
    }

    function _saveCss()
    {
        $sTmpCss = $this->_getCssFromArray($this->_oDb->getSiteTmp());
        $this->_oDb->updateSiteCss($this->iUserId, $sTmpCss);
    }

    function _deleteTheme($iThemeId)
    {
        $sResult = _t('_bx_sctr_err_delete_theme');

        if (!$iThemeId)
            return $sResult;

        $aTheme = $this->_oDb->getThemeById($iThemeId);
        if (!empty($aTheme) && $this->_oDb->deleteTheme($iThemeId)) {
            $sFile = $this->_getImagesDir() . BX_SCTR_THEME_PREFIX . $iThemeId . BX_SCTR_THUMB_EXT;
            if (file_exists($sFile))
                unlink($sFile);
            $this->_parseImages(unserialize($aTheme['css']), BX_SCTR_IMAGES_DELETE);

            $sResult = _t('_bx_sctr_delete_complete');
        }

        return $sResult;
    }

    function _checkActions()
    {
        $sResult = '';
        $sFileImport = 'theme_file';

        if ($_POST['theme']) {
            if ($_POST['action_theme_export'])
                $this->_exportTheme($_POST['theme']);

            if ($_POST['action_theme_delete'])
                $sResult = $this->_deleteTheme($_POST['theme']);
        } else if (isset($_FILES[$sFileImport]) && is_uploaded_file($_FILES[$sFileImport]['tmp_name'])) {
            $sResult = $this->_importTheme($sFileImport);
            unlink($_FILES[$sFileImport]['tmp_name']);
        }

        return $sResult;
    }

    function _exportTheme($iThemeId)
    {
        $aTheme = $this->_oDb->getThemeById($iThemeId);

        if (empty($aTheme))
            return;

        $sConf = "\$sThemeName = '{$aTheme['name']}';\n";
        $sConf .= "\$sThemeStyle = '{$aTheme['css']}';\n";

        $sImagesPath = $this->_getImagesDir();
        $oZipFile = new zipfile();

        $oZipFile->addFile($sConf, BX_SCTR_THEME_CONF);
        $sFile = $sImagesPath . BX_SCTR_THEME_PREFIX . $iThemeId . BX_SCTR_THUMB_EXT;
        if (file_exists($sFile)) {
            $oData = implode("", file($sFile));
            $oZipFile->addFile($oData, BX_SCTR_THEME_THUMB);
        }

        $aImages = $this->_getImages(unserialize($aTheme['css']));
        foreach ($aImages as $sImage) {
            $sFile = $sImagesPath . $sImage;
            if (file_exists($sFile)) {
                $oData = implode("", file($sFile));
                $oZipFile->addFile($oData, 'images/' . $sImage);
            }
        }

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename={$aTheme['name']}.dfn");
        echo $oZipFile->file();
    }

    function _importTheme($sFileImport)
    {
        $sResult = '';
        $sDestDir = $this->_getImagesDir();

        if (!$sFileImport)
            return $sResult;

        if (pathinfo($_FILES[$sFileImport]['name'], PATHINFO_EXTENSION) != 'dfn')
            return _t('_bx_sctr_err_format');

        $oUnZip = new SimpleUnzip($_FILES[$sFileImport]['tmp_name']);
        $aFiles = $this->_getZipFilesFromPath($oUnZip, '');

        // check exist 'conf.php'
        if (!isset($aFiles[BX_SCTR_THEME_CONF]))
            return sprintf(_t('_bx_sctr_err_conf_php'), 'conf.php');

        eval($oUnZip->GetData($aFiles[BX_SCTR_THEME_CONF]));

        // check parameters
        if (!isset($sThemeName) || !isset($sThemeStyle))
            return _t('_bx_sctr_err_theme_parameters');

        // check exist theme
        $aTheme = $this->_oDb->getThemeByName($sThemeName);
        if (!empty($aTheme))
            return sprintf(_t('_bx_sctr_err_already_exist'), $sThemeName);

        $aImages = $this->_getZipFilesFromPath($oUnZip, 'images');
        if (!empty($aImages))
            $sStyle = serialize($this->_importImages(unserialize($sThemeStyle), $oUnZip, $aImages));
        else
            $sStyle = '';

        // insert in table
        $iThemeId = $this->_oDb->addTheme($sThemeName, 0, $sStyle);
        if ($iThemeId == -1)
            return _t('_bx_sctr_err_add_theme');

        // copy thumbnail
        if (isset($aFiles[BX_SCTR_THEME_THUMB])) {
            $sThumbName = BX_SCTR_THEME_PREFIX . $iThemeId . BX_SCTR_THUMB_EXT;

            $oFile = fopen($sDestDir . $sThumbName, 'w', false);
            if ($oFile) {
                fwrite($oFile, $oUnZip->GetData($aFiles[BX_SCTR_THEME_THUMB]));
                fclose($oFile);
            }
        }

        return _t('_bx_sctr_import_complete');
    }

    function _getZipFilesFromPath($oZipFile, $sPath = '')
    {
        $aFiles = array();

        for ($i = 0; $i < $oZipFile->Count(); $i++) {
            if ($oZipFile->GetPath($i) == $sPath)
                $aFiles[$oZipFile->GetName($i)] = $i;
        }

        return $aFiles;
    }

    function _compileBackground($aParam)
    {
        $sParams = '';

        foreach ($aParam as $sKey => $sValue) {
            if (!$sValue)
                continue;

            switch ($sKey) {
                case 'color':
                    $sParams .= 'background-color: ' . $sValue . ';';
                    if (!isset($aParam['image']))
                        $sParams .= 'background-image: none;';
                    break;

                case 'image':
                    if (isset($aParam['useimage']))
                        $sParams .= 'background-image: url(' . $this->_getImagesPath() . $sValue . ');';
                    else
                        $sParams .= 'background-image: none;';
                    break;

                case 'repeat':
                    $sParams .= 'background-repeat: ' . $sValue . ';';
                    break;

                case 'position':
                    $sParams .= 'background-position: ' . $sValue . ';';
                    break;
            }
        }

        return $sParams;
    }

    function _compileFont($aParam)
    {
        $sParams = '';

        foreach ($aParam as $sKey => $sValue) {
            if ($sValue == '')
                continue;

            switch ($sKey) {
                case 'size':
                    $sParams .= 'font-size: ' . $sValue . 'px;';
                    break;

                case 'color':
                    $sParams .= 'color: ' . $sValue . ';';
                    break;

                case 'name':
                    $sParams .= 'font-family: ' . $sValue . ';';
                    break;

                case 'style':
                    switch ($sValue) {
                        case 'normal':
                            $sParams .= 'font-style: normal;';
                            break;

                        case 'bold':
                            $sParams .= 'font-weight: bold;';
                            break;

                        case 'italic':
                            $sParams .= 'font-style: italic;';
                            break;
                    }
            }
        }

        return $sParams;
    }

    function _compileBorder($aParam)
    {
        $sParams = '';
        $aProperties = array(
            'border'
        );

        if (isset($aParam['position']))
            switch($aParam['position']) {
                case 'top':
                    $aProperties = array(
                        'border-top'
                    );
                    break;

                case 'right':
                    $aProperties = array(
                        'border-right'
                    );
                    break;

                case 'bottom':
                    $aProperties = array(
                        'border-bottom'
                    );
                    break;

                case 'left':
                    $aProperties = array(
                        'border-left'
                    );
                    break;

                case 'left_right':
                    $aProperties = array(
                        'border-left',
                        'border-right'
                    );
                    break;

                case 'top_bottom':
                    $aProperties = array(
                        'border-top',
                        'border-bottom'
                    );
                    break;

                case 'top_right':
                    $aProperties = array(
                        'border-top',
                        'border-right'
                    );
                    break;

                case 'right_bottom':
                    $aProperties = array(
                        'border-right',
                        'border-bottom'
                    );
                    break;

                case 'bottom_left':
                    $aProperties = array(
                        'border-bottom',
                        'border-left'
                    );
                    break;

                case 'left_top':
                    $aProperties = array(
                        'border-left',
                        'border-top'
                    );
                    break;
            }

        foreach ($aParam as $sKey => $sValue) {
            $sProperty = '';
            if ($sValue == '')
                continue;

            switch ($sKey) {
                case 'size':
                    foreach ($aProperties as $sVal)
                        $sParams .= $sVal . '-width: ' . $sValue . 'px;';
                    break;

                case 'color':
                    foreach ($aProperties as $sVal)
                        $sParams .= $sVal . '-color: ' . $sValue . ';';
                    break;

                case 'style':
                    foreach ($aProperties as $sVal)
                        $sParams .= $sVal . '-style: ' . $sValue . ';';
                    break;
            }
        }

        return $sParams;
    }

	function _getCustomizeBlock($sPage = '', $sTarget = '')
    {
        if (!$this->iUserId || !isAdmin($this->iUserId) || !getParam('bx_sctr_enable'))
            return '';

        $sUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'customizepage/';
        $aMenuItems = array('themes', 'background', 'font', 'border');
        $aTopMenu = array();
        $aTargets = array();

        list($sSesPage, $sSesTarget, $iSesTheme, $sSesAction) = $this->_oConfig->getSessionData();
        if($sPage == '' && !empty($sSesPage))
        	$sPage = $sSesPage;

        if($sTarget == '' && !empty($sSesTarget))
        	$sTarget = $sSesTarget;

        if (!$sPage)
            $sPage = $aMenuItems[0];

        foreach ($aMenuItems as $sItem) {
            $aTopMenu[_t('_bx_sctr_page_' . $sItem)] = array(
                'href' => $sUrl . $sItem,
                'dynamic' => true,
                'active' => $sItem == $sPage
            );
        }

        $aTargets = array();
        if (isset($this->_aCssMatch[$sPage])) {
            if (!$sTarget)
                $sTarget = key($this->_aCssMatch[$sPage]);

            foreach ($this->_aCssMatch[$sPage] as $sKey => $aValues) {
                $aTargets[] = array(
                    'name' => _t($aValues['name']),
                    'value' => $sUrl . $sPage . '/' . $sKey,
                    'select' => $sKey == $sTarget ? 'selected' : ''
                );
            }
        }

        $aVars = array();
        $aStyle = $this->_oDb->getSiteTmp();
        if (!empty($aStyle) && isset($aStyle[$sPage][$sTarget]))
            $aVars = $aStyle[$sPage][$sTarget];

        return $this->_oTemplate->profileCustomizeBlock($aTopMenu, $sPage, $aTargets, $sTarget, $aVars);
    }
}
