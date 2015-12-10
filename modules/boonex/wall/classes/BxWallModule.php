<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxDolPrivacy');
bx_import('BxDolPaginate');
bx_import('BxDolRssFactory');
bx_import('BxDolAdminSettings');

require_once( BX_DIRECTORY_PATH_PLUGINS . 'Services_JSON.php' );

require_once('BxWallCmts.php');
require_once('BxWallVoting.php');
require_once('BxWallPrivacy.php');
require_once('BxWallResponse.php');

define('BX_WALL_FILTER_ALL', 'all');
define('BX_WALL_FILTER_OWNER', 'owner');
define('BX_WALL_FILTER_OTHER', 'other');

define('BX_WALL_VIEW_TIMELINE', 'timeline');
define('BX_WALL_VIEW_OUTLINE', 'outline');

define('BX_WALL_PARSE_TYPE_TEXT', 'text');
define('BX_WALL_PARSE_TYPE_LINK', 'link');
define('BX_WALL_PARSE_TYPE_PHOTOS', 'photos');
define('BX_WALL_PARSE_TYPE_SOUNDS', 'sounds');
define('BX_WALL_PARSE_TYPE_VIDEOS', 'videos');
define('BX_WALL_PARSE_TYPE_REPOST', 'repost');

define('BX_WALL_MEDIA_CATEGORY_NAME', 'wall');

define('BX_WALL_DIVIDER_ID', ',');
define('BX_WALL_DIVIDER_TIMELINE', '-');

class BxWallModule extends BxDolModule
{
    var $_iOwnerId;
    var $_aPostElements;

    var $_sDividerTemplate;
    var $_sBalloonTemplate;
    var $_sCmtPostTemplate;
    var $_sCmtViewTemplate;
    var $_sCmtTemplate;

    /**
     * Constructor
     */
    function BxWallModule($aModule)
    {
        parent::BxDolModule($aModule);

        $this->_oConfig->init($this->_oDb);
        $this->_oTemplate->init($this);

        $this->_iOwnerId = 0;

        //--- Define Membership Actions ---//
        defineMembershipActions(array('timeline repost', 'timeline post comment', 'timeline delete comment'), 'ACTION_ID_');
    }

    /**
     *
     * Admin Settings Methods
     *
     */
    function getSettingsForm($mixedResult)
    {
        $iId = (int)$this->_oDb->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name`='Timeline'");
        if(empty($iId))
           return MsgBox(_t('_wall_msg_no_results'));

        $oSettings = new BxDolAdminSettings($iId, BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'admin');
        $sResult = $oSettings->getForm();

        if($mixedResult !== true && !empty($mixedResult))
            $sResult = $mixedResult . $sResult;

        return $sResult;
    }
    function setSettings($aData)
    {
        $iId = (int)$this->_oDb->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name`='Timeline'");
        if(empty($iId))
           return MsgBox(_t('_wall_msg_no_results'));

        $oSettings = new BxDolAdminSettings($iId);
        return $oSettings->saveChanges($_POST);
    }

    /**
     * ACTION METHODS
     * Post somthing on the wall.
     *
     * @return string with JavaScript code.
     */
    function actionPost()
    {
    	$sJsObject = $this->_oConfig->getJsObject('post');

        $sResult = "parent." . $sJsObject . "._loading(null, false);\n";

        $this->_iOwnerId = (int)$_POST['WallOwnerId'];
        if (!$this->_isCommentPostAllowed(true))
            return "<script>" . $sResult . "alert('" . bx_js_string(_t('_wall_msg_not_allowed_post')) . "');</script>";

        $sPostType = process_db_input($_POST['WallPostType'], BX_TAGS_STRIP);
        $sContentType = process_db_input($_POST['WallContentType'], BX_TAGS_STRIP);

        $sMethod = "_process" . ucfirst($sPostType) . ucfirst($sContentType);
        if(method_exists($this, $sMethod)) {
            $aResult = $this->$sMethod();
            if((int)$aResult['code'] == 0) {
                $iId = $this->_oDb->insertEvent(array(
                   'owner_id' => $this->_iOwnerId,
                   'object_id' => $aResult['object_id'],
                   'type' => $this->_oConfig->getCommonPostPrefix() . $sPostType,
                   'action' => '',
                   'content' => process_db_input($aResult['content'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
                   'title' => process_db_input($aResult['title'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
                   'description' => process_db_input($aResult['description'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION)
                ));

                $this->onPost($iId);

                $sResult = "parent.$('form#WallPost" . ucfirst($sPostType) . "').find(':input:not(:button,:submit,[type = hidden],[type = radio],[type = checkbox])').val('');\n";
                $sResult .= "parent." . $sJsObject . "._getPost(null, " . $iId . ");";
            } else
                $sResult .= "alert('" . bx_js_string(_t($aResult['message'])) . "');";
        } else
           $sResult .= "alert('" . bx_js_string(_t('_wall_msg_failed_post')) . "');";

        return '<script>' . $sResult . '</script>';
    }

	function actionRepost()
    {
    	$iAuthorId = $this->_getAuthorId();

        $iOwnerId = process_db_input(bx_get('owner_id'), BX_DATA_INT);
        $aContent = array(
            'type' => process_db_input(bx_get('type'), BX_DATA_TEXT),
            'action' => process_db_input(bx_get('action'), BX_DATA_TEXT),
            'object_id' => process_db_input(bx_get('object_id'), BX_DATA_INT),
        );

        $aReposted = $this->_oDb->getReposted($aContent['type'], $aContent['action'], $aContent['object_id']);
        if(empty($aReposted) || !is_array($aReposted)) {
            $this->_echoResultJson(array('code' => 1, 'msg' => _t('_wall_txt_err_cannot_repost')));
            return;
        }

        $mixedAllowed = $this->_isRepostAllowed($aReposted, true);
        if($mixedAllowed !== true) {
            $this->_echoResultJson(array('code' => 2, 'msg' => strip_tags($mixedAllowed)));
            return;
        }

        $bReposted = $this->_oDb->isReposted($aReposted['id'], $iOwnerId, $iAuthorId);
		if($bReposted) {
        	$this->_echoResultJson(array('code' => 3, 'msg' => _t('_wall_txt_err_already_reposted')));
            return;
        }

        $iId = $this->_oDb->insertEvent(array(
            'owner_id' => $iOwnerId,
            'type' => $this->_oConfig->getPrefix('common_post') . 'repost',
            'action' => '',
            'object_id' => $iAuthorId,
            'content' => serialize($aContent),
            'title' => _t('_wall_reposted_title_' . bx_ltrim_str($aReposted['type'], $this->_oConfig->getPrefix('common_post'), '') . (!empty($aReposted['action']) ? '_' . $aReposted['action'] : ''), getNickName($iAuthorId)),
            'description' => ''
        ));

        if(empty($iId)) {
	        $this->_echoResultJson(array('code' => 4, 'msg' => _t('_wall_txt_err_cannot_repost')));        
	        return;
        }

        $this->onRepost($iId, $aReposted);

        $aReposted = $this->_oDb->getReposted($aContent['type'], $aContent['action'], $aContent['object_id']);
		$this->_echoResultJson(array(
			'code' => 0, 
			'msg' => _t('_wall_txt_msg_success_repost'), 
			'count' => $aReposted['reposts'], 
			'counter' => $this->_oTemplate->getRepostCounter($aReposted),
			'disabled' => !$bReposted
		));
    }

    /**
     * Delete post from the wall. Allow to wall owner only.
     *
     * @return string with JavaScript code.
     */
    function actionDelete()
    {
        $oJson = new Services_JSON();
        header('Content-Type:text/javascript');

        $this->_iOwnerId = (int)$_POST['WallOwnerId'];

        $iEvent = (int)$_POST['WallEventId'];
        $aEvent = $this->_oDb->getEvents(array('browse' => 'id', 'object_id' => $iEvent));

        if(!$this->_isCommentDeleteAllowed($aEvent, true))
            return $oJson->encode(array('code' => 1));

        $bResult = $this->_oDb->deleteEvent(array('id' => $iEvent));
        if($bResult) {
        	$this->onDelete($aEvent);

            return $oJson->encode(array('code' => 0, 'id' => $iEvent));
        } else
            return $oJson->encode(array('code' => 2));
    }
    /**
     * Get post content.
     *
     * @return string with post.
     */
    function actionGetPost()
    {
        $this->_oConfig->setJsMode(true);
        $this->_iOwnerId = (int)$_POST['WallOwnerId'];
        $iPostId = (int)$_POST['WallPostId'];

        $aEvent = $this->_oDb->getEvents(array('browse' => 'id', 'object_id' => $iPostId));

        header('Content-Type: text/html; charset=utf-8');
        return $this->_oTemplate->getCommon($aEvent);
    }
    /**
     * Get posts content.
     *
     * @return string with posts.
     */
    function actionGetPosts()
    {
        $this->_oConfig->setJsMode(true);

        $this->_iOwnerId = $_POST['WallOwnerId'];
        if(strpos($this->_iOwnerId, BX_WALL_DIVIDER_ID) !== false)
            $this->_iOwnerId = explode(BX_WALL_DIVIDER_ID, $this->_iOwnerId);

        $iStart = isset($_POST['WallStart']) && !empty($_POST['WallStart']) ? (int)$_POST['WallStart'] : 0;
        $iPerPage = isset($_POST['WallPerPage']) && !empty($_POST['WallPerPage']) ? (int)$_POST['WallPerPage'] : $this->_oConfig->getPerPage();
        $sFilter = isset($_POST['WallFilter']) && !empty($_POST['WallFilter']) ? process_db_input($_POST['WallFilter'], BX_TAGS_STRIP) : BX_WALL_FILTER_ALL;
        $sTimeline = isset($_POST['WallTimeline']) && !empty($_POST['WallTimeline']) ? process_db_input($_POST['WallTimeline'], BX_TAGS_STRIP) : '';
        $aModules = isset($_POST['WallModules']) && !empty($_POST['WallModules']) ? process_db_input($_POST['WallModules'], BX_TAGS_STRIP) : array();

        header('Content-Type: text/html; charset=utf-8');
        return $sContent = $this->_getPosts('desc', $iStart, $iPerPage, $sFilter, $sTimeline, $aModules);
    }
    /**
     * Get posts content (Outline).
     *
     * @return string with posts.
     */
    function actionGetPostsOutline()
    {
        $this->_oConfig->setJsMode(true);

        $iStart = isset($_POST['WallStart']) && !empty($_POST['WallStart']) ? (int)$_POST['WallStart'] : 0;
        $iPerPage = isset($_POST['WallPerPage']) && !empty($_POST['WallPerPage']) ? (int)$_POST['WallPerPage'] : $this->_oConfig->getPerPage();
        $sFilter = isset($_POST['WallFilter']) && !empty($_POST['WallFilter']) ? process_db_input($_POST['WallFilter'], BX_TAGS_STRIP) : BX_WALL_FILTER_ALL;
        $aModules = isset($_POST['WallModules']) && !empty($_POST['WallModules']) ? process_db_input($_POST['WallModules'], BX_TAGS_STRIP) : array();

        list($sContent, $sPaginate) = $this->_getPostsOutline('desc', $iStart, $iPerPage, $sFilter, $aModules);

        $oJson = new Services_JSON();
        header('Content-Type:text/javascript; charset=utf-8');
        return $oJson->encode(array(
            'code' => 0,
            'items' => $sContent,
            'paginate' => $sPaginate
        ));
    }
    /**
     * Get timeline content.
     *
     * @return string with paginate.
     */
    function actionGetTimeline()
    {
        $this->_iOwnerId = $_POST['WallOwnerId'];
        if(strpos($this->_iOwnerId, BX_WALL_DIVIDER_ID) !== false)
            $this->_iOwnerId = explode(BX_WALL_DIVIDER_ID, $this->_iOwnerId);

        $iStart = isset($_POST['WallStart']) && !empty($_POST['WallStart']) ? (int)$_POST['WallStart'] : 0;
        $iPerPage = isset($_POST['WallPerPage']) && !empty($_POST['WallPerPage']) ? (int)$_POST['WallPerPage'] : $this->_oConfig->getPerPage();
        $sFilter = isset($_POST['WallFilter']) && !empty($_POST['WallFilter']) ? process_db_input($_POST['WallFilter'], BX_TAGS_STRIP) : BX_WALL_FILTER_ALL;
        $sTimeline = isset($_POST['WallTimeline']) && !empty($_POST['WallTimeline']) ? process_db_input($_POST['WallTimeline'], BX_TAGS_STRIP) : '';
        $aModules = isset($_POST['WallModules']) && !empty($_POST['WallModules']) ? process_db_input($_POST['WallModules'], BX_TAGS_STRIP) : array();

        header('Content-Type: text/html; charset=utf-8');
        return $this->_getTimeline($iStart, $iPerPage, $sFilter, $sTimeline, $aModules);
    }
    /**
     * Get paginate content.
     *
     * @return string with paginate.
     */
    function actionGetPaginate()
    {
        $this->_iOwnerId = $_POST['WallOwnerId'];
        if(strpos($this->_iOwnerId, BX_WALL_DIVIDER_ID) !== false)
            $this->_iOwnerId = explode(BX_WALL_DIVIDER_ID, $this->_iOwnerId);

        $iStart = isset($_POST['WallStart']) && !empty($_POST['WallStart']) ? (int)$_POST['WallStart'] : 0;
        $iPerPage = isset($_POST['WallPerPage']) && !empty($_POST['WallPerPage']) ? (int)$_POST['WallPerPage'] : $this->_oConfig->getPerPage();
        $sFilter = isset($_POST['WallFilter']) && !empty($_POST['WallFilter']) ? process_db_input($_POST['WallFilter'], BX_TAGS_STRIP) : BX_WALL_FILTER_ALL;
        $sTimeline = isset($_POST['WallTimeline']) && !empty($_POST['WallTimeline']) ? process_db_input($_POST['WallTimeline'], BX_TAGS_STRIP) : '';
        $aModules = isset($_POST['WallModules']) && !empty($_POST['WallModules']) ? process_db_input($_POST['WallModules'], BX_TAGS_STRIP) : array();

        $oPaginate = $this->_getPaginate($sFilter, $sTimeline, $aModules);

        header('Content-Type: text/html; charset=utf-8');
        return $oPaginate->getPaginate($iStart, $iPerPage);
    }
    /**
     * Get photo uploading form.
     *
     * @return string with form.
     */
    function actionGetPhotoUploaders($iOwnerId)
    {
        $this->_iOwnerId = $iOwnerId;
        header('Content-Type: text/html; charset=utf-8');
        return BxDolService::call('photos', 'get_uploader_form', array(array('mode' => 'single', 'category' => 'wall', 'album'=>_t('_wall_photo_album', getNickName(getLoggedId())), 'albumPrivacy' => BX_DOL_PG_ALL, 'from_wall' => 1, 'owner_id' => $this->_iOwnerId)), 'Uploader');
    }
    /**
     * Get music uploading form.
     *
     * @return srting with form.
     */
    function actionGetMusicUploaders($iOwnerId)
    {
        $this->_iOwnerId = $iOwnerId;
        header('Content-Type: text/html; charset=utf-8');
        return BxDolService::call('sounds', 'get_uploader_form', array(array('mode' => 'single', 'category' => 'wall', 'album'=>_t('_wall_sound_album', getNickName(getLoggedId())), 'albumPrivacy' => BX_DOL_PG_ALL, 'from_wall' => 1, 'owner_id' => $this->_iOwnerId)), 'Uploader');
    }
    /**
     * Get video uploading form.
     *
     * @return string with form.
     */
    function actionGetVideoUploaders($iOwnerId)
    {
        $this->_iOwnerId = $iOwnerId;
        header('Content-Type: text/html; charset=utf-8');
        return BxDolService::call('videos', 'get_uploader_form', array(array('mode' => 'single', 'category' => 'wall', 'album'=>_t('_wall_video_album', getNickName(getLoggedId())), 'albumPrivacy' => BX_DOL_PG_ALL, 'from_wall' => 1, 'owner_id' => $this->_iOwnerId)), 'Uploader');
    }
    /**
     * Get popup with profiles.
     *
     * @return string with popup.
     */
	function actionGetRepostedBy()
    {
        $iRepostedId = (int)bx_get('id');

        header('Content-Type:text/javascript; charset=utf-8');
        return json_encode(array(
        	'code' => 0,
        	'content' => $this->_oTemplate->getRepostedBy($iRepostedId)
        ));
    }
    /**
     * Get RSS for specified owner.
     *
     * @param  string $sUsername wall owner username
     * @return string with RSS.
     */
    function actionRss($sUsername)
    {
        $aOwner = $this->_oDb->getUser($sUsername, 'username');

        $aEvents = $this->_oDb->getEvents(array(
            'browse' => 'owner',
            'owner_id' => $aOwner['id'],
            'order' => 'desc',
            'start' => 0,
            'count' => $this->_oConfig->getRssLength(),
            'filter' => ''
        ));

        $sRssBaseUrl = $this->_oConfig->getBaseUri() . 'index/' . $aOwner['username'] . '/';
        $aRssData = array();
        foreach($aEvents as $aEvent) {
            if(empty($aEvent['title'])) continue;

            $aRssData[$aEvent['id']] = array(
               'UnitID' => $aEvent['id'],
               'OwnerID' => $aOwner['id'],
               'UnitTitle' => $aEvent['title'],
               'UnitLink' => BX_DOL_URL_ROOT . $sRssBaseUrl . '#wall-event-' . $aEvent['id'],
               'UnitDesc' => $aEvent['description'],
               'UnitDateTimeUTS' => $aEvent['date'],
               'UnitIcon' => ''
            );
        }

        $oRss = new BxDolRssFactory();

        header('Content-Type: text/html; charset=utf-8');
        return $oRss->GenRssByData($aRssData, $aOwner['username'] . ' ' . _t('_wall_rss_caption'), $sRssBaseUrl);
    }

    /**
     * SERVICE METHODS
     * Process alert.
     *
     * @param BxDolAlerts $oAlert an instance with accured alert.
     */
    function serviceResponse($oAlert)
    {
        $oResponse = new BxWallResponse($this);
        $oResponse->response($oAlert);
    }

    /**
     * Display Post block on home page.
     *
     * @param  integer $mixed - owner ID or Username.
     * @return array   containing block info.
     */
    function servicePostBlockIndexTimeline($sType = 'id')
    {
    	if(!isLogged())
    		return '';

    	$sJsObject = $this->_oConfig->getJsObject('post');

        $aOwner = $this->_oDb->getUser(getLoggedId(), $sType);
        $this->_iOwnerId = $aOwner['id'];

        if(!$this->_isCommentPostAllowed())
            return "";

        $aTopMenu = array(
            'wall-ptype-text' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'comment-o', 'title' => _t('_wall_write'), 'active' => 1),
            'wall-ptype-link' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'link', 'title' => _t('_wall_share_link')),
        );

        if($this->_oDb->isModule('photos'))
            $aTopMenu['wall-ptype-photo'] = array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'picture-o', 'title' => _t('_wall_add_photo'));
        if($this->_oDb->isModule('sounds'))
            $aTopMenu['wall-ptype-music'] = array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'music', 'title' => _t('_wall_add_music'));
        if($this->_oDb->isModule('videos'))
            $aTopMenu['wall-ptype-video'] = array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'film', 'title' => _t('_wall_add_video'));

        //--- Parse template ---//
        $aVariables = array (
            'post_js_content' => $this->_oTemplate->getJsCode('post', array('iOwnerId' => 0)),
            'post_wall_text' => $this->_getWriteForm('_getWriteFormIndex'),
            'post_wall_link' => $this->_getShareLinkForm('_getShareLinkFormIndex'),
            'post_wall_photo' => '',
            'post_wall_video' => '',
            'post_wall_music' => '',
        );

        $this->_oTemplate->addCss('post.css');
        $this->_oTemplate->addJs(array('main.js', 'post.js'));
        return array($this->_oTemplate->parseHtmlByName('post.html', $aVariables), $aTopMenu, LoadingBox('bx-wall-post-loading'), true, 'getBlockCaptionMenu');
    }

	function serviceViewBlockIndexTimeline($iStart = -1, $iPerPage = -1, $sFilter = '', $sTimeline = '', $sType = 'id', $aModules = array())
    {
        $this->_iOwnerId = 0;

        if($iStart == -1)
           $iStart = 0;
        if($iPerPage == -1)
           $iPerPage = $this->_oConfig->getPerPage('index_tl');
        if(empty($sFilter))
            $sFilter = BX_WALL_FILTER_ALL;

        $aVariables = array(
            'timeline' => $this->_getTimeline($iStart, $iPerPage, $sFilter, $sTimeline, $aModules),
            'content' => $this->_getPosts('desc', $iStart, $iPerPage, $sFilter, $sTimeline, $aModules),
            'view_js_content' => $this->_oTemplate->getJsCode('view', array('iOwnerId' => $this->_iOwnerId), array(
				'WallOwnerId' => $this->_iOwnerId, 
				'WallStart' => $iStart, 
				'WallPerPage' => $iPerPage, 
				'WallFilter' => $sFilter, 
				'WallTimeline' => $sTimeline, 
				'WallModules' => $aModules
	        )),
        );

        bx_import('BxTemplFormView');
		$oForm = new BxTemplFormView(array());
		$oForm->addCssJs(true, true);

        $this->_oTemplate->addCss(array('view.css', 'view_phone.css'));
        $this->_oTemplate->addJs(array('modernizr.js', 'main.js', 'view.js'));
        return array($this->_oTemplate->parseHtmlByName('view.html', $aVariables), array(), LoadingBox('bx-wall-view-loading'), false, 'getBlockCaptionMenu');
    }

    /**
     * Display Post block on profile page.
     *
     * @param  integer $mixed - owner ID or Username.
     * @return array   containing block info.
     */
    function servicePostBlockProfileTimeline($mixed, $sType = 'id')
    {
    	$sJsObject = $this->_oConfig->getJsObject('post');

        $aOwner = $this->_oDb->getUser($mixed, $sType);
        $this->_iOwnerId = $aOwner['id'];

        if(($aOwner['id'] != $this->_getAuthorId() && !$this->_isCommentPostAllowed()) || !$this->_isViewAllowed())
            return "";

        $aTopMenu = array(
            'wall-ptype-text' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'comment-o', 'title' => _t('_wall_write'), 'active' => 1),
            'wall-ptype-link' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'link', 'title' => _t('_wall_share_link')),
        );

        if($this->_oDb->isModule('photos'))
            $aTopMenu['wall-ptype-photo'] = array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'picture-o', 'title' => _t('_wall_add_photo'));
        if($this->_oDb->isModule('sounds'))
            $aTopMenu['wall-ptype-music'] = array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'music', 'title' => _t('_wall_add_music'));
        if($this->_oDb->isModule('videos'))
            $aTopMenu['wall-ptype-video'] = array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changePostType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'film', 'title' => _t('_wall_add_video'));

        //--- Parse template ---//
        $aVariables = array (
            'post_js_content' => $this->_oTemplate->getJsCode('post', array('iOwnerId' => $this->_iOwnerId)),
            'post_wall_text' => $this->_getWriteForm(),
            'post_wall_link' => $this->_getShareLinkForm(),
            'post_wall_photo' => '',
            'post_wall_video' => '',
            'post_wall_music' => '',
        );

        $GLOBALS['oTopMenu']->setCurrentProfileID((int)$this->_iOwnerId);

        $this->_oTemplate->addCss('post.css');
        $this->_oTemplate->addJs(array('main.js', 'post.js'));
        return array($this->_oTemplate->parseHtmlByName('post.html', $aVariables), $aTopMenu, LoadingBox('bx-wall-post-loading'), true, 'getBlockCaptionMenu');
    }

    function serviceViewBlockProfileTimeline($mixed, $iStart = -1, $iPerPage = -1, $sFilter = '', $sTimeline = '', $sType = 'id', $aModules = array())
    {
        $sContent = '';
        $sJsObject = $this->_oConfig->getJsObject('view');

        $aOwner = $this->_oDb->getUser($mixed, $sType);
        $this->_iOwnerId = $aOwner['id'];

		if(!$this->_isViewAllowed())
    		return $sContent;

        $oSubscription = BxDolSubscription::getInstance();
        $aButton = $oSubscription->getButton($this->_getAuthorId(), 'bx_wall', '', $this->_iOwnerId);

        $aTopMenu = array(
            'wall-view-all' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changeFilter(this)', 'title' => _t('_wall_view_all'), 'active' => 1),
            'wall-view-owner' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changeFilter(this)', 'title' => _t('_wall_view_owner', getNickName($aOwner['id']))),
            'wall-view-other' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:' . $sJsObject . '.changeFilter(this)', 'title' => _t('_wall_view_other')),
            'wall-get-rss' => array('href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'rss/' . $aOwner['username'] . '/', 'target' => '_blank', 'title' => _t('_wall_get_rss')),
            'wall-subscription' => array('href' => 'javascript:void(0);', 'onclick' => 'javascript:' . $aButton['script'] . '', 'title' => $aButton['title']),
        );

        if($iStart == -1)
           $iStart = 0;
        if($iPerPage == -1)
           $iPerPage = $this->_oConfig->getPerPage('profile');
        if(empty($sFilter))
            $sFilter = BX_WALL_FILTER_ALL;

        $aVariables = array(
            'timeline' => $this->_getTimeline($iStart, $iPerPage, $sFilter, $sTimeline, $aModules),
            'content' => $this->_getPosts('desc', $iStart, $iPerPage, $sFilter, $sTimeline, $aModules),
            'view_js_content' => $this->_oTemplate->getJsCode('view', array('iOwnerId' => $this->_iOwnerId), array(
				'WallOwnerId' => $this->_iOwnerId, 
				'WallStart' => $iStart, 
				'WallPerPage' => $iPerPage, 
				'WallFilter' => $sFilter, 
				'WallTimeline' => $sTimeline, 
				'WallModules' => $aModules
	        ))
        );

        $GLOBALS['oTopMenu']->setCurrentProfileID((int)$this->_iOwnerId);

        bx_import('BxTemplFormView');
		$oForm = new BxTemplFormView(array());
		$oForm->addCssJs(true, true);

        $this->_oTemplate->addCss(array('view.css'));
        $this->_oTemplate->addJs(array('modernizr.js', 'main.js', 'view.js'));
        return array($oSubscription->getData() . $this->_oTemplate->parseHtmlByName('view.html', $aVariables), $aTopMenu, LoadingBox('bx-wall-view-loading'), false, 'getBlockCaptionMenu');
    }

    function serviceViewBlockAccountTimeline($mixed, $iStart = -1, $iPerPage = -1, $sFilter = '', $sTimeline = '', $sType = 'id', $aModules = array())
    {
        $aOwner = $this->_oDb->getUser($mixed, $sType);
        $this->_iOwnerId = $aOwner['id'];

        $aFriends = getMyFriendsEx($this->_iOwnerId, '', '', 'LIMIT 20');
        if(empty($aFriends))
            return $this->_oTemplate->getEmpty(true);

        $this->_iOwnerId = array_keys($aFriends);
        $sOwnerId = implode(BX_WALL_DIVIDER_ID, $this->_iOwnerId);

        if($iStart == -1)
           $iStart = 0;
        if($iPerPage == -1)
           $iPerPage = $this->_oConfig->getPerPage('account');
        if(empty($sFilter))
            $sFilter = BX_WALL_FILTER_ALL;

        $aVariables = array(
            'timeline' => $this->_getTimeline($iStart, $iPerPage, $sFilter, $sTimeline, $aModules),
            'content' => $this->_getPosts('desc', $iStart, $iPerPage, $sFilter, $sTimeline, $aModules),
            'view_js_content' => $this->_oTemplate->getJsCode('view', array('iOwnerId' => $sOwnerId), array(
				'WallOwnerId' => $sOwnerId, 
				'WallStart' => $iStart, 
				'WallPerPage' => $iPerPage, 
				'WallFilter' => $sFilter, 
				'WallTimeline' => $sTimeline, 
				'WallModules' => $aModules
	        ))
        );

        bx_import('BxTemplFormView');
		$oForm = new BxTemplFormView(array());
		$oForm->addCssJs(true, true);

        $this->_oTemplate->addCss(array('view.css'));
        $this->_oTemplate->addJs(array('main.js', 'view.js'));
        return array($this->_oTemplate->parseHtmlByName('view.html', $aVariables), array(), LoadingBox('bx-wall-view-loading'), false, 'getBlockCaptionMenu');
    }

    function serviceViewBlockIndexOutline($iStart = -1, $iPerPage = -1, $sFilter = '', $aModules = array())
    {
        $sContent = '';
        $this->_iOwnerId = 0;

        if($iStart == -1)
           $iStart = 0;
        if($iPerPage == -1)
           $iPerPage = $this->_oConfig->getPerPage('index_ol');
        if(empty($sFilter))
            $sFilter = BX_WALL_FILTER_ALL;

        list($sContent, $sPaginate) = $this->_getPostsOutline('desc', $iStart, $iPerPage, $sFilter, $aModules);
        if(empty($sContent))
            return;

        $aTmplVars = array(
            'outline_js_content' => $this->_oTemplate->getJsCode('outline', array('iOwnerId' => 0), array(
				'WallFilter' => $sFilter, 
				'WallModules' => $aModules
	        )),
            'content' => $sContent,
            'paginate' => $sPaginate
        );

        $this->_oTemplate->addCss(array('outline.css', 'outline_tablet.css', 'outline_phone.css'));
        $this->_oTemplate->addJs(array('masonry.pkgd.min.js', 'main.js', 'outline.js'));
        return array($this->_oTemplate->parseHtmlByName('outline.html', $aTmplVars), array(), LoadingBox('bx-wall-view-loading'), true, 'getBlockCaptionMenu');
    }

    function serviceGetActionsChecklist($sType)
    {
        bx_instance('BxDolModuleDb');
        $oModuleDb = new BxDolModuleDb();
        $aHandlers = $this->_oDb->getHandlers(array('type' => $sType));

        $aResults = array();
        foreach($aHandlers as $aHandler) {
            $aModule = $oModuleDb->getModuleByUri($aHandler['module_uri']);
            if(empty($aModule))
                $aModule['title'] = _t('_wall_alert_module_' . $aHandler['alert_unit']);

            $aResults[$aHandler['id']] = $aModule['title'] . ' (' . _t('_wall_alert_action_' . $aHandler['alert_action']) . ')';
        }

        asort($aResults);
        return $aResults;
    }

    function serviceUpdateHandlers($sModuleUri = 'all', $bInstall = true)
    {
        $aModules = $sModuleUri == 'all' ? $this->_oDb->getModules() : array($this->_oDb->getModuleByUri($sModuleUri));

        foreach($aModules as $aModule) {
           if(!BxDolRequest::serviceExists($aModule, 'get_wall_data')) continue;

           $aData = BxDolService::call($aModule['uri'], 'get_wall_data');
           if($bInstall)
               $this->_oDb->insertData($aData);
           else
               $this->_oDb->deleteData($aData);
        }

        BxDolAlerts::cache();
    }

    function serviceGetMemberMenuItem()
    {
        $oMemberMenu = bx_instance('BxDolMemberMenu');

        $aLanguageKeys = array(
            'wall' => _t( '_wall_pc_view' ),
        );

        // fill all necessary data;
        $aLinkInfo = array(
            'item_img_src'  => 'time',
            'item_img_alt'  => $aLanguageKeys['wall'],
            'item_link'     => BX_DOL_URL_ROOT . $this -> _oConfig -> getBaseUri(),
            'item_onclick'  => null,
            'item_title'    => $aLanguageKeys['wall'],
            'extra_info'    => null,
        );

        return $oMemberMenu -> getGetExtraMenuLink($aLinkInfo);
    }
    function serviceGetSubscriptionParams($sUnit, $sAction, $iObjectId)
    {
        $sUnit = str_replace('bx_', '_', $sUnit);
        if(empty($sAction))
            $sAction = 'main';

        $aProfileInfo = getProfileInfo($iObjectId);
        return array(
            'template' => array(
                'Subscription' => _t($sUnit . '_sbs_' . $sAction, $aProfileInfo['NickName']),
                'ViewLink' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri()  . 'index/' . $aProfileInfo['NickName']
            )
        );
    }

    function serviceGetSpyData()
    {
        $AlertName = $this->_oConfig->getAlertSystemName();

        return array(
            'handlers' => array(
                array('alert_unit' => $AlertName, 'alert_action' => 'post', 'module_uri' => $this->_oConfig->getUri(), 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => $AlertName, 'alert_action' => 'commentPost', 'module_uri' => $this->_oConfig->getUri(), 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
            ),
            'alerts' => array(
                array('unit' => $AlertName, 'action' => 'post'),
                array('unit' => $AlertName, 'action' => 'delete'),
                array('unit' => $AlertName, 'action' => 'commentPost'),
                array('unit' => $AlertName, 'action' => 'commentRemoved')
            )
        );
    }

    function serviceGetSpyPost($sAction, $iObjectId = 0, $iSenderId = 0, $aExtraParams = array())
    {
        $aEvent = $this->_oDb->getEvents(array('browse' => 'id', 'object_id' => $iObjectId));
        if($aEvent['owner_id'] == $iSenderId)
        	return array();

        $sLangKey = '';
        switch ($sAction) {
            case 'post':
                $sLangKey = '_wall_spy_post';
                break;
            case 'commentPost':
                $sLangKey = '_wall_spy_post_comment';
                break;
        }

        return array(
            'params'    => array(
                'profile_link'  => getProfileLink($iSenderId),
                'profile_nick'  => getNickName($iSenderId),
                'recipient_p_link' => getProfileLink($aEvent['owner_id']),
                'recipient_p_nick' => getNickName($aEvent['owner_id']),
            ),
            'recipient_id' => $aEvent['owner_id'],
            'lang_key' => $sLangKey
        );
    }

	function serviceGetRepostElementBlock($iOwnerId, $sType, $sAction, $iObjectId, $aParams = array())
    {
        $aParams = array_merge($this->_oConfig->getRepostDefaults(), $aParams);
        return $this->_oTemplate->getRepostElement($iOwnerId, $sType, $sAction, $iObjectId, $aParams);
    }

    function serviceGetRepostCounter($sType, $sAction, $iObjectId)
    {
		$aReposted = $this->_oDb->getRepost($sType, $sAction, $iObjectId);

        return $this->_oTemplate->getRepostCounter($aReposted);
    }

    function serviceGetRepostJsScript()
    {
        return $this->_oTemplate->getRepostJsScript();
    }

    function serviceGetRepostJsClick($iOwnerId, $sType, $sAction, $iObjectId)
    {
        return $this->_oTemplate->getRepostJsClick($iOwnerId, $sType, $sAction, $iObjectId);
    }

    function onPost($iId)
    {
    	$iUserId = $this->_getAuthorId();

		//--- Event -> Post for Alerts Engine ---//
		bx_import('BxDolAlerts');
		$oAlert = new BxDolAlerts($this->_oConfig->getAlertSystemName(), 'post', $iId, $iUserId);
		$oAlert->alert();
		//--- Event -> Post for Alerts Engine ---//
    }

    function onDelete($aEvent)
    {
    	$iUserId = $this->_getAuthorId();
    	$sCommonPostPrefix = $this->_oConfig->getPrefix('common_post');

       	//--- Update parent event when repost event was deleted.
        if($aEvent['type'] == $sCommonPostPrefix . BX_WALL_PARSE_TYPE_REPOST) {
            $this->_oDb->deleteRepostTrack($aEvent['id']);

            $aContent = unserialize($aEvent['content']);
            $aReposted = $this->_oDb->getReposted($aContent['type'], $aContent['action'], $aContent['object_id']);
            if(!empty($aReposted) && is_array($aReposted))
                $this->_oDb->updateRepostCounter($aReposted['id'], $aReposted['reposts'], -1);
        }

	    //--- Find and delete repost events when parent event was deleted.
        $bSystem = $this->_oConfig->isSystem($aEvent['type'], $aEvent['action']);
	    $aRepostEvents = $this->_oDb->getEvents(array('browse' => 'reposted_by_descriptor', 'type' => $aEvent['type']));
		foreach($aRepostEvents as $aRepostEvent) {
			$aContent = unserialize($aRepostEvent['content']);
			if(isset($aContent['type']) && $aContent['type'] == $aEvent['type'] && isset($aContent['object_id']) && (($bSystem && (int)$aContent['object_id'] == (int)$aEvent['object_id']) || (!$bSystem  && (int)$aContent['object_id'] == (int)$aEvent['id'])))
				$this->_oDb->deleteEvent(array('id' => (int)$aRepostEvent['id']));
		}

    	//--- Event -> Delete for Alerts Engine ---//
		bx_import('BxDolAlerts');
		$oAlert = new BxDolAlerts($this->_oConfig->getAlertSystemName(), 'delete', $iId, $iUserId);
		$oAlert->alert();
		//--- Event -> Delete for Alerts Engine ---//
    }

	function onRepost($iId, $aReposted = array())
    {
        $aEvent = $this->_oDb->getEvents(array('browse' => 'id', 'object_id' => $iId));

        if(empty($aReposted)) {
            $aContent = unserialize($aEvent['content']);

            $aReposted = $this->_oDb->getReposted($aContent['type'], $aContent['action'], $aContent['object_id']);
            if(empty($aReposted) || !is_array($aReposted))
                return;
        }

        $iUserId = $this->_getAuthorId();
        $this->_oDb->insertRepostTrack($aEvent['id'], $iUserId, $this->_getAuthorIp(), $aReposted['id']);
        $this->_oDb->updateRepostCounter($aReposted['id'], $aReposted['reposts']);

        //--- Wall -> Update for Alerts Engine ---//
        $oAlert = new BxDolAlerts($this->_oConfig->getAlertSystemName(), 'repost', $aReposted['id'], $iUserId);
        $oAlert->alert();
        //--- Wall -> Update for Alerts Engine ---//
    }

    /**
     * Private Methods
     * Is used for actions processing.
     */
    function _processTextUpload()
    {
    	$sJsObject = $this->_oConfig->getJsObject('view');

        $aOwner = $this->_oDb->getUser($this->_getAuthorId());

        $sContent = get_magic_quotes_gpc() ? stripslashes($_POST['content']) : $_POST['content'];
        $sContent = strip_tags($sContent);        
        $sContent = nl2br($sContent);

        if(empty($sContent))
            return array(
                'code' => 1,
                'message' => '_wall_msg_text_empty_message'
            );        

		$sContentMore = '';
		$iMaxLength = $this->_oConfig->getCharsDisplayMax();
		if(mb_strlen($sContent) > $iMaxLength) {
			$iLength = mb_strpos($sContent, ' ', $iMaxLength);

			$sContentMore = trim(mb_substr($sContent, $iLength));
			$sContent = trim(mb_substr($sContent, 0, $iLength));
		}
            
        return array(
            'code' => 0,
            'object_id' => $aOwner['id'],
            'content' => $this->_oTemplate->parseHtmlByName('common_text.html', array(
                'content' => $sContent,
        		'bx_if:show_more' => array(
					'condition' => !empty($sContentMore),
					'content' => array(
						'js_object' => $sJsObject,
						'post_content_more' => $sContentMore
					)
				),
            )),
            'title' => _t('_wall_added_title_text', $aOwner['username']),
            'description' => $sContent
        );
    }
    function _processLinkUpload()
    {
        $aOwner = $this->_oDb->getUser($this->_getAuthorId());

        $sUrl = trim(process_db_input($_POST['url'], BX_TAGS_STRIP));
        if(empty($sUrl))
            return array(
                'code' => 1,
                'message' => '_wall_msg_link_empty_link'
            );

        $a = getSiteInfo($sUrl);
        $sTitle = isset($a['title']) ? $a['title'] : $sUrl;
        $sDescription = isset($a['description']) ? $a['description'] : '';

        return array(
           'object_id' => $aOwner['id'],
           'content' => $this->_oTemplate->parseHtmlByName('common_link.html', array(
               'title' => $sTitle,
               'url' => strpos($sUrl, 'http://') === false && strpos($sUrl, 'https://') === false ? 'http://' . $sUrl : $sUrl,
               'description' => $sDescription
           )),
           'title' => _t('_wall_added_title_link', $aOwner['username']),
           'description' => $sUrl . ' - ' . $sTitle
        );
    }

    /**
     * Private Methods
     * Is used for content displaying
     */
    function _getTimeline($iStart, $iPerPage, $sFilter, $sTimeline, $aModules)
    {
        return $this->_oTemplate->getTimeline($iStart, $iPerPage, $sFilter, $sTimeline, $aModules);
    }
    function _getLoadMore($iStart, $iPerPage, $bEnabled = true, $bVisible = true)
    {
        return $this->_oTemplate->getLoadMore($iStart, $iPerPage, $bEnabled, $bVisible);
    }
    function _getLoadMoreOutline($iStart, $iPerPage, $bEnabled = true, $bVisible = true)
    {
        return $this->_oTemplate->getLoadMoreOutline($iStart, $iPerPage, $bEnabled, $bVisible);
    }
    function _getPaginate($sFilter, $sTimeline, $aModules)
    {
    	$sJsObject = $this->_oConfig->getJsObject('view');

        return new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'start' => 0,
            'count' => $this->_oDb->getEventsCount($this->_iOwnerId, $sFilter, $sTimeline, $aModules),
            'per_page' => $this->_oConfig->getPerPage(),
            'on_change_page' => $sJsObject . '.changePage({start}, {per_page})',
            'on_change_per_page' => $sJsObject . '.changePerPage(this)',
        ));
    }
    function _getPosts($sOrder, $iStart, $iPerPage, $sFilter, $sTimeline, $aModules)
    {
        $iDays = -1;
        $bPrevious = $bNext = false;

        $iStartEv = $iStart;
        $iPerPageEv = $iPerPage;

        //--- Check for Previous
        if($iStart - 1 >= 0) {
            $iStartEv -= 1;
            $iPerPageEv += 1;
            $bPrevious = true;
        }

        //--- Check for Next
        $iPerPageEv += 1;
        $aEvents = $this->_oDb->getEvents(array(
        	'browse' => 'owner', 
        	'owner_id' => $this->_iOwnerId, 
        	'order' => $sOrder, 
        	'start' => $iStartEv, 
        	'count' => $iPerPageEv, 
        	'filter' => $sFilter, 
        	'timeline' => $sTimeline,
        	'modules' => $aModules
        ));

        //--- Check for Previous
        if($bPrevious) {
            $aEvent = array_shift($aEvents);
            $iDays = (int)$aEvent['days'];
        }

        //--- Check for Next
        if(count($aEvents) > $iPerPage) {
            $aEvent = array_pop($aEvents);
            $bNext = true;
        }

        $iEvents = count($aEvents);
        $sContent = $this->_oTemplate->getEmpty($iEvents <= 0);
        if($iEvents <= 0)
        	$sContent .= $this->_oTemplate->getDividerToday();

        $bFirst = true;
        foreach($aEvents as $aEvent) {
            $aEvent['content'] = !empty($aEvent['action']) ? $this->_oTemplate->getSystem($aEvent) : $this->_oTemplate->getCommon($aEvent);
            if(empty($aEvent['content']))
                continue;

            if($bFirst) {
                $sContent .= $this->_oTemplate->getDividerToday($aEvent);
                $bFirst = false;
            }

            $sContent .= !empty($aEvent['content']) ? $this->_oTemplate->getDivider($iDays, $aEvent) : '';
            $sContent .= $aEvent['content'];
        }

        $sContent .= $this->_getLoadMore($iStart, $iPerPage, $bNext, $iEvents > 0);
        return $sContent;
    }
    function _getPostsOutline($sOrder, $iStart, $iPerPage, $sFilter, $aModules)
    {
        $iStartEv = $iStart;
        $iPerPageEv = $iPerPage;

        //--- Check for Next
        $iPerPageEv += 1;
        $aEvents = $this->_oDb->getEvents(array(
        	'browse' => BX_WALL_VIEW_OUTLINE, 
        	'order' => $sOrder, 
        	'start' => $iStartEv, 
        	'count' => $iPerPageEv, 
        	'filter' => $sFilter, 
        	'modules' => $aModules
        ));

        //--- Check for Next
        $bNext = false;
        if(count($aEvents) > $iPerPage) {
            $aEvent = array_pop($aEvents);
            $bNext = true;
        }

        $iEvents = count($aEvents);
        foreach($aEvents as $aEvent) {
            if(empty($aEvent['action']))
                continue;

            $aEvent['content'] = $this->_oTemplate->getSystem($aEvent, BX_WALL_VIEW_OUTLINE);
            if(empty($aEvent['content']))
                continue;

            $sContent .= $aEvent['content'];
        }

        $sPaginate = $this->_getLoadMoreOutline($iStart, $iPerPage, $bNext, $iEvents > 0);
        return array($sContent, $sPaginate);
    }
    function _getWriteForm($sGetFormArrayMethod = '_getWriteFormCommon')
    {
    	if(!method_exists($this, $sGetFormArrayMethod))
    		return '';

    	$aForm = $this->$sGetFormArrayMethod();
        $oForm = new BxTemplFormView($aForm);
        return $oForm->getCode();
    }
	function _getWriteFormCommon()
    {
    	$sJsObject = $this->_oConfig->getJsObject('post');

        $aForm = array(
            'form_attrs' => array(
                'name' => 'WallPostText',
                'action' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'post/',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'target' => 'WallPostIframe',
                'onsubmit' => 'javascript:return ' . $sJsObject . '.postSubmit(this);'
            ),
            'inputs' => array(
                'content' => array(
                    'type' => 'textarea',
                    'name' => 'content',
                    'caption' => '',
                    'colspan' => true
                ),
                'submit' => array(
                    'type' => 'submit',
                    'name' => 'submit',
                    'value' => _t('_wall_post'),
                    'colspan' => true
                )
            ),
        );
        $aForm['inputs'] = array_merge($aForm['inputs'], $this->_addHidden('text'));

        return $aForm;
    }
	function _getWriteFormIndex()
    {
        $aForm = $this->_getWriteFormCommon();
        $aForm['inputs']['WallOwnerId']['value'] = 0;

        return $aForm;
    }
    function _getShareLinkForm($sGetFormArrayMethod = '_getShareLinkFormCommon')
    {
    	if(!method_exists($this, $sGetFormArrayMethod))
    		return '';

    	$aForm = $this->$sGetFormArrayMethod();
        $oForm = new BxTemplFormView($aForm);
        return $oForm->getCode();
    }
    function _getShareLinkFormCommon()
    {
    	$sJsObject = $this->_oConfig->getJsObject('post');

        $aForm = array(
            'form_attrs' => array(
                'name' => 'WallPostLink',
                'action' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'post/',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'target' => 'WallPostIframe',
                'onsubmit' => 'javascript:return ' . $sJsObject . '.postSubmit(this);'
            ),
            'inputs' => array(
                'title' => array(
                    'type' => 'text',
                    'name' => 'url',
                    'caption' => _t('_wall_link_url'),
                ),
                'submit' => array(
                    'type' => 'submit',
                    'name' => 'submit',
                    'value' => _t('_wall_post'),
                    'colspan' => true
                )
            ),
        );
        $aForm['inputs'] = array_merge($aForm['inputs'], $this->_addHidden('link'));

        return $aForm;
    }
    function _getShareLinkFormIndex()
    {
    	$aForm = $this->_getShareLinkFormCommon();
        $aForm['inputs']['WallOwnerId']['value'] = 0;

        return $aForm;
    }
    function _getObjectPrivacy()
    {
    	return new BxWallPrivacy($this);
    }
	function _getObjectVoting($aEvent)
    {
    	if(in_array($aEvent['type'], array('profile', 'friend')) || $aEvent['action'] == 'commentPost')
    		return $this->_getObjectVotingDefault($aEvent['id']);

		$sType = $aEvent['type'];
	    $iObjectId = $aEvent['object_id'];
		if(strpos($iObjectId, ',') !== false) 
			return $this->_getObjectVotingDefault($aEvent['id']);

		$oVoting = new BxWallVoting($sType, $iObjectId);
		if($oVoting->isEnabled())
        	return $oVoting;

		return $this->_getObjectVotingDefault($aEvent['id']);
    }

	function _getObjectVotingDefault($iEventId)
    {
        return new BxWallVoting($this->_oConfig->getVotingSystemName(), $iEventId);
    }

    function _addHidden($sPostType = "photos", $sContentType = "upload", $sAction = "post")
    {
        return array(
            'WallOwnerId' => array (
                'type' => 'hidden',
                'name' => 'WallOwnerId',
                'value' => $this->_iOwnerId,
            ),
            'WallPostAction' => array (
                'type' => 'hidden',
                'name' => 'WallPostAction',
                'value' => $sAction,
            ),
            'WallPostType' => array (
                'type' => 'hidden',
                'name' => 'WallPostType',
                'value' => $sPostType,
            ),
            'WallContentType' => array (
                'type' => 'hidden',
                'name' => 'WallContentType',
                'value' => $sContentType,
            ),
        );
    }
	function _isViewAllowed()
    {
    	$oPrivacy = $this->_getObjectPrivacy();
        return $oPrivacy->check('view', $this->_iOwnerId, $this->_getAuthorId());
    }
    function _isCommentPostAllowed($bPerform = false)
    {
        if(isAdmin())
            return true;

        $iAuthorId = $this->_getAuthorId();
        if($iAuthorId == 0 && getParam('wall_enable_guest_comments') == 'on')
               return true;

           if(isBlocked($this->_iOwnerId, $iAuthorId))
            return false;

        $aCheckResult = checkAction($iAuthorId, ACTION_ID_TIMELINE_POST_COMMENT, $bPerform);
        return $aCheckResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }
    function _isCommentDeleteAllowed($aEvent, $bPerform = false)
    {
    	$sCommonPostPrefix = $this->_oConfig->getCommonPostPrefix();

        if(isAdmin())
            return true;

        $iUserId = (int)$this->_getAuthorId();
        if((int)$aEvent['owner_id'] == $iUserId || (strpos($aEvent['type'], $sCommonPostPrefix) === 0 && (int)$aEvent['object_id'] == $iUserId))
           return true;

        $aCheckResult = checkAction($iUserId, ACTION_ID_TIMELINE_DELETE_COMMENT, $bPerform);
        return $aCheckResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }
	function _isRepostAllowed($aEvent, $bPerform = false)
    {
    	$bSystem = $this->_oConfig->isSystem($aEvent['type'], $aEvent['action']);
    	if($bSystem && strcmp($aEvent['action'], 'commentPost') == 0)
    		return false;

        if(isAdmin())
            return true;

        $iUserId = (int)$this->_getAuthorId();
        if(($bSystem && (int)$aEvent['owner_id'] == $iUserId) || (!$bSystem && (int)$aEvent['object_id'] == $iUserId))
           return true;

        $aCheckResult = checkAction($iUserId, ACTION_ID_TIMELINE_REPOST, $bPerform);
        return $aCheckResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }
    function _getAuthorId()
    {
        return !isLogged() ? 0 : getLoggedId();
    }
    function _getAuthorPassword()
    {
        return !isLogged() ? '' : getLoggedPassword();
    }
    function _getAuthorIp()
    {
        return getVisitorIP();
    }
	function _echoResultJson($a, $isAutoWrapForFormFileSubmit = false)
    {
        header('Content-type: text/html; charset=utf-8');

        $s = json_encode($a);
        if ($isAutoWrapForFormFileSubmit && !empty($_FILES))
            $s = '<textarea>' . $s . '</textarea>';
        echo $s;
    }
}
