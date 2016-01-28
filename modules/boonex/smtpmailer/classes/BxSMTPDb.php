<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

class BxSMTPDb extends BxDolModuleDb
{
    var $_oConfig;

    function __construct(&$oConfig)
    {
        parent::__construct();
        $this->_oConfig = $oConfig;
    }

    function getSettingsCategory()
    {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'SMTP Mailer' LIMIT 1");
    }
}
