<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolMistake.php');

class BxDolCache extends BxDolMistake
{
    /**
     * constructor
     */
    function BxDolCache()
    {
        parent::BxDolMistake();
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
