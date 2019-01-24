<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

//require_once(BX_DIRECTORY_PATH_MODULES . 'boonex/photos/classes/BxPhotosModule.php' );

bx_import('BxDolFilesUploader');
bx_import('BxDolCategories');
bx_import('BxDolAlbums');
bx_import('BxDolModule');

global $sModulesPath;
global $sModulesUrl;
$sModule = "photo";
global $sFilesPath;
global $sFilesUrl;
require_once($sModulesPath . $sModule . '/inc/header.inc.php');
require_once($sModulesPath . $sModule . '/inc/constants.inc.php');
require_once($sModulesPath . $sModule . '/inc/functions.inc.php');

class BxPhotosUploader extends BxDolFilesUploader
{
    // constructor
    function __construct()
    {
        parent::__construct('Photo');

        $this->oModule = BxDolModule::getInstance('BxPhotosModule');
        $this->sWorkingFile = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'albums/my/add_objects';

        $this->sAcceptMimeType = 'image/*';
        $this->bImageAutoRotate = 1;
    }

    function getEmbedFormFile()
    {
        $sKey = $this->oModule->_oConfig->getGlParam('flickr_photo_api');
        return ($sKey != '') ? $this->_getEmbedFormFile() : MsgBox(_t('_bx_photos_flickr_key_not_exist'));
    }

    function getRecordFormFile($aExtras)
    {
        $sCustomRecorderObject = getApplicationContent('photo', 'shooter', array('id' => $this->_getAuthorId(), 'password' => $this->_getAuthorPassword(), 'extra' => ''), true);
        return $this->_getRecordFormFile($sCustomRecorderObject, $aExtras);
    }

    function GenSendFileInfoForm($iFileID, $aDefaultValues = array())
    {
        $sPhotoUrl = "";
        if(isset($aDefaultValues['image']))
            $sPhotoUrl = $aDefaultValues['image'];
        else if(!empty($iFileID)) {
            $aPhotoInfo = BxDolService::call('photos', 'get_photo_array', array($iFileID), 'Search');
            $sPhotoUrl = $aPhotoInfo['file'];
        }
        $sProtoEl = '<img src="' . $sPhotoUrl . '" />';

        $aPossibleImage = array();
        $aPossibleImage['preview_image'] = array(
            'type' => 'custom',
            'content' => $sProtoEl,
            'caption' => _t('_bx_photos_preview'),
        );

        return $this->_GenSendFileInfoForm($iFileID, $aDefaultValues, $aPossibleImage, array());
    }

    function serviceAcceptRecordFile()
    {
        $sResult = $this->_recordPhoto();
        return ($sResult!='') ? $this->GenJquieryInjection() . $sResult : '';
    }

    function serviceAcceptEmbedFile()
    {
        $sErrorReturn = '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.showErrorMsg("photo_embed_failed_message");parent.' . $this->_sJsPostObject . '.resetEmbed();</script>';

        $sEmbed = process_db_input(bx_get('embed'));

        $aMatches = array();
        if(!preg_match("/^https?:\/\/(www.)?flickr.com\/photos\/([0-9A-Za-z_@-]+)\/([0-9]{11})\/$/i", $sEmbed, $aMatches))
            return $sErrorReturn;

        if(empty($aMatches[3]))
            return $sErrorReturn;

        $sPhotoId = $aMatches[3];

        $sApiKey = $this->oModule->_oConfig->getGlParam('flickr_photo_api');
        $sPhotoUrl = str_replace("#api_key#", $sApiKey, FLICKR_PHOTO_RSS);
        $sPhotoUrl = str_replace("#photo#", $sPhotoId, $sPhotoUrl);
        $sPhotoDataOriginal = $this->embedReadUrl($sPhotoUrl);

        $aResult = $this->embedGetTagAttributes($sPhotoDataOriginal, "rsp");
        if($aResult["stat"] == "fail") {
            $aResult = $this->embedGetTagAttributes($sPhotoDataOriginal, "err");
            $sNewError = $aResult["msg"];
            $sErrorReturn = '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.changeErrorMsgBoxMsg("photo_embed_failed_message", "'.$sNewError.'"); parent.' . $this->_sJsPostObject . '.showErrorMsg("photo_embed_failed_message");parent.' . $this->_sJsPostObject . '.resetEmbed();</script>';
            return $sErrorReturn;
        }

        $sPhotoData = $this->embedGetTagContents($sPhotoDataOriginal, "photo");
        if(empty($sPhotoData)) return $sErrorReturn;

        $sTitle = $this->embedGetTagContents($sPhotoData, "title");
        $sDesc = $this->embedGetTagContents($sPhotoData, "description");
        $sTags = strip_tags($this->embedGetTagContents($sPhotoData, "tags"));
        $sTags = trim(str_replace("\n", " ", $sTags));
        $sTags = trim(str_replace("\t", "", $sTags));

        $aPhoto = $this->embedGetTagAttributes($sPhotoDataOriginal, "photo");
        $sImage = str_replace("#id#", $sPhotoId, FLICKR_PHOTO_URL);
        $sImage = str_replace("#farm#", $aPhoto['farm'], $sImage);
        $sImage = str_replace("#server#", $aPhoto['server'], $sImage);
        $sExt = "jpg";
        $sMode = "";
        if(isset($aPhoto['originalsecret'])) {
            $aPhoto['secret'] = $aPhoto['originalsecret'];
            $sExt = $aPhoto['originalformat'];
            $sMode = "_o";
        }
        $sImage = str_replace("#secret#", $aPhoto['secret'], $sImage);
        $sImage = str_replace("#mode#", $sMode, $sImage);
        $sImage = str_replace("#ext#", $sExt, $sImage);
        if(empty($sTitle)) return $sErrorReturn;

        $sResult = $this->_embedPhoto($sPhotoId, $sTitle, $sDesc, $sTags, $sImage);
        return ($sResult!='') ? $this->GenJquieryInjection() . $sResult : '';
    }

    function serviceAcceptFileInfo()
    {
        $iAuthorId = $this->_getAuthorId();

        $sType = process_db_input($_POST['type']);
        $iFileId = (int)$_POST['file_id'];
        switch($sType) {
            case 'embed':
            case 'record':
                global $sFilesPath;
                $iPhotoId = (int)$this->performPhotoUpload($sFilesPath . $iAuthorId . IMAGE_EXTENSION, array(), false, false);
                removeFiles($iAuthorId);
                break;

            case 'upload':
            default:
                $iPhotoId = $iFileId;
                break;
        }

        if($iPhotoId && $iAuthorId) {
            $sTitle = $_POST['title'];
            $sTags = $_POST['tags'];
            if($sType == 'embed' && !empty($sTags) && strpos($sTags, BX_DOL_TAGS_DIVIDER) === false)
                $sTags = str_replace(' ', BX_DOL_TAGS_DIVIDER, $sTags);
            $sDescription = $_POST['description'];

            $aCategories = array();
            foreach($_POST['Categories'] as $sKey => $sVal)
                if($sVal != '')
                    $aCategories[] = $sVal;
            $sCategories = implode(CATEGORIES_DIVIDER, $aCategories);

            if($this->initFile($iPhotoId, $sTitle, $sCategories, $sTags, $sDescription)) {
                $this->alertAdd($iPhotoId);

                return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.onSuccessSendingFileInfo("' . $iFileId . '");</script>';
            }
        }

        return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.showErrorMsg("photo_failed_message");</script>';
    }

    function _embedPhoto($sPhotoId, $sTitle, $sDesc, $sTags, $sImage)
    {
        $sAuthorCheck = $this->checkAuthorBeforeAdd();
        if(empty($sAuthorCheck)) {
            $sEmbedThumbUrl = photo_getEmbedThumbnail($this->_getAuthorId(), $sImage);
            if($sEmbedThumbUrl) {
                $aDefault = array('photo' => $sPhotoId, 'title' => $sTitle, 'description' => $sDesc, 'tags' => $sTags, 'image' => $sEmbedThumbUrl, 'type' => "embed");
                return $this->GenSendFileInfoForm(1, $aDefault);
            } else
                return $this->getFileAddError();
        } else
            return $sAuthorCheck;
    }

    function servicePerformPhotoUpload ($sTmpFilename, $aFileInfo, $isUpdateThumb, $iAuthorId = 0)
    {
        if (!$iAuthorId)
            $iAuthorId = $this->_iOwnerId;
        return $this->performPhotoUpload($sTmpFilename, $aFileInfo, $isUpdateThumb, false, 0, $iAuthorId);
    }

    function servicePerformPhotoReplace($sTmpFilename, $aFileInfo, $isUpdateThumb, $iPhotoID)
    {
        return $this->performPhotoUpload($sTmpFilename, $aFileInfo, $isUpdateThumb, false, $iPhotoID);
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

        $this->sTempFilename = pathinfo($sRealFilename, PATHINFO_FILENAME);
        if (!($iMID = $this->performPhotoUpload($sFilePath, $aInfo, false, $isMoveUploadedFile)))
            return array('error' => _t('_sys_txt_upload_failed'));
        
        return array('id' => $iMID);
    }

    function getUploadHtml5FileImageTransform ()
    {
        return array(
            'maxWidth' => $this->oModule->_oConfig->getGlParam('client_width'),
            'maxHeight' => $this->oModule->_oConfig->getGlParam('client_height'),            
            'quality' => 0.86, // jpeg quality
        );
    }

    function _recordPhoto()
    {
        $sAuthorCheck = $this->checkAuthorBeforeAdd();
        if(empty($sAuthorCheck)) {
            global $sFilesPath;
            $sRecordThumbUrl = photo_getRecordThumbnail($this->_getAuthorId());
            if($sRecordThumbUrl) {
                $aDefault = array('image' => $sRecordThumbUrl, 'type' => "record");
                return $this->GenSendFileInfoForm(1, $aDefault);
            } else
                return $this->getFileAddError();
        } else
            return $sAuthorCheck;
    }

    function initFile($iMedID, $sTitle, $sCategories = '', $sTags = '', $sDesc = '', $aCustom = array())
    {
        $aCustom['Approved'] = getParam('bx_photos_activation') == 'on' ? 'approved' : 'pending';
        return parent::initFile($iMedID, $sTitle, $sCategories, $sTags, $sDesc, $aCustom);
    }

    // simple upload
    function performPhotoUpload($sTmpFile, $aFileInfo, $bAutoAssign2Profile = false, $isMoveUploadedFile = true, $iChangingPhotoID = 0, $iAuthorId = 0)
    {
        global $dir;

        $iLastID = -1;

        if (!$iAuthorId)
            $iAuthorId = $this->_iOwnerId;
        $this->oModule = BxDolModule::getInstance('BxPhotosModule');
        // checker for flash uploader
        if (!$this->oModule->_iProfileId)
            $this->oModule->_iProfileId = $this->_iOwnerId;
        if (!$iAuthorId || file_exists($sTmpFile) == false || !$this->oModule->isAllowedAdd(FALSE, FALSE, FALSE))
            return false;

        $sMediaDir = $this->oModule->_oConfig->getFilesPath();

        if (!$sMediaDir) {
            @unlink($sTmpFile);
            return false;
        }

        $sTempFileName = $sMediaDir . $iAuthorId . '_temp';
        @unlink($sTempFileName);

        if (($isMoveUploadedFile && is_uploaded_file($sTmpFile)) || !$isMoveUploadedFile) {

            if ($isMoveUploadedFile) {
                move_uploaded_file($sTmpFile, $sTempFileName);
                @unlink($sTmpFile);
            } else {
                $sTempFileName = $sTmpFile;
            }

            @chmod($sTempFileName, 0644);
            if(file_exists($sTempFileName) && filesize($sTempFileName)>0) {
                $aSize = getimagesize($sTempFileName);
                if (!$aSize) {
                    @unlink($sTempFileName);
                    return false;
                }

                switch($aSize[2]) {
                    case IMAGETYPE_JPEG: $sExtension = '.jpg'; break;
                    case IMAGETYPE_GIF:  $sExtension = '.gif'; break;
                    case IMAGETYPE_PNG:  $sExtension = '.png'; break;
                    default:
                        @unlink($sTempFileName);
                        return false;
                }

                $sStatus = 'processing';
                $iImgWidth = (int)$aSize[0];
                $iImgHeight = (int)$aSize[1];
                $sDimension = $iImgWidth.'x'.$iImgHeight;
                $sFileSize = sprintf("%u", filesize($sTempFileName) / 1024);

                if ($iChangingPhotoID==0) {
                    if (is_array($aFileInfo) && count($aFileInfo)>0) {
                        $aFileInfo['dimension'] = $sDimension;
                        $iLastID = $this->insertSharedMediaToDb($sExtension, $aFileInfo, $iAuthorId);
                    } else {
                        $sExtDb = trim($sExtension, '.');
                        $sMedUri = $sCurTime = time();

                        $sTitleDescTemp = ($this->sTempFilename != '') ? $this->sTempFilename : $iAuthorId . '_temp';
                        if (getParam('bx_photos_activation') == 'on') {
                            $bAutoActivate = true;
                            $sStatus = 'approved';
                        } else {
                            $bAutoActivate = false;
                            $sStatus = 'pending';
                        }

                        $sAlbum = $_POST['extra_param_album'];
                        $aAlbumParams = isset($_POST['extra_param_albumPrivacy']) ? array('privacy' => (int)$_POST['extra_param_albumPrivacy']) : array();

                        $iLastID = $this->oModule->_oDb->insertData(array('medProfId'=>$iAuthorId, 'medExt'=>$sExtDb, 'medTitle'=>$sTitleDescTemp, 'medUri'=>$sMedUri, 'medDesc'=>$sTitleDescTemp, 'medTags'=>'', 'Categories'=>PROFILE_PHOTO_CATEGORY, 'medSize'=>$sDimension, 'Approved'=>$sStatus, 'medDate'=>$sCurTime));
                        $this->addObjectToAlbum($this->oModule->oAlbums, $sAlbum, $iLastID, $bAutoActivate, $iAuthorId, $aAlbumParams);
                        $this->oModule->isAllowedAdd(true, true);
                    }
                } else {
                    $iLastID = $iChangingPhotoID;
                    $this->updateMediaShared($iLastID, $aFileInfo);
                }

                $sFunc = ($isMoveUploadedFile) ? 'rename' : 'copy';
                if (! $sFunc($sTempFileName, $sMediaDir . $iLastID . $sExtension)) {
                    @unlink($sTempFileName);
                    return false;
                }

                $this->sSendFileInfoFormCaption = $iLastID . $sExtension . " ({$sDimension}) ({$sFileSize}kb)";

                $sFile = $sMediaDir . $iLastID . $sExtension;

                // watermark postprocessing
                if (getParam('enable_watermark') == 'on') {
                    $iTransparent = getParam('transparent1');
                    $sWaterMark = $dir['profileImage'] . getParam('Water_Mark');

                    if (strlen(getParam('Water_Mark')) && file_exists($sWaterMark))
                        applyWatermark($sFile, $sFile, $sWaterMark, $iTransparent);
                }

                // generate present pics
                foreach ($this->oModule->_oConfig->aFilesConfig as $sKey => $aValue) {
                    if (!isset($aValue['size_def']))
                        continue;
                    $iWidth  = (int)$this->oModule->_oConfig->getGlParam($sKey . '_width');
                    $iHeight = (int)$this->oModule->_oConfig->getGlParam($sKey . '_height');
                    if ($iWidth == 0)
                        $iWidth = $aValue['size_def'];
                    if ($iHeight == 0)
                        $iHeight = $aValue['size_def'];
                    $sNewFilePath = $sMediaDir . $iLastID . $aValue['postfix'];
                    $iRes = imageResize($sFile, $sNewFilePath, $iWidth, $iHeight, true, isset($aValue['square']) && $aValue['square']);
                    if ($iRes != 0)
                        return false; //resizing was failed
                    @chmod($sNewFilePath, 0644);
                }

                $aOwnerInfo = getProfileInfo($iAuthorId);
                $bAutoAssign2Profile = ($aOwnerInfo['Avatar']==0) ? true : $bAutoAssign2Profile;
                if ($bAutoAssign2Profile && $iLastID > 0) {
                    $this->setPrimarySharedPhoto($iLastID, $iAuthorId);
                    createUserDataFile($iAuthorId);
                }

                if (is_array($aFileInfo) && count($aFileInfo) > 0)
                    $this->alertAdd($iLastID, true);
            }
        }

        return $iLastID;
    }

    function setPrimarySharedPhoto($iPhotoID, $iAuthorId = 0)
    {
        
    }

    function updateMediaShared($iMediaID, $aFileInfo)
    {
        $sMedUri = uriGenerate($aFileInfo['medTitle'], $this->oModule->_oDb->sFileTable, $this->oModule->_oDb->aFileFields['medUri']);
        return $this->oModule->_oDb->updateData($iMediaID, array('medTitle' => $aFileInfo['medTitle'], 'medUri' => $sMedUri, 'medDesc' => $aFileInfo['medDesc'], 'medDate' => time()));
    }
}
