<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    class BxSimpleMessengerResponse extends BxDolAlertsResponse
    {
        function response($o)
        {
            if ( $o -> sUnit == 'profile' ) {
                switch ( $o -> sAction ) {
                    case 'delete' :
                       $oModule = BxDolModule::getInstance('BxSimpleMessengerModule');
                       $oModule -> _oDb -> deleteAllMessagesHistory($o -> iObject);
                    break;
                }
            }
        }
    }
