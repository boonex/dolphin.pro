<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

class BxMsgDb extends BxDolModuleDb
{
    var $_oConfig;
    /*
     * Constructor.
     */
    function BxMsgDb(&$oConfig)
    {
        parent::BxDolModuleDb();

        $this->_oConfig = $oConfig;
    }
    function getMembershipActions()
    {
        $sSql = "SELECT `ID` AS `id`, `Name` AS `name` FROM `sys_acl_actions` WHERE `Name`='use messenger'";
        return $this->getAll($sSql);
    }
}
