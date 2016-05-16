<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_DOL_TABLE_PROFILES', '`Profiles`');

define('DB_FULL_VISUAL_PROCESSING', true);
define('DB_FULL_DEBUG_MODE', true);
define('DB_DO_EMAIL_ERROR_REPORT', true);

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolParams.php');
require_once(BX_DIRECTORY_PATH_INC . 'traits/Logger.php');

class BxDolDb
{
    use Logger;

    protected $bErrorChecking = true;
    protected $error_message;
    protected $host, $port, $socket, $dbname, $user, $password;

    /**
     * @var PDO
     */
    protected $link;

    /**
     * @var $this
     */
    protected static $instance;

    /**
     * @var PDOStatement
     */
    protected $oCurrentStmt;

    protected $iCurrentFetchStyle;

    var $oParams = null;
    var $oDbCacheObject = null;

    /*
    * set database parameters and connect to it
    * don't want anyone to initate this class
    */
    public function __construct()
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

        @set_exception_handler(array($this, 'queryExceptionHandler'));
    }

    /**
     * connect to database with appointed parameters
     */
    protected function connect()
    {
    	try {
	        $sSocketOrHost = ($this->socket) ? "unix_socket={$this->socket}" : "host={$this->host};port={$this->port}";

	        $this->link = new PDO(
	            "mysql:{$sSocketOrHost};dbname={$this->dbname};charset=utf8",
	            $this->user,
	            $this->password,
	            [
	                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""',
	                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	                PDO::ATTR_EMULATE_PREPARES   => false
	            ]
	        );
    	}
    	catch (PDOException $e) {
    		$this->error_message = $e->getMessage();
    		$this->error('Database connect failed');
    		return;
    	}
    }

    /**
     * close pdo connection
     */
    protected function disconnect()
    {
        $this->link = null;
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

    /**
     * execute any query
     *
     * @param string $sQuery
     * @param array  $aBindings
     * @return PDOStatement
     */
    public function res($sQuery, $aBindings = [])
    {
        if (strlen(trim($sQuery)) < 1) {
            throw new InvalidArgumentException('Please provide a valid sql query');
        }

        if ($this->link == null) {
            $this->connect();
        }

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->beginQuery($sQuery);
        }

        if ($aBindings) {
            $oStmt = $this->link->prepare($sQuery);
            $oStmt->execute($aBindings);
        } else {
            $oStmt = $this->link->query($sQuery);
        }

        if (isset($GLOBALS['bx_profiler'])) {
            $GLOBALS['bx_profiler']->endQuery($oStmt);
        }

        if (!$oStmt) {
            $this->error('Database query error', false, $sQuery);
        }

        return $oStmt;
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
     * @param int    $index
     * @return mixed
     */
    public function getOne($sQuery, $aBindings = [], $index = 0)
    {
        $oStmt = $this->res($sQuery, $aBindings);

        $result = $oStmt->fetch(PDO::FETCH_BOTH);
        if ($result) {
            return $result[$index];
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
     * return number of affected rows in current mysql result
     *
     * @param null|PDOStatement $oStmt
     * @return int
     */
    public function getNumRows($oStmt = null)
    {
        if ($oStmt) {
            return $oStmt->rowCount();
        }

        if (!$this->oCurrentStmt) {
            return $this->oCurrentStmt->rowCount();
        }

        return 0;
    }

    /**
     * execute any query return number of rows affected/false
     *
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->oCurrentStmt->rowCount();
    }

    /**
     * execute any query return number of rows affected/false
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
     * @param     $sQuery
     * @param     $sFieldKey
     * @param     $sFieldValue
     * @return array
     */
    public function getPairs($sQuery, $sFieldKey, $sFieldValue)
    {
        $oStmt = $this->res($sQuery);

        $aResult = [];
        if ($oStmt) {
            while ($row = $oStmt->fetch()) {
                $aResult[$row[$sFieldKey]] = $row[$sFieldValue];
            }

            $oStmt = null;
        }

        return $aResult;
    }

    public function lastId()
    {
        return $this->link->lastInsertId();
    }

    public function queryExceptionHandler($oException)
    {
		$this->error_message = $oException->getMessage();
    	$this->error('Database query error');
    	return;
    }

    public function getErrorMessage()
    {
    	if(!empty($this->error_message))
			return $this->error_message;

		$aError = $this->link->errorInfo();
        if(!empty($aError[2]))
            return $aError[2];

        return 'Database error';
    }

    public function error($text, $isForceErrorChecking = false, $sSqlQuery = '')
    {
        if ($this->bErrorChecking || $isForceErrorChecking) {
            $this->genMySQLErr($text, $sSqlQuery);
        } else {
            $this->log($text . ': ' . $this->getErrorMessage());
        }
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

    public function genMySQLErr($sOutput, $query = '')
    {
        global $site;

        $sParamsOutput = false;
        $sFoundError   = '';

        $aBackTrace = debug_backtrace();
        unset($aBackTrace[0]);

        if ($query) {
            //try help to find error

            $aFoundError = [];

            foreach ($aBackTrace as $aCall) {

                // truncating global settings since it repeated many times and output it separately
                if (isset($aCall['object']) && property_exists($aCall['object'],
                        'oParams') && property_exists($aCall['object']->oParams, '_aParams')
                ) {
                    if (false === $sParamsOutput) {
                        $sParamsOutput = var_export($aCall['object']->oParams->_aParams, true);
                    }
                    $aCall['object']->oParams->_aParams = '[truncated]';
                }

                if (isset($aCall['args']) && is_array($aCall['args'])) {
                    foreach ($aCall['args'] as $argNum => $argVal) {
                        if (is_string($argVal) and strcmp($argVal, $query) == 0) {
                            $aFoundError['file']     = $aCall['file'];
                            $aFoundError['line']     = $aCall['line'];
                            $aFoundError['function'] = $aCall['function'];
                            $aFoundError['arg']      = $argNum;
                        }
                    }
                }
            }

            if ($aFoundError) {
                $sFoundError = <<<EOJ
Found error in the file '<b>{$aFoundError['file']}</b>' at line <b>{$aFoundError['line']}</b>.<br />
Called '<b>{$aFoundError['function']}</b>' function with erroneous argument #<b>{$aFoundError['arg']}</b>.<br /><br />
EOJ;
            }
        }

        if (DB_FULL_VISUAL_PROCESSING) {

            ob_start();

            ?>
            <div style="border:2px solid red;padding:4px;width:600px;margin:0px auto;">
                <div style="text-align:center;background-color:red;color:white;font-weight:bold;">Error</div>
                <div style="text-align:center;"><?= $sOutput; ?></div>
                <?php
                if (DB_FULL_DEBUG_MODE) {
                    if (strlen($query)) {
                        echo "<div><b>Query:</b><br />{$query}</div>";
                    }

                    if ($this->link) {
                        echo '<div><b>Mysql error:</b><br />' . $this->getErrorMessage() . '</div>';
                    }

                    echo '<div style="overflow:scroll;height:300px;border:1px solid gray;">';
                    echo $sFoundError;
                    echo "<b>Debug backtrace:</b><br />";

                    $sBackTrace = print_r($aBackTrace, true);
                    $sBackTrace = str_replace('[password] => ' . DATABASE_PASS, '[password] => *****', $sBackTrace);
                    $sBackTrace = str_replace('[user] => ' . DATABASE_USER, '[user] => *****', $sBackTrace);

                    echo '<pre>' . $sBackTrace . '</pre>';

                    if ($sParamsOutput) {
                        echo '<hr />';
                        echo "<b>Settings:</b><br />";
                        echo '<pre>' . htmlspecialchars_adv($sParamsOutput) . '</pre>';
                    }

                    echo "<b>Called script:</b> " . $_SERVER['PHP_SELF'] . "<br />";
                    echo "<b>Request parameters:</b><br />";
                    echoDbg($_REQUEST);
                    echo '</div>';
                }
                ?>
            </div>
            <?php

            $sOutput = ob_get_clean();
        }

        if (DB_DO_EMAIL_ERROR_REPORT) {
            $sMailBody = "Database error in " . $GLOBALS['site']['title'] . "<br /><br /> \n";

            if (strlen($query)) {
                $sMailBody .= "Query:  <pre>" . htmlspecialchars_adv($query) . "</pre> ";
            }

            if ($this->link) {
                $sMailBody .= "Mysql error: " . $this->getErrorMessage() . "<br /><br /> ";
            }

            $sMailBody .= $sFoundError . '<br /> ';

            $sBackTrace = print_r($aBackTrace, true);
            $sBackTrace = str_replace('[password] => ' . DATABASE_PASS, '[password] => *****', $sBackTrace);
            $sBackTrace = str_replace('[user] => ' . DATABASE_USER, '[user] => *****', $sBackTrace);
            $sMailBody .= "Debug backtrace:\n <pre>" . htmlspecialchars_adv($sBackTrace) . "</pre> ";

            if ($sParamsOutput) {
                $sMailBody .= "<hr />Settings:\n <pre>" . htmlspecialchars_adv($sParamsOutput) . "</pre> ";
            }

            $sMailBody .= "<hr />Called script: " . $_SERVER['PHP_SELF'] . "<br /> ";

            $sMailBody .= "<hr />Request parameters: <pre>" . print_r($_REQUEST, true) . " </pre>";

            $sMailBody .= "--\nAuto-report system\n";

            sendMail(
                $site['bugReportMail'], "Database error in " . $GLOBALS['site']['title'],
                $sMailBody,
                0,
                [],
                'html',
                true
            );
        }

        bx_show_service_unavailable_error_and_exit($sOutput);
    }

    public function setErrorChecking($b)
    {
        $this->bErrorChecking = $b;
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

        } else {
            $aArgs = func_get_args();
            array_shift($aArgs); // shift $sName
            array_shift($aArgs); // shift $sFunc
            $GLOBALS['gl_db_cache'][$sName] = call_user_func_array(array($this, $sFunc),
                $aArgs); // pass other function parameters as database function parameters
            return $GLOBALS['gl_db_cache'][$sName];

        }
    }

    public function cleanMemory($sName)
    {
        if (isset($GLOBALS['gl_db_cache'][$sName])) {
            unset($GLOBALS['gl_db_cache'][$sName]);

            return true;
        }

        return false;
    }

    public function escape($s)
    {
    	try {
        	$s = $this->link->quote($s);
        }
    	catch (PDOException $e) {
    		$this->error('Escape string error');
    		return false;
    	}

        return $s;
    }

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
