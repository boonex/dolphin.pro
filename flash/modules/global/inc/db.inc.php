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

    var $sHost;
    var $iPort;
    var $iSocket;
    var $sDb;
    var $sUser;
    var $sPassword;
    var $bConnected;
    var $rLink;

    function BxDbConnect($sHost, $iPort, $iSocket, $sDb, $sUser, $sPassword)
    {
        $this->bPrintLog = true;
        $this->sHost = $sHost;
        $this->iPort = $iPort;
        $this->iSocket = $iSocket;
        $this->sDb = $sDb;
        $this->sUser = $sUser;
        $this->sPassword = $sPassword;

        $this->bConnected = false;
    }

    function connect()
    {
        if($this->bConnected) return;
        $dbHost = strlen($this->iPort) ? $this->sHost . ":" . $this->iPort : $this->sHost;
        $dbHost .= strlen($this->iSocket) ? ":" . $this->iSocket : "";
        @$this->rLink = mysql_connect($dbHost, $this->sUser, $this->sPassword);
        if($this->rLink)$this->bConnected = true;
        else			$this->bConnected = false;
        @mysql_select_db($this->sDb, $this->rLink);
        mysql_query("SET NAMES 'utf8'", $this->rLink);
        mysql_query("SET @@local.wait_timeout=9000;", $this->rLink);
        mysql_query("SET @@local.interactive_timeout=9000;", $this->rLink);
    }

    function disconnect()
    {
        mysql_close($this->rLink);
        $this->bConnected = false;
    }

    function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    function getResult($sQuery)
    {
        if(!$this->bConnected || !($rResult = mysql_query($sQuery, $this->rLink))) {
            echo 'Database access error.';
            if($this->bPrintLog === true) echo " Description: " . mysql_error($this->rLink);
            return false;
        }

        return $rResult;
    }

    function getArray($sQuery)
    {
        if(!$this->bConnected || !($rResult = mysql_query($sQuery, $this->rLink))) {
            echo 'Database access error.';
            if($this->bPrintLog === true) echo " Description: " . mysql_error($this->rLink);
            return false;
        }

        return mysql_fetch_array($rResult);
    }

    function getValue($sQuery)
    {
        if(!$this->bConnected || !($rResult = mysql_query( $sQuery, $this->rLink))) {
            echo 'Database access error.';
            if($this->bPrintLog === true) echo " Description: " . mysql_error($this->rLink);
            return false;
        } else {
            $aResult = mysql_fetch_array($rResult);
            return $aResult[0];
        }
    }

    function getLastInsertId()
    {
        return mysql_insert_id($this->rLink);
    }

	function escape ($s)
    {
        return mysql_real_escape_string($s);
    }

}

global $oDb;
$oDb = new BxDbConnect(DB_HOST, DB_PORT, DB_SOCKET, DB_NAME, DB_USER, DB_PASSWORD);
$oDb->connect();

/**
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
