<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

$sId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$sPassword = isset($_REQUEST['password']) ? process_db_input($_REQUEST['password']) : "";
$sTitle = isset($_REQUEST['title']) ? process_db_input($_REQUEST['title']) : "Untitled";
$sTags = isset($_REQUEST['tags']) ? process_db_input($_REQUEST['tags']) : "";
$sDesc = isset($_REQUEST['desc']) ? process_db_input($_REQUEST['desc']) : "";

$sSkin = isset($_REQUEST['skin']) ? process_db_input($_REQUEST['skin']) : "";
$sLanguage = isset($_REQUEST['language']) ? process_db_input($_REQUEST['language']) : "english";

switch ($sAction) {
    case 'getPlugins':
        $sFolder = "/plugins/";
        $sContents = "";
        $sPluginsPath = $sModulesPath . $sModule . $sFolder;
        if(is_dir($sPluginsPath)) {
            if($rDirHandle = opendir($sModulesPath . $sModule . $sFolder))
                while(false !== ($sPlugin = readdir($rDirHandle)))
                    if(strpos($sPlugin, ".swf") === strlen($sPlugin)-4)
                        $sContents .= parseXml(array(1 => '<plugin><![CDATA[#1#]]></plugin>'), $sModulesUrl . $sModule . $sFolder . $sPlugin);
            closedir($rDirHandle);
        }
        $sContents = makeGroup($sContents, "plugins");
        break;

    /**
    * gets skins
    */
    case 'getSkins':
        $sContents = printFiles($sModule, "skins", false, true);
        break;

    /**
     * Sets default skin.
     */
    case 'setSkin':
        setCurrentFile($sModule, $sSkin, "skins");
        break;

    /**
     * gets languages
     */
    case 'getLanguages':
        $sContents = printFiles($sModule, "langs", false, true);
        break;

    /**
    * Sets default language.
    */
    case 'setLanguage':
        setCurrentFile($sModule, $sLanguage, "langs");
        break;

    /**
    * Authorize user.
    */
    case 'userAuthorize':
        $sContents = parseXml($aXmlTemplates['result'], "msgAuthorizingUserError");
        if(loginUser($sId, $sPassword) == TRUE_VAL) {
            removeFiles($sId);
            $sContents = parseXml($aXmlTemplates['result'], TRUE_VAL);
        }
        break;

    /**
    * Get config
    */
    case 'config':
        $sFileName = $sModulesPath . $sModule . "/xml/config.xml";
        $rHandle = fopen($sFileName, "rt");
        $sContents = fread($rHandle, filesize($sFileName)) ;
        fclose($rHandle);
        $sContents = str_replace("#filesUrl#", $sFilesUrl, $sContents);
        break;

    case 'transmit':
        //--- Prepare data ---//
        $iWidth = isset($_REQUEST['width']) ? (int)$_REQUEST['width'] : 0;
        $iHeight = isset($_REQUEST['height']) ? (int)$_REQUEST['height'] : 0;
        $sData = isset($_REQUEST['data']) ? process_db_input($_REQUEST['data']) : "";
        $aImageData = explode(',', $sData);
        $iLength = count($aImageData);
        for($i=0; $i<$iLength; $i++)
            $aImageData[$i] = base_convert($aImageData[$i], 36, 10);
        if($iLength != $iWidth * $iHeight) {
            $sContents = parseXml($aXmlTemplates['result'], 'msgErrorSizes', FAILED_VAL);
            break;
        }
        if(!function_exists("imagecreatetruecolor")) {
            $sContents = parseXml($aXmlTemplates['result'], 'msgErrorGD', FAILED_VAL);
            break;
        }

        //--- Create Image Resource ---//
        $rImage = @imagecreatetruecolor($iWidth, $iHeight);
        for($i=0, $y=0; $y<$iHeight; $y++ )
            for($x=0; $x<$iWidth; $x++, $i++)
                @imagesetpixel($rImage, $x, $y, $aImageData[$i]);

        //--- Save image file ---//
        $sFileName = $sFilesPath . $sId . IMAGE_EXTENSION;
        $iQuality = getSettingValue($sModule, "quality");
        if(!is_numeric($iQuality)) $iQuality = 75;
        if(!@imagejpeg($rImage, $sFileName, $iQuality))
            $sContents = parseXml($aXmlTemplates['result'], 'msgErrorFile', FAILED_VAL);
        else
            $sContents = parseXml($aXmlTemplates['result'], '', SUCCESS_VAL);
        break;

    case 'post':
        $sTable = isset($_REQUEST['table']) ? process_db_input($_REQUEST['table']) : "";
        $sAuthor = isset($_REQUEST['author']) ? process_db_input($_REQUEST['author']) : "";
        $sParent = isset($_REQUEST['parent']) ? process_db_input($_REQUEST['parent']) : "";
        $sMood = isset($_REQUEST['mood']) ? process_db_input($_REQUEST['mood']) : "";

        $sContents = "";
        $aResult = initFile($sAuthor);
        if($aResult['status'] == SUCCESS_VAL)
            $sContents = post($sTable, $sId, $sAuthor, $sParent, $sMood, $aResult['file']);
        $sContentsType = "text";
        //break shouln't be here

    case 'removeTempFiles':
        removeFiles($sId);
        break;
}
