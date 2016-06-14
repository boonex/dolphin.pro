<?php
/***************************************************************************
 *
 * IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
 * This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
 * This notice may not be removed from the source code.
 *
 ***************************************************************************/

/**
 * This class is needed to work with database.
 */
class BxDbConnect
{
    var $bPrintLog;

    var $bConnected;

    function __construct()
    {
    }

    function connect()
    {
//        if ($this->bConnected) {
//            return;
//        }
//        $dbHost = strlen($this->iPort) ? $this->sHost . ":" . $this->iPort : $this->sHost;
//        $dbHost .= strlen($this->iSocket) ? ":" . $this->iSocket : "";
//        @$this->rLink = mysql_connect($dbHost, $this->sUser, $this->sPassword);
//        if ($this->rLink) {
//            $this->bConnected = true;
//        } else {
//            $this->bConnected = false;
//        }
//        @mysql_select_db($this->sDb, $this->rLink);
//        mysql_query("SET NAMES 'utf8'", $this->rLink);
//        mysql_query("SET @@local.wait_timeout=9000;", $this->rLink);
//        mysql_query("SET @@local.interactive_timeout=9000;", $this->rLink);
    }

    function disconnect()
    {
        $this->bConnected = false;
    }

    function reconnect()
    {
//        $this->disconnect();
//        $this->connect();
    }

    function getResult($sQuery)
    {
        return BxDolDb::getInstance()->query($sQuery);
    }

    function getArray($sQuery)
    {
        return BxDolDb::getInstance()->getRow($sQuery);
    }

    function getValue($sQuery)
    {
        return BxDolDb::getInstance()->getOne($sQuery);
    }

    function getLastInsertId()
    {
        return BxDolDb::getInstance()->lastId();
    }

    function escape($s)
    {
        return BxDolDb::getInstance()->escape($s, false);
    }

}

global $oDb;
$oDb = new BxDbConnect();
//$oDb->connect();

/*
 * Interface functions are needed to simplify the useing of BxDbConnect class.
 */
function getResult($sQuery)
{
    global $oDb;

    return $oDb->getResult($sQuery);
}

function getArray($sQuery)
{
    global $oDb;

    return $oDb->getArray($sQuery);
}

function getValue($sQuery)
{
    global $oDb;

    return $oDb->getValue($sQuery);
}

function getLastInsertId()
{
    global $oDb;

    return $oDb->getLastInsertId();
}

function getEscapedValue($sValue)
{
    global $oDb;

    return $oDb->escape($sValue);
}
