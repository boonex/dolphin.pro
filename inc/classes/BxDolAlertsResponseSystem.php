<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolAlerts');

class BxDolAlertsResponseSystem extends BxDolAlertsResponse
{
    function __construct()
    {
        parent::__construct();
    }

    function response($oAlert)
    {
        $sMethodName = '_process' . ucfirst($oAlert->sUnit) . str_replace(' ', '', ucwords(str_replace('_', ' ', $oAlert->sAction)));
        if(method_exists($this, $sMethodName))
            $this->$sMethodName($oAlert);
    }

    function _processSystemBegin($oAlert) {}
}
