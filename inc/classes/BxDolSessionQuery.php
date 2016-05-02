<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once (BX_DIRECTORY_PATH_CLASSES . 'BxDolDb.php');

/**
 * @see BxDolSession
 */
class BxDolSessionQuery extends BxDolDb
{
    var $sTable;

    function __construct()
    {
        parent::__construct();

        $this->sTable = 'sys_sessions';
    }
    function getTableName()
    {
        return $this->sTable;
    }
    function exists($sId)
    {
        $aSession = $this->getRow("SELECT `id`, `user_id`, `data` FROM `" . $this->sTable . "` WHERE `id`= ? LIMIT 1", [$sId]);
        return !empty($aSession) ? $aSession : false;
    }
    function save($sId, $aSet)
    {
        $sSetClause = "`id`='" . $sId . "'";
        foreach($aSet as $sKey => $sValue)
            $sSetClause .= ", `" . $sKey . "`='" . $sValue . "'";
        $sSetClause .= ", `date`=UNIX_TIMESTAMP()";

        return (int)$this->query("REPLACE INTO `" . $this->sTable . "` SET " . $sSetClause) > 0;
    }
    function delete($sId)
    {
        return (int)$this->query("DELETE FROM `" . $this->sTable . "` WHERE `id`='" . $sId . "' LIMIT 1") > 0;
    }
    function deleteExpired()
    {
        $iRet = (int)$this->query("DELETE FROM `" . $this->sTable . "` WHERE `date`<(UNIX_TIMESTAMP()-" . BX_DOL_SESSION_LIFETIME . ")");
        $this->query("OPTIMIZE TABLE `" . $this->sTable . "`");
        return $iRet;
    }
}
