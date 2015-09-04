<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextVoting');

class BxArlVoting extends BxDolTextVoting
{
    function BxArlVoting($sSystem, $iId, $iInit = 1)
    {
        parent::BxDolTextVoting($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxArlModule');
    }
}
