<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolRequest.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php');

/**
 * Service calls to modules' methods.
 *
 * The class has one static method is needed to make service calls
 * to module's methods from the Dolphin's core or the other modules.
 *
 *
 * Example of usage:
 * BxDolService::call('payment', 'get_add_to_cart_link', array($iVendorId, $mixedModuleId, $iItemId, $iItemCount));
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxDolService
{
    public static function call($mixed, $sMethod, $aParams = array(), $sClass = 'Module')
    {
        $oDb = new BxDolModuleDb();

        if(is_string($mixed))
            $aModule = $oDb->getModuleByUri($mixed);
        else
            $aModule = $oDb->getModuleById($mixed);

        return empty($aModule) ? '' : BxDolRequest::processAsService($aModule, $sMethod, $aParams, $sClass);
    }

    public static function callArray($a)
    {
        if (!isset($a['module']) || !isset($a['method']))
            return false;

        return self::call($a['module'], $a['method'], isset($a['params']) ? $a['params'] : array(), isset($a['class']) ? $a['class'] : 'Module');
    }

}
