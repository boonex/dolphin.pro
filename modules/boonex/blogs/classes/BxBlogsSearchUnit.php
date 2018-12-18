<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'header.inc.php' );
bx_import('BxTemplCmtsView');
bx_import('BxTemplSearchResultText');

if (!defined('BX_BLOGS_IMAGES_PATH')) {
    define('BX_BLOGS_IMAGES_PATH', BX_DIRECTORY_PATH_ROOT . "media/images/blog/");
}
if (!defined('BX_BLOGS_IMAGES_URL')) {
    define('BX_BLOGS_IMAGES_URL', BX_DOL_URL_ROOT . "media/images/blog/");
}

class BxBlogsSearchUnit extends BxTemplSearchResultText
{
    var $sHomePath;
    var $sHomeUrl;
    var $iPostViewType; // 2 - with author name; 3 - without link at title and with image; 4 - with member icon; 5 - without date and owner(short)
    var $sMobileWrapper = false;

    var $bAdminMode;
    var $bShowCheckboxes;

    var $aCurrent = array(
        'name' => 'blogposts',
        'title' => '_bx_blog_Blogs',
        'table' => 'bx_blogs_posts',
        'ownFields' => array('PostID', 'PostCaption', 'PostUri', 'PostDate', 'PostText', 'Tags', 'PostPhoto','PostStatus', 'Rate', 'RateCount', 'CommentsCount', 'Categories', 'Views'),
        'searchFields' => array('PostCaption', 'PostText', 'Tags'),
        'join' => array(
            'profile' => array(
                'type' => 'left',
                'table' => 'Profiles',
                'mainField' => 'OwnerID',
                'onField' => 'ID',
                'joinFields' => array('NickName')
            )
        ),
        'restriction' => array(
            'activeStatus' => array('value'=>'approval', 'field'=>'PostStatus', 'operator'=>'='),
            'featuredStatus' => array('value'=>'', 'field'=>'Featured', 'operator'=>'='),
            'owner' => array('value'=>'', 'field'=>'OwnerID', 'operator'=>'='),
            'tag' => array('value'=>'', 'field'=>'Tags', 'operator'=>'like'),
            'tag2' => array('value'=>'', 'field'=>'Tags', 'operator'=>'against', 'paramName'=>'tag'),
            'id'=> array('value'=>'', 'field'=>'PostID', 'operator'=>'='),
            'category_uri'=> array('value'=>'', 'field'=>'Categories', 'operator'=>'against', 'paramName'=>'uri'),
            'allow_view' => array('value'=>'', 'field'=>'allowView', 'operator'=>'in', 'table'=> 'bx_blogs_posts'),
        ),
        'paginate' => array('perPage' => 4, 'page' => 1, 'totalNum' => 10, 'totalPages' => 1),
        'sorting' => 'last'
    );

    var $aPermalinks;

    //max sizes of pictures for resizing during upload
    var $iIconSize;
    var $iThumbSize;
    var $iBigThumbSize;
    var $iImgSize;

    var $sSearchedTag;

    function __construct($oBlogObject = null)
    {
        $this->bShowCheckboxes = false;
        $this->bAdminMode = false;

        $oMain = $this->getBlogsMain();

        $this->iIconSize = $oMain->iIconSize;
        $this->iThumbSize = $oMain->iThumbSize;
        $this->iBigThumbSize = $oMain->iBigThumbSize;
        $this->iImgSize = $oMain->iImgSize;

        if ($oMain->isAdmin()) {
            $this->bAdminMode = true;
            //$this->bShowCheckboxes = true;
        }

        $this->sHomeUrl = $oMain->_oConfig->getHomeUrl();
        $this->sHomePath = $oMain->_oConfig->getHomePath();

        $this->aPermalinks = array(
            'param' => 'permalinks_blogs',
            'enabled' => array(
                'file' => 'blogs/entry/{uri}',
                'category' => 'blogs/posts/{ownerName}/category/{uri}',
                'member' => 'blogs/posts/{ownerName}',
                'tag' => 'blogs/tag/{uri}',
                'browseAll' => 'blogs/',
                'admin_file' => 'blogs/entry/{uri}',
                'admin_category' => 'blogs/posts/{ownerName}/category/{uri}',
                'admin_member' => 'blogs/posts/{ownerName}',
                'admin_tag' => 'blogs/tag/{uri}',
                'admin_browseAll' => 'blogs/',
                                'last_posts' => 'blogs/all_posts/',
                                'popular_posts' => 'blogs/popular_posts/',
                                'top_posts' => 'blogs/top_posts/',
            ),
            'disabled' => array(
                'file' => 'blogs.php?action=show_member_post&post_id={id}',
                'category' => 'blogs.php?action=show_member_blog&ownerID={ownerId}&category={id}',
                'member' => 'blogs.php?action=show_member_blog&ownerID={ownerId}',
                'tag' => 'blogs.php?action=search_by_tag&tagKey={uri}',
                'browseAll' => 'blogs.php',
                'admin_file' => 'blogs.php?action=show_member_post&post_id={id}',
                'admin_category' => 'blogs.php?action=show_member_blog&ownerID={ownerId}&category={id}',
                'admin_member' => 'blogs.php?action=show_member_blog&ownerID={ownerId}',
                'admin_tag' => 'blogs.php?action=search_by_tag&tagKey={uri}',
                'admin_browseAll' => 'blogs.php',
                                'last_posts' => 'blogs.php?action=all_posts',
                                'popular_posts' => 'blogs.php?action=popular_posts',
                                'top_posts' => 'blogs.php?action=top_posts'
            )
        );

        if(!$oBlogObject) {
            $oBlogObject =  BxDolModule::getInstance('BxBlogsModule');
        }

        if ( $this->bAdminMode || ( is_object($oBlogObject) && ($oBlogObject -> isAllowedApprove()
            || $oBlogObject -> isAllowedPostEdit(-1) || $oBlogObject -> isAllowedPostDelete(-1)) )) {

            $this->aCurrent['restriction']['activeStatus'] = '';
        }
        parent::__construct();

        $this->iPostViewType = 4;
        $this->sSearchedTag = '';
    }

    function getBlogsMain()
    {
        return BxDolModule::getInstance('BxBlogsModule');
    }

    function addCustomParts()
    {
        $oMain = $this->getBlogsMain();
        return $oMain->serviceGetCommonCss();
    }

    function PerformObligatoryInit(&$oBlogsModule, $iPostViewType = 2, $sMobileWrapper = false)
    {
        $GLOBALS['oBxBlogsModule'] = $oBlogsModule;
        $oMain = $this->getBlogsMain();

        $this->sHomePath = $oMain->_oConfig->getHomePath();
        $this->sHomeUrl = $oMain->_oConfig->getHomeUrl();

        $this->iPostViewType = $iPostViewType;

        $this->sMobileWrapper = $sMobileWrapper;
    }

    function getCurrentUrl($sType, $iId, $sUri, $aOwner = '')
    {
        if ($this->bAdminMode && isset($this->aConstants['linksTempl']['admin_' . $sType])) {
            $sType = 'admin_' . $sType;
        }

        $sLink = $this->aConstants['linksTempl'][$sType];
        $sLink = str_replace('{id}', $iId, $sLink);
        $sLink = str_replace('{uri}', $sUri, $sLink);
        if (is_array($aOwner) && !empty($aOwner)) {
            $sLink = str_replace('{ownerName}', $aOwner['ownerName'], $sLink);
            $sLink = str_replace('{ownerId}', $aOwner['ownerId'], $sLink);
        }

        $oMain = $this->getBlogsMain();
        return ($oMain->isPermalinkEnabled()) ? BX_DOL_URL_ROOT . $sLink : $this->sHomeUrl . $sLink;
    }

    function displaySearchBox ($sCode, $sPaginate = '', $bAdminBox = false)
    {
        $sCode = DesignBoxContent(_t($this->aCurrent['title']), '<div class="bx-def-bc-padding">'.$sCode .'<div class="clear_both"></div></div>'. $sPaginate, 1);
        if (true !== bx_get('searchMode'))
            $sCode = '<div id="page_block_'.$this->id.'">'.$sCode.'<div class="clear_both"></div></div>';
        return $sCode;
    }

    function displaySearchUnit($aResSQL)
    {
        $iVisitorID = getLoggedId();

        $oMain = $this->getBlogsMain();

        $iPostID = (int)$aResSQL['id'];
        $sBlogsImagesUrl = BX_BLOGS_IMAGES_URL;

        $bPossibleToView = $oMain->oPrivacy->check('view', $iPostID, $oMain->_iVisitorID);
        if (!$bPossibleToView) {
            if ($this->sMobileWrapper)
                return $this->_wrapMobileUnit ($oMain->_oTemplate->parseHtmlByTemplateName('browse_unit_private_mobile', array()), $iPostID, $oMain);
            else
                return $oMain->_oTemplate->parseHtmlByName('browse_unit_private.html', array('extra_css_class' => ''));
        }

        $sCategories = $aResSQL['Categories'];
        $aCategories = $oMain->getTagLinks($aResSQL['Categories'], 'category', CATEGORIES_DIVIDER);

        $sStyle = '';
        $sFriendStyle = '';
        $sPostVote = '';
        $sPostMode = '';
        $sVotePostRating = $this->oRate->getJustVotingElement(0, 0, $aResSQL['Rate']);

        $aProfileInfo = getProfileInfo($aResSQL['ownerId']);
        $sAuthorTitle = process_line_output(getNickName($aProfileInfo['ID']));
        $sAuthorUsername = getUsername($aProfileInfo['ID']);
        $sAuthorLink = getProfileLink($aProfileInfo['ID']);

        $sCategoryName = $aResSQL['Categories'];
        $sPostLink = $this->getCurrentUrl('file', $iPostID, $aResSQL['uri']) . $sCategoryUrlAdd;

        $sAllCategoriesLinks = '';
        if (count($aCategories)>0) {
            foreach ($aCategories as $iKey => $sCatValue) {
                $sCatLink = $this->getCurrentUrl('category', title2uri($sCatValue), title2uri($sCatValue), array('ownerId' => $aResSQL['ownerId'], 'ownerName' => $sAuthorUsername));
                $sCatName = process_line_output($sCatValue);
                $aAllCategoriesLinks[] = '<a href="' . $sCatLink . '">' . $sCatName . '</a>';
            }
            $aAllCategoriesLinkHrefs = implode(", ", $aAllCategoriesLinks);
            $sAllCategoriesLinks = <<<EOF
<span class="margined">
    <span>{$aAllCategoriesLinkHrefs}</span>
</span>
EOF;
        }

        $sAdminCheck = $sAdminStatus = '';
        if ($this->bShowCheckboxes) {
            $sAdminCheck = <<<EOF
<div class="browseCheckbox"><input id="ch{$iPostID}" type="checkbox" name="bposts[]" value="{$iPostID}" /></div>
EOF;

            $sPostStatus = process_line_output($aResSQL['PostStatus']);
            $sAdminStatus = <<<EOF
&nbsp;({$sPostStatus})
EOF;
        }

        $sPostCaption = process_line_output($aResSQL['title']);
        $sPostCaptionHref = <<<EOF
<a class="unit_title bx-def-font-h2" href="{$sPostLink}">{$sPostCaption}</a>{$sAdminStatus}
EOF;

        if ($this->iPostViewType==3 || $this->sMobileWrapper) {
            $sFriendStyle="2";
            $sPostMode = '_post';
            $sPostCaptionHref = '<div class="unit_title bx-def-font-h2">'.$sPostCaption.'</div>';
        }

        $sDateTime = defineTimeInterval($aResSQL['date']);

        //$oCmtsView = new BxTemplCmtsView ('blogposts', (int)$iPostID);
        $iCommentsCnt = (int)$aResSQL['CommentsCount'];
        $iViewsCnt = (int)$aResSQL['Views'];

        $sTagsCommas = $aResSQL['tag'];
        //$aTags = split(',', $sTagsCommas);
        $aTags = preg_split("/[;,]/", $sTagsCommas);

        //search by tag skiping
        if ( $this->sSearchedTag != '' && in_array($this->sSearchedTag,$aTags)==false ) return;

        $sTagsHrefs = '';
        $aTagsHrefs = array();
        foreach($aTags as $sTagKey) {
            if ($sTagKey != '') {
                $sTagLink = $this->getCurrentUrl('tag', $iPostID, htmlspecialchars(title2uri($sTagKey)));
                $sTagsHrefAny = <<<EOF
<a href="{$sTagLink}" title="{$sTagKey}">{$sTagKey}</a>
EOF;
                $aTagsHrefs[] = $sTagsHrefAny;
            }
        }
        $sTagsHrefs = implode(", ", $aTagsHrefs);

        $sTags = <<<EOF
<span class="margined">
    <span>{$sTagsHrefs}</span>
</span>
EOF;

        $sPostText = $aResSQL['bodyText'];
        $bOwner = ($iVisitorID==$aResSQL['ownerId']) ? true : false;

        $sOwnerThumb = $sPostPicture = $sPreviewPicture = '';
        if($aResSQL['PostPhoto'] && in_array($this->iPostViewType, array(1, 3, 4, 5))) {
        	$oMain->_oTemplate->addJs('plugins/fancybox/|jquery.fancybox.js');
        	$oMain->_oTemplate->addCss('plugins/fancybox/|jquery.fancybox.css');

            $sPostPicture = $oMain->_oTemplate->parseHtmlByName('picture_preview.html', array(
            	'img_url_big' => $sBlogsImagesUrl . 'orig_' . $aResSQL['PostPhoto'],
            	'img_url_small' => $sBlogsImagesUrl . 'big_' . $aResSQL['PostPhoto']
            ));
        }

        if ($this->iPostViewType==4) {
            $sOwnerThumb = $GLOBALS['oFunctions']->getMemberIcon($aResSQL['ownerId'], 'left');
        }

        if (in_array($this->iPostViewType, array(1, 4, 5))) {
            $iBlogLimitChars = (int)getParam('max_blog_preview');
            $sPostText = trim(strip_tags($sPostText));
            if (mb_strlen($sPostText) > $iBlogLimitChars) {
                $sPostText = mb_substr( $sPostText, 0, $iBlogLimitChars);
                $sLinkMore = $this->sMobileWrapper ? '' : ' <a title="' . htmlspecialchars_adv(_t('_Read more')) . '" href="' . $sPostLink . '">&hellip;</a>';
            }
            $sPostText = htmlspecialchars_adv($sPostText) . $sLinkMore;
        }

        $aUnitReplace = array(
            'checkbox' => $sAdminCheck,
            'post_caption' => $sPostCaptionHref,
            'author_title' => $sAuthorTitle,
            'author_username' => $sAuthorUsername,
            'author_link' => $sAuthorLink,
            'post_date' => $sDateTime,
            'all_categories' => $sAllCategoriesLinks,
            'comments_count' => $iCommentsCnt,
            'views_count' => $iViewsCnt,
            'post_tags' => $sTags,
            'friend_style' => $sFriendStyle,
            'post_uthumb' => $sOwnerThumb,
            'post_picture2' => $sPostPicture,
            'preview_picture' => $sPreviewPicture,
            'post_description' => $sPostText,
            'post_vote' => $sVotePostRating,
            'post_mode' => $sPostMode,
            'style' => $sStyle,
            'bx_if:full' => array (
                'condition' => $this->iPostViewType != 5,
                'content' => array (
                    'author_title' => $sAuthorTitle,
                    'author_username' => $sAuthorUsername,
                    'author_link' => $sAuthorLink,
                    'post_date' => $sDateTime,
                    'comments_count' => $iCommentsCnt,
                    'views_count' => $iViewsCnt,
                ),
            ),
        );

        if ($this->sMobileWrapper) {
            return $this->_wrapMobileUnit ($oMain->_oTemplate->parseHtmlByTemplateName('blogpost_unit_mobile', $aUnitReplace), $iPostID, $oMain);
        } else {
            return $oMain->_oTemplate->parseHtmlByTemplateName('blogpost_unit', $aUnitReplace);
        }
    }

    function setSorting ()
    {
        $this->aCurrent['sorting'] = (false !== bx_get('blogs_mode')) ? bx_get('blogs_mode') : $this->aCurrent['sorting'];

        if( $this->aCurrent['sorting'] != 'top' && $this->aCurrent['sorting'] != 'last' && $this->aCurrent['sorting'] != 'score' && $this->aCurrent['sorting'] != 'popular')
            $this->aCurrent['sorting'] = 'last';
    }

    function getAlterOrder()
    {
        if ($this->aCurrent['sorting'] == 'popular') {
            $aSql = array();
            $aSql['order'] = " ORDER BY `CommentsCount` DESC, `PostDate` DESC";
            return $aSql;
        }
        return array();
    }

    function showPagination($aParams = array())
    {
        $aLinkAddon = $this->getLinkAddByPrams();
        $oPaginate = new BxDolPaginate(array(
            'page_url' => $this->aCurrent['paginate']['page_url'],
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'on_change_page' => 'return !loadDynamicBlock('.$this->id.', \'searchKeywordContent.php?searchMode=ajax&blogs_mode='.$this->aCurrent['sorting'].'&section[]=blog&keyword='.bx_get('keyword').$aLinkAddon['params'].'&page={page}&per_page={per_page}\');',
            'on_change_per_page' => 'return !loadDynamicBlock('.$this->id.', \'searchKeywordContent.php?searchMode=ajax&blogs_mode='.$this->aCurrent['sorting'].'&section[]=blog&keyword='.bx_get('keyword').$aLinkAddon['params'].'&page=1&per_page=\' + this.value);'
        ));
        $sPaginate = '<div class="clear_both"></div>'.$oPaginate->getPaginate();

        return $sPaginate;
    }

    function showPagination3($bAdmin = false)
    {
            bx_import('BxDolPaginate');
            $sPgnAdd = false === strpos($this->aCurrent['paginate']['page_url'], '{page}') ? 'per_page={per_page}&page={page}' : '';
            $oPaginate = new BxDolPaginate(array(
                'page_url' => bx_append_url_params($this->aCurrent['paginate']['page_url'], $sPgnAdd),
                'count' => $this->aCurrent['paginate']['totalNum'],
                'per_page' => $this->aCurrent['paginate']['perPage'],
                'page' => $this->aCurrent['paginate']['page'],
            ));

            $sPaginate = '<div class="clear_both"></div>'.$oPaginate->getPaginate();

        return $sPaginate;
    }

    function showPagination2($bAdmin = false, $sOverrideViewAllUrl = false, $bShort = true)
    {
        bx_import('BxDolPaginate');
        $aLinkAddon = $this->getLinkAddByPrams();

        $sAllUrl = $sOverrideViewAllUrl ? $sOverrideViewAllUrl : $this->getCurrentUrl('browseAll', 0, '');
        $sLink = bx_html_attribute($_SERVER['PHP_SELF']) . '?blogs_mode=' . $this->aCurrent['sorting'] . $aLinkAddon['params'];
        $oPaginate = new BxDolPaginate(array(
            'page_url' => $sLink,
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'on_change_page' => 'return !loadDynamicBlock({id}, \'' . $sLink . '&page={page}&per_page={per_page}\');',
            'on_change_per_page' => 'return !loadDynamicBlock({id}, \'' . $sLink . '&page=1&per_page=\' + this.value);',
        ));

        $sPaginate = $bShort ? $oPaginate->getSimplePaginate($sAllUrl) : $oPaginate->getPaginate();
                $sPaginate = '<div class="clear_both"></div>' . $sPaginate;

        return $sPaginate;
    }

    function showPaginationAjax($sContainerId, $sBaseUrl = false)
    {
        bx_import('BxDolPaginate');

        $sLink = BX_DOL_URL_ROOT . ($sBaseUrl ? $sBaseUrl : $this->aCurrent['paginate']['page_url']);
        $sLink = bx_append_url_params($sLink, 'ajax=1');
        
        $oPaginate = new BxDolPaginate(array(
            'page_url' => $sLink,
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'on_change_page' => 'getHtmlData(\'' . bx_html_attribute($sContainerId) . '\', \'' . bx_html_attribute($sLink) . '&page={page}&per_page={per_page}\'); return false;',
            'on_change_per_page' => 'getHtmlData(\'' . bx_html_attribute($sContainerId) . '\', \'' . bx_html_attribute($sLink) . '&page=1&per_page=\' + this.value); return false;',
        ));

        $sPaginate = '<div class="clear_both"></div>' . $oPaginate->getPaginate();

        return $sPaginate;
    }

    function _getPseud ()
    {
      return array(
            'id' => 'PostID',
            'title' => 'PostCaption',
            'date' => 'PostDate',
            'uri' => 'PostUri',
            'categoryName' => 'CategoryName',
            'categoryUri' => 'CategoryUri',
            'ownerId' => 'OwnerID',
            'ownerName' => 'NickName',
            'bodyText' => 'PostText',
            'countComment' => 'cmt_id',
            'tag' => 'Tags'
     );
    }

    function _wrapMobileUnit ($sContent, $iPostID, $oMain)
    {
        $aVars = array (
            'content' => $sContent,
            'url' => bx_js_string($oMain->genBlogSubUrl() . '?action=mobile&mode=post&id=' . $iPostID),
        );
        bx_import('BxDolMobileTemplate');
        $oMobileTemplate = new BxDolMobileTemplate($oMain->_oConfig, $oMain->_oDb);
        return $oMobileTemplate->parseHtmlByName($this->sMobileWrapper, $aVars);
    }
}
