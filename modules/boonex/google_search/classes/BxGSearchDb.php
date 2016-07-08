<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

/*
 * Map module Data
 */
class BxGSearchDb extends BxDolModuleDb
{
    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct();
        $this->_sPrefix = $oConfig->getDbPrefix();
    }

    function getSettingsCategory()
    {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Google Search' LIMIT 1");
    }
}
