<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_MODULES . 'boonex/payment/classes/BxPmtOrders.php');

class BxPfwOrders extends BxPmtOrders
{
    /*
     * Constructor.
     */
    function __construct($iUserId, &$oDb, &$oConfig, &$oTemplate)
    {
    	parent::__construct($iUserId, $oDb, $oConfig, $oTemplate);
    }
}
