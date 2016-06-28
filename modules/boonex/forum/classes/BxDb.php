<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */
// common database operations

class BxDb extends Mistake
{
    /**
     * execute sql query and return one row result
     *
     * @param     $query
     * @param     $bindings
     * @param int $arr_type
     * @return array
     */
    function getRow($query, $bindings = [], $arr_type = PDO::FETCH_ASSOC)
    {
        return BxDolDb::getInstance()->getRow($query, $bindings, $arr_type);
    }

    /**
     * execute sql query and return one value result
     *
     * @param $query
     * @param $bindings
     * @return mixed
     */
    function getOne($query, $bindings = [])
    {
        return BxDolDb::getInstance()->getOne($query, $bindings);
    }

    /**
     * @param $query
     * @param $bindings
     * @return array
     */
    function getColumn($query, $bindings = [])
    {
        return BxDolDb::getInstance()->getColumn($query, $bindings);
    }

    /**
     * execute sql query and return the first row of result
     * and keep $array type and poiter to all data
     *
     * @param string $query
     * @param  array $bindings
     * @param int    $arr_type
     * @return array
     */
    function getFirstRow($query, $bindings = [], $arr_type = PDO::FETCH_ASSOC)
    {
        return BxDolDb::getInstance()->getFirstRow($query, $bindings, $arr_type);
    }

    /**
     * return next row of pointed last getFirstRow calling data
     */
    function getNextRow()
    {
        return BxDolDb::getInstance()->getNextRow();
    }

    /**
     * return number of affected rows in current mysql result
     *
     * @param PDOStatement $res
     * @return int
     */
    function getNumRows($res = null)
    {
        return BxDolDb::getInstance()->getAffectedRows($res);
    }

    /**
     * get last insert id
     */
    function getLastId()
    {
        return BxDolDb::getInstance()->lastId();
    }

    /**
     * execute any query return number of rows affected/false
     *
     * @param $query
     * @param $bindings
     * @return int
     */
    function query($query, $bindings = [])
    {
        return BxDolDb::getInstance()->query($query, $bindings);
    }

    /**
     * execute sql query and return table of records as result
     *
     * @param     $query
     * @param     $bindings
     * @param int $arr_type
     * @return array
     */
    function getAll($query, $bindings = [], $arr_type = PDO::FETCH_ASSOC)
    {
        return BxDolDb::getInstance()->getAll($query, $bindings, $arr_type);
    }

    /**
     * @param $s
     * @return string
     */
    function escape($s)
    {
        return BxDolDb::getInstance()->escape($s, false);
    }
}
