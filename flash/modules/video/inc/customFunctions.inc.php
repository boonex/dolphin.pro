<?php
require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'tags.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_MODULES . 'boonex/videos/classes/BxVideosSearch.php');

function video_parseTags($iId)
{
    reparseObjTags( 'video', $iId );
}

function video_genUri($s)
{
    global $sModule;
    $sDBModule = DB_PREFIX . ucfirst($sModule);
    return uriGenerate($s, $sDBModule . 'Files', 'Uri', 255);
}

function postVideo($sUploadedFile, $aFileInfo)
{
    global $oDb;
    global $sFilesPath;

    $sId = $aFileInfo['author'];
    if($sUploadedFile != "") {
        $sTempFile = $sFilesPath . $sId . TEMP_FILE_NAME;
        @unlink($sTempFile);
        if(!is_uploaded_file($sUploadedFile)) return false;

        move_uploaded_file($sUploadedFile, $sTempFile);
        if(!convertVideo($sId)) {
            deleteTempVideos($sId);
            return false;
        }
    }
    $aResult = initVideo($sId, $aFileInfo['category'], addslashes($aFileInfo['title']), addslashes($aFileInfo['tags']), addslashes($aFileInfo['description']));
    if($aResult['status'] == SUCCESS_VAL) return $aResult['file'];
    else return false;
}

function video_getList($sId)
{
    global $sModule;
    global $aXmlTemplates;
    global $sFilesPath;

    $sMode = getSettingValue($sModule, "listSource");
    $iCount = (int)getSettingValue($sModule, "listCount");
    if(!is_numeric($iCount) || $iCount <= 0) $iCount = 10;

    $oSource = new BxVideosSearch();
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
        $sImageFile = $aData[$i]['id'] . IMAGE_EXTENSION;
        $sThumbFile = $aData[$i]['id'] . THUMB_FILE_NAME . IMAGE_EXTENSION;
        if(!file_exists($sFilesPath . $sThumbFile)) $sThumbFile = $sImageFile;
        $sResult .= parseXml($aXmlTemplates['file'], $sThumbFile, $aData[$i]['size'], $aData[$i]['ownerName'], $aData[$i]['view'], $aData[$i]['voting_rate'], $aData[$i]['date'], $aData[$i]['title'], BX_DOL_URL_ROOT . $aData[$i]['uri']);
    }
    return $sResult;
}

function video_getCustomEmbedCode($sSource, $sVideo)
{
    return "";
}
