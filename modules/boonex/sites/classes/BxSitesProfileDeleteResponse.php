<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolAlerts');

class BxSitesProfileDeleteResponse extends BxDolAlertsResponse
{
    function response ($oTag)
    {
        if (!($iProfileId = (int)$oTag->iObject))
            return;

        if (!defined('BX_SITES_ON_PROFILE_DELETE'))
            define ('BX_SITES_ON_PROFILE_DELETE', 1);

        $_GET['r'] = 'sites/delete_profile_sites/' . $iProfileId;
        chdir(BX_DIRECTORY_PATH_MODULES);
        include(BX_DIRECTORY_PATH_MODULES . 'index.php');
    }
}
