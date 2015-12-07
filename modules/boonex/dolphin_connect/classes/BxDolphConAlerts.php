<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectAlerts');

class BxDolphConAlerts extends BxDolConnectAlerts
{
    function BxDolphConAlerts()
    {
        parent::BxDolConnectAlerts();
        $this -> oModule = BxDolModule::getInstance('BxDolphConModule');
    }
}
