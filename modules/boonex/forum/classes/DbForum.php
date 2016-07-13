<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define ('TF_FORUM',			 '`'.$gConf['db']['prefix'].'forum`');
define ('TF_FORUM_CAT',		 '`'.$gConf['db']['prefix'].'forum_cat`');
define ('TF_FORUM_POST',	 '`'.$gConf['db']['prefix'].'forum_post`');
define ('TF_FORUM_TOPIC',	 '`'.$gConf['db']['prefix'].'forum_topic`');
define ('TF_FORUM_VOTE',	 '`'.$gConf['db']['prefix'].'forum_vote`');
define ('TF_FORUM_FLAG',	 '`'.$gConf['db']['prefix'].'forum_flag`');
define ('TF_FORUM_USER_ACT', '`'.$gConf['db']['prefix'].'forum_user_activity`');
define ('TF_FORUM_USER_STAT', '`'.$gConf['db']['prefix'].'forum_user_stat`');
define ('TF_FORUM_ACTIONS_LOG', '`'.$gConf['db']['prefix'].'forum_actions_log`');
define ('TF_FORUM_ATTACHMENTS', '`'.$gConf['db']['prefix'].'forum_attachments`');
define ('TF_FORUM_SIGNATURES',  '`'.$gConf['db']['prefix'].'forum_signatures`');

define ('TF_ACTION_REPORT', 'report');
define ('TF_ACTION_STICK', 'stick');
define ('TF_ACTION_LOCK', 'lock');
define ('TF_ACTION_HIDE_TOPIC', 'hide topic');
define ('TF_ACTION_HIDE_POST', 'hide post');
define ('TF_ACTION_UNHIDE_TOPIC', 'unhide topic');
define ('TF_ACTION_UNHIDE_POST', 'unhide post');
define ('TF_ACTION_DEL_TOPIC', 'del topic');
define ('TF_ACTION_DEL_POST', 'del post');
define ('TF_ACTION_EDIT_POST', 'edit post');
define ('TF_ACTION_RESET_VOTES', 'reset votes');

// forum database functions

class DbForum extends BxDb
{
    var $_aUserTables2Field = array (
        'forum_actions_log' => 'user_name',
        'forum_flag' => 'user',
        'forum_post' => 'user',
        'forum_signatures' => 'user',
        'forum_topic' => 'first_post_user',
        'forum_user_activity' => 'user',
        'forum_user_stat' => 'user',
        'forum_vote' => 'user_name',
    );

    /**
     * constructor
     */
    function DbForum ()
    {
        global $gConf;
        parent::BxDb ($gConf['db']['db'], $gConf['db']['user'], $gConf['db']['pwd'], $gConf['db']['host'], $gConf['db']['port'], $gConf['db']['sock']);
    }

    function searchMessages ($s, $u, $f, $type, $posts, $start, &$num = null)
    {
        global $gConf;

        $order_by = 'ORDER BY `last_post_when` DESC';
        $sql_w = '';
        $fields = '';

        $sCalcFoundRows = '';
        if ($num !== null) $sCalcFoundRows = 'SQL_CALC_FOUND_ROWS';

        if ($start < 0) $start = 0;

        $u = trim ($u);
        $s = trim ($s);

        if (strlen($s) > 2) {
            if ($gConf['fulltext_search']) {

                $match = '';
                if ('msgs' == $type)
                    $match = "MATCH (`post_text`) AGAINST ('$s') > 1";
                else // titles
                    $match = "MATCH (`topic_title`) AGAINST ('$s') > 1";

                //$fields .= ", $match AS `score`";
                //$order_by = 'ORDER BY `score` DESC, `last_post_when` DESC';
                $order_by = 'ORDER BY `last_post_when` DESC';
                $sql_w .= " AND $match ";

            } else {

                $s = preg_replace ('/\s+/', '%', $s);
                if ('msgs' == $type) // messages
                    $sql_w .= " AND `post_text` LIKE '%$s%' ";
                else // titles
                    $sql_w .= " AND `topic_title` LIKE '%$s%' ";
                $order_by = 'ORDER BY `last_post_when` DESC';
            }
        }

        if (strlen($u) > 2) {
            $u = preg_replace ('/\s+/', '%', $u);
            if ('msgs' == $type)
                $sql_w .= " AND `user` LIKE '$u%' ";
            else
                $sql_w .= " AND `first_post_user` LIKE '$u%' ";
        }

        if ($f > 0) {
            $sql_w .= " AND t3.`forum_id` = '$f' ";
        }

        if ($posts) $fields .= ', `post_text` ';

        if ('msgs' == $type) {
            $sSQL = "
    SELECT DISTINCTROW $sCalcFoundRows t4.`cat_id`, t4.`cat_uri`, `cat_name`, t3.`forum_id`, t3.`forum_uri`, t3.`forum_type`, `forum_title`, t2.`topic_id`, t2.`topic_uri`, `topic_title`, `post_id`, t1.`when` AS `date`, `user` $fields
    FROM " . TF_FORUM_POST . " AS t1
    INNER JOIN  " . TF_FORUM_TOPIC . " AS t2 ON (t2.`topic_id` = t1.`topic_id`)
    INNER JOIN  " . TF_FORUM . " AS t3 ON (t1.`forum_id` = t3.`forum_id`)
    INNER JOIN " . TF_FORUM_CAT . " AS t4 ON (t3.`cat_id` = t4.`cat_id`)
    WHERE t2.`topic_hidden` = '0' $sql_w
    $order_by
    LIMIT $start, {$gConf['topics_per_page']}";
        } else { // search titles
            $sSQL = "
    SELECT $sCalcFoundRows t4.`cat_id`, t4.`cat_uri`, `cat_name`, t3.`forum_id`, t3.`forum_uri`, t3.`forum_type`, `forum_title`, t2.`topic_id`, t2.`topic_uri`, `topic_title`, `first_post_when` AS `date`, `first_post_user` AS `user` $fields
    FROM " . TF_FORUM_TOPIC . " AS t2
    INNER JOIN  " . TF_FORUM . " AS t3 ON (t2.`forum_id` = t3.`forum_id`)
    INNER JOIN " . TF_FORUM_CAT . " AS t4 ON (t3.`cat_id` = t4.`cat_id`)
    WHERE t2.`topic_hidden` = '0' $sql_w
    $order_by
    LIMIT $start, {$gConf['topics_per_page']}";
        }

        $aRows = $this->getAll ($sSQL);

        if ($num !== null) $num = $this->getOne('SELECT FOUND_ROWS()');

        return $aRows;
    }

    function getCategs ()
    {
        return $this->getAll ("SELECT `tc`.`cat_id`, `tc`.`cat_uri`, `tc`.`cat_name`, `tc`.`cat_icon`, `tc`.`cat_expanded`, SUM(`tf`.`forum_posts`) AS `count_posts`, SUM(`tf`.`forum_topics`) AS `count_topics`, COUNT(`tf`.`forum_id`) AS `count_forums` FROM " . TF_FORUM_CAT . " AS `tc` LEFT JOIN " . TF_FORUM . " AS `tf` ON  (`tc`.`cat_id` = `tf`.`cat_id`) GROUP BY `tc`.`cat_id` ORDER BY `tc`.`cat_order` ASC");
    }

    function getCatTitle ($id)
    {
        return $this->getOne ("SELECT `cat_name` FROM " . TF_FORUM_CAT . " WHERE `cat_id` = '$id'");
    }

    function getCat ($id)
    {
        return $this->getRow ("SELECT `cat_id`, `cat_uri`, `cat_name`, `cat_order`, `cat_expanded` FROM " . TF_FORUM_CAT . " WHERE `cat_id` = '$id'");
    }

    function getForums ($c)
    {
        global $gConf;
        return $this->getAll ( "SELECT `forum_id`, `forum_uri`, `cat_id`, `forum_title`, `forum_desc`, `forum_type`, `forum_posts`, `forum_topics`, `forum_last` AS `forum_last`, `forum_last` AS `forum_last_ts` FROM " . TF_FORUM . ($c ? " WHERE `cat_id` = '$c'": '') . ' ORDER BY `forum_order` ASC');
    }

    function getForumsByCatUri ($c)
    {
        global $gConf;
        return $this->getAll ( "SELECT `forum_id`, `forum_uri`, `tc`.`cat_id`, `tc`.`cat_uri`, `forum_title`, `forum_desc`, `forum_type`, `forum_posts`, `forum_topics`, `forum_last` AS `forum_last`, `forum_last` AS `forum_last_ts` FROM " . TF_FORUM . " AS `tf` INNER JOIN " . TF_FORUM_CAT . " AS `tc` ON (`tf`.`cat_id` = `tc`.`cat_id`) " . ($c ? " WHERE `cat_uri` = '$c'": '') . ' ORDER BY `forum_order` ASC');
    }

    function getForum ($f)
    {
        return $this->getForumBy ('forum_id', $f);
    }

    function getForumByUri ($f)
    {
        return $this->getForumBy ('forum_uri', $f);
    }

    function getForumBy ($sName, $sVal)
    {
        global $gConf;
        return $this->getRow ( "SELECT `cat_id`, `forum_id`, `forum_uri`, `forum_title`, `forum_desc`, `forum_order`, `forum_type`, `forum_posts`, `forum_last` AS `forum_last` FROM " . TF_FORUM . " WHERE `$sName` = '$sVal' LIMIT 1");
    }

    function getForumByPostId ($post_id)
    {
        return $this->getRow ( "SELECT `tf`.`forum_id`, `tf`.`forum_uri`, `tf`.`forum_type` FROM " . TF_FORUM . " AS `tf` INNER JOIN " . TF_FORUM_POST . " USING(`forum_id`) WHERE `post_id` = '$post_id' LIMIT 1");
    }

    function getForumByTopicId ($topic_id)
    {
        return $this->getRow ( "SELECT `tf`.`forum_id`, `tf`.`forum_uri`, `tf`.`forum_type` FROM " . TF_FORUM . " AS `tf` INNER JOIN " . TF_FORUM_TOPIC . " USING(`forum_id`) WHERE `topic_id` = '$topic_id' LIMIT 1");
    }

    function getPostIds ($p)
    {
        return $this->getRow ( "SELECT `forum_id`, `topic_id` FROM " . TF_FORUM_POST . " WHERE `post_id` = '$p' LIMIT 1");
    }

    function getTopicsNum ($f)
    {
        return $this->_getTopicsNumWithCondition(" AND `topic_hidden` = '0' AND `forum_id` = '$f' ");
    }

    function getRecentTopicsNum ()
    {
        return $this->_getTopicsNumWithCondition(" AND `topic_hidden` = '0' ");
    }

    function _getTopicsNumWithCondition ($sWhere = '')
    {
        return $this->getOne ("SELECT COUNT(`topic_id`) FROM " . TF_FORUM_TOPIC . " WHERE 1 $sWhere");
    }

    function getTopics ($f, $start)
    {
        global $gConf;

        $sql = "SELECT f1.`topic_id`, f1.`topic_uri`, `topic_title`, `first_post_user`, `first_post_when` AS `first_when`, `last_post_user`, `last_post_when` AS `last_when`, `last_post_when`, `topic_posts` AS `count_posts`, `topic_sticky`, `topic_locked` FROM " . TF_FORUM_TOPIC . " AS f1 WHERE f1.`topic_hidden` = '0' AND f1.`forum_id` = '$f' ORDER BY `topic_sticky` DESC, `last_post_when` DESC, f1.`topic_id` DESC LIMIT $start, {$gConf['topics_per_page']}";
        return $this->getAll ($sql);
    }

    function getRecentTopics ($start)
    {
        global $gConf;

        return $this->getAll ( "SELECT
                f1.`topic_id`, f1.`topic_uri`, `topic_title`, `first_post_user`, `topic_posts` AS `count_posts`, `topic_sticky`, `topic_locked`,
                `first_post_when` AS `first_when`, `last_post_user`,
                `last_post_when` AS `last_when`, `last_post_when`,
                `tf`.`forum_id`, `tf`.`forum_uri`, `tf`.`forum_title`, `tf`.`forum_type`,
                `tc`.`cat_id`, `tc`.`cat_uri`, `tc`.`cat_name`
            FROM " . TF_FORUM_TOPIC . " AS f1
            INNER JOIN " . TF_FORUM . " AS `tf` ON (`tf`.`forum_id` = `f1`.`forum_id`)
            INNER JOIN " . TF_FORUM_CAT . " AS `tc` ON (`tc`.`cat_id` = `tf`.`cat_id`)
            WHERE f1.`topic_hidden` = '0'
            ORDER BY `last_post_when` DESC, f1.`topic_id` DESC
            LIMIT $start, {$gConf['topics_per_page']}");
    }

    function getHiddenTopics ($u, $start = 0, &$num = null)
    {
        global $gConf;

        $sCalcFoundRows = '';
        if ($num !== null) $sCalcFoundRows = 'SQL_CALC_FOUND_ROWS';

        if ($start < 0) $start = 0;

        $sql = "SELECT $sCalcFoundRows f1.`topic_id`, f1.`topic_uri`, `topic_title`, `last_post_when`, `topic_posts` AS `count_posts` FROM " . TF_FORUM_TOPIC . " AS f1 WHERE f1.`topic_hidden` = '1' ORDER BY `last_post_when` DESC LIMIT $start, {$gConf['topics_per_page']}";

        $aRows = $this->getAll ($sql);
        if ($num !== null) $num = $this->getOne('SELECT FOUND_ROWS()');
        return $aRows;
    }

    function getMyFlaggedTopics ($u, $start = 0, &$num = null)
    {
        global $gConf;

        $sCalcFoundRows = '';
        if ($num !== null) $sCalcFoundRows = 'SQL_CALC_FOUND_ROWS';

        if ($start < 0) $start = 0;

        $sql = "SELECT $sCalcFoundRows f1.`topic_id`, f1.`topic_uri`, `topic_title`, `last_post_when`, `topic_posts` AS `count_posts` FROM " . TF_FORUM_TOPIC . " AS f1 INNER JOIN "  . TF_FORUM_FLAG . " AS f2 USING (`topic_id`) WHERE f2.`user` = '$u' ORDER BY `last_post_when` DESC LIMIT $start, {$gConf['topics_per_page']}";

        $aRows = $this->getAll ($sql);
        if ($num !== null) $num = $this->getOne('SELECT FOUND_ROWS()');
        return $aRows;
    }

    function getSubscribersToTopic ($iTopicId)
    {
        return $this->getAll ("SELECT `user` FROM "  . TF_FORUM_FLAG . " WHERE `topic_id` = '$iTopicId'");
    }

    function getMyThreadsTopics ($u, $start = 0, &$num = null)
    {
        global $gConf;

        $sCalcFoundRows = '';
        if ($num !== null) $sCalcFoundRows = 'SQL_CALC_FOUND_ROWS';

        if ($start < 0) $start = 0;

        $sql = "SELECT DISTINCTROW $sCalcFoundRows f1.`topic_id`, f1.`topic_uri`, `topic_title`, `last_post_when`, `topic_posts` AS `count_posts` FROM " . TF_FORUM_TOPIC . " AS f1 INNER JOIN "  . TF_FORUM_POST . " AS f2 USING (`topic_id`) WHERE f2.`user` = '$u' ORDER BY `last_post_when` DESC LIMIT $start, {$gConf['topics_per_page']}";

        $aRows = $this->getAll ($sql);
        if ($num !== null) $num = $this->getOne('SELECT FOUND_ROWS()');
        return $aRows;
    }

    function getTopic ($t)
    {
        return $this->getTopicBy ('topic_id', $t);
    }

    function getTopicByUri ($t)
    {
        return $this->getTopicBy ('topic_uri', $t);
    }

    function getTopicBy ($sName, $sVal)
    {
        return $this->getRow ( "SELECT `topic_id`, `topic_uri`, `topic_title`, `topic_posts`, `forum_title`, `forum_desc`, `forum_type`, `forum_uri`, f1.`forum_id`, `cat_id`, `topic_locked`, `topic_sticky`, `topic_hidden` FROM " . TF_FORUM_TOPIC . " AS f1 INNER JOIN " . TF_FORUM . " USING (`forum_id`) WHERE f1.`$sName` = '$sVal' LIMIT 1");
    }

    function getPostUser ($p)
    {
        return $this->getOne ( "SELECT `user` FROM " . TF_FORUM_POST . " WHERE `post_id` = '$p'");
    }

    function getTopicPost ($t, $x = 'last')
    {
        global $gConf;
        $sOrderDir = ('last' == $x ?  'DESC' : 'ASC');
        return $this->getRow ( "SELECT `user`, t1.`when` AS `when2`, `when` FROM " . TF_FORUM_POST . " AS t1 WHERE `topic_id` = '$t' ORDER BY t1.`when` $sOrderDir, t1.`post_id` $sOrderDir LIMIT 1");
    }

    function getTopicDesc ($t)
    {
        return $this->getOne ( "SELECT `post_text` FROM " . TF_FORUM_POST . " WHERE `topic_id` = '$t' ORDER BY `when` ASC LIMIT 1");
    }

    function editPost ($p, $text, $user)
    {
        $this->logAction ($p, $user, TF_ACTION_EDIT_POST);
        return $this->query ("UPDATE " . TF_FORUM_POST . " SET `post_text` = '$text' WHERE post_id = '$p'");
    }

    function newTopic ($f, $title, $text, $sticky, $user, $uri)
    {
        $ts = time ();

        $sticky = $sticky ? $ts : 0;

        // add topic title
        if (!$this->query ("INSERT INTO" . TF_FORUM_TOPIC . " SET `topic_posts` = 1, `forum_id` = '$f', `topic_title` = '$title', `when` = '$ts', `first_post_user` = '$user', `first_post_when` = '$ts', `last_post_user` = '$user', `last_post_when` = '$ts', `topic_sticky` = '$sticky', `topic_uri` = '$uri'"))
            return false;

        // get topic_id
        if (!($topic_id = $this->getOne ("SELECT `topic_id` FROM " . TF_FORUM_TOPIC . " WHERE `forum_id` = '$f' AND `when` = '$ts'")))
            return false;

        // add topic post
        if (!$this->query ("INSERT INTO" . TF_FORUM_POST . " SET `topic_id` = '$topic_id', `forum_id` = '$f', `user` = '$user', `post_text` = '$text', `when` = '$ts'"))
            return false;

        $iPostId = $this->getLastId();

        // increase number of forum posts and set timeof last post
        if (!$this->query ("UPDATE" . TF_FORUM . " SET `forum_posts` = `forum_posts` + 1, `forum_topics` = `forum_topics` + 1, `forum_last` = '$ts' WHERE `forum_id` = '$f'"))
            return false;

        // update user stats
        $this->userStatsInc ($user, $ts);

        return $iPostId;
    }

    function stick ($topic_id, $user)
    {
        if (!$this->query ("UPDATE" . TF_FORUM_TOPIC . " SET `topic_sticky` = IF(`topic_sticky`, 0, '" . time() . "') WHERE `topic_id` = '{$topic_id}'"))
            return false;

        $this->logAction ($topic_id, $user, TF_ACTION_STICK);

        return true;
    }

    function hideTopic ($is_hide, $topic_id, $user)
    {
        if (!$this->query ("UPDATE" . TF_FORUM_TOPIC . " SET `topic_hidden` = '" . ($is_hide ? 1 : 0) . "' WHERE `topic_id` = '{$topic_id}'"))
            return false;

        $this->logAction ($topic_id, $user, $is_hide ? TF_ACTION_HIDE_TOPIC : TF_ACTION_UNHIDE_TOPIC);

        return true;
    }

    function hidePost ($is_hide, $post_id, $user)
    {
        if (!$this->query ("UPDATE" . TF_FORUM_POST . " SET `hidden` = '" . ($is_hide ? 1 : 0) . "' WHERE `post_id` = '{$post_id}'"))
            return false;

        $this->logAction ($post_id, $user, $is_hide ? TF_ACTION_HIDE_POST : TF_ACTION_UNHIDE_POST);

        if (!$is_hide) {
            $p = $this->getPost ($post_id, '');
            if ($p['votes'] < 0 && $this->query("UPDATE " . TF_FORUM_POST . " SET `votes` = 0 WHERE `post_id` = '$post_id' LIMIT 1"))
                $this->logAction ($post_id, $user, TF_ACTION_RESET_VOTES);
        }

        return true;
    }

    function deletePost ($post_id, $user = '')
    {
        $a = $this->getPostIds ($post_id);

        if (!$this->_deletePostWithoutUpdatingTopic ($post_id, $a['forum_id']))
            return false;

        if ($user)
            $this->logAction ($post_id, $user, TF_ACTION_DEL_POST);

        // update last post
        $last = $this->getTopicPost ($a['topic_id'], 'last');

        // decrease number of topic posts
        if (!$this->query ("UPDATE" . TF_FORUM_TOPIC . " SET `topic_posts` = `topic_posts` - 1, `last_post_user` = '{$last['user']}', `last_post_when` = '{$last['when2']}' WHERE `topic_id` = '{$a['topic_id']}'"))
            return false;

        // delete topic
        if (0 == $this->getOne("SELECT COUNT(*) FROM " . TF_FORUM_POST . " WHERE `topic_id` = '{$a['topic_id']}'")) {
            $this->delTopic ($a['topic_id'], '');
        }

        return true;
    }

    function _deletePostWithoutUpdatingTopic ($post_id, $forum_id)
    {
        $user = $this->getPostUser ($post_id);

        // delete post
        if (!$this->query ("DELETE FROM " . TF_FORUM_POST . " WHERE `post_id` = '$post_id'"))
            return false;

        // decrease number of forum posts
        $this->query ("UPDATE" . TF_FORUM . " SET `forum_posts` = `forum_posts` - 1 WHERE `forum_id` = '{$forum_id}'");

        // update user stats
        $this->userStatsDec ($user);

        $this->removeAttachments ($post_id);

        return true;
    }

    function delTopic ($topic_id, $user)
    {
        $t = $this->getTopic ($topic_id);

        if (!$this->query("DELETE FROM " . TF_FORUM_TOPIC . " WHERE `topic_id` = '{$topic_id}'"))
            return false;

        $this->logAction ($topic_id, $user, TF_ACTION_DEL_TOPIC);

        // descrease number of topics
        $this->query ("UPDATE " . TF_FORUM . " SET `forum_topics` = `forum_topics` - 1 WHERE `forum_id` = '{$t['forum_id']}'");

        // delete flags/subscriptions
        $this->query("DELETE FROM " . TF_FORUM_FLAG . " WHERE `topic_id` = '{$topic_id}'");

        // delete posts
        $sql =  "SELECT `post_id` FROM " . TF_FORUM_POST . " WHERE `topic_id` = '{$topic_id}'";
        $p = $this->getAll ($sql);
        foreach ($p as $r)
            $this->_deletePostWithoutUpdatingTopic ($r['post_id'], $t['forum_id']);

        return true;
    }

    function moveTopic ($topic_id, $forum_id, $old_forum_id)
    {
        if (!$this->query ("UPDATE " . TF_FORUM_TOPIC . " SET `forum_id` = '{$forum_id}' WHERE `topic_id` = '{$topic_id}'"))
            return false;

        $t = $this->getTopic ($topic_id);
        if (!$t)
            return false;

        // update topic posts
        $this->query ("UPDATE " . TF_FORUM_POST . " SET `forum_id` = '{$forum_id}' WHERE `topic_id` = '{$topic_id}'");

        // descrease number of topics/posts in old forum
        $this->query ("UPDATE " . TF_FORUM . " SET `forum_topics` = `forum_topics` - 1, `forum_posts` = `forum_posts` - {$t['topic_posts']} WHERE `forum_id` = '{$old_forum_id}'");

        // increase number of topics/posts in new forum
        $this->query ("UPDATE " . TF_FORUM . " SET `forum_topics` = `forum_topics` + 1, `forum_posts` = `forum_posts` + {$t['topic_posts']} WHERE `forum_id` = '{$forum_id}'");

        return true;
    }

    function postReply ($forum_id, $topic_id, $text, $user)
    {
        $ts = time ();

        // add topic post
        if (!$this->query ("INSERT INTO" . TF_FORUM_POST . " SET `topic_id` = '$topic_id', `forum_id` = '$forum_id', `user` = '$user', `post_text` = '$text', `when` = '$ts'"))
            return false;

        $iReplyId = $this->getOne("SELECT LAST_INSERT_ID()");

        // increase number of forum posts and set timeof last post
        if (!$this->query ("UPDATE" . TF_FORUM . " SET `forum_posts` = `forum_posts` + 1, `forum_last` = '$ts' WHERE `forum_id` = '$forum_id'"))
            return $iReplyId;

        // update last post
        $last = $this->getPost ($iReplyId, '');

        // increase number of topic posts
        if (!$this->query ("UPDATE" . TF_FORUM_TOPIC . " SET `topic_posts` = `topic_posts` + 1, `last_post_user` = '{$last['user']}', `last_post_when` = '{$last['when']}' WHERE `topic_id` = '{$topic_id}'"))
            return $iReplyId;

        // update user stats
        $this->userStatsInc ($user, $ts);

        return $iReplyId;
    }

    function getPosts ($t, $u, $sOrder = 'ASC', $iLimit = 0)
    {
        return $this->getPostsBy ($u, '`ft`.`topic_id`', $t, $sOrder, $iLimit);
    }

    function getPostsByUri ($t, $u)
    {
        return $this->getPostsBy ($u, '`ft`.`topic_uri`', $t);
    }

    function getPostsBy ($u, $sName, $sVal, $sOrder = 'ASC', $iLimit = 0)
    {
        global $gConf;

        $sql_add1 = ", '-1' AS `voted`, 0 as `vote_user_point` ";
        $sql_add2 = '';

        if ($u) {
            $sql_add1 = ", (1 - ISNULL(t2.`post_id`)) AS `voted`, t2.`vote_point` as `vote_user_point` ";
            $sql_add2 = " LEFT JOIN " . TF_FORUM_VOTE . " AS t2 ON ( t2.`user_name` = '$u' AND t1.`post_id` = t2.`post_id`) ";
        }

        $sql =  "SELECT `ft`.`forum_id`, `t1`.`topic_id`, `t1`.`post_id`, `user`, `post_text`, `votes`, `hidden`, t1.`when` $sql_add1 FROM " . TF_FORUM_POST . " AS t1 $sql_add2 INNER JOIN " . TF_FORUM_TOPIC . " AS `ft`  ON (`ft`.`topic_id` = `t1`.`topic_id`) WHERE $sName = '$sVal' ORDER BY t1.`when` " . ('ASC' == $sOrder ? 'ASC' : 'DESC') . ((int)$iLimit ? ' LIMIT ' . (int)$iLimit : '');

        return $this->getAll ($sql);
    }

    function _cutPostText (&$a, $sPostTextField = 'post_text')
    {
        foreach ($a as $k => $v) {
            if ($sPostTextField)
                $a[$k][$sPostTextField] = orca_mb_substr ($a[$k][$sPostTextField], 0, 255);
        }

    }

    function getUserPostsList ($user, $sort, $limit = 10)
    {
        global $gConf;

        switch ($sort) {
            case 'top':
                $order_by = " t1.`votes` DESC ";
                break;
            case 'rnd':
                $order_by = " RAND() ";
                break;
            default:
                $order_by = " t1.`when` DESC ";
        }

        $sql =  "
        SELECT t1.`forum_id`, t1.`topic_id`, t2.`topic_uri`, t2.`topic_title`, t1.`post_id`, t1.`user`, `post_text`, t1.`when`
            FROM " . TF_FORUM_POST . " AS t1
        INNER JOIN " . TF_FORUM_TOPIC . " AS t2
            ON (t1.`topic_id` = t2.`topic_id`)
        WHERE  t1.`user` = '$user' AND `t2`.`topic_hidden` = '0'
        ORDER BY " . $order_by . "
        LIMIT $limit";

        $a = $this->getAll ($sql);
        $this->_cutPostText($a);
        return $a;
    }

    function getAllPostsList ($sort, $limit = 10)
    {
        global $gConf;

        switch ($sort) {
            case 'top':
                $order_by = " t1.`votes` DESC ";
                break;
            case 'rnd':
                $order_by = " RAND() ";
                break;
            default:
                $order_by = " t1.`when` DESC ";
        }

        $sql =  "
        SELECT t1.`forum_id`, t1.`topic_id`, t2.`topic_uri`, t2.`topic_title`, t1.`post_id`, t1.`user`, `post_text`, t1.`when`
            FROM " . TF_FORUM_POST . " AS t1
        INNER JOIN " . TF_FORUM_TOPIC . " AS t2
            ON (t1.`topic_id` = t2.`topic_id`)
        WHERE `t2`.`topic_hidden` = '0'
        ORDER BY " . $order_by . "
        LIMIT $limit";

        $a = $this->getAll ($sql);
        $this->_cutPostText($a);
        return $a;
    }

    function getPost ($post_id, $u)
    {
        global $gConf;

        $sql_add1 = ", '-1' AS `voted`, 0 as `vote_user_point` ";
        $sql_add2 = '';

        if ($u) {
            $sql_add1 = ", (1 - ISNULL(t2.`post_id`)) AS `voted`, t2.`vote_point` as `vote_user_point` ";
            $sql_add2 = " LEFT JOIN " . TF_FORUM_VOTE . " AS t2 ON ( t2.`user_name` = '$u' AND t1.`post_id` = t2.`post_id`) ";
        }

        $sql =  "SELECT `forum_id`, `topic_id`, t1.`post_id`, `user`, `post_text`, `votes`, `hidden`, t1.`when` $sql_add1  FROM " . TF_FORUM_POST . " AS t1 $sql_add2 WHERE t1.`post_id` = '$post_id' LIMIT 1";
        return $this->getRow ($sql);
    }

    function getPostWhen ($post_id)
    {
        return $this->getOne ("SELECT `when` FROM " . TF_FORUM_POST . " WHERE `post_id` = '$post_id' LIMIT 1");
    }

    function getUserPosts ($u)
    {
        //return $this->getOne ("SELECT COUNT(`post_id`) FROM " . TF_FORUM_POST . " WHERE `user` = '$u'");
        return (int)$this->getOne ("SELECT `posts` FROM " . TF_FORUM_USER_STAT . " WHERE `user` = '$u'");
    }

    function insertVote ($post_id, $u, $vote)
    {
        $isOwnPost = $this->getOne("SELECT `post_id` FROM " . TF_FORUM_POST . " WHERE `user` = '$u' AND `post_id` = '$post_id' LIMIT 1");
        if ($isOwnPost)
            return false;

        $vote_prev = $this->getOne("SELECT `vote_point` FROM " . TF_FORUM_VOTE . " WHERE `user_name` = '$u' AND `post_id` = '$post_id'");

        $sql = "INSERT INTO " . TF_FORUM_VOTE . " SET `user_name` = '$u', `post_id` = '$post_id', `vote_point` = " . ($vote > 0 ? '1' : '-1') . ", `vote_when` = UNIX_TIMESTAMP() ON DUPLICATE KEY UPDATE `vote_point` = " . ($vote > 0 ? '1' : '-1') . ", `vote_when` = UNIX_TIMESTAMP()";
        if (!$this->query($sql))
            return false;
    
        $diff = $vote - $vote_prev;
        if ($diff > 0 || $diff < -1)
            return $this->query("UPDATE " . TF_FORUM_POST . " SET `votes` = `votes` + " . ($diff < -1 ? -1 : 1) . " WHERE `post_id` = '$post_id' LIMIT 1");
        else
            return true;
    }

    function getTopicByPostId ($post_id)
    {
        $sql = "SELECT `topic_id`, `forum_id` FROM " . TF_FORUM_POST . " WHERE `post_id` = '$post_id'";
        return $this->getRow ($sql);
    }

    function report ($post_id, $u)
    {
        $this->logAction ($post_id, $u, TF_ACTION_REPORT);

        $sql = "UPDATE " . TF_FORUM_POST . " SET `reports` = `reports` + 1 WHERE `post_id` = '$post_id' LIMIT 1";
        return $this->query($sql);
    }

    function isFlagged ($topic_id, $u)
    {
        $sql = "SELECT `topic_id` FROM " . TF_FORUM_FLAG . " WHERE `user` = '$u' AND `topic_id` = '$topic_id'";
        return $this->getOne ($sql);
    }

    function flag ($topic_id, $u)
    {
        $sql = "INSERT INTO " . TF_FORUM_FLAG . " SET `user` = '$u', `topic_id` = '$topic_id', `when` = UNIX_TIMESTAMP()";
        return $this->query ($sql);
    }

    function unflag ($topic_id, $u)
    {
        $sql = "DELETE FROM " . TF_FORUM_FLAG . " WHERE `user` = '$u' AND `topic_id` = '$topic_id' LIMIT 1";
        return $this->query ($sql);
    }

    function updateUserActivity ($user)
    {
        global $gConf;

        $sql = "SELECT `act_current` FROM " . TF_FORUM_USER_ACT . " WHERE `user`  = '$user' LIMIT 1";
        $current = (int)$this->getOne ($sql);

        if ((time() - $current) > $gConf['online']) {
            if ((int)$this->getOne ("SELECT COUNT(*) FROM " . TF_FORUM_USER_ACT . " WHERE `user`  = '$user' LIMIT 1"))
                $sql = "UPDATE " . TF_FORUM_USER_ACT . " SET `act_current`='" . time() . "', `act_last` = '$current' WHERE `user` = '$user'";
            else
                $sql = "INSERT INTO " . TF_FORUM_USER_ACT . " (`user`,`act_current`,`act_last`) VALUES ('$user', '" . time() . "', '$current')";
        } else {
            $sql = "UPDATE " . TF_FORUM_USER_ACT . " SET `act_current`='" . time() . "' WHERE `user` = '$user'";
        }

        return $this->query ($sql);
    }

    function updateUserLastActivity ($user)
    {
        global $gConf;

        $t = time();

        $sql = "UPDATE " . TF_FORUM_USER_ACT . " SET `act_current`='$t', `act_last` = '$t' WHERE `user` = '$user'";

        return $this->query ($sql);
    }

    function getUserLastActivity ($user)
    {
        $sql = "SELECT `act_last` FROM " . TF_FORUM_USER_ACT . " WHERE `user`  = '$user' LIMIT 1";
        return (int)$this->getOne ($sql);
    }

    function getUserLastOnlineTime ($user)
    {
        global $gConf;
        return $this->getOne ("SELECT `act_current` AS `act_current` FROM " . TF_FORUM_USER_ACT . " WHERE `user`  = '$user' LIMIT 1");
    }

    function userStatsInc ($user, $when)
    {
        $u = $this->getOne ("SELECT `user` FROM " . TF_FORUM_USER_STAT . " WHERE `user` = '$user'");
        if ($u) {
            $this->query ("UPDATE " . TF_FORUM_USER_STAT . " SET `posts` = `posts` + 1, `user_last_post` = '$when' WHERE `user` = '$user'");
        } else {
            $this->query ("INSERT INTO " . TF_FORUM_USER_STAT . " SET `posts` = 1, `user_last_post` = '$when', `user` = '$user'");
        }
    }

    function userStatsDec ($user)
    {
        $u = $this->getOne ("SELECT `user` FROM " . TF_FORUM_USER_STAT . " WHERE `user` = '$user'");
        if (!$u) return;

        $when = $this->getOne ("SELECT `when` FROM " . TF_FORUM_POST . " WHERE `user` = '$user' ORDER BY `when` DESC LIMIT 1");

        return $this->query ("UPDATE " . TF_FORUM_USER_STAT . " SET `posts` = `posts` - 1, `user_last_post` = '$when' WHERE `user` = '$user'");
    }

    function getUserStat ($u)
    {
        global $gConf;

        return $this->getRow ("SELECT `posts`, `user_last_post` FROM " . TF_FORUM_USER_STAT . " WHERE `user` = '$u'");
    }

    function _cutLivePosts (&$a)
    {
        global $gConf;
        $iNow = $this->getOne("SELECT UNIX_TIMESTAMP()");
        foreach ($a as $k => $v) {
            $a[$k]['post_text'] = orca_mb_substr ($a[$k]['post_text'], 0, $gConf['live_tracker_desc_len']);
            $a[$k]['sec'] = $iNow - $a[$k]['ts'];
        }
    }

    function getLivePosts ($c, $ts)
    {
        global $gConf;

        $where = " `tt`.`topic_hidden` = '0' ";
        $order = 'DESC';
        if ($ts) {
            $where .= " AND tp.`when` > $ts";
            $order = 'ASC';
        }

        $sql = "SELECT tp.`when` AS `ts`, `tp`.`user`, `tp`.`post_id`, `tp`.`post_text`, tt.`topic_id`, tt.`topic_uri`, `tt`.`topic_title`, tf.`forum_id`, tf.`forum_uri`, `tf`.`forum_title`, tc.`cat_id`, tc.`cat_uri`, `tc`.`cat_name` FROM " . TF_FORUM_POST . " AS tp INNER JOIN " . TF_FORUM_TOPIC . " AS tt USING(`topic_id`) INNER JOIN " . TF_FORUM . " AS tf ON (tf.`forum_id` = tp.`forum_id` AND `tt`.`forum_id` = tf.`forum_id` AND tf.`forum_type` = 'public') INNER JOIN " . TF_FORUM_CAT . " AS tc USING(`cat_id`) WHERE $where ORDER BY tp.`when` $order LIMIT $c";

        $a = $this->getAll ($sql);
        $this->_cutLivePosts($a);
        return $a;
    }

    function getNewPostTs ($ts)
    {
        return $this->getOne("SELECT `when` FROM " . TF_FORUM_POST . " WHERE `when` > '$ts' ORDER BY `when` ASC LIMIT 1");
    }

    function getLogActionsCount ($u, $sAction, $iPeriod = 0)
    {
        $sWhere = '';
        if ($iPeriod)
            $sWhere = " AND `action_when` >= (UNIX_TIMESTAMP() - $iPeriod)";

        $sql = "SELECT COUNT(*) FROM " . TF_FORUM_ACTIONS_LOG . " WHERE `user_name` = '" . $this->escape($u) . "' AND `action_name` = '" . $sAction . "' " . $sWhere;
        return $this->getOne($sql);
    }

    function logAction ($id, $u, $action)
    {
        $sql = "INSERT INTO " . TF_FORUM_ACTIONS_LOG . " SET `user_name` = '" . $this->escape($u) . "', `id` = '$id', `action_name` = '" . $action . "', `action_when` = UNIX_TIMESTAMP()";
        return $this->query($sql);
    }

    function updateAttachmentDownloads($hash)
    {
        $sql = "UPDATE " . TF_FORUM_ATTACHMENTS . " SET `att_downloads` = `att_downloads` + 1 WHERE `att_hash` = '$hash' LIMIT 1";
        return $this->query ($sql);
    }

    function getAttachment($hash)
    {
        $sql = "SELECT `a`.`att_hash`, `a`.`att_name`, `a`.`att_type`, `a`.`att_size`, `a`.`att_downloads`, `p`.`forum_id` FROM " . TF_FORUM_ATTACHMENTS . " AS `a` LEFT JOIN " . TF_FORUM_POST . " AS `p` ON (`a`.`post_id` = `p`.`post_id`) WHERE `att_hash` = '$hash' LIMIT 1";
        return $this->getRow ($sql);
    }

    function getAttachments ($post_id)
    {
        $sql = "SELECT `att_hash`, `att_name`, `att_type`, `att_size`, `att_downloads` FROM " . TF_FORUM_ATTACHMENTS . " WHERE `post_id` = '$post_id'";
        return $this->getAll ($sql);
    }

    function insertAttachment ($post_id, $hash, $name, $type, $size)
    {
        return $this->query("INSERT INTO " . TF_FORUM_ATTACHMENTS . " SET `att_hash` = '$hash', `post_id` = '$post_id', `att_name` = '$name', `att_type` = '$type', `att_when` = UNIX_TIMESTAMP(), `att_size` = '$size'");
    }

    function removeAttachments ($post_id)
    {
        $ret = 0;
        $attachments = $this->getAttachments ($post_id);
        foreach ($attachments as $file) {
            $ret += $this->removeAttachment($file['att_hash']);
        }
        return $ret;
    }

    function removeAttachment ($hash)
    {
        global $gConf;
        $ret = $this->query("DELETE FROM " . TF_FORUM_ATTACHMENTS . " WHERE `att_hash` = '$hash'");
        @unlink($gConf['dir']['attachments'] . orca_build_path ($hash) . $hash);
        return $ret;
    }

    function genFileHash ()
    {
        $sChars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        do {
            $s = '';
            srand((double)microtime()*1000000);
            for ($i = 0; $i < 10; $i++) {
                $x = mt_rand(0, strlen($sChars)-1);
                $s .= $sChars{$x};
            }
        } while ($this->getOne("SELECT `post_id` FROM " . TF_FORUM_ATTACHMENTS . " WHERE `att_hash` = '$s' LIMIT 1"));
        return $s;
    }

    function getSignature ($user)
    {
        return $this->getOne ("SELECT `signature` FROM " . TF_FORUM_SIGNATURES . " WHERE `user` = '$user'");
    }

    function updateSignature ($signature, $user)
    {
        return $this->query ("INSERT INTO " . TF_FORUM_SIGNATURES . " SET `user` = '$user', `signature` = '$signature', `when` = UNIX_TIMESTAMP() ON DUPLICATE KEY UPDATE `signature` = '$signature', `when` = UNIX_TIMESTAMP()");
    }

    function isLocked ($topic_id)
    {
        return $this->getOne ("SELECT `topic_locked` FROM " . TF_FORUM_TOPIC . " WHERE `topic_id` = $topic_id LIMIT 1");
    }

    function lock ($topic_id, $user)
    {
        if (!$this->query ("UPDATE " . TF_FORUM_TOPIC . " SET `topic_locked` = IF(`topic_locked`, 0, 1) WHERE `topic_id` = $topic_id LIMIT 1"))
            return false;

        $this->logAction ($topic_id, $user, TF_ACTION_LOCK);

        return true;
    }

    function renameUser ($sUserOld, $sUserNew)
    {
        global $gConf;

        $iAffectedRows = 0;

        $aTables2Field = $this->_aUserTables2Field;

        if ($sUserNew == $gConf['anonymous']) {
            unset($aTables2Field['forum_actions_log']);
            $iAffectedRows += $this->query ("DELETE FROM  `" . $gConf['db']['prefix'] . "forum_flag` WHERE `user` = '" . $this->escape($sUserOld) . "'");
            $iAffectedRows += $this->query ("DELETE FROM  `" . $gConf['db']['prefix'] . "forum_signatures` WHERE `user` = '" . $this->escape($sUserOld) . "'");
            $iAffectedRows += $this->query ("DELETE FROM  `" . $gConf['db']['prefix'] . "forum_user_activity` WHERE `user` = '" . $this->escape($sUserOld) . "'");
            $iAffectedRows += $this->query ("DELETE FROM  `" . $gConf['db']['prefix'] . "forum_user_stat` WHERE `user` = '" . $this->escape($sUserOld) . "'");
            $iAffectedRows += $this->query ("DELETE FROM  `" . $gConf['db']['prefix'] . "forum_vote` WHERE `user` = '" . $this->escape($sUserOld) . "'");
        }

        foreach ($aTables2Field as $sTable => $sField)
            $iAffectedRows += $this->query ("UPDATE `" . $gConf['db']['prefix'] . $sTable . "` SET `" . $sField . "` = '" . $this->escape($sUserNew) . "' WHERE `" . $sField . "` = '" . $this->escape($sUserOld) . "'");
        $iAffectedRows += $this->query ("UPDATE `" . $gConf['db']['prefix'] . "forum_topic` SET `last_post_user` = '" . $this->escape($sUserNew) . "' WHERE `last_post_user` = '" . $this->escape($sUserOld) . "'");

        return $iAffectedRows;
    }

    function deleteUser ($sUser)
    {
        global $gConf;

        $iAffectedRows = 0;
        $iAffectedRowsForumPosts = 0;

        $aTables2Field = $this->_aUserTables2Field;
        
        unset($aTables2Field['forum_topic']);
        unset($aTables2Field['forum_post']);

        foreach ($aTables2Field as $sTable => $sField)
            $iAffectedRows += $this->query ("DELETE FROM `" . $gConf['db']['prefix'] . $sTable . "` WHERE `" . $sField . "` = '" . $this->escape($sUser) . "'");

        $aPosts = $this->getAll("SELECT `post_id` FROM " . TF_FORUM_POST . " WHERE `user` = '" . $this->escape($sUser) . "'");
        foreach ($aPosts as $r)
            $iAffectedRows += $this->deletePost ($r['post_id']);

        return $iAffectedRows;
    }    
}
