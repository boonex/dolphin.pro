<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once("header.inc.php");
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolDb.php');

$GLOBALS['MySQL'] = BxDolDb::getInstance();

$site['title']        = getParam('site_title');
$site['email']        = getParam('site_email');
$site['email_notify'] = getParam('site_email_notify');

date_default_timezone_set(getParam('site_timezone'));
$GLOBALS['MySQL']->setTimezone(getParam('site_timezone'));

function db_list_tables($error_checking = true)
{
    $GLOBALS['MySQL']->setErrorChecking($error_checking);

    return $GLOBALS['MySQL']->listTables();
}

/**
 * @param       $query
 * @param array $bindings
 * @param bool  $error_checking
 * @return PDOStatement
 */
function db_res($query, $bindings = [], $error_checking = true)
{
    $GLOBALS['MySQL']->setErrorChecking($error_checking);

    return $GLOBALS['MySQL']->res($query, $bindings);
}

function db_last_id()
{
    return $GLOBALS['MySQL']->lastId();
}

function db_affected_rows()
{
    return $GLOBALS['MySQL']->getAffectedRows();
}

function db_res_assoc_arr($query, $bindings = [], $error_checking = true)
{
    $GLOBALS['MySQL']->setErrorChecking($error_checking);

    return $GLOBALS['MySQL']->getAll($query, $bindings);
}

function db_arr($query, $bindings = [], $error_checking = true)
{
    $GLOBALS['MySQL']->setErrorChecking($error_checking);

    return $GLOBALS['MySQL']->getRow($query, $bindings, PDO::FETCH_BOTH);
}

function db_assoc_arr($query, $bindings = [], $error_checking = true)
{
    $GLOBALS['MySQL']->setErrorChecking($error_checking);

    return $GLOBALS['MySQL']->getRow($query, $bindings);
}

function db_value($query, $bindings = [], $error_checking = true, $index = 0)
{
    $GLOBALS['MySQL']->setErrorChecking($error_checking);

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

function getParam($param_name, $use_cache = true)
{
    return $GLOBALS['MySQL']->getParam($param_name, $use_cache);
}

function getParamDesc($param_name)
{
    return $GLOBALS['MySQL']->getOne("SELECT `desc` FROM `sys_options` WHERE `Name` = '$param_name'");
}

function setParam($param_name, $param_val)
{
    return $GLOBALS['MySQL']->setParam($param_name, $param_val);
}
