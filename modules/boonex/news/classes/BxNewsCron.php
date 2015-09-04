<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextCron');

class BxNewsCron extends BxDolTextCron
{
    function BxNewsCron()
    {
        parent::BxDolTextCron();

        $this->_oModule = BxDolModule::getInstance('BxNewsModule');
    }
}
