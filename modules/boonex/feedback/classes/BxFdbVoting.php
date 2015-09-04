<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextVoting');

class BxFdbVoting extends BxDolTextVoting
{
    function BxFdbVoting($sSystem, $iId, $iInit = 1)
    {
        parent::BxDolTextVoting($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxFdbModule');
    }
}
