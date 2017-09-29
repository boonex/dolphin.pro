<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_DOL_TABLE_PROFILES', '`Profiles`');

define( 'DB_FULL_VISUAL_PROCESSING', true );
define( 'DB_FULL_DEBUG_MODE', false );
define( 'DB_DO_EMAIL_ERROR_REPORT', true );

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolMistake.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolParams.php');

class BxDolDb extends BxDolMistake
{
    var $error_checking = true;
    var $error_message;
    var $host, $port, $socket, $dbname, $user, $password, $link;
    var $current_res, $current_arr_type;

    var $oParams = null;
    var $oDbCacheObject = null;

    /*
    *set database parameters and connect to it
    */
    function BxDolDb()
    {
        parent::BxDolMistake();

        $this->host = DATABASE_HOST;
        $this->port = DATABASE_PORT;
        $this->socket = DATABASE_SOCK;
        $this->dbname = DATABASE_NAME;
        $this->user = DATABASE_USER;
        $this->password = DATABASE_PASS;
        $this->current_arr_type = MYSQL_ASSOC;

        //	connect to db automatically
        if (empty($GLOBALS['bx_db_link'])) {
            $this->connect();
            $GLOBALS['gl_db_cache'] = array();
            $GLOBALS['bx_db_param'] = array();
        } else {
            $this->link = $GLOBALS['bx_db_link'];
        }

        if(empty($GLOBALS['bx_db_param']))
            $GLOBALS['bx_db_param'] = new BxDolParams($this);

        $this->oParams = $GLOBALS['bx_db_param'];
    }

    /**
     * connect to database with appointed parameters
     */
    function connect()
    {
        $full_host = $this->host;
        $full_host .= $this->port ? ':'.$this->port : '';
        $full_host .= $this->socket ? ':'.$this->socket : '';

        $this->link = @mysql_pconnect($full_host, $this->user, $this->password);
        if (!$this->link)
            $this->error('Database connect failed', true);

        if (!$this->select_db())
            $this->error('Database select failed', true);        

        mysql_query("SET NAMES 'utf8'", $this->link);
        mysql_query("SET sql_mode = ''", $this->link);

        $GLOBALS['bx_db_link'] = $this->link;
    }

    function select_db()
    {
        return @mysql_select_db($this->dbname, $this->link) or $this->error('Cannot complete query (select_db)');
    }

    /**
     * close mysql connection
     */
    function close()
    {
        mysql_close($this->link);
    }

    function setTimezone($sTimezone)
    {
        if (!$sTimezone)
            return;
        $oTimeZone = new DateTimeZone($sTimezone);
        $oDate = new DateTime('now', $oTimeZone);
        mysql_query('SET time_zone = "' . $oDate->format('P') . '"', $this->link);
    }

    /**
     * execute sql query and return one row result
     */
    function getRow($query, $arr_type = MYSQL_ASSOC)
    {
        if(!$query)
            return array();
        if($arr_type != MYSQL_ASSOC && $arr_type != MYSQL_NUM && $arr_type != MYSQL_BOTH)
            $arr_type = MYSQL_ASSOC;
        $res = $this->res ($query);
        $arr_res = array();
        if($res && mysql_num_rows($res)) {
            $arr_res = mysql_fetch_array($res, $arr_type);
            mysql_free_result($res);
        }
        return $arr_res;
    }
    /**
     * execute sql query and return a column as result
     */
    function getColumn($sQuery)
    {
        if(!$sQuery)
            return array();

        $rResult = $this->res($sQuery);

        $aResult = array();
        if($rResult) {
            while($aRow = mysql_fetch_array($rResult, MYSQL_NUM))
                $aResult[] = $aRow[0];
            mysql_free_result($rResult);
        }
        return $aResult;
    }

    /**
     * execute sql query and return one value result
     */
    function getOne($query, $index = 0)
    {
        if(!$query)
            return false;
        $res = $this->res ($query);
        $arr_res = array();
        if($res && mysql_num_rows($res))
            $arr_res = mysql_fetch_array($res);
        if(count($arr_res))
            return $arr_res[$index];
        else
            return false;
    }

    /**
     * execute sql query and return the first row of result
     * and keep $array type and poiter to all data
     */
    function getFirstRow($query, $arr_type = MYSQL_ASSOC)
    {
        if(!$query)
            return array();
        if($arr_type != MYSQL_ASSOC && $arr_type != MYSQL_NUM)
            $this->current_arr_type = MYSQL_ASSOC;
        else
            $this->current_arr_type = $arr_type;
        $this->current_res = $this->res ($query);
        $arr_res = array();
        if($this->current_res && mysql_num_rows($this->current_res))
            $arr_res = mysql_fetch_array($this->current_res, $this->current_arr_type);
        return $arr_res;
    }

    /**
     * return next row of pointed last getFirstRow calling data
     */
    function getNextRow()
    {
        $arr_res = mysql_fetch_array($this->current_res, $this->current_arr_type);
        if($arr_res)
            return $arr_res;
        else {
            mysql_free_result($this->current_res);
            $this->current_arr_type = MYSQL_ASSOC;
            return array();
        }
    }

    /**
     * return number of affected rows in current mysql result
     */
    function getNumRows($res = false)
    {
        if ($res)
            return (int)@mysql_num_rows($res);
        elseif (!$this->current_res)
            return (int)@mysql_num_rows($this->current_res);
        else
            return 0;
    }

    /**
     * execute any query return number of rows affected/false
     */
    function getAffectedRows()
    {
        return mysql_affected_rows($this->link);
    }

    /**
     * execute any query return number of rows affected/false
     */
    function query($query)
    {
        $res = $this->res($query);
        if($res)
            return mysql_affected_rows($this->link);
        return false;
    }

    /**
     * execute any query
     */
    function res($query, $error_checking = true)
    {
        if(!$query)
            return false;

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->beginQuery($query);

        $res = @mysql_query($query, $this->link);

        if (false === $res)
            $this->error_message = @mysql_error($this->link); // we need to remeber last error message since mysql_ping will reset it on the next line !
        else
            $this->error_message = '';

        if (false === $res && !@mysql_ping($this->link)) { // if mysql connection is lost - reconnect and try again
            @mysql_close($this->link);
            $this->connect();
            $res = mysql_query($query, $this->link);
        }

        if (isset($GLOBALS['bx_profiler'])) $GLOBALS['bx_profiler']->endQuery($res);

        if (!$res)
            $this->error('Database query error', false, $query);
        return $res;
    }

    /**
     * execute sql query and return table of records as result
     */
    function getAll($query, $arr_type = MYSQL_ASSOC)
    {
        if(!$query)
            return array();

        if($arr_type != MYSQL_ASSOC && $arr_type != MYSQL_NUM && $arr_type != MYSQL_BOTH)
            $arr_type = MYSQL_ASSOC;

        $res = $this->res ($query);
        $arr_res = array();
        if($res) {
            while($row = mysql_fetch_array($res, $arr_type))
                $arr_res[] = $row;
            mysql_free_result($res);
        }
        return $arr_res;
    }

    /**
     * execute sql query and return table of records as result
     */
    function fillArray($res, $arr_type = MYSQL_ASSOC)
    {
        if(!$res)
            return array();

        if($arr_type != MYSQL_ASSOC && $arr_type != MYSQL_NUM && $arr_type != MYSQL_BOTH)
            $arr_type = MYSQL_ASSOC;

        $arr_res = array();
        while($row = mysql_fetch_array($res, $arr_type))
            $arr_res[] = $row;
        mysql_free_result($res);

        return $arr_res;
    }

    /**
     * execute sql query and return table of records as result
     */
    function getAllWithKey($query, $sFieldKey, $iFetchType = MYSQL_ASSOC)
    {
        if(!$query)
            return array();

        $res = $this->res ($query);
        $arr_res = array();
        if($res) {
            while($row = mysql_fetch_array($res, $iFetchType)) {
                $arr_res[$row[$sFieldKey]] = $row;
            }
            mysql_free_result($res);
        }
        return $arr_res;
    }

    /**
     * execute sql query and return table of records as result
     */
    function getPairs($query, $sFieldKey, $sFieldValue, $arr_type = MYSQL_ASSOC)
    {
        if(!$query)
            return array();

        $res = $this->res ($query);
        $arr_res = array();
        if($res) {
            while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $arr_res[$row[$sFieldKey]] = $row[$sFieldValue];
            }
            mysql_free_result($res);
        }
        return $arr_res;
    }

    function lastId()
    {
        return mysql_insert_id($this->link);
    }

    function getErrorMessage ()
    {
        $s = mysql_error($this->link);
        if ($s)
            return $s;
        else
            return $this->error_message;
    }

    function error($text, $isForceErrorChecking = false, $sSqlQuery = '')
    {
        if ($this->error_checking || $isForceErrorChecking)
            $this->genMySQLErr ($text, $sSqlQuery);
        else
            $this->log($text.': ' . $this->getErrorMessage());
    }

    function getParam($sName, $bCache = true)
    {
        return $this->oParams->get($sName, $bCache);
    }

    function setParam($sName, $sValue )
    {
        $this->oParams->set($sName, $sValue);
        return true;
    }

    function listTables()
    {
        return mysql_list_tables($GLOBALS['db']['db'], $this->link);
        //return mysql_list_tables($GLOBALS['db']['db'], $this->link) or $this->error('Database get encoding error');
    }

    function getFields($sTable)
    {
        $rFields = mysql_list_fields($this->dbname, $sTable, $this->link);
        $iFields = mysql_num_fields($rFields);

        $aResult = array('original' => array(), 'uppercase' => array());
        for($i = 0; $i < $iFields; $i++) {
            $sName = mysql_field_name($rFields, $i);
            $aResult['original'][] = $sName;
            $aResult['uppercase'][] = strtoupper($sName);
        }

        return $aResult;
    }

    function isFieldExists($sTable, $sFieldName)
    {
        $aFields = $this->getFields($sTable);
        return in_array(strtoupper($sFieldName), $aFields['uppercase']);
    }

    function fetchField($mixedQuery, $iField)
    {
        if(is_string($mixedQuery))
            $mixedQuery = $this->res($mixedQuery);

        return mysql_fetch_field($mixedQuery, $iField);
    }

    function getEncoding()
    {
        return  mysql_client_encoding($this->link) or $this->error('Database get encoding error');
    }

    function genMySQLErr( $sOutput, $query ='' )
    {
        global $site;

        $sParamsOutput = false;
        $sFoundError = '';

        $aBackTrace = debug_backtrace();
        unset( $aBackTrace[0] );

        if( $query ) {
            //try help to find error

            $aFoundError = array();

            foreach( $aBackTrace as $aCall ) {

                // truncating global settings since it repeated many times and output it separately
                if (isset($aCall['object']) && property_exists($aCall['object'], 'oParams') && property_exists($aCall['object']->oParams, '_aParams')) {
                    if (false === $sParamsOutput)
                        $sParamsOutput = var_export($aCall['object']->oParams->_aParams, true);
                    $aCall['object']->oParams->_aParams = '[truncated]';
                }

                if (isset($aCall['args']) && is_array($aCall['args'])) {
                    foreach( $aCall['args'] as $argNum => $argVal ) {
                        if( is_string($argVal) and strcmp( $argVal, $query ) == 0 ) {
                            $aFoundError['file']     = $aCall['file'];
                            $aFoundError['line']     = $aCall['line'];
                            $aFoundError['function'] = $aCall['function'];
                            $aFoundError['arg']      = $argNum;
                        }
                    }
                }
            }

            if( $aFoundError ) {
                $sFoundError = <<<EOJ
Found error in the file '<b>{$aFoundError['file']}</b>' at line <b>{$aFoundError['line']}</b>.<br />
Called '<b>{$aFoundError['function']}</b>' function with erroneous argument #<b>{$aFoundError['arg']}</b>.<br /><br />
EOJ;
            }
        }

        if( DB_FULL_VISUAL_PROCESSING ) {

            ob_start();

            ?>
                <div style="border:2px solid red;padding:4px;width:600px;margin:0px auto;">
                    <div style="text-align:center;background-color:red;color:white;font-weight:bold;">Error</div>
                    <div style="text-align:center;"><?=$sOutput;?></div>
            <?php
            if( DB_FULL_DEBUG_MODE ) {
                if( strlen( $query ) )
                    echo "<div><b>Query:</b><br />{$query}</div>";

                if ($this->link)
                    echo '<div><b>Mysql error:</b><br />' . $this->getErrorMessage() . '</div>';

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
                    echoDbg( $_REQUEST );
                echo '</div>';
            }
            ?>
                </div>
            <?php

            $sOutput = ob_get_clean();
        }

        if( DB_DO_EMAIL_ERROR_REPORT ) {
            $sMailBody = "Database error in " . $GLOBALS['site']['title'] . "<br /><br /> \n";

            if( strlen( $query ) )
                $sMailBody .= "Query:  <pre>" . htmlspecialchars_adv($query) . "</pre> ";

            if ($this->link)
                $sMailBody .= "Mysql error: " . $this->getErrorMessage() . "<br /><br /> ";

            $sMailBody .= $sFoundError. '<br /> ';

            $sBackTrace = print_r($aBackTrace, true);
            $sBackTrace = str_replace('[password] => ' . DATABASE_PASS, '[password] => *****', $sBackTrace);
            $sBackTrace = str_replace('[user] => ' . DATABASE_USER, '[user] => *****', $sBackTrace);
            $sMailBody .= "Debug backtrace:\n <pre>" . htmlspecialchars_adv($sBackTrace) . "</pre> ";

            if ($sParamsOutput)
                $sMailBody .= "<hr />Settings:\n <pre>" . htmlspecialchars_adv($sParamsOutput) . "</pre> ";

            $sMailBody .= "<hr />Called script: " . $_SERVER['PHP_SELF'] . "<br /> ";

            $sMailBody .= "<hr />Request parameters: <pre>" . print_r( $_REQUEST, true ) . " </pre>";

            $sMailBody .= "--\nAuto-report system\n";

            sendMail( $site['bugReportMail'], "Database error in " . $GLOBALS['site']['title'], $sMailBody, 0, array(), 'html', true );
        }

        bx_show_service_unavailable_error_and_exit($sOutput);
    }

    function setErrorChecking ($b)
    {
        $this->error_checking = $b;
    }

    function getDbCacheObject ()
    {
        if ($this->oDbCacheObject != null) {
            return $this->oDbCacheObject;
        } else {
            $sEngine = getParam('sys_db_cache_engine');
            $this->oDbCacheObject = bx_instance ('BxDolCache'.$sEngine);
            if (!$this->oDbCacheObject->isAvailable())
                $this->oDbCacheObject = bx_instance ('BxDolCacheFile');
            return $this->oDbCacheObject;
        }
    }

    function genDbCacheKey ($sName)
    {
        global $site;
        return 'db_' . $sName . '_' . md5($site['ver'] . $site['build'] . $site['url']) . '.php';
    }

    function fromCache ($sName, $sFunc)
    {
        $aArgs = func_get_args();
        array_shift ($aArgs); // shift $sName
        array_shift ($aArgs); // shift $sFunc

        if (!getParam('sys_db_cache_enable'))
            return call_user_func_array (array ($this, $sFunc), $aArgs); // pass other function parameters as database function parameters

        $oCache = $this->getDbCacheObject ();

        $sKey = $this->genDbCacheKey($sName);

        $mixedRet = $oCache->getData($sKey);

        if ($mixedRet !== null) {

            return $mixedRet;

        } else {

            $mixedRet = call_user_func_array (array ($this, $sFunc), $aArgs); // pass other function parameters as database function parameters

            $oCache->setData($sKey, $mixedRet);
        }

        return $mixedRet;
    }

    function cleanCache ($sName)
    {
        $oCache = $this->getDbCacheObject ();

        $sKey = $this->genDbCacheKey($sName);

        return $oCache->delData($sKey);
    }

    function & fromMemory ($sName, $sFunc) {
        if (array_key_exists($sName, $GLOBALS['gl_db_cache'])) {
            return $GLOBALS['gl_db_cache'][$sName];

        } else {
            $aArgs = func_get_args();
            array_shift ($aArgs); // shift $sName
            array_shift ($aArgs); // shift $sFunc
            $GLOBALS['gl_db_cache'][$sName] = call_user_func_array (array ($this, $sFunc), $aArgs); // pass other function parameters as database function parameters
            return $GLOBALS['gl_db_cache'][$sName];

        }
    }

    function cleanMemory ($sName)
    {
        if (isset($GLOBALS['gl_db_cache'][$sName])) {
            unset($GLOBALS['gl_db_cache'][$sName]);
            return true;
        }
        return false;
    }

    public function arrayToSQL($a, $sDiv = ',')
    {
        $s = '';
        foreach($a as $k => $v)
            $s .= "`{$k}` = '" . $this->escape($v) . "'" . $sDiv;

        return trim($s, $sDiv);
    }

    function escape ($s)
    {
        return mysql_real_escape_string($s);
    }

    function unescape ($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $k => $v)
                $mixed[$k] = $this->getOne("SELECT '$v'");
            return $mixed;
        } else {
            return $this->getOne("SELECT '$mixed'");
        }
    }
}
