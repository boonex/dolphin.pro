<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolCache
{
    public function __construct()
    {
    }

    /**
     * Is cache engine available?
     * @return boolean
     */
    function isAvailable()
    {
        return true;
    }

    /**
     * Are required php modules are installed for this cache engine ?
     * @return boolean
     */
    function isInstalled()
    {
        return true;
    }
    function getData($sKey, $iTTL = false) {}
    function setData($sKey, $mixedData, $iTTL = false) {}
    function delData($sKey) {}
    function removeAllByPrefix ($s) {}
    function getSizeByPrefix ($s) {}
}
