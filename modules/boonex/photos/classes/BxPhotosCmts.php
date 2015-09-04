<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesCmts');

class BxPhotosCmts extends BxDolFilesCmts
{
    function BxPhotosCmts($sSystem, $iId, $iInit = 1)
    {
        parent::BxDolFilesCmts($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxPhotosModule');
    }
}
