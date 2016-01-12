<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolAlertsResponse');

class BxChatWebRTCAlerts extends BxDolAlertsResponse
{
    function BxChatWebRTCAlerts()
    {
        parent::BxDolAlertsResponse();
        //$this -> oModule = BxDolModule::getInstance('BxChatWebRTCModule');
    }
}
