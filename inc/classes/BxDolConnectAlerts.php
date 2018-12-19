<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolConnectAlerts extends BxDolAlertsResponse
{
    var $oModule;

    function response($oAlert)
    {
        if($o->sUnit == 'system') {
            switch($o->sAction) {
                case 'join_after_payment';
                    $this->oModule->processJoinAfterPayment($o);
                    break;
            }
        }

        if ( $o -> sUnit == 'profile' ) {
            switch ( $o -> sAction ) {
                case 'join':
                        bx_import('BxDolSession');
                        $oSession = BxDolSession::getInstance();

                        $iRemoteProfileId = $oSession -> getValue($this -> oModule -> _oConfig -> sSessionUid);

                        if($iRemoteProfileId) {
                            $oSession -> unsetValue($this -> oModule -> _oConfig -> sSessionUid);

                            // save remote profile id
                            $this -> oModule -> _oDb -> saveRemoteId($o -> iObject, $iRemoteProfileId);
                        }
                    break;

                case 'delete':
                    // remove remote account
                    $this -> oModule -> _oDb -> deleteRemoteAccount($o -> iObject);
                    break;
            }
        }
    }
}
