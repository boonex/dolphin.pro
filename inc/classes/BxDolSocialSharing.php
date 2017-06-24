<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSocialSharingQuery');

/**
 * Social sharing buttons for any content
 *
 * It displays sharing buttons from popular social networks, like facebook, twitter, gogole plus, etc.
 *
 *
 * Example of usage:
 *
 * bx_import('BxTemplSocialSharing');
 * echo BxTemplSocialSharing::getInstance()->getCode($sUrl, $sTitle);
 *
 *
 * Alerts:

 * Type/unit: system
 * Action: social_sharing_display
 * Options:
 *      buttons - reference to buttons array
 *      markers - reference to variables for replacement
 *      override_output - override output string
 *
 */
class BxDolSocialSharing
{
    var $_aSocialButtons = array (); // active social buttons array

    /**
     * Constructor
     */
    protected function __construct()
    {
        $oQuery = new BxDolSocialSharingQuery();
        $this->_aSocialButtons = $oQuery->getActiveButtons();
    }

    /**
     * Get object instance
     * @param $sObject object name
     * @return object instance
     */
    static public function getInstance()
    {
        if (isset($GLOBALS['bxDolClasses']['BxDolSocialSharing']))
            return $GLOBALS['bxDolClasses']['BxDolSocialSharing'];

        bx_import('BxTemplSocialSharing');
        $o = new BxTemplSocialSharing();

        return ($GLOBALS['bxDolClasses']['BxDolSocialSharing'] = $o);
    }

    public function getCode ($sUrl, $sTitle, $aCustomVars = false)
    {
        // overrided in template class
    }

    /**
     * Replace provided markers in string.
     * @param $s - string to replace markers in
     * @param $a - markers array
     * @return string with replaces markers
     */
    protected function _replaceMarkers ($s, $a)
    {
        if (empty($s) || empty($a) || !is_array($a))
            return $s;

        foreach ($a as $sKey => $sValue)
            $s = str_replace('{' . $sKey . '}', $sValue, $s);

        return $s;
    }

    /**
     * Get most facebook locale for provided language code.
     * @param $sLang lang code
     * @return locale string or empty string if no lacale is found
     */
    protected function _getLocaleFacebook ($sLocale)
    {
        $aLocales = $this->_getLocalesFacebook();
        if (!isset($aLocales[$sLocale]))
            return '';
        return $sLocale;
    }

    /**
     * Get facebook locales
     * @return locales array, lang is array key and locale is array value
     */
    protected function _getLocalesFacebook ()
    {
        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        $sCacheKey = $GLOBALS['MySQL']->genDbCacheKey('sys_social_sharing_locales_fb');
        $aData = $oCache->getData($sCacheKey);
        if (null === $aData) {
            $sXML = bx_file_get_contents (BX_DOL_URL_ROOT . 'plugins/facebook-php-sdk/FacebookLocales.xml');
            if (!$sXML)
                return false;
            $xmlLocates = new SimpleXMLElement($sXML);
            $aData = array ();
            foreach ($xmlLocates->locale as $xmlLocale) {
                $sLocale = (string)($xmlLocale->codes->code->standard->representation);
                $aData[$sLocale] = 1;
            }
            $oCache->setData ($sCacheKey, $aData);
        }
        return $aData;
    }
}
