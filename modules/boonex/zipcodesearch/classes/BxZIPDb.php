<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

class BxZIPDb extends BxDolModuleDb
{
    var $_oConfig;

    function __construct(&$oConfig)
    {
        parent::__construct();
        $this->_oConfig = $oConfig;
    }

    function getCountriesGeonames ()
    {
        $a = $this->getPairs("SELECT `t1`.`ISO2`, `t1`.`Country` FROM `sys_countries` AS `t1` INNER JOIN `bx_zip_countries_geonames` AS `t2` ON `t1`.`ISO2` = `t2`.`ISO2`", 'ISO2', 'Country');
        $this->_countriesSortAndTranslate($a);
        return $a;
    }

    function getCountriesGoogle ()
    {
        $a = $this->getPairs("SELECT `t1`.`ISO2`, `t1`.`Country` FROM `sys_countries` AS `t1` INNER JOIN `bx_zip_countries_google` AS `t2` ON `t1`.`ISO2` = `t2`.`ISO2`", 'ISO2', 'Country');
        $this->_countriesSortAndTranslate($a);
        return $a;
    }

    function _countriesSortAndTranslate (&$a)
    {
        foreach ($a as $k => $v)
            $a[$k] = _t('__'.$v);
        asort ($a);
    }

    function getSettingsCategory()
    {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'ZIP Code Search' LIMIT 1");
    }
}
