<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolAlerts');

class BxAvaProfileDeleteResponse extends BxDolAlertsResponse
{
    function response ($oTag)
    {
        if (!($iProfileId = (int)$oTag->iObject))
            return;

        bx_import('BxDolService');
        BxDolService::call('avatar', 'delete_profile_avatars', array ($iProfileId));
    }
}
