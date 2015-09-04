<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesRate');

require_once('BxSoundsSearch.php');

class BxSoundsRate extends BxDolFilesRate
{
    function BxSoundsRate()
    {
        $oMedia = new BxSoundsSearch();
        parent::BxDolFilesRate('bx_sounds', $oMedia);
    }

    function getRateFile(&$aData)
    {
        return $this->oMedia->oTemplate->getFileConcept($aData[0]['id']);
    }
}
