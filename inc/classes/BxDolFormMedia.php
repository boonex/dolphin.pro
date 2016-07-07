<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxTemplFormView');

/**
 * Base class for form which is using a lot of media uploads
 */
class BxDolFormMedia extends BxTemplFormView
{
    var $_aMedia = array();

    function __construct ($aCustomForm)
    {
        if (isset($aCustomForm['inputs']['allow_post_in_forum_to']['type'])) {
            $oModuleDb = new BxDolModuleDb(); 
            if (!$oModuleDb->getModuleByUri('forum'))
                $aCustomForm['inputs']['allow_post_in_forum_to']['type'] = 'hidden';
        }

        parent::__construct ($aCustomForm);
    }

    /**
     * upload photos to photos module
     * @param $sTag a tag to accociate with an image
     * @param $sCat a category to accociate with an image
     * @param $sName form field name with a files
     * @param $sTitle form field name with image titles
     * @param $sTitleAlt alternative form field name with image title
     * @return array of uploaded images ids
     */
    function uploadPhotos ($sTag, $sCat, $sName = 'images', $sTitle = 'images_titles', $sTitleAlt = 'title')
    {
        $aRet = array ();
        $aTitles = $this->getCleanValue($sTitle);

        foreach ($_FILES[$sName]['tmp_name'] as $i => $sUploadedFile) {
            $aFileInfo = array (
                'medTitle' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'medDesc' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'medTags' => $sTag,
                'Categories' => array($sCat),
            );
            $aPathInfo = pathinfo ($_FILES[$sName]['name'][$i]);
            $sTmpFile = BX_DIRECTORY_PATH_ROOT . 'tmp/i' . time() . $i . getLoggedId() . '.' . $aPathInfo['extension'];
            if (move_uploaded_file($sUploadedFile,  $sTmpFile)) {
                $iRet = BxDolService::call('photos', 'perform_photo_upload', array($sTmpFile, $aFileInfo, false), 'Uploader');
                @unlink ($sTmpFile);
                if ($iRet)
                    $aRet[] = $iRet;
            }
        }
        return $aRet;
    }

    /**
     * upload videos to videos module
     * @param $sTag a tag to accociate with an image
     * @param $sCat a category to accociate with an image
     * @param $sName form field name with a files
     * @param $sTitle form field name with image titles
     * @param $sTitleAlt alternative form field name with image title
     * @return array of uploaded images ids
     */
    function uploadVideos ($sTag, $sCat, $sName = 'videos', $sTitle = 'videos_titles', $sTitleAlt = 'title')
    {
        $aRet = array ();
        $aTitles = $this->getCleanValue($sTitle);

        foreach ($_FILES[$sName]['tmp_name'] as $i => $sUploadedFile) {
            $aFileInfo = array (
                'title' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'desc' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'tags' => $sTag,
                'categories' => $sCat,
            );
            $aPathInfo = pathinfo ($_FILES[$sName]['name'][$i]);
            $sTmpFile = BX_DIRECTORY_PATH_ROOT . 'tmp/v' . time() . $i . getLoggedId() . '.' . $aPathInfo['extension'];
            if (move_uploaded_file($sUploadedFile,  $sTmpFile)) {
                $iRet = BxDolService::call('videos', 'perform_video_upload', array($sTmpFile, $aFileInfo, false), 'Uploader');
                @unlink ($sTmpFile);
                if ($iRet)
                    $aRet[] = $iRet;
            }
        }
        return $aRet;
    }

    /**
     * upload sounds to sounds module
     * @param $sTag a tag to accociate with an image
     * @param $sCat a category to accociate with an image
     * @param $sName form field name with a files
     * @param $sTitle form field name with image titles
     * @param $sTitleAlt alternative form field name with image title
     * @return array of uploaded images ids
     */
    function uploadSounds ($sTag, $sCat, $sName = 'sounds', $sTitle = 'sounds_titles', $sTitleAlt = 'title')
    {
        $aRet = array ();
        $aTitles = $this->getCleanValue($sTitle);

        foreach ($_FILES[$sName]['tmp_name'] as $i => $sUploadedFile) {
            $aFileInfo = array (
                'title' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'desc' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'tags' => $sTag,
                'categories' => $sCat,
            );
            $aPathInfo = pathinfo ($_FILES[$sName]['name'][$i]);
            $sTmpFile = BX_DIRECTORY_PATH_ROOT . 'tmp/s' . time() . $i . getLoggedId() . '.' . $aPathInfo['extension'];
            if (move_uploaded_file($sUploadedFile,  $sTmpFile)) {
                $iRet = BxDolService::call('sounds', 'perform_music_upload', array($sTmpFile, $aFileInfo, false), 'Uploader');
                @unlink ($sTmpFile);
                if ($iRet)
                    $aRet[] = $iRet;
            }
        }
        return $aRet;
    }

    /**
     * upload files to files module
     * @param $sTag a tag to accociate with an image
     * @param $sCat a category to accociate with an image
     * @param $sName form field name with a files
     * @param $sTitle form field name with image titles
     * @param $sTitleAlt alternative form field name with image title
     * @return array of uploaded images ids
     */
    function uploadFiles ($sTag, $sCat, $sName = 'files', $sTitle = 'files_titles', $sTitleAlt = 'title')
    {
        $aRet = array ();
        $aTitles = $this->getCleanValue($sTitle);

        foreach ($_FILES[$sName]['tmp_name'] as $i => $sUploadedFile) {
            $aFileInfo = array (
                'medTitle' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'medDesc' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'medTags' => $sTag,
                'Categories' => array($sCat),
                'Type' => $_FILES[$sName]['type'][$i],
            );
            $aPathInfo = pathinfo ($_FILES[$sName]['name'][$i]);
            $sTmpFile = BX_DIRECTORY_PATH_ROOT . 'tmp/v' . time() . getLoggedId() . $i . '.' . $aPathInfo['extension'];
            if (move_uploaded_file($sUploadedFile,  $sTmpFile)) {
                $iRet = BxDolService::call('files', 'perform_file_upload', array($sTmpFile, $aFileInfo), 'Uploader');
                @unlink ($sTmpFile);
                if ($iRet)
                    $aRet[] = $iRet;
            }
        }
        return $aRet;
    }

    /**
     * Insert media to database
     * @param $iEntryId associated entry id
     * @param $aMedia media id's array
     * @param $sMediaType media type, like images, videos, etc
     */
    function insertMedia ($iEntryId, $aMedia, $sMediaType)
    {
        $aMediaValidated = $this->_validateMediaIds ($aMedia);
        $this->_oDb->insertMedia ($iEntryId, $aMediaValidated, $sMediaType);
    }

    /**
     * Update media in database
     * First it delete media ids from database, then adds new
     * Be carefull if you store more information than just a pair of ids
     * @param $iEntryId associated entry id
     * @param $aMedia media id's array
     * @param $sMediaType media type, like images, videos, etc
     */
    function updateMedia ($iEntryId, $aMediaAdd, $aMediaDelete, $sMediaType)
    {
        $aMediaValidated = $this->_validateMediaIds ($aMediaAdd);
        $this->_oDb->updateMedia ($iEntryId, $aMediaValidated, $aMediaDelete, $sMediaType);
    }

    /**
     * Delete media from database
     * @param $iEntryId associated entry id
     * @param $aMedia media id's array
     * @param $sMediaType media type, like images, videos, etc
     */
    function deleteMedia ($iEntryId, $aMedia, $sMediaType)
    {
        $aMediaValidated = $this->_validateMediaIds ($aMedia);
        $this->_oDb->deleteMedia ($iEntryId, $aMediaValidated, $sMediaType);
    }

    /**
     * @access private
     */
    function _validateMediaIds ($aMedia)
    {
        if (is_array ($aMedia)) {
            $aMediaValidated = array ();
            foreach ($aMedia as $iId) {
                $iId = (int)$iId;
                $aMediaValidated[$iId] = $iId;
            }
            return $aMediaValidated;
        } else {
            return (int)$aMedia;
        }
    }

    /**
     * @access private
     */
    function _getFilesInEntry ($sModuleName, $sServiceMethod, $sName, $sMediaType, $iIdProfile, $iEntryId)
    {

        $aReadyMedia = array ();
        if ($iEntryId)
            $aReadyMedia = $this->_oDb->getMediaIds($iEntryId, $sMediaType);

        if (!$aReadyMedia)
            return array();

        $aDataEntry = $this->_oDb->getEntryById($iEntryId);

        $aFiles = array ();
        foreach ($aReadyMedia as $iMediaId) {
            switch ($sModuleName) {
            case 'photos':
                $aRow = BxDolService::call($sModuleName, $sServiceMethod, array($iMediaId, 'icon'), 'Search');
                break;
            case 'sounds':
                $aRow = BxDolService::call($sModuleName, $sServiceMethod, array($iMediaId, 'browse'), 'Search');
                break;
            default:
                $aRow = BxDolService::call($sModuleName, $sServiceMethod, array($iMediaId), 'Search');
            }

            if (!$this->_oMain->isEntryAdmin($aDataEntry, $iIdProfile) && $aRow['owner'] != $iIdProfile)
                continue;

            $aFiles[] = array (
                'name' => $sName,
                'id' => $iMediaId,
                'title' => $aRow['title'],
                'icon' => $aRow['file'],
                'owner' => $aRow['owner'],
                'checked' => 'checked',
            );
        }
        return $aFiles;
    }

    /**
     * process media upload updates
     * call it after successful call $form->insert/update functions
     * @param $iEntryId associated entry id
     * @return nothing
     */
    function processMedia ($iEntryId, $iProfileId)
    {
        $aDataEntry = $this->_oDb->getEntryById($iEntryId);

        foreach ($this->_aMedia as $sName => $a) {

            $aFiles2Delete = array ();
            $aFiles = $this->_getFilesInEntry ($a['module'], $a['service_method'], $a['post'], $sName, (int)$iProfileId, $iEntryId);
            foreach ($aFiles as $aRow)
                $aFiles2Delete[$aRow['id']] = $aRow['id'];

            if (is_array($_REQUEST[$a['post']]) && $_REQUEST[$a['post']] && $_REQUEST[$a['post']][0]) {
                $this->updateMedia ($iEntryId, $_REQUEST[$a['post']], $aFiles2Delete, $sName);
            } else {
                $this->deleteMedia ($iEntryId, $aFiles2Delete, $sName);
            }

            $sUploadFunc = $a['upload_func'];
            if ($aMedia = $this->$sUploadFunc($a['tag'], $a['cat'])) {
                $this->_oDb->insertMedia ($iEntryId, $aMedia, $sName);
                if ($a['thumb'] && !$aDataEntry[$a['thumb']] && !$_REQUEST[$a['thumb']])
                    $this->_oDb->setThumbnail ($iEntryId, 0);
            }

            $aMediaIds = $this->_oDb->getMediaIds($iEntryId, $sName);

            if ($a['thumb']) { // set thumbnail to another one if current thumbnail is deleted
                $sThumbFieldName = $a['thumb'];
                if ($aDataEntry[$sThumbFieldName] && !isset($aMediaIds[$aDataEntry[$sThumbFieldName]])) {
                    $this->_oDb->setThumbnail ($iEntryId, 0);
                }
            }

            // process all deleted media - delete actual file
            $aDeletedMedia = array_diff ($aFiles2Delete, $aMediaIds);
            if ($aDeletedMedia) {
                foreach ($aDeletedMedia as $iMediaId) {
                    if (!$this->_oDb->isMediaInUse($iMediaId, $sName))
                        BxDolService::call($a['module'], 'remove_object', array($iMediaId));
                }
            }
        }

    }

    /**
     * Generate templates for custom media elements
     * @param $iProfileId current profile id
     * @param $iEntryId associated entry id
     * @return array with templates grouped by media typed
     */
    function generateCustomMediaTemplates ($iProfileId, $iEntryId, $iThumb = 0)
    {
        $aTemplates = array ();
        foreach ($this->_aMedia as $sName => $a) {

            $aFiles = $this->_getFilesInEntry ($a['module'], $a['service_method'], $a['post'], $sName, (int)$iProfileId, $iEntryId);

            // files choice / check boxes
            $aVarsChoice = array (
                'bx_if:empty' => array(
                    'condition' => empty($aFiles),
                    'content' => array ()
                ),
                'bx_repeat:files' => $aFiles,
            );
            $aTemplates[$sName]['choice'] = $aFiles ? $this->_oMain->_oTemplate->parseHtmlByName('form_field_files_choice', $aVarsChoice) : '';

            // thumb choice / radio buttons
            if ($a['thumb']) {
                foreach ($aFiles as $k => $r) {
                    $aFiles[$k]['checked'] = ($iThumb == $r['id'] ? 'checked' : '');
                    $aFiles[$k]['name'] = $a['thumb'];
                }
                $aVarsThumbsChoice = array (
                    'bx_if:empty' => array (
                        'condition' => empty($aFiles),
                        'content' => array ()
                    ),
                    'bx_repeat:files' => $aFiles,
                );
                $aTemplates[$sName]['thumb_choice'] = $aFiles ? $this->_oMain->_oTemplate->parseHtmlByName('form_field_thumb_choice', $aVarsThumbsChoice) : '';
            }

            // upload form
            $aVarsUpload = array (
                'file' => $sName,
                'title' => $a['title_upload_post'],
                'file_upload_title' => $a['title_upload'],
                'bx_if:price' => array (
                    'condition' => false, 'content' => array('price' => '', 'file_price_title' => '')
                ),
                'bx_if:privacy' => array (
                    'condition' => false, 'content' => array('select' => '', 'file_permission_title' => '')
                ),
            );
            $aTemplates[$sName]['upload'] = $this->_oMain->_oTemplate->parseHtmlByName('form_field_files_upload', $aVarsUpload);
        }
        return $aTemplates;
    }

    function processMembershipChecksForMediaUploads (&$aInputs)
    {
        $isAdmin = $GLOBALS['logged']['admin'] && isProfileActive($this->_iProfileId);

        defineMembershipActions (array('photos add', 'sounds add', 'videos add', 'files add'));

        if (defined('BX_PHOTOS_ADD'))
            $aCheck = checkAction(getLoggedId(), BX_PHOTOS_ADD);
        if (!defined('BX_PHOTOS_ADD') || ($aCheck[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED && !$isAdmin)) {
            unset($aInputs['thumb']);
        }

        $a = array ('images' => 'PHOTOS', 'videos' => 'VIDEOS', 'sounds' => 'SOUNDS', 'files' => 'FILES');
        foreach ($a as $k => $v) {
            if (defined("BX_{$v}_ADD"))
                $aCheck = checkAction(getLoggedId(), constant("BX_{$v}_ADD"));
            if ((!defined("BX_{$v}_ADD") || $aCheck[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED) && !$isAdmin) {
                unset($this->_aMedia[$k]);
                unset($aInputs['header_'.$k]);
                unset($aInputs[$k.'_choice']);
                unset($aInputs[$k.'_upload']);
            }
        }
    }
}
