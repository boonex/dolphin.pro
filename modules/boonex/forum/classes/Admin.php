<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

// admin functions

class Admin extends ThingPage
{
    /**
     * constructor
     */
    function __construct ()
    {
        global $f;
        $this->_admin = $f->isAdmin ();
    }

    /**
     * delete category
     *	@param $cat_id	category id
     *	@param return	xml (<ret>0</ret>|<ret>1</ret>)
     */
    function deleteCategory ($cat_id)
    {
        if (!$this->_admin || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
            return '<ret>0</ret>';

        $db = new DbAdmin ();
        return $db->deleteCategoryAll ((int)$cat_id) ? '<ret>1</ret>' : '<ret>0</ret>';
    }

    /**
     * delete forum
     *	@param $forum_id	forum id
     *	@param return		xml (<ret>0</ret>|<ret>1</ret>)
     */
    function deleteForum ($forum_id)
    {
        if (!$this->_admin || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
            return '<ret>0</ret>';

        $db = new DbAdmin ();

        $cat = $db->getCatByForumId($forum_id);

        if ($db->deleteForumAll ((int)$forum_id))
            return '<root><cat_uri>' . $cat['cat_uri'] . '</cat_uri><cat_id>' . $cat['cat_id'] . '</cat_id></root>';
        else
            return '<root><cat_id>0</cat_id></root>';
    }

    /**
     * show edit category page
     *	@param $cat_id		category id
     *	@param return		category window xml
     */
    function editCategory ($cat_id)
    {
        $db = new DbForum ();
        $a = $db->getCat ((int)$cat_id);

        $cu = $this->getUrlsXml ();

        encode_post_text ($a['cat_name']);

        return <<<EOS
<root>
$cu
<cat cat_id="$cat_id">
    <cat_name>{$a['cat_name']}</cat_name>
    <cat_order>{$a['cat_order']}</cat_order>
    <cat_expanded>{$a['cat_expanded']}</cat_expanded>
</cat>
</root>
EOS;
    }

    /**
     * save category information
     *	@param $cat_id		category id
     *	@param $cat_name	category name
     *	@param return		xml (<ret>0</ret>|<ret>1</ret>)
     */
    function editCategorySubmit ($cat_id, $cat_name, $cat_order, $cat_expanded)
    {
        if (!$this->_admin || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
            return '<ret>0</ret>';

        $cat_name = unicode_urldecode($cat_name);
        prepare_to_db($cat_name, 0);

        // cat_name check

        $db = new DbAdmin ();
        if ($cat_id) {
            return $db->editCategory ((int)$cat_id, $cat_name, (int)$cat_order, $cat_expanded ? 1 : 0) ? '<ret>1</ret>' : '<ret>0</ret>';
        } else {
            global $f;
            $cat_uri = $f->uriGenerate ($cat_name, TF_FORUM_CAT, 'cat_uri');
            return $db->insertCategory ($cat_name, $cat_uri, (int)$cat_order, $cat_expanded ? 1 : 0) ? '<ret>1</ret>' : '<ret>0</ret>';
        }
    }

    /**
     * show edit forum page
     *	@param $forum_id	forum id
     *	@param $cat_id		category id
     *	@param return		forum edit window xml
     */
    function editForum ($forum_id, $cat_id)
    {
        $db = new DbAdmin ();
        $fdb = new DbForum ();

        if ($forum_id)
            $a = $db->getForum ((int)$forum_id);
        else
            $a['cat_id'] = $cat_id;

        $c = $fdb->getCat ($a['cat_id']);
        $a['cat_uri'] = $c['cat_uri'];

        $cu = $this->getUrlsXml ();

        encode_post_text ($a['forum_title']);
        encode_post_text ($a['forum_desc']);

        return <<<OES
<root>
$cu
<forum forum_id="$forum_id">
    <cat_id>{$a['cat_id']}</cat_id>
    <cat_uri>{$a['cat_uri']}</cat_uri>
    <title>{$a['forum_title']}</title>
    <desc>{$a['forum_desc']}</desc>
    <type>{$a['forum_type']}</type>
    <order>{$a['forum_order']}</order>
</forum>
</root>
OES;
    }

    /**
     * save forum information
     *	@param $cat_id		category id
     *	@param $forum_id	forum id
     *	@param $title		forum title
     *	@param $desc		forum description
     *	@param $type		forum type (public|private)
     *	@param return		xml (<ret>0</ret>|<ret>1</ret>)
     */
    function editFormSubmit ($cat_id, $forum_id, $title, $desc, $type, $order)
    {
        if (!$this->_admin || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
            return '<ret>0</ret>';

        $title = unicode_urldecode ($title);
        $desc = unicode_urldecode ($desc);

        prepare_to_db($title, 0);
        prepare_to_db($desc, 0);
        prepare_to_db($type, 0);

        $db = new DbAdmin ();

        if ($forum_id > 0) {
            return $db->editForum ((int)$forum_id, $title, $desc, $type, (int)$order) ? '<ret>1</ret>' : '<ret>0</ret>';
        } else {
            global $f;
            $forum_uri = $f->uriGenerate ($title, TF_FORUM, 'forum_uri');
            return $db->insertForum ((int)$cat_id, $title, $desc, $type, $forum_uri, (int)$order) ? '<ret>1</ret>' : '<ret>0</ret>';
        }

    }

    /**
     * returns reported posts XML
     */
    function getHiddenPostsXML ($wp)
    {
        return $this->getXxxPostsXML ($wp, 'getHiddenPosts', '[L[Hidden Posts]]');
    }

    /**
     * returns reported posts XML
     */
    function getReportedPostsXML ($wp)
    {
        return $this->getXxxPostsXML ($wp, 'getReportedPosts', '[L[Reported Posts]]', '<allow_clear_report>1</allow_clear_report>');
    }

    function getXxxPostsXML ($wp, $sDbFunc, $sTitle, $sAddXml = '')
    {
        global $gConf;
        global $f;

        $ui = array ();

        $fdb = new DbForum ();
        $adb = new DbAdmin ();

        if (!$this->_admin) {
            if ($wp) {
                $GLOBALS['f']->setTitle ('<![CDATA[' . $sTitle . ']]>');
                $li = $GLOBALS['f']->_getLoginInfo ($u);
                return $this->addHeaderFooter ($li, "<posts></posts>");
            } else {
                return "<root><posts></posts></root>";
            }
        }

        // check user permissions to delete or edit posts
        $gl_allow_edit = 1;
        $gl_allow_del = 1;
        $gl_allow_hide_posts = 1;
        $gl_allow_unhide_posts = 1;
        $gl_allow_clear_report = 1;
        $gl_allow_download = 1;

        $u = $f->_getLoginUser();

        $a = $adb->$sDbFunc($u);
        $p = '';
        foreach ($a as $r) {
            // acquire user info
            if (!isset($ui[$r['user']]) && ($aa = $f->_getUserInfoReadyArray ($r['user'])))
                $ui[$r['user']] = $aa;

            $allow_edit = $gl_allow_edit;
            $allow_del = $gl_allow_del;

            $files = $GLOBALS['f']->_getAttachmentsXML ($r['post_id']);

            encode_post_text ($r['post_text']);

            $r['when'] = orca_format_date($r['when']);

            $p .= <<<EOF
<post id="{$r['post_id']}"  force_show="1">
    <text>{$r['post_text']}</text>
    <when>{$r['when']}</when>
    <allow_edit>$allow_edit</allow_edit>
    <allow_del>$allow_del</allow_del>
    <allow_hide_posts>$gl_allow_hide_posts</allow_hide_posts>
    <allow_unhide_posts>$gl_allow_unhide_posts</allow_unhide_posts>
    <allow_download>$gl_allow_download</allow_download>
    $sAddXml
    <points>{$r['votes']}</points>
    <hidden>{$r['hidden']}</hidden>
    <vote_user_point>{$r['vote_user_point']}</vote_user_point>
    <user posts="{$ui[$r['user']]['posts']}" name="{$r['user']}">
        <avatar>{$ui[$r['user']]['avatar']}</avatar>
        <url>{$ui[$r['user']]['url']}</url>
        <title>{$ui[$r['user']]['title']}</title>
        <onclick>{$ui[$r['user']]['onclick']}</onclick>
        <role>{$ui[$r['user']]['role']}</role>
    </user>
    <attachments>$files</attachments>
    <min_point>{$gConf['min_point']}</min_point>
</post>
EOF;
            $rr = $r;

        }

        if ($wp) {
            $GLOBALS['f']->setTitle ('<![CDATA[' . $sTitle . ']]>');
            $li = $GLOBALS['f']->_getLoginInfo ($u);
            return $this->addHeaderFooter ($li, "<posts><topic><title>$sTitle</title><id>0</id></topic><forum><id>0</id></forum>{$p}</posts>");
        } else {
            $cu = $this->getUrlsXml ();
            return "<root>$cu<posts><topic><title>$sTitle</title><id>0</id></topic><forum><id>0</id></forum>{$p}</posts></root>";
        }

    }

    function compileLangs ()
    {
        global $gConf;

        if (!$this->_admin || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            return '<ret>0</ret>';
        }

        require_once( './classes/BxLang.php' );
        require_once( $gConf['dir']['xml'].'lang.php' );

        $sLang = isset($_GET['lang']) && preg_match("/^[a-z]{2}$/", $_GET['lang']) ? $_GET['lang'] : $gConf['lang'];
        $l = new BxLang ($sLang, $gConf['skin']);
        $l->setVisualProcessing(0);
        if ($l->compile ())
            return '<ret>1</ret>';
        return '<ret>0</ret>';
    }

    function clearReport ($post_id)
    {
        if (!$post_id || !$this->_admin || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
             return '<ret>0</ret>';

        $db = new DbAdmin ();
        if (!$db->clearReport ((int)$post_id))
            return '<ret>0</ret>';

        return '<ret>1</ret>';
    }
}
