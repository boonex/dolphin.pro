<?php

require_once(BX_DIRECTORY_PATH_INC . 'admin.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'languages.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');

/**
 * Get information about avaliable rooms in XML format.
 * @comment - Refreshed
 */

 function save($sSavedId, $sFilePath, $sTitle)
 {
    @copy($sFilePath, $sFilePath . ".tmp");
    $sFilePath .= ".tmp";
    $aRes = array('status' => FAILED_VAL, 'value' => "msgErrorSave");

    define ('BX_BOARD_PHOTOS_CAT', 'Board');
    define ('BX_BOARD_PHOTOS_TAG', 'Board');

    $aUser = getProfileInfo();
    $aFileInfo = array (
        'medTitle' => stripslashes($sTitle), 'medDesc' => stripslashes($sTitle),
        'medTags' => BX_BOARD_PHOTOS_TAG, 'Categories' => array(BX_BOARD_PHOTOS_CAT),
        'album' => str_replace('{nickname}', $aUser["NickName"], getParam('bx_photos_profile_album_name'))
    );

    if ($sSavedId > 0) {
        $iRet = BxDolService::call('photos', 'perform_photo_replace', array($sFilePath, $aFileInfo, false, $sSavedId), 'Uploader');
        if ($iRet) {
            return array('status' => SUCCESS_VAL, 'value' => $sSavedId);
        }
    } else {
        $iRet = BxDolService::call('photos', 'perform_photo_upload', array($sFilePath, $aFileInfo, false), 'Uploader');
        if ($iRet) {
            return array('status' => SUCCESS_VAL, 'value' => $iRet);
        }
    }

    return $aRes;
 }

 function getSavedBoardInfo($sId, $iBoardId)
 {
    global $aXmlTemplates;

    $aBoard = BxDolService::call('photos', 'get_photo_array', array($iBoardId, 'original'), 'Search');
    if(count($aBoard)==0 || $sId != $aBoard["owner"])
        $sResult = parseXml($aXmlTemplates["result"], "msgSavedError", FAILED_VAL);
    else {
        $sResult = parseXml($aXmlTemplates["result"], $iBoardId, SUCCESS_VAL);
        $sResult .= parseXml($aXmlTemplates["savedBoard"], $aBoard["file"], $aBoard["title"]);
    }
    return $sResult;
 }
