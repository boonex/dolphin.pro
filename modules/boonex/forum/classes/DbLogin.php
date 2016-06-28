<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define ('TF_FORUM_USER',	'`'.$gConf['db']['prefix'].'forum_user`');

// join/login operations with database

class DbLogin extends BxDb
{
    function getUserByName ($s)
    {
        $sql = "SELECT `user_name` FROM " . TF_FORUM_USER . " WHERE `user_name` = ? LIMIT 1";
        return $this->getOne ($sql, [$s]);
    }

    function getUserByEmail ($s)
    {
        $sql = "SELECT `user_email` FROM " . TF_FORUM_USER . " WHERE `user_email` = ? LIMIT 1";
        return $this->getOne ($sql, [$s]);
    }

    function insertUser ($p)
    {
        $sql = "INSERT INTO " . TF_FORUM_USER . " SET `user_name` = ?, `user_email` = ?, `user_pwd` = MD5(?), `user_join_date` = UNIX_TIMESTAMP()";
        return $this->query($sql, [$p['username'], $p['email'], $p['pwd']]);
    }

    function checkLogin ($p)
    {
        $sql = "SELECT `user_name` FROM " . TF_FORUM_USER . " WHERE `user_name` = ? AND `user_pwd` = ? LIMIT 1";
        return $this->getRow ($sql, [$p['username'], $p['pwd']]);
    }

    function getUserJoinDate ($u)
    {
        global $gConf;
        return $this->getOne ("SELECT DATE_FORMAT(FROM_UNIXTIME(`user_join_date`),'{$gConf['date_format']}') AS `user_join_date` FROM " . TF_FORUM_USER . " WHERE `user_name` = ? LIMIT 1", [$u]);
    }

// private functions

}
