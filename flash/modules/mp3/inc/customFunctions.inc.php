<?php
require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'tags.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_MODULES . 'boonex/sounds/classes/BxSoundsSearch.php');

if(!defined("SCREENSHOT_WIDTH")) define("SCREENSHOT_WIDTH", 453);
if(!defined("SCREENSHOT_HEIGHT")) define("SCREENSHOT_HEIGHT", 330);

function mp3_parseTags($iId)
{
    reparseObjTags('music', $iId);
}

function mp3_genUri($s)
{
    global $sModule;
    $sDBModule = DB_PREFIX . ucfirst($sModule);
    return uriGenerate($s, $sDBModule . 'Files', 'Uri', 255);
}

function postMusic($sUploadedFile, $aFileInfo)
{
    global $oDb;
    global $sFilesPathMp3;

    $sId = $aFileInfo['author'];
    if($sUploadedFile != "") {
        $sTempFile = $sFilesPathMp3 . $sId . TEMP_FILE_NAME;
        @unlink($sTempFile);
        if(!is_uploaded_file($sUploadedFile)) return false;

        move_uploaded_file($sUploadedFile, $sTempFile);
        if(!convert($sId, $aFileInfo['mp3'])) {
            deleteTempMp3s($sId);
            return false;
        }
    }
    $aResult = initFile($sId, $aFileInfo['category'], addslashes($aFileInfo['title']), addslashes($aFileInfo['tags']), addslashes($aFileInfo['description']));

    if($aResult['status'] == SUCCESS_VAL) return $aResult['file'];
    else return false;
}

function mp3_getList($sId)
{
    global $sModule;
    global $aXmlTemplates;
    global $sFilesPathMp3;

    $sMode = getSettingValue($sModule, "listSource");
    $iCount = (int)getSettingValue($sModule, "listCount");
    if(!is_numeric($iCount) || $iCount <= 0) $iCount = 10;

    $oSource = new BxSoundsSearch();
    $oSource->aCurrent['sorting'] = 'top';
    $oSource->aCurrent['paginate']['perPage'] = $iCount;
    $oSource->aCurrent['restriction']['id'] = array(
        'value'=>$sId,
        'field'=>'ID',
        'operator'=>'<>'
    );
    switch($sMode) {
        case "Member":
            $sOwner = getValue("SELECT `Owner` FROM `" . MODULE_DB_PREFIX . "Files` WHERE `ID` = '" . $sId . "'");
            $oSource->aCurrent['restriction']['owner'] = array(
                'value'=>$sOwner,
                'field'=>'Owner',
                'operator'=>'='
            );
            break;

        case "Related":
            $aFile = getArray("SELECT * FROM `" . MODULE_DB_PREFIX . "Files` WHERE `ID` = '" . $sId . "'");
            $oSource->aCurrent['restriction']['keyword'] = array(
                'value' => $aFile['Title'] . " " . $aFile['Tags'] . " " . $aFile['Description'],
                'field' => '',
                'operator' => 'against'
            );
            break;

        case "Top":
        default:
            $oSource->aCurrent['restriction']['id'] = array(
                'value'=>$sId,
                'field'=>'ID',
                'operator'=>'<>'
            );
            break;
    }

    $aData = $oSource->getSearchData();
    $sResult = "";

    for($i=0; $i<count($aData); $i++) {
        $aData[$i]['uri'] = $oSource->getCurrentUrl('file', $aData[$i]['id'], $aData[$i]['uri']);
        $aData[$i]['date'] = defineTimeInterval($aData[$i]['date']);
        $sImageFile = $aData[$i]['id'] . SCREENSHOT_EXT;
        $sResult .= parseXml($aXmlTemplates['file'], $sImageFile, $aData[$i]['size'], $aData[$i]['ownerName'], $aData[$i]['view'], $aData[$i]['voting_rate'], $aData[$i]['date'], $aData[$i]['title'], BX_DOL_URL_ROOT . $aData[$i]['uri']);
    }
    return $sResult;
}
