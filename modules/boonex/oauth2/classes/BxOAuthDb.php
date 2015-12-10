<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

class BxOAuthDb extends BxDolModuleDb
{
    var $_oConfig;

    function BxOAuthDb(&$oConfig)
    {
        parent::BxDolModuleDb();
        $this->_oConfig = $oConfig;
    }

    function getClients()
    {
        return $this->getAll("SELECT * FROM `bx_oauth_clients` ORDER BY `title`");
    }

    function getClientTitle($sClientId)
    {
        return $this->getOne("SELECT `title` FROM `bx_oauth_clients` WHERE `client_id` = '" . process_db_input($sClientId) . "'");
    }

    function getSettingsCategory()
    {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'OAuth2 Server' LIMIT 1");
    }

    function deleteClients($aClients)
    {        
        foreach ($aClients as $sClientId)
            $this->query("DELETE FROM `bx_oauth_clients` WHERE `client_id` = '" . process_db_input($sClientId) . "'");
    }
}
