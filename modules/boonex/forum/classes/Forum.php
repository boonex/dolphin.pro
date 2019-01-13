<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

// forum operations

class Forum extends ThingPage
{
    // mandatory methods
    var $getUserInfo; // $stringUser
    var $getUserPerm; // $stringUser, $stringType (public, provate, own), $stringAction (read, post, edit, del, search, sticky), $intForumId
    var $getLoginUser; // no parameters

    // optional methods
    var $onPostReply; // $arrayTopic, $stringPostText, $stringUser
    var $onPostEdit; // $arrayTopic, $intPostId, $stringPostText, $stringUser
    var $onPostDelete; // $arrayTopic, $intPostId, $stringUser
    var $onNewTopic; // $intForumId, $stringTopicSubject, $stringTopicText, $isTopicSticky, $stringUser, $stringTopicUri
    var $onVote; // $intPostId, $stringUser, $intVote (1 or -1)
    var $onReport; // $intPostId, $stringUser
    var $onFlag; // $intTopicId, $stringUser
    var $onUnflag; // $intTopicId, $stringUser

    /**
     * constructor
     */
    function __construct ()
    {
        $this->fdb = new DbForum ();
    }

    /**
     * returns search results XML
     * @param $text		search string
     * @param $type		search type: msgs - messages | tlts - titles
     * @param $forum	forum id to search within
     * @param $u		search posts of this user only
     * @param $disp		display: topics | posts
     * @param $start	for pagination
     */
    function getSearchResultsXML ($text, $type, $forum, $u, $disp, $start = 0, $isWholePage = false)
    {
        global $gConf;

        if (!$this->_checkUserPerm ('', '', 'search')) {
            return $this->_no_access();
        }

        $num = 0;
        switch ($type) {
            case 'msgs':
            case 'tlts':
                $a = $this->fdb->searchMessages (filter_to_db($text), filter_to_db($u), $forum, $type, ('posts' == $disp ? 1 : 0), $start, $num);
                break;
            default:
                return '<error>[L[Wrong search type]]</error>';
        }

        $ws = preg_split("/\s+/", $text);

        $ui = array();
        $s = '';
        switch ($type) {
            case 'tlts':
                foreach ($a as $r) {

                    if (!$this->_checkUserPerm ('', $r['forum_type'], 'read', $r['forum_id']))
                        continue;

                    encode_post_text($r['cat_name']);
                    encode_post_text($r['forum_title']);
                    encode_post_text($r['topic_title'], true);

                    // search hightlight
                    if ($text) {
                        foreach ($ws as $w) {
                            if ($w) {
                                $wreg = preg_quote($w, '/');
                                $r['topic_title'] = preg_replace ("/($wreg)/i", "<span style=\"background-color:yellow\">$w</span>", $r['topic_title']);
                            }
                        }
                    }

                    // acquire user info
                    if (!isset($ui[$r['user']]) && ($aa = $this->_getUserInfoReadyArray ($r['user'])))
                        $ui[$r['user']] = $aa;

                    $r['date'] = bx_html_attribute(orca_format_date($r['date']));

                    $s .= <<<EOF
                    <sr date="{$r['date']}">
                        <c id="{$r['cat_id']}" uri="{$r['cat_uri']}">{$r['cat_name']}</c>
                        <f id="{$r['forum_id']}" uri="{$r['forum_uri']}">{$r['forum_title']}</f>
                        <t id="{$r['topic_id']}" uri="{$r['topic_uri']}">{$r['topic_title']}</t>
                        <u>
                            <avatar>{$ui[$r['user']]['avatar']}</avatar>
                            <avatar_medium>{$ui[$r['user']]['avatar64']}</avatar_medium>
                            <profile>{$ui[$r['user']]['url']}</profile>
                            <profile_title>{$ui[$r['user']]['title']}</profile_title>
                            <onclick>{$ui[$r['user']]['onclick']}</onclick>
                            <role>{$ui[$r['user']]['role']}</role>
                        </u>
                    </sr>
EOF;
                }
                break;
            case 'msgs':
                foreach ($a as $r) {

                    if (!$this->_checkUserPerm ('', $r['forum_type'], 'read', $r['forum_id']))
                        continue;

                    // search hightlight
                    if ($text) {
                        foreach ($ws as $w) {
                            if ($w) {
                                $wreg = preg_quote($w, '/');
                                $ind = preg_match( "/([^>]*<)/i", $r['post_text'], $ind ); // html tags?
                                if ($ind)
                                    $r['post_text'] = preg_replace("/($wreg)(?=[^>]*<)/i", "<span style=\"background-color:yellow\">$w</span>", "<div>{$r['post_text']}</div>");
                                else
                                    $r['post_text'] = preg_replace("/($wreg)/i", "<span style=\"background-color:yellow\">$w</span>", $r['post_text']);
                            }
                        }
                    }

                    encode_post_text ($r['post_text']);
                    encode_post_text($r['cat_name']);
                    encode_post_text($r['forum_title']);
                    encode_post_text($r['topic_title'], true);

                    if ($text) {
                        foreach ($ws as $w) {
                            $wreg = preg_quote($w, '/');
                            $r['topic_title'] = preg_replace ("/($wreg)/i", "<span style=\"background-color:yellow\">$w</span>", $r['topic_title']);
                        }
                    }

                    // acquire user info
                    if (!isset($ui[$r['user']]) && ($aa = $this->_getUserInfoReadyArray ($r['user'])))
                        $ui[$r['user']] = $aa;

                    $r['date'] = bx_html_attribute(orca_format_date($r['date']));

                    $s .= <<<EOF
                    <sr date="{$r['date']}">
                        <c id="{$r['cat_id']}" uri="{$r['cat_uri']}">{$r['cat_name']}</c>
                        <f id="{$r['forum_id']}" uri="{$r['forum_uri']}">{$r['forum_title']}</f>
                        <t id="{$r['topic_id']}" uri="{$r['topic_uri']}">{$r['topic_title']}</t>
                        <p id="{$r['post_id']}">{$r['post_text']}</p>
                        <u name="{$r['user']}">
                            <avatar>{$ui[$r['user']]['avatar']}</avatar>
                            <avatar_medium>{$ui[$r['user']]['avatar64']}</avatar_medium>
                            <profile>{$ui[$r['user']]['url']}</profile>
                            <profile_title>{$ui[$r['user']]['title']}</profile_title>
                            <onclick>{$ui[$r['user']]['onclick']}</onclick>
                            <role>{$ui[$r['user']]['role']}</role>
                        </u>
                    </sr>
EOF;
                }
                break;
        }

        $p = $this->_getPages ($start, $num, $gConf['topics_per_page']);

        $textEncoded = bx_js_string($text, BX_ESCAPE_STR_APOS);
        $userEncoded = bx_js_string($u, BX_ESCAPE_STR_APOS);

        $sp = <<<EOS
<search_text><![CDATA[$text]]></search_text>
<search_params>
    <text><![CDATA[$textEncoded]]></text>
    <type>$type</type>
    <forum>$forum</forum>
    <user><![CDATA[$userEncoded]]></user>
    <disp>$disp</disp>
</search_params>
EOS;
        $cu = $this->getUrlsXml ();
        encode_post_text($text);

        if ($isWholePage) {
            $this->setTitle ('<![CDATA[[L[Search Results For:]]' . $text . ']]>');
            $li = $this->_getLoginInfo ($u);
            return $this->addHeaderFooter ($li, "<search>{$sp}<pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>$s</search>");
        } else {
            return "<root>$cu<search>{$sp}<pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>$s</search></root>";
        }
    }

    /**
     * returns search  page XML
     */
    function getSearchXML ($wp = false)
    {
        if (!$this->_checkUserPerm ('', '', 'search')) {
            return $this->_no_access($wp);
        }

        $s = $this->getCategsShortXML('read');

        $cu = $this->getUrlsXml ();

        if ($wp) {
            $this->setTitle ('<![CDATA[[L[Search The Forum]]]]>');
            $li = $this->_getLoginInfo ($u);
            return $this->addHeaderFooter ($li, "<search>$s</search>");
        } else {
            return "<root>$cu<search>$s</search></root>";
        }
    }

    /**
     * returns new topic page XML
     */
    function getNewTopicXML ($forum_uri, $isWholePage = false)
    {
        $f = $this->fdb->getForumByUri (filter_to_db($forum_uri));
        $forum_id = isset($f['forum_id']) ? $f['forum_id'] : 0;

        if (!$this->_checkUserPerm ('', isset($f['forum_type']) ? $f['forum_type'] : 'public', 'post', $forum_id)) {
            return $this->_no_access();
        }

        $sticky = 0;
        if ($this->_checkUserPerm ('', '', 'sticky', $forum_id)) {
            $sticky = 1;
        }

        encode_post_text ($f['forum_title']);
        encode_post_text ($f['forum_desc']);

        $x1 = <<<EOF
<forum>
    <id>{$f['forum_id']}</id>
    <uri>{$f['forum_uri']}</uri>
    <title>{$f['forum_title']}</title>
    <desc>{$f['forum_desc']}</desc>
    <type>{$f['forum_type']}</type>
</forum>
EOF;

        $cat = $this->fdb->getCat ($f['cat_id']);
        encode_post_text ($cat['cat_name']);
        $x2 = <<<EOF
<cat>
    <id>{$f['cat_id']}</id>
    <uri>{$cat['cat_uri']}</uri>
    <title>{$cat['cat_name']}</title>
</cat>
EOF;

        $x3 = $this->getCategsShortXML('post');

        $cu = $this->getUrlsXml ();

        $u = $this->_getLoginUser();
        $signature = $this->fdb->getSignature ($u);
        encode_post_text ($signature);

        if ($isWholePage) {
            $this->setTitle ('<![CDATA[[L[New Topic]]]]>');
            $li = $this->_getLoginInfo ($u);
            return $this->addHeaderFooter ($li, "<new_topic sticky=\"$sticky\">{$x2}{$x1}{$x3}<signature>{$signature}</signature></new_topic>");
        } else {
            return "<root>$cu<new_topic sticky=\"$sticky\">{$x2}{$x1}{$x3}<signature>{$signature}</signature></new_topic></root>";
        }
    }

    /**
     * returns post reply page XML
     */
    function getPostReplyXML ($forum_id, $topic_id)
    {
        $f = $this->fdb->getForum ($forum_id);

        $t = $this->fdb->getTopic ((int)$topic_id);

        if (!$this->_checkUserPerm ('', $f['forum_type'], 'post', (int)$forum_id) || $t['topic_locked']) {
            return $this->_no_access();
        }

        encode_post_text ($f['forum_title']);
        encode_post_text ($f['forum_desc']);

        $x1 = <<<EOF
<forum>
    <id>{$f['forum_id']}</id>
    <uri>{$f['forum_uri']}</uri>
    <title>{$f['forum_title']}</title>
    <desc>{$f['forum_desc']}</desc>
    <type>{$f['forum_type']}</type>
</forum>
EOF;

        $u = $this->_getLoginUser();
        $signature = $this->fdb->getSignature ($u);
        encode_post_text ($signature);

        $cu = $this->getUrlsXml ();
        return "<root>$cu<new_topic>$x1<topic><id>$topic_id</id></topic><signature>{$signature}</signature></new_topic></root>";
    }

    /**
     * returns single post XML
     * @param $post_id		post id
     * @param $force_show	force show hidden post
     */
    function getHiddenPostXML ($post_id, $force_show)
    {
        global $gConf;

        $post_id = (int)$post_id;
        if (!$post_id) return false;

        $ui = array ();

        $t = $this->fdb->getTopicByPostId ($post_id);
        $topic_id = $t['topic_id'];

        $f = $this->fdb->getForum ($t['forum_id']);
        $forum_id = $f['forum_id'];

        // check user permission to read this topic posts

        $forum_type = $f['forum_type'];

        if (!$this->_checkUserPerm ('', $forum_type, 'read', $forum_id)) {
            return $this->_no_access();
        }

        // check user permissions to delete or edit posts
        $gl_allow_edit = 0;
        $gl_allow_del = 0;
        $gl_allow_hide_posts = 0;
        $gl_allow_unhide_posts = 0;
        $gl_allow_download = 0;

        if ($this->_checkUserPerm ('', $forum_type, 'edit', $forum_id))
            $gl_allow_edit = 1;

        if ($this->_checkUserPerm ('', $forum_type, 'del', $forum_id))
            $gl_allow_del = 1;

        if ($this->_checkUserPerm ('', '', 'hide_posts', $forum_id))
            $gl_allow_hide_posts = 1;

        if ($this->_checkUserPerm ('', '', 'unhide_posts', $forum_id))
            $gl_allow_unhide_posts = 1;

        if ($this->_checkUserPerm ('', '', 'download', $forum_id));
            $gl_allow_download = 1;

        $u = $this->_getLoginUser();

        $r = $this->fdb->getPost($post_id, $u);

        // acquire user info
        if (!isset($ui[$r['user']]) && ($aa = $this->_getUserInfoReadyArray ($r['user']))) {
            $ui[$r['user']] = $aa;
            $ui[$r['user']]['posts'] = (int)$this->fdb->getUserPosts($r['user']);
        }

        $allow_edit = $gl_allow_edit;
        $allow_del = $gl_allow_del;

        if (!$allow_edit && $r['user'] == $this->_getLoginUserName()) {
            if ($this->_checkUserPerm ($r['user'], 'own', 'edit', $forum_id) && !$this->_isEditTimeout($r['post_id']) && !$t['topic_locked'])
                $allow_edit = 1;
        }

        if (!$allow_del && $r['user'] == $this->_getLoginUserName()) {
            if ($this->_checkUserPerm ($r['user'], 'own', 'del', $forum_id) && !$this->_isEditTimeout($r['post_id']) && !$t['topic_locked'])
                $allow_del = 1;
        }

        $cu = $this->getUrlsXml ();
        $li = "<logininfo>" . array2xml($this->_getLoginInfo ($u)) . "</logininfo>";

        $files = $this->_getAttachmentsXML ($r['post_id']);

        encode_post_text ($r['post_text'], false, true);

        $r['when'] = orca_format_date($r['when']);

        return <<<EOF
<root>
$cu
$li
<forum>
    <id>{$f['forum_id']}</id>
    <uri>{$f['forum_uri']}</uri>
</forum>
<topic>
    <id>$topic_id</id>
    <uri>{$t['topic_uri']}</uri>
</topic>
<post id="{$r['post_id']}" force_show="$force_show">
    <text>{$r['post_text']}</text>
    <when>{$r['when']}</when>
    <allow_edit>$allow_edit</allow_edit>
    <allow_del>$allow_del</allow_del>
    <allow_hide_posts>$gl_allow_hide_posts</allow_hide_posts>
    <allow_unhide_posts>$gl_allow_unhide_posts</allow_unhide_posts>
    <allow_download>$gl_allow_download</allow_download>
    <points>{$r['votes']}</points>
    <hidden>{$r['hidden']}</hidden>
    <vote_user_point>{$r['vote_user_point']}</vote_user_point>
    <user posts="{$ui[$r['user']]['posts']}" name="{$r['user']}">
        <avatar>{$ui[$r['user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$r['user']]['avatar64']}</avatar_medium>
        <url>{$ui[$r['user']]['url']}</url>
        <title>{$ui[$r['user']]['title']}</title>
        <onclick>{$ui[$r['user']]['onclick']}</onclick>
        <role>{$ui[$r['user']]['role']}</role>
    </user>
    <attachments>$files</attachments>
    <min_point>{$gConf['min_point']}</min_point>
</post>
</root>
EOF;
    }

    /**
     * returns topic posts XML
     * @param $topic_id
     * @param $wp			return whole page XML
     */
    function getPostsXML ($topic_uri, $wp)
    {
        global $gConf;

        $ui = array ();

        $u = $this->_getLoginUser();
        $a = $this->fdb->getPostsByUri(filter_to_db($topic_uri), $u);
        $topic_id = $a[0]['topic_id'];

        // check user permission to read this topic posts
        $f = $this->fdb->getForum ($a[0]['forum_id']);
        $forum_id = $f['forum_id'];
        $forum_type = $f['forum_type'];

        if (!$this->_checkUserPerm ($u, $forum_type, 'read', $forum_id)) {
            return $this->_no_access($wp);
        }

        $t = $this->fdb->getTopic ($topic_id);

        $gl_allow_hide_topics = 0;
        $gl_allow_unhide_topics = 0;
        $gl_allow_lock_topics = 0;
        $gl_allow_stick_topics = 0;

        if ($this->_checkUserPerm ($u, '', 'hide_topics', $forum_id))
            $gl_allow_hide_topics = 1;

        if ($this->_checkUserPerm ($u, '', 'unhide_topics', $forum_id))
            $gl_allow_unhide_topics = 1;

        if (!$gl_allow_unhide_topics && !$gl_allow_hide_topics && $t['topic_hidden'])
            return $this->_no_access($wp);

        if ($this->_checkUserPerm ($u, '', 'lock', $forum_id))
            $gl_allow_lock_topics = 1;

        if ($this->_checkUserPerm ($u, '', 'sticky', $forum_id))
            $gl_allow_stick_topics = 1;

        $this->setTrackTopic ($topic_id);

        // check user permissions to delete or edit posts
        $gl_allow_edit = 0;
        $gl_allow_del = 0;
        $gl_allow_hide_posts = 0;
        $gl_allow_unhide_posts = 0;

        $gl_allow_move_topics = 0;
        $gl_allow_del_topics = 0;
        $gl_allow_download = 0;

        if ($this->_checkUserPerm ($u, $forum_type, 'edit', $forum_id))
            $gl_allow_edit = 1;

        if ($this->_checkUserPerm ($u, $forum_type, 'del', $forum_id))
            $gl_allow_del = 1;

        if ($this->_checkUserPerm ($u, '', 'hide_posts', $forum_id))
            $gl_allow_hide_posts = 1;

        if ($this->_checkUserPerm ('', '', 'unhide_posts', $forum_id))
            $gl_allow_unhide_posts = 1;

        if ($this->_checkUserPerm ($u, '', 'move_topics', $forum_id))
            $gl_allow_move_topics = 1;

        if ($this->_checkUserPerm ($u, '', 'del_topics', $forum_id))
            $gl_allow_del_topics = 1;

        if ($this->_checkUserPerm ('', '', 'download', $forum_id));
            $gl_allow_download = 1;

        $p = '';
        foreach ($a as $r) {

            // acquire user info
            if (!$ui[$r['user']] && ($aa = $this->_getUserInfoReadyArray ($r['user']))) {
                $signature = $this->fdb->getSignature ($r['user']);
                encode_post_text ($signature);            
                $ui[$r['user']] = $aa;
                $ui[$r['user']]['signature'] = $signature;
                $ui[$r['user']]['posts'] = (int)$this->fdb->getUserPosts($r['user']);
            }

            $allow_edit = $gl_allow_edit;
            $allow_del = $gl_allow_del;

            if (!$allow_edit && $r['user'] == $u) {
                if ($this->_checkUserPerm ($r['user'], 'own', 'edit', $forum_id) && !$this->_isEditTimeout($r['post_id']) && !$t['topic_locked'])
                    $allow_edit = 1;
            }

            if (!$allow_del && $r['user'] == $u) {
                if ($this->_checkUserPerm ($r['user'], 'own', 'del', $forum_id) && !$this->_isEditTimeout($r['post_id']) && !$t['topic_locked'])
                    $allow_del = 1;
            }

            $files = $this->_getAttachmentsXML ($r['post_id']);

            encode_post_text ($r['post_text'], false, true);

            $r['when'] = orca_format_date($r['when']);

            $p .= <<<EOF
<post id="{$r['post_id']}"  force_show="0">
    <text>{$r['post_text']}</text>
    <when>{$r['when']}</when>
    <allow_edit>$allow_edit</allow_edit>
    <allow_del>$allow_del</allow_del>
    <allow_hide_posts>$gl_allow_hide_posts</allow_hide_posts>
    <allow_unhide_posts>$gl_allow_unhide_posts</allow_unhide_posts>
    <allow_download>$gl_allow_download</allow_download>
    <points>{$r['votes']}</points>
    <hidden>{$r['hidden']}</hidden>
    <vote_user_point>{$r['vote_user_point']}</vote_user_point>
    <user posts="{$ui[$r['user']]['posts']}" name="{$r['user']}">
        <avatar>{$ui[$r['user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$r['user']]['avatar64']}</avatar_medium>
        <url>{$ui[$r['user']]['url']}</url>
        <title>{$ui[$r['user']]['title']}</title>
        <onclick>{$ui[$r['user']]['onclick']}</onclick>
        <role>{$ui[$r['user']]['role']}</role>
        <signature>{$ui[$r['user']]['signature']}</signature>
    </user>
    <attachments>$files</attachments>
    <min_point>{$gConf['min_point']}</min_point>
</post>
EOF;
            $rr = $r;
        }

        $cat = $this->fdb->getCat ($f['cat_id']);
        encode_post_text ($cat['cat_name']);
        $x0 = <<<EOF
<cat>
    <id>{$cat['cat_id']}</id>
    <uri>{$cat['cat_uri']}</uri>
    <title>{$cat['cat_name']}</title>
</cat>
EOF;

        encode_post_text ($t['forum_title']);
        encode_post_text ($t['forum_desc']);
        $x1 = <<<EOF
<forum>
    <id>{$f['forum_id']}</id>
    <uri>{$f['forum_uri']}</uri>
    <title>{$t['forum_title']}</title>
    <desc>{$t['forum_desc']}</desc>
    <type>{$f['forum_type']}</type>
</forum>
EOF;
        encode_post_text ($t['topic_title'], true);
        $topic_flagged = $this->fdb->isFlagged ($rr['topic_id'], $u) ? 1 : 0;
        $x2 = <<<EOF
<topic>
    <id>{$t['topic_id']}</id>
    <uri>{$t['topic_uri']}</uri>
    <title>{$t['topic_title']}</title>
    <locked>{$t['topic_locked']}</locked>
    <sticky>{$t['topic_sticky']}</sticky>
    <hidden>{$t['topic_hidden']}</hidden>
    <flagged>{$topic_flagged}</flagged>
    <allow_hide_topics>$gl_allow_hide_topics</allow_hide_topics>
    <allow_unhide_topics>$gl_allow_unhide_topics</allow_unhide_topics>
    <allow_move_topics>$gl_allow_move_topics</allow_move_topics>
    <allow_del_topics>$gl_allow_del_topics</allow_del_topics>
    <allow_lock_topics>$gl_allow_lock_topics</allow_lock_topics>
    <allow_stick_topics>$gl_allow_stick_topics</allow_stick_topics>
</topic>
EOF;

        if ($wp) {
            $this->setTitle($t['topic_title']);
            $li = $this->_getLoginInfo ($u);            
            return $this->addHeaderFooter ($li, "<posts>{$x0}{$x1}{$x2}{$p}</posts>");
        } else {
            $cu = $this->getUrlsXml ();
            $li = $this->_getLoginInfo ($u);
            encode_post_text ($li['profile_title']);
            return "<root><logininfo>" . array2xml($li) . "</logininfo>$cu<posts>{$x0}{$x1}{$x2}{$p}</posts></root>";
        }
    }


    /**
     * returns my threads topics XML
     * @param $wp			return whole page XML
     */
    function getXxxTopicsXML ($wp, $sTitle, $sDesc, $sFunc, $start = 0)
    {
        global $gConf;

        $user = $this->getLoginUser();

        if (!$user) {
            return $this->_no_access($wp);
        }

        $x1 = <<<EOF
<forum>
    <title><![CDATA[$sTitle]]></title>
    <desc><![CDATA[$sDesc]]></desc>
</forum>
EOF;

        $x2 = '';

        $user_last_act = (int)$this->fdb->getUserLastActivity ($user);

        $num = 0;
        $a = $this->fdb->$sFunc($user, $start, $num);
        $t = '';
        foreach ($a as $r) {
                $lp = $this->fdb->getTopicPost($r['topic_id'], 'last');
                $fp = $this->fdb->getTopicPost($r['topic_id'], 'first');

                // acquire user info
                if (!isset($ui[$fp['user']]) && ($aa = $this->_getUserInfoReadyArray ($fp['user'])))
                    $ui[$fp['user']] = $aa;
                if (!isset($ui[$lp['user']]) && ($aa = $this->_getUserInfoReadyArray ($lp['user'])))
                    $ui[$lp['user']] = $aa;

                $td = $this->fdb->getTopicDesc ($r['topic_id']);
                $td = orca_mb_substr($td, 0, $gConf['topics_desc_len']);
                if (orca_mb_len($td) == $gConf['topics_desc_len'])
                    $td .= '...';
                $this->_buld_topic_desc ($td);

                if (!$user)
                    $new_topic = 0;
                else
                    $new_topic = $this->isNewTopic ($r['topic_id'],  $r['last_post_when'], $user_last_act) ? 1 : 0;

                encode_post_text ($r['topic_title'], true);

                $lp['when'] = orca_format_date($lp['when']);
                $fp['when'] = orca_format_date($fp['when']);

                $t .= <<<EOF
<topic id="{$r['topic_id']}" new="$new_topic" lpt="{$r['last_post_when']}" lut="{$user_last_act}">
    <uri>{$r['topic_uri']}</uri>
    <title>{$r['topic_title']}</title>
    <desc>{$td}</desc>
    <count>{$r['count_posts']}</count>
    <last_u>
        <avatar>{$ui[$lp['user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$lp['user']]['avatar64']}</avatar_medium>
        <profile>{$ui[$lp['user']]['url']}</profile>
        <profile_title>{$ui[$lp['user']]['title']}</profile_title>
        <profile_link>{$ui[$lp['user']]['link']}</profile_link>
        <onclick>{$ui[$lp['user']]['onclick']}</onclick>
        <role>{$ui[$lp['user']]['role']}</role>
    </last_u>
    <last_d>{$lp['when']}</last_d>
    <first_u>
        <avatar>{$ui[$fp['user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$fp['user']]['avatar64']}</avatar_medium>
        <profile>{$ui[$fp['user']]['url']}</profile>
        <profile_title>{$ui[$fp['user']]['title']}</profile_title>
        <profile_link>{$ui[$fp['user']]['link']}</profile_link>
        <onclick>{$ui[$fp['user']]['onclick']}</onclick>
        <role>{$ui[$fp['user']]['role']}</role>
    </first_u>
    <first_d>{$fp['when']}</first_d>
</topic>
EOF;
        }

        $p = $this->_getPages ($start, $num, $gConf['topics_per_page']);

        if ($wp) {
            $this->setTitle($sTitle);
            $li = $this->_getLoginInfo ();
            return $this->addHeaderFooter ($li, "<topics><pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>{$x2}{$x1}{$t}</topics>");
        } else {
            $cu = $this->getUrlsXml ();
            return "<root>$cu<topics><pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>{$x2}{$x1}{$t}</topics></root>";
        }
    }

    /**
     * returns my threads topics XML
     * @param $wp			return whole page XML
     */
    function getHiddenTopicsXML ($wp, $start = 0)
    {
        return $this->getXxxTopicsXML ($wp, '[L[Hidden Topics]]', '[L[Hidden topics]]', 'getHiddenTopics', $start);
    }

    /**
     * returns my threads topics XML
     * @param $wp			return whole page XML
     */
    function getMyThreadsXML ($wp, $start = 0)
    {
        return $this->getXxxTopicsXML ($wp, '[L[My Topics]]', '[L[Topics you participate in]]', 'getMyThreadsTopics', $start);
    }

    /**
     * returns flagged topics XML
     * @param $wp			return whole page XML
     */
    function getMyFlagsXML ($wp, $start = 0)
    {
        return $this->getXxxTopicsXML ($wp, '[L[Flagged topics]]', '[L[Topics you have flagged]]', 'getMyFlaggedTopics', $start);
    }

    /**
     * returns forum topics XML
     * @param $forum_id		forum id
     * @param $wp			return whole page XML
     * @param $start		record to start with
     */
    function getTopicsXML ($forum_uri, $wp, $start = 0)
    {
        global $gConf;

        $f = $this->fdb->getForumByUri (filter_to_db($forum_uri));
        $forum_id = $f['forum_id'];

        $user = $this->getLoginUser();

        if (!$this->_checkUserPerm ($user, $f['forum_type'], 'read', $forum_id)) {
            return $this->_no_access($wp);
        }

        encode_post_text ($f['forum_title']);
        encode_post_text ($f['forum_desc']);

        $x1 = <<<EOF
<forum>
    <id>{$f['forum_id']}</id>
    <uri>{$f['forum_uri']}</uri>
    <title>{$f['forum_title']}</title>
    <desc>{$f['forum_desc']}</desc>
    <type>{$f['forum_type']}</type>
</forum>
EOF;

        $cat = $this->fdb->getCat ($f['cat_id']);
        encode_post_text ($cat['cat_name']);
        $x2 = <<<EOF
<cat>
    <id>{$cat['cat_id']}</id>
    <uri>{$cat['cat_uri']}</uri>
    <title>{$cat['cat_name']}</title>
</cat>
EOF;

        $user_last_act = (int)$this->fdb->getUserLastActivity ($user);

        $a = $this->fdb->getTopics($forum_id, $start);
        $ui = array();
        $t = '';
        foreach ($a as $r) {
                // acquire user info
                if (!isset($ui[$r['first_post_user']]) && ($aa = $this->_getUserInfoReadyArray ($r['first_post_user'])))
                    $ui[$r['first_post_user']] = $aa;
                if (!isset($ui[$r['last_post_user']]) && ($aa = $this->_getUserInfoReadyArray ($r['last_post_user'])))
                    $ui[$r['last_post_user']] = $aa;

                $td = $this->fdb->getTopicDesc ($r['topic_id']);
                $td = orca_mb_substr($td, 0, $gConf['topics_desc_len']);
                if (orca_mb_len($td) == $gConf['topics_desc_len'])
                    $td .= '...';
                $this->_buld_topic_desc ($td);

                if (!$user)
                    $new_topic = 0;
                else
                    $new_topic = $this->isNewTopic ($r['topic_id'],  $r['last_post_when'], $user_last_act) ? 1 : 0;

                encode_post_text ($r['topic_title'], true);
                $r['last_when'] = orca_format_date($r['last_when']);
                $r['first_when'] = orca_format_date($r['first_when']);

                $t .= <<<EOF
<topic id="{$r['topic_id']}" new="$new_topic" lpt="{$r['last_post_when']}" lut="{$user_last_act}" sticky="{$r['topic_sticky']}" locked="{$r['topic_locked']}">
    <uri>{$r['topic_uri']}</uri>
    <title>{$r['topic_title']}</title>
    <desc>{$td}</desc>
    <count>{$r['count_posts']}</count>
    <last_u>
        <avatar>{$ui[$r['last_post_user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$r['last_post_user']]['avatar64']}</avatar_medium>
        <profile>{$ui[$r['last_post_user']]['url']}</profile>
        <profile_title>{$ui[$r['last_post_user']]['title']}</profile_title>
        <profile_link>{$ui[$r['last_post_user']]['link']}</profile_link>
        <onclick>{$ui[$r['last_post_user']]['onclick']}</onclick>
        <role>{$ui[$r['last_post_user']]['role']}</role>
    </last_u>
    <last_d>{$r['last_when']}</last_d>
    <first_u>
        <avatar>{$ui[$r['first_post_user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$r['first_post_user']]['avatar64']}</avatar_medium>
        <profile>{$ui[$r['first_post_user']]['url']}</profile>
        <profile_title>{$ui[$r['first_post_user']]['title']}</profile_title>
        <profile_link>{$ui[$r['first_post_user']]['link']}</profile_link>
        <onclick>{$ui[$r['first_post_user']]['onclick']}</onclick>
        <role>{$ui[$r['first_post_user']]['role']}</role>
    </first_u>
    <first_d>{$r['first_when']}</first_d>
</topic>
EOF;
        }

        $num = $this->fdb->getTopicsNum($forum_id);
        $p = $this->_getPages ($start, $num, $gConf['topics_per_page']);

        if ($wp) {
            $this->setTitle($f['forum_title']);
            $li = $this->_getLoginInfo ($user);
            return $this->addHeaderFooter ($li, "<topics><pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>{$x2}{$x1}{$t}</topics>");
        } else {
            $cu = $this->getUrlsXml ();
            return "<root>$cu<topics><pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>{$x2}{$x1}{$t}</topics></root>";
        }
    }

    /**
     * returns recent topics XML
     * @param $forum_id		forum id
     * @param $wp			return whole page XML
     * @param $start		record to start with
     */
    function getRecentTopicsXML ($wp, $start = 0)
    {
        global $gConf;

        $user = $this->getLoginUser();

        $user_last_act = (int)$this->fdb->getUserLastActivity ($user);

        $a = $this->fdb->getRecentTopics($start);
        $ui = array();
        $t = '';
        foreach ($a as $r) {
                if (!$this->_checkUserPerm ('', $r['forum_type'], 'read', $r['forum_id']))
                    continue;

                // acquire user info
                if (!isset($ui[$r['first_post_user']]) && ($aa = $this->_getUserInfoReadyArray ($r['first_post_user'])))
                    $ui[$r['first_post_user']] = $aa;
                if (!isset($ui[$r['last_post_user']]) && ($aa = $this->_getUserInfoReadyArray ($r['last_post_user'])))
                    $ui[$r['last_post_user']] = $aa;

                if (!$user)
                    $new_topic = 0;
                else
                    $new_topic = $this->isNewTopic ($r['topic_id'],  $r['last_post_when'], $user_last_act) ? 1 : 0;

                encode_post_text ($r['topic_title'], true);
                encode_post_text ($r['forum_title']);
                encode_post_text ($r['cat_name']);

                $r['last_when'] = orca_format_date($r['last_when']);
                $r['first_when'] = orca_format_date($r['first_when']);

                $t .= <<<EOF
<topic id="{$r['topic_id']}" new="$new_topic" lpt="{$r['last_post_when']}" lut="{$user_last_act}" sticky="{$r['topic_sticky']}" locked="{$r['topic_locked']}">
    <uri>{$r['topic_uri']}</uri>
    <title>{$r['topic_title']}</title>
    <desc />
    <count>{$r['count_posts']}</count>
    <last_u>
        <avatar>{$ui[$r['last_post_user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$r['last_post_user']]['avatar64']}</avatar_medium>
        <profile>{$ui[$r['last_post_user']]['url']}</profile>
        <profile_title>{$ui[$r['last_post_user']]['title']}</profile_title>
        <profile_link>{$ui[$r['last_post_user']]['link']}</profile_link>
        <onclick>{$ui[$r['last_post_user']]['onclick']}</onclick>
        <role>{$ui[$r['last_post_user']]['role']}</role>
    </last_u>
    <last_d>{$r['last_when']}</last_d>
    <first_u>
        <avatar>{$ui[$r['first_post_user']]['avatar']}</avatar>
        <avatar_medium>{$ui[$r['first_post_user']]['avatar64']}</avatar_medium>
        <profile>{$ui[$r['first_post_user']]['url']}</profile>
        <profile_title>{$ui[$r['first_post_user']]['title']}</profile_title>
        <profile_link>{$ui[$r['first_post_user']]['link']}</profile_link>
        <onclick>{$ui[$r['first_post_user']]['onclick']}</onclick>
        <role>{$ui[$r['first_post_user']]['role']}</role>
    </first_u>
    <first_d>{$r['first_when']}</first_d>
    <forum id="{$r['forum_id']}" uri="{$r['forum_uri']}">{$r['forum_title']}</forum>
    <cat id="{$r['cat_id']}" uri="{$r['cat_uri']}">{$r['cat_name']}</cat>
</topic>
EOF;
        }

        $num = $this->fdb->getRecentTopicsNum();
        $p = $this->_getPages ($start, $num, $gConf['topics_per_page']);

        $aParams = array();
        $sCategories = '';//$this->getCategoriesXML ($wp, $aParams);

        if ($wp) {
            $this->setTitle('[L[Recent Topics]]');
            $li = $this->_getLoginInfo ($user);
            return $this->addHeaderFooter ($li, "<topics><pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>{$t}</topics>$sCategories");
        } else {
            $cu = $this->getUrlsXml ();
            return "<root>$cu<topics><pages num=\"$num\" per_page=\"{$gConf['topics_per_page']}\">$p</pages>{$t}</topics>$sCategories</root>";
        }
    }

    /**
     * returns array with viewed topics
     */
    function getTrackTopics ()
    {
        $a = unserialize($_COOKIE['track_topics']);
        if (!is_array($a)) return array ();
        return $a;
    }

    /**
     * mark topic as viewed
     */
    function setTrackTopic ($topic_id)
    {
        $a = unserialize($_COOKIE['track_topics']);
        if (!is_array($a)) $a = array ();
        $a[$topic_id] = time();
        setcookie ('track_topics', serialize($a));
    }

    /**
     * detect new topic by last topic update time and user activity and cookies
     *
     */
    function isNewTopic ($topic_id, $topic_last_time, $user_last_time)
    {
        $a = $this->getTrackTopics ();

        if ($a[$topic_id] && $topic_last_time > $a[$topic_id])
            return 1;
        else if ($a[$topic_id])
            return 0;

        if (!$user_last_time) return 1;

        if ($topic_last_time > $user_last_time) return 1;

        return 0;
    }

    /**
     * returns categs XML
     */
    function getCategsShortXML ($sCheckPermission = false)
    {
        $a = $this->fdb->getCategs();
        $c = '';
        foreach ($a as $r) {
            $c .= "<categ id=\"{$r['cat_id']}\" uri=\"{$r['cat_uri']}\">";
            encode_post_text($r['cat_name']);
            $c .= "<title>{$r['cat_name']}</title>";
            $c .= '<forums>' . $this->getForumsShortXML ($r['cat_id'], 0, $sCheckPermission) . '</forums>';
            $c .= "</categ>";
        }

        return "<categs>$c</categs>";
    }

    /**
     * returns forums XML
     */
    function getForumsShortXML ($cat, $root, $sCheckPermission = false)
    {
        $c = $root ? '<forums>' : '';
        $aa = $this->fdb->getForums ($cat);
        foreach ($aa as $rr) {
            if ($sCheckPermission && !$this->_checkUserPerm ('', $rr['forum_type'], $sCheckPermission, $rr['forum_id']))
                continue;

            encode_post_text($rr['forum_title']);

            $c .= <<<EOF
<forum id="{$rr['forum_id']}">
    <uri>{$rr['forum_uri']}</uri>
    <title>{$rr['forum_title']}</title>
    <type>{$rr['forum_type']}</type>
</forum>

EOF;
        }
        return $root ? ($c . "</forums>\n") : $c;
    }

    /**
     * returns forums XML
     */
    function getForumsXML ($cat, $root)
    {
        if ($root)
            $c = '<forums>';
        else
            $c = '';
        $aa = $this->fdb->getForumsByCatUri (filter_to_db($cat));

        foreach ($aa as $rr) {
            if (!$this->_checkUserPerm ('', $rr['forum_type'], 'read', $rr['forum_id']))
                continue;

            encode_post_text ($rr['forum_title']);
            encode_post_text ($rr['forum_desc']);

            if (!$rr['forum_last_ts'])
                $rr['forum_last'] = '';
            else
                $rr['forum_last'] = orca_format_date($rr['forum_last']);

            $c .= <<<EOF
<forum id="{$rr['forum_id']}" new="0" cat="$cat">
    <uri>{$rr['forum_uri']}</uri>
    <title>{$rr['forum_title']}</title>
    <desc>{$rr['forum_desc']}</desc>
    <type>{$rr['forum_type']}</type>
    <posts>{$rr['forum_posts']}</posts>
    <topics>{$rr['forum_topics']}</topics>
    <last>{$rr['forum_last']}</last>
</forum>

EOF;
        }

        if ($root) {
            $cu = $this->getUrlsXml ();
            return '<root>' . $cu . $c . "</forums></root>\n";
        } else {
            return $c;
        }
    }



    /**
     * returns page XML
     */
    function getPageXML ($first_load = 1, &$p)
    {
        global $gConf;

        if (isset($p['manage_forum']) && $p['manage_forum'] && !$this->isAdmin()) {
            return $this->_no_access($first_load);
        }

        $s = $this->getCategoriesXML ($first_load, $p);

        // live tracker
        $lt = '';//"<live_tracker>" . $this->getLivePostsXML() . "</live_tracker>";

        $li = $this->_getLoginInfo ();

        if ($first_load) {
            return $this->addHeaderFooter ($li, $s.$lt);
        } else {
            $cu = $this->getUrlsXml ();
            return "<root>$cu<logininfo>".array2xml($li)."</logininfo><page>{$s}{$lt}</page></root>";
        }
    }

    /**
     * returns categories XML (forums index)
     */
    function getCategoriesXML ($first_load = 1, &$p)
    {
        global $gConf;

        $a = $this->fdb->getCategs();
        $c = '';
        foreach ($a as $r) {
            $icon_url  = $r['cat_icon'] ? $gConf['url']['icon'] . $r['cat_icon'] : '';
            $c .= "<categ id=\"{$r['cat_id']}\" uri=\"{$r['cat_uri']}\" icon=\"$icon_url\" count_posts=\"{$r['count_posts']}\" count_topics=\"{$r['count_topics']}\" count_forums=\"{$r['count_forums']}\">";
            encode_post_text ($r['cat_name']);
            $c .= "<title>{$r['cat_name']}</title>";

            if ((isset($p['cat']) && $p['cat'] == $r['cat_uri']) || ($r['cat_expanded'] && !isset($p['cat']))) {
                    $this->setTitle ($r['cat_name']);
                $c .= '<forums>'.$this->getForumsXML ($r['cat_uri'], 0) . '</forums>';
            }
            $c .= "</categ>";
        }

        $s = "<categs>$c</categs>";

        if ($first_load && isset($p['action']) && 'goto' == $p['action'] && isset($p['forum_id']))
            $s .= "<onload>f.selectForum('" . $p['forum_id'] . "', 0)</onload>";

        if ($first_load && isset($p['action']) && 'goto' == $p['action'] && isset($p['topic_id']))
            $s .= "<onload>f.selectTopic('" . $p['topic_id'] . "')</onload>";

        return $s;
    }

    function liveTracker ()
    {
        $lt = "<live_tracker>" . $this->getLivePostsXML() . "</live_tracker>";
        return $this->addHeaderFooter ($li, $lt);
    }

    function getLivePostsXML ($count = 10, $ts = 0)
    {
        global $gConf;

        $ret = '';

        if ($ts < (time() - 172800))
            $ts = 0;

        $a = $this->fdb->getLivePosts ($count, $ts);
        $ui = array ();
        foreach ($a as $r) {
            // acquire user info
            if (!isset($ui[$r['user']]) && ($aa = $this->_getUserInfoReadyArray ($r['user'])))
                $ui[$r['user']] = $aa;

            $this->_buld_topic_desc ($r['post_text']);

            encode_post_text($r['topic_title'], true);
            encode_post_text($r['forum_title']);
            encode_post_text($r['cat_name']);

            $r['when'] = $this->_format_when ($r['sec']);

            $ret .= <<<EOF
<post id="{$r['post_id']}" ts="{$r['ts']}">
    <text>{$r['post_text']}</text>
    <user>{$r['user']}</user>
    <date>{$r['when']}</date>

    <avatar>{$ui[$r['user']]['avatar']}</avatar>
    <avatar_medium>{$ui[$r['user']]['avatar64']}</avatar_medium>
    <profile>{$ui[$r['user']]['url']}</profile>
    <profile_title>{$ui[$r['user']]['title']}</profile_title>
    <onclick>{$ui[$r['user']]['onclick']}</onclick>
    <role>{$ui[$r['user']]['role']}</role>

    <topic id="{$r['topic_id']}" uri="{$r['topic_uri']}">{$r['topic_title']}</topic>
    <forum id="{$r['forum_id']}" uri="{$r['forum_uri']}">{$r['forum_title']}</forum>
    <cat id="{$r['cat_id']}" uri="{$r['cat_uri']}">{$r['cat_name']}</cat>
    <base>{$gConf['url']['base']}</base>
</post>
EOF;
        }

        return $ret;
    }

    /**
     * check if new posts are available
     *	@param	$ts		timestamp of last post
     */
    function isNewPost ($ts)
    {
        return '<ret>' . (int)$this->fdb->getNewPostTs ($ts) . '</ret>';
    }


    /**
     * post reply
     * @param $p	_post array
     */
    function postReplyXML (&$p)
    {
        global $gConf;

        $f = $this->fdb->getForum ((int)$p['forum_id']);

        $t = $this->fdb->getTopic ((int)$p['topic_id']);

        if (!$this->_checkUserPerm ('', $f['forum_type'], 'post', (int)$p['forum_id']) || $t['topic_locked'] || bx_is_spam($p['topic_text'])) {
            return <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">
    window.parent.document.f.accessDenied();
</script>
</body>
</html>
EOF;
        }


        // post mesage here

        $p['forum_id'] = (int)$p['forum_id'];
        $p['topic_id'] = (int)$p['topic_id'];

        $user = $this->_getLoginUserName ();

        prepare_to_db($p['topic_text'], 1);

        // check if max number of posts per topic is reached
        if ($t['topic_posts'] >= $gConf['max_posts']) {
            $t = $this->topicAutoContinue($t);
        }

        $iReplyId = $this->fdb->postReply ($p['forum_id'], $t['topic_id'], $p['topic_text'], $user);
        $aReply = $this->fdb->getPost($iReplyId, '');

        $this->_handleSignature ($_POST, $user);

        $isUploadSuccess = $this->_handleUpload ($p, $iReplyId);

        $t = $this->fdb->getTopic($t['topic_id']);

        if (is_callable($this->onPostReply))
            call_user_func_array ($this->onPostReply,  array($t, $aReply['post_text'], $user));

        return <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">
    if (!$isUploadSuccess)
        window.parent.alert ('[L[Some or all files upload failed]]');
    window.parent.document.f.replySuccess('{$f['forum_uri']}', '{$t['topic_uri']}');
</script>
</body>
</html>
EOF;


    }

    /**
     * Automatically close old topic and begin new one
     * @param $t - old topic array
     * @return new topic array
     */
    function topicAutoContinue ($t)
    {
        global $gConf;

        // extract index of last topic to continue from
        $iTopicIndex = 1;
        $sTopicUri = $t['topic_uri'];
        $iPos = mb_strpos($t['topic_uri'], '.');
        if (false !== $iPos) {
            $iTopicIndexOld = mb_substr($t['topic_uri'], $iPos + 1);
            if ((int)$iTopicIndexOld) {
                $iTopicIndex = (int)$iTopicIndexOld;
                $sTopicUri = mb_substr($t['topic_uri'], 0, $iPos);
            }
        }

        // generate new topic uri
        $iLimit = 1000;
        do {
            $sNewTopicUri = $sTopicUri . '.' . (++$iTopicIndex);
        } while (--$iLimit && $this->uriCheckUniq($sTopicUri, TF_FORUM_TOPIC, 'topic_uri'));

        // create new topic with service post
        $aTopicFirst = $this->fdb->getTopicByUri($sTopicUri);
        $sNewTopicSubj = ($aTopicFirst ? $aTopicFirst['topic_title'] : $t['topic_title']) . sprintf('[L[continue_topic_suffix - %d]]', $iTopicIndex);
        $sOldTopicUrl = $gConf['url']['base'] . sprintf($gConf['rewrite']['topic'], $t['topic_uri']);
        $sNewTopicText = sprintf('[L[continue_topic_first %s]]', '<a href="' . $sOldTopicUrl . '">' . $t['topic_title'] . '</a>');
        prepare_to_db($sNewTopicSubj, -1);
        prepare_to_db($sNewTopicText, 1);
        $iNewTopicReply = $this->fdb->newTopic ((int)$t['forum_id'], $sNewTopicSubj, $sNewTopicText, false, $gConf['robot'], $sNewTopicUri);
        $aNewTopicReply = $this->fdb->getPost($iNewTopicReply, '');
        $aNewTopic = $this->fdb->getTopic($aNewTopicReply['topic_id']);

        // add service reply in old topic
        $sNewTopicUrl = $gConf['url']['base'] . sprintf($gConf['rewrite']['topic'], $sNewTopicUri);
        $sOldTopicText = sprintf('[L[continue_topic_last %s]]', '<a href="' . $sNewTopicUrl . '">' . $aNewTopic['topic_title'] . '</a>');
        prepare_to_db($sOldTopicText, 1);
        $iReplyId = $this->fdb->postReply ((int)$t['forum_id'], $t['topic_id'], $sOldTopicText, $gConf['robot']);

        // lock old topic
        if (!$this->fdb->isLocked($t['topic_id']))
            $this->fdb->lock($t['topic_id'], $gConf['robot']);

        // trasfer subscribers to new topic
        $a = $this->fdb->getSubscribersToTopic ($t['topic_id']);
        foreach ($a as $r)
            $this->fdb->flag($aNewTopic['topic_id'], $r['user']);

        return $aNewTopic;
    }

    /**
     * xml for edit post
     * @param $post_id		post id
     * @param $topic_id		topic id
     */
    function editPostXml ($post_id, $topic_id)
    {
        $cu = $this->getUrlsXml ();
        if ($post_id) {
            $a = $this->fdb->getTopicByPostId ($post_id);
            $t = $this->fdb->getTopic ($a['topic_id']);
            $topic_id = $t['topic_uri'];

            $timeout = 'disabled';
            $access = 'deny';
            if ($this->_checkUserPerm ('', $t['forum_type'], 'edit', $t['forum_id']) )
                $access = 'allow';
            if ('deny' == $access
                &&
                ($user = $this->fdb->getPostUser((int)$post_id)) == $this->_getLoginUserName()
                &&
                $this->_checkUserPerm ('', 'own', 'edit', $t['forum_id']) ) {

                $when = $this->fdb->getPostWhen ((int)$post_id);
                $timeout = $GLOBALS['gConf']['edit_timeout'] - (time() - $when);
                $access = $timeout > 10 && !$t['topic_locked'] ? 'allow' : 'deny';
            }
        }

        $files = '';
        $signature = '';
        if ('allow' == $access) {
            $files = $this->_getAttachmentsXML ((int)$post_id);
            $signature = $this->fdb->getSignature ($this->fdb->getPostUser((int)$post_id));
            encode_post_text ($signature);
        }

        return <<<EOS
<root>
    $cu
    <edit_post>
        <post_id>$post_id</post_id>
        <topic_id>$topic_id</topic_id>
        <timeout>$timeout</timeout>
        <access>$access</access>
        <attachments>$files</attachments>
        <signature>$signature</signature>
    </edit_post>
</root>
EOS;
    }

    /**
     * edit post
     * @param $post_id		post id
     * @param $topic_id		topic id
     * @param $text			new post text
     */
    function editPost ($post_id, $topic_id, $text)
    {
        $no_access = true;

        $t = $this->fdb->getTopicByUri (filter_to_db($topic_id));
        $user = $this->fdb->getPostUser((int)$post_id);

        if ($this->_checkUserPerm ('', $t['forum_type'], 'edit', $t['forum_id']))
            $no_access = false;
        if ($no_access && $user == $this->_getLoginUserName())
            if ($this->_checkUserPerm ('', 'own', 'edit', $t['forum_id']) && !$this->_isEditTimeout((int)$post_id) && !$t['topic_locked'])
                $no_access = false;

        if ($no_access) {
            return <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">
    window.parent.document.f.accessDenied();
</script>
</body>
</html>
EOF;
        }

        // edit post here
        prepare_to_db($text, 1);

        $this->fdb->editPost ($post_id, $text, $user);

        $this->_handleSignature ($_POST, $user);

        $isUploadSuccess = $this->_handleUpload ($_POST, $post_id);

        if (is_callable($this->onPostEdit))
            call_user_func_array ($this->onPostEdit,  array($t, $post_id, $text, $user));

        return <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">
    if (!$isUploadSuccess)
        window.parent.alert ('[L[Some or all files upload failed]]');
    window.parent.document.f.editSuccess('{$t['topic_uri']}');
</script>
</body>
</html>
EOF;

    }

    /**
     * delete post
     * @param $post_id		post id
     * @param $topic_id		topic id
     * @param $forum_id		forum id
     */
    function deletePostXML ($post_id, $topic_id, $forum_id)
    {
        $no_access = true;

        $f = $this->fdb->getForumByPostId ($post_id);
        $aTopic = $this->fdb->getTopic ($topic_id);
        $sPostUser = $this->fdb->getPostUser((int)$post_id);
        $sUser = $this->_getLoginUserName();

        if ($this->_checkUserPerm ('', $f['forum_type'], 'del', $f['forum_id']))
            $no_access = false;
        if ($no_access && $sPostUser == $sUser)
            if ($this->_checkUserPerm ('', 'own', 'del', $f['forum_id']) && !$this->_isEditTimeout((int)$post_id) && !$aTopic['topic_locked'])
                $no_access = false;

        if ($no_access || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            return <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">
    window.parent.document.f.accessDenied();
</script>
</body>
</html>
EOF;
        }

        // delete post here

        $this->fdb->deletePost ($post_id, $sPostUser == $sUser ? '' : $sUser);

        $exists = $this->fdb->getTopic ($topic_id) ? 1 : 0;

        if (is_callable($this->onPostDelete))
            call_user_func_array ($this->onPostDelete,  array($aTopic, $post_id, $user));

        return <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">
    window.parent.document.f.deleteSuccess('{$f['forum_uri']}', '{$aTopic['topic_uri']}', {$exists});
</script>
</body>
</html>
EOF;

    }

    /**
     * hide or unhide post
     * @param $post_id		post id
     * @param $topic_id		topic id
     * @param $forum_id		forum id
     */
    function hidePost ($is_hide, $post_id)
    {
        $f = $this->fdb->getForumByPostId((int)$post_id);
        $user = $this->_getLoginUserName();
        if (0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') || !$this->_checkUserPerm ($user, '', 'hide_posts', $f ? $f['forum_id'] : 0))
            return '<ret>0</ret>';

        if (!$this->fdb->hidePost ((int)$is_hide, (int)$post_id, $user))
            return '<ret>0</ret>';

        if (is_callable($this->onPostHideUnhide))
            call_user_func_array ($this->onPostHideUnhide,  array($is_hide, $post_id, $user));

        return '<ret>1</ret>';
    }

    /**
     * move topic
     * @param $topic_id		topic id to move
     */
    function moveTopicForm ($topic_id)
    {
        $cu = $this->getUrlsXml ();

        $t = $this->fdb->getTopic ($topic_id);

        $s = '<forums>';
        $ac = $this->fdb->getCategs();
        foreach ($ac as $c) {
            $s .= '<cat name="'. htmlspecialchars($c['cat_name']) .'">';

            $af = $this->fdb->getForumsByCatUri (filter_to_db($c['cat_uri']));

            foreach ($af as $f) {
                encode_post_text ($f['forum_title']);
                $s .= '<forum id="' . $f['forum_id'] . '" uri="' . $f['forum_uri'] . '" selected="' . ($t['forum_id'] == $f['forum_id'] ? 1 : 0) . '">' . $f['forum_title'] . '</forum>';
            }

            $s .= '</cat>';
        }
        $s .= '</forums>';

        return <<<EOS
<root>
$cu
<form>
    <topic>
        <id>{$t['topic_id']}</id>
        <title><![CDATA[{$t['topic_title']}]]></title>
        <uri>{$t['topic_uri']}</uri>
        <forum_id>{$t['forum_id']}</forum_id>
    </topic>
    $s
</form>
</root>
EOS;
    }

    function moveTopicSubmit ($topic_id, $forum_id, $old_forum_id, $goto_new_location)
    {
        $user = $this->_getLoginUserName();
        if (0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') || !$this->_checkUserPerm ($user, '', 'move_topics', (int)$forum_id) || !$topic_id || !$forum_id || !$old_forum_id)
            return '<ret>0</ret>';

        if (!$this->fdb->moveTopic ((int)$topic_id, (int)$forum_id, (int)$old_forum_id))
            return '<ret>0</ret>';

        if (is_callable($this->onTopicMove))
            call_user_func_array ($this->onTopicMove,  array($topic_id, $forum_id, $user));

        $f = $this->fdb->getForum($goto_new_location ? $forum_id : $old_forum_id);
        return "<ret>{$f['forum_uri']}</ret>";
    }

    /**
     * delete topic
     * @param $topic_id		topic id
     */
    function delTopic ($topic_id)
    {
        $f = $this->fdb->getForumByTopicId ((int)$topic_id);
        $user = $this->_getLoginUserName();
        if (0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') || !$this->_checkUserPerm ($user, '', 'del_topics', $f ? $f['forum_id'] : 0))
            return '<ret>0</ret>';

        if (!$this->fdb->delTopic ((int)$topic_id, $user))
            return '<ret>0</ret>';

        if (is_callable($this->onTopicDelete))
            call_user_func_array ($this->onTopicDelete,  array($topic_id, $user));

        return '<ret>1</ret>';
    }

    /**
     * hide or unhide post
     * @param $is_hide		hide or unhide
     * @param $topic_id		topic id
     */
    function hideTopic ($is_hide, $topic_id)
    {
        $f = $this->fdb->getForumByTopicId ((int)$topic_id);
        $user = $this->_getLoginUserName();
        if (0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') || !$this->_checkUserPerm ($user, '', $is_hide ? 'hide_topics' : 'unhide_topics', $f ? $f['forum_id'] : 0))
            return '<ret>0</ret>';

        if (!$this->fdb->hideTopic ((int)$is_hide, (int)$topic_id, $user))
            return '<ret>0</ret>';

        if (is_callable($this->onTopicHideUnhide))
            call_user_func_array ($this->onTopicHideUnhide,  array($is_hide, $topic_id, $user));

        return '<ret>1</ret>';
    }

    /**
     * stick/unstick topic
     *	@param $topic_id	topic id
     */
    function stick ($topic_id)
    {
        $f = $this->fdb->getForumByTopicId ((int)$topic_id);
        $user = $this->_getLoginUserName();
        if (0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') || !$this->_checkUserPerm ($user, '', 'sticky', $f ? $f['forum_id'] : 0))
            return '<ret>0</ret>';

        if (!$this->fdb->stick ((int)$topic_id, $user))
            return '<ret>0</ret>';

        $t = $this->fdb->getTopic($topic_id);
        return $t['topic_sticky'] ? '<ret>1</ret>' : '<ret>-1</ret>';
    }

    /**
     * lock/unlock topic
     *	@param $topic_id	topic id
     */
    function lock ($topic_id)
    {
        $f = $this->fdb->getForumByTopicId ((int)$topic_id);
        $user = $this->_getLoginUserName();
        if (0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') || !$this->_checkUserPerm ($user, '', 'lock', $f ? $f['forum_id'] : 0))
            return '<ret>0</ret>';

        if (!$this->fdb->lock ((int)$topic_id, $user))
            return '<ret>0</ret>';

        return $this->fdb->isLocked ((int)$topic_id) ? '<ret>1</ret>' : '<ret>-1</ret>';
    }

    /**
     * post new topic
     * @param $p	_post array
     */
    function postNewTopicXML ($p)
    {
        $sAccessDeniedCode = <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">

    if (window.parent.document.getElementById('tinyEditor'))
        window.parent.tinyMCE.execCommand('mceRemoveEditor', false, 'tinyEditor');

    window.parent.document.f.accessDenied();

</script>
</body>
</html>
EOF;

        $f = $this->fdb->getForum ((int)$p['forum']);

        if (!$this->_checkUserPerm ('', $f['forum_type'], 'post', (int)$p['forum']) || bx_is_spam($p['topic_text']))
            return $sAccessDeniedCode;

        if ($p['topic_sticky'] == 'on' && !$this->_checkUserPerm ('', '', 'sticky', (int)$p['forum']))
            return $sAccessDeniedCode;


        // post mesage here

        $user = $this->_getLoginUserName ();

        prepare_to_db($p['topic_subject'], -1);
        prepare_to_db($p['topic_text'], 1);

        $topic_uri = $this->uriGenerate ($p['topic_subject'], TF_FORUM_TOPIC, 'topic_uri');
        $post_id = $this->fdb->newTopic ((int)$p['forum'], $p['topic_subject'], $p['topic_text'], ($p['topic_sticky'] == 'on'), $user, $topic_uri);

        $this->_handleSignature ($_POST, $user);

        $isUploadSuccess = $this->_handleUpload ($p, $post_id);

        if (is_callable($this->onNewTopic))
            call_user_func_array ($this->onNewTopic,  array((int)$p['forum'], $p['topic_subject'], $p['topic_text'], ($p['topic_sticky'] == 'on'), $user, $topic_uri, $post_id));

        return <<<EOF
<html>
<body>
<script language="javascript" type="text/javascript">

    if (!$isUploadSuccess)
        window.parent.alert ('[L[Some or all files upload failed]]');

    if (window.parent.document.getElementById('tinyEditor'))
        window.parent.tinyMCE.execCommand('mceRemoveEditor', false, 'tinyEditor');

    window.parent.document.f.selectTopic('{$topic_uri}');

</script>
</body>
</html>
EOF;

    }

    function isAdmin ()
    {
        $a = $this->_getUserInfo ($this->getLoginUser());
        return $a['admin'];
    }

    /**
     * returns logged in user
     */
    function getLoginUser ()
    {
        return $this->_getLoginUser();
    }

    /**
     * updates current user last activity time
     */
    function updateCurrentUserActivity ()
    {
        $u = $this->getLoginUser ();
        if (!$u) return;

        $this->fdb->updateUserActivity ($u);
    }

    function logout ()
    {
        $u = $this->getLoginUser ();
        if (!$u) return '<ret>0</ret>';

        setcookie('orca_pwd', 'orca_pwd', time() - 86400);
        setcookie('orca_user', 'orca_user', time() - 86400);
        setcookie('track_topics', 'track_topics', time() - 86400);

        $this->fdb->updateUserLastActivity ($u);

        return '<ret>1</ret>';
    }

    /**
     * post voting
     *	@param $post_id	post id
     *	@param $vote	vote (1|-1)
     */
    function votePost ($post_id, $vote)
    {
        $u = $this->getLoginUser ();
        if (!$u || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
            return '<ret>0</ret>';

        if (!$this->fdb->insertVote ((int)$post_id, $u, $vote))
            return '<ret>0</ret>';

        if (is_callable($this->onVote))
            call_user_func_array ($this->onVote,  array((int)$post_id, $u, $vote));

        return '<ret>1</ret>';
    }

    /**
     * report post
     *	@param $post_id	post id
     */
    function report ($post_id)
    {
        if (!$post_id || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
            return '<ret>0</ret>';

        $u = $this->getLoginUser ();
        if (!$u)
            return '<ret>0</ret>';

        if (!$this->fdb->report ((int)$post_id, $u))
            return '<ret>0</ret>';

        if (is_callable($this->onReport))
            call_user_func_array ($this->onReport,  array((int)$post_id, $u));

        return '<ret>1</ret>';
    }

    /**
     * flag/unflag topic
     *	@param $topic_id	topic id
     */
    function flag ($topic_id)
    {
        if (!$topic_id || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST'))
            return '<ret>0</ret>';

        $u = $this->getLoginUser ();
        if (!$u)
            return '<ret>0</ret>';

        if ($this->fdb->isFlagged ((int)$topic_id, $u)) {
            if (!$this->fdb->unflag ((int)$topic_id, $u))
                return '<ret>0</ret>';
            if (is_callable($this->onUnflag))
                call_user_func_array ($this->onUnflag,  array((int)$topic_id, $u));
            return '<ret>-1</ret>';
        }

        if (!$this->fdb->flag ((int)$topic_id, $u))
            return '<ret>0</ret>';

        if (is_callable($this->onFlag))
            call_user_func_array ($this->onFlag,  array((int)$topic_id, $u));

        return '<ret>1</ret>';
    }

    /**
     * forum rss feed, 10 latest topics in the forum
     *	@param $forum_id	forum id
     */
    function getRssForum ($forum_uri)
    {
        global $gConf;

        $this->_rssPrepareConf ();

        $f = $this->fdb->getForumByUri (filter_to_db($forum_uri));
        $forum_id = $f['forum_id'];

        if (!$f) exit;

        $a = $this->fdb->getTopics ($forum_id, 0);

        $items = '';
        $lastBuildDate = '';
        foreach ($a as $r) {
            $lp = $this->fdb->getTopicPost($r['topic_id'], 'last');

            $td = $this->fdb->getTopicDesc ($r['topic_id']);
            $td = orca_mb_substr($td, 0, $gConf['topics_desc_len']);
            if (orca_mb_len($td) == $gConf['topics_desc_len'])
                $td .= '...';

            if (!$lastBuildDate)
                $lastBuildDate = $lp['when'];

            $items .= $this->_rssItem ($r['topic_title'], sprintf($gConf['rewrite']['topic'], $r['topic_uri']), $td, $lp['when']);
        }

        return $this->_rssFeed ($f['forum_title'], sprintf($gConf['rewrite']['forum'], $f['forum_uri'], 0), $f['forum_title'], $lastBuildDate, $items);
    }


    /**
     * latest updates feed, 10 latest topics
     *	@param $forum_id	forum id
     */
    function getRssUpdatedTopics ()
    {
        global $gConf;

        $this->_rssPrepareConf ();

        $a = $this->fdb->getRecentTopics (0);

        $items = '';
        $lastBuildDate = '';
        $ui = array();
        foreach ($a as $r) {
            // acquire user info
            if (!isset($ui[$r['last_post_user']]) && ($aa = $this->_getUserInfoReadyArray ($r['last_post_user'], false)))
                $ui[$r['last_post_user']] = $aa;

            $td = orca_mb_replace('/#/', $r['count_posts'], '[L[# posts]]') . ' &#183; ' . orca_mb_replace('/#/', $ui[$r['last_post_user']]['title'], '[L[last reply by #]]') . ' &#183; ' . $r['cat_name'] . ' &#187; ' . $r['forum_title'];

            if (!$lastBuildDate)
                $lastBuildDate = $r['last_when'];

            $items .= $this->_rssItem ($r['topic_title'], sprintf($gConf['rewrite']['topic'], $r['topic_uri']), $td, $r['last_when']);
        }

        return $this->_rssFeed ('[L[Updated Topics]]', '', '[L[Updated Topics]]', $lastBuildDate, $items);
    }


    /**
     * topic rss feed, 10 latest posts in the topic
     *	@param $forum_id	forum id
     */
    function getRssTopic ($topic_uri)
    {
        global $gConf;

        $this->_rssPrepareConf ();

        $t = $this->fdb->getTopicByUri(filter_to_db($topic_uri));
        $topic_id = (int)$t['topic_id'];

        if (!$t) {
            header("HTTP/1.1 404 Not Found");
            echo '404 Not Found';
            exit;
        }

        $a = $this->fdb->getPosts ($topic_id, 0, 'DESC', 10);

        $items = '';
        $lastBuildDate = '';
        foreach ($a as $r) {
            $td = orca_mb_substr($r['post_text'], 0, 255);
            if (orca_mb_len($td) == 255) $td .= '[...]';
            $td = strip_tags($td);

            $tt = orca_mb_substr($td, 0, 32);

            if (!$lastBuildDate) $lastBuildDate = $a['when'];

            $items .= $this->_rssItem ($tt, sprintf($gConf['rewrite']['topic'], $t['topic_uri']), $td, $r['when']);
        }

        return $this->_rssFeed ($t['topic_title'], sprintf($gConf['rewrite']['topic'], $t['topic_uri']), $t['topic_title'], $lastBuildDate, $items);
    }

    /**
     * user posts rss feed, 10 latest posts of specified user
     *	@param $user	username
     *  @param $sort	sort : rnd | top | latest - default
     */
    function getRssUser ($user, $sort)
    {
        global $gConf;

        $this->_rssPrepareConf ();

        $a = $this->fdb->getUserPostsList(filter_to_db($user), $sort, $gConf['topics_per_page']);

        $items = '';
        $lastBuildDate = '';
        foreach ($a as $r) {
            if (!$lastBuildDate)
                $lastBuildDate = $r['when'];

            $td = strip_tags($r['post_text']);
            if (strlen($td) == 256) $td .= '[...]';

            $items .= $this->_rssItem ($r['topic_title'], sprintf($gConf['rewrite']['topic'], $r['topic_uri']), $td, $r['when']);
        }

        if ($sort == 'rnd' || $sort == 'top') $lastBuildDate = '';

        $aUser = $this->_getUserInfoReadyArray ($user, false);

        $sTitle = sprintf("[L[%s's forum posts]]", $aUser['title']);

        return $this->_rssFeed ($sTitle, '?action=goto&amp;search_result=1&amp;text=&amp;type=msgs&amp;forum=0&amp;u=' . $user . '&amp;disp=posts&amp;start=0', $sTitle, $lastBuildDate, $items);
    }

    /**
     * all posts rss feed, 10 latest posts
     *	@param $user	username
     *  @param $sort	sort : rnd | top | latest - default
     */
    function getRssAll ($sort)
    {
        global $gConf;

        $this->_rssPrepareConf ();

        $a = $this->fdb->getAllPostsList($sort, $gConf['topics_per_page']);

        $ui = array();
        $items = '';
        $lastBuildDate = '';
        foreach ($a as $r) {
            if (!$lastBuildDate)
                $lastBuildDate = $r['when'];

            if (!isset($ui[$r['user']]) && ($aa = $this->_getUserInfo ($r['user'])))
                $ui[$r['user']] = $aa;

            $td = $r['post_text'];
            if (orca_mb_len($td) == 255) $td .= '[...]';
            $td = strip_tags($td);


            $items .= $this->_rssItem ($r['topic_title'], sprintf($gConf['rewrite']['topic'], $r['topic_uri']), $ui[$r['user']]['profile_title'] . ': ' . $td, $r['when']);
        }

        if ($sort == 'rnd' || $sort == 'top') $lastBuildDate = '';

        return $this->_rssFeed ('[L[Forum Posts]]', '', '[L[Forum Posts]]', $lastBuildDate, $items);
    }

    function _rssPrepareConf ()
    {
        global $gConf;
        $gConf['topics_per_page'] = 10;
    }

    function _rssItem ($sTitle, $sLink, $sDesc, $iTimestamp)
    {
        global $gConf;
        if ($iTimestamp)
            $sDate = date(DATE_RSS, $iTimestamp);
        else
            $sDate = '';
        return "
            <item>
                <title><![CDATA[{$sTitle}]]></title>
                <link>" . $gConf['url']['base'] . $sLink . "</link>
                <description><![CDATA[{$sDesc}]]></description>
                <pubDate>{$sDate}</pubDate>
                <guid>" . $gConf['url']['base'] . $sLink . "</guid>
            </item>";
    }

    function _rssFeed ($sTitle, $sLink, $sDesc, $iLastDateTimestamp, $sItems)
    {
        global $gConf;
        if ($iLastDateTimestamp)
            $sLastDate = date(DATE_RSS, $iLastDateTimestamp);
        else
            $sLastDate = '';
        return <<<EOF
<rss version="2.0">
    <channel>
        <title><![CDATA[{$sTitle}]]></title>
        <link>{$gConf['url']['base']}{$sLink}</link>
        <description><![CDATA[{$sDesc}]]></description>
        <lastBuildDate>{$sLastDate}</lastBuildDate>
        $sItems
    </channel>
</rss>
EOF;
    }

    /**
     * profile xml
     * @param	$u	username
     * @param	$wp	return whole page XML
     */
    function showProfile ($u, $wp)
    {
        $a = $this->_getUserInfo ($u);
        $as = $this->fdb->getUserStat (filter_to_db($u));

        $a['username'] = $u;
        $a['posts'] = (int)$as['posts'];
        $a['user_last_post'] = orca_format_date($as['user_last_post']);
        $a['last_online'] = orca_format_date($this->fdb->getUserLastOnlineTime (filter_to_db($u)));

        encode_post_text ($as['role']);
        $a['role'] = $as['role'];

        $p = array2xml ($a);

        if ($wp) {
            if ($a)
                $this->setTitle ("<![CDATA[$u]]>");
            $li = $this->_getLoginInfo ();
            return $this->addHeaderFooter ($li, "<profile>$p</profile>");
        } else {
            $cu = $this->getUrlsXml ();
            return "<root>$cu<profile>$p</profile></root>";
        }
    }

    // private functions

    function _getLoginInfo ($user = '')
    {
        if (!strlen($user)) $user = $this->_getLoginUserName ();
        $a = $this->_getUserInfo ($user);
        $a['username'] = $user;
        return $a;
    }

    function _getUserInfoReadyArray ($user, $bWrapWithCdata = true)
    {
        $aa = $this->_getUserInfo ($user);
        if (!$aa)
            return array();

        if ($bWrapWithCdata ) {
            encode_post_text ($aa['role']);
            encode_post_text ($aa['profile_title']);
        }

        $aa['profile_link'] = ($aa['profile_url'] ? '<a href="' . $aa['profile_url'] . '">' . $aa['profile_title'] . '</a>' : $aa['profile_title']);

        return array ('avatar' => $aa['avatar'], 'avatar64' => $aa['avatar64'], 'url' => $aa['profile_url'], 'link' => $aa['profile_link'], 'title' => $aa['profile_title'], 'onclick' => $aa['profile_onclick'], 'role' => $aa['role']);
    }

    function _getUserInfo ($user)
    {
        if (is_callable($this->getUserInfo))
            return call_user_func_array ($this->getUserInfo,  array($user));
        return array ();
    }

    /**
     * check user perms
     * @param $user		username
     * @param $f_type	forum type private/public/own
     * @param $a_type	access type read/post/edit/del
     */
    function _checkUserPerm ($user, $f_type, $a_type, $forum_id = 0)
    {
        if (is_callable($this->getUserPerm)) {
            $a = call_user_func_array ($this->getUserPerm,  array($user, $f_type, $a_type, $forum_id));
            return $a["{$a_type}_{$f_type}"];
        }
        return false;
    }

    /**
     * check timeout
     * @param $post_id	post id
     */
    function _isEditTimeout ($post_id)
    {
        if ((time() - $this->fdb->getPostWhen($post_id) - 10) > $GLOBALS['gConf']['edit_timeout'])
            return true;
        return false;
    }

    /**
     * returns loggen in user
     */
    function _getLoginUserName ()
    {
        return $this->_getLoginUser();
    }

    /**
     * returns logged in user
     */
    function _getLoginUser ()
    {
        if (is_callable($this->getLoginUser))
            return call_user_func ($this->getLoginUser);
        return '';
    }

    function _format_when ($iSec)
    {
        $s = '';
        if ($iSec < 3600) {
            $i = round($iSec/60);
            if (0 == $i || 1 == $i) $s .= '1[L[Minute Ago]]';
            else $s .= $i . '[L[Minutes Ago]]';
        } else if ($iSec < 86400) {
            $i = round($iSec/60/60);
            if (0 == $i || 1 == $i) $s .= '1[L[Hour Ago]]';
            else $s .= $i . '[L[Hours Ago]]';
        } else {
            $i = round($iSec/60/60/24);
            if (0 == $i || 1 == $i) $s .= '1[L[Day Ago]]';
            else $s .= $i . '[L[Days Ago]]';
        }
        return $s;
    }

    function _no_access ($wp = 0)
    {
        $xml = '<forum_access>no</forum_access>';
        if (!$wp) return $xml;
        $li = $this->_getLoginInfo ();
        return $this->addHeaderFooter ($li, $xml);
    }

    function _buld_topic_desc (&$s)
    {
        $s = strip_tags ($s);
        validate_unicode ($s);
        if ($s == '') $s = ' ';
        $s = '<![CDATA[' . $s . ']]>';
    }

    function uriGenerate ($s, $sTable, $sField, $iMaxLen = 255)
    {
        if ($GLOBALS['oTemplConfig']->bAllowUnicodeInPreg)
            $s = orca_mb_replace ('/[^\pL^\pN]+/u', '-', $s); // unicode characters
        else
            $s = orca_mb_replace ('/([^\d^\w]+)/u', '-', $s); // latin characters

        $s = orca_mb_replace ('/([-^]+)/', '-', $s);
        if (!$s) $s = '-';

        if ($this->uriCheckUniq($s, $sTable, $sField)) return $s;

        // try to add date

        if (orca_mb_len($s) > 240)
            $s = orca_mb_substr ($s, 0, 240);

        $s .= '-' . date('Y-m-d');

        if ($this->uriCheckUniq($s, $sTable, $sField)) return $s;

        // try to add number

        for ($i = 0 ; $i < 999 ; ++$i) {
            if ($this->uriCheckUniq($s . '-' . $i, $sTable, $sField)) {
                return ($s . '-' . $i);
            }
        }

        return rand(0, 999999999);
    }

    function uriCheckUniq ($s, $sTable, $sField)
    {
        return !$this->fdb->getOne("SELECT 1 FROM $sTable WHERE $sField = ? LIMIT 1", [$s]);
    }

    function setTitle ($s)
    {
        $s = str_replace(array('<![CDATA[', ']]>'), '', $s);
        $GLOBALS['gConf']['title'] = '<![CDATA[' . $s . '[L[add this to title]]' . ']]>';
    }

    function _handleSignature (&$p, $user)
    {
        if (isset($p['signature']))
            $p['signature'] = trim ($p['signature']);

        if (!isset($p['signature']) || $p['signature'] == $this->fdb->getSignature ($user))
            return true;

        prepare_to_db ($p['signature'], 1);

        return $this->fdb->updateSignature ($p['signature'], $user);
    }

    function _handleUpload (&$p, $post_id)
    {
        global $gConf;

        // handle existing uploads

        $attachments = $this->fdb->getAttachments ($post_id);
        foreach ($attachments as $file) {
            $mixedIndex = array_search ($file['att_hash'], $p['existing_file']);
            if (false !== $mixedIndex && null !== $mixedIndex)
                continue;
            $this->fdb->removeAttachment($file['att_hash']);
        }

        // handle new uploads

        $failed_uploads = 0;

        foreach ($_FILES['attachments']['error'] as $i => $error) {

            if (UPLOAD_ERR_NO_FILE == $error)
                continue;

            if ($error != UPLOAD_ERR_OK || !$_FILES['attachments']['size'][$i]) {
                ++$failed_uploads;
                continue;
            }

            $tmp_name = $_FILES["attachments"]["tmp_name"][$i];
            $name = $_FILES["attachments"]["name"][$i];
            $type = $_FILES["attachments"]["type"][$i];
            $size = (int)$_FILES["attachments"]["size"][$i];
            $hash = $this->fdb->genFileHash();
            $path = $gConf['dir']['attachments'] . orca_build_path ($hash);

            orca_mkdir_r ($path);

            if (!move_uploaded_file($tmp_name, $path . $hash)) {
                ++$failed_uploads;
                continue;
            }

            prepare_to_db ($name, 0);
            prepare_to_db ($type, 0);

            if (!$this->fdb->insertAttachment ($post_id, $hash, $name, $type, $size)) {
                ++$failed_uploads;
                continue;
            }

        }

        return $failed_uploads > 0 ? 0 : 1;
    }

    function download($hash)
    {
        global $gConf;

        prepare_to_db ($hash, 0);
        $a = $this->fdb->getAttachment($hash);
        if (!$a) {
            header("HTTP/1.1 404 Not Found");
            echo '404 Not Found';
            exit;
        }

        if (!$this->_checkUserPerm ('', '', 'download', (int)$a['forum_id'])) {
            transCheck ($this->_no_access(1), $gConf['dir']['xsl'] . 'search_form_main.xsl', $_GET['debug'] ? 0 : 1);
            exit;
        }

        $this->fdb->updateAttachmentDownloads ($hash);

        header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header ("Content-type: {$a['att_type']}");
        header ("Content-Length: " . $a['att_size']);
        if (0 !== strncmp('image/', $a['att_type'], 6))
            header ("Content-Disposition: attachment; filename=\"{$a['att_name']}\"");

        readfile ($gConf['dir']['attachments'] . orca_build_path ($hash) . $hash);

        exit;
    }

    function _getAttachmentsXML ($post_id)
    {
        $attachments = $this->fdb->getAttachments ($post_id);
        $files = '';
        foreach ($attachments as $file) {
            encode_post_text ($file['att_name']);
            $isImage = 0 === strncmp('image/', $file['att_type'], 6) ? 1 : 0;
            $files .= '<file image="' . $isImage . '" hash="' . $file['att_hash'] . '" size="' . orca_format_bytes($file['att_size']) . '" downloads="' . $file['att_downloads'] . '">' . $file['att_name'] . '</file>';
        }

        return $files;
    }

    function _getPages ($iStart, $iNum, $iPerPage)
    {
        $p = '';
        for ($i = 0 ; $i < $iNum ; $i += $iPerPage)
            if (0 >= $i || ($iNum - $iPerPage) <= $i || ($i >= ($iStart - 2*$iPerPage) && $i <= ($iStart + 2*$iPerPage)))
                $p .= '<p c="' . (($iStart >= $i && $iStart < ($i + $iPerPage)) ? 1 : 0 ). '" start="' . $i . '">' . ($i/$iPerPage + 1) . '</p>';
        return $p;
    }

}
