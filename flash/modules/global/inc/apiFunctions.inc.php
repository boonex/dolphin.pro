<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

/**--------------- XML Retrieving and Formatting Functions ---------------**/
/**
 * Parse XML template using specified information.
 * @param aXmlTemplates - array with XML templates grouped by type.
 * @param ... - variable amount of incoming parameters with information to be used in the process of parsing.
 * @return $sContent - XML entry
 */
function parseXml($aXmlTemplates)
{
    $iNumArgs = func_num_args();
    $sContent = $aXmlTemplates[$iNumArgs - 1];

    for($i=1; $i<$iNumArgs; $i++) {
        $sValue = func_get_arg($i);
        $sContent = str_replace("#" . $i. "#", $sValue, $sContent);
    }
    return $sContent;
}

/**
 * Group specified content.
 * @param sXmlContent - content to be grouped.
 * @param sXmlGroup - group name.
 */
function makeGroup($sXmlContent, $sXmlGroup = "ray")
{
    return "<" . $sXmlGroup . ">" . $sXmlContent . "</" . $sXmlGroup . ">";
}

/**
 * Saves setting value in the config.xml file of the specified widget.
 * @param sWidget - widget's name.
 * @param $sSettingKey - setting's key.
 * @param sSettingValue - new value for specified setting.
 */
function setSettingValue($sWidget, $sSettingKey, $sSettingValue, $sFile = "config")
{
    global $sModulesPath;
    global $aXmlTemplates;
    global $aErrorCodes;

    //--- Read file ---//
    $sWidgetFile = $sWidget . "/xml/" . $sFile . ".xml";
    $sFileName = $sModulesPath . $sWidgetFile;
    if(!file_exists($sFileName)) return parseXml($aXmlTemplates['result'], getError($aErrorCodes[1], $sWidgetFile), FAILED_VAL);
    $sConfigContents = "";
    if(($rHandle = @fopen($sFileName, "rt")) !== false && filesize($sFileName) > 0) {
        $sConfigContents = fread($rHandle, filesize($sFileName)) ;
        fclose($rHandle);
    }

    //--- Update info ---//
    if(is_array($sSettingKey) && is_array($sSettingValue)) {
        for($i=0; $i<count($sSettingKey); $i++)
            $sConfigContents = xmlSetValue($sConfigContents, "item", $sSettingKey[$i], $sSettingValue[$i]);
    } else
        $sConfigContents = xmlSetValue($sConfigContents, "item", $sSettingKey, $sSettingValue);

    //--- Save changes in the file---//
    $bResult = true;
    if(($rHandle = @fopen($sFileName, "wt")) !== false) {
        $bResult = (fwrite($rHandle, $sConfigContents) !== false);
        fclose($rHandle);
    }
    $sValue = $bResult && $rHandle ? "" : getError($aErrorCodes[2], $sWidgetFile);

    return array('value' => $sValue, 'status' => $bResult ? SUCCESS_VAL : FAILED_VAL);
}

/**
 * Gets setting value from config.xml file.
 * @param sWidget - widget's name.
 * @param $sSettingKey - setting's key.
 */
function getSettingValue($sWidget, $sSettingKey, $sFile = "config", $bFullReturn = false, $sFolder = "xml")
{
    global $sModulesPath;
    global $aErrorCodes;

    //--- Read file ---//
    $sWidgetFile = $sWidget . "/" . $sFolder . "/" . $sFile . ".xml";
    $sFileName = $sModulesPath . $sWidgetFile;
    if(!file_exists($sFileName)) {
        if($bFullReturn)	return array('value' => getError($aErrorCodes[1], $sWidgetFile), 'status' => FAILED_VAL);
        else          		return "";
    }
    $sConfigContents = makeGroup("", "items");
    if(($rHandle = @fopen($sFileName, "rt")) !== false && filesize($sFileName) > 0) {
        $sConfigContents = fread($rHandle, filesize($sFileName));
        fclose($rHandle);
    }

    //--- Update info ---//
    $sValue = xmlGetValue($sConfigContents, "item", $sSettingKey);
    if($bFullReturn)	return array('value' => $sValue, 'status' => SUCCESS_VAL);
    else		        return $sValue;
}

function getWMode()
{
    return getSettingValue(GLOBAL_MODULE, "opaqueMode") == TRUE_VAL ? "opaque" : "window";
}

/**
 * return RMS Url to given application
 * @param sApplication - application name.
 * @return sRMSUrl - RMS Url.
 */
function getRMSUrl($sApplication, $bHttp = false)
{
    $sRMSProtocol = $bHttp ? "http://" : "rtmp://";
    $sRMSPort = getSettingValue(GLOBAL_MODULE, $bHttp ? "RMSHttpPort" : "RMSPort");
    $sRMSPort = empty($sRMSPort) ? "" : ":" . $sRMSPort;
    $sRMSUrl = $sRMSProtocol . getSettingValue(GLOBAL_MODULE, "RMSIP") . $sRMSPort . "/" . $sApplication . "/";
    return $sRMSUrl;
}

/**
 * is server turned on
 * @return bTurned
 */
function useServer()
{
    $sUseServer = getSettingValue(GLOBAL_MODULE, "useRMS");
    return $sUseServer == TRUE_VAL;
}

/**--------------- Online Users Retrieving and Updating Functions ---------------**/
/**
 * get online users
 * @param aRange - users IDs range to select from
 * @param $bInRange - get users that in(not in) range
 */
function getOnline($aRange = array(), $bInRange = true)
{
    require_once(BX_DIRECTORY_PATH_INC . "db.inc.php");
    $iMin = (int)getParam("member_online_time");

    $sInRange = $bInRange ? "IN" : "NOT IN";
    $sWhere = " WHERE `UserStatus`!='" . USER_STATUS_OFFLINE . "' AND `DateLastNav`>SUBDATE(NOW(), INTERVAL " . $iMin . " MINUTE) ";
    if(isset($aRange) && count($aRange) > 0)
        $sQuery = "SELECT `ID` FROM `Profiles`" . $sWhere . "AND `ID` " . $sInRange . " (" . implode(",", $aRange) . ") ORDER BY `ID`";
    else $sQuery = "SELECT `ID` FROM `Profiles`" . $sWhere . "ORDER BY `ID`";

    $rOnline = getResult($sQuery);
    $aOnline = array();
    while($aUser = $rOnline->fetch())
        $aOnline[] = $aUser['ID'];
    return $aOnline;
}

/**--------------- GET/SET Language/Skin Functions ---------------**/
/**
 * get extra files list for given module
 * @param $sModule - module name
 * @param $sFolder - folder name where to look for files
 * @param $bGetUserFile - get current user file (true) or default file (false)
 * @param $bGetDate - get dates of files
 * @return $aResult - files array without extension and/or current file
 */
function getExtraFiles($sModule, $sFolder = "langs", $bGetUserFile = true, $bGetDate = false)
{
    global $sModulesPath;

    $sFilesPath = $sModulesPath . $sModule . "/" . $sFolder . "/";
    $aFiles = Array();
    $aDates = Array();
    $sExtension = getFileExtension($sModule, $sFolder);

    if($bGetDate) clearstatcache();
	
	if($sFolder == "langs")
	    $sSysLang = getCurrentLangName(false);
	else
	{
	    $aResult = getSettingValue($sModule, FILE_DEFAULT_KEY, $sFolder, true);
        if($aResult['status'] == FAILED_VAL || empty($aResult['value'])) $sDefaultFile = ($sFolder == "langs") ? "english" : "default";
        else $sDefaultFile = $aResult['value'];
	}

    if($rDirHandle = opendir($sFilesPath))
        while (false !== ($sFile = readdir($rDirHandle)))
            if(is_file($sFilesPath . $sFile) && $sFile != "." && $sFile != ".." && $sExtension == substr($sFile, strpos($sFile, ".") + 1)) {
                $aFiles[] = substr($sFile, 0, strpos($sFile, "."));
                if($bGetDate) $aDates[] = filectime($sFilesPath . $sFile);
				if($sFolder == "langs" && $sSysLang == substr($sFile, 0, 2))
                    $sDefaultFile = substr($sFile, 0, strpos($sFile, "."));
            }
    closedir($rDirHandle);

    $sCurrentFile = (in_array($sDefaultFile, $aFiles)) ? $sDefaultFile : $aFiles[0];
    if($bGetUserFile) {
        $sCookieValue = $_COOKIE["ray_" . $sFolder . "_" . $sModule];
        $sCurrentFile = (isset($sCookieValue) && in_array($sCookieValue, $aFiles)) ? $sCookieValue : $sCurrentFile;
    }
    return array('files' => $aFiles, 'dates' => $aDates, 'current' => $sCurrentFile, 'extension' => $sExtension);
}

/**
 * set current file for module to cookie
 * @param $sModule - module name
 * @param $sFile - file value
 * @param $sFolder - folder name for which value is set
 */
function setCurrentFile($sModule, $sFile, $sFolder = "langs")
{
    setCookie("ray_" . $sFolder . "_" . $sModule, $sFile, time() + 31536000);
}

/**
 * get extra files for module in XML format
 * @param $sModule - module name
 * @param $sFolder - folder name for which value is set
 * @param $bGetDate - get dates of files
 * @return $sContents - XML formatted result
 */
function printFiles($sModule, $sFolder = "langs", $bGetDate = false, $bGetNames = false)
{
    global $sIncPath;
    global $sModulesUrl;
    require_once($sIncPath . "xmlTemplates.inc.php");

    $aFileContents = getFileContents($sModule, "/xml/" . $sFolder . ".xml", true);
    $aFiles = $aFileContents['contents'];
    $aEnabledFiles = array();
    foreach($aFiles as $sFile => $sEnabled)
       if($sEnabled == TRUE_VAL)
           $aEnabledFiles[] = $sFile;
    $sDefault = $aFiles['_default_'];

    $aResult = getExtraFiles($sModule, $sFolder, true, $bGetDate);
    $sCurrent = $aResult['current'];
    $sCurrent = in_array($sCurrent, $aEnabledFiles) ? $sCurrent : $sDefault;
    $sCurrentFile = $sCurrent . "." . $aResult['extension'];

    $aRealFiles = array_flip($aResult['files']);
    $aFileDates = $aResult['dates'];
    $sContents = "";

    for($i=0; $i<count($aEnabledFiles); $i++)
        if(isset($aRealFiles[$aEnabledFiles[$i]])) {
            $sFile = $aEnabledFiles[$i];
            if($bGetDate) $sContents .= parseXml($aXmlTemplates['file'], $sFile, $aFileDates[$aRealFiles[$sFile]]);
            else {
                if($bGetNames) {
                    $sName = $sFolder == "langs"
                        ? getSettingValue($sModule, "_name_", $sFile, false, "langs")
                        : getSettingValue($sModule, $sFile, "skinsNames");
                    if(empty($sName)) $sName = $sFile;
                    $sContents .= parseXml($aXmlTemplates['file'], $sFile, $sName, "");
                } else $sContents .= parseXml($aXmlTemplates['file'], $sFile);
            }
        }

    $sContents = makeGroup($sContents, "files");
    $sContents .= parseXml($aXmlTemplates['current'], $sCurrent, $sModulesUrl . $sModule . "/" . $sFolder . "/" . $sCurrentFile);

    return $sContents;
}

function moveMp4Meta($sFilePath)
{
    require_once(BX_DIRECTORY_PATH_PLUGINS . 'moovrelocator/lib/Moovrelocator.class.php');

    $oMoovrelocator = new Moovrelocator();

    $mixedRet = $oMoovrelocator->relocateMoovAtom ($sFilePath, null, true);
    if ($mixedRet !== true)
        return false;

    return true;
}
