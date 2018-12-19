<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

// admin operations with database

if (!defined('TF_FORUM')) {
    define('TF_FORUM', '`' . $gConf['db']['prefix'] . 'forum`');
}
if (!defined('TF_FORUM_CAT')) {
    define('TF_FORUM_CAT', '`' . $gConf['db']['prefix'] . 'forum_cat`');
}
if (!defined('TF_FORUM_POST')) {
    define('TF_FORUM_POST', '`' . $gConf['db']['prefix'] . 'forum_post`');
}
if (!defined('TF_FORUM_VOTE')) {
    define('TF_FORUM_VOTE', '`' . $gConf['db']['prefix'] . 'forum_vote`');
}
if (!defined('TF_FORUM_REPORT')) {
    define('TF_FORUM_REPORT', '`' . $gConf['db']['prefix'] . 'forum_report`');
}
if (!defined('TF_FORUM_TOPIC')) {
    define('TF_FORUM_TOPIC', '`' . $gConf['db']['prefix'] . 'forum_topic`');
}
if (!defined('TF_FORUM_FLAG')) {
    define('TF_FORUM_FLAG', '`' . $gConf['db']['prefix'] . 'forum_flag`');
}
if (!defined('TF_FORUM_USER_STAT')) {
    define('TF_FORUM_USER_STAT', '`' . $gConf['db']['prefix'] . 'forum_user_stat`');
}

define('CAT_ORDER_STEP', 128);

class DbAdmin extends BxDb
{
    function deleteCategoryAll($cat_id)
    {
        $sql = "SELECT `forum_id` FROM " . TF_FORUM . " WHERE `cat_id` = '$cat_id'";
        $a   = $this->getAll($sql);
        foreach ($a as $r) {
            $this->deleteForumPosts($r['forum_id']);
            $this->deleteForumTopics($r['forum_id']);
            $this->deleteForum($r['forum_id']);
        }

        return $this->deleteCategory($cat_id);
    }

    function getCatByForumId($forum_id)
    {
        $sql = "SELECT `cat_id`, `cat_uri` FROM " . TF_FORUM . " INNER JOIN " . TF_FORUM_CAT . " USING (`cat_id`) WHERE `forum_id` = ? LIMIT 1";

        return $this->getRow($sql, [$forum_id]);
    }

    function getCatOrder($cat_id)
    {
        if (!$cat_id) {
            return false;
        }
        $sql = "SELECT `cat_order` FROM " . TF_FORUM_CAT . " WHERE `cat_id` = ? LIMIT 1";

        return $this->getOne($sql, [$cat_id]);
    }

    function getCatsInOrder($cat_order, $dir, $num)
    {
        $sql = "SELECT `cat_id`,`cat_order` FROM  " . TF_FORUM_CAT . " WHERE `cat_order` " . ($dir == 'up' ? '<' : '>') . " $cat_order  ORDER BY  `cat_order` " . ($dir == 'up' ? 'DESC' : 'ASC') . " LIMIT $num";

        return $this->getAll($sql);
    }

    function deleteForumAll($forum_id)
    {
        $this->deleteForumPosts($forum_id);
        $this->deleteForumTopics($forum_id);

        return $this->deleteForum($forum_id);
    }

    function deleteCategory($cat_id)
    {
        $sql = "DELETE FROM " . TF_FORUM_CAT . " WHERE `cat_id` = ?";

        return $this->query($sql, [$cat_id]);
    }

    function deleteForumPosts($forum_id)
    {
        $a = $this->getAll("SELECT `post_id`, `user` FROM " . TF_FORUM_POST . " WHERE `forum_id` = '$forum_id'");
        foreach ($a as $r) {
            $this->query("DELETE FROM " . TF_FORUM_VOTE . " WHERE `post_id` = ?", [$r['post_id']]);
            $this->query("DELETE FROM " . TF_FORUM_REPORT . " WHERE `post_id` = ?", [$r['post_id']]);
            $this->query("UPDATE " . TF_FORUM_USER_STAT . " SET `posts` = `posts` - 1 WHERE `user` = ? AND `posts` > 0", [$r['user']]);
        }

        return $this->query("DELETE FROM " . TF_FORUM_POST . " WHERE `forum_id` = ?", [$forum_id]);
    }

    function deleteForumTopics($forum_id)
    {
        $a = $this->getAll("SELECT `topic_id` FROM " . TF_FORUM_TOPIC . " WHERE `forum_id` = ?", [$forum_id]);
        foreach ($a as $r) {
            $this->query("DELETE FROM " . TF_FORUM_FLAG . " WHERE `topic_id` = ?", [$r['topic_id']]);
        }

        return $this->query("DELETE FROM " . TF_FORUM_TOPIC . " WHERE `forum_id` = ?", [$forum_id]);
    }

    function deleteForum($forum_id)
    {
        $sql = "DELETE FROM " . TF_FORUM . " WHERE `forum_id` = ?";

        return $this->query($sql, [$forum_id]);
    }

    function getCatName($cat_id)
    {
        $sql = "SELECT `cat_name` FROM " . TF_FORUM_CAT . " WHERE `cat_id` = ? LIMIT 1";

        return $this->getOne($sql, [$cat_id]);
    }

    function editCategory($cat_id, $cat_name, $cat_order, $cat_expanded)
    {
        $sql = "UPDATE " . TF_FORUM_CAT . " SET `cat_name` = ?, `cat_order` = ?, `cat_expanded` = ? WHERE `cat_id` = ?";

        return $this->query($sql, [$cat_name, $cat_order, $cat_expanded, $cat_id]);
    }

    function insertCategory($cat_name, $uri, $cat_order, $cat_expanded)
    {
        $sql = "INSERT INTO " . TF_FORUM_CAT . " SET `cat_name` = ?, `cat_uri` = ?, `cat_order` = ?, `cat_expanded` = ?";

        return $this->query($sql, [$cat_name, $uri, $cat_order, $cat_expanded]);
    }

    function getForum($forum_id)
    {
        $sql = "SELECT `cat_id`, `forum_title`, `forum_desc`, `forum_order`, `forum_type` FROM " . TF_FORUM . " WHERE `forum_id` = ? LIMIT 1";

        return $this->getRow($sql, [$forum_id]);
    }

    function editForum($forum_id, $title, $desc, $type, $order)
    {
        $sql = "UPDATE " . TF_FORUM . " SET `forum_title` = ?, `forum_desc` = ?, `forum_type` = ?, `forum_order` = ? WHERE `forum_id` = ?";

        return $this->query($sql, [$title, $desc, $type, $order, $forum_id]);
    }

    function insertForum($cat_id, $title, $desc, $type, $uri, $order)
    {
        $sql = "INSERT INTO " . TF_FORUM . " SET `cat_id` = ?, `forum_title` = ?, `forum_desc` = ?, `forum_type` = ?, `forum_uri` = ?, `forum_order` = ?";

        return $this->query($sql, [$cat_id, $title, $desc, $type, $uri, $order]);
    }

    function getReportedPosts($u)
    {
        return $this->getXxxPosts($u, ' AND `reports` != 0');
    }

    function getHiddenPosts($u)
    {
        return $this->getXxxPosts($u, ' AND `hidden` != 0');
    }

    function getXxxPosts($u, $sWhere = '')
    {
        global $gConf;

        $sql_add1 = ", '-1' AS `voted`, 0 as `vote_user_point` ";
        $sql_add2 = '';

        if ($u) {
            $sql_add1 = ", (1 - ISNULL(t2.`post_id`)) AS `voted`, t2.`vote_point` as `vote_user_point` ";
            $sql_add2 = " LEFT JOIN " . TF_FORUM_VOTE . " AS t2 ON ( t2.`user_name` = '$u' AND t1.`post_id` = t2.`post_id`) ";
        }

        $sql = "SELECT `forum_id`, `topic_id`, t1.`post_id`, `user`, `post_text`, `votes`, `hidden`, t1.`when` $sql_add1  FROM " . TF_FORUM_POST . " AS t1 $sql_add2 WHERE 1 $sWhere ORDER BY t1.`when` DESC";

        return $this->getAll($sql);
    }

    function clearReport($post_id)
    {
        return $this->query("UPDATE " . TF_FORUM_POST . " SET `reports` = 0 WHERE `post_id` = ? LIMIT 1", [$post_id]);
    }

    // private functions

}
