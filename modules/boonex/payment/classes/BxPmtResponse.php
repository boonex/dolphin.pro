<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');

class BxPmtResponse extends BxDolAlertsResponse
{
    var $_oModule;

    /**
     * Constructor
     * @param BxWallModule $oModule - an instance of current module
     */
    function __construct($oModule)
    {
        parent::__construct();

        $this->_oModule = $oModule;
    }
    /**
     * Overwtire the method of parent class.
     *
     * @param BxDolAlerts $oAlert an instance of alert.
     */
    function response($oAlert)
    {

    }
}
