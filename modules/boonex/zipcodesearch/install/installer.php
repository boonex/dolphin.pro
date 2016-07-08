<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolInstaller');

class BxZIPInstaller extends BxDolInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }

    function install($aParams)
    {
        $aResult = parent::install($aParams);

        $s = $this->_readFromUrl("http://ws.geonames.org/postalCodeCountryInfo?");
        $a = $this->_getCountriesArray ($s);
        if (count($a)) {
            db_res ("TRUNCATE TABLE `bx_zip_countries_geonames`");
            foreach ($a as $sCountry)
                db_res ("INSERT INTO `bx_zip_countries_geonames` VALUES ('$sCountry')");
        } else {
            return array('code' => BX_DOL_INSTALLER_FAILED, 'content' => 'Network error - can not get list of countries');
        }

        return $aResult;
    }

    function uninstall($aParams)
    {
        return parent::uninstall($aParams);
    }

    function _getCountriesArray (&$s)
    {
        if (!preg_match_all('/<countryCode>(.*)<\/countryCode>/', $s, $m)) {
            return array ();
        }
        return array_unique($m[1]);
    }

    function _readFromUrl ($sUrl)
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $sUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $s = curl_exec($curl);
            curl_close($curl);
            if (true === $s)
                $s = '';
        } else {
            $s = @file_get_contents($sUrl);
        }
        return $s;
    }
}
