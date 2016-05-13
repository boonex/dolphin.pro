<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');
class BxBlogsPageHome extends BxDolPageView
{
    var $oBlogs;

    function __construct(&$oBlogs)
    {
        parent::__construct('bx_blogs_home');
        $this->oBlogs = &$oBlogs;
    }

    function getBlockCode_Top()
    {
        return $this->oBlogs->GenBlogLists('top', false);
    }

    function getBlockCode_Latest($iBlockId)
    {
        $s = $this->oBlogs->serviceBlogsIndexPage(false, $this->oBlogs->_oConfig->getPerPage('home'));
        return $s ? $s : array(MsgBox(_t('_Empty')));
    }

    function getBlockCode_Calendar($iBlockID, $sContent)
    {
        return $this->oBlogs->GenBlogCalendar(true, $iBlockID, $this->oBlogs->genBlogLink('home'));
    }
}
