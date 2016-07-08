<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectAlerts');

class BxFaceBookConnectAlerts extends BxDolConnectAlerts
{
    function __construct()
    {
        parent::__construct();
        $this -> oModule = BxDolModule::getInstance('BxFaceBookConnectModule');
    }
}
