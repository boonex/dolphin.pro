<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesUploader');
bx_import('BxDolCategories');
bx_import('BxDolAlbums');
bx_import('BxDolModule');

global $sIncPath;
global $sModulesPath;
global $sFilesPath;
global $sFilesUrl;
global $oDb;
require_once($sIncPath . 'db.inc.php');

$sModule = "video";
$sModulePath = $sModulesPath . $sModule . '/inc/';

global $sModulesUrl;
require_once($sModulesPath . $sModule . '/inc/header.inc.php');
require_once($sModulesPath . $sModule . '/inc/constants.inc.php');
require_once($sModulesPath . $sModule . '/inc/functions.inc.php');
require_once($sModulesPath . $sModule . '/inc/customFunctions.inc.php');

class BxVideosUploader extends BxDolFilesUploader
{
    // constructor
    function __construct()
    {
        parent::__construct('Video');

        $this->oModule = BxDolModule::getInstance('BxVideosModule');
        $this->sWorkingFile = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'albums/my/add_objects';

        $iMaxByAdmin = 1024*1024*(int)getParam($this->oModule->_oConfig->getMainPrefix() . '_max_file_size');
        if ($iMaxByAdmin > 0 && $iMaxByAdmin < $this->iMaxFilesize)
            $this->iMaxFilesize = $iMaxByAdmin;

        $this->sAcceptMimeType = '*';
    }

    function getEmbedFormFile()
    {
        return $this->_getEmbedFormFile();
    }

    function getRecordFormFile($aExtras)
    {
        $sCustomRecorderObject = getApplicationContent('video', 'recorder', array('user' => $this->_getAuthorId(), 'password' => $this->_getAuthorPassword(), 'extra' => ''), true);
        return $this->_getRecordFormFile($sCustomRecorderObject, $aExtras);
    }

    function GenSendFileInfoForm($iFileID, $aDefaultValues = array())
    {
        $sVideoUrl = "";
        if(isset($aDefaultValues['image']))
            $sVideoUrl = $aDefaultValues['image'];
        else if(!empty($iFileID)) {
            $aVideoInfo = BxDolService::call('videos', 'get_video_array', array($iFileID), 'Search');
            $sVideoUrl = $aVideoInfo['file'];
        }

        $sVideoUrl .= (false === strpos($sVideoUrl, '?') ? '?' : '&') . '_t=' . time();

        $sProtoEl = '<img src="' . $sVideoUrl . '" />';

        $aPossibleImage = array();
        $aPossibleImage['preview_image'] = array(
            'type' => 'custom',
            'content' => $sProtoEl,
            'caption' => _t('_bx_videos_preview'),
        );

        $aPossibleDuration = array();
        $aPossibleDuration['duration'] = array(
            'type' => 'hidden',
            'name' => 'duration',
            'value' => isset($aDefaultValues['duration']) ? $aDefaultValues['duration'] : "0"
        );

        return $this->_GenSendFileInfoForm($iFileID, $aDefaultValues, $aPossibleImage, $aPossibleDuration);
    }

    function serviceCancelFileInfo()
    {
        if ($bRet = parent::serviceCancelFileInfo())
            deleteVideo($iFileID, $this->oModule->_oConfig->aFilesConfig);
        return $bRet;
    }

    function servicePerformVideoUpload($sFilePath, $aInfo, $isMoveUploadedFile = false)
    {
        $a = $this->performUpload ($sFilePath, $sRealFilename, $aInfo, $isMoveUploadedFile);

        return isset($a['id']) && $a['id'] ? $a['id'] : false;
    }

    function serviceAcceptRecordFile()
    {
        $sResult = $this->_recordVideo();
        return ($sResult!='') ? $this->GenJquieryInjection() . $sResult : '';
    }

    function serviceAcceptEmbedFile()
    {
        $sErrorReturn = '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.showErrorMsg("video_embed_failed_message");parent.' . $this->_sJsPostObject . '.resetEmbed();</script>';
        $sVideoId = substr(trim($_POST['embed']), -11);
        if(empty($sVideoId)) return $sErrorReturn;
       
        $aSiteInfo = getSiteInfo('https://www.youtube.com/watch?v=' . $sVideoId, array(
            'name' => array(),
            'duration' => array(),
            'thumbnailUrl' => array('tag' => 'link', 'content_attr' => 'href'),
        ));

        $aSiteInfo['duration'] = bx_parse_time_duration($aSiteInfo['duration']);

        $sTitle = $aSiteInfo['name'];
        $sDesc = $aSiteInfo['description'];
        $sTags = $aSiteInfo['keywords'];
        $sImage = $aSiteInfo['thumbnailUrl'];
        $iDuration = (int)$aSiteInfo['duration'];

        if(empty($sTitle)) return $sErrorReturn;

        $sResult = $this->_embedVideo($sVideoId, $sTitle, $sDesc, $sTags, $sImage, $iDuration);
        return ($sResult!='') ? $this->GenJquieryInjection() . $sResult : '';
    }

    function serviceAcceptFileInfo()
    {
        global $sModule;
        $iAuthorId = $this->_getAuthorId();
        $sJSVideoId = (int)$_POST['file_id'];
        switch($_POST['type']) {
            case 'embed':
                $iVideoID = (int)embedVideo($iAuthorId, $_POST['video'], $_POST['duration'], $this->oModule->_oConfig->aFilesConfig);
                $bUpdateCounter = getSettingValue($sModule, "autoApprove") == TRUE_VAL ? true : false;
                $this->addObjectToAlbum($this->oModule->oAlbums, $_POST['extra_param_album'], $iVideoID, $bUpdateCounter);
                break;
            case 'record':
                $iVideoID = (int)recordVideo($iAuthorId);
                $this->addObjectToAlbum($this->oModule->oAlbums, $_POST['extra_param_album'], $iVideoID, false);
                break;
            case 'upload':
            default:
                $iVideoID = $sJSVideoId;
                break;
        }

        if ($iVideoID && $iAuthorId) {
            $sTitle = $_POST['title'];
            $sTags = $_POST['tags'];
            $sDescription = $_POST['description'];

            $aCategories = array();
            foreach ($_POST['Categories'] as $sKey => $sVal) {
                if ($sVal != '')
                    $aCategories[] = $sVal;
            }
            $sCategories = implode(CATEGORIES_DIVIDER, $aCategories);

            if ($this->initFile($iVideoID, $sTitle, $sCategories, $sTags, $sDescription)) {
                $this->alertAdd($iVideoID);
                return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.onSuccessSendingFileInfo("' . $sJSVideoId . '");</script>';
            }
        }
        return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.showErrorMsg("video_failed_message");</script>';
    }

    function _embedVideo($sVideoId, $sTitle, $sDesc, $sTags, $sImage, $iDuration)
    {
        $sAuthorCheck = $this->checkAuthorBeforeAdd();
        if(empty($sAuthorCheck)) {
            $sEmbedThumbUrl = getEmbedThumbnail($this->_getAuthorId(), $sImage, $this->oModule->_oConfig->aFilesConfig);
            if($sEmbedThumbUrl) {
                $this->oModule->isAllowedAdd(true);
                $aDefault = array('video' => $sVideoId, 'title' => $sTitle, 'description' => $sDesc, 'tags' => $sTags, 'duration' => $iDuration, 'image' => $sEmbedThumbUrl, 'type' => "embed");
                return $this->GenSendFileInfoForm(1, $aDefault);
            } else
                return $this->getFileAddError();
        } else
            return $sAuthorCheck;
    }

    function _recordVideo()
    {
        $sAuthorCheck = $this->checkAuthorBeforeAdd();
        if(empty($sAuthorCheck)) {
            $sRecordThumbUrl = getRecordThumbnail($this->_getAuthorId());
            if($sRecordThumbUrl) {
                $this->oModule->isAllowedAdd(true);
                $aDefault = array('image' => $sRecordThumbUrl, 'type' => "record");
                return $this->GenSendFileInfoForm(1, $aDefault);
            } else
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

        $GLOBALS['sModule'] = 'video';
        include($GLOBALS['sModulesPath'] . $GLOBALS['sModule'] . '/inc/header.inc.php');

        $this->sTempFilename = pathinfo($sRealFilename, PATHINFO_FILENAME);
        if (!($iMID = uploadVideo(process_db_input($sFilePath), $iOwner, $isMoveUploadedFile, '', process_db_input($sRealFilename), $this->oModule->_oConfig->aFilesConfig)))
            return array('error' => _t('_sys_txt_upload_failed'));

        // update uploaded file info if needed

        if ($aInfo) {
            foreach (array('title', 'categories', 'tags', 'desc') as $sKey)
                $aInfo[$sKey] = isset($aInfo[$sKey]) ? $aInfo[$sKey] : '';

            $this->initFile($iMID, $aInfo['title'], $aInfo['categories'], $aInfo['tags'], $aInfo['desc']);
        }

        // add uploaded file to the album

        $sAlbum = empty($_POST['extra_param_album']) ? getParam('sys_album_default_name') : $_POST['extra_param_album'];
        $aAlbumParams = isset($_POST['extra_param_albumPrivacy']) ? array('privacy' => (int)$_POST['extra_param_albumPrivacy']) : array();
        $this->addObjectToAlbum($this->oModule->oAlbums, !empty($aInfo['album']) ? $aInfo['album'] : $sAlbum, $iMID, false, $iOwner, $aAlbumParams);

        // perfom action

        $this->oModule->isAllowedAdd(true, true);

        // return uploaded file ID
    
        return array('id' => $iMID);
    }
}
