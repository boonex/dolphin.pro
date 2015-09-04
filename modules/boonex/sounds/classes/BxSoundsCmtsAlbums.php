<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesCmtsAlbums');

class BxSoundsCmtsAlbums extends BxDolFilesCmtsAlbums
{
    function BxSoundsCmtsAlbums($sSystem, $iId, $iInit = 1)
    {
        parent::BxDolFilesCmtsAlbums($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxSoundsModule');
    }
}
