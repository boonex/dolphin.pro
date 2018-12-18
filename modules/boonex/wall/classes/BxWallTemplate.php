<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');

class BxWallTemplate extends BxDolModuleTemplate
{
    var $_oModule;

    /**
     * Constructor
     */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);

        $this->_aTemplates = array('divider', 'balloon', 'repost', 'common', 'common_media', 'comments', 'actions');
    }

    function setModule(&$oModule)
    {
        $this->_oModule = $oModule;
    }

    /**
     * Common public methods.
     * Is used to display events on the Wall.
     */
    function getSystem($aEvent, $sDisplayType = BX_WALL_VIEW_TIMELINE)
    {
        $sHandler = $aEvent['type'] . '_' . $aEvent['action'];
        if(!$this->_oConfig->isHandler($sHandler))
            return '';

		$aResult = $this->_getSystemData($aEvent, $sDisplayType);
        $bResult = !empty($aResult);

        if($bResult && isset($aResult['perform_delete']) && $aResult['perform_delete'] == true) {
            $this->_oDb->deleteEvent(array('id' => $aEvent['id']));
            return '';
        }
        else if(!$bResult || ($bResult && empty($aResult['content'])))
            return '';

        $sResult = "";
        switch($sDisplayType) {
			case BX_WALL_VIEW_TIMELINE:
	            if((empty($aEvent['title']) && !empty($aResult['title'])) || (empty($aEvent['description']) && !empty($aResult['description'])))
	                $this->_oDb->updateEvent(array(
	                    'title' => process_db_input($aResult['title'], BX_TAGS_STRIP),
	                    'description' => process_db_input($aResult['description'], BX_TAGS_STRIP)
	                ), $aEvent['id']);

				$sResult = $this->parseHtmlByTemplateName('balloon', array(
		        	'post_type' => $aEvent['type'],
		            'post_id' => $aEvent['id'],
		            'post_owner_icon' => $this->getOwnerThumbnail((int)$aEvent['owner_id']),
		        	'post_content' => $aResult['content'],
		            'comments_content' => $this->getComments($aEvent, $aResult)
		        ));
				break;

			case BX_WALL_VIEW_OUTLINE:
				//--- Votes
				$sVote = '';
				$oVote = $this->_oModule->_getObjectVoting($aEvent);
        		if($oVote->isEnabled() && $oVote->isVotingAllowed())
        			$sVote = $oVote->getVotingOutline();

				//--- Repost
				$sRepost = '';
				if($this->_oModule->_isRepostAllowed($aEvent)) {
					$iOwnerId = $this->_oModule->_getAuthorId(); //--- in whose timeline the content will be shared
		        	$iObjectId = $this->_oModule->_oConfig->isSystem($aEvent['type'], $aEvent['action']) ? $aEvent['object_id'] : $aEvent['id'];

					$sRepost = $this->_oModule->serviceGetRepostElementBlock($iOwnerId, $aEvent['type'], $aEvent['action'], $iObjectId, array(
						'show_do_repost_as_button_small' => true,
		        		'show_do_repost_icon' => true,
		        		'show_do_repost_label' => false
		        	));
				}

				$sResult = $this->parseHtmlByContent($aResult['content'], array(
		            'post_id' => $aEvent['id'],
		            'post_owner_icon' => $this->getOwnerIcon((int)$aEvent['owner_id']),
					'post_vote' => $sVote,
					'post_repost' => $sRepost
		        ));
				break;
        }

        return $sResult;
    }

    function getCommon($aEvent)
    {
    	$sPrefix = $this->_oConfig->getCommonPostPrefix();
        if(strpos($aEvent['type'], $sPrefix) !== 0)
            return '';

		$sEventType = bx_ltrim_str($aEvent['type'], $sPrefix, '');

		$aResult = $this->_getCommonData($aEvent);
    	if(isset($aResult['perform_delete']) && $aResult['perform_delete'] === true) {
            $this->_oDb->deleteEvent(array('id' => $aEvent['id']));
            return '';
        }

		if(empty($aResult) || empty($aResult['content']))
			return '';

		switch($sEventType) {
			case BX_WALL_PARSE_TYPE_PHOTOS:
        	case BX_WALL_PARSE_TYPE_SOUNDS:
        	case BX_WALL_PARSE_TYPE_VIDEOS:
        		$aContent = unserialize($aEvent['content']);

        		$oComments = new BxWallCmts($this->_oConfig->getCommonName($aContent['type']), $aContent['id']);
				if($oComments->isEnabled())
					$aResult['comments'] = $oComments->getCommentsFirstSystem('comment', $aEvent['id']);
				else
					$aResult['comments'] = $this->getDefaultComments($aEvent['id']);
        		break;

        	default:
				$aResult['comments'] = $this->getDefaultComments($aEvent['id']);
		}

        return $this->parseHtmlByTemplateName('balloon', array(
            'post_type' => bx_ltrim_str($aEvent['type'], $sPrefix, ''),
            'post_id' => $aEvent['id'],
            'post_owner_icon' => $this->getOwnerThumbnail((int)$aEvent['object_id']),
            'post_content' => $aResult['content'],
        	'comments_content' => $aResult['comments']
        ));
    }

    function _getSystemData(&$aEvent, $sDisplayType = BX_WALL_VIEW_TIMELINE)
    {
    	$sHandler = $aEvent['type'] . '_' . $aEvent['action'];
        if(!$this->_oConfig->isHandler($sHandler))
            return array();

        $aHandler = $this->_oConfig->getHandlers($sHandler);
        if(empty($aHandler['module_uri']) && empty($aHandler['module_class']) && empty($aHandler['module_method'])) {
            $sMethod = 'display' . bx_gen_method_name($aHandler['alert_unit'] . '_' . $aHandler['alert_action']);
            if(!method_exists($this, $sMethod))
                return array();

            $aResult = $this->$sMethod($aEvent, $sDisplayType);
        } 
        else {
            $aEvent['js_mode'] = $this->_oConfig->getJsMode();

            $aResult = $this->_oConfig->getSystemData($aEvent, $sDisplayType);
            if(isset($aResult['save']))
                $this->_oDb->updateEvent($aResult['save'], $aEvent['id']);
        }

        return $aResult;
    }

	function _getCommonData($aEvent)
    {
    	$sPrefix = $this->_oConfig->getPrefix('common_post');
    	$sEventType = bx_ltrim_str($aEvent['type'], $sPrefix, '');

        $sTmplName = '';
        $aTmplVars = array();

        $aResult = array(
        	'content' => '',
        	'comments' => '' 
        );
        switch($sEventType) {
        	case BX_WALL_PARSE_TYPE_TEXT:
        		$aResult['content'] = bx_linkify_html($aEvent['content'], 'class="' . BX_DOL_LINK_CLASS . '"');

        		$sTmplName = 'common';
        		$aTmplVars['cpt_added_new'] = _t('_wall_added_' . $sEventType);
        		break;

			case BX_WALL_PARSE_TYPE_LINK:
				$aResult['content'] = $this->parseHtmlByContent($aEvent['content'], array(
				    'bx_wall_get_image_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'get_image/' . $aEvent['id'] . '/'
				), array('{', '}'));

				$sTmplName = 'common';
        		$aTmplVars['cpt_added_new'] = _t('_wall_added_' . $sEventType);
				break;

        	case BX_WALL_PARSE_TYPE_PHOTOS:
        	case BX_WALL_PARSE_TYPE_SOUNDS:
        	case BX_WALL_PARSE_TYPE_VIDEOS:
        		$aContent = unserialize($aEvent['content']);
        		$iContent = (int)$aContent['id'];

				$aResultMedia = $this->_getCommonMedia($aContent['type'], $iContent);
				if(empty($aResultMedia) || !is_array($aResultMedia))
					return array('perform_delete' => true);

				$aResult = array_merge($aResult, $aResultMedia);

				$sTmplName = 'common';
        		$aTmplVars['cpt_added_new'] = _t('_wall_added_' . $sEventType);
        		break;

            case BX_WALL_PARSE_TYPE_REPOST:
                if(empty($aEvent['content']))
                    return array();

                $aContent = unserialize($aEvent['content']);
                $aReposted = $this->_oDb->getReposted($aContent['type'], $aContent['action'], $aContent['object_id']);

                $sMethod = $this->_oConfig->isSystem($aContent['type'] , $aContent['action']) ? '_getSystemData' : '_getCommonData';
				$aResult = array_merge($aResult, $this->$sMethod($aReposted));
				if(empty($aResult) || !is_array($aResult))
					return array();

				$sTmplName = 'repost';
				$aTmplVars['cpt_reposted'] = _t($this->_oModule->getRepostedLanguageKey($aReposted['type'], $aReposted['action'], $aReposted['object_id']));
                break;
        }

		$aResult['content'] = $this->parseHtmlByTemplateName($sTmplName, array_merge(array(
			'post_type' => $sEventType,
			'author_url' => getProfileLink($aEvent['object_id']),
            'author_username' => getNickName($aEvent['object_id']),
        	'bx_if:show_wall_owner' => array(
        		'condition' => (int)$aEvent['owner_id'] != 0 && (int)$aEvent['owner_id'] != (int)$aEvent['object_id'],
        		'content' => array(
        			'owner_url' => getProfileLink($aEvent['owner_id']),
        			'owner_username' => getNickName($aEvent['owner_id']),
        		)
        	),
        	'content' => $aResult['content'],
		), $aTmplVars));		

        return $aResult;
    }

	function _getCommonMedia($sType, $iObject)
    {
        $aConverter = array(
        	BX_WALL_PARSE_TYPE_PHOTOS => 'photo', 
        	BX_WALL_PARSE_TYPE_SOUNDS => 'sound', 
        	BX_WALL_PARSE_TYPE_VIDEOS => 'video'
        );

        $aMediaInfo = BxDolService::call($sType, 'get_' . $aConverter[$sType] . '_array', array($iObject, 'browse'), 'Search');
		if(empty($aMediaInfo) || !is_array($aMediaInfo) || empty($aMediaInfo['file']))
        	return array();

		return array(
			'title' => _t('_wall_added_title_' . $sType, getNickName($aMediaInfo['owner'])),
			'description' => $aMediaInfo['description'],
			'content' => $this->parseHtmlByTemplateName('common_media', array(
				'image_url' =>  isset($aMediaInfo['file']) ? $aMediaInfo['file'] : '',
				'image_width' => isset($aMediaInfo['width']) ? (int)$aMediaInfo['width'] : 0,
				'image_height' => isset($aMediaInfo['height']) ? (int)$aMediaInfo['height'] : 0,
				'link' => isset($aMediaInfo['url']) ? $aMediaInfo['url'] : '',
				'title' => isset($aMediaInfo['title']) ? bx_html_attribute($aMediaInfo['title']) : '',
				'description' => isset($aMediaInfo['description']) ? $aMediaInfo['description'] : '',
				'bx_if:show_duration' => array(
					'condition' => !empty($aMediaInfo['duration_f']),
					'content' => array(
				    	'duration_f' => $aMediaInfo['duration_f']
					)
				)
			))
		);
    }

    function getEmpty($bVisible)
    {
        return $this->parseHtmlByName('empty.html', array(
            'visible' => $bVisible ? 'block' : 'none',
            'content' => MsgBox(_t('_wall_msg_no_results'))
        ));
    }
    function getDivider(&$iDays, &$aEvent)
    {
        if($iDays == $aEvent['days'])
            return "";

        $iDaysAgo = (int)$aEvent['ago_days'];
        if($aEvent['today'] == $aEvent['days'] || (($aEvent['today'] - $aEvent['days']) == 1 && $iDaysAgo == 0)) {
            $iDays = $aEvent['days'];
            return "";
        }

        $sDaysAgo = "";
        if($iDaysAgo == 1)
            $sDaysAgo = _t('_wall_1_days_ago');
        else if($iDaysAgo > 1 && $iDaysAgo < 31)
            $sDaysAgo = _t('_wall_n_days_ago', $aEvent['ago_days']);
        else
            $sDaysAgo = $aEvent['print_date'];

        $sResult = $this->parseHtmlByTemplateName('divider', array(
            'cpt_class' => 'wall-divider',
            'content' => $sDaysAgo
        ));

        $iDays = $aEvent['days'];
        return $sResult;
    }
    function getDividerToday($aEvent = array())
    {
    	$bToday = !empty($aEvent) && ($aEvent['today'] == $aEvent['days'] || (($aEvent['today'] - $aEvent['days']) == 1 && (int)$aEvent['ago_days'] == 0));
        return $this->parseHtmlByTemplateName('divider', array(
            'cpt_class' => 'wall-divider-today ' . ($bToday ? 'visible' : 'hidden'),
            'content' => _t('_wall_today')
        ));
    }
    function getTimeline($iStart, $iPerPage, $sFilter, $sTimeline, $aModules)
    {
        $aEvent = $this->_oDb->getEvents(array('browse' => 'last', 'owner_id' => $this->_oModule->_iOwnerId, 'filter' => $sFilter, 'modules' => $aModules));
        if(empty($aEvents) || !is_array($aEvents))
            return "";

        $iMaxDuration = (int)$aEvent['ago_days'] + 1;
        if(empty($sTimeline))
            $sTimeline = '0' . BX_WALL_DIVIDER_TIMELINE . $iMaxDuration;

        $aInput = array(
            'type' => 'doublerange',
            'name' => 'timeline',
            'value' => $sTimeline,
            'attrs' => array(
                'min' => 0,
                'max' => $iMaxDuration,
                'onchange' => $this->_oConfig->getJsObject('view') . ".changeTimeline(e)"
            )
        );

        bx_import('BxTemplFormView');
        $oForm = new BxTemplFormView(array());
        $sContent = $oForm->genInput($aInput);
        $sContent = $oForm->genWrapperInput($aInput, $sContent);
        return $this->parseHtmlByName('timeline.html', array('content' => $sContent));
    }
    function getLoadMore($iStart, $iPerPage, $bEnabled = true, $bVisible = true)
    {
        $aTmplVars = array(
            'visible' => $bVisible ? 'block' : 'none',
            'bx_if:is_disabled' => array(
                'condition' => !$bEnabled,
                'content' => array()
            ),
            'bx_if:show_on_click' => array(
                'condition' => $bEnabled,
                'content' => array(
                    'on_click' => $this->_oConfig->getJsObject('view') . '.changePage(' . ($iStart + $iPerPage) . ', ' . $iPerPage . ')'
                )
            )
        );
        return $this->parseHtmlByName('load_more.html', $aTmplVars);
    }
    function getLoadMoreOutline($iStart, $iPerPage, $bEnabled = true, $bVisible = true)
    {
        $aTmplVars = array(
            'visible' => $bVisible ? 'block' : 'none',
            'bx_if:is_disabled' => array(
                'condition' => !$bEnabled,
                'content' => array()
            ),
            'bx_if:show_on_click' => array(
                'condition' => $bEnabled,
                'content' => array(
                    'on_click' => $this->_oConfig->getJsObject('outline') . '.changePage(' . ($iStart + $iPerPage) . ', ' . $iPerPage . ')'
                )
            )
        );
        return $this->parseHtmlByName('load_more.html', $aTmplVars);
    }

    function getUploader($iOwnerId, $sType, $sSubType = '')
    {
    	$sModule = $sType . 's';

    	$aUploaders = BxDolService::call($sModule, 'get_uploaders_list', array(), 'Uploader');
    	$bUploaders = !empty($aUploaders) && is_array($aUploaders) && count($aUploaders) > 1;

    	$aTmplVarsItems = array();
    	if($bUploaders)
    		foreach($aUploaders as $sValue => $sCaption)
    			$aTmplVarsItems[] = array(
    				'value' => $sValue,
    				'caption' => _t($sCaption),
    				'bx_if:show_selected' => array(
    					'condition' => $sValue == $sSubType,
    					'content' => array()
    				)
    			);

    	return $this->parseHtmlByName('uploader.html', array(
    		'bx_if:show_selector' => array(
    			'condition' => $bUploaders,
    			'content' => array(
    				'js_object' => $this->_oConfig->getJsObject('post'),
    				'type' => $sType,
    				'bx_repeat:items' => $aTmplVarsItems
    			)
    		),
    		'uploader' => BxDolService::call($sModule, 'get_uploader_form', array(array(
    			'mode' => $sSubType, 
    			'category' => 'wall', 
    			'album'=>_t('_wall_' . $sType . '_album', getNickName(getLoggedId())), 
    			'from_wall' => 1, 
    			'owner_id' => $iOwnerId,
    			'txt' => array(
    				'select_files' => _t('_wall_select_file')
    			)
    		)), 'Uploader')
    	));
    }

    function displayProfileEdit($aEvent)
    {
        $aOwner = $this->_oDb->getUser($aEvent['owner_id']);
        if(empty($aOwner))
            return array('perform_delete' => true);

        if($aOwner['status'] != 'Active')
            return array();

        if($aOwner['couple'] == 0 && $aOwner['sex'] == 'male')
            $sTxtEditedProfile = _t('_wall_edited_his_profile');
        else if($aOwner['couple'] == 0 && $aOwner['sex'] == 'female')
            $sTxtEditedProfile = _t('_wall_edited_her_profile');
        else if($aOwner['couple'] > 0)
            $sTxtEditedProfile = _t('_wall_edited_their_profile');

        $sOwner = getNickName((int)$aEvent['owner_id']);
        return array(
            'title' => $sOwner . ' ' . $sTxtEditedProfile,
            'description' => '',
            'content' => $this->parseHtmlByName('p_edit.html', array(
                'cpt_user_name' => $sOwner,
                'cpt_edited_profile' => $sTxtEditedProfile,
                'cpt_info_url' => BX_DOL_URL_ROOT . 'profile_info.php?ID=' . $aOwner['id'],
                'post_id' => $aEvent['id']
            ))
        );
    }

    function displayProfileEditStatusMessage($aEvent)
    {
        $aOwner = $this->_oDb->getUser($aEvent['owner_id']);
        if(empty($aOwner))
            return array('perform_delete' => true);

        if($aOwner['status'] != 'Active')
            return array();

        if($aOwner['couple'] == 0 && $aOwner['sex'] == 'male')
            $sTxtEditedProfile = _t('_wall_edited_his_profile_status_message');
        else if($aOwner['couple'] == 0 && $aOwner['sex'] == 'female')
            $sTxtEditedProfile = _t('_wall_edited_her_profile_status_message');
        else if($aOwner['couple'] > 0)
            $sTxtEditedProfile = _t('_wall_edited_their_profile_status_message');

        $aParams = array();
        if(!empty($aEvent['content']))
            $aParams = unserialize($aEvent['content']);

        $sOwner = getNickName((int)$aEvent['owner_id']);
        $sMessage = isset($aParams[0]) ? stripslashes($aParams[0]) : '';
        return array(
            'title' => $sOwner . ' ' . $sTxtEditedProfile,
            'description' => $sMessage,
            'content' => $this->parseHtmlByName('p_edit_status_message.html', array(
                'cpt_user_name' => $sOwner,
                'cpt_edited_profile_status_message' => $sTxtEditedProfile,
                'cnt_status_message' => $sMessage,
                'post_id' => $aEvent['id']
            ))
        );
    }

	function displayProfileCommentAdd($aEvent)
    {
        $iComment = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || empty($aContent['object_id']))
            return array();

		$iItem = (int)$aContent['object_id'];
        $aItem = getProfileInfo($iItem);
		if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        bx_import('BxDolCmtsProfile');
        $oCmts = new BxDolCmtsProfile('profile', $iItem);
        if(!$oCmts->isEnabled())
            return array();

		$aItem['url'] = getProfileLink($iItem);
        $aComment = $oCmts->getCommentRow($iComment);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sTextWallObject = _t('_wall_object_profile');
        return array(
            'title' => _t('_wall_added_new_title_comment_profile', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content' => $this->parseHtmlByName('p_comment.html', array(
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t('_wall_added_new_comment_profile'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $aItem['url'],
	            'cnt_comment_text' => $aComment['cmt_text'],
	            'cnt_item_page' => $aItem['url'],
	            'cnt_item_icon' => get_member_thumbnail($iItem, 'none', true),
	            'cnt_item_title' => $aItem['title'],
	            'cnt_item_description' => $aItem['description'],
	            'post_id' => $aEvent['id'],
        	))
        );
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function displayProfileCommentPost($aEvent)
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aItem = getProfileInfo($iId);
		if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || !isset($aContent['comment_id']))
            return array();

        bx_import('BxDolCmtsProfile');
        $oCmts = new BxDolCmtsProfile('profile', $iId);
        if(!$oCmts->isEnabled())
            return array();

        $aItem['url'] = getProfileLink($iId);
        $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sTextWallObject = _t('_wall_object_profile');
        return array(
            'title' => _t('_wall_added_new_title_comment_profile', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content' => $this->parseHtmlByName('p_comment.html', array(
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t('_wall_added_new_comment_profile'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $aItem['url'],
	            'cnt_comment_text' => $aComment['cmt_text'],
	            'cnt_item_page' => $aItem['url'],
	            'cnt_item_icon' => get_member_thumbnail($iId, 'none', true),
	            'cnt_item_title' => $aItem['title'],
	            'cnt_item_description' => $aItem['description'],
	            'post_id' => $aEvent['id'],
        	))
        );
    }

    function displayFriendAccept($aEvent)
    {
        $aOwner = $this->_oDb->getUser($aEvent['owner_id']);
        $aFriend = $this->_oDb->getUser($aEvent['object_id']);
        if(empty($aOwner) || empty($aFriend))
            return array('perform_delete' => true);

        if($aOwner['status'] != 'Active' || $aFriend['status'] != 'Active')
            return array();

        $sOwner = getNickName((int)$aEvent['owner_id']);

        $iFriend = (int)$aFriend['id'];
        $sFriend = getNickName($iFriend);
        return array(
            'title' => $sOwner . ' ' . _t('_wall_friends_with') . ' ' . $aFriend['username'],
            'description' => '',
            'content' => $this->parseHtmlByName('f_accept.html', array(
                'cpt_user_name' => $sOwner,
                'cpt_friend_url' => getProfileLink($aFriend['id']),
                'cpt_friend_name' => $sFriend,
                'cnt_friend' => get_member_thumbnail($iFriend, 'none', true),
                'post_id' => $aEvent['id']
            ))
        );
    }

    function getOwnerThumbnail($iOwnerId)
    {
    	return $this->getOwnerImage('thumbnail', $iOwnerId);
    }

    function getOwnerIcon($iOwnerId)
    {
    	return $this->getOwnerImage('icon', $iOwnerId);
    }

    protected function getOwnerImage($sType, $iOwnerId)
    {
    	$sFunction = 'get_member_' . $sType;
    	if($iOwnerId != 0 && function_exists($sFunction))
    		return $sFunction($iOwnerId, 'none');

		$aType2Icon = array('icon' => 'small', 'thumbnail' => 'medium');
    	return $this->parseHtmlByName('owner_image.html', array(
    		'class' => 'thumbnail_block_' . $sType,
    		'src' => $GLOBALS['oFunctions']->getSexPic('', $aType2Icon[$sType])
    	));
    }

    function getComments($aEvent, $aResult) 
    {
        if(in_array($aEvent['type'], array('profile', 'friend'))) 
            return $this->getDefaultComments($aEvent['id']);

        $sType = $aEvent['type'];
        $iObjectId = $aEvent['object_id'];

        if($aEvent['action'] == 'comment_add') {
            $aContent = unserialize($aEvent['content']);
            $iObjectId = (int)$aContent['object_id'];
        }

        if($this->_oConfig->isGrouped($aEvent['type'], $aEvent['action'], $iObjectId)) {
            $sType = isset($aResult['grouped']['group_cmts_name']) ? $aResult['grouped']['group_cmts_name'] : '';
            $iObjectId = isset($aResult['grouped']['group_id']) ? (int)$aResult['grouped']['group_id'] : 0;
        }

        if($this->_oConfig->isGroupedObject($iObjectId))
            return $this->getDefaultComments($aEvent['id']);

        $oComments = new BxWallCmts($sType, $iObjectId);
        if($oComments->isEnabled())
            return $oComments->getCommentsFirstSystem('comment', $aEvent['id']);

        return $this->getDefaultComments($aEvent['id']);
    }

    function getDefaultComments($iEventId)
    {
        $oComments = new BxWallCmts($this->_oConfig->getCommentSystemName(), $iEventId);
        return $oComments->getCommentsFirstDefault('comment');
    }

    function getJsCode($sType, $aParams = array(), $aRequestParams = array())
    {
    	$sBaseUri = $this->_oConfig->getBaseUri();
    	$sJsClass = $this->_oConfig->getJsClass($sType);
    	$sJsObject = $this->_oConfig->getJsObject($sType);

    	$aParams = array_merge(array(
    		'sActionUri' => $sBaseUri,
    		'sActionUrl' => BX_DOL_URL_ROOT . $sBaseUri, 
    		'sObjName' => $sJsObject,
    		'iOwnerId' => 0,
    		'sAnimationEffect' => $this->_oConfig->getAnimationEffect(),
    		'iAnimationSpeed' => $this->_oConfig->getAnimationSpeed(),
    		'aHtmlIds' => $this->_oConfig->getHtmlIds($sType),
    		'oRequestParams' => $aRequestParams
    	), $aParams);

        return $this->_wrapInTagJsCode("var " . $sJsObject . " = new " . $sJsClass . "(" . json_encode($aParams) . ");");
    }

    /**
     * Repost functions.
     */
	function getRepostElement($iOwnerId, $sType, $sAction, $iObjectId, $aParams = array())
    {
        $aReposted = $this->_oDb->getReposted($sType, $sAction, $iObjectId);
        if(empty($aReposted) || !is_array($aReposted))
            return '';

		$bDisabled = $this->_oModule->_isRepostAllowed($aReposted) !== true || $this->_oDb->isReposted($aReposted['id'], $iOwnerId, $this->_oModule->_getAuthorId());
		if($bDisabled && (int)$aReposted['reposts'] == 0)
            return '';

		$sStylePrefix = $this->_oConfig->getPrefix('style');
        $sStylePrefixRepost = $sStylePrefix . '-repost-';

        $bShowDoRepostAsButtonSmall = isset($aParams['show_do_repost_as_button_small']) && $aParams['show_do_repost_as_button_small'] == true;
        $bShowDoRepostAsButton = !$bShowDoRepostAsButtonSmall && isset($aParams['show_do_repost_as_button']) && $aParams['show_do_repost_as_button'] == true;

        $bShowDoRepostIcon = isset($aParams['show_do_repost_icon']) && $aParams['show_do_repost_icon'] == true;
        $bShowDoRepostLabel = isset($aParams['show_do_repost_label']) && $aParams['show_do_repost_label'] == true;
        $bShowCounter = isset($aParams['show_counter']) && $aParams['show_counter'] === true;

        $sTmplMain = !empty($aParams['template_main']) ? $aParams['template_main'] : 'repost_element_block.html';
        $sTmplDoRepost = !empty($aParams['template_do_repost']) ? $aParams['template_do_repost'] : 'repost_link.html';

        //--- Do repost link ---//
		$sClass = $sStylePrefixRepost . 'do-repost';
		if($bShowDoRepostAsButton)
			$sClass .= ' bx-btn';
		else if($bShowDoRepostAsButtonSmall)
			$sClass .= ' bx-btn bx-btn-small';

		$sOnClick = '';
		if(!$bDisabled) {
			$sCommonPrefix = $this->_oConfig->getPrefix('common_post');
			if($sType == $sCommonPrefix . BX_WALL_PARSE_TYPE_REPOST) {
				$aRepostedContent = unserialize($aReposted['content']);

	            $sOnClick = $this->getRepostJsClick($iOwnerId, $aRepostedContent['type'], $aRepostedContent['action'], $aRepostedContent['object_id']);
			}
			else
				$sOnClick = $this->getRepostJsClick($iOwnerId, $sType, $sAction, $iObjectId);
		}
		else
			$sClass .= $bShowDoRepostAsButton || $bShowDoRepostAsButtonSmall ? ' bx-btn-disabled' : ' ' . $sStylePrefixRepost . 'disabled';

		$aOnClickAttrs = array();
		if(!empty($sClass))
			$aOnClickAttrs[] = array('key' => 'class', 'value' => $sClass);
		if(!empty($sOnClick))
			$aOnClickAttrs[] = array('key' => 'onclick', 'value' => $sOnClick);

        return $this->parseHtmlByName($sTmplMain, array(
            'style_prefix' => $sStylePrefix,
            'html_id' => $this->_oConfig->getHtmlIds('repost', 'main') . $aReposted['id'],
            'class' => ($bShowDoRepostAsButton ? $sStylePrefixRepost . 'button' : '') . ($bShowDoRepostAsButtonSmall ? $sStylePrefixRepost . 'button-small' : ''),
            'count' => $aReposted['reposts'],
            'do_repost' => $this->parseHtmlByName($sTmplDoRepost, array(
	            'href' => 'javascript:void(0)',
	            'title' => _t('_wall_txt_do_repost'),
	            'bx_repeat:attrs' => $aOnClickAttrs,
        		'bx_if:show_icon' => array(
        			'condition' => $bShowDoRepostIcon,
        			'content' => array()
        		),
	            'bx_if:show_text' => array(
        			'condition' => $bShowDoRepostLabel,
        			'content' => array(
        				'content' => _t('_wall_txt_do_repost')
        			)
        		)
	        )),
            'bx_if:show_counter' => array(
                'condition' => $bShowCounter,
                'content' => array(
                    'style_prefix' => $sStylePrefix,
        			'bx_if:show_hidden' => array(
        				'condition' => (int)$aReposted['reposts'] == 0,
        				'content' => array()
        			),
                    'counter' => $this->getRepostCounter($aReposted, $aParams)
                )
            ),
            'script' => $this->getRepostJsScript()
        ));
    }

    function getRepostCounter($aEvent, $aParams = array())
    {
        $sStylePrefix = $this->_oConfig->getPrefix('style');
        $sJsObject = $this->_oConfig->getJsObject('repost');

        $sTmplCounter = !empty($aParams['template_counter']) ? $aParams['template_counter'] : 'repost_counter.html';

        $sTxtCounter = !empty($aParams['text_counter']) ? $aParams['text_counter'] : '_wall_n_reposts';
        $sTxtCounterEmpty = !empty($aParams['text_counter_empty']) ? $aParams['text_counter_empty'] : '_wall_no_reposts';

        return $this->parseHtmlByName($sTmplCounter, array(
            'href' => 'javascript:void(0)',
            'title' => bx_html_attribute(_t('_wall_txt_reposted_by')),
            'bx_repeat:attrs' => array(
                array('key' => 'id', 'value' => $this->_oConfig->getHtmlIds('repost', 'counter') . $aEvent['id']),
                array('key' => 'class', 'value' => $sStylePrefix . '-repost-counter'),
                array('key' => 'onclick', 'value' => 'javascript:' . $sJsObject . '.toggleByPopup(this, ' . $aEvent['id'] . ')')
            ),
            'content' => !empty($aEvent['reposts']) && (int)$aEvent['reposts'] > 0 ? _t($sTxtCounter, $aEvent['reposts']) : _t($sTxtCounterEmpty, $aEvent['reposts'])
        ));
    }

    function getRepostedBy($iId)
    {
        $sStylePrefix = $this->_oConfig->getPrefix('style');

        $aUserIds = $this->_oDb->getRepostedBy($iId);

        $aTmplUsers = array();
        foreach($aUserIds as $iUserId)
            $aTmplUsers[] = array(
                'style_prefix' => $sStylePrefix,
                'thumbnail' => get_member_thumbnail($iUserId, 'none', true)
            );

        if(empty($aTmplUsers))
            $aTmplUsers = MsgBox(_t('_Empty'));

		$sName = $this->_oConfig->getHtmlIds('repost', 'by_popup') . $iId;
		$sContent = $this->parseHtmlByName('repost_by_list.html', array(
            'style_prefix' => $sStylePrefix,
            'bx_repeat:list' => $aTmplUsers
        ));

        return PopupBox($sName, _t('_wall_txt_reposted_by'), $sContent);
    }

    function getRepostJsScript()
    {
        $this->addCss(array('repost.css'));
        $this->addJs(array('main.js', 'repost.js'));

        return $this->getJsCode('repost', array(
        	'iOwnerId' => $oModule->_iOwnerId
        ));
    }

    function getRepostJsClick($iOwnerId, $sType, $sAction, $mixedObjectId)
    {
        $sJsObject = $this->_oConfig->getJsObject('repost');
        $sFormat = "%s.repostItem(this, %d, '%s', '%s', " . (is_int($mixedObjectId) ? "%d" : "'%s'") . ");";

        $iOwnerId = !empty($iOwnerId) ? (int)$iOwnerId : $this->_oModule->_getAuthorId(); //--- in whose timeline the content will be reposted
        return sprintf($sFormat, $sJsObject, $iOwnerId, $sType, $sAction, $mixedObjectId);
    }
}
