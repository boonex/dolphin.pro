<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once("header.inc.php");
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolDb.php');

/**
 * Please don't get lazy, get your db instance yourself
 *
 * @var BxDolDb
 */
$GLOBALS['MySQL'] = BxDolDb::getInstance();

$site['title']        = getParam('site_title');
$site['email']        = getParam('site_email');
$site['email_notify'] = getParam('site_email_notify');

date_default_timezone_set(getParam('site_timezone'));
$GLOBALS['MySQL']->setTimezone(getParam('site_timezone'));

/**
 * @return array
 */
function db_list_tables()
{
    return $GLOBALS['MySQL']->listTables();
}

/**
 * @param string $query
 * @param array  $bindings
 * @return PDOStatement
 */
function db_res($query, $bindings = [])
{
    return $GLOBALS['MySQL']->res($query, $bindings);
}

/**
 * @return int
 */
function db_last_id()
{
    return $GLOBALS['MySQL']->lastId();
}

/**
 * @param null|PDOStatement $oStmt
 * @return int
 */
function db_affected_rows($oStmt = null)
{
    return $GLOBALS['MySQL']->getAffectedRows($oStmt);
}

/**
 * @param string $query
 * @param array  $bindings
 * @return array
 */
function db_res_assoc_arr($query, $bindings = [])
{
    return $GLOBALS['MySQL']->getAll($query, $bindings);
}

/**
 * @param string $query
 * @param array  $bindings
 * @return mixed
 */
function db_arr($query, $bindings = [])
{
    return $GLOBALS['MySQL']->getRow($query, $bindings, PDO::FETCH_BOTH);
}

/**
 * @param string $query
 * @param array  $bindings
 * @return mixed
 */
function db_assoc_arr($query, $bindings = [])
{
    return $GLOBALS['MySQL']->getRow($query, $bindings);
}

/**
 * @param string $query
 * @param array  $bindings
 * @param bool   $error_checking Only here cuz order of args might break old code
 * @param int    $index
 * @return mixed
 */
function db_value($query, $bindings = [], $error_checking = true, $index = 0)
{
    return $GLOBALS['MySQL']->getOne($query, $bindings, $index);
}

/**
 * @deprecated
 *
 * @param $res
 * @return mixed
 */
function fill_array($res)
{
    return $GLOBALS['MySQL']->fillArray($res, PDO::FETCH_BOTH);
}

/**
 * @deprecated
 *
 * @param $res
 * @return mixed
 */
function fill_assoc_array($res)
{
    return $GLOBALS['MySQL']->fillArray($res, PDO::FETCH_ASSOC);
}

/**
 * @param      $sParamName
 * @param bool $bUseCache
 * @return mixed
 */
function getParam($sParamName, $bUseCache = true)
{
    return $GLOBALS['MySQL']->getParam($sParamName, $bUseCache);
}

/**
 * @param $sParamName
 * @return mixed
 */
function getParamDesc($sParamName)
{
    return $GLOBALS['MySQL']->getOne("SELECT `desc` FROM `sys_options` WHERE `Name` = ?", [$sParamName]);
}

/**
 * @param $sParamName
 * @param $sParamValue
 * @return mixed
 */
function setParam($sParamName, $sParamValue)
{
    return $GLOBALS['MySQL']->setParam($sParamName, $sParamValue);
}
