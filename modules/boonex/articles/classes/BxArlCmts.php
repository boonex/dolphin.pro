<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextCmts');

class BxArlCmts extends BxDolTextCmts
{
    function BxArlCmts($sSystem, $iId, $iInit = 1)
    {
        parent::BxDolTextCmts($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxArlModule');
    }
}
