<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesRate');

require_once('BxPhotosSearch.php');

class BxPhotosRate extends BxDolFilesRate
{
    function __construct()
    {
        $oMedia = new BxPhotosSearch();
        parent::__construct('bx_photos', $oMedia);
    }

    function getRateFile(&$aData)
    {
        $aImg = $this->oMedia->serviceGetPhotoArray($aData[0]['id'], 'file');
        $iImgWidth = (int)getParam($this->sType . '_file_width');

        $aFile = array(
            'fileBody' => $aImg['file'],
            'infoWidth' => $iImgWidth > 0 ? $iImgWidth + 2: ''
        );

        return $this->oMedia->oTemplate->parseHtmlByName('rate_object_file.html', $aFile);
    }
}
