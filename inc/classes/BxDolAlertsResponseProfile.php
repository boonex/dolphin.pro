<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTags');
bx_import('BxDolAlerts');
bx_import('BxDolProfilesController');

class BxDolAlertsResponseProfile extends BxDolAlertsResponse
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

    function _processProfileBeforeJoin($oAlert) {}

    function _processProfileJoin($oAlert)
    {
        $oPC = new BxDolProfilesController();

        //--- reparse profile tags
        $oTags = new BxDolTags();
        $oTags->reparseObjTags('profile', $oAlert->iObject);

        //--- send new user notification
        if(getParam('newusernotify') == 'on' )
            $oPC->sendNewUserNotify($oAlert->iObject);

        //--- Promotional membership
        if(getParam('enable_promotion_membership') == 'on') {
            $iMemershipDays = getParam('promotion_membership_days');
            setMembership($oAlert->iObject, MEMBERSHIP_ID_PROMOTION, $iMemershipDays, true);
        }
    }

    function _processProfileBeforeLogin($oAlert) {}

    function _processProfileLogin($oAlert) {}

    function _processProfileLogout($oAlert) {}

    function _processProfileEdit ($oAlert)
    {
        //--- reparse profile tags
        $oTags = new BxDolTags();
        $oTags->reparseObjTags('profile', $oAlert->iObject);
    }

    function _processProfileDelete ($oAlert)
    {
    	$oPC = new BxDolProfilesController();
    	if(getParam('unregisterusernotify') == 'on' )
    		$oPC->sendUnregisterUserNotify($oAlert->aExtras['profile_info']);
    }
}
