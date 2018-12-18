<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesModule');
define('PROFILE_VIDEO_CATEGORY', 'Profile videos');

class BxVideosModule extends BxDolFilesModule
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

            $sPathFull = $this->_oConfig->getFilesPath() . $aInfo['medID'] . '.';
            $aExts = array('flv', 'm4v');
            if (getSettingValue('video', "usex264") == TRUE_VAL)
                rsort($aExts);
            reset($aExts);
            $sExt = '';
            foreach ($aExts as $sPostfix)
            {
                if (file_exists($sPathFull . $sPostfix))
                {
                    $sExt = $sPostfix;
                    $sPathFull .= $sExt;
                    break;
                }
            }
            if (!empty($sExt)) {
                $this->isAllowedDownload($aInfo, true);
                header('Connection: close');
                header('Content-Type: video/x-' . $sExt);
                header('Content-Length: ' . filesize($sPathFull));
                header('Last-Modified: ' . gmdate('r', filemtime($sPathFull)));
                header('Content-Disposition: attachment; filename="' . $aInfo['medUri'] . '.' . $sExt . '";');
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
        return PROFILE_VIDEO_CATEGORY;
    }

    function serviceGetMemberMenuItem ($sIcon = 'film')
    {
        return parent::serviceGetMemberMenuItem ($sIcon);
    }

    function serviceGetMemberMenuItemAddContent ($sIcon = 'film')
    {
        return parent::serviceGetMemberMenuItemAddContent ($sIcon);
    }

    function getWallPost($aEvent, $sIcon = 'save', $aParams = array())
    {
    	return parent::getWallPost($aEvent, $sIcon, array(
    		'templates' => array(
    			'single' => 'timeline_post.html',
    			'grouped' => 'timeline_post_grouped.html'
    		)
    	));
    }

    function getWallPostOutline($aEvent, $sIcon = 'save', $aParams = array())
    {
    	return parent::getWallPostOutline($aEvent, $sIcon, array(
    		'templates' => array(
    			'single' => 'outline_item_image.html',
    			'grouped' => 'outline_item_image_grouped.html'
    		)
    	));
    }

    function getEmbedCode ($iFileId, $aExtra = array())
    {
        return $this->_oTemplate->getEmbedCode($iFileId, $aExtra);
    }

	function isAllowedShare(&$aDataEntry)
    {
    	if($aDataEntry['AllowAlbumView'] != BX_DOL_PG_ALL)
    		return false;

        return true;
    }
    
    function isAllowedDownload (&$aFile, $isPerformAction = false)
    {
        if (getSettingValue('video', "save") != TRUE_VAL)
            return false;
        return $this->isAllowedView($aFile, $isPerformAction);
    }

    function serviceGetWallPost($aEvent)
    {
        return $this->getWallPost($aEvent, 'film');
    }

    function serviceGetWallPostOutline($aEvent)
    {
        return $this->getWallPostOutline($aEvent, 'film');
    }
}
