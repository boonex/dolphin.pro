<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesRate');

require_once('BxVideosSearch.php');

class BxVideosRate extends BxDolFilesRate
{
    function __construct()
    {
        $oMedia = new BxVideosSearch();
        $oMedia->aCurrent['ownFields'][] = 'Video';
        $oMedia->aCurrent['ownFields'][] = 'Source';

        parent::__construct('bx_videos', $oMedia);
    }

    function getRateFile(&$aData)
    {
        return $this->oMedia->oTemplate->getFileConcept($aData[0]['id'], array('ext'=>$aData[0]['Video'], 'source'=>$aData[0]['Source']));
    }
}
