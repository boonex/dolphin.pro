<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define( 'BX_UPGRADE_DB_FULL_VISUAL_PROCESSING', true );
define( 'BX_UPGRADE_DB_FULL_DEBUG_MODE', true );

class BxDolUpgradeDb
{
    protected $host, $port, $socket, $dbname, $user, $password;
    protected $link;
    protected static $instance;
    protected $oCurrentStmt;
    protected $iCurrentFetchStyle;


    function __construct()
    {
        $this->host               = DATABASE_HOST;
        $this->port               = DATABASE_PORT;
        $this->socket             = DATABASE_SOCK;
        $this->dbname             = DATABASE_NAME;
        $this->user               = DATABASE_USER;
        $this->password           = DATABASE_PASS;
        $this->iCurrentFetchStyle = PDO::FETCH_ASSOC;

        if (!$this->link) {
            $this->connect();
        }
    }

    /**
     * connect to database with appointed parameters
     */
    protected function connect()
    {
        $sSocketOrHost = ($this->socket) ? "unix_socket={$this->socket}" : "host={$this->host};port={$this->port}";

        $this->link = new PDO(
            "mysql:{$sSocketOrHost};dbname={$this->dbname};charset=utf8",
            $this->user,
            $this->password,
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""',
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => defined('DATABASE_PERSISTENT') && DATABASE_PERSISTENT ? true : false,
            ]
        );
    }

    /**
     * close pdo connection
     */
    protected function disconnect()
    {
        $this->link = null;
    }

    /**
     * execute sql query and return one row result
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @param int    $iFetchStyle
     * @return array
     */
    public function getRow($sQuery, $aBindings = [], $iFetchStyle = PDO::FETCH_ASSOC)
    {
        if ($iFetchStyle != PDO::FETCH_ASSOC && $iFetchStyle != PDO::FETCH_NUM && $iFetchStyle != PDO::FETCH_BOTH) {
            $iFetchStyle = PDO::FETCH_ASSOC;
        }

        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;

        return $oStmt->fetch($iFetchStyle);
    }
    
    /**
     * execute sql query and return a column as result
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @return array
     */
    public function getColumn($sQuery, $aBindings = [])
    {
        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;
        
        $aResultRows = [];
        while ($row = $oStmt->fetchColumn()) {
            $aResultRows[] = $row;
        }

        return $aResultRows;
    }

    /**
     * execute sql query and return one value result
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @param int    $iIndex
     * @return mixed
     */
    public function getOne($sQuery, $aBindings = [], $iIndex = 0)
    {
        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;
        
        $result = $oStmt->fetch(PDO::FETCH_BOTH);
        if ($result) {
            return $result[$iIndex];
        }

        return null;
    }

    /**
     * execute sql query and return the first row of result
     * and keep $array type and poiter to all data
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @param int    $iFetchStyle
     * @return array
     */
    public function getFirstRow($sQuery, $aBindings = [], $iFetchStyle = PDO::FETCH_ASSOC)
    {
        if ($iFetchStyle != PDO::FETCH_ASSOC && $iFetchStyle != PDO::FETCH_NUM) {
            $this->iCurrentFetchStyle = PDO::FETCH_ASSOC;
        } else {
            $this->iCurrentFetchStyle = $iFetchStyle;
        }

        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;
        
        $this->oCurrentStmt = $oStmt;

        $result = $this->oCurrentStmt->fetch($this->iCurrentFetchStyle);
        if ($result) {
            return $result;
        }

        return [];
    }

    /**
     * return next row of pointed last getFirstRow calling data
     *
     * @return array
     */
    public function getNextRow()
    {
        $aResult = [];

        if (!$this->oCurrentStmt) {
            return $aResult;
        }

        $aResult = $this->oCurrentStmt->fetch($this->iCurrentFetchStyle);

        if ($aResult === false) {
            $this->oCurrentStmt       = null;
            $this->iCurrentFetchStyle = PDO::FETCH_ASSOC;

            return [];
        }

        return $aResult;
    }

    /**
     * @deprecated use getAffectedRows instead
     * return number of affected rows in current mysql result
     *
     * @param null|PDOStatement $oStmt
     * @return int
     */
    public function getNumRows($oStmt = null)
    {
        return $this->getAffectedRows($oStmt);
    }

    /**
     * execute any query return number of rows affected/false
     *
     * @param null|PDOStatement $oStmt
     * @return int
     */
    public function getAffectedRows($oStmt = null)
    {
        if ($oStmt) {
            return $oStmt->rowCount();
        }

        if ($this->oCurrentStmt) {
            return $this->oCurrentStmt->rowCount();
        }

        return 0;
    }

    /**
     * execute any query return number of rows affected
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @return int
     */
    public function query($sQuery, $aBindings = [])
    {
        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;

        return $oStmt->rowCount();
    }

    /**
     * execute any query
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @param bool   $bReplaying
     * @return PDOStatement
     */
    public function res($sQuery, $aBindings = [], $bReplaying = false)
    {
        if (strlen(trim($sQuery)) < 1) {
            throw new InvalidArgumentException('Please provide a valid sql query');
        }

        if ($this->link === null) {
            $this->connect();
        }

        if ($aBindings) {
            $oStmt = $this->link->prepare($sQuery);
            $oStmt->execute($aBindings);
        } else {
            $oStmt = $this->link->query($sQuery);
        }

        return $oStmt;
    }

    /**
     * execute sql query and return table of records as result
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @param int    $iFetchType
     * @return array
     */
    public function getAll($sQuery, $aBindings = [], $iFetchType = PDO::FETCH_ASSOC)
    {
        if ($iFetchType != PDO::FETCH_ASSOC && $iFetchType != PDO::FETCH_NUM && $iFetchType != PDO::FETCH_BOTH) {
            $iFetchType = PDO::FETCH_ASSOC;
        }

        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;
        
        return $oStmt->fetchAll($iFetchType);
    }

    /**
     * @deprecated
     * fetches records from a pdo statement and builds an array
     *
     * @param PDOStatement $oStmt
     * @param int          $iFetchType
     * @return array
     */
    public function fillArray($oStmt, $iFetchType = PDO::FETCH_ASSOC)
    {
        if ($iFetchType != PDO::FETCH_ASSOC && $iFetchType != PDO::FETCH_NUM && $iFetchType != PDO::FETCH_BOTH) {
            $iFetchType = PDO::FETCH_ASSOC;
        }

        $aResult = [];
        while ($row = $oStmt->fetch($iFetchType)) {
            $aResult[] = $row;
        }

        return $aResult;
    }

    /**
     * execute sql query and return table of records as result
     *
     * @param string $sQuery
     * @param string $sFieldKey
     * @param array  $aBindings
     * @param int    $iFetchType
     * @return array
     */
    public function getAllWithKey($sQuery, $sFieldKey, $aBindings = [], $iFetchType = PDO::FETCH_ASSOC)
    {
        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;
        
        $aResult = [];
        if ($oStmt) {
            while ($row = $oStmt->fetch($iFetchType)) {
                $aResult[$row[$sFieldKey]] = $row;
            }

            $oStmt = null;
        }

        return $aResult;
    }

    /**
     * execute sql query and return table of records as result
     *
     * @param string $sQuery
     * @param string $sFieldKey
     * @param string $sFieldValue
     * @param array  $aBindings
     * @return array
     */
    public function getPairs($sQuery, $sFieldKey, $sFieldValue, $aBindings = [])
    {
        $oStmt = $this->res($sQuery, $aBindings);
        if (!$oStmt)
            return false;
        
        $aResult = [];
        if ($oStmt) {
            while ($row = $oStmt->fetch()) {
                $aResult[$row[$sFieldKey]] = $row[$sFieldValue];
            }

            $oStmt = null;
        }

        return $aResult;
    }

    /**
     * Retuns last insert id
     *
     * @return string
     */
    public function lastId()
    {
        return $this->link->lastInsertId();
    }

    /**
     * Returns an array of all the table names in the database
     *
     * @return array
     */
    public function listTables()
    {
        $aResult = [];

        $oStmt = $this->link->query("SHOW TABLES FROM `{$this->dbname}`");
        if (!$oStmt)
            return false;
        
        while ($row = $oStmt->fetch(PDO::FETCH_NUM)) {
            $aResult[] = $row[0];
        }

        return $aResult;
    }

    public function getFields($sTable)
    {
        $oFieldsStmt = $this->link->query("SHOW COLUMNS FROM `{$sTable}`");
        if (!$oFieldsStmt)
            return false;
        
        $aResult = ['original' => [], 'uppercase' => []];
        while ($row = $oFieldsStmt->fetch()) {
            $sName                  = $row['Field'];
            $aResult['original'][]  = $sName;
            $aResult['uppercase'][] = strtoupper($sName);
        }

        return $aResult;
    }

    public function isFieldExists($sTable, $sFieldName)
    {
        $aFields = $this->getFields($sTable);

        return in_array(strtoupper($sFieldName), $aFields['uppercase']);
    }

    public function fetchField($mixedQuery, $iField, $aBindings = [])
    {
        if(is_string($mixedQuery))
            $mixedQuery = $this->res($mixedQuery, $aBindings);

        return $mixedQuery->getColumnMeta($iField);
    }

	public function isIndexExists($sTable, $sIndexName)
	{
		$bIndex = false;

        $aIndexes = $this->getAll("SHOW INDEXES FROM `" . $sTable . "`");
        if (!$aIndexes)
            return null;

        foreach($aIndexes as $aIndex)
			if($aIndex['Key_name'] == $sIndexName) {
				$bIndex = true;
				break;
			}

		return $bIndex;
    }
    
    /**
     * @param string $sText
     * @param bool   $bReal return the actual quotes value or strip the quotes,
     *                      PS: Use the pdo bindings for user's sake
     * @return string
     */
    public function escape($sText, $bReal = false)
    {
        $pdoEscapted = $this->link->quote($sText);

        if ($bReal) {
            return $pdoEscapted;
        }

        // don't need the actual quotes pdo adds, so it
        // behaves kinda like mysql_real_escape_string
        // p.s. things we do for legacy code
        return trim($pdoEscapted, "'");
    }

    function executeSQL($sPath, $aReplace = array (), $isBreakOnError = true)
    {
        if(!file_exists($sPath) || !($rHandler = fopen($sPath, "r")))
            return array ('query' => "fopen($sPath, 'r')", 'error' => 'file not found or permission denied');

        $sQuery = "";
        $sDelimiter = ';';
        $aResult = array();
        while(!feof($rHandler)) {
            $sStr = trim(fgets($rHandler));

            if(empty($sStr) || $sStr[0] == "" || $sStr[0] == "#" || ($sStr[0] == "-" && $sStr[1] == "-"))
                continue;

            //--- Change delimiter ---//
            if(strpos($sStr, "DELIMITER //") !== false || strpos($sStr, "DELIMITER ;") !== false) {
                $sDelimiter = trim(str_replace('DELIMITER', '', $sStr));
                continue;
            }

            $sQuery .= $sStr;

            //--- Check for multiline query ---//
            if(substr($sStr, -strlen($sDelimiter)) != $sDelimiter)
                continue;

            //--- Execute query ---//
            if ($aReplace)
                $sQuery = str_replace($aReplace['from'], $aReplace['to'], $sQuery);
            if($sDelimiter != ';')
                $sQuery = str_replace($sDelimiter, "", $sQuery);
            $rResult = $this->res(trim($sQuery), false);
            if(!$rResult) {
                $aErrInfo = $this->link->errorInfo();
                $aResult[] = array('query' => $sQuery, 'error' => $aErrInfo[2]);
                if ($isBreakOnError)
                    break;
            }

            $sQuery = "";
        }
        fclose($rHandler);

        return empty($aResult) ? true : $aResult;
    }
}
