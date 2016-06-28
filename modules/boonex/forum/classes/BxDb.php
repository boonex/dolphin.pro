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
     */
    function getRow($query, $arr_type = PDO::FETCH_ASSOC)
    {
        return BxDolDb::getInstance()->getRow($query, [], $arr_type);
    }

    /**
     * execute sql query and return one value result
     */
    function getOne($query)
    {
        return BxDolDb::getInstance()->getOne($query);
    }

    function getColumn($query)
    {
        return BxDolDb::getInstance()->getColumn($query);
    }

    /**
     * execute sql query and return the first row of result
     * and keep $array type and poiter to all data
     */
    function getFirstRow($query, $arr_type = PDO::FETCH_ASSOC)
    {
        return BxDolDb::getInstance()->getFirstRow($query, [], $arr_type);
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
     */
    function getNumRows($res = false)
    {
        return BxDolDb::getInstance()->getAffectedRows();
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
     */
    function query($query)
    {
        return BxDolDb::getInstance()->query($query);
    }

    /**
     * execute sql query and return table of records as result
     */
    function getAll($query, $arr_type = PDO::FETCH_ASSOC)
    {
        return BxDolDb::getInstance()->getAll($query, [], $arr_type);
    }

    function error($text)
    {
        dd('called error here in forum BxDb.php');
        $this->log($text.': '.mysql_error($this->link));
    }

    function escape ($s)
    {
        return BxDolDb::getInstance()->escape($s);
    }
}
