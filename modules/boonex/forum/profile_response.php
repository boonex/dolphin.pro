<?php

if ($_GET['orca_integration'] && preg_match('/^[0-9a-z]+$/', $_GET['orca_integration'])) {
    define('BX_ORCA_INTEGRATION', $_GET['orca_integration']);
} else {
    define('BX_ORCA_INTEGRATION', 'dolphin');
}

$aPathInfo = pathinfo(__FILE__);
require_once( $aPathInfo['dirname'] . '/inc/header.inc.php' );
if (!class_exists('Thing'))
    require_once( $GLOBALS['gConf']['dir']['classes'] . 'Thing.php' );
if (!class_exists('ThingPage'))
    require_once( $GLOBALS['gConf']['dir']['classes'] . 'ThingPage.php' );
if (!class_exists('Mistake'))
    require_once( $GLOBALS['gConf']['dir']['classes'] . 'Mistake.php' );
if (!class_exists('BxXslTransform'))
    require_once( $GLOBALS['gConf']['dir']['classes'] . 'BxXslTransform.php' );
if (!class_exists('BxDb'))
    require_once( $GLOBALS['gConf']['dir']['classes'] . 'BxDb.php' );
if (!class_exists('DbForum'))
    require_once( $GLOBALS['gConf']['dir']['classes'] . 'DbForum.php' );

class BxForumProfileResponse extends BxDolAlertsResponse
{
    function response($oAlert)
    {
        global $gConf;

        $iProfileId = $oAlert->iObject;

        if (!$iProfileId || $oAlert->sUnit != 'profile' || ('delete' != $oAlert->sAction && 'edit' != $oAlert->sAction))
            return;

        $sUsername = ('delete' == $oAlert->sAction ? $oAlert->aExtras['profile_info']['NickName'] : getUsername($iProfileId));

        if ('edit' == $oAlert->sAction && $oAlert->aExtras['OldProfileInfo']['NickName'] == $sUsername)
            return;

        $oDb = new DbForum ();

        if (isset($oAlert->aExtras['delete_spammer']) && $oAlert->aExtras['delete_spammer']) {
            $oDb->deleteUser($sUsername);
        } else {
            $sOldUsername = ('delete' == $oAlert->sAction ? $sUsername : $oAlert->aExtras['OldProfileInfo']['NickName']);
            $sNewUsername = ('delete' == $oAlert->sAction ? $gConf['anonymous'] : $sUsername);        
            $oDb->renameUser($sOldUsername, $sNewUsername);
        }
    }

}
