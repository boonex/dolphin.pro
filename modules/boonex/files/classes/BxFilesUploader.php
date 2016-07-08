<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesUploader');
bx_import('BxDolCategories');
bx_import('BxDolAlbums');
bx_import('BxDolModule');

class BxFilesUploader extends BxDolFilesUploader
{
    // constructor
    function __construct()
    {
        parent::__construct('File');

        $this->oModule = BxDolModule::getInstance('BxFilesModule');
        $this->sWorkingFile = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'albums/my/add_objects';

        $iMaxByAdmin = 1024*1024*(int)getParam($this->oModule->_oConfig->getMainPrefix() . '_max_file_size');
        if ($iMaxByAdmin > 0 && $iMaxByAdmin < $this->iMaxFilesize)
            $this->iMaxFilesize = $iMaxByAdmin;
    }

    function GenSendFileInfoForm($iFileID, $aDefaultValues = array())
    {
        $sFileUrl = "";
        if(isset($aDefaultValues['image']))
            $sFileUrl = $aDefaultValues['image'];
        else if(!empty($iFileID)) {
            $aFileInfo = BxDolService::call('files', 'get_file_array', array($iFileID), 'Search');
            $sFileUrl = $aFileInfo['file'];
        }
        $sProtoEl = '<img src="' . $sFileUrl . '" />';

        $aPossibleImage = array();
        $aPossibleImage['preview_image'] = array(
            'type' => 'custom',
            'content' => $sProtoEl,
            'caption' => _t('_bx_files_preview'),
        );

        return $this->_GenSendFileInfoForm($iFileID, $aDefaultValues, $aPossibleImage, array());
    }

    function serviceAcceptFileInfo()
    {
        $iAuthorId = $this->_getAuthorId();
        $sJSFileId = (int)$_POST['file_id'];
        switch($_POST['type']) {
            case 'upload':
            default:
                $iFileID = $sJSFileId;
                break;
        }

        if ($iFileID && $iAuthorId) {
            $sTitle = $_POST['title'];
            $sTags = $_POST['tags'];
            $sDescription = $_POST['description'];
            $iAllowDownload = (int)$_POST['AllowDownload'];

            $aCategories = array();
            foreach ($_POST['Categories'] as $sVal) {
                if ($sVal != '')
                    $aCategories[] = $sVal;
            }
            $sCategories = implode(CATEGORIES_DIVIDER, $aCategories);

            if ($this->initFile($iFileID, $sTitle, $sCategories, $sTags, $sDescription, array('AllowDownload' => $iAllowDownload))) {
                $this->alertAdd($iFileID);
                return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.onSuccessSendingFileInfo("' . $sJSFileId . '");</script>';
            }
        }
        return '<script type="text/javascript">parent.' . $this->_sJsPostObject . '.showErrorMsg("file_failed_message");</script>';
    }

    function servicePerformFileUpload ($sTmpFilename, $aFileInfo, $isUpdateThumb = '')
    {
        return $this->performFileUpload($sTmpFilename, $aFileInfo, false, $sTmpFilename);
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

        $iFileSize = filesize($sFilePath);
        if (!$iFileSize || $iFileSize > $this->iMaxFilesize)
            return array('error' => _t('_' . $this->oModule->_oConfig->getMainPrefix() . '_size_error', _t_format_size($this->iMaxFilesize)));

        $this->sTempFilename = pathinfo($sRealFilename, PATHINFO_FILENAME);
        if (!($iMID = $this->performFileUpload($sFilePath, array(), $isMoveUploadedFile, $sRealFilename, isset($aExtraParams['file_type']) ? $aExtraParams['file_type'] : '')))
            return array('error' => _t('_sys_txt_upload_failed'));

        return array('id' => $iMID);
    }

    function initFile($iMedID, $sTitle, $sCategories = '', $sTags = '', $sDesc = '', $aCustom = array())
    {
        $aCustom['Approved'] = getParam('bx_files_activation') == 'on' ? 'approved' : 'pending';
        return parent::initFile($iMedID, $sTitle, $sCategories, $sTags, $sDesc, $aCustom);
    }

    function performFileUpload($sTmpFile, $aFileInfo, $isMoveUploadedFile = true, $sOriginalFilename = '', $sFileType = '')
    {
        $iLastID = -1;

        // checker for flash uploader
        if (!$this->oModule->_iProfileId)
            $this->oModule->_iProfileId = $this->_iOwnerId;
        if (! $this->_iOwnerId || file_exists($sTmpFile) == false || !$this->oModule->isAllowedAdd())
            return false;

        $sMediaDir = $this->oModule->_oConfig->getFilesPath();

        if (! $sMediaDir) {
            @unlink($sTmpFile);
            return false;
        }

        $sTempFileName = $sMediaDir . $this->_iOwnerId . '_temp';
        @unlink($sTempFileName);

        if (($isMoveUploadedFile && is_uploaded_file($sTmpFile)) || !$isMoveUploadedFile) {

            if ($isMoveUploadedFile) {
                move_uploaded_file($sTmpFile, $sTempFileName);
                @unlink($sTmpFile);
            } else {
                $sTempFileName = $sTmpFile;
            }

            @chmod($sTempFileName, 0666);
            if (file_exists($sTempFileName)) {
                $sOriginalFilenameSafe = process_db_input($sOriginalFilename, BX_TAGS_STRIP);
                $sExtension = strrchr($sOriginalFilename, '.');
                $iFileSize = filesize($sTempFileName);
                $sFileSize = sprintf("%u", $iFileSize / 1024);
                $sCurTime = time();

                if (is_array($aFileInfo) && count($aFileInfo) > 0) {
                    $aFileInfo['medSize'] = $iFileSize;
                    $iLastID = $this->insertSharedMediaToDb($sExtension, $aFileInfo, $this->_iOwnerId, array(
                        'AllowDownload' => (int)$aFileInfo['AllowDownload'],
                        'Type' => $aFileInfo['Type'],
                    ));
                } else {
                    $aPassArray = array(
                        'medProfId' => $this->_iOwnerId,
                        'medTitle' => $sOriginalFilenameSafe,
                        'medDesc' => $sOriginalFilenameSafe,
                        'medExt' => trim($sExtension, '.'),
                        'medDate' => $sCurTime,
                        'medUri' => $sCurTime,
                        'medSize' => $iFileSize
                    );

                    if (getParam('bx_files_activation') == 'on') {
                        $bAutoActivate = true;
                        $aPassArray['Approved'] = 'approved';
                    } else {
                        $bAutoActivate = false;
                        $aPassArray['Approved'] = 'pending';
                    }

                    if ($sFileType)
                        $aPassArray['Type'] = process_db_input($sFileType, BX_TAGS_STRIP);
                    $iLastID = $this->oModule->_oDb->insertData($aPassArray);
                    $this->addObjectToAlbum($this->oModule->oAlbums, $_POST['extra_param_album'], $iLastID, $bAutoActivate);
                    $this->oModule->isAllowedAdd(true, true);
                }

                $sFunc = ($isMoveUploadedFile) ? 'rename' : 'copy';
                $sFilePostfix = '_' . sha1($sCurTime);
                if (! $sFunc($sTempFileName, $sMediaDir . $iLastID . $sFilePostfix)) {
                    @unlink($sTempFileName);
                    return false;
                }

                $this->sSendFileInfoFormCaption = $iLastID . $sExtension . " ({$sFileSize}kb)";

                $sFile = $sMediaDir . $iLastID . $sExtension;
            }
        }

        return $iLastID;
    }

    function getUploadFormArray (&$aForm, $aAddObjects = array())
    {
        $aForm = parent::getUploadFormArray($aForm, $aAddObjects);
        $aForm['AllowView'] = $this->oModule->oPrivacy->getGroupChooser($this->_iOwnerId, $this->oModule->_oConfig->getUri(), 'download');
        return $aForm;
    }
}
