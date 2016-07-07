<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('../../../inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');

//require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $aModule['class_prefix'] . 'Module.php');
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php');
require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/blogs/classes/BxBlogsModule.php');

// --------------- page variables and login
$_page['name_index']	= 49;

check_logged();

$oModuleDb = new BxDolModuleDb();
$aModule = $oModuleDb->getModuleByUri('blogs');

$oBlogs = new BxBlogsModule($aModule);
$sHeaderValue = $oBlogs->GetHeaderString();

if ('mobile' == bx_get('action')) {
    $oBlogs->GenPostListMobile((int)bx_get('author'), bx_get('mode'));
    exit;
}

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompBlogs($oBlogs);

$oBlogs->_oTemplate->setPageTitle($sHeaderValue);
$oBlogs->_oTemplate->setPageMainBoxTitle($sHeaderValue);

$oBlogs->_oTemplate->addCss(array('blogs.css', 'blogs_common.css'));

function PageCompBlogs($oBlogs)
{
    $sRetHtml = '';
    $sRetHtml .= $oBlogs->GenCommandForms();

    switch (bx_get('action')) {
        case 'top_blogs':
            $sRetHtml .= $oBlogs->GenBlogLists('top');
            break;
        case 'show_admin_blog':
            $sRetHtml .= $oBlogs->GenMemberBlog(0);
            break;
        case 'show_member_blog':                        
            if (isset($_SERVER['REQUEST_URI']) && false !== strpos($_SERVER['REQUEST_URI'], '/member_posts/')) { 
                // redirect from old page to the new one
                $s = $oBlogs->genBlogLink('show_member_blog_home', array('Permalink' => getUsername((int)bx_get('ownerID')), 'Link' => (int)bx_get('ownerID')), '', '', '', true);
                header("HTTP/1.1 301 Moved Permanently");
                header('Location:' . $s);
                exit;
            }
            $sRetHtml .= $oBlogs->ActionChangeFeatureStatus();
            $sRetHtml .= $oBlogs->GenMemberBlog();
            break;
        case 'popular_posts':
            $sRetHtml .= $oBlogs->GenPostLists('popular');
            break;
        case 'top_posts':
            $sRetHtml .= $oBlogs->GenPostLists('top');
            break;
        case 'all_posts':
            $sRetHtml .= $oBlogs->GenPostLists('last');
            break;
        case 'featured_posts':
            $sRetHtml .= $oBlogs->GenPostLists('featured');
            break;
        case 'my_page':
            $sRetHtml .= $oBlogs->GenMyPageAdmin(bx_get('mode'));
            break;
        case 'new_post':
            $sRetHtml .= $oBlogs->AddNewPostForm();
            break;
        case 'show_member_post':
            $sRetHtml .= $oBlogs->ActionChangeFeatureStatus();
            $sRetHtml .= $oBlogs->GenPostPage();
            break;
        case 'search_by_tag':
            $sRetHtml .= $oBlogs->GenSearchResult();
            break;
        case 'add_category':
            $sRetHtml .= $oBlogs->GenAddCategoryForm();
            break;
        case 'edit_post':
            $iPostID = (int)bx_get('EditPostID');
            $sRetHtml .= $oBlogs->AddNewPostForm($iPostID);
            break;
        case 'create_blog':
            $sRetHtml .= $oBlogs->GenCreateBlogForm();
            break;
        case 'edit_blog':
            $sRetHtml .= $oBlogs->ActionEditBlog();
            $iBlogID = (int)bx_get('EditBlogID');
            $iOwnerID = (int)bx_get('EOwnerID');
            $sRetHtml .= $oBlogs->GenMemberBlog($iOwnerID);
            break;
        case 'delete_blog':
            $sRetHtml .= $oBlogs->ActionDeleteBlogSQL();
            $sRetHtml .= $oBlogs->GenBlogLists('last');
            break;
        case 'del_img':
            $sRetHtml .= $oBlogs->ActionDelImg();
            if (bx_get('mode')=='ajax') {
                exit;
            }
            $sRetHtml .= $oBlogs->GenPostPage();
            break;
        case 'delete_post':
            $iPostID = (int)bx_get('DeletePostID');
            $sRetHtml .= $oBlogs->ActionDeletePost($iPostID);
            $sRetHtml .= $oBlogs->GenMemberBlog($oBlogs->_iVisitorID);
            break;
        case 'category':
            $sRetHtml .= $oBlogs->GenPostsOfCategory();
            break;
        case 'show_calendar':
            $sRetHtml .= $oBlogs->GenBlogCalendar();
            break;
        case 'show_calendar_day':
            $sRetHtml .= $oBlogs->GenPostCalendarDay();
            break;
        case 'home':
            $sRetHtml .= $oBlogs->GenBlogHome();
            break;
        case 'tags':
            $sRetHtml .= $oBlogs->GenTagsPage();
            break;
        case 'share_post':
            $oBlogs->ActionSharePopup($_GET['post_id']);
            break;
        default:
            $sRetHtml .= $oBlogs->GenBlogLists('last');
            break;
    }

    return $sRetHtml;
}

if ($oBlogs->_iVisitorID) {
    $sVisitorNickname = getUsername($oBlogs->_iVisitorID);
    $sVisitorBlogLink = $oBlogs->genBlogLink('show_member_blog_home', array('Permalink'=>$sVisitorNickname, 'Link'=>$oBlogs->_iVisitorID), '', '', '', true);
    $aOpt = array('only_menu' => 1, 'blog_owner_link' => $sVisitorBlogLink);
    $GLOBALS['oTopMenu']->setCustomSubActions($aOpt, 'bx_blogs', true);
}

PageCode($oBlogs->_oTemplate);
