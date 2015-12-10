<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectAlerts');

class BxFaceBookConnectAlerts extends BxDolConnectAlerts
{
    function BxFaceBookConnectAlerts()
    {
        parent::BxDolConnectAlerts();
        $this -> oModule = BxDolModule::getInstance('BxFaceBookConnectModule');
    }
}
