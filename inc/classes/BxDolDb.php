<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_DOL_TABLE_PROFILES', '`Profiles`');

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolParams.php');

class BxDolDb
{
    protected $host, $port, $socket, $dbname, $user, $password;

    /**
     * @var PDO
     */
    protected $link;

    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var PDOStatement
     */
    protected $oCurrentStmt;

    /**
     * @var int
     */
    protected $iCurrentFetchStyle;

    /**
     * @var BxDolParams
     */
    public $oParams = null;

    /**
     * Cache engine selected for db
     *
     * @var BxDolCacheFile|BxDolCacheAPC|BxDolCacheMemcache|BxDolCacheXCache
     */
    public $oDbCacheObject = null;

    /*
    * set database parameters and connect to it
    * don't want anyone to initate this class
    */
    protected function __construct()
    {
        $this->host               = DATABASE_HOST;
        $this->port               = DATABASE_PORT;
        $this->socket             = DATABASE_SOCK;
        $this->dbname             = DATABASE_NAME;
        $this->user               = DATABASE_USER;
        $this->password           = DATABASE_PASS;
        $this->iCurrentFetchStyle = PDO::FETCH_ASSOC;

        //	connect to db automatically
        if (!$this->link) {
            $this->connect();
        }

        $GLOBALS['gl_db_cache'] = [];
        $GLOBALS['bx_db_param'] = [];

        if (empty($GLOBALS['bx_db_param'])) {
            $GLOBALS['bx_db_param'] = new BxDolParams($this);
        }

        $this->oParams = $GLOBALS['bx_db_param'];
    }

    /**
     * Instance of db class
     *
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
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
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
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
     * Sets mysql time zone for current session
     *
     * @param string $sTimezone
     */
    public function setTimezone($sTimezone)
    {
        $oTimeZone = new DateTimeZone($sTimezone);
        $oDate     = new DateTime('now', $oTimeZone);
        $this->link->query('SET time_zone = "' . $oDate->format('P') . '"');
    }

    public function getLink()
    {
    	return $this->link;
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

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->beginQuery($sQuery);
        }

        try {
            if ($aBindings) {
                $oStmt = $this->link->prepare($sQuery);
                $oStmt->execute($aBindings);
            } else {
                $oStmt = $this->link->query($sQuery);
            }
        } catch (PDOException $e) {
            // check if this is not a replay call already, if it is, than we will skip this block
            // and also check if the error is about mysql server going away/disconnecting
            if (!$bReplaying && (stripos($e->getMessage(), 'gone away') !== false)) {
                // reconnect to db
                $this->disconnect();
                $this->connect();

                // lets retry after reconnecting by replaying
                // the call with the replay arg set to true
                return $this->res($sQuery, $aBindings, true);
            }

            // this was a replay call and it failed again OR
            // the error was not about mysql disconnecting.
            // We will throw the exception and let
            // the system handle it like a boss
            throw $e;
        }

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->endQuery($oStmt);
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

        return $oStmt->fetchAll($iFetchType);
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
     * execute any query return number of rows affected
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @return int
     */
    public function query($sQuery, $aBindings = [])
    {
        return $this->res($sQuery, $aBindings)->rowCount();
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
     * Retuns last insert id
     *
     * @return string
     */
    public function lastId()
    {
        return $this->link->lastInsertId();
    }

    public function getParam($sName, $bCache = true)
    {
        return $this->oParams->get($sName, $bCache);
    }

    public function setParam($sName, $sValue)
    {
        $this->oParams->set($sName, $sValue);

        return true;
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
        while ($row = $oStmt->fetch(PDO::FETCH_NUM)) {
            $aResult[] = $row[0];
        }

        return $aResult;
    }

    public function getFields($sTable)
    {
        $oFieldsStmt = $this->link->query("SHOW COLUMNS FROM `{$sTable}`");

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

    public function getDbCacheObject()
    {
        if ($this->oDbCacheObject != null) {
            return $this->oDbCacheObject;
        } else {
            $sEngine              = getParam('sys_db_cache_engine');
            $this->oDbCacheObject = bx_instance('BxDolCache' . $sEngine);
            if (!$this->oDbCacheObject->isAvailable()) {
                $this->oDbCacheObject = bx_instance('BxDolCacheFile');
            }

            return $this->oDbCacheObject;
        }
    }

    public function genDbCacheKey($sName)
    {
        global $site;

        return 'db_' . $sName . '_' . md5($site['ver'] . $site['build'] . $site['url']) . '.php';
    }

    public function fromCache($sName, $sFunc)
    {
        $aArgs = func_get_args();
        array_shift($aArgs); // shift $sName
        array_shift($aArgs); // shift $sFunc

        if (!getParam('sys_db_cache_enable')) {
            return call_user_func_array(array($this, $sFunc), $aArgs);
        } // pass other function parameters as database function parameters

        $oCache = $this->getDbCacheObject();

        $sKey = $this->genDbCacheKey($sName);

        $mixedRet = $oCache->getData($sKey);

        if ($mixedRet !== null) {

            return $mixedRet;

        } else {

            $mixedRet = call_user_func_array(array($this, $sFunc),
                $aArgs); // pass other function parameters as database function parameters

            $oCache->setData($sKey, $mixedRet);
        }

        return $mixedRet;
    }

    public function cleanCache($sName)
    {
        $oCache = $this->getDbCacheObject();

        $sKey = $this->genDbCacheKey($sName);

        return $oCache->delData($sKey);
    }

    public function & fromMemory($sName, $sFunc)
    {
        if (array_key_exists($sName, $GLOBALS['gl_db_cache'])) {
            return $GLOBALS['gl_db_cache'][$sName];
        }

        $aArgs = func_get_args();
        array_shift($aArgs); // shift $sName
        array_shift($aArgs); // shift $sFunc

        // pass other function parameters as database function parameters
        $GLOBALS['gl_db_cache'][$sName] = call_user_func_array(array($this, $sFunc), $aArgs);

        return $GLOBALS['gl_db_cache'][$sName];

    }

    public function cleanMemory($sName)
    {
        if (isset($GLOBALS['gl_db_cache'][$sName])) {
            unset($GLOBALS['gl_db_cache'][$sName]);

            return true;
        }

        return false;
    }

    /**
     * Convert array of key => values to SQL query.
     * Array keys are field names and array values are field values.
     * @param $a array
     * @param $sDiv fields separator, by default it is ',', another useful value is ' AND '
     * @return part of SQL query string
     */
    public function arrayToSQL($a, $sDiv = ',')
    {
        $s = '';
        foreach($a as $k => $v)
            $s .= "`{$k}` = " . $this->escape($v) . $sDiv;

        return trim($s, $sDiv);
    }

    /**
     * @param string $sText
     * @param bool   $bReal return the actual quotes value or strip the quotes,
     *                      PS: Use the pdo bindings for user's sake
     * @return string
     */
    public function escape($sText, $bReal = true)
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

    /**
     * This function is usefull when you need to form array of parameters to pass to IN(...) SQL construction.
     * Example:
     * @code
     * $a = array(2, 4.5, 'apple', 'car');
     * $s = "SELECT * FROM `t` WHERE `a` IN (" . $oDb->implode_escape($a) . ")";
     * echo $s; // outputs: SELECT * FROM `t` WHERE `a` IN (2, 4.5, 'apple', 'car')
     * @endcode
     *
     * @param $mixed array or parameters or just one paramter
     * @return string which is ready to pass to IN(...) SQL construction
     */
    public function implode_escape ($mixed)
    {
        if (is_array($mixed)) {
            $s = '';
            foreach ($mixed as $v)
                $s .= (is_numeric($v) ? $v : $this->escape($v)) . ',';
            if ($s)
                return substr($s, 0, -1);
            else
                return 'NULL';
        }

        return is_numeric($mixed) ? $mixed : ($mixed ? $this->escape($mixed) : 'NULL');
    }

    /**
     * @deprecated
     * @param $mixed
     * @return array|mixed
     */
    public function unescape($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $k => $v) {
                $mixed[$k] = $this->getOne("SELECT '$v'");
            }

            return $mixed;
        } else {
            return $this->getOne("SELECT '$mixed'");
        }
    }
}
