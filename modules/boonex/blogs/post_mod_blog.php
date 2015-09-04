<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('../../../inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );

//require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $aModule['class_prefix'] . 'Module.php');
bx_import('BxDolModuleDb');
require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/blogs/classes/BxBlogsModule.php');

$logged['admin'] = member_auth( 1, true, true );

$oModuleDb = new BxDolModuleDb();
$aModule = $oModuleDb->getModuleByUri('blogs');

$oBlogs = new BxBlogsModule($aModule);
$sHeaderValue = $oBlogs->GetHeaderString();

$iNameIndex = 9;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('common.css', 'forms_adv.css', 'blogs_common.css', 'blogs.css'),
    'header' => $sHeaderValue,
    'header_text' => $sHeaderValue
);
$_page_cont[$iNameIndex]['page_main_code'] = PageCompBlogs($oBlogs);
PageCodeAdmin();

function PageCompBlogs($oBlogs)
{
    $sCss = $oBlogs->_oTemplate->addCss(array('blogs.css', 'blogs_common.css'), true);
    $sRetHtml = $sCss . $oBlogs->GenCommandForms();

    switch (bx_get('action')) {
        case 'top_blogs':
            $sRetHtml .= $oBlogs->GenBlogLists('top');
            break;
        case 'show_admin_blog':
            $sRetHtml .= $oBlogs->GenMemberBlog(0);
            break;
        case 'show_member_blog':
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
        default:
            $sRetHtml .= $oBlogs->GenAdminTabbedPage();
            break;
    }

    return $sRetHtml;
}
