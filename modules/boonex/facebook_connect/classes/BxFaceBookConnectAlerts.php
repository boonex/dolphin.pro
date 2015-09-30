<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    class BxFaceBookConnectAlerts extends BxDolAlertsResponse
    {
        var $oModule;

        /**
         * Class constructor;
         */
        function BxFaceBookConnectAlerts()
        {
            $this -> oModule = BxDolModule::getInstance('BxFaceBookConnectModule');
        }

        function response(&$o)
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
                    case 'logout' :
                        break;

                    case 'join' :
                            bx_import('BxDolSession');
                            $oSession = BxDolSession::getInstance();

                            $iFacebookProfileUid = $oSession
                                -> getValue($this -> oModule -> _oConfig -> sFacebookSessionUid);

                            if($iFacebookProfileUid) {
                                $oSession -> unsetValue($this -> oModule -> _oConfig -> sFacebookSessionUid);

                                //save Fb's uid
                                $this -> oModule -> _oDb -> saveFbUid($o -> iObject, $iFacebookProfileUid);

                                //Auto-friend members if they are already friends on Facebook
                                $this -> oModule -> _makeFriends($o -> iObject);
                            }
                        break;

                    case 'delete' :
                        //remove Fb account
                        $this -> oModule -> _oDb -> deleteFbUid($o -> iObject);
                        break;

                    default :
                }
            }
        }
    }
