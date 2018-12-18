<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesModule');
define('PROFILE_SOUND_CATEGORY', 'Profile sounds');

class BxSoundsModule extends BxDolFilesModule
{
    function __construct (&$aModule)
    {
        parent::__construct($aModule);

        // add more sections for administration
        $this->aSectionsAdmin['processing'] = array('exclude_btns' => 'all');
        $this->aSectionsAdmin['failed'] = array(
            'exclude_btns' => array('activate', 'deactivate', 'featured', 'unfeatured')
        );
    }
    
    function actionGetFile($iFileId)
    {
        $aInfo = $this->_oDb->getFileInfo(array('fileId'=>(int)$iFileId), false, array('medID', 'medProfId', 'medUri', 'albumId', 'Approved'));

        if ($aInfo && $this->isAllowedDownload($aInfo)) {

            $sPathFull = $this->_oConfig->getFilesPath() . $aInfo['medID'] . '.mp3';
            if (file_exists($sPathFull)) {
                $this->isAllowedDownload($aInfo, true);
                header('Connection: close');
                header('Content-Type: audio/mpeg');
                header('Content-Length: ' . filesize($sPathFull));
                header('Last-Modified: ' . gmdate('r', filemtime($sPathFull)));
                header('Content-Disposition: attachment; filename="' . $aInfo['medUri'] . '.mp3";');
                readfile($sPathFull);
                exit;
            } else {
                $this->_oTemplate->displayPageNotFound();
            }

        } elseif (!$aInfo) {
            $this->_oTemplate->displayPageNotFound();
        } else {
            $this->_oTemplate->displayAccessDenied();
        }
    }

    function serviceGetProfileCat ()
    {
        return PROFILE_SOUND_CATEGORY;
    }

    function serviceGetMemberMenuItem ($sIcon = 'music')
    {
        return parent::serviceGetMemberMenuItem ($sIcon);
    }
    function serviceGetMemberMenuItemAddContent ($sIcon = 'music')
    {
        return parent::serviceGetMemberMenuItemAddContent ($sIcon);
    }

    function getEmbedCode ($iFileId)
    {
        return $this->_oTemplate->getEmbedCode($iFileId);
    }

	function isAllowedShare(&$aDataEntry)
    {
    	if($aDataEntry['AllowAlbumView'] != BX_DOL_PG_ALL)
    		return false;

        return true;
    }
    
    function isAllowedDownload (&$aFile, $isPerformAction = false)
    {
        if (getSettingValue('mp3', "save") != TRUE_VAL)
            return false;
        return $this->isAllowedView($aFile, $isPerformAction);
    }

	function serviceGetWallPost($aEvent)
    {
        return $this->getWallPost($aEvent, 'music');
    }

    function serviceGetWallPostOutline($aEvent)
    {
        return $this->getWallPostOutline($aEvent, 'music');
    }
}
