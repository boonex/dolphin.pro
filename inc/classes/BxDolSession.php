<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolMistake');
bx_import('BxDolSessionQuery');

define('BX_DOL_SESSION_LIFETIME', 3600);
define('BX_DOL_SESSION_COOKIE', 'memberSession');

class BxDolSession extends BxDolMistake
{
    var $oDb;
    var $sId;
    var $iUserId;
    var $aData;

    private function BxDolSession()
    {
        parent::BxDolMistake();

        $this->oDb = new BxDolSessionQuery();
        $this->sId = '';
        $this->iUserId = 0;
        $this->aData = array();
    }

    function getInstance()
    {
        if(!isset($GLOBALS['bxDolClasses']['BxDolSession']))
            $GLOBALS['bxDolClasses']['BxDolSession'] = new BxDolSession();

        if(!$GLOBALS['bxDolClasses']['BxDolSession']->getId())
            $GLOBALS['bxDolClasses']['BxDolSession']->start();

        return $GLOBALS['bxDolClasses']['BxDolSession'];
    }

    function start()
    {
        if (defined('BX_DOL_CRON_EXECUTE'))
            return true;

        if($this->exists($this->sId))
            return true;

        $this->sId = genRndPwd(32, true);

        $aUrl = parse_url($GLOBALS['site']['url']);
        $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';
        setcookie(BX_DOL_SESSION_COOKIE, $this->sId, 0, $sPath, '', false, true);

        $this->save();
        return true;
    }

    function destroy()
    {
        $aUrl = parse_url($GLOBALS['site']['url']);
        $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';
        setcookie(BX_DOL_SESSION_COOKIE, '', time() - 86400, $sPath, '', false, true);
        unset($_COOKIE[BX_DOL_SESSION_COOKIE]);

        $this->oDb->delete($this->sId);

        $this->sId = '';
        $this->iUserId = 0;
        $this->aData = array();
    }

    function exists($sId = '')
    {
        if(empty($sId) && isset($_COOKIE[BX_DOL_SESSION_COOKIE]))
            $sId = process_db_input($_COOKIE[BX_DOL_SESSION_COOKIE], BX_TAGS_STRIP);

        $mixedSession = array();
        if(($mixedSession = $this->oDb->exists($sId)) !== false) {
            $this->sId = $mixedSession['id'];
            $this->iUserId = (int)$mixedSession['user_id'];
            $this->aData = unserialize($mixedSession['data']);
            return true;
        } else
            return false;
    }

    function getId()
    {
        return $this->sId;
    }

    function setValue($sKey, $mixedValue)
    {
        if(empty($this->sId))
            $this->start();

        $this->aData[$sKey] = $mixedValue;
        $this->save();
    }

    function unsetValue($sKey)
    {
        if(empty($this->sId))
            $this->start();

        unset($this->aData[$sKey]);

        if(!empty($this->aData))
            $this->save();
        else
            $this->destroy();
    }

    function getValue($sKey)
    {
        if(empty($this->sId))
            $this->start();

        return isset($this->aData[$sKey]) ? $this->aData[$sKey] : false;
    }

    private function save()
    {
        if($this->iUserId == 0)
            $this->iUserId = getLoggedId();

        $this->oDb->save($this->sId, array(
            'user_id' => $this->iUserId,
            'data' => serialize($this->aData)
        ));
    }
}
