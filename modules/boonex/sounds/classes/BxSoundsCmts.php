<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesCmts');

class BxSoundsCmts extends BxDolFilesCmts
{
    function BxSoundsCmts($sSystem, $iId, $iInit = 1)
    {
        parent::BxDolFilesCmts($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxSoundsModule');
    }
}
