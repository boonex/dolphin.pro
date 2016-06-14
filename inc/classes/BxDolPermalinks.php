<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

/**
 * Permalinks for any content.
 *
 * An object of the class allows to check whether permalink is enabled
 * and get it for specified standard URI.
 *
 *
 * Example of usage:
 * 1. Register permalink in database by adding necessary info in the `sys_permalinks` table.
 * 2. Create an object and process the URI
 * $oPermalinks = new BxDolPermalinks();
 * $oPermalinks->permalink('modules/?r=news/');
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxDolPermalinks
{
    public $sCacheFile;
    public $aLinks;
    protected $oDb;

    function __construct()
    {
        $this->oDb    = BxDolDb::getInstance();
        $oCache       = $this->oDb->getDbCacheObject();
        $this->aLinks = $oCache->getData($this->oDb->genDbCacheKey('sys_permalinks'));
        if (null === $this->aLinks) {
            if (!$this->cache()) {
                $this->aLinks = array();
            }
        }
    }

    function getInstance()
    {
        if (!isset($GLOBALS['bxDolClasses']['BxDolPermalinks'])) {
            $GLOBALS['bxDolClasses']['BxDolPermalinks'] = new BxDolPermalinks();
        }

        return $GLOBALS['bxDolClasses']['BxDolPermalinks'];
    }

    function cache()
    {
        $aLinks = $this->oDb->getAll("SELECT * FROM `sys_permalinks`");

        $aResult = array();
        foreach ($aLinks as $aLink) {
            $aResult[$aLink['standard']] = array(
                'permalink' => $aLink['permalink'],
                'check'     => $aLink['check'],
                'enabled'   => $this->oDb->getParam($aLink['check']) == 'on'
            );
        }

        $oCache = $this->oDb->getDbCacheObject();
        if ($oCache->setData($this->oDb->genDbCacheKey('sys_permalinks'), $aResult)) {
            $this->aLinks = $aResult;

            return true;
        }

        return false;
    }

    function permalink($sLink)
    {
        if (strpos($sLink, 'modules/?r=') === false && strpos($sLink, 'modules/index.php?r=') === false) {
            // check for exact match
            if ($this->_isEnabled($sLink)) {
                return $this->aLinks[$sLink]['permalink'];
            }

            // check permalinks with numeric id or parameter at the end
            $aMatch = array();
            preg_match('/([\d]+)$/', $sLink, $aMatch);
            if (!isset($aMatch[1])) {
                preg_match('/(\{[a-zA-Z0-9_]+\})$/', $sLink, $aMatch);
            }
            if (!isset($aMatch[1])) {
                return $sLink;
            } // no id at the end and no exact match, return unmodified link

            // process links with id or parameter at the end
            $sLink = substr($sLink, 0, -strlen($aMatch[1]));

            return $this->_isEnabled($sLink) ? $this->aLinks[$sLink]['permalink'] . $aMatch[1] : $sLink . $aMatch[1];
        }

        // modules permalinks

        $aMatch = array();
        preg_match('/^.*(modules\/(index.php)?\?r=[A-Za-z0-9_-]+\/).*$/', $sLink, $aMatch);

        if (!isset($aMatch[1])) {
            return $this->_isEnabled($sLink) ? $this->aLinks[$sLink]['permalink'] : $sLink;
        }

        $sBase = $aMatch[1];
        if ($this->_isEnabled($sBase)) {
            return str_replace($sBase, $this->aLinks[$sBase]['permalink'], $sLink);
        }

        $sBaseShort = str_replace('index.php', '', $sBase);

        return $this->_isEnabled($sBaseShort) ? str_replace($sBase, $this->aLinks[$sBaseShort]['permalink'],
            $sLink) : $sLink;
    }

    function _isEnabled($sLink)
    {
        return array_key_exists($sLink, $this->aLinks) && $this->aLinks[$sLink]['enabled'];
    }

    /**
     * redirect to the correct url after switching skin ot language
     * only correct modules urls are supported
     */
    function redirectIfNecessary($aSkip = array())
    {
        $sCurrentUrl = $_SERVER['PHP_SELF'] . '?' . bx_encode_url_params($_GET, $aSkip);

        if (!preg_match('/modules\/index.php\?r=(\w+)(.*)/', $sCurrentUrl, $m)) {
            return false;
        }

        $sStandardLink = 'modules/?r=' . $m[1] . '/';
        $sPermalink    = $this->permalink($sStandardLink);

        if (false !== strpos($sCurrentUrl, $sPermalink)) {
            return false;
        }

        header("HTTP/1.1 301 Moved Permanently");
        header('Location:' . BX_DOL_URL_ROOT . $sPermalink . rtrim(trim(urldecode($m[2]), '/'), '&'));
        send_headers_page_changed();

        return true;
    }
}
