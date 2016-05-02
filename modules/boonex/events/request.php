<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');

check_logged();

bx_import('BxDolRequest');

class BxEventsRequest extends BxDolRequest
{
    function __construct()
    {
        parent::__construct();
    }

    public static function processAsAction($aModule, &$aRequest, $sClass = "Module")
    {
        $sClassRequire = $aModule['class_prefix'] . $sClass;
        $oModule = BxDolRequest::_require($aModule, $sClassRequire);
        $aVars = array ('BaseUri' => $oModule->_oConfig->getBaseUri());
        $GLOBALS['oTopMenu']->setCustomSubActions($aVars, 'bx_events_title', false);

        return BxDolRequest::processAsAction($aModule, $aRequest, $sClass);
    }
}

BxEventsRequest::processAsAction($GLOBALS['aModule'], $GLOBALS['aRequest']);
