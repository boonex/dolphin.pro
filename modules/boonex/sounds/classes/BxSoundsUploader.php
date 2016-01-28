<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesUploader');
bx_import('BxDolCategories');
bx_import('BxDolModule');
bx_import('BxDolAlbums');

global $sIncPath;
global $sModulesPath;
global $sModule;
global $sFilesPath;
global $oDb;
require_once($sIncPath . 'db.inc.php');

$sModule = "mp3";
$sModulePath = $sModulesPath . $sModule . '/inc/';

global $sFilesPathMp3;

global $sModulesPath;
$sModule = "mp3";
require_once($sModulesPath . $sModule . '/inc/header.inc.php');
require_once($sModulesPath . $sModule . '/inc/constants.inc.php');
require_once($sModulesPath . $sModule . '/inc/functions.inc.php');
require_once($sModulesPath . $sModule . '/inc/customFunctions.inc.php');

class BxSoundsUploader extends BxDolFilesUploader
{
    // constructor
    function __construct()
    {
        parent::__construct('Sound');

        $this->oModule = BxDolModule::getInstance('BxSoundsModule');
        $this->sWorkingFile = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'albums/my/add_objects';

        $iMaxByAdmin = 1024*1024*(int)getParam($this->oModule->_oConfig->getMainPrefix() . '_max_file_size');
        if ($iMaxByAdmin > 0 && $iMaxByAdmin < $this->iMaxFilesize)
            $this->iMaxFilesize = $iMaxByAdmin;

        $this->sAcceptMimeType = 'audio/*';
    }

    function getRecordFormFile($aExtras)
    {
        $sCustomRecorderObject = getApplicationContent('mp3', 'recorder', array('user' => $this->_getAuthorId(), 'password' => $this->_getAuthorPassword(), 'extra' => ''), true);
        return $this->_getRecordFormFile($sCustomRecorderObject, $aExtras);
    }

    function GenSendFileInfoForm($iFileID, $aDefaultValues = array())
    {
        $aPossibleDuration = array();
        $aPossibleDuration['duration'] = array(
            'type' => 'hidden',
            'name' => 'duration',
            'value' => isset($aDefaultValues['duration']) ? $aDefaultValues['duration'] : "0"
        );

        return $this->_GenSendFileInfoForm($iFileID, $aDefaultValues, array(), $aPossibleDuration);
    }

    function servicePerformMusicUpload($sFilePath, $aInfo, $isMoveUploadedFile = false)
    {
        $a = $this->performUpload ($sFilePath, $sRealFilename, $aInfo, $isMoveUploadedFile);

        return isset($a['id']) && $a['id'] ? $a['id'] : false;
    }

    function serviceAcceptRecordFile()
    {
        $sResult = $this->_recordMusic();
        return ($sResult!='') ? $this->GenJquieryInjection() . $sResult : '';
    }

    function serviceAcceptFileInfo()
    {
        $iAuthorId = $this->_getAuthorId();
        $sJSMusicId = (int)$_POST['file_id'];
        switch($_POST['type']) {
            case 'record':
                global $sFilesPathMp3;
                $sFileName = $iAuthorId . TEMP_FILE_NAME . MP3_EXTENSION;
                $iMusicID = uploadMusic($sFilesPathMp3 . $sFileName, $iAuthorId, $sFileName, false);
                $this->addObjectToAlbum($this->oModule->oAlbums, $_POST['extra_param_album'], $iMusicID, false);
                break;
            case 'upload':
            default:
                $iMusicID = $sJSMusicId;
                break;
        }

        if ($iMusicID && $iAuthorId) {
            $sTitle = $_POST['title'];
            $sTags = $_POST['tags'];
            $sDescription = $_POST['description'];

            $aCategories = array();
            foreach ($_POST['Categories'] as $sKey => $sVal) {
                if ($sVal != '') 
                    $aCategories[] = $sVal;
            }
            $sCategories = implode(CATEGORIES_DIVIDER, $aCategories);

            if ($this->initFile($iMusicID, $sTitle, $sCategories, $sTags, $sDescription)) {
                $this->alertAdd($iMusicID);
                return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.onSuccessSendingFileInfo("' . $sJSMusicId . '");</script>';
            }
        }
        return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.showErrorMsg("sound_failed_message");</script>';
    }

    function _embedMusic($sMusicId, $sTitle, $sDesc, $sTags, $sImage, $iDuration)
    {
        $sAuthorCheck = $this->checkAuthorBeforeAdd();
        if(empty($sAuthorCheck)) {
            $sEmbedThumbUrl = getEmbedThumbnail($this->_getAuthorId(), $sImage);
            if($sEmbedThumbUrl) {
                $aDefault = array('music' => $sMusicId, 'title' => $sTitle, 'description' => $sDesc, 'tags' => $sTags, 'duration' => $iDuration, 'image' => $sEmbedThumbUrl, 'type' => "embed");
                return $this->GenSendFileInfoForm(1, $aDefault);
            } else
                return $this->getFileAddError();
        } else
            return $sAuthorCheck;
    }

    function _recordMusic()
    {
        $sAuthorCheck = $this->checkAuthorBeforeAdd();
        if(empty($sAuthorCheck)) {
            if(checkRecord($this->_getAuthorId()))
                return $this->GenSendFileInfoForm(1, array('type' => "record"));
            else
                return $this->getFileAddError();
        } else
            return $sAuthorCheck;
    }

    /**
     * @return array with the following keys:
     *         - id: uploaded file ID if file was successfully uploaded
     *         - error: error message if file wasn't successfully uploaded
     */
    function performUpload ($sFilePath, $sRealFilename = '', $aInfo = array(), $isMoveUploadedFile = true, $aExtraParams = array())
    {
        $iOwner = $this->_getAuthorId();
        if ($this->_iOwnerId)
            $iOwner = $this->oModule->_iProfileId = $this->_iOwnerId;

        if (!$sRealFilename)
            $sRealFilename = pathinfo($sFilePath, PATHINFO_BASENAME);

        // basic checking before upload

        if ($this->checkAuthorBeforeAdd())
            return array('error' => _t('_LOGIN_REQUIRED_AE1'));

        if (!$this->oModule->_oConfig->checkAllowedExtsByFilename($sRealFilename))
            return array('error' => _t('_sys_txt_wrong_file_extension'));

        if (!$this->oModule->isAllowedAdd())
            return array('error' => _t('_Access denied'));

        // perform upload

        $GLOBALS['sModule'] = 'mp3';
        include($GLOBALS['sModulesPath'] . $GLOBALS['sModule'] . '/inc/header.inc.php');

        $this->sTempFilename = pathinfo($sRealFilename, PATHINFO_FILENAME);
        if (!($iMID = uploadMusic(process_db_input($sFilePath), $iOwner, process_db_input($sRealFilename), $isMoveUploadedFile)))
            return array('error' => _t('_sys_txt_upload_failed'));

        // update uploaded file info if needed

        if ($aInfo) {
            foreach (array('title', 'categories', 'tags', 'desc') as $sKey)
                $aInfo[$sKey] = isset($aInfo[$sKey]) ? $aInfo[$sKey] : '';

            $this->initFile($iMID, $aInfo['title'], $aInfo['categories'], $aInfo['tags'], $aInfo['desc']);
        }

        // add uploaded file to the album

        $sExt = strtolower(pathinfo($sRealFilename, PATHINFO_EXTENSION));
        $sAlbum = !empty($_POST['extra_param_album']) > 0 ? $_POST['extra_param_album'] : getParam('sys_album_default_name');
        $sAlbum = isset($aInfo['album']) ? $aInfo['album'] : $sAlbum;
        $sAutoActive = $sExt == 'mp3' && getSettingValue($sModule, "autoApprove") == true;
        $this->addObjectToAlbum($this->oModule->oAlbums, $sAlbum, $iMID, $sAutoActive);

        $this->oModule->isAllowedAdd(true, true);

        return array('id' => $iMID);

    }
}
