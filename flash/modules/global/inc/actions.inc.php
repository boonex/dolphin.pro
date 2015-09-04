<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
//user's ID
$sId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
//user's nick
$sNick = isset($_REQUEST['nick']) ? process_db_input($_REQUEST['nick']) : "";
//user's password
$sPassword = isset($_REQUEST['password']) ? process_db_input($_REQUEST['password']) : "";
//user's status
$sStatus = isset($_REQUEST['status']) ? process_db_input($_REQUEST['status']) : "";

//widget name
$sWidget = isset($_REQUEST['widget']) ? process_db_input($_REQUEST['widget']) : "";
//widget status
$bWidgetEnable = isset($_REQUEST['enable']) ? $_REQUEST['enable'] == 'true' : true;
//folder name
$sFolderName = isset($_REQUEST['folder']) ? process_db_input($_REQUEST['folder']) : "";
//default file
$sDefaultFile = isset($_REQUEST['default']) ? process_db_input($_REQUEST['default']) : "";
//file
$sFile = isset($_REQUEST['file']) ? process_db_input($_REQUEST['file']) : "";

//setting key
$sSettingKey = isset($_REQUEST['key']) ? process_db_input($_REQUEST['key']) : "";
//setting value
$sSettingValue = isset($_REQUEST['value']) ? process_db_input($_REQUEST['value']) : "";

switch ($sAction) {
    case 'version':
        $sContents = parseXml($aXmlTemplates['result'], $aInfo['version']);
        break;

    /**
     * ==== INSTALLATION ACTIONS ====
     * Gets install.xml file for specified widget name.
     */
    case 'startInstall':
        if(loginAdmin($sNick, $sPassword) != TRUE_VAL) {
            $sContents = parseXml($aXmlTemplates['result'], "Admin Authorization Failed", FAILED_VAL);
            break;
        }
        $sWidgetFile = $sWidget . "/install/install.xml";
        $sFileName = $sModulesPath . $sWidgetFile;
        if(file_exists($sFileName)) {
            $aResult = getFileContents($sWidget, "/install/permissions.xml", true);
            $aUserFiles = ($aResult['status'] == SUCCESS_VAL) ? $aResult['contents'] : array();
            $aFiles = Array("xml/main.xml" => "666", "xml/config.xml" => "666", "xml/skins.xml" => "666", "xml/langs.xml" => "666");
            $aFiles = array_merge($aFiles, $aUserFiles);
            $aItems = Array();
            foreach($aFiles as $sFile => $sPermissions)
                if(file_exists($sModulesPath . $sWidget . "/" . $sFile))
                    $aItems[$sFile] = parseXml($aXmlTemplates["item"], $sWidget, $sFile, $sPermissions);

            if(!isset($aItems["xml/main.xml"]))
                $sContents = parseXml($aXmlTemplates['result'], getError($aErrorCodes[1], $sWidget . "/xml/main.xml"), FAILED_VAL);
            else $sContents = parseXml($aXmlTemplates['result'], '', SUCCESS_VAL);

            $sCaption = parseXml($aXmlTemplates['caption'], "Permissions");
            $sText = parseXml($aXmlTemplates['text'], '<p align="center">Click "NEXT" to renew permission settings</p>');
            $sItems = makeGroup(implode("", $aItems), "items");
            $sPages = makeGroup($sCaption . $sText . $sItems, "page");

            $rHandle = fopen($sFileName, "rt");
            $sPages .= fread($rHandle, filesize($sFileName));
            $sContents .= makeGroup($sPages, "pages");
            fclose($rHandle);
        } else
            $sContents = parseXml($aXmlTemplates['result'], getError($aErrorCodes[1], $sWidgetFile), FAILED_VAL);
        break;

    /**
     * Finalize installation process
     * 1. Create database
     * 2. Change config.xml file to indicate that widget was installed.
     * @param sWidget - the name of the widget.
     */
    case 'finishInstall':
        if(loginAdmin($sNick, $sPassword) != TRUE_VAL) {
            $sContents = parseXml($aXmlTemplates['result'], "Admin Authorization Failed", FAILED_VAL);
            break;
        }
        //--- 1. Recompile integration JS.
        $sWidgetFile = $sWidget . "/inc/constants.inc.php";
        $sWidgetFileName = $sModulesPath . $sWidgetFile;
        if(!secureCheckWidgetName($sWidget) || !file_exists($sWidgetFileName)) {
            $sContents = parseXml($aXmlTemplates['result'], getError($aErrorCodes[1], $sWidgetFile), FAILED_VAL);
            break;
        }
        if(isset($aModules)) unset($aModules);
        require_once($sWidgetFileName);

        $aResult = recompileIntegrator($sWidget);
        if($aResult['status'] == FAILED_VAL) {
            $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], FAILED_VAL);
            break;
        }

        //--- 2. Create skins.xml file.
        if(file_exists($sModulesPath . $sWidget . "/xml/skins.xml")) {
            $aResult = refreshExtraFile($sWidget, "skins");
            if($aResult['status'] == FAILED_VAL) {
                $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], FAILED_VAL);
                break;
            }
        }

        //--- 3. Create langs.xml file.
        if(file_exists($sModulesPath . $sWidget . "/xml/langs.xml")) {
            $aResult = refreshExtraFile($sWidget, "langs");
            if($aResult['status'] == FAILED_VAL) {
                $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], FAILED_VAL);
                break;
            }
        }

        //--- 4. Change main.xml file.
        $aResult = createMainFile($sWidget);
        if($aResult['status'] == FAILED_VAL) {
            $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], FAILED_VAL);
            break;
        }

        //--- 5. Create database.
        $sResult = createDataBase($sWidget);
        if(empty($sResult))	$sContents = parseXml($aXmlTemplates['result'], "", SUCCESS_VAL);
        else				$sContents = parseXml($aXmlTemplates['result'], $sResult, FAILED_VAL);
        break;

    /**
    * Item action which checks the permissions of specified file.
    * @param sWidget - the name of the widget.
    * @param sFileName - the name of the file(folder).
    */
    case 'checkPermissions':
        $sWidgetFile = $sWidget . "/" . $sFile;
        $sFileName = $sModulesPath . $sWidgetFile;
        $sResult = checkPermissions($sFileName);
        if(empty($sResult))	$sContents = parseXml($aXmlTemplates['result'], getError($aErrorCodes[1], $sWidgetFile), FAILED_VAL);
        else				$sContents = parseXml($aXmlTemplates['result'], $sResult, SUCCESS_VAL);
        break;

    /**
    * Saves the configuration setting in the XML-config file.
    * @param sWidget - the name of the widget.
    * @param sSettingName - the setting's name.
    * @param sSettingValue - the setting's value.
    */
    case 'setSettingValue':
        if(loginAdmin($sNick, $sPassword) != TRUE_VAL) {
            $sContents = parseXml($aXmlTemplates['result'], "Admin Authorization Failed", FAILED_VAL);
            break;
        }
        $aResult = setSettingValue($sWidget, $sSettingKey, $sSettingValue, $sFile);
        if($aResult['status'] == SUCCESS_VAL && (strpos($sSettingKey, "_width") > 0 || strpos($sSettingKey, "_height") > 0)) {
            if(isset($aModules)) unset($aModules);
            if(secureCheckWidgetName($sWidget)) {
                require_once($sModulesPath . $sWidget . "/inc/constants.inc.php");
                $aResult = recompileIntegrator($sWidget);
            } else {
                $aResult = array('status' => FAILED_VAL, 'value' => $aErrorCodes[8]);
            }
        }

        $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], $aResult['status']);
        break;

    /**
     * Gets the configuration setting from the XML-config file.
     * @param sWidget - the name of the widget.
     * @param sSettingName - the setting's name.
     */
    case 'getSettingValue':
        $aSetting = getSettingValue($sWidget, $sSettingKey, $sFile, true);
        $sContents = parseXml($aXmlTemplates['result'], $aSetting['value'], $aSetting['status']);
        break;

    /**
    * Gets widget's settings with descriptions and values.
    */
    case 'settings':
        $aResult = getFileContents($sWidget, "/xml/settings.xml");
        $sSettingsContents = $aResult['status'] == FAILED_VAL ? makeGroup("", "items") : $aResult['contents'];
        $sContents = makeGroup($sSettingsContents, "settings");

        $aResult = getFileContents($sWidget, "/xml/config.xml");
        $sConfigContents = $aResult['status'] == FAILED_VAL ? makeGroup("", "items") : $aResult['contents'];
        $sContents .= makeGroup($sConfigContents, "config");
        break;

    /**
    * Gets widget's templates
    */
    case 'templates':
        $aResult = refreshExtraFile($sWidget, "skins");
        $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], $aResult['status']);
        $sContents .= $aResult['contents'];
        break;

    /**
    * Gets widget's languages
    */
    case 'languages':
        $aResult = refreshExtraFile($sWidget, "langs");
        $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], $aResult['status']);
        $sContents .= $aResult['contents'];
        break;

    /**
    * Gets widget's updates
    */
    case 'updates':
        $aResult = getFileContents($sWidget, "/xml/main.xml", true);
        if($aResult['status'] == SUCCESS_VAL) {
           $aContents = $aResult['contents'];
           $sContents .= parseXml($aXmlTemplates['result'], SUCCESS_VAL, $aContents['updated'], $aContents['updateLast'], $aContents['updateUrl']);
        } else $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], FAILED_VAL);
        break;

    /**
    * save extra file values
    */
    case 'saveExtraFile':
        if(loginAdmin($sNick, $sPassword) != TRUE_VAL) {
            $sContents = parseXml($aXmlTemplates['result'], "Admin Authorization Failed", FAILED_VAL);
            break;
        }
        $aEnabledFiles = explode(",", $sFile);
        $aResult = refreshExtraFile($sWidget, $sFolderName, true, $sDefaultFile, $aEnabledFiles);
        $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], $aResult['status']);
        break;

    /**
    * Authorization.
    */
    case 'adminAuthorize':
        $sContents .= parseXml($aXmlTemplates['result'], loginAdmin($sNick, $sPassword));
        break;

    /**
    * Gets all available widgets with necessary information.
    */
    case 'getMyWidgets':
        $rDirHandler = opendir($sModulesPath);
        $aContents = array();
        $aTitles = array();

        while(($sInner = readdir($rDirHandler)) !== false)
            if(is_dir($sModulesPath . $sInner) && substr($sInner, 0, 1) != '.' && $sInner != 'global') {
                if(isset($aModules)) unset($aModules);
                if(!secureCheckWidgetName($sInner))
                    continue;

                $sConstantsFile = $sModulesPath . $sInner . "/inc/constants.inc.php";
                if(!file_exists($sConstantsFile))
                    continue;

                require_once($sConstantsFile);
                $sAdminUrl = file_exists($sModulesPath . $sInner . "/app/admin.swf") ? $sHomeUrl .  "index.php?module=" . $sInner . "&amp;app=admin&amp;nick=#nick#&amp;password=#password#": "";
                $aStatus = getSettingValue($sInner, "status", "main", true);
                $sStatus = ($aStatus['status'] == FAILED_VAL) ? WIDGET_STATUS_NOT_INSTALLED : $aStatus['value'];
                $sStatus = (empty($sStatus) || $sStatus == "") ? WIDGET_STATUS_NOT_INSTALLED : $sStatus;
                $sStatus = ("666" != checkPermissions($sModulesPath . $sInner . "/xml/main.xml")) ? WIDGET_STATUS_NOT_INSTALLED : $sStatus;
                $sVersion = isset($aInfo) ? $aInfo['version'] : "";
                $sTitle = isset($aInfo) ? $aInfo['title'] : "";
                $sAuthor = isset($aInfo) ? $aInfo['author'] : "";
                $sAuthorUrl = isset($aInfo) ? $aInfo['authorUrl'] : "";
                $sImageUrl = file_exists($sModulesPath . $sInner . "/data/preview.jpg") ? $sModulesUrl . $sInner . "/data/preview.jpg" : "";

                $aContents[] = parseXml($aXmlTemplates['widget'], $sInner, $sVersion, $sTitle, $sAuthor, $sAuthorUrl, $sImageUrl, $sStatus, $sAdminUrl);
                $aTitles[] = $sTitle;
                array_multisort($aTitles, $aContents);
                $sContent = implode("", $aContents);
            }

        $sContents = makeGroup($sContent, "widgets");
        break;

    /**
    * Gets widget code.
    */
    case 'getWidgetCode':
        $aResult = getFileContents($sWidget, "/xml/main.xml", true);
        if($aResult['status'] == SUCCESS_VAL) {
            $aContents = $aResult['contents'];
            $sCode = $aContents['code'];
            if(empty($sCode)) {
                if(secureCheckWidgetName($sWidget)) {
                    require_once($sModulesPath . $sWidget . "/inc/constants.inc.php");
                    $sCode = $aInfo['code'];
                }
            }
            $sContents = parseXml($aXmlTemplates['result'], SUCCESS_VAL, $sCode, $aContents['license']);
        } else $sContents = parseXml($aXmlTemplates['result'], $aResult['value'], FAILED_VAL);
        break;

    /**
    * Gets widget status and ads banner if it's paid
    */
    case 'getWidgetAds':
        $sFooter = getParam("enable_dolphin_footer");
        $bPaid = empty($sFooter);
        $sEnabled = $bPaid ? TRUE_VAL : FALSE_VAL;
        if($bPaid) {
            $sBannerUrl = getSettingValue(GLOBAL_MODULE, "bannerUrl");
            $sBannerLink = getSettingValue(GLOBAL_MODULE, "bannerLink");
            $sBannerTarget = getSettingValue(GLOBAL_MODULE, "bannerTarget");
            $iBannerAlpha = getSettingValue(GLOBAL_MODULE, "bannerAlpha");
            if(!is_numeric($iBannerAlpha) || $iBannerAlpha < 0 || $iBannerAlpha > 100) $iBannerAlpha = 100;
            $sContents = parseXml($aXmlTemplates['ads'], $sEnabled, $sBannerUrl, $sBannerLink, $sBannerTarget, $iBannerAlpha);
        } else $sContents = parseXml($aXmlTemplates['ads'], $sEnabled);
        break;
}
