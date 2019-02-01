<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'admin.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');

bx_import('BxTemplCmtsView');
bx_import('BxDolPaginate');
bx_import('BxDolCategories');
bx_import('BxDolModule');
bx_import('BxDolPageView');

require_once('BxBlogsPrivacy.php');

if (!defined('BX_BLOGS_IMAGES_PATH')) {
    define('BX_BLOGS_IMAGES_PATH', BX_DIRECTORY_PATH_ROOT . "media/images/blog/");
}

if (!defined('BX_BLOGS_IMAGES_URL')) {
    define('BX_BLOGS_IMAGES_URL', BX_DOL_URL_ROOT . "media/images/blog/");
}

class BxDolBlogsPageView extends BxDolPageView
{
    var $oBlogs;

    function __construct(&$oBlogs)
    {
        $this->oBlogs = &$oBlogs;
        parent::__construct('bx_blogs');
    }

    function getBlockCode_PostActions()
    {
        return $this->oBlogs->getActionsBlock();
    }

    function getBlockCode_PostRate()
    {
        return $this->oBlogs->getRateBlock();
    }

    function getBlockCode_PostOverview()
    {
        return array($this->oBlogs->getPostOverviewBlock());
    }

    function getBlockCode_PostCategories()
    {
        return $this->oBlogs->getPostCategoriesBlock();
    }

    function getBlockCode_PostFeature()
    {
        return $this->oBlogs->getPostFeatureBlock();
    }

    function getBlockCode_PostTags()
    {
        return $this->oBlogs->getPostTagsBlock();
    }

    function getBlockCode_PostComments()
    {
        return $this->oBlogs->getCommentsBlock();
    }

    function getBlockCode_PostView()
    {
        return $this->oBlogs->getBlogPostBlock();
    }

    function getBlockCode_PostSocialSharing()
    {
        return $this->oBlogs->getPostSocialSharingBlock();
    }
}

/**
 * Blogs module by BoonEx
 *
 * This module allow user to keep blog.
 *
 * Example of using this module to get any member blog page:
 *
 * bx_import('BxDolModuleDb');
 * require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/blogs/classes/BxBlogsModule.php');
 * $oModuleDb = new BxDolModuleDb();
 * $aModule = $oModuleDb->getModuleByUri('blogs');
 * $oBlogs = new BxBlogsModule($aModule);
 * echo $oBlogs->GenMemberBlog($iMemberID);
 *
 *
 *
 * Profile's Wall:
 * 'create' event is displayed on profile's wall
 *
 *
 *
 * Spy:
 * 'create' event is displayed in spy
 *
 *
 *
 * Memberships/ACL:
 * View blog - BX_BLOG_VIEW
 * View posts - BX_BLOG_POST_VIEW
 * Browse blogs - BX_BLOGS_BROWSE
 * Browse posts - BX_BLOGS_POSTS_BROWSE
 * Use search and view search results - BX_BLOG_POST_SEARCH
 * Add posts - BX_BLOG_POST_ADD
 * Edit any post (as admin) - BX_BLOG_POSTS_EDIT_ANY_POST
 * Delete any post (as admin) - BX_BLOG_POSTS_DELETE_ANY_POST
 * Approve any post (as admin) - BX_BLOG_POSTS_APPROVING
 *
 *
 *
 * Service methods:
 *
 * Delete blog (carefully with it)
 * @see BxBlogsModule::serviceActionDeleteBlog
 * BxDolService::call('blogs', 'action_delete_blog', array());
 *
 * Blogs block for index page (as PHP function)
 * @see BxBlogsModule::serviceBlogsIndexPage
 * BxDolService::call('blogs', 'blogs_index_page', array());
 *
 * Blogs block for profile page (as PHP function)
 * @see BxBlogsModule::serviceBlogsProfilePage
 * BxDolService::call('blogs', 'blogs_profile_page', array($_iProfileID));
 *
 * Generation of member RSS feeds
 * @see BxBlogsModule::serviceBlogsRss
 * BxDolService::call('blogs', 'blogs_rss', array());
 *
 * Get common css
 * @see BxBlogsModule::serviceGetCommonCss
 * BxDolService::call('blogs', 'get_common_css', array());
 *
 * Get member menu item - my content
 * @see BxBlogsModule::serviceGetMemberMenuItem
 * BxDolService::call('blogs', 'get_member_menu_item');
 *
 * Get member menu item - add content
 * @see BxBlogsModule::serviceGetMemberMenuItemAddContent
 * BxDolService::call('blogs', 'get_member_menu_item_add_content');
 *
 * Get number of posts for particular member
 * @see BxBlogsModule::serviceGetPostsCountForMember
 * BxDolService::call('blogs', 'get_posts_count_for_member', array($iMemberId));
 *
 * Get Spy data
 * @see BxBlogsModule::serviceGetSpyData
 * BxDolService::call('blogs', 'get_spy_data', array());
 *
 * Get Spy blog post
 * @see BxBlogsModule::serviceGetSpyPost
 * BxDolService::call('blogs', 'get_spy_post', array($sAction, $iObjectId, $iSenderId));
 *
 *
 *
 * Alerts:
 * Alerts type/unit - 'bx_blogs'
 * The following alerts are rised
 *
 *  view_post - view post
 *      $this->iViewingPostID - viewing post id
 *      $this->_iVisitorID - visitor id
 *
 *  create - creating of new post
 *      $iPostID - post id (for new post - 0)
 *      $iPostOwnerID - post owner id
 *
 *  edit_post - editing of existed post
 *      $iPostID - post id
 *      $iPostOwnerID - post owner id
 *
 *  delete_post - deleting of existed post
 *      $iPostID - post id
 *      $iPostOwnerID - post owner id
 *
 */
class BxBlogsModule extends BxDolModule
{
    //variables

    //max sizes of pictures for resizing during upload
    var $iIconSize;
    var $iThumbSize;
    var $iBigThumbSize;
    var $iImgSize;

    //admin mode, can All actions
    var $bAdminMode;

    //path to spacer image
    var $sSpacerPath;

    var $iLastPostedPostID;

    var $iPostViewType;
    var $iViewingPostID;
    var $aViewingPostInfo;

    var $sHomeUrl;
    var $sHomePath;

    var $oPrivacy;
    var $_iVisitorID;
    var $_sPageHeader;

    // Constructor
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->iIconSize = (int)getParam('bx_blogs_iconsize');
        $this->iThumbSize = (int)getParam('bx_blogs_thumbsize');
        $this->iBigThumbSize = (int)getParam('bx_blogs_bigthumbsize');
        $this->iImgSize = (int)getParam('bx_blogs_imagesize');

        $this->sHomeUrl = $this->_oConfig->getHomeUrl();
        $this->sHomePath = $this->_oConfig->getHomePath();

        $this->_iVisitorID = isLogged() ? getLoggedId() : 0;

        //temple
        $this->bAdminMode = $this->isAdmin() == true;

        $this->iPostViewType = 1;
        $this->iViewingPostID = -1;
        $this->iLastPostedPostID = -1;
        $this->aViewingPostInfo = array();

        $this->sSpacerPath = getTemplateIcon('spacer.gif');

        $this->_sPageHeader = '';

        $this->oPrivacy = new BxBlogsPrivacy($this);
    }

    function CheckLogged()
    {
        if (!getLoggedId()) {
            member_auth(0);
        }
    }

    /**
     * Return string for Header, depends at POST params
     *
     * @return Textpresentation of data
     */
    function GetHeaderString()
    {
        switch (bx_get('action')) {
            case 'home':
                $sCaption = _t('_bx_blog_Blogs_Home');
                break;
            case 'all':
                $sCaption = _t('_bx_blog_All_Blogs');
                break;
            case 'tags':
                $sCaption = _t('_Tags');
                break;
            case 'all_posts':
                $sCaption = _t('_bx_blog_All_Posts');
                break;
            case 'top_posts':
                $sCaption = _t('_bx_blog_Top_Posts');
                break;
            case 'popular_posts':
                $sCaption = _t('_bx_blog_Popular_Posts');
                break;
            case 'featured_posts':
                $sCaption = _t('_bx_blog_Featured_Posts');
                break;
            /*case 'show_categories':
                $sCaption = _t('_bx_blog_Categories');
                break;*/
            case 'show_calendar':
                $sCaption = _t('_bx_blog_Calendar');
                break;
            case 'show_calendar_day' :
                $sDate = bx_get('date');
                $aDate = explode('/', $sDate);

                $iValue1 = (int)$aDate[0];
                $iValue2 = (int)$aDate[1];
                $iValue3 = (int)$aDate[2];

                $sCaption = _t('_bx_blog_caption_browse_by_day')
                    . getLocaleDate(strtotime("{$iValue1}-{$iValue2}-{$iValue3}")
                        , BX_DOL_LOCALE_DATE_SHORT);
                break;
            case 'add_category':
                $sCaption = _t('_Add Category');
                break;
            case 'new_post':
                $sCaption = _t('_Add Post');
                break;
            case 'show_member_blog':
                $sCaption = _t('_bx_blog_My_blog');
                $iMemberID = $this->defineUserId();
                if (!$iMemberID) {
                    $GLOBALS['oTopMenu']->setCustomSubHeader(_t('_sys_request_page_not_found_cpt'));
                    $GLOBALS['oSysTemplate']->displayPageNotFound();
                } else {
                    $sUser = getNickName($iMemberID);
                    $sAsBlog = _t('_bx_blog_Members_blog', $sUser);
                }
                //$GLOBALS['oTopMenu']->setCustomSubHeader($sAsBlog);
                $sCaption = $sAsBlog;
                break;
            case 'edit_post':
                $sCaption = _t('_Post');
                if (false !== bx_get('EditPostID')) {
                    $iPostID = (int)bx_get('EditPostID');
                    $sCaption = htmlspecialchars($this->_oDb->getPostCaptionByID($iPostID));
                }
                break;
            case 'show_member_post':
                $sCaption = _t('_Post');
                if (false !== bx_get('postUri')) {
                    $sPostUri = process_db_input(bx_get('postUri'), BX_TAGS_STRIP);
                    $sPostCapt = $this->_oDb->getPostCaptionByUri($sPostUri);
                } elseif (false !== bx_get('post_id')) {
                    $iPostID = (int)bx_get('post_id');
                    $sPostCapt = $this->_oDb->getPostCaptionByID($iPostID);
                }
                if ($sPostCapt != '') {
                    $sCaption = htmlspecialchars($sPostCapt);
                }
                break;
            case 'search_by_tag':
                $sCaption = _t('_Search result');
                break;
            case 'my_page':
                switch (bx_get('mode')) {
                    case 'add':
                        $sCaption = _t('_bx_blog_Add') . ' ' . _t('_Post');
                        break;
                    case 'manage':
                        $sCaption = _t('_bx_blog_Manage');
                        break;
                    case 'pending':
                        $sCaption = _t('_bx_blog_pending_approval');
                        break;
                    default:
                        $sCaption = _t('_bx_blog_My_blog');
                        break;
                }
                break;
            default:
                $sCaption = _t('_bx_blog_Blogs');
                break;
        }
        $this->_sPageHeader = $sCaption;

        return $sCaption;
    }

    /**
     * Generate common forms and includes js
     *
     * @return void
     */
    function GenCommandForms()
    {
        $this->_oTemplate->addJs('main.js');
    }

    // ================================== permissions
    function isAllowedComments(&$aBlogPost)
    {
        if (($aBlogPost['OwnerID'] == $this->_iVisitorID && isMember()) || $this->isAdmin()) {
            return true;
        }

        return $this->oPrivacy->check('comment', $aBlogPost['PostID'], $this->_iVisitorID);
    }

    function isAllowedCreatorCommentsDeleteAndEdit(&$aBlogPost, $isPerformAction = false)
    {
        if ($this->isAdmin()) {
            return true;
        }
        if (!isMember() || $aBlogPost['OwnerID'] != $this->_iVisitorID) {
            return false;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_POSTS_COMMENTS_DELETE_AND_EDIT, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedBlogView($iOwnerID, $isPerformAction = false)
    {
        if ($this->isAdmin() || $iOwnerID == $this->_iVisitorID) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_VIEW, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedBlogPostView($iOwnerID, $isPerformAction = false)
    {
        if ($this->isAdmin() || $iOwnerID == $this->_iVisitorID) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_POST_VIEW, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedBlogsBrowse($isPerformAction = false)
    {
        if ($this->isAdmin()) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOGS_BROWSE, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedBlogsPostsBrowse($isPerformAction = false)
    {
        if ($this->isAdmin()) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOGS_POSTS_BROWSE, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedBlogPostSearch($isPerformAction = false)
    {
        if ($this->isAdmin()) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_POST_SEARCH, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedPostAdd($isPerformAction = false)
    {
        if ($this->isAdmin()) {
            return true;
        }
        if (isMember() == false) {
            return false;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_POST_ADD, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedPostEdit($iOwnerID, $isPerformAction = false)
    {
        if ($this->isAdmin() || (isMember() && $iOwnerID == $this->_iVisitorID)) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_POSTS_EDIT_ANY_POST, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedPostDelete($iOwnerID, $isPerformAction = false)
    {
        if ($this->isAdmin() || (isMember() && $iOwnerID == $this->_iVisitorID)) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_POSTS_DELETE_ANY_POST, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedApprove($isPerformAction = false)
    {
        if ($this->isAdmin()) {
            return true;
        }
        if (isMember() == false) {
            return false;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iVisitorID, BX_BLOG_POSTS_APPROVING, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedShare(&$aDataEntry)
    {
        if ($aDataEntry['allowView'] != BX_DOL_PG_ALL) {
            return false;
        }

        return true;
    }

    function isAdmin()
    {
        return isAdmin($this->_iVisitorID) || isModerator($this->_iVisitorID);
    }

    function _defineActions()
    {
        defineMembershipActions(array(
            'blog view',
            'blog post view',
            'blogs browse',
            'blogs posts browse',
            'blog post search',
            'blog post add',
            'blog posts edit any post',
            'blog posts delete any post',
            'blog posts approving',
            'blog posts comments delete and edit'
        ));
    }

    function GenBlogAdminIndex()
    {
        if ($this->bAdminMode) {
            //actions
            if (bx_get('action_approve') && is_array(bx_get('bposts'))) {
                foreach (bx_get('bposts') as $iBPostID) {
                    if ($this->_oDb->setPostStatus((int)$iBPostID, 'approval')) {
                        $this->onPostApproveDisapprove($iBPostID, true);
                    }
                }
            } elseif (bx_get('action_disapprove') && is_array(bx_get('bposts'))) {
                foreach (bx_get('bposts') as $iBPostID) {
                    if ($this->_oDb->setPostStatus((int)$iBPostID)) {
                        $this->onPostApproveDisapprove($iBPostID, false);
                    }
                }
            } elseif (bx_get('action_delete') && is_array(bx_get('bposts'))) {
                foreach (bx_get('bposts') as $iBPostID) {
                    $this->ActionDeletePost((int)$iBPostID);
                }
            }

            $sPostLink = $this->sHomeUrl . $this->_oConfig->sAdminExFile;

            require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
            $oBlogSearch = new BxBlogsSearchUnit();
            $oBlogSearch->PerformObligatoryInit($this, 4);
            $oBlogSearch->aCurrent['restriction']['activeStatus'] = array(
                'value'    => 'disapproval',
                'field'    => 'PostStatus',
                'operator' => '='
            );
            $oBlogSearch->bShowCheckboxes = true;
            $oBlogSearch->aCurrent['paginate']['perPage'] = $this->_oConfig->getPerPage();
            $sPosts = $oBlogSearch->displayResultBlock();
            $sPosts = ($oBlogSearch->aCurrent['paginate']['totalNum'] == 0) ? MsgBox(_t('_bx_blog_No_blogs_available')) : $sPosts;

            $sAdmPanel = $oBlogSearch->showAdminActionsPanel('bposts_box', array(
                'action_approve'    => '_Approve',
                'action_disapprove' => '_Disapprove',
                'action_delete'     => '_Delete'
            ), 'bposts');

            $oBlogSearch->aCurrent['paginate']['page_url'] = $sPostLink;
            $sPostPagination = $oBlogSearch->showPagination3();

            $aVariables = array(
                'admin_page'  => $sPostLink,
                'units'       => $sPosts,
                'paginate'    => $sPostPagination,
                'admin_panel' => $sAdmPanel,
            );

            return $this->_oTemplate->parseHtmlByTemplateName('admin_page', $aVariables);
        }
    }

    function GenBlogHome()
    {
        bx_import('PageHome', $this->_aModule);
        $oHomePageView = new BxBlogsPageHome($this);

        return $oHomePageView->getCode();
    }

    /**
     * Generate List of Blogs
     *
     * @param $sType - tyle of list ('top', 'last')
     * @return HTML presentation of data
     */
    function GenBlogLists($sType = '', $bBlock = true)
    {
        if (!$this->isAllowedBlogsBrowse()) {
            return $this->_oTemplate->displayAccessDenied();
        }

        // lang keys
        $sPostsC = _t('_bx_blog_Posts');
        $sNoBlogsC = _t('_Empty');
        $sAllBlogsC = _t('_bx_blog_All_Blogs');
        $sTopBlogsC = _t('_bx_blog_Top_Blogs');

        $iCheckedMemberID = $this->_iVisitorID;

        //////////////////pagination addition//////////////////////////
        //number elements for per page
        $iPerPage = (false !== bx_get('per_page')) ? (int)bx_get('per_page') : 10;

        if ($iPerPage > 100) {
            $iPerPage = 100;
        }

        $iCurPage = (false !== bx_get('page')) ? (int)bx_get('page') : 1;
        if ($iCurPage < 1) {
            $iCurPage = 1;
        }
        $sLimitFrom = ($iCurPage - 1) * $iPerPage;
        $sqlLimit = "LIMIT {$sLimitFrom}, {$iPerPage}";
        ////////////////////////////
        $sCaption = $sAllBlogsC;

        $sStatusFilter = ($this->isAdmin() == true
            || $this->isAllowedApprove() || $this->isAllowedPostEdit(-1)
            || $this->isAllowedPostDelete(-1))
            ? '1'
            : "`PostStatus`='approval'";

        switch ($sType) {
            case 'top':
                $vBlogsRes = $this->_oDb->getTopBlogs($sStatusFilter, $sqlLimit);
                $sCaption = $sTopBlogsC;
                break;
            case 'last':
            default:
                $vBlogsRes = $this->_oDb->getLastBlogs($sStatusFilter, $sqlLimit);
                break;
        }

        $iTotalBlogs = $this->_oDb->getAllBlogsCnt($sStatusFilter);

        // process database queries
        $iTotalNum = $vBlogsRes->rowCount();
        if ($iTotalNum == 0) {
            $sCode = MsgBox($sNoBlogsC);

            return $bBlock ? DesignBoxContent($sCaption, $sCode, 1) : $sCode;
        }

        $iGenPostsCnt = 0;
        while ($aBlogsRes = $vBlogsRes->fetch()) {
            if ($aBlogsRes['PostCount'] == 0 && $sType == 'top') //in Top blogs skip posts with 0 comments
            {
                continue;
            }

            $aOwnerInfo = getProfileInfo($aBlogsRes['OwnerID']);
            $sOwnerNickname = getNickName($aBlogsRes['OwnerID']);
            if ($aBlogsRes['OwnerID'] == 0) {
                $sOwnerNickname = _t('_Admin');
            }
            if ($sOwnerNickname) {
                $sCont = get_member_thumbnail($aBlogsRes['OwnerID'], 'left');
                $sBlogOwnerLink = $this->genBlogLink('show_member_blog',
                    array('Permalink' => $aOwnerInfo['NickName'], 'Link' => $aBlogsRes['OwnerID']));
                $sDescription = htmlspecialchars(strip_tags($aBlogsRes['Description']));

                $aBlogUnitVariables = array(
                    'owner_thumbnail'  => $sCont,
                    'owner_nickname'   => $sOwnerNickname . ' ' . _t('_bx_blog_Blog'),
                    'posts_count'      => $aBlogsRes['PostCount'] . ' ' . $sPostsC,
                    'blog_link'        => $sBlogOwnerLink,
                    'blog_description' => $sDescription
                );
                $sRetHtml .= $this->_oTemplate->parseHtmlByTemplateName('blog_unit', $aBlogUnitVariables);

                $iGenPostsCnt++;
            }
        }

        /////////pagination addition//////////////////
        if ($this->isPermalinkEnabled() == false) {
            $sRequest = bx_html_attribute($_SERVER['PHP_SELF']) . '?action=top_blogs&page={page}&per_page={per_page}';
        } else {
            $sRequest = (bx_get('action') == 'top_blogs')
                ? BX_DOL_URL_ROOT . 'blogs/top/'
                : BX_DOL_URL_ROOT . 'blogs/all/';

            $sRequest .= '{per_page}/{page}' . $sPaginAddon;
        }
        ///////////////////////////
        $oPaginate = new BxDolPaginate
        (
            array
            (
                'page_url' => $sRequest,
                'count'    => $iTotalBlogs,
                'per_page' => $iPerPage,
                'page'     => $iCurPage,
            )
        );

        $sPagination = $oPaginate->getPaginate();

        $sRetHtmlVal = <<<EOF
<div class="bx-def-bc-padding">
    {$sRetHtml}
</div>
{$sPagination}
EOF;

        return $bBlock ? DesignBoxContent($sCaption, $sRetHtmlVal, 1) : $sRetHtmlVal;
    }

    /**
     * Generate List of Posts for mobile frontend
     *
     * @param $iAuthor - display posts of provided user only
     * @param $sMode - display all latest[default], featured or top posts
     * @return HTML presentation of data
     */
    function GenPostListMobile($iAuthor = 0, $sMode = false)
    {
        if ($this->_iVisitorID) // some workaround for mobile apps, to force login
        {
            bx_login($this->_iVisitorID);
        }

        bx_import('BxDolMobileTemplate');
        $oMobileTemplate = new BxDolMobileTemplate($this->_oConfig, $this->_oDb);
        $oMobileTemplate->pageStart();
        echo $oMobileTemplate->addCss('blogs_common.css', 1);

        $iPerPage = 10;
        $iPage = (int)bx_get('page');
        if ($iPage < 1) {
            $iPage = 1;
        }

        $this->iPostViewType = 4;

        $sOrder = 'last';
        $sMobileWrapper = 'mobile_row.html';
        $aParams = array();
        switch ($sMode) {
            case 'post':
                $aViewingPostInfo = $this->_oDb->getPostInfo((int)bx_get('id'));
                if (!$this->oPrivacy->check('view', (int)bx_get('id'),
                        $this->_iVisitorID) || !$this->isAllowedBlogPostView($aViewingPostInfo['OwnerID'], true)
                ) {
                    $oMobileTemplate->displayAccessDenied($sCaption);

                    return;
                }
                $this->iPostViewType = 3;
                $aParams = array('id' => (int)bx_get('id'));
                $sCaption = _t('_bx_blog_post_view');
                $sMobileWrapper = 'mobile_box.html';
                echo $oMobileTemplate->addCss('blogs.css', 1);
                break;
            case 'user':
                $aParams = array('id' => (int)bx_get('id'));
                $sCaption = _t('_bx_blog_Members_blog', getNickName((int)bx_get('id')));
                break;
            case 'featured':
                $sCaption = _t('_bx_blog_Featured_Posts');
                break;
            case 'top':
                $sOrder = 'top';
                $sCaption = _t('_bx_blog_Top_Posts');
                break;
            case 'popular':
                $sOrder = 'popular';
                $sCaption = _t('_bx_blog_Popular_Posts');
                break;
            case 'last':
            default:
                $sMode = 'last';
                $sCaption = _t('_bx_blog_Latest_posts');
        }

        if ('post' != $sMode && !$this->isAllowedBlogsPostsBrowse()) {
            $oMobileTemplate->displayAccessDenied($sCaption);

            return;
        }

        $oTmpBlogSearch = false;
        $sCode = $this->_GenPosts($this->iPostViewType, $iPerPage, $sMode, $aParams, $sOrder, $oBlogSearchResults,
            $sMobileWrapper);
        if (!$sCode || $oBlogSearchResults->aCurrent['paginate']['totalNum'] == 0) {
            $oMobileTemplate->displayNoData($sCaption);

            return;
        }

        echo $sCode;

        if ($sMode != 'post') {
            bx_import('BxDolPaginate');
            $oPaginate = new BxDolPaginate(array(
                'page_url' => $this->genBlogSubUrl() . '?action=mobile&mode=' . $sMode . '&page={page}',
                'count'    => $oBlogSearchResults->aCurrent['paginate']['totalNum'],
                'per_page' => $iPerPage,
                'page'     => $iPage,
            ));
            echo $oPaginate->getMobilePaginate();
        }

        $oMobileTemplate->pageCode($sCaption, false);
    }

    /**
     * Generate List of Posts
     *
     * @param $sType - tyle of list ('top', 'last'), but now realized only Top posts
     * @return HTML presentation of data
     */
    function GenPostLists($sType = '', $bBlock = true)
    {
        $sDisplayMode = '';
        $sTypeMode = '';
        switch ($sType) {
            case 'last':
                $sDisplayMode = 'last';
                break;
            case 'popular':
                $sDisplayMode = 'popular';
                break;
            case 'featured':
                $sTypeMode = 'featured';
                $sDisplayMode = 'last';
                break;
            case 'top':
            default:
                $sDisplayMode = 'top';
                break;
        }

        $this->iPostViewType = 4;

        $sCaption = ($this->_sPageHeader != '') ? $this->_sPageHeader : _t('_bx_blog_Top_Posts');

        if (!$this->isAllowedBlogsPostsBrowse()) {
            return DesignBoxContent($sCaption, $this->_oTemplate->displayAccessDenied(), 1);
        }

        $oTmpBlogSearch = false;
        $sCode = $this->_GenPosts($this->iPostViewType, $this->_oConfig->getPerPage(), $sTypeMode, false, $sDisplayMode,
            $oTmpBlogSearch);
        if ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) {
            $sCode = MsgBox(_t('_Empty'));
        }

        $oTmpBlogSearch->aCurrent['paginate']['page_url'] = $oTmpBlogSearch->getCurrentUrl($sType . '_posts', 0, '');
        $sPagination = $oTmpBlogSearch->showPagination3();

        $sRetHtmlVal = <<<EOF
<div class="bx-def-bc-padding">
    {$sCode}
</div>
{$sPagination}
EOF;

        return $bBlock ? DesignBoxContent($sCaption, $sRetHtmlVal, 1) : $sRetHtmlVal;
    }


    /**
     * Get list of blog posts
     * @param $iPostViewType view type
     * @param $iPerPage posts per page
     * @param $sMode last[default], top, featured
     * @param $sOrder last[default], popular, top
     * @param $oSearchResult search result object will be assigned to this variable
     * @return HTML string of blog posts
     */
    function _GenPosts($iPostViewType, $iPerPage, $sMode, $aParams, $sOrder, &$oSearchResult, $sMobileWrapper = false)
    {
        bx_import('SearchUnit', $this->_aModule);

        $oTmpBlogSearch = new BxBlogsSearchUnit($this);
        $oTmpBlogSearch->PerformObligatoryInit($this, $iPostViewType, $sMobileWrapper);
        $oTmpBlogSearch->aCurrent['paginate']['perPage'] = $iPerPage;
        $oTmpBlogSearch->aCurrent['sorting'] = $sOrder;

        switch ($sMode) {
            case 'user':
                $oTmpBlogSearch->aCurrent['restriction']['owner']['value'] = (int)$aParams['id'];
                break;
            case 'post':
                $oTmpBlogSearch->aCurrent['restriction']['id']['value'] = $aParams['id'];
                if (($this->aViewingPostInfo['OwnerID'] == $this->_iVisitorID && $this->aViewingPostInfo['OwnerID'] > 0) || $this->bAdminMode) {
                    $oTmpBlogSearch->aCurrent['restriction']['activeStatus'] = '';
                }
                $oTmpBlogSearch->aCurrent['paginate']['perPage'] = 1;
                break;
            case 'featured':
                $oTmpBlogSearch->aCurrent['restriction']['featuredStatus']['value'] = 1;
                break;
            case 'category':
                $oTmpBlogSearch->aCurrent['restriction']['category_uri']['value'] = $aParams['cat_uri'];
                break;
            case 'last':
            default:
                $oTmpBlogSearch->aCurrent['restriction']['allow_view']['value'] = $this->_iVisitorID ? array(
                    BX_DOL_PG_ALL,
                    BX_DOL_PG_MEMBERS
                ) : array(BX_DOL_PG_ALL);
        }

        $oSearchResult = $oTmpBlogSearch;

        return $oTmpBlogSearch->displayResultBlock();
    }

    function GenPostsOfCategory()
    {
        $sCatUri = process_db_input(bx_get('uri'), BX_TAGS_STRIP);
        if ($sCatUri) {
            $this->iPostViewType = 4;
            $sCaption = $GLOBALS['MySQL']->unescape($sCatUri);

            $oTmpBlogSearch = false;
            $sCode = $this->_GenPosts($this->iPostViewType, $this->_oConfig->getPerPage(), 'category',
                array('cat_uri' => $sCatUri), 'last', $oTmpBlogSearch);
            if ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) {
                $sCode = MsgBox(_t('_Empty'));
            }

            $sRetHtmlVal = '<div class="bx-def-bc-padding">' . $sCode . '</div>';

            return DesignBoxContent($sCaption, $sRetHtmlVal, 1);
        }
    }

    /**
     * Generate User Right Part for Blogs
     *
     * @param $aBlogsRes - Blog Array Data
     * @param $iView - type of view(1 is short view, 2 is full view, 3 is post view(short))
     * @return HTML presentation of data
     */
    function GenMemberDescrAndCat($aBlogsRes, $sCategoryName = '')
    {
        $sSureC = _t('_Are_you_sure');
        $sApplyChangesC = _t('_Submit');

        $iMemberID = (int)$aBlogsRes['OwnerID'];
        $sOwnerNickname = getNickName($iMemberID);

        $aBlogInfo = $this->_oDb->getBlogInfo($iMemberID);
        $this->aViewingPostInfo = $aBlogInfo;

        if ($this->iViewingPostID == -1) {

            $aBlogID = (int)$aBlogInfo['ID'];
            $sWorkLink = $this->genBlogFormUrl();
            $sProcessingFile = $this->genBlogSubUrl();

            $aBlogActionKeys = array(
                'visitor_id'          => $this->_iVisitorID,
                'owner_id'            => $iMemberID,
                'owner_name'          => $sOwnerNickname,
                'blog_owner_link'     => '',//$sOwnerBlogLink,
                'admin_mode'          => "'" . $this->bAdminMode . "'",
                'sure_label'          => $sSureC,
                'work_url'            => $sProcessingFile,
                'site_url'            => BX_DOL_URL_ROOT,
                'blog_id'             => $aBlogID,
                'blog_description_js' => $sDescrAct,
            );

            $sBlogActionsVal = $GLOBALS['oFunctions']->genObjectsActions($aBlogActionKeys, 'bx_blogs_m', false);

            if (($this->_iVisitorID == $iMemberID && $iMemberID > 0) || $this->bAdminMode == true) {
                $aBlogDesc = $aBlogInfo['Description'];
                $sDescrAct = $this->ActionPrepareForEdit($aBlogDesc);
                $sBlogDescription = process_html_output($aBlogDesc);

                $sBlogActionsVal .= <<<EOF
<div id="edited_blog_div" style="display: none; position:relative;">
    <div class="bx-def-bc-padding" style="padding-top:0;">
        <form action="{$sWorkLink}" method="post" name="EditBlogForm">
            <input type="hidden" name="action" id="action" value="edit_blog" />
            <input type="hidden" name="EditBlogID" id="EditBlogID" value="" />
            <input type="hidden" name="EOwnerID" id="EOwnerID" value="" />
            <textarea name="Description" rows="3" cols="3" style="width:99%;height:50px;" onkeyup="if( this.value.length > 255 ) this.value = this.value.substr( 0, 255 );" value="{$sBlogDescription}">{$sBlogDescription}</textarea>
            <div class="bx-def-margin-sec-top">
                <input type="submit" value="{$sApplyChangesC}" class="form_input_submit bx-btn bx-btn-small" />
            </div>
            <div class="clear_both"></div>
        </form>
    </div>
</div>
EOF;
            }
        }
        $sBlogActionsSect = ($sBlogActionsVal != '') ? DesignBoxContent(_t('_Actions'), $sBlogActionsVal, 1) : '';

        $sDescriptionSect = DesignBoxContent(_t('_Overview'), $this->getPostOverviewBlock(), 1);
        $sCategoriesSect = $this->getPostCategoriesBlock();
        $sTagsSect = DesignBoxContent(_t('_Tags'), $this->getPostTagsBlock(), 1);

        $sFeaturedSectCont = $this->getPostFeatureBlock();
        $sFeaturedSect = ($sFeaturedSectCont) ? DesignBoxContent(_t('_bx_blog_Featured_Posts'),
            $this->getPostFeatureBlock(), 1) : '';

        return $sBlogActionsSect . $sActionsSect . $sDescriptionSect . $sCategoriesSect . $sFeaturedSect . $sTagsSect;
    }

    /**
     * Generate User`s Blog Page
     *
     * @param $iUserID - User ID
     * @return HTML presentation of data
     */
    function GenMemberBlog($iUserID = 0)
    {
        $iCheckedMemberID = $this->_iVisitorID;

        $sRetHtml = '';
        $sBlogPosts = '';
        $iMemberID = $this->defineUserId();

        if ($iUserID > 0) {
            $iMemberID = $iUserID;
        }

        $GLOBALS['oTopMenu']->setCurrentProfileID($iMemberID);

        $sCategoryName = $this->defineCategoryName();

        $aBlogsRes = $this->_oDb->getBlogInfo($iMemberID);

        if (!$aBlogsRes) {
            if (($iMemberID == $iCheckedMemberID && $iCheckedMemberID > 0) || $this->isAdmin()) {
                return $this->GenCreateBlogForm();
            } else {
                return DesignBoxContent($this->_sPageHeader, MsgBox(_t('_Empty')), 1);
            }
        }

        $iOwnerID = $aBlogsRes['OwnerID'];
        if ((!$this->_iVisitorID || $iOwnerID != $this->_iVisitorID) && !$this->isAllowedBlogView($iOwnerID, true)) {
            return $this->_oTemplate->displayAccessDenied();
        }

        if ($aBlogsRes['OwnerID'] > 0) {
            $sUser = getNickName($aBlogsRes['OwnerID']);
            $aUserInfo = getProfileInfo($aBlogsRes['OwnerID']);
        } else {
            $sUser = _t('_Admin');
            $aUserInfo = array('NickName' => _t('_Admin'));
        }
        $sOwnerBlogLink = $this->genBlogLink('show_member_blog_home',
            array('Permalink' => $aUserInfo['NickName'], 'Link' => $aBlogsRes['OwnerID']));
        $sAsBlog = _t('_bx_blog_Members_blog', $sUser);

        $sHome = $this->genBlogFormUrl();
        $sCurCategory = $sCategoryName;

        $sBreadCrumb = ($sCurCategory != '') ? $sCurCategory : _t('_bx_blog_Latest_posts');

        if (!$this->isAllowedBlogsPostsBrowse()) {
            $sBlogPostsHtmlVal = $this->_oTemplate->displayAccessDenied();
        } else {
            require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
            $oTmpBlogSearch = new BxBlogsSearchUnit($this);
            $oTmpBlogSearch->PerformObligatoryInit($this, 4);
            $oTmpBlogSearch->aCurrent['sorting'] = 'last';
            $oTmpBlogSearch->aCurrent['paginate']['perPage'] = $this->_oConfig->getPerPage();
            $oTmpBlogSearch->aCurrent['restriction']['owner']['value'] = $iMemberID;

            if ($sCategoryName != '') {

                $oTmpBlogSearch->aCurrent['join']['category'] = array(
                    'type'       => 'left',
                    'table'      => 'sys_categories',
                    'mainField'  => 'PostID',
                    'onField'    => 'ID',
                    'joinFields' => array('Category')
                );
                $oTmpBlogSearch->aCurrent['restriction']['category'] = array(
                    'field'    => 'Category',
                    'operator' => '=',
                    'table'    => 'sys_categories',
                    'value'    => $sCategoryName
                );
                $oTmpBlogSearch->aCurrent['restriction']['category_type'] = array(
                    'field'    => 'Type',
                    'operator' => '=',
                    'table'    => 'sys_categories',
                    'value'    => 'bx_blogs',
                );
                $oTmpBlogSearch->aCurrent['restriction']['category_owner'] = array(
                    'field'    => 'Owner',
                    'operator' => '=',
                    'table'    => 'sys_categories',
                    'value'    => $iMemberID,
                );
            }
            if (($this->_iVisitorID == $iMemberID && $iMemberID > 0) || $this->isAdmin() == true) {
                $oTmpBlogSearch->aCurrent['restriction']['activeStatus'] = '';
            }
            $sBlogPostsVal = $oTmpBlogSearch->displayResultBlock();
            $sBlogPostsVal = ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) ? MsgBox(_t('_Empty')) : $sBlogPostsVal;

            // Prepare link to pagination
            if ($this->bUseFriendlyLinks == false || $this->bAdminMode == true) { //old variant
                $sCategUrlAdd = $sCategoryName ? "category={$sCategoryName}" : '';
                $sRequest = bx_append_url_params($sOwnerBlogLink, $sCategUrlAdd . '&page={page}&per_page={per_page}');
            } else {
                $sCategUrlAdd = $sCategoryName ? "/category/{$sCategoryName}" : '';
                $sRequest = bx_append_url_params($sOwnerBlogLink . $sCategUrlAdd, 'page={page}&per_page={per_page}');
            }

            // End of prepare link to pagination
            $oTmpBlogSearch->aCurrent['paginate']['page_url'] = $sRequest;

            $sPagination = $oTmpBlogSearch->showPagination3();
            $sBlogPostsHtmlVal = <<<EOF
<div class="bx-def-bc-padding">
    {$sBlogPostsVal}
</div>
{$sPagination}
EOF;
        }

        $sPostsSect = DesignBoxContent($sBreadCrumb, $sBlogPostsHtmlVal, 1);
        $sRightSect = $this->GenMemberDescrAndCat($aBlogsRes, $sCategoryName);
        $sRetHtml = $this->Templater($sPostsSect, $sRightSect);

        return $sRetHtml;
    }

    function ActionDelImg()
    {
        $this->CheckLogged();

        $sSuccUpdPost = _t('_bx_blog_Post_succ_updated');
        $sFailUpdPost = _t('_bx_blog_Post_fail_updated');

        $iPostID = (int)bx_get('post_id');
        $iPostOwnerID = $this->_oDb->getPostOwnerByID($iPostID);

        if ((($this->_iVisitorID == $iPostOwnerID && $iPostOwnerID > 0) || $this->bAdminMode) && $iPostID > 0) {
            $sFileNameExt = '';
            $sFileName = $this->_oDb->getPostPhotoByID($iPostID);
            if ($sFileName == '') {
                $sFileName = 'blog_' . $iPostID;
            }
            $sDFilePath = BX_BLOGS_IMAGES_PATH . "small_{$sFileName}";
            @unlink($sDFilePath);
            $sDFilePath = BX_BLOGS_IMAGES_PATH . "big_{$sFileName}";
            @unlink($sDFilePath);
            $sDFilePath = BX_BLOGS_IMAGES_PATH . "orig_{$sFileName}";
            @unlink($sDFilePath);

            $vSqlRes = $this->_oDb->performUpdatePostWithPhoto($iPostID);
            $sRet = (db_affected_rows($vSqlRes) > 0) ? _t($sSuccUpdPost) : _t($sFailUpdPost);
            print 1;

            return MsgBox($sRet);
        } elseif ($this->_iVisitorID != $iPostOwnerID) {
            print MsgBox(_t('_Access denied'));

            return MsgBox(_t('_Access denied'));
        } else {
            print MsgBox(_t('_Error Occured'));

            return MsgBox(_t('_Error Occured'));
        }
    }

    /**
     * SQL: Delete post by POSTed data
     *
     * @return MsgBox of result
     */
    function ActionDeletePost($iPostID = 0)
    {
        if (!$this->bAdminMode) {
            $this->CheckLogged();
        }

        if ($iPostID == 0) {
            $iPostID = (int)bx_get('DeletePostID');
        }

        $iPostOwnerID = $this->_oDb->getPostOwnerByID($iPostID);

        if (!$this->isAllowedPostDelete($iPostOwnerID)) {
            return $this->_oTemplate->displayAccessDenied();
        }

        if ($iPostID > 0) {
            $oCmts = new BxDolCmts($this->_oConfig->getCommentSystemName(), (int)$iPostID);
            $oCmts->onObjectDelete();

            // delete votings
            bx_import('BxTemplVotingView');
            $oVoting = new BxTemplVotingView ($this->_oConfig->getRateSystemName(), $iPostID);
            $oVoting->deleteVotings($iPostID);

            $sFileName = $this->_oDb->getPostPhotoByID($iPostID);
            $sFilePathPost = 'big_' . $sFileName;
            if ($sFilePathPost != '' && file_exists(BX_BLOGS_IMAGES_PATH . $sFilePathPost) && is_file(BX_BLOGS_IMAGES_PATH . $sFilePathPost)) {
                @unlink(BX_BLOGS_IMAGES_PATH . $sFilePathPost);
            }
            $sFilePathPost = 'small_' . $sFileName;
            if ($sFilePathPost != '' && file_exists(BX_BLOGS_IMAGES_PATH . $sFilePathPost) && is_file(BX_BLOGS_IMAGES_PATH . $sFilePathPost)) {
                @unlink(BX_BLOGS_IMAGES_PATH . $sFilePathPost);
            }
            $sFilePathPost = 'orig_' . $sFileName;
            if ($sFilePathPost != '' && file_exists(BX_BLOGS_IMAGES_PATH . $sFilePathPost) && is_file(BX_BLOGS_IMAGES_PATH . $sFilePathPost)) {
                @unlink(BX_BLOGS_IMAGES_PATH . $sFilePathPost);
            }

            $vSqlRes = $this->_oDb->deletePost($iPostID);
            $sRet = (db_affected_rows($vSqlRes) > 0) ? _t('_post_successfully_deleted') : _t('_failed_to_delete_post');

            $this->isAllowedPostDelete($iPostOwnerID, true); // perform action

            //reparse tags
            bx_import('BxDolTags');
            $oTags = new BxDolTags();
            $oTags->reparseObjTags('blog', $iPostID);

            //reparse categories
            $oCategories = new BxDolCategories();
            $oCategories->reparseObjTags('bx_blogs', $iPostID);

            // delete views
            bx_import('BxDolViews');
            $oViews = new BxDolViews($this->_oConfig->getViewSystemName(), $iPostID, false);
            $oViews->onObjectDelete();

            //delete all subscriptions
            $oSubscription = BxDolSubscription::getInstance();
            $oSubscription->unsubscribe(array('type' => 'object_id', 'unit' => 'bx_blogs', 'object_id' => $iPostID));

            bx_import('BxDolAlerts');
            $oZ = new BxDolAlerts('bx_blogs', 'delete_post', $iPostID, $iPostOwnerID);
            $oZ->alert();

            return MsgBox($sRet);
        } else {
            return MsgBox(_t('_Error Occured'));
        }
    }

    function getViewingPostInfo()
    {
        if ($this->iViewingPostID < 0) {
            if (false !== bx_get('postUri')) {
                $sPostUri = process_db_input(bx_get('postUri'), BX_TAGS_STRIP);
                $this->iViewingPostID = (int)$this->_oDb->getPostIDByUri($sPostUri);
            } elseif (false !== bx_get('post_id')) {
                $this->iViewingPostID = (int)bx_get('post_id');
            }
        }
        if ($this->iViewingPostID) {
            $this->aViewingPostInfo = $this->_oDb->getPostInfo($this->iViewingPostID);

            if (is_array($this->aViewingPostInfo) && $this->aViewingPostInfo) {
                $iOwnerID = (int)$this->aViewingPostInfo['OwnerID'];

                $bPossibleToView = $this->oPrivacy->check('view', $this->iViewingPostID, $this->_iVisitorID);

                if ($this->isAllowedBlogPostView($iOwnerID, true) == false || $bPossibleToView == false) {
                    return array(
                        DesignBoxContent($this->_sPageHeader, $this->_oTemplate->displayAccessDenied(), 1),
                        false
                    );
                }

                $this->iPostViewType = 3;

                bx_import('BxDolViews');
                new BxDolViews($this->_oConfig->getViewSystemName(), $this->iViewingPostID);

                bx_import('BxDolAlerts');
                $oZ = new BxDolAlerts('bx_blogs', 'view_post', $this->iViewingPostID, $this->_iVisitorID);
                $oZ->alert();

                if ($this->aViewingPostInfo['PostPhoto'] != '' && file_exists(BX_BLOGS_IMAGES_PATH . 'small_' . $this->aViewingPostInfo['PostPhoto'])) {
                    $GLOBALS['oTopMenu']->setCustomSubIconUrl(BX_BLOGS_IMAGES_URL . 'small_' . $this->aViewingPostInfo['PostPhoto']);
                } else {
                    $GLOBALS['oTopMenu']->setCustomSubIconUrl('book');
                }

                $sPostCaption = htmlspecialchars($this->aViewingPostInfo['PostCaption']);
                $GLOBALS['oTopMenu']->setCustomSubHeader($sPostCaption);

                $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
                    _t('_bx_blog_Blogs') => $this->genBlogLink('home'),
                    $sPostCaption        => '',
                ));

                return array('', true);
            }
        }

        return array(DesignBoxContent($this->_sPageHeader, MsgBox(_t('_Empty')), 1), false);
    }

    function getBlogPostBlock()
    {
        require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
        $oTmpBlogSearch = new BxBlogsSearchUnit($this);
        $oTmpBlogSearch->PerformObligatoryInit($this, 3);
        $oTmpBlogSearch->aCurrent['restriction']['id']['value'] = $this->iViewingPostID;
        if (($this->aViewingPostInfo['OwnerID'] == $this->_iVisitorID && $this->aViewingPostInfo['OwnerID'] > 0) || $this->bAdminMode) {
            $oTmpBlogSearch->aCurrent['restriction']['activeStatus'] = '';
        }
        $oTmpBlogSearch->aCurrent['paginate']['perPage'] = 1;
        $sPostStringVal = $oTmpBlogSearch->displayResultBlock();

        // back - forward func
        $iOwnerID = (int)$this->aViewingPostInfo['OwnerID'];

        $sCategoryName = $this->defineCategoryName();
        $sCategoryUrlAdd = ($sCategoryName != '') ? "&category=" . $sCategoryName : '';
        $sStatusFilter = ($this->isAdmin() == true) ? '1' : "`PostStatus`='approval'";
        $aPostsInCategory = $this->_oDb->getPostsInCategory($sStatusFilter, $sCategoryName, $iOwnerID);

        reset($aPostsInCategory);
        $iCurKey = array_search($this->iViewingPostID, $aPostsInCategory);

        $sBackNextNav = '';
        $sMoreIcon = $this->_oTemplate->getIconUrl('more.png');
        if (isset($aPostsInCategory[$iCurKey - 1]) && $aPostsInCategory[$iCurKey - 1] > 0) {
            $iPrevUnitID = (int)$aPostsInCategory[$iCurKey - 1];
            $aPrevPostInfo = $this->_oDb->getPostCaptionAndUriByID($iPrevUnitID);
            $sPrevPostCaption = $aPrevPostInfo['PostCaption'];
            $sPrevPostUri = $aPrevPostInfo['PostUri'];
            $sUnitUrl = $this->genUrl($iPrevUnitID, $sPrevPostUri) . $sCategoryUrlAdd;
            $sBackNextNav .= <<<EOF
<i class="bot_icon_left sys-icon arrow-left"></i>
<a title="{$sPrevPostCaption}" href="{$sUnitUrl}" class="backMembers bx-def-margin-thd-left">
    {$sPrevPostCaption}
</a>
EOF;
        }
        if (isset($aPostsInCategory[$iCurKey + 1]) && $aPostsInCategory[$iCurKey + 1] > 0) {
            $iNextUnitID = (int)$aPostsInCategory[$iCurKey + 1];
            $aNextPostInfo = $this->_oDb->getPostCaptionAndUriByID($iNextUnitID);
            $sNextPostCaption = $aNextPostInfo['PostCaption'];
            $sNextPostUri = $aNextPostInfo['PostUri'];
            $sUnitUrl = $this->genUrl($iNextUnitID, $sNextPostUri) . $sCategoryUrlAdd;
            $sBackNextNav .= <<<EOF
<i class="bot_icon_right sys-icon arrow-right"></i>
<a title="{$sNextPostCaption}" href="{$sUnitUrl}" class="moreMembers bx-def-margin-thd-right">
    {$sNextPostCaption}
</a>
EOF;
        }

        $sSPaginate = <<<EOF
<div class="dbBottomMenu">
    <div class="pages_section">
        {$sBackNextNav}
    </div>
</div>
EOF;

        // end of back - forward func

        return DesignBoxContent(_t('_bx_blog_post_view'), $sPostStringVal, 11, '', $sSPaginate);
    }

    function getPostSocialSharingBlock()
    {
        if (!$this->isAllowedShare($this->aViewingPostInfo)) {
            return '';
        }

        $sUrl = $this->genUrl($this->aViewingPostInfo['PostID'], $this->aViewingPostInfo['PostUri']);
        $sTitle = $this->aViewingPostInfo['PostCaption'];
        $sImgUrl = false;
        $aCustomParams = false;
        if ($this->aViewingPostInfo['PostPhoto']) {
            $sImgUrl = BX_BLOGS_IMAGES_URL . 'orig_' . $this->aViewingPostInfo['PostPhoto'];
            $aCustomParams = array(
                'img_url'         => $sImgUrl,
                'img_url_encoded' => rawurlencode($sImgUrl)
            );
        }

        bx_import('BxTemplSocialSharing');
        $sCode = BxTemplSocialSharing::getInstance()->getCode($sUrl, $sTitle, $aCustomParams);

        return array($sCode, array(), array(), false);
    }

    function getCommentsBlock()
    {
        require_once($this->_oConfig->getClassPath() . 'BxBlogsCmts.php');
        $_oCmtsView = new BxBlogsCmts($this->_oConfig->getCommentSystemName(), $this->iViewingPostID);
        $sPostComm = $_oCmtsView->getExtraCss();
        $sPostComm .= $_oCmtsView->getExtraJs();
        $sPostComm .= (!$_oCmtsView->isEnabled()) ? MsgBox(_t('_bx_blog_Comments_is_disabled')) : $_oCmtsView->getCommentsFirst();

        return $sPostComm;
    }

    function getActionsBlock()
    {
        if ($this->iPostViewType == 3 && $this->iViewingPostID > 0) {

            $iMemberID = (int)$this->aViewingPostInfo['OwnerID'];
            $aOwnerInfo = getProfileInfo($iMemberID);
            $sOwnerNickname = getNickName($iMemberID);
            $aUser = array('Permalink' => $aOwnerInfo['NickName'], 'Link' => $iMemberID);

            $sOwnerBlogLinkSub = $this->genBlogLink('show_member_blog_home', $aUser, '', '', '', true);

            $sApproveC = _t('_Approve');
            $sDisApproveC = _t('_Disapprove');
            $sFeatureItC = _t('_Feature it');
            $sDeFeatureItC = _t('_De-Feature it');

            $bApproveAllowed = $this->isAllowedApprove() ? 'true' : 'false';

            if (($this->_iVisitorID == $iMemberID && $iMemberID > 0) || $this->bAdminMode || $bApproveAllowed) {
                $iFeaturedStatus = $this->_oDb->getFeaturedStatus($this->iViewingPostID);
                $sFeatureC = ((int)$iFeaturedStatus == 1) ? $sDeFeatureItC : $sFeatureItC;

                if ($this->bAdminMode || $bApproveAllowed == 'true') {
                    $iApproved = 0; //0 = not changed; 1 = app; 2 = disapp;
                    if (bx_get('sa') == 'approve') { //approve this post
                        $this->_oDb->setPostStatus($this->iViewingPostID, 'approval');
                        $this->onPostApproveDisapprove($this->iViewingPostID, true);
                        $iApproved = 1;
                    }
                    if (bx_get('sa') == 'disapprove') { //disapprove this post
                        $this->_oDb->setPostStatus($this->iViewingPostID);
                        $this->onPostApproveDisapprove($this->iViewingPostID, false);
                        $iApproved = 2;
                    }

                    $sCurPostStatus = $this->_oDb->getActiveStatus($this->iViewingPostID);
                    switch ($iApproved) {
                        case 0:
                            $sSAAction = ($sCurPostStatus == 'disapproval') ? 'approve' : 'disapprove';
                            $sSACaption = ($sCurPostStatus == 'disapproval') ? $sApproveC : $sDisApproveC;
                            break;
                        case 1:
                            $sSAAction = 'disapprove';
                            $sSACaption = $sDisApproveC;
                            break;
                        case 2:
                            $sSAAction = 'approve';
                            $sSACaption = $sApproveC;
                            break;
                    }
                }
            }

            $sLink = $this->genBlogLink('show_member_blog_home', $aUser);

            $sViewingPostUri = $this->_oDb->getPostUriByID($this->iViewingPostID);
            $aViewingPost = array('Permalink' => $sViewingPostUri, 'Link' => $this->iViewingPostID);
            $sViewingPostLink = $this->genBlogLink('show_member_post', $aUser, '', $aViewingPost);
            $sLink = $this->genBlogLink('show_member_post', $aUser, '', $aViewingPost, '', true);

            $sProcessingFile = $this->genBlogSubUrl();

            bx_import('BxDolSubscription');
            $oSubscription = BxDolSubscription::getInstance();
            $aButton = $oSubscription->getButton($this->_iVisitorID, 'bx_' . $this->_oConfig->getUri(), '',
                $this->iViewingPostID);
            $sSubsAddon = $oSubscription->getData();

            $aActionKeys = array(
                'edit_allowed'          => $this->isAllowedPostEdit(-1) ? 'true' : 'false',
                'visitor_id'            => $this->_iVisitorID,
                'owner_id'              => $iMemberID,
                'blog_owner_link'       => $sOwnerBlogLinkSub,
                'owner_title'           => $sOwnerNickname,
                'owner_name'            => $aOwnerInfo['NickName'],
                'admin_mode'            => "'" . $this->bAdminMode . "'",
                'post_id'               => $this->iViewingPostID,
                'post_featured'         => (int)$iFeaturedStatus,
                'sure_label'            => _t('_Are_you_sure'),
                'post_entry_url'        => $sLink,
                'post_inside_entry_url' => $sViewingPostLink,
                'sSACaption'            => $sSACaption,
                'sSAAction'             => $sSAAction,
                'work_url'              => $sProcessingFile,
                'only_menu'             => 0,
                'sbs_blogs_title'       => $aButton['title'],
                'sbs_blogs_script'      => $aButton['script'],
                'site_url'              => BX_DOL_URL_ROOT,
                'allow_approve'         => $bApproveAllowed,
                'base_url'              => $this->sHomeUrl,
                'TitleShare'            => $this->isAllowedShare($this->aViewingPostInfo) ? _t('_Share') : '',
            );

            $aActionKeys['repostCpt'] = $aActionKeys['repostScript'] = '';
	        if(BxDolRequest::serviceExists('wall', 'get_repost_js_click')) {
	        	$sSubsAddon .= BxDolService::call('wall', 'get_repost_js_script');
	
				$aActionKeys['repostCpt'] = _t('_Repost');
				$aActionKeys['repostScript'] = BxDolService::call('wall', 'get_repost_js_click', array($this->_iVisitorID, 'bx_blogs', 'create', $this->iViewingPostID));
	        }

            $sActionsVal = $GLOBALS['oFunctions']->genObjectsActions($aActionKeys, 'bx_blogs', false);

            return $sSubsAddon . $sActionsVal;
        }
    }

    function serviceGetSubscriptionParams($sAction, $iEntryId)
    {
        $aPostInfo = $this->_oDb->getPostInfo($iEntryId);
        if ($aPostInfo['OwnerID']) {
            $sEntryUrl = $this->genUrl($iEntryId, $aPostInfo['PostUri']);
            $sEntryCaption = $aPostInfo['PostCaption'];
        } else {
            return array('skip' => true);
        }

        $aActionList = array(
            'commentPost' => '_bx_blog_sbs_comments'
        );

        $sActionName = isset($aActionList[$sAction]) ? ' (' . _t($aActionList[$sAction]) . ')' : '';

        return array(
            'skip'     => false,
            'template' => array(
                'Subscription' => $sEntryCaption . $sActionName,
                'ViewLink'     => $sEntryUrl,
            ),
        );
    }

    function getRateBlock()
    {
        if ($this->iPostViewType != 3 || !$this->iViewingPostID) {
            return false;
        }

        bx_import('BxTemplVotingView');
        $bPossibleToRate = $this->oPrivacy->check('rate', $this->iViewingPostID, $this->_iVisitorID);
        $oVotingView = new BxTemplVotingView ($this->_oConfig->getRateSystemName(), $this->iViewingPostID);
        if ($oVotingView && $oVotingView->isEnabled() && $bPossibleToRate) {
            $sVotePostRating = $oVotingView->getBigVoting(1);
        } else {
            $sVotePostRating = $oVotingView->getBigVoting(0);
        }
        $aVars = array('content' => $sVotePostRating);

        return $this->_oTemplate->parseHtmlByName('default_padding.html', $aVars);
    }

    function getPostOverviewBlock()
    {
        $iMemberID = (int)$this->aViewingPostInfo['OwnerID'];
        $aBlogInfo = $this->_oDb->getBlogInfo($iMemberID);
        $sBlogDescription = '<div class="blog_desc bx-def-margin-sec-top">' . process_html_output($aBlogInfo['Description']) . '</div>';
        $aAuthor = getProfileInfo($iMemberID);

        $aVars = array(
            'author_unit' => get_member_thumbnail($aAuthor['ID'], 'none', true),
            'fields'      => $sBlogDescription,
        );

        if ($this->iPostViewType == 3 && $this->iViewingPostID > 0) {
            require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
            $oBlogSearch = new BxBlogsSearchUnit();

            bx_import('BxDolCategories');
            bx_import('BxDolTags');
            $oCategories = new BxDolCategories();
            $oTags = new BxDolTags();

            $sCats = '';
            $aCategories = $oCategories->explodeTags($this->aViewingPostInfo['Categories']);
            $aCatLinks = array();
            if (count($aCategories) > 0) {
                foreach ($aCategories as $iKey => $sCatValue) {
                    $sCatLink = $oBlogSearch->getCurrentUrl('category', title2uri(trim($sCatValue)),
                        title2uri(trim($sCatValue)),
                        array('ownerId' => $iMemberID, 'blogOwnerName' => $aAuthor['NickName']));
                    $aCatLinks[] = '<a href="' . $sCatLink . '" rel="nofollow">' . $sCatValue . '</a>';
                }
                $sCats = implode(", ", $aCatLinks);
            }

            $sTags = '';
            $aTags = $oTags->explodeTags($this->aViewingPostInfo['Tags']);
            $aTagLinks = array();
            if (count($aTags) > 0) {
                foreach ($aTags as $sTagKey) {
                    if ($sTagKey != '') {
                        $sTagLink = $oBlogSearch->getCurrentUrl('tag', $iPostID, title2uri(trim($sTagKey)));
                        $aTagLinks[] = '<a href="' . $sTagLink . '" title="' . $sTagKey . '" rel="nofollow">' . $sTagKey . '</a>';
                    }
                }
                $sTags = implode(", ", $aTagLinks);
            }

            $aVars['date'] = getLocaleDate($this->aViewingPostInfo['PostDate'], BX_DOL_LOCALE_DATE_SHORT);
            $aVars['date_ago'] = defineTimeInterval($this->aViewingPostInfo['PostDate'], false);
            $aVars['cats'] = $sCats;
            $aVars['tags'] = $sTags;
            $aVars['fields'] = '';

            return $this->_oTemplate->parseHtmlByName('entry_view_block_info.html', $aVars);
        }

        return $this->_oTemplate->parseHtmlByName('entry_view_empty_block_info.html', $aVars);
    }

    function getPostTagsBlock()
    {
        $iMemberID = (int)$this->aViewingPostInfo['OwnerID'];
        $sOwnerNickname = getNickName($iMemberID);
        $aOwnerInfo = getProfileInfo($iMemberID);

        $sOwnerAddAp = ($iMemberID == $this->_iVisitorID
            || $this->isAllowedApprove() || $this->isAllowedPostEdit(-1)
            || $this->isAllowedPostDelete(-1))
            ? ''
            : "AND `PostStatus`='approval'";

        $sStatusFilter = ($this->isAdmin() == true) ? '' : $sOwnerAddAp;

        $aTagsPost = array();
        $sTagsVals = '';

        $vTags = $this->_oDb->getTagsInfo($iMemberID, $sStatusFilter, '');

        $aTagsPost = array();
        while ($aPost = $vTags->fetch()) {
            $sTagsCommas = trim($aPost['Tags']);
            $aTags = explode(',', $sTagsCommas);
            foreach ($aTags as $sTagKeyVal) {
                $sTagKey = trim($sTagKeyVal);
                if ($sTagKey != '') {
                    if (isset($aTagsPost[$sTagKey])) {
                        $aTagsPost[$sTagKey]++;
                    } else {
                        $aTagsPost[$sTagKey] = 1;
                    }
                }
            }
        }
        ksort($aTagsPost);
        $aTagsPost = array_slice($aTagsPost, 0, $this->_oConfig->iTopTagsCnt);

        if (count($aTagsPost)) {
            $iMinFontSize = $GLOBALS['oTemplConfig']->iTagsMinFontSize;
            $iMaxFontSize = $GLOBALS['oTemplConfig']->iTagsMaxFontSize;
            $iFontDiff = $iMaxFontSize - $iMinFontSize;

            $iMinRating = min($aTagsPost);
            $iMaxRating = max($aTagsPost);

            $iRatingDiff = $iMaxRating - $iMinRating;
            $iRatingDiff = ($iRatingDiff == 0) ? 1 : $iRatingDiff;
        }

        $aProf = array('Permalink' => $aOwnerInfo['NickName'], 'Link' => $iMemberID);

        foreach ($aTagsPost as $sTag => $iCount) {
            $iTagSize = $iMinFontSize + round($iFontDiff * (($iCount - $iMinRating) / $iRatingDiff));
            $href = str_replace('{tag}', urlencode($sTag), $sCrtHrefTmpl);
            $sTagLink = $this->genBlogLink('search_by_tag', $aProf, '', '', title2uri($sTag));

            $sTagsVals .= '<span class="one_tag" style="font-size:' . $iTagSize . 'px;">
                <a href="' . $sTagLink . '" title="' . _t('_Count') . ':' . $iCount . '">' . htmlspecialchars_adv($sTag) . '</a>
            </span>';
        }

        $sTagsVals = ($sTagsVals == '') ? MsgBox(_t('_Empty')) : $sTagsVals;

        return <<<EOF
<div class="bx-def-bc-padding">
    {$sTagsVals}
    <div class="clear_both"></div>
</div>
EOF;
    }

    function getPostCategoriesBlock()
    {
        $iMemberID = (int)$this->aViewingPostInfo['OwnerID'];
        $aOwnerInfo = getProfileInfo($iMemberID);
        $sOwnerNickname = getNickName($iMemberID);
        $aProf = array('Permalink' => $aOwnerInfo['NickName'], 'Link' => $iMemberID);

        $sOwnerAddAp = ($iMemberID == $this->_iVisitorID
            || $this->isAllowedApprove() || $this->isAllowedPostEdit(-1)
            || $this->isAllowedPostDelete(-1))
            ? ''
            : "AND `PostStatus`='approval'";

        $sStatusFilter = ($this->isAdmin() == true) ? '' : $sOwnerAddAp;

        $sNewC = ucfirst(_t('_new'));
        $sCategoriesC = _t('_bx_blog_Categories');
        $sPostsCL = mb_strtolower(_t('_bx_blog_Posts'));

        $sCategories = '';

        $oCategories = new BxDolCategories();
        $aAllCategories = $oCategories->getCategoriesList('bx_blogs', $iMemberID);

        if (is_array($aAllCategories) && count($aAllCategories) > 0) {
            foreach ($aAllCategories as $iCatID => $sCategoryName) {
                $sCategoryNameS = addslashes($sCategoryName);
                $iCountCatPost = $this->_oDb->getPostsCntInCategory($sCategoryNameS, $sStatusFilter, $iMemberID);

                if ($iCountCatPost == 0) {
                    continue;
                }

                $sCatName = process_line_output($sCategoryName);
                $sSpacerName = $this->sSpacerPath;

                $aCat = array('Permalink' => title2uri($sCategoryName), 'Link' => title2uri($sCategoryName));
                $sCatLink = $this->genBlogLink('show_member_blog', $aProf, $aCat);

                $sCategories .= <<<EOF
<div class="cls_result_row bx-def-margin-sec-top-auto">
    <div class="cls_categ_name">
        <i class="sys-icon folder"></i>
        <a href="{$sCatLink}">{$sCatName}</a>&nbsp;<span class="blog_author bx-def-font-grayed bx-def-font-small">({$iCountCatPost} {$sPostsCL})</span>
    </div>
</div>
EOF;
            }
        }

        return DesignBoxContent($sCategoriesC, $sCategories, 11);
    }

    function getPostFeatureBlock()
    {
        $iMemberID = (int)$this->aViewingPostInfo['OwnerID'];
        $aOwnerInfo = getProfileInfo($iMemberID);
        $aUser = array('Permalink' => $aOwnerInfo['NickName'], 'Link' => $iMemberID);

        $sFeaturedSect = '';
        $vFeaturedPosts = $this->_oDb->getFeaturedPosts($iMemberID);
        if ($vFeaturedPosts->rowCount()) {
            $sFeatured = '';
            while ($aFeaturedPost = $vFeaturedPosts->fetch()) {
                $iPostID = (int)$aFeaturedPost['PostID'];
                $aPost = array('Permalink' => $aFeaturedPost['PostUri'], 'Link' => $iPostID);
                $sPostLink = $this->genBlogLink('show_member_post', $aUser, '', $aPost);
                $sFeaturedPostTitle = process_line_output($aFeaturedPost['PostCaption']);

                $sFeatured .= <<<EOF
<div class="cls_result_row bx-def-margin-sec-top-auto">
    <div class="cls_categ_name">
        <i class="sys-icon star-o"></i>
        <a href="{$sPostLink}" title="{$sFeaturedPostTitle}">{$sFeaturedPostTitle}</a>
    </div>
</div>
EOF;
            }

            return <<<EOF
<div class="bx-def-bc-padding">
    {$sFeatured}
</div>
EOF;
        }
    }

    /**
     * Generate User`s Blog Post Page
     *
     * @return HTML presentation of data
     */
    function GenPostPage($iParamPostID = 0)
    {
        $this->iViewingPostID = ($iParamPostID > 0) ? $iParamPostID : $this->iViewingPostID;

        list($sCode, $bShowBlocks) = $this->getViewingPostInfo();
        if (empty($this->aViewingPostInfo)) {
            header("HTTP/1.1 404 Not Found");
            $sMsg = _t('_sys_request_page_not_found_cpt');
            $GLOBALS['oTopMenu']->setCustomSubHeader($sMsg);

            return DesignBoxContent($sMsg, MsgBox($sMsg), 1);
        }

        $iBlogLimitChars = (int)getParam('max_blog_preview');
        $sPostText = htmlspecialchars_adv(mb_substr(trim(strip_tags($this->aViewingPostInfo['PostText'])), 0,
            $iBlogLimitChars));
        $this->_oTemplate->setPageDescription($sPostText);

        if (mb_strlen($this->aViewingPostInfo['Tags']) > 0) {
            $this->_oTemplate->addPageKeywords($this->aViewingPostInfo['Tags']);
        }

        $sRetHtml .= $sCode;
        if ($bShowBlocks) {
            $oBPV = new BxDolBlogsPageView($this);
            $sRetHtml .= $oBPV->getCode();
        }

        return $sRetHtml;
    }

    function GenMyPageAdmin($sMode = '')
    {
        $this->CheckLogged();

        $sMainC = _t('_bx_blog_Manage_main');
        $sAddC = _t('_bx_blog_Add');
        $sManageC = _t('_bx_blog_Manage');
        $sPendingC = _t('_bx_blog_pending_approval');
        $sAdministrationC = _t('_bx_blog_Administration');
        $sMyBlogC = _t('_bx_blog_My_blog');
        $sPendApprC = _t('_bx_blog_pending_approval');
        $sMyPostsC = _t('_bx_blog_My_posts');

        $bUseFriendlyLinks = $this->isPermalinkEnabled();
        $sLink = $this->genBlogFormUrl();
        $sBlogMainLink = ($bUseFriendlyLinks) ? 'blogs/my_page/' : "{$sLink}?action=my_page";
        $sBlogAddLink = ($bUseFriendlyLinks) ? 'blogs/my_page/add/' : "{$sLink}?action=my_page&mode=add";
        $sBlogManageLink = ($bUseFriendlyLinks) ? 'blogs/my_page/manage/' : "{$sLink}?action=my_page&mode=manage";
        $sBlogPendingLink = ($bUseFriendlyLinks) ? 'blogs/my_page/pending/' : "{$sLink}?action=my_page&mode=pending";

        if (bx_get('action_delete') && is_array(bx_get('bposts'))) {
            foreach (bx_get('bposts') as $iBPostID) {
                $this->ActionDeletePost((int)$iBPostID);
            }
        }

        require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
        $oTmpBlogSearch = new BxBlogsSearchUnit();
        $oTmpBlogSearch->PerformObligatoryInit($this, 4);
        $oTmpBlogSearch->bShowCheckboxes = false;
        $oTmpBlogSearch->aCurrent['paginate']['perPage'] = $this->_oConfig->getPerPage();
        $oTmpBlogSearch->aCurrent['restriction']['owner']['value'] = $this->_iVisitorID;

        $GLOBALS['oTopMenu']->setCurrentProfileID($this->_iVisitorID);

        if (!bx_get('ajax')) {
            $sMyBlogPostsVal = $oTmpBlogSearch->displayResultBlock();
            $sMyPosts = ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) ? MsgBox(_t('_Empty')) : $sMyBlogPostsVal;

            $oTmpBlogSearch->aCurrent['paginate']['page_url'] = $sBlogMainLink;
            $sMyPostsPagination = $oTmpBlogSearch->showPagination3();
        }

        $sMainTabClass = $sAddTabClass = $sManageTabClass = $sPendingTabClass = 0;
        switch ($sMode) {
            case 'add':
                $sAddTabClass = 1;

                $aBlogsRes = $this->_oDb->getBlogInfo($this->_iVisitorID);
                $sNewPostForm = (!$aBlogsRes) ? $this->GenCreateBlogForm(false) : $this->AddNewPostForm(0, false);

                $sAdmContent = $sNewPostForm;

                break;
            case 'manage':
                $sManageTabClass = 1;

                $oTmpBlogSearch->bShowCheckboxes = true;
                $sBlogPostsVal = $oTmpBlogSearch->displayResultBlock();
                $sActivePosts = ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) ? MsgBox(_t('_Empty')) : $sBlogPostsVal;

                $sManagePostsPagination = $oTmpBlogSearch->showPaginationAjax('bx_blogs_user_form', $sBlogManageLink);

                $sAdmPanel = $oTmpBlogSearch->showAdminActionsPanel('bposts_box', array('action_delete' => '_Delete'),
                    'bposts');
                $sManagePostsUnits = <<<EOF
<div id="bposts_box" class="bx-def-bc-padding">
    {$sActivePosts}
    <div class="clear_both"></div>
</div>
{$sManagePostsPagination}
{$sAdmPanel}
EOF;
                $sAjaxContent = $sManagePostsUnits;
                $sAdmContent = '<form id="bx_blogs_user_form" method="post">' . $sManagePostsUnits . '</form>';
                break;
            case 'pending':
                $sPendingTabClass = 1;

                $oTmpBlogSearch->aCurrent['restriction']['activeStatus']['value'] = 'disapproval';
                $sDisPostsVal = $oTmpBlogSearch->displayResultBlock();
                $sDisPostsVal = ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) ? MsgBox(_t('_Empty')) : $sDisPostsVal;

                $sPendingPostsPagination = $oTmpBlogSearch->showPaginationAjax('bx_blogs_pending', $sBlogPendingLink);

                $sPendingPostsUnits = <<<EOF
<div id="bposts_box" class="bx-def-bc-padding">
    {$sDisPostsVal}
    <div class="clear_both"></div>
</div>
$sPendingPostsPagination
EOF;
                $sAjaxContent = $sPendingPostsUnits;
                $sAdmContent = '<div id="bx_blogs_pending">' . $sPendingPostsUnits . '</div>';
                break;
            case 'main':
            default:
                $sMainTabClass = 1;

                $iMyPostsCnt = $this->_oDb->getMemberPostsCnt($this->_iVisitorID);
                $sAdmContent = '<div class="bx-def-font-large" style="text-align: center;">' . _t('_bx_blog_admin_box_desc',
                        $iMyPostsCnt, $sBlogManageLink, $sBlogAddLink) . '</div>';
                $aVars = array('content' => $sAdmContent);
                $sAdmContent = $this->_oTemplate->parseHtmlByName('default_padding.html', $aVars);
                break;
        }

        $sAdmPost = BxDolPageView::getBlockCaptionMenu(time(), array(
            'blogs_main'    => array('href' => $sBlogMainLink, 'title' => $sMainC, 'active' => $sMainTabClass),
            'blogs_add'     => array('href' => $sBlogAddLink, 'title' => $sAddC, 'active' => $sAddTabClass),
            'blogs_manage'  => array('href' => $sBlogManageLink, 'title' => $sManageC, 'active' => $sManageTabClass),
            'blogs_pending' => array('href' => $sBlogPendingLink, 'title' => $sPendingC, 'active' => $sPendingTabClass)
        ));

        if ($sAjaxContent && bx_get('ajax')) {
            header('Content-type:text/html;charset=utf-8');
            echo $sAjaxContent;
            exit;
        }

        $sAdministrationUnitsSect = DesignBoxContent($sAdministrationC, $sAdmContent, 1, $sAdmPost);

        $sMyPostsBox = DesignBoxContent($sMyPostsC, $sMyPosts, 11, false, $sMyPostsPagination);

        return $sAdministrationUnitsSect . $sMyPostsBox;
    }

    /**
     * Generate Form for NewPost/EditPost
     *
     * @param $iPostID - Post ID
     * @return HTML presentation of data
     */
    function AddNewPostForm($iPostID = 0, $bBox = true)
    {
        $this->CheckLogged();

        if ($iPostID == 0) {
            if (!$this->isAllowedPostAdd()) {
                return $this->_oTemplate->displayAccessDenied();
            }
        } else {
            $iOwnerID = (int)$this->_oDb->getPostOwnerByID($iPostID);
            if (!$this->isAllowedPostEdit($iOwnerID)) {
                return $this->_oTemplate->displayAccessDenied();
            }
        }

        $sPostCaptionC = _t('_Title');
        $sPostTextC = _t('_Text');
        $sAssociatedImageC = _t('_associated_image');
        $sAddBlogC = ($iPostID) ? _t('_Submit') : _t('_Add Post');
        $sTagsC = _t('_Tags');
        $sNewPostC = _t('_New Post');
        $sEditPostC = _t('_bx_blog_Edit_post');
        $sDelImgC = _t('_Delete image');
        $sErrorC = _t('_Error Occured');
        $sCaptionErrorC = _t('_bx_blog_Caption_error');
        $sTextErrorC = _t('_bx_blog_Text_error');
        $sTagsInfoC = _t('_sys_tags_note');

        $sLink = $this->genBlogFormUrl();

        $sAddingForm = '';

        $oCategories = new BxDolCategories();
        $oCategories->getTagObjectConfig();

        $aAllowView = $this->oPrivacy->getGroupChooser($this->_iVisitorID, 'blogs', 'view', array(),
            _t('_bx_blog_privacy_view'));
        $aAllowRate = $this->oPrivacy->getGroupChooser($this->_iVisitorID, 'blogs', 'rate', array(),
            _t('_bx_blog_privacy_rate'));
        $aAllowComment = $this->oPrivacy->getGroupChooser($this->_iVisitorID, 'blogs', 'comment', array(),
            _t('_bx_blog_privacy_comment'));

        $sAction = ($iPostID == 0) ? 'new_post' : 'edit_post';

        //adding form
        $aForm = array(
            'form_attrs' => array(
                'name'    => 'CreateBlogPostForm',
                'action'  => $sLink,
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ),
            'params'     => array(
                'db' => array(
                    'table'       => $this->_oConfig->sSQLPostsTable,
                    'key'         => 'PostID',
                    'submit_name' => 'add_button',
                ),
            ),
            'inputs'     => array(
                'PostCaption'     => array(
                    'type'     => 'text',
                    'name'     => 'PostCaption',
                    'caption'  => $sPostCaptionC,
                    'required' => true,
                    'checker'  => array(
                        'func'   => 'length',
                        'params' => array(3, 255),
                        'error'  => $sCaptionErrorC,
                    ),
                    'db'       => array(
                        'pass' => 'Xss',
                    ),
                ),
                'Tags'            => array(
                    'type'     => 'text',
                    'name'     => 'Tags',
                    'caption'  => $sTagsC,
                    'info'     => $sTagsInfoC,
                    'required' => false,
                    'db'       => array(
                        'pass' => 'Xss',
                    ),
                ),
                'PostText'        => array(
                    'type'     => 'textarea',
                    'html'     => 2,
                    'name'     => 'PostText',
                    'caption'  => $sPostTextC,
                    'required' => true,
                    'checker'  => array(
                        'func'   => 'length',
                        'params' => array(3, 65535),
                        'error'  => $sTextErrorC,
                    ),
                    'db'       => array(
                        'pass' => 'XssHtml',
                    ),
                ),
                'Categories'      => $oCategories->getGroupChooser('bx_blogs', $this->_iVisitorID, true),
                'File'            => array(
                    'type'    => 'file',
                    'name'    => 'BlogPic[]',
                    'caption' => $sAssociatedImageC,
                ),
                'AssociatedImage' => array(
                    'type' => 'hidden',
                ),
                'allowView'       => $aAllowView,
                'allowRate'       => $aAllowRate,
                'allowComment'    => $aAllowComment,
                'hidden_action'   => array(
                    'type'  => 'hidden',
                    'name'  => 'action',
                    'value' => $sAction,
                ),
                'add_button'      => array(
                    'type'  => 'submit',
                    'name'  => 'add_button',
                    'value' => $sAddBlogC,
                ),
            ),
        );

        if ($iPostID > 0) {
            $aBlogPost = $this->_oDb->getJustPostInfo($iPostID);
            $sPostCaption = $aBlogPost['PostCaption'];
            $sPostText = $aBlogPost['PostText'];
            $sPostTags = $aBlogPost['Tags'];
            $sPostPicture = $aBlogPost['PostPhoto'];
            if ($sPostPicture != '') {
                $sBlogsImagesUrl = BX_BLOGS_IMAGES_URL;
                $sPostPictureTag = <<<EOF
<div class="blog_edit_image" id="edit_post_image_{$iPostID}">
    <img class="bx-def-shadow bx-def-round-corners bx-def-margin-sec-right" style="max-width:{$this->iThumbSize}px; max-height:{$this->iThumbSize}px;" src="{$sBlogsImagesUrl}big_{$sPostPicture}" />
    <a href="{$sLink}?action=del_img&amp;post_id={$iPostID}" onclick="BlogpostImageDelete('{$sLink}?action=del_img&post_id={$iPostID}&mode=ajax', 'edit_post_image_{$iPostID}');return false;" >{$sDelImgC}</a>
</div>
EOF;

                $aForm['inputs']['AssociatedImage']['type'] = 'custom';
                $aForm['inputs']['AssociatedImage']['content'] = $sPostPictureTag;
                $aForm['inputs']['AssociatedImage']['caption'] = $sAssociatedImageC;
            }

            $aCategories = explode(';', $aBlogPost['Categories']);

            $aForm['inputs']['PostCaption']['value'] = $sPostCaption;
            $aForm['inputs']['PostText']['value'] = $sPostText;
            $aForm['inputs']['Tags']['value'] = $sPostTags;
            $aForm['inputs']['Categories']['value'] = $aCategories;

            $aForm['inputs']['allowView']['value'] = $aBlogPost['allowView'];
            $aForm['inputs']['allowRate']['value'] = $aBlogPost['allowRate'];
            $aForm['inputs']['allowComment']['value'] = $aBlogPost['allowComment'];

            $aForm['inputs']['hidden_postid'] = array(
                'type'  => 'hidden',
                'name'  => 'EditPostID',
                'value' => $iPostID,
            );

            if ($aBlogPost['PostPhoto'] != '' && file_exists(BX_BLOGS_IMAGES_PATH . 'small_' . $aBlogPost['PostPhoto'])) {
                $GLOBALS['oTopMenu']->setCustomSubIconUrl(BX_BLOGS_IMAGES_URL . 'small_' . $aBlogPost['PostPhoto']);
            } else {
                $GLOBALS['oTopMenu']->setCustomSubIconUrl('book');
            }
            $GLOBALS['oTopMenu']->setCustomSubHeader($sPostCaption);
        }

        if (empty($aForm['inputs']['allowView']['value']) || !$aForm['inputs']['allowView']['value']) {
            $aForm['inputs']['allowView']['value'] = BX_DOL_PG_ALL;
        }
        if (empty($aForm['inputs']['allowRate']['value']) || !$aForm['inputs']['allowRate']['value']) {
            $aForm['inputs']['allowRate']['value'] = BX_DOL_PG_ALL;
        }
        if (empty($aForm['inputs']['allowComment']['value']) || !$aForm['inputs']['allowComment']['value']) {
            $aForm['inputs']['allowComment']['value'] = BX_DOL_PG_ALL;
        }

        $oForm = new BxTemplFormView($aForm);
        $oForm->initChecker();
        if ($oForm->isSubmittedAndValid()) {
            $this->CheckLogged();

            $iOwnID = $this->_iVisitorID;
            $sCurTime = time();
            $sPostUri = uriGenerate(bx_get('PostCaption'), $this->_oConfig->sSQLPostsTable, 'PostUri');
            $sAutoApprovalVal = (getParam('blogAutoApproval') == 'on') ? "approval" : "disapproval";

            $aValsAdd = array(
                'PostDate'   => $sCurTime,
                'PostStatus' => $sAutoApprovalVal
            );
            if ($iPostID == 0) {
                $aValsAdd['OwnerID'] = $iOwnID;
                $aValsAdd['PostUri'] = $sPostUri;
            }

            $iBlogPostID = -1;

            if ($iPostID > 0) {
                unset($aValsAdd['PostDate']);
                $oForm->update($iPostID, $aValsAdd);
                $this->isAllowedPostEdit($iOwnerID, true);
                $iBlogPostID = $iPostID;
            } else {
                $iBlogPostID = $oForm->insert($aValsAdd);
                $this->isAllowedPostAdd(true);
            }

            if ($iBlogPostID) {
                $this->iLastPostedPostID = $iBlogPostID;

                if ($_FILES) {
                    for ($i = 0; $i < count($_FILES['BlogPic']['tmp_name']); $i++) {
                        if ($_FILES['BlogPic']['error'][$i]) {
                            continue;
                        }
                        if (0 < $_FILES['BlogPic']['size'][$i] && 0 < strlen($_FILES['BlogPic']['name'][$i]) && 0 < $iBlogPostID) {
                            $sTmpFile = $_FILES['BlogPic']['tmp_name'][$i];
                            if (file_exists($sTmpFile) == false) {
                                break;
                            }

                            $aSize = getimagesize($sTmpFile);
                            if (!$aSize) {
                                @unlink($sTmpFile);
                                break;
                            }

                            switch ($aSize[2]) {
                                case IMAGETYPE_JPEG:
                                case IMAGETYPE_GIF:
                                case IMAGETYPE_PNG:

                                    $sOriginalFilename = $_FILES['BlogPic']['name'][$i];
                                    $sExt = strrchr($sOriginalFilename, '.');

                                    $sFileName = 'blog_' . $iBlogPostID . '_' . $i;
                                    @unlink($sFileName);

                                    move_uploaded_file($sTmpFile, BX_BLOGS_IMAGES_PATH . $sFileName . $sExt);
                                    @unlink($sTmpFile);

                                    if (strlen($sExt)) {
                                        $sPathSrc = BX_BLOGS_IMAGES_PATH . $sFileName . $sExt;
                                        $sPathDst = BX_BLOGS_IMAGES_PATH . '%s_' . $sFileName . $sExt;

                                        imageResize($sPathSrc, sprintf($sPathDst, 'small'), $this->iIconSize / 1,
                                            $this->iIconSize / 1);
                                        imageResize($sPathSrc, sprintf($sPathDst, 'big'), $this->iThumbSize,
                                            $this->iThumbSize);
                                        imageResize($sPathSrc, sprintf($sPathDst, 'browse'), $this->iBigThumbSize,
                                            null);
                                        imageResize($sPathSrc, sprintf($sPathDst, 'orig'), $this->iImgSize,
                                            $this->iImgSize);

                                        chmod(sprintf($sPathDst, 'small'), 0644);
                                        chmod(sprintf($sPathDst, 'big'), 0644);
                                        chmod(sprintf($sPathDst, 'browse'), 0644);
                                        chmod(sprintf($sPathDst, 'orig'), 0644);

                                        $this->_oDb->performUpdatePostWithPhoto($iBlogPostID, $sFileName . $sExt);
                                        @unlink($sPathSrc);
                                    }

                                    break;
                                default:
                                    @unlink($sTempFileName);

                                    return false;
                            }
                        }
                    }
                }

                //reparse tags
                bx_import('BxDolTags');
                $oTags = new BxDolTags();
                $oTags->reparseObjTags('blog', $iBlogPostID);

                //reparse categories
                $oCategories = new BxDolCategories();
                $oCategories->reparseObjTags('bx_blogs', $iBlogPostID);

                $sAlertAction = ($iPostID == 0) ? 'create' : 'edit_post';
                bx_import('BxDolAlerts');
                $oZ = new BxDolAlerts('bx_blogs', $sAlertAction, $iBlogPostID, $this->_iVisitorID);
                $oZ->alert();

                header("X-XSS-Protection: 0"); // to prevent browser's security audit to block youtube embeds(and others), just after post creation
                return $this->GenPostPage($iBlogPostID);
            } else {
                return MsgBox($sErrorC);
            }
        } else {
            $sAddingForm = $oForm->getCode();
        }

        $sCaption = ($iPostID) ? $sEditPostC : $sNewPostC;
        $sAddingFormVal = '<div class="blogs-view bx-def-bc-padding">' . $sAddingForm . '</div>';

        return ($bBox) ? DesignBoxContent($sCaption,
            '<div class="blogs-view bx-def-bc-padding">' . $sAddingForm . '</div>', 1) : $sAddingFormVal;
    }

    function getTagLinks($sTagList, $sType = 'tag', $sDivider = ' ')
    {
        if (strlen($sTagList)) {
            $aTags = explode($sDivider, $sTagList);
            foreach ($aTags as $iKey => $sValue) {
                $sValue = trim($sValue, ',');
                // $sLink = $this->getCurrentUrl($sType, 0, $sValue);
                // $aRes[$sValue] = $sLink;
                $aRes[$sValue] = $sValue;
            }
        }

        return $aRes;
    }

    /**
     * Generate a Form to Editing/Adding of Category of Blog
     *
     * @return HTML presentation of data
     */
    function GenAddCategoryForm()
    {
        $this->CheckLogged();

        $aBlogsRes = $this->_oDb->getBlogInfo($this->_iVisitorID);
        if (!$aBlogsRes) {
            return $this->GenCreateBlogForm();
        }

        $iOwnerID = (int)$aBlogsRes['OwnerID'];
        if ((!$this->_iVisitorID || $iOwnerID != $this->_iVisitorID) && !$this->isAllowedBlogView($iOwnerID)) {
            return $this->_oTemplate->displayAccessDenied();
        }

        $sAddCategoryC = _t('_Add Category');

        $sRetHtml = '';
        if (($this->_iVisitorID == $aBlogsRes['OwnerID'] && $this->_iVisitorID > 0) || $this->bAdminMode == true) {
            $sCategoryCaptionC = _t('_Title');
            $sErrorC = _t('_Error Occured');

            $sLink = $this->genBlogFormUrl();

            //adding form
            $aForm = array(
                'form_attrs' => array(
                    'name'   => 'CreateBlogPostForm',
                    'action' => $sLink,
                    'method' => 'post'
                ),
                'params'     => array(
                    'db' => array(
                        'table'       => 'sys_categories',
                        /*'key' => 'PostID',*/
                        'submit_name' => 'add_button',
                    ),
                ),
                'inputs'     => array(
                    'Caption'       => array(
                        'type'     => 'text',
                        'name'     => 'Category',
                        'caption'  => $sCategoryCaptionC,
                        'required' => true,
                        'checker'  => array(
                            'func'   => 'length',
                            'params' => array(3, 128),
                            'error'  => $sErrorC,
                        ),
                        'db'       => array(
                            'pass' => 'Xss',
                        ),
                    ),
                    'hidden_action' => array(
                        'type'  => 'hidden',
                        'name'  => 'action',
                        'value' => 'add_category',
                    ),
                    'add_button'    => array(
                        'type'  => 'submit',
                        'name'  => 'add_button',
                        'value' => $sAddCategoryC,
                    ),
                ),
            );

            $oForm = new BxTemplFormView($aForm);
            $oForm->initChecker();
            if ($oForm->isSubmittedAndValid()) {
                $this->CheckLogged();

                $aValsAdd = array(
                    'ID'    => '0',
                    'Type'  => 'bx_blogs',
                    'Owner' => $this->_iVisitorID
                );

                $iInsertedCategoryID = $oForm->insert($aValsAdd);

                if ($iInsertedCategoryID >= 0) {
                    return $this->GenMemberBlog($this->_iVisitorID);
                } else {
                    return MsgBox($sErrorC);
                }
            } else {
                $sRetHtml = $oForm->getCode();
            }

        } else {
            $sRetHtml = $this->_oTemplate->displayAccessDenied();
        }

        return DesignBoxContent($sAddCategoryC, '<div class="blogs-view bx-def-bc-padding">' . $sRetHtml . '</div>', 1);
    }

    function ActionChangeFeatureStatus()
    {
        if (false == bx_get('do') || bx_get('do') != 'cfs') {
            return;
        }

        $this->CheckLogged();
        $iPostID = (int)bx_get('id');

        $iPostOwnerID = $this->_oDb->getPostOwnerByID($iPostID);
        $iFeaturedStatus = $this->_oDb->getFeaturedStatus($iPostID);

        if ((($this->_iVisitorID == $iPostOwnerID && $iPostOwnerID > 0) || $this->bAdminMode) && $iPostID > 0) {
            $iNewStatus = ((int)$iFeaturedStatus == 1) ? '0' : '1';
            $aUpdatingParams = array(
                'postID' => $iPostID,
                'status' => $iNewStatus
            );
            $this->_oDb->performUpdateFeatureStatus($aUpdatingParams);
        } elseif ($this->_iVisitorID != $iPostOwnerID) {
            return MsgBox(_t('_Access denied'));
        } else {
            return MsgBox(_t('_Error Occured'));
        }
    }

    /**
     * Generate a Block of searching result by Tag (GET is tagKey)
     *
     * @return HTML presentation of data
     */
    function GenSearchResult()
    {
        if (!$this->isAllowedBlogPostSearch(true)) {
            return $this->_oTemplate->displayAccessDenied();
        }

        $iCheckedMemberID = $this->_iVisitorID;

        $bNoProfileMode = (false !== bx_get('ownerID') || false !== bx_get('blogOwnerName')) ? false : true;

        $sRetHtml = '';
        $sSearchedTag = uri2title(process_db_input(bx_get('tagKey'), BX_TAGS_STRIP));
        $iMemberID = $this->defineUserId();

        $sTagsC = _t('_Tags');
        $sNoBlogC = _t('_bx_blog_No_blogs_available');

        require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
        $oTmpBlogSearch = new BxBlogsSearchUnit($this);
        $oTmpBlogSearch->PerformObligatoryInit($this, 4);
        $oTmpBlogSearch->aCurrent['restriction']['tag2']['value'] = $sSearchedTag;
        $oTmpBlogSearch->aCurrent['paginate']['perPage'] = $this->_oConfig->getPerPage();
        if ($iMemberID > 0) {
            $oTmpBlogSearch->aCurrent['restriction']['owner']['value'] = $iMemberID;
        }
        if (($iMemberID != 0 && $iMemberID == $iCheckedMemberID) || $this->isAdmin() == true) {
            $oTmpBlogSearch->aCurrent['restriction']['activeStatus'] = '';
        }
        $sBlogPostsVal = $oTmpBlogSearch->displayResultBlock();
        $sBlogPostsVal = '<div class="blogs-view bx-def-bc-padding">' . $sBlogPostsVal . '</div>';

        $oTmpBlogSearch->aCurrent['paginate']['page_url'] = $oTmpBlogSearch->getCurrentUrl('tag', 0,
            title2uri($sSearchedTag));
        $sBlogPostsVal .= $oTmpBlogSearch->showPagination3();

        $sBlogPosts = ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) ? MsgBox(_t('_Empty')) : $sBlogPostsVal;

        $sContentSect = DesignBoxContent($sTagsC . ' - ' . $sSearchedTag, $sBlogPostsVal, 1);

        if ($bNoProfileMode == false) {
            $sRightSect = '';
            if ($iMemberID > -1) {
                $aBlogsRes = $this->_oDb->getBlogInfo($iMemberID);
                if (!$aBlogsRes) {
                    $sNoBlogC = MsgBox($sNoBlogC);
                    $sRetHtml = <<<EOF
<div class="{$sWidthClass}">
    {$sNoBlogC}
</div>
<div class="clear_both"></div>
EOF;
                } else {
                    $sRightSect = $this->GenMemberDescrAndCat($aBlogsRes);
                    $sWidthClass = ($iMemberID > 0) ? 'cls_info_left' : 'cls_res_thumb';

                    $sRetHtml = $this->Templater($sContentSect, $sRightSect, $sWidthClass);
                }
            } else {
                $sRetHtml = MsgBox(_t('_Profile Not found Ex'));
            }
        } else {
            $sRetHtml = <<<EOF
<div class="{$sWidthClass}">
    {$sContentSect}
</div>
<div class="clear_both"></div>
EOF;
        }

        return $sRetHtml;
    }

    function GenBlogCalendar($isMiniMode = false, $iBlockID = 0, $sDynamicUrl = '')
    {
        $aDateParams = array();
        $sDate = bx_get('date');
        if ($sDate) {
            $aDateParams = explode('/', $sDate);
        }

        require_once($this->_oConfig->getClassPath() . 'BxBlogsCalendar.php');
        $oCalendar = new BxBlogsCalendar ((int)$aDateParams[0], (int)$aDateParams[1], $this);
        $oCalendar->setBlockId($iBlockID);
        $oCalendar->setDynamicUrl($sDynamicUrl);
        $sBlogPostsCalendar = $oCalendar->display($isMiniMode);

        return DesignBoxContent(_t('_bx_blog_Calendar'), $sBlogPostsCalendar, 1);
    }

    /**
     * Generate List of Posts for calendar
     *
     * @return HTML presentation of data
     */
    function GenPostCalendarDay()
    { //  date=2009/3/18
        $sCode = MsgBox(_t('_Empty'));

        $sDate = bx_get('date');
        $aDate = explode('/', $sDate);

        $iValue1 = (int)$aDate[0];
        $iValue2 = (int)$aDate[1];
        $iValue3 = (int)$aDate[2];

        if ($iValue1 > 0 && $iValue2 > 0 && $iValue3 > 0) {

            $this->iPostViewType = 4;

            $sCaption = _t('_bx_blog_caption_browse_by_day')
                . getLocaleDate(strtotime("{$iValue1}-{$iValue2}-{$iValue3}"), BX_DOL_LOCALE_DATE_SHORT);

            if (!$this->isAllowedBlogsPostsBrowse()) {
                return DesignBoxContent($sCaption, $this->_oTemplate->displayAccessDenied(), 1);
            }

            require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');

            $oTmpBlogSearch = new BxBlogsSearchUnit($this);
            $oTmpBlogSearch->PerformObligatoryInit($this, $this->iPostViewType);
            $oTmpBlogSearch->aCurrent['paginate']['perPage'] = $this->_oConfig->getPerPage();
            $oTmpBlogSearch->aCurrent['sorting'] = 'last';

            $oTmpBlogSearch->aCurrent['restriction']['calendar-min'] = array(
                'value'          => "UNIX_TIMESTAMP('{$iValue1}-{$iValue2}-{$iValue3} 00:00:00')",
                'field'          => 'PostDate',
                'operator'       => '>=',
                'no_quote_value' => true
            );
            $oTmpBlogSearch->aCurrent['restriction']['calendar-max'] = array(
                'value'          => "UNIX_TIMESTAMP('{$iValue1}-{$iValue2}-{$iValue3} 23:59:59')",
                'field'          => 'PostDate',
                'operator'       => '<=',
                'no_quote_value' => true
            );

            $sCode = $oTmpBlogSearch->displayResultBlock();
            $sCode = ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) ? MsgBox(_t('_Empty')) : $sCode;

            $sRequest = BX_DOL_URL_ROOT . 'modules/boonex/blogs/blogs.php?action=show_calendar_day&date=' . "{$iValue1}/{$iValue2}/{$iValue3}" . '&page={page}&per_page={per_page}';
            $oTmpBlogSearch->aCurrent['paginate']['page_url'] = $sRequest;

            $sPagination = $oTmpBlogSearch->showPagination3();
        }

        $sRetHtmlVal = <<<EOF
<div class="bx-def-bc-padding">
    {$sCode}
</div>
{$sPagination}
EOF;

        return DesignBoxContent($sCaption, $sRetHtmlVal, 1);
    }

    /**
     * Generate a Form to Create Blog
     *
     * @return HTML presentation of data
     */
    function GenCreateBlogForm($bBox = true)
    {
        $this->CheckLogged();

        if (!$this->isAllowedPostAdd()) {
            return $this->_oTemplate->displayAccessDenied();
        }

        $sRetHtml = $sCreateForm = '';
        $sActionsC = _t('_Actions');
        $sPleaseCreateBlogC = _t('_bx_blog_Please_create_blog');
        $sNoBlogC = _t('_bx_blog_No_blogs_available');
        $sMyBlogC = _t('_bx_blog_My_blog');
        $sNewBlogDescC = _t('_bx_blog_description');
        $sErrorC = _t('_bx_blog_create_blog_form_error');
        $sSubmitC = _t('_Submit');

        $sRetHtml .= MsgBox($sNoBlogC);

        if ($this->_iVisitorID || $this->isAdmin()) {
            $sRetHtml = MsgBox($sPleaseCreateBlogC);
            $sLink = $this->genBlogFormUrl();

            $sAddingForm = '';

            //adding form
            $aForm = array(
                'form_attrs' => array(
                    'name'   => 'CreateBlogForm',
                    'action' => $sLink,
                    'method' => 'post',
                ),
                'params'     => array(
                    'db' => array(
                        'table'       => $this->_oConfig->sSQLBlogsTable,
                        'key'         => 'ID',
                        'submit_name' => 'add_button',
                    ),
                ),
                'inputs'     => array(
                    'Description'   => array(
                        'type'     => 'textarea',
                        'html'     => 0,
                        'name'     => 'Description',
                        'caption'  => $sNewBlogDescC,
                        'required' => true,
                        'checker'  => array(
                            'func'   => 'length',
                            'params' => array(3, 255),
                            'error'  => $sErrorC,
                        ),
                        'db'       => array(
                            'pass' => 'XssHtml',
                        ),
                    ),
                    'hidden_action' => array(
                        'type'  => 'hidden',
                        'name'  => 'action',
                        'value' => 'create_blog',
                    ),
                    'add_button'    => array(
                        'type'  => 'submit',
                        'name'  => 'add_button',
                        'value' => $sSubmitC,
                    ),
                ),
            );

            $oForm = new BxTemplFormView($aForm);
            $oForm->initChecker();
            if ($oForm->isSubmittedAndValid()) {
                $this->CheckLogged();

                $iOwnID = $this->_iVisitorID;
                $aBlogsRes = $this->_oDb->getBlogInfo($iOwnID);
                if (!$aBlogsRes) {
                    $aValsAdd = array(
                        'OwnerID' => $iOwnID
                    );
                    $iBlogID = $oForm->insert($aValsAdd);

                    //return $this->GenMemberBlog($iOwnID, false);
                    $bUseFriendlyLinks = $this->isPermalinkEnabled();
                    $sBlogAddLink = ($bUseFriendlyLinks)
                        ? BX_DOL_URL_ROOT . 'blogs/my_page/add/'
                        : $this->genBlogFormUrl() . '?action=my_page&mode=add';

                    header('Location:' . $sBlogAddLink);

                    return $this->GenMyPageAdmin('add');
                } else {
                    return MsgBox($sErrorC);
                }
            } else {
                $sAddingForm = $oForm->getCode();
            }

            $aVars = array('content' => $sAddingForm);
            $sAddingForm = $this->_oTemplate->parseHtmlByName('default_padding.html', $aVars);
            $sCreateForm = ($bBox) ? DesignBoxContent($sActionsC, $sAddingForm, 1) : $sAddingForm;
        }

        $sMyBlogResult = ($bBox) ? DesignBoxContent($sMyBlogC, $sRetHtml, 1) : $sRetHtml;

        return $sMyBlogResult . $sCreateForm;
    }

    function GenAdminTabbedPage()
    {
        $sTitleC = _t('_bx_blog_Administration');
        $sPendingC = _t('_bx_blog_pending_approval');
        $sSettingsC = _t('_Settings');

        $sPendingTab = $this->GenBlogAdminIndex();
        $sSettingsTab = $this->getAdministrationSettings();

        $sContent = '';
        $sContent .= DesignBoxAdmin($sSettingsC, $sSettingsTab, '', '', 11);
        $sContent .= DesignBoxAdmin($sPendingC, $sPendingTab);

        return $sContent;
    }

    function getAdministrationSettings()
    {
        $iId = $this->_oDb->getSettingsCategory();
        if (empty($iId)) {
            return MsgBox(_t('_sys_request_page_not_found_cpt'));
        }

        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if (isset($_POST['save']) && isset($_POST['cat'])) {
            $oSettings = new BxDolAdminSettings($iId);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        $oSettings = new BxDolAdminSettings($iId);
        $sResult = $oSettings->getForm();

        if ($mixedResult !== true && !empty($mixedResult)) {
            $sResult = $mixedResult . $sResult;
        }

        return $sResult;
    }

    /**
     * Editing a Description of Blog
     *
     * @return MsgBox result
     */
    function ActionEditBlog()
    {
        $this->CheckLogged();
        $iBlogID = (int)bx_get('EditBlogID');

        $iBlogOwner = $this->_oDb->getOwnerByBlogID($iBlogID);
        if ((($this->_iVisitorID == $iBlogOwner && $iBlogOwner > 0) || $this->bAdminMode) && $iBlogID > 0) {
            $sDescription = process_db_input(bx_get('Description'), BX_TAGS_VALIDATE);

            $aUpdateParams = array(
                'blogID'      => $iBlogID,
                'description' => $sDescription
            );
            $this->_oDb->performUpdateBlog($aUpdateParams);

            $sBlogOwnerLink = $this->genBlogLink('show_member_blog',
                array('Permalink' => getUsername($this->_iVisitorID), 'Link' => $this->_iVisitorID));
            header('Location:' . $sBlogOwnerLink);
        } elseif ($this->_iVisitorID != $iBlogOwner) {
            return MsgBox(_t('_Access denied'));
        } else {
            return MsgBox(_t('_Error Occured'));
        }
    }

    /**
     * Deleting a Full Blog
     *
     * @return MsgBox result
     */
    function ActionDeleteBlogSQL()
    {
        $this->CheckLogged();
        $iBlogID = (int)bx_get('DeleteBlogID');

        $iBlogOwner = $this->_oDb->getOwnerByBlogID($iBlogID);

        if ((($this->_iVisitorID == $iBlogOwner && $iBlogOwner > 0) || $this->bAdminMode) && $iBlogID > 0) {
            $aPostsInCategory = $this->_oDb->getPostsInCategory(1, '', $iBlogOwner);
            foreach ($aPostsInCategory as $iKeyID => $iPostID) {
                $this->ActionDeletePost((int)$iPostID);
            }

            $this->_oDb->deleteBlog($iBlogID);
        } elseif ($this->_iVisitorID != $iBlogOwner) {
            return MsgBox(_t('_Access denied'));
        } else {
            return MsgBox(_t('_Error Occured'));
        }
    }

    /**
     * Blog deleting. For outer usage (carefully in using).
     */
    function serviceActionDeleteBlog()
    {
        $this->ActionDeleteBlogSQL();
    }

    function ActionSharePopup($iPostId)
    {
        $iPostId = (int)$iPostId;
        if ($iPostId) {
            $sViewingPostUri = $this->_oDb->getPostUriByID($iPostId);
            $aViewingPost = array('Permalink' => $sViewingPostUri, 'Link' => $this->$iPostId);
            $sViewingPostLink = $this->genBlogLink('show_member_post', $aUser, '', $aViewingPost);
            $sEntryUrl = $this->genBlogLink('show_member_post', $aUser, '', $aViewingPost, '', true);

            require_once(BX_DIRECTORY_PATH_INC . 'shared_sites.inc.php');
            header('Content-type:text/html;charset=utf-8');
            echo getSitesHtml($sEntryUrl, _t('_Share'));
        }
        exit;
    }

    function ActionPrepareForEdit($sInput)
    {
        $sResJSHTML = addslashes(htmlspecialchars($sInput));
        $sResJSHTML = str_replace("\r\n", '', $sResJSHTML);

        return $sResJSHTML;
    }

    function defineUserId()
    {
        $iMemberId = 0;

        if (false !== bx_get('blogOwnerName')) {
            $sNickName = process_db_input(bx_get('blogOwnerName'), BX_TAGS_STRIP);
            $iMemberId = $this->_oDb->getMemberIDByNickname($sNickName);
        } elseif (bx_get('ownerID')) {
            $iMemberId = (int)bx_get('ownerID');
        }

        if ($this->isPermalinkEnabled() && $iMemberId == 0 && bx_get('action') == 'show_member_post') {
            $sPostUri = process_db_input(bx_get('postUri'), BX_TAGS_STRIP);
            $iPostID = $this->_oDb->getPostIDByUri($sPostUri);

            $iMemberId = $this->_oDb->getPostOwnerByID($iPostID);
        }

        return $iMemberId;
    }

    function defineCategoryName()
    {
        $sCat = '';

        if (false !== bx_get('categoryUri')) {
            $sCat = uri2title(process_db_input(bx_get('categoryUri'), BX_TAGS_STRIP));
        } elseif (false !== bx_get('category')) {
            $sCat = uri2title(process_db_input(bx_get('category'), BX_TAGS_STRIP));
        }

        return $sCat;
    }

    function isPermalinkEnabled()
    {
        $bEnabled = isset($this->_isPermalinkEnabled) ? $this->_isPermalinkEnabled : ($this->_isPermalinkEnabled = (getParam('permalinks_blogs') == 'on'));

        //if ($this->bAdminMode) $bEnabled = false;
        return $bEnabled;
    }

    function genBlogFormUrl()
    {
        $sMainLink = $this->sHomeUrl . $this->_oConfig->sUserExFile;
        //if ($this->bAdminMode) $sMainLink = $this->sHomeUrl . $this->_oConfig->sAdminExFile;

        $sLink = $this->isPermalinkEnabled() ? BX_DOL_URL_ROOT . $this->_oConfig->sUserExPermalink : $sMainLink;

        return $sLink;
    }

    function genBlogSubUrl()
    {
        //$sMainFile = ($this->bAdminMode) ? $this->_oConfig->sAdminExFile : $this->_oConfig->sUserExFile;
        $sMainFile = $this->_oConfig->sUserExFile;
        if ($this->isPermalinkEnabled()) {
            return BX_DOL_URL_ROOT . $this->_oConfig->sUserExPermalink;
        }

        return $this->sHomeUrl . $sMainFile;
    }

    function genBlogLink(
        $sAction,
        $aUser = array(),
        $aCategory = array(),
        $aPost = array(),
        $sTag = '',
        $bSubUrl = false
    ) {
        $sKey = '';
        $aService = array();
        if ($this->isPermalinkEnabled()) {
            $sKey = 'Permalink';

            $aService['User'] = '';
            $aService['Category'] = 'category/';
            $aService['Post'] = '';
            $aService['Tag'] = '';
        } else {
            $sKey = 'Link';

            $aService['User'] = 'ownerID=';
            $aService['Category'] = 'category=';
            $aService['Post'] = 'post_id=';
            $aService['Tag'] = 'tagKey=';
        }

        $sMainLink = (!$bSubUrl) ? $this->genBlogFormUrl() : $this->genBlogSubUrl();

        switch ($sAction) {
            case 'home':
                $aAction = array('Permalink' => 'home/', 'Link' => '?action=home');
                break;
            case 'show_member_blog_home':
                $aAction = array('Permalink' => 'posts/{User}', 'Link' => '?action=show_member_blog&{User}');
                break;
            case 'show_member_blog':
                $aAction = array(
                    'Permalink' => 'posts/{User}/{Category}',
                    'Link'      => '?action=show_member_blog&{User}&{Category}'
                );
                break;
            case 'show_member_post':
                $aAction = array('Permalink' => 'entry/{Post}', 'Link' => '?action=show_member_post&{User}&{Post}');
                break;
            case 'search_by_tag':
                if ($aUser) {
                    $aAction = array(
                        'Permalink' => 'posts/{User}/tag/{Tag}',
                        'Link'      => '?action=search_by_tag&{Tag}&{User}'
                    );
                } else {
                    $aAction = array('Permalink' => 'tag/{Tag}', 'Link' => '?action=search_by_tag&{Tag}');
                }
                break;
            default :
                break;
        }
        $aFinal = array();

        $aFinal['User'] = $aUser ? $aService['User'] . $aUser[$sKey] : '';
        $aFinal['Category'] = $aCategory ? $aService['Category'] . $aCategory[$sKey] : '';
        $aFinal['Post'] = $aPost ? $aService['Post'] . $aPost[$sKey] : '';
        $aFinal['Tag'] = strlen($sTag) > 0 ? $aService['Tag'] . $sTag : '';

        $sLink = $aAction[$sKey];

        foreach ($aFinal as $sKey => $sVal) {
            $sLink = str_replace('{' . $sKey . '}', $sVal, $sLink);
        }

        return $sMainLink . trim($sLink, '/&');
    }

    //For RSS generator
    function genUrl($iEntryId, $sEntryUri, $sType = 'entry')
    {
        if ($this->isPermalinkEnabled()) {
            $sUrl = BX_DOL_URL_ROOT . $this->_oConfig->sUserExPermalink . "{$sType}/{$sEntryUri}";
        } else {
            $sUrl = $this->sHomeUrl . $this->_oConfig->sUserExFile . "?action=show_member_post&post_id={$iEntryId}";
        }

        return $sUrl;
    }

    function Templater($sPostsSect, $sRightSect)
    {
        $aBlogVariables = array(
            'member_section' => $sRightSect,
            'post_section'   => $sPostsSect
        );
        $sRetHtml = $this->_oTemplate->parseHtmlByTemplateName('blog', $aBlogVariables);

        return $sRetHtml;
    }

    /**
     * New implementation of Tags page
     *
     * @return html
     */
    function GenTagsPage()
    {
        bx_import('BxTemplTagsModule');
        $aParam = array(
            'type'    => 'blog',
            'orderby' => 'popular'
        );
        $sLink = $this->isPermalinkEnabled() ? BX_DOL_URL_ROOT . 'blogs/' . 'tags' : BX_DOL_URL_ROOT . 'modules/boonex/blogs/blogs.php?action=tags';
        $oTags = new BxTemplTagsModule($aParam, _t('_all'), $sLink);

        return $oTags->getCode();
    }

    /**
     * Blogs mini-calendar block for index page (as PHP function). List of latest posts.
     *
     * @return html of blog mini-calendar
     */
    function serviceBlogsCalendarIndexPage($iBlockID)
    {
        if (!$this->isAllowedBlogsPostsBrowse()) {
            return $this->_oTemplate->displayAccessDenied();
        }

        return $this->GenBlogCalendar(true, $iBlockID, BX_DOL_URL_ROOT);
    }

    /**
     * Blogs block for index page (as PHP function). List of latest posts.
     *
     * @return html of last blog posts
     */
    function serviceBlogsIndexPage($bShortPgn = true, $iPerPage = 0)
    {
        if (!$this->isAllowedBlogsPostsBrowse()) {
            return $this->_oTemplate->displayAccessDenied();
        }

        require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
        $oBlogSearch = new BxBlogsSearchUnit();
        $oBlogSearch->PerformObligatoryInit($this, 4);
        $oBlogSearch->aCurrent['paginate']['perPage'] = ($iPerPage > 0 ? $iPerPage : $this->_oConfig->getPerPage('index'));
        $aVis = array(BX_DOL_PG_ALL);
        if ($this->getUserId()) {
            $aVis[] = BX_DOL_PG_MEMBERS;
        }
        $oBlogSearch->aCurrent['restriction']['allow_view']['value'] = $aVis;
        $sCode = $oBlogSearch->displayResultBlock();
        $sPostPagination = $oBlogSearch->showPagination2(false, $this->genBlogLink('home'), $bShortPgn);

        if ($oBlogSearch->aCurrent['paginate']['totalNum'] > 0) {
            $sCodeBlock = $sCode;

            return array($sCodeBlock, false, $sPostPagination);
        }
    }

    /**
     * Blogs block for profile page (as PHP function). List of latest posts of member.
     *
     * @param $_iProfileID - member id
     *
     * @return html of last blog posts
     */
    function serviceBlogsProfilePage($_iProfileID)
    {
        if (!$this->isAllowedBlogsPostsBrowse()) {
            return $this->_oTemplate->displayAccessDenied();
        }

        $GLOBALS['oTopMenu']->setCurrentProfileID($_iProfileID);

        require_once($this->_oConfig->getClassPath() . 'BxBlogsSearchUnit.php');
        $oBlogSearch = new BxBlogsSearchUnit();
        $oBlogSearch->PerformObligatoryInit($this, 4);
        $oBlogSearch->aCurrent['paginate']['perPage'] = $this->_oConfig->getPerPage('profile');
        $oBlogSearch->aCurrent['restriction']['owner']['value'] = $_iProfileID;
        //$oBlogSearch->aCurrent['restriction']['publicStatus']['value'] = 'public';
        $sCode = $oBlogSearch->displayResultBlock();

        if ($oBlogSearch->aCurrent['paginate']['totalNum']) {
            return <<<EOF
<div class="bx-def-bc-padding">
    {$sCode}
</div>
EOF;
        }
    }

    /**
     * Printing of member`s blog post rss feeds
     *
     * @param bx_get ('pid') - member id
     *
     * @return html of blog posts of member
     */
    function serviceBlogsRss()
    {
        $iPID = (int)bx_get('pid');
        $aRssUnits = $this->_oDb->getMemberPostsRSS($iPID);
        if (is_array($aRssUnits) && count($aRssUnits) > 0) {

            foreach ($aRssUnits as $iUnitID => $aUnitInfo) {
                $iPostID = (int)$aUnitInfo['UnitID'];
                $aPost = array('Permalink' => $aUnitInfo['UnitUri'], 'Link' => $iPostID);
                $sPostLink = $this->genBlogLink('show_member_post', $aUser, '', $aPost);

                $aRssUnits[$iUnitID]['UnitLink'] = $sPostLink;

                $sFileName = $this->_oDb->getPostPhotoByID($iPostID);
                $sPostPhoto = ($sFileName != '') ? BX_BLOGS_IMAGES_URL . 'orig_' . $sFileName : '';
                $aRssUnits[$iUnitID]['UnitIcon'] = $sPostPhoto;
            }

            $sUnitTitleC = _t('_bx_blog_Blogs');
            $sMainLink = 'rss_factory.php?action=blogs&amp;pid=' . $iPID;

            bx_import('BxDolRssFactory');
            $oRssFactory = new BxDolRssFactory();
            $oRssFactory->SetRssHeader();
            echo $oRssFactory->GenRssByData($aRssUnits, $sUnitTitleC, $sMainLink);
            exit;
        }
    }

    /**
     * Get common blogs css
     *
     * @return void
     */
    function serviceGetCommonCss()
    {
        $this->_oTemplate->addCss('blogs_common.css');
    }

    /**
     * Get member menu item - my content
     *
     * @return html with generated menu item
     */
    function serviceGetMemberMenuItem()
    {
        $oMemberMenu = bx_instance('BxDolMemberMenu');

        $sPostsCnt = $this->_oDb->getMemberPostsCnt($this->_iVisitorID);

        $aUser = array('Permalink' => getUsername($iMemberID), 'Link' => $iMemberID);

        $aLinkInfo = array(
            'item_img_src' => 'book',
            'item_img_alt' => _t('_bx_blog_Posts'),
            'item_link'    => $this->genBlogLink('show_member_blog_home', $aUser, '', '', '', true),
            'item_title'   => _t('_bx_blog_Posts'),
            'extra_info'   => $sPostsCnt,
        );

        return $oMemberMenu->getGetExtraMenuLink($aLinkInfo);
    }

    /**
     * Get member menu item - add content
     *
     * @return html with generated menu item
     */
    function serviceGetMemberMenuItemAddContent()
    {
        if (!$this->isAllowedPostAdd()) {
            return '';
        }

        $oMemberMenu = bx_instance('BxDolMemberMenu');
        $aLinkInfo = array(
            'item_img_src' => 'book',
            'item_img_alt' => _t('_bx_blog_post'),
            'item_link'    => BX_DOL_URL_ROOT . (getParam('permalinks_blogs') == 'on' ? 'blogs/my_page/add/' : 'modules/boonex/blogs/blogs.php?action=my_page&mode=add'),
            'item_title'   => _t('_bx_blog_post'),
        );

        return $oMemberMenu->getGetExtraMenuLink($aLinkInfo);
    }

    /**
     * Get number of posts for particular member
     *
     * @return html with generated menu item
     */
    function serviceGetPostsCountForMember($iMemberId)
    {
        return $this->_oDb->getMemberPostsCnt((int)$iMemberId);
    }

    /*
    * Service - response profile delete
    */
    function serviceResponseProfileDelete($oAlert)
    {
        if (!($iProfileId = (int)$oAlert->iObject)) {
            return false;
        }

        $this->bAdminMode = true;
        $aPostsInCategory = $this->_oDb->getPostsInCategory(1, '', $iProfileId);
        foreach ($aPostsInCategory as $iKeyID => $iPostID) {
            $this->ActionDeletePost((int)$iPostID);
        }
        $aBlogInfo = $this->_oDb->getBlogInfo($iProfileId);
        $this->_oDb->deleteBlog((int)$aBlogInfo['ID']);

        return true;
    }

    function serviceGetWallData()
    {
        $sUri = $this->_oConfig->getUri();
        $sName = 'bx_' . $sUri;

        return array(
            'handlers' => array(
                array(
                    'alert_unit'    => $sName,
                    'alert_action'  => 'create',
                    'module_uri'    => $sUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_wall_post',
                    'groupable'     => 0,
                    'group_by'      => '',
                    'timeline'      => 1,
                    'outline'       => 1
                ),
                array(
                    'alert_unit'    => $sName,
                    'alert_action'  => 'comment_add',
                    'module_uri'    => $sUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_wall_add_comment',
                    'groupable'     => 0,
                    'group_by'      => '',
                    'timeline'      => 1,
                    'outline'       => 0
                ),

                //DEPRICATED, saved for backward compatibility
                array(
                    'alert_unit'    => $sName,
                    'alert_action'  => 'commentPost',
                    'module_uri'    => $sUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_wall_post_comment',
                    'groupable'     => 0,
                    'group_by'      => '',
                    'timeline'      => 1,
                    'outline'       => 0
                )
            ),
            'alerts'   => array(
                array('unit' => $sName, 'action' => 'create')
            )
        );
    }

    function serviceGetWallPost($aEvent)
    {
        if (!($aProfile = getProfileInfo($aEvent['owner_id']))) {
            return '';
        }

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',',
            $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $iDeleted = 0;
        $aItems = array();
        foreach ($aObjectIds as $iId) {
            $aItem = $this->_oDb->getPostInfo($iId);
            if (empty($aItem)) {
                $iDeleted++;
            } else {
                if ($aItem['PostStatus'] == 'approval' && $this->oPrivacy->check('view', $aItem['PostID'],
                        $this->_iVisitorID)
                ) {
                    $aItems[] = $aItem;
                }
            }
        }

        if ($iDeleted == count($aObjectIds)) {
            return array('perform_delete' => true);
        }

        $iOwner = 0;
        if(!empty($aEvent['owner_id']))
            $iOwner = (int)$aEvent['owner_id'];

        $iDate = 0;
        if(!empty($aEvent['date']))
            $iDate = (int)$aEvent['date'];

        $bItems = !empty($aItems) && is_array($aItems);
        if($iOwner == 0 && $bItems && !empty($aItems[0]['OwnerID']))
            $iOwner = (int)$aItems[0]['OwnerID'];

        if($iDate == 0 && $bItems && !empty($aItems[0]['PostDate']))
            $iDate = (int)$aItems[0]['PostDate'];

        if($iOwner == 0 || empty($aItems))
            return '';

        $sCss = '';
        if ($aEvent['js_mode']) {
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css', 'blogs_common.css'), true);
        } else {
            $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css', 'blogs_common.css'));
        }

        $iItems = count($aItems);
        $sOwner = getNickName($iOwner);

        //--- Grouped events
        if ($iItems > 1) {
            if ($iItems > 4) {
                $aItems = array_slice($aItems, 0, 4);
            }

            $aTmplItems = array();
            foreach ($aItems as $aItem) {
                $oTmpBlogSearch = false;
                $sPostUnit = $this->_GenPosts(5, 1, 'post', array('id' => $aItem['PostID']), 'last', $oTmpBlogSearch);
                if ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) {
                    continue;
                }

                $aTmplItems[] = array('unit' => $sPostUnit);
            }

            return array(
            	'owner_id' => $iOwner,
                'title' => _t('_bx_blog_wall_added_new_title_items', $sOwner, $iItems),
                'description' => '',
                'content' => $sCss . $this->_oTemplate->parseHtmlByName('wall_post_grouped.html', array(
	                'cpt_user_name' => $sOwner,
	                'cpt_added_new' => _t('_bx_blog_wall_added_new_items', $iItems),
	                'bx_repeat:items' => $aTmplItems,
	                'post_id' => $aEvent['id']
	            )),
	            'date' => $iDate
            );
        }

        //--- Single public event
        $aItem = $aItems[0];
        $aItem['url'] = $this->genUrl($aItem['PostID'], $aItem['PostUri'], 'entry');

        $oTmpBlogSearch = false;
        $sPostUnit = $this->_GenPosts(5, 1, 'post', array('id' => $aItem['PostID']), 'last', $oTmpBlogSearch);
        if ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) {
            return '';
        }

        $sTextWallObject = _t('_bx_blog_wall_object');

        return array(
        	'owner_id' => $iOwner,
            'title' => _t('_bx_blog_wall_added_new_title', $sOwner, $sTextWallObject),
            'description' => $aItem['PostText'],
            'content' => $sCss . $this->_oTemplate->parseHtmlByName('wall_post.html', array(
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t('_bx_blog_wall_added_new'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $aItem['url'],
	            'unit' => $sPostUnit,
	            'post_id' => $aEvent['id'],
	        )),
	        'date' => $iDate
        );
    }

    function serviceGetWallAddComment($aEvent)
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = $iOwner != 0 ? getNickName($iOwner) : _t('_Anonymous');

        $aContent = unserialize($aEvent['content']);
        if (empty($aContent) || empty($aContent['object_id'])) {
            return '';
        }

        $iItem = (int)$aContent['object_id'];
        $aItem = $this->_oDb->getPostInfo($iItem);
        if (empty($aItem) || !is_array($aItem)) {
            return array('perform_delete' => true);
        }

        if (!$this->oPrivacy->check('view', $iItem, $this->_iVisitorID)) {
            return;
        }

        bx_import('Cmts', $this->_aModule);
        $oCmts = new BxBlogsCmts($this->_oConfig->getCommentSystemName(), $iItem);
        if (!$oCmts->isEnabled()) {
            return '';
        }

        $aComment = $oCmts->getCommentRow($iId);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sCss = '';
        if ($aEvent['js_mode']) {
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css', 'blogs_common.css'), true);
        } else {
            $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css', 'blogs_common.css'));
        }

        $oTmpBlogSearch = false;
        $sPostUnit = $this->_GenPosts(5, 1, 'post', array('id' => $aItem['PostID']), 'last', $oTmpBlogSearch);
        if ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) {
            return '';
        }

        $aItem['url'] = $this->genUrl($aItem['ID'], $aItem['PostUri'], 'entry');

        $sTextWallObject = _t('_bx_blog_wall_object');

        return array(
            'title'       => _t('_bx_blog_wall_added_new_title_comment', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content'     => $sCss . $this->_oTemplate->parseHtmlByName('wall_post_comment.html', array(
                    'cpt_user_name'    => $sOwner,
                    'cpt_added_new'    => _t('_bx_blog_wall_added_new_comment'),
                    'cpt_object'       => $sTextWallObject,
                    'cpt_item_url'     => $aItem['url'],
                    'cnt_comment_text' => $aComment['cmt_text'],
                    'unit'             => $sPostUnit,
                    'post_id'          => $aEvent['id'],
                ))
        );
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostComment($aEvent)
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aItem = $this->_oDb->getPostInfo($iId);
        if (empty($aItem) || !is_array($aItem)) {
            return array('perform_delete' => true);
        }

        if (!$this->oPrivacy->check('view', $iId, $this->_iVisitorID)) {
            return;
        }

        $aContent = unserialize($aEvent['content']);
        if (empty($aContent) || !isset($aContent['comment_id'])) {
            return '';
        }

        bx_import('Cmts', $this->_aModule);
        $oCmts = new BxBlogsCmts($this->_oConfig->getCommentSystemName(), $iId);
        if (!$oCmts->isEnabled()) {
            return '';
        }

        $aItem['url'] = $this->genUrl($aItem['ID'], $aItem['PostUri'], 'entry');
        $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sCss = '';
        if ($aEvent['js_mode']) {
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css', 'blogs_common.css'), true);
        } else {
            $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css', 'blogs_common.css'));
        }

        $oTmpBlogSearch = false;
        $sPostUnit = $this->_GenPosts(5, 1, 'post', array('id' => $aItem['PostID']), 'last', $oTmpBlogSearch);
        if ($oTmpBlogSearch->aCurrent['paginate']['totalNum'] == 0) {
            return '';
        }

        $sTextWallObject = _t('_bx_blog_wall_object');

        return array(
            'title'       => _t('_bx_blog_wall_added_new_title_comment', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content'     => $sCss . $this->_oTemplate->parseHtmlByName('wall_post_comment.html', array(
                    'cpt_user_name'    => $sOwner,
                    'cpt_added_new'    => _t('_bx_blog_wall_added_new_comment'),
                    'cpt_object'       => $sTextWallObject,
                    'cpt_item_url'     => $aItem['url'],
                    'cnt_comment_text' => $aComment['cmt_text'],
                    'unit'             => $sPostUnit,
                    'post_id'          => $aEvent['id'],
                ))
        );
    }

    function serviceGetWallPostOutline($aEvent)
    {
        $sPrefix = 'bx_' . $this->_oConfig->getUri();
        $aProfile = getProfileInfo($aEvent['owner_id']);
        if (!$aProfile) {
            return '';
        }

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',',
            $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $iItems = count($aObjectIds);
        $iItemsLimit = 3;
        if ($iItems > $iItemsLimit) {
            $aObjectIds = array_slice($aObjectIds, 0, $iItemsLimit);
        }

        $bSave = false;
        $aContent = array();
        if (!empty($aEvent['content'])) {
            $aContent = unserialize($aEvent['content']);
        }

        if (!isset($aContent['idims'])) {
            $aContent['idims'] = array();
        }

        $iDeleted = 0;
        $aItems = $aTmplItems = array();
        foreach ($aObjectIds as $iId) {
            $aItem = $this->_oDb->getPostInfo($iId);
            if (empty($aItem)) {
                $iDeleted++;
            } else {
                if ($aItem['PostStatus'] == 'approval' && $this->oPrivacy->check('view', $aItem['PostID'],
                        $this->_iVisitorID)
                ) {
                    $aItem['thumb_file'] = '';
                    $aItem['thumb_dims'] = array();
                    if (!empty($aItem['PostPhoto'])) {
                        $aItem['thumb_file'] = BX_BLOGS_IMAGES_URL . 'browse_' . $aItem['PostPhoto'];
                        $aItem['thumb_file_path'] = BX_BLOGS_IMAGES_PATH . 'browse_' . $aItem['PostPhoto'];
                        if (!file_exists($aItem['thumb_file_path'])) {
                            $aItem['thumb_file'] = BX_BLOGS_IMAGES_URL . 'big_' . $aItem['PostPhoto'];
                            $aItem['thumb_file_path'] = BX_BLOGS_IMAGES_PATH . 'big_' . $aItem['PostPhoto'];
                        }

                        if (!isset($aContent['idims'][$iId])) {
                            $sPath = file_exists($aItem['thumb_file_path']) ? $aItem['thumb_file_path'] : $aItem['thumb_file'];
                            $aContent['idims'][$iId] = BxDolImageResize::instance()->getImageSize($sPath);
                            $bSave = true;
                        }

                        $aItem['thumb_dims'] = $aContent['idims'][$iId];

                        $aItem['thumb_file_2x'] = BX_BLOGS_IMAGES_URL . 'orig_' . $aItem['PostPhoto'];
                        $aItem['thumb_file_2x_path'] = BX_BLOGS_IMAGES_PATH . 'orig_' . $aItem['PostPhoto'];
                    }

                    $aItem['PostUrl'] = $this->genUrl($aItem['PostID'], $aItem['PostUri'], 'entry');
                    $aItems[] = $aItem;

                    $aTmplItems[] = array(
                        'mod_prefix'       => $sPrefix,
                        'item_width'       => isset($aItem['thumb_dims']['w']) ? $aItem['thumb_dims']['w'] : $this->iThumbSize,
                        'item_height'      => isset($aItem['thumb_dims']['h']) ? $aItem['thumb_dims']['h'] : $this->iThumbSize,
                        'item_icon'        => $aItem['thumb_file'],
                        'item_icon_2x'     => $aItem['thumb_file_2x'],
                        'item_page'        => $aItem['PostUrl'],
                        'item_title'       => $aItem['PostCaption'],
                        'item_description' => strmaxtextlen($aItem['PostText'], 300),
                    );
                }
            }
        }

        if ($iDeleted == count($aObjectIds)) {
            return array('perform_delete' => true);
        }

        if (empty($aItems)) {
            return '';
        }

        $aResult = array();
        if ($bSave) {
            $aResult['save']['content'] = serialize($aContent);
        }

        $sCss = '';
        if ($aEvent['js_mode']) {
            $sCss = $this->_oTemplate->addCss(array('wall_outline.css'), true);
        } else {
            $this->_oTemplate->addCss(array('wall_outline.css'));
        }

        $iItems = count($aItems);
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);
        $sOwnerLink = getProfileLink($iOwner);

        //--- Grouped events
        $iItems = count($aItems);
        if ($iItems > 1) {
            $sTmplName = 'wall_outline_grouped.html';
            $aResult['content'] = $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
                    'mod_prefix'         => $sPrefix,
                    'mod_icon'           => 'book',
                    'user_name'          => $sOwner,
                    'user_link'          => $sOwnerLink,
                    'bx_repeat:items'    => $aTmplItems,
                    'item_comments'      => 0 ? _t('_wall_n_comments', 0) : _t('_wall_no_comments'),
                    'item_comments_link' => '',
                    'post_id'            => $aEvent['id'],
                    'post_ago'           => $aEvent['ago']
                ));

            return $aResult;
        }

        //--- Single public event
        $aItem = $aItems[0];
        $aTmplItem = $aTmplItems[0];

        $sTmplName = empty($aItem['thumb_file']) ? 'modules/boonex/wall/|outline_item_text.html' : 'modules/boonex/wall/|outline_item_image.html';
        $aResult['content'] = $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array_merge($aTmplItem, array(
                'mod_prefix'         => $sPrefix,
                'mod_icon'           => 'book',
                'user_name'          => $sOwner,
                'user_link'          => $sOwnerLink,
                'item_comments'      => (int)$aItem['CommentsCount'] > 0 ? _t('_wall_n_comments',
                    $aItem['CommentsCount']) : _t('_wall_no_comments'),
                'item_comments_link' => $aItem['PostUrl'] . '#cmta-' . $sPrefix . '-' . $aItem['PostID'],
                'post_id'            => $aEvent['id'],
                'post_ago'           => $aEvent['ago']
            )));

        return $aResult;
    }

    /**
     * Get Spy data
     *
     * @returm array of necessary parameters
     */
    function serviceGetSpyData()
    {
        return array(
            'handlers' => array(
                array(
                    'alert_unit'    => 'bx_blogs',
                    'alert_action'  => 'create',
                    'module_uri'    => 'blogs',
                    'module_class'  => 'Module',
                    'module_method' => 'get_spy_post'
                ),
                array(
                    'alert_unit'    => 'bx_blogs',
                    'alert_action'  => 'rate',
                    'module_uri'    => 'blogs',
                    'module_class'  => 'Module',
                    'module_method' => 'get_spy_post'
                ),
                array(
                    'alert_unit'    => 'bx_blogs',
                    'alert_action'  => 'commentPost',
                    'module_uri'    => 'blogs',
                    'module_class'  => 'Module',
                    'module_method' => 'get_spy_post'
                )
            ),
            'alerts'   => array(
                array('unit' => 'bx_blogs', 'action' => 'create'),
                array('unit' => 'bx_blogs', 'action' => 'rate'),
                array('unit' => 'bx_blogs', 'action' => 'delete_post'),
                array('unit' => 'bx_blogs', 'action' => 'commentPost'),
                array('unit' => 'bx_blogs', 'action' => 'commentRemoved')
            )
        );
    }

    /**
     * Get Spy post
     *
     * $sAction - name of accepted action
     * $iObjectId - object id
     * $iSenderId - sender id
     *
     * @returm array of necessary parameters
     */
    function serviceGetSpyPost($sAction, $iObjectId = 0, $iSenderId = 0, $aExtraParams = array())
    {
        $aRet = array();

        $aPostInfo = $this->_oDb->getPostInfo($iObjectId);
        if (!$aPostInfo['OwnerID']) {
            return $aRet;
        }

        $sRecipientNickName = getNickName($aPostInfo['OwnerID']);
        $sRecipientProfileLink = getProfileLink($aPostInfo['OwnerID']);
        $sSenderNickName = $iSenderId ? getNickName($iSenderId) : _t('_Guest');
        $sSenderProfileLink = $iSenderId ? getProfileLink($iSenderId) : 'javascript:void(0)';
        $sCaption = $aPostInfo['PostCaption'];
        $sEntryUrl = $this->genUrl($iObjectId, $aPostInfo['PostUri']);

        $sLangKey = '';
        $iRecipientId = 0;
        switch ($sAction) {
            case 'create' :
                $sLangKey = '_bx_blog_added_spy';
                $iRecipientId = 0;
                break;

            case 'rate' :
                $sLangKey = '_bx_blog_rated_spy';
                $iRecipientId = $aPostInfo['OwnerID'];
                break;

            case 'commentPost' :
                $sLangKey = '_bx_blog_commented_spy';
                $iRecipientId = $aPostInfo['OwnerID'];
                break;
        }

        return array(
            'lang_key'     => $sLangKey,
            'params'       => array(
                'recipient_p_link' => $sRecipientProfileLink,
                'recipient_p_nick' => $sRecipientNickName,
                'profile_nick'     => $sSenderNickName,
                'profile_link'     => $sSenderProfileLink,
                'post_url'         => $sEntryUrl,
                'post_caption'     => $sCaption,
            ),
            'recipient_id' => $iRecipientId,
            'spy_type'     => 'content_activity',
        );
    }

    /**
     * Fired when post status is changed to approved or disapproved
     */
    function onPostApproveDisapprove($iBPostID, $isApprove)
    {
        $aPostInfo = $this->_oDb->getPostInfo($iBPostID);
        if (!$aPostInfo) {
            return;
        }

        //reparse tags
        bx_import('BxDolTags');
        $oTags = new BxDolTags();
        $oTags->reparseObjTags('blog', $iBPostID);

        //reparse categories
        bx_import('BxDolCategories');
        $oCategories = new BxDolCategories($aPostInfo['OwnerID']);
        $oCategories->reparseObjTags('bx_blogs', $iBPostID);

        $oZ = new BxDolAlerts('bx_blogs', $isApprove ? 'approve' : 'disapprove', $iBPostID, $this->_iVisitorID);
        $oZ->alert();
    }
}
