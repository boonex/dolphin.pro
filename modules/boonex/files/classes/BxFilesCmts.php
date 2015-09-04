<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesCmts');

class BxFilesCmts extends BxDolFilesCmts
{
    function BxFilesCmts($sSystem, $iId, $iInit = 1)
    {
        parent::BxDolFilesCmts($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxFilesModule');
    }
}
