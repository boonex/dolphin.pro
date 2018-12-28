<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxPollResponse extends BxDolAlertsResponse
{
    function response($o)
    {
        if($o->sUnit == 'profile') {
            switch($o->sAction) {
                case 'delete':
                    $oPoll = BxDolModule::getInstance('BxPollModule');

                    $aPolls = $oPoll->_oDb->getAllPolls(null, $o->iObject);
                    foreach($aPolls as $aPoll)
                        $oPoll->deletePoll($aPoll['id_poll']);
                    break;
            }
        }
    }
}
