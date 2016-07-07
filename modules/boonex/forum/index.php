<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

if (version_compare(phpversion(), "5.3.0", ">=") == 1) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
}

if (isset($_GET['refresh']) && $_GET['refresh']) {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

if (!file_exists('./inc/header.inc.php')) {
    header("Location: install/");
    exit;
}

if ($_GET['orca_integration'] && preg_match('/^[0-9a-z]+$/', $_GET['orca_integration'])) {
    define('BX_ORCA_INTEGRATION', $_GET['orca_integration']);
} else {
    define('BX_ORCA_INTEGRATION', 'dolphin');
}

require_once('./inc/header.inc.php');

$ret = @include_once($gConf['dir']['inc'] . 'util.inc.php');
if (!$ret) {
    echo 'File inclusion failed. <br />Did you properly edit <b>inc/header.inc.php</b> file ?';
    exit;
}

require_once(BX_DIRECTORY_PATH_CLASSES . 'Thing.php');
require_once($gConf['dir']['classes'] . 'ThingPage.php');
require_once($gConf['dir']['classes'] . 'Mistake.php');
require_once($gConf['dir']['classes'] . 'BxXslTransform.php');
require_once($gConf['dir']['classes'] . 'BxDb.php');
require_once($gConf['dir']['classes'] . 'DbForum.php');
require_once($gConf['dir']['classes'] . 'Forum.php');

require_once($gConf['dir']['classes'] . 'DbLogin.php');
require_once($gConf['dir']['classes'] . 'Login.php');

require_once($gConf['dir']['classes'] . 'BxMail.php');

require_once($gConf['dir']['classes'] . 'DbAdmin.php');
require_once($gConf['dir']['classes'] . 'Admin.php');

//checkMagicQuotes ();

require_once($gConf['dir']['base'] . 'integrations/' . BX_ORCA_INTEGRATION . '/class.php'); // override Forum class if needed
require_once($gConf['dir']['base'] . 'integrations/' . BX_ORCA_INTEGRATION . '/design.php'); // include custom header/footer

$action        = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$l             = 'base64_decode';
$_GET['debug'] = isset($_GET['debug']) && $_GET['debug'] ? 1 : 0;
$_GET['trans'] = isset ($_GET['trans']) && $_GET['trans'] ? 1 : 0;

require_once($gConf['dir']['base'] . 'integrations/' . BX_ORCA_INTEGRATION . '/callback.php'); // define custom callback functions

$f->updateCurrentUserActivity();

global $orca_admin;
switch ($action) {
    // admin functions

    case 'compile_langs':
        $orca_admin = new Admin ();
        echo_utf8($orca_admin->compileLangs());
        break;

    case 'edit_categories':
        transCheck($f->getPageXML(0, $_GET), $gConf['dir']['xsl'] . 'edit_categories.xsl', $_GET['trans']);
        break;

    case 'edit_category_del':
        $orca_admin = new Admin ();
        transCheck($orca_admin->deleteCategory((int)$_GET['cat_id']), '', 0);
        break;

    case 'edit_forum_del':
        $orca_admin = new Admin ();
        transCheck($orca_admin->deleteForum((int)$_GET['forum_id']), '', 0);
        break;

    case 'edit_category':
        $orca_admin = new Admin ();
        transCheck($orca_admin->editCategory((int)$_GET['cat_id']), $gConf['dir']['xsl'] . 'edit_cat_form.xsl',
            $_GET['trans']);
        break;

    case 'edit_category_submit':
        $orca_admin = new Admin ();
        transCheck($orca_admin->editCategorySubmit((int)$_GET['cat_id'], $_GET['cat_name'], (int)$_GET['cat_order'],
            (int)$_GET['cat_expanded']), '', 0);
        break;

    case 'edit_forum':
        $orca_admin = new Admin ();
        transCheck($orca_admin->editForum((int)$_GET['forum_id'], $_GET['cat_id']),
            $gConf['dir']['xsl'] . 'edit_forum_form.xsl', $_GET['trans']);
        break;

    case 'edit_forum_submit':
        $orca_admin = new Admin ();
        transCheck($orca_admin->editFormSubmit((int)$_GET['cat_id'], (int)$_GET['forum_id'], $_GET['title'],
            $_GET['desc'], $_GET['type'], (int)$_GET['order']), '', 0);
        break;

    case 'reported_posts':
        $orca_admin = new Admin ();
        transCheck($orca_admin->getReportedPostsXML(false), $gConf['dir']['xsl'] . 'forum_posts.xsl', $_GET['trans']);
        break;

    case 'hidden_posts':
        $orca_admin = new Admin ();
        transCheck($orca_admin->getHiddenPostsXML(false), $gConf['dir']['xsl'] . 'forum_posts.xsl', $_GET['trans']);
        break;

    case 'list_forums_admin':
        transCheck($f->getForumsXML($_GET['cat'], 1), $gConf['dir']['xsl'] . 'edit_cat_forums.xsl', $_GET['trans']);
        break;

    case 'clear_report':
        $orca_admin = new Admin ();
        transCheck($orca_admin->clearReport((int)$_GET['post_id']), '', 0);
        break;

    // login functions are replaced by dolphin login/join functions
    /*
        case 'join_form':
            $orca_login = new Login ();
            transcheck ($orca_login->getJoinForm(), $gConf['dir']['xsl'] . 'join_form.xsl', $_GET['trans']);
            break;
    
        case 'login_form':
            $orca_login = new Login ();
            transcheck ($orca_login->getLoginForm(), $gConf['dir']['xsl'] . 'login_form.xsl', $_GET['trans']);
            break;
    
        case 'join_submit':
            $orca_login = new Login ();
            transCheck ($orca_login->joinSubmit (array('username' => $_GET['username'], 'email' => $_GET['email'])), '', 0);
            break;
    
        case 'login_submit':
            $orca_login = new Login ();
            transCheck ($orca_login->loginSubmit (array('username' => $_GET['username'], 'pwd' => $_GET['pwd'])), '', 0);
            break;
    
        case 'logout':
            transcheck ($f->logout(), '', 0);
            break;
    */

    // user functions

    case 'rss_forum':
        transCheck($f->getRssForum($_GET['forum']), '', 0);
        break;

    case 'rss_topic':
        transCheck($f->getRssTopic($_GET['topic']), '', 0);
        break;

    case 'rss_user':
        transCheck($f->getRssUser($_GET['user'], $_GET['sort']), '', 0);
        break;

    case 'rss_all':
        transCheck($f->getRssAll($_GET['sort']), '', 0);
        break;

    case 'rss_updated_topics':
        transCheck($f->getRssUpdatedTopics(), '', 0);
        break;

    case 'report_post':
        transCheck($f->report((int)$_GET['post_id']), '', 0);
        break;

    case 'flag_topic':
        transCheck($f->flag((int)$_GET['topic_id']), '', 0);
        break;

    case 'vote_post_good':
        transCheck($f->votePost((int)$_GET['post_id'], 1), '', 0);
        break;

    case 'vote_post_bad':
        transCheck($f->votePost((int)$_GET['post_id'], -1), '', 0);
        break;

    case 'get_new_post':
        transCheck($f->getLivePostsXML(1, (int)$_GET['ts']), $gConf['dir']['xsl'] . 'live_tracker_main.xsl',
            $_GET['trans']);
        break;

    case 'is_new_post':
        transCheck($f->isNewPost((int)$_GET['ts']), '', 0);
        break;

    case 'profile':
        transCheck($f->showProfile($_GET['user'], false), $gConf['dir']['xsl'] . 'profile.xsl', $_GET['trans']);
        break;

    case 'show_hidden_topics':
        transCheck($f->getHiddenTopicsXML(false, (int)$_GET['start']), $gConf['dir']['xsl'] . 'hidden_topics.xsl',
            $_GET['trans']);
        break;

    case 'show_my_threads':
        transCheck($f->getMyThreadsXML(false, (int)$_GET['start']), $gConf['dir']['xsl'] . 'my_topics.xsl',
            $_GET['trans']);
        break;

    case 'show_my_flags':
        transCheck($f->getMyFlagsXML(false, (int)$_GET['start']), $gConf['dir']['xsl'] . 'flagged_topics.xsl',
            $_GET['trans']);
        break;

    case 'list_topics':
        transCheck($f->getTopicsXML($_GET['forum'], false, (int)$_GET['start']),
            $gConf['dir']['xsl'] . 'forum_topics.xsl', $_GET['trans']);
        break;

    case 'list_posts':
        transCheck($f->getPostsXML($_GET['topic'], false), $gConf['dir']['xsl'] . 'forum_posts.xsl', $_GET['trans']);
        break;

    case 'show_hidden_post':
        transCheck($f->getHiddenPostXML((int)$_GET['post_id'], 1), $gConf['dir']['xsl'] . 'forum_posts.xsl',
            $_GET['trans']);
        break;

    case 'hide_hidden_post':
        transCheck($f->getHiddenPostXML((int)$_GET['post_id'], 0), $gConf['dir']['xsl'] . 'forum_posts.xsl',
            $_GET['trans']);
        break;

    case 'hide_post':
        transCheck($f->hidePost((int)$_GET['is_hide'], (int)$_GET['post_id']), '', 0);
        break;

    case 'delete_post':
        echo_utf8($f->deletePostXML((int)$_GET['post_id'], (int)$_GET['topic_id'], (int)$_GET['forum_id']));
        break;

    case 'edit_post':
        echo_utf8($f->editPost((int)$_POST['post_id'], $_POST['topic_id'], $_POST['post_text']));
        break;

    case 'edit_post_xml':
        transcheck($f->editPostXml((int)$_GET['post_id'], $_GET['topic_id']), $gConf['dir']['xsl'] . 'edit_post.xsl',
            $_GET['trans']);
        break;

    case 'del_topic':
        transCheck($f->delTopic((int)$_GET['topic_id']), '', 0);
        break;

    case 'move_topic_form':
        transCheck($f->moveTopicForm((int)$_GET['topic_id']), $gConf['dir']['xsl'] . 'move_topic_form.xsl',
            $_GET['trans']);
        break;

    case 'move_topic_submit':
        transCheck($f->moveTopicSubmit((int)$_GET['topic_id'], (int)$_GET['forum_id'], (int)$_GET['old_forum_id'],
            (int)$_GET['goto_new_location']), '', 0);
        break;

    case 'new_topic':
        transCheck($f->getNewTopicXML($_GET['forum']), $gConf['dir']['xsl'] . 'new_topic.xsl', $_GET['trans']);
        break;

    case 'hide_topic':
        transCheck($f->hideTopic((int)$_GET['is_hide'], (int)$_GET['topic_id']), '', 0);
        break;

    case 'stick':
        transCheck($f->stick((int)$_GET['topic_id']), '', 0);
        break;

    case 'lock_topic':
        transCheck($f->lock((int)$_GET['topic_id']), '', 0);
        break;

    case 'reply':
        transCheck($f->getPostReplyXML((int)$_GET['forum'], (int)$_GET['topic']),
            $gConf['dir']['xsl'] . 'post_reply.xsl', $_GET['trans']);
        break;

    case 'show_search':
        transCheck($f->getSearchXML(), $gConf['dir']['xsl'] . 'search_form.xsl', $_GET['trans']);
        break;

    case 'search':
        transCheck($f->getSearchResultsXML($_GET['text'], $_GET['type'], (int)$_GET['forum'], $_GET['u'], $_GET['disp'],
            $_GET['start']), $gConf['dir']['xsl'] . 'search.xsl', $_GET['trans']);
        break;

    case 'post_reply':
        echo_utf8($f->postReplyXML($_POST));
        break;

    case 'post_new_topic':
        echo_utf8($f->postNewTopicXML($_POST));
        break;

    case 'post_success':
        transCheck("<forum><uri>{$_GET['forum']}</uri></forum>", $gConf['dir']['xsl'] . 'default_post_success.xsl',
            $_GET['trans']);
        break;

    case 'access_denied':
        transCheck('<forum_access>no</forum_access>', $gConf['dir']['xsl'] . 'default_access_denied.xsl',
            $_GET['trans']);
        break;

    case 'forum_index':
        transCheck($f->getPageXML(0, $_GET), $gConf['dir']['xsl'] . 'home.xsl', $_GET['trans']);
        break;

    case 'list_forums':
        transCheck($f->getForumsXML($_GET['cat'], 1), $gConf['dir']['xsl'] . 'cat_forums.xsl', $_GET['trans']);
        break;

    case 'live_tracker':
        transCheck($f->liveTracker(), $gConf['dir']['xsl'] . 'live_tracker_main.xsl', $_GET['debug'] ? 0 : 1);
        break;

    case 'download':
        $f->download($_GET['hash']);
        break;

    case 'recent_topics':
        transCheck($f->getRecentTopicsXML(false, (int)$_GET['start']), $gConf['dir']['xsl'] . 'recent_topics.xsl',
            $_GET['trans']);
        break;

    default:
        $isMarker = true;
        if (!isset($_GET['start'])) {
            $o = new BxDolOrcaForumsHome();
            $s = $o->getCode();
            $isMarker = false !== strpos($s, $o->sMarker);
            list($GLOBALS['glBeforeContent'], $GLOBALS['glAfterContent']) = explode($o->sMarker, $s);
        }
        if ($isMarker) {
            $sXml = $f->getRecentTopicsXML(true, (int)$_GET['start']);
        } else {
            $li = $f->_getLoginInfo ();
            $sXml = $f->addHeaderFooter ($li, "");
        }
        transCheck($sXml, $gConf['dir']['xsl'] . 'recent_topics_main.xsl', $_GET['debug'] ? 0 : 1);
        break;

    case 'goto':
        switch (true) {
            // user functions
            case (isset($_GET['cat_id'])):
                $_GET['cat'] = $_GET['cat_id'];
                $xsl         = 'home_main.xsl';
                transCheck($f->getPageXML(1, $_GET), $gConf['dir']['xsl'] . $xsl, $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['new_topic'])):
                transCheck($f->getNewTopicXML($_GET['new_topic'], true), $gConf['dir']['xsl'] . 'new_topic_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['forum_id'])):
                transCheck($f->getTopicsXML($_GET['forum_id'], true, (int)$_GET['start']),
                    $gConf['dir']['xsl'] . 'forum_topics_main.xsl', $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['topic_id'])):
                transCheck($f->getPostsXML($_GET['topic_id'], true), $gConf['dir']['xsl'] . 'forum_posts_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['user'])):
                transCheck($f->showProfile($_GET['user'], true), $gConf['dir']['xsl'] . 'profile_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['search'])):
                transCheck($f->getSearchXML(true), $gConf['dir']['xsl'] . 'search_form_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['search_result'])):
                transCheck($f->getSearchResultsXML($_GET['text'], $_GET['type'], (int)$_GET['forum'], $_GET['u'],
                    $_GET['disp'], $_GET['start'], true), $gConf['dir']['xsl'] . 'search_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['recent_topics'])):
                transCheck($f->getRecentTopicsXML(true, (int)$_GET['start']),
                    $gConf['dir']['xsl'] . 'recent_topics_main.xsl', $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['my_flags'])):
                transCheck($f->getMyFlagsXML(true, (int)$_GET['start']),
                    $gConf['dir']['xsl'] . 'flagged_topics_main.xsl', $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['my_threads'])):
                transCheck($f->getMyThreadsXML(true, (int)$_GET['start']), $gConf['dir']['xsl'] . 'my_topics_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['hidden_topics'])):
                transCheck($f->getHiddenTopicsXML(true, (int)$_GET['start']),
                    $gConf['dir']['xsl'] . 'hidden_topics_main.xsl', $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['hidden_posts'])):
                $orca_admin = new Admin ();
                transCheck($orca_admin->getHiddenPostsXML(true), $gConf['dir']['xsl'] . 'forum_posts_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['manage_forum'])):
                transCheck($f->getPageXML(1, $_GET), $gConf['dir']['xsl'] . 'edit_categories_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['reported_posts'])):
                $orca_admin = new Admin ();
                transCheck($orca_admin->getReportedPostsXML(true), $gConf['dir']['xsl'] . 'forum_posts_main.xsl',
                    $_GET['debug'] ? 0 : 1);
                break;
            case (isset($_GET['index'])):
                $o = new BxDolOrcaForumsIndex();
                $s = $o->getCode();
                list($GLOBALS['glBeforeContent'], $GLOBALS['glAfterContent']) = explode($o->sMarker, $s);
                transCheck($f->getPageXML(1, $_GET), $gConf['dir']['xsl'] . 'home_main.xsl', $_GET['debug'] ? 0 : 1);
                break;
        }
        break;

}
