<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php');

class BxShoutBoxDb extends BxDolModuleDb
{
    var $_oConfig;

    var $_aObjects = array();

    /**
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct();

        $this->_oConfig  = $oConfig;
        $this->_aObjects = $this->getShoutboxObjects();
    }

    function getShoutboxObjects()
    {
        if (!isset($GLOBALS['bx_dol_shoutbox_objects'])) {
            $GLOBALS['bx_dol_shoutbox_objects'] = $GLOBALS['MySQL']->fromCache('bx_shoutbox_objects', 'getAllWithKey',
                'SELECT * FROM `bx_shoutbox_objects`', 'name');
        }

        return $GLOBALS['bx_dol_shoutbox_objects'];
    }

    function clearShoutboxObjectsCache()
    {
        $this->cleanCache('bx_shoutbox_objects');
    }

    /**
     * Function will create new message
     *
     * @param  : $sObject (string)   - object;
     * @param  : $iHandler (string)  - handler;
     * @param  : $sMessage (string)  - message;
     * @param  : $iOwnerId (integer) - message's owner Id;
     * @param  : $iIP integer
     * @return : void;
     */
    function writeMessage($sObject, $iHandler, $sMessage, $iOwnerId = 0, $iIP = 0)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return false;
        }

        $sMessage = process_db_input($sMessage, 0, BX_SLASHES_AUTO);
        $iOwnerId = (int)$iOwnerId;
        if (!preg_match('/^[0-9]+$/', $iIP)) {
            $iIP = 0;
        }

        $sQuery =
            "
                INSERT INTO
                    `{$this -> _aObjects[$sObject]['table']}`
                SET
                    `HandlerID` = {$iHandler},
                    `OwnerID` = {$iOwnerId},
                    `Message` = '{$sMessage}',
                    `Date` = TIMESTAMP( NOW() ),
                    `IP` = {$iIP}
            ";

        return (int)$this->query($sQuery) > 0 ? $this->lastId() : false;
    }

    /**
     * Function will return last message's Id;
     *
     * @param  : $sObject (string)   - object;
     * @param  : $iHandler (string)  - handler;
     * @return : (integer) ;
     */
    function getLastMessageId($sObject, $iHandler)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return false;
        }

        $sQuery  = "SELECT `ID` FROM `{$this -> _aObjects[$sObject]['table']}` WHERE `HandlerID` = '{$iHandler}' ORDER BY `ID` DESC LIMIT 1";
        $iLastId = $this->getOne($sQuery);

        return ($iLastId) ? $iLastId : 0;
    }

    /**
     * Function will return array with messages;
     *
     * @param  : $sObject (string) - object;
     * @param  : $iHandler (string) - handler;
     * @param  : iLastId (integer) - message's last id;
     * return : array();
     * [OwnerID] - (integer) message owner's Id;
     * [Message] - (string)  message text;
     * [Date]    - (string)  message creation data;
     */
    function getMessages($sObject, $iHandler, $iLastId)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return false;
        }

        $iLastId = (int)$iLastId;
        $sQuery  = "SELECT *, UNIX_TIMESTAMP(`Date`) AS `DateTS` FROM `{$this -> _aObjects[$sObject]['table']}` WHERE `HandlerID` = '{$iHandler}' AND `ID` > " . (int)$iLastId . " ORDER BY `ID`";

        return $this->getAll($sQuery);
    }

    /**
     * Function will get count of all messages;
     *
     * @param  : $sObject (string)   - object;
     * @param  : $iHandler (string)  - handler;
     * @return : (integer) - number of messages;
     */
    function getMessagesCount($sObject, $iHandler)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return false;
        }

        $sQuery = "SELECT COUNT(*) FROM `{$this -> _aObjects[$sObject]['table']}` WHERE `HandlerID` = '{$iHandler}'";

        return $this->getOne($sQuery);
    }

    /**
     * get message info
     *
     * @param $sObject    (string) object;
     * @param $iHandler   (string) handler;
     * @param $iMessageId integer
     * @return array
     */
    function getMessageInfo($sObject, $iHandler, $iMessageId)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return array();
        }

        $iMessageId = (int)$iMessageId;
        $sQuery     = "SELECT * FROM `{$this -> _aObjects[$sObject]['table']}` WHERE `HandlerID` = '{$iHandler}' AND `ID` = {$iMessageId}";
        $aInfo      = $this->getAll($sQuery);

        return $aInfo ? array_shift($aInfo) : array();
    }

    /**
     * Delete messages;
     *
     * @param  : $iLimit (integer) - limit of deleted messages;
     * @return : void;
     */
    function deleteMessages($sObject, $iHandler, $iLimit)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return false;
        }

        $iLimit = (int)$iLimit;
        $sQuery = "DELETE FROM `{$this -> _aObjects[$sObject]['table']}` WHERE `HandlerID` = {$iHandler} ORDER BY `ID` LIMIT {$iLimit}";
        $this->query($sQuery);
    }

    /**
     * Delete message
     *
     * @param $sObject    (string)   - object
     * @param $iHandler   (string)  - handler
     * @param $iMessageId integer
     * @return integer
     */
    function deleteMessage($sObject, $iHandler, $iMessageId)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return false;
        }

        $iMessageId = (int)$iMessageId;
        $sQuery     = "DELETE FROM `{$this -> _aObjects[$sObject]['table']}` WHERE `HandlerID` = {$iHandler} AND `ID` = {$iMessageId}";

        return $this->query($sQuery);
    }

    /**
     * Delete messages by IP
     *
     * @param $sObject  (string)   - object
     * @param $iHandler (string)  - handler
     * @param $iIp      integer
     * @return void
     */
    function deleteMessagesByIp($sObject, $iHandler, $iIp)
    {
        if (!isset($this->_aObjects[$sObject])) {
            return false;
        }

        $iIp = (int)$iIp;

        foreach ($this->_aObjects as $a) {
            $sQuery = "DELETE FROM `{$a['table']}` WHERE `IP` = {$iIp}";
            $this->query($sQuery);
        }
    }

    /**
     * Delete messages by profile id
     *
     * @param $iProfileId integer
     * @return void
     */
    function deleteMessagesByProfile($iProfileId)
    {
        $iProfileId = (int)$iProfileId;
        foreach ($this->_aObjects as $a) {
            $sQuery = "DELETE  FROM `{$a['table']}` WHERE `OwnerID` = {$iProfileId}";
            $this->query($sQuery);
        }
    }

    /**
     * Function will delete all oldest data;
     *
     * @param  : $iLifeTime (integer);
     * @return : void();
     */
    function deleteOldMessages($iLifeTime)
    {
        if (!is_numeric($iLifeTime)) {
            return;
        }

        foreach ($this->_aObjects as $a) {
            $sQuery = "DELETE FROM `{$a['table']}` WHERE FROM_UNIXTIME( UNIX_TIMESTAMP() - {$iLifeTime} ) >= `Date`";
            db_res($sQuery);
        }
    }

    /**
     * Function will return number of global settings category;
     *
     * @return : (integer)
     */
    function getSettingsCategory($sName)
    {
        return $this->getOne("SELECT `kateg` FROM `sys_options` WHERE `Name` = ?", [$sName]);
    }

    function insertData($aData)
    {
        foreach ($aData as $a) {
            $this->query("INSERT INTO `bx_shoutbox_objects` (`name`, `title`, `table`, `code_allow_use`, `code_allow_delete`, `code_allow_block`) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $a['name'],
                    $a['title'],
                    $a['table'],
                    $a['code_allow_use'],
                    $a['code_allow_delete'],
                    $a['code_allow_block']
                ]);
        }
    }

    function deleteData($aData)
    {
        foreach ($aData as $a) {
            $this->query("DELETE FROM `bx_shoutbox_objects` WHERE `name` = ?", [$a['name']]);
        }
    }

}
