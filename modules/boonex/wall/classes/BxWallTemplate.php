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
    function BxWallTemplate(&$oConfig, &$oDb)
    {
        parent::BxDolModuleTemplate($oConfig, $oDb);

        $this->_aTemplates = array('divider', 'balloon', 'repost', 'common', 'common_media', 'comments', 'comments_actions');
    }

    function init(&$oModule)
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
	
	            if(!in_array($aEvent['type'], array('profile', 'friend')) && !in_array($aEvent['action'], array('commentPost', 'comment_add'))) {
	                $sType = $aEvent['type'];
	                $iObjectId = $aEvent['object_id'];
	                if(strpos($iObjectId, ',') !== false) {
	                    $sType = isset($aResult['grouped']['group_cmts_name']) ? $aResult['grouped']['group_cmts_name'] : '';
	                    $iObjectId = isset($aResult['grouped']['group_id']) ? (int)$aResult['grouped']['group_id'] : 0;
	                }
	
	                $oComments = new BxWallCmts($sType, $iObjectId);
	                if($oComments->isEnabled())
	                    $sComments = $oComments->getCommentsFirstSystem('comment', $aEvent['id']);
	                else
	                    $sComments = $this->getDefaultComments($aEvent['id']);
	            }
	            else
					$sComments = $this->getDefaultComments($aEvent['id']);

				$sResult = $this->parseHtmlByTemplateName('balloon', array(
		        	'post_type' => $aEvent['type'],
		            'post_id' => $aEvent['id'],
		            'post_owner_icon' => get_member_thumbnail($aEvent['owner_id'], 'none'),
		        	'post_content' => $aResult['content'],
		            'comments_content' => $sComments
		        ));
				break;

			case BX_WALL_VIEW_OUTLINE:
				$sResult = $this->parseHtmlByContent($aResult['content'], array(
		            'post_id' => $aEvent['id'],
		            'post_owner_icon' => get_member_icon($aEvent['owner_id'], 'none'),
		            'comments_content' => $sComments
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

		$aResult = $this->_getCommonData($aEvent);
		if(empty($aResult) || empty($aResult['content']))
			return '';

        $oComments = new BxWallCmts($this->_oConfig->getCommentSystemName(), $aEvent['id']);
        return $this->parseHtmlByTemplateName('balloon', array(
            'post_type' => bx_ltrim_str($aEvent['type'], $sPrefix, ''),
            'post_id' => $aEvent['id'],
            'post_owner_icon' => get_member_thumbnail((int)$aEvent['object_id'], 'none'),
            'post_content' => $aResult['content'],
        	'comments_content' => $oComments->getCommentsFirst('comment')
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
        	'content' => ''
        );
        switch($sEventType) {
        	case BX_WALL_PARSE_TYPE_TEXT:
        		$aResult['content'] = bx_linkify_html($aEvent['content'], 'class="' . BX_DOL_LINK_CLASS . '"');

        		$sTmplName = 'common';
        		$aTmplVars['cpt_added_new'] = _t('_wall_added_' . $sEventType);
        		break;

			case BX_WALL_PARSE_TYPE_LINK:
				$aResult['content'] = $aEvent['content'];

				$sTmplName = 'common';
        		$aTmplVars['cpt_added_new'] = _t('_wall_added_' . $sEventType);
				break;

        	case BX_WALL_PARSE_TYPE_PHOTOS:
        	case BX_WALL_PARSE_TYPE_SOUNDS:
        	case BX_WALL_PARSE_TYPE_VIDEOS:
        		$aContent = unserialize($aEvent['content']);
        		$aResult = array_merge($aResult, $this->_getCommonMedia($aContent['type'], (int)$aContent['id']));

				$sTmplName = 'common';
        		$aTmplVars['cpt_added_new'] = _t('_wall_added_' . $sEventType);
        		break;

            case BX_WALL_PARSE_TYPE_REPOST:
                if(empty($aEvent['content']))
                    return array();

                $aContent = unserialize($aEvent['content']);
                $aReposted = $this->_oDb->getReposted($aContent['type'], $aContent['action'], $aContent['object_id']);
                $sRepostedType = bx_ltrim_str($aReposted['type'], $sPrefix, '');

                $sMethod = $this->_oConfig->isSystem($aContent['type'] , $aContent['action']) ? '_getSystemData' : '_getCommonData';
				$aResult = array_merge($aResult, $this->$sMethod($aReposted));
				if(empty($aResult) || !is_array($aResult))
					return array();

				$sTmplName = 'repost';
				$aTmplVars['cpt_reposted'] = _t('_wall_reposted_' . $sRepostedType . (!empty($aReposted['action']) ? '_' . $aReposted['action'] : ''));
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
        	BX_WALL_PARSE_TYPE_SOUNDS => 'music', 
        	BX_WALL_PARSE_TYPE_VIDEOS => 'video'
        );

        $aMediaInfo = BxDolService::call($sType, 'get_' . $aConverter[$sType] . '_array', array($iObject, 'browse'), 'Search');

        $aContent = array('title' => '', 'description' => '', 'content' => '');
        if(!empty($aMediaInfo) && is_array($aMediaInfo) && !empty($aMediaInfo['file']))
            $aContent = array(
                'title' => _t('_wall_added_title_' . $sType, getNickName($aMediaInfo['owner'])),
                'description' => $aMediaInfo['description'],
                'content' => $this->parseHtmlByTemplateName('common_media', array(
                    'image_url' =>  isset($aMediaInfo['file']) ? $aMediaInfo['file'] : '',
                    'image_width' => isset($aMediaInfo['width']) ? (int)$aMediaInfo['width'] : 0,
                    'image_height' => isset($aMediaInfo['height']) ? (int)$aMediaInfo['height'] : 0,
                    'link' => isset($aMediaInfo['url']) ? $aMediaInfo['url'] : '',
                    'title' => isset($aMediaInfo['title']) ? bx_html_attribute($aMediaInfo['title']) : '',
                    'description' => isset($aMediaInfo['description']) ? $aMediaInfo['description'] : ''
                ))
            );

        return $aContent;
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

    function displayProfileEdit($aEvent)
    {
        $aOwner = $this->_oDb->getUser($aEvent['owner_id']);
        if(empty($aOwner))
            return array('perform_delete' => true);

        if($aOwner['status'] != 'Active')
            return "";

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
            return "";

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
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || empty($aContent['object_id']))
            return '';

		$iItem = (int)$aContent['object_id'];
        $aItem = getProfileInfo($iItem);
		if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        bx_import('BxDolCmtsProfile');
        $oCmts = new BxDolCmtsProfile('profile', $iItem);
        if(!$oCmts->isEnabled())
            return '';

		$aItem['url'] = getProfileLink($iId);
        $aComment = $oCmts->getCommentRow($iId);

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
            return '';

        bx_import('BxDolCmtsProfile');
        $oCmts = new BxDolCmtsProfile('profile', $iId);
        if(!$oCmts->isEnabled())
            return '';

        $aItem['url'] = getProfileLink($iId);
        $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);

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
            return "";

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

    function getDefaultComments($iEventId)
    {
        $oComments = new BxWallCmts($this->_oConfig->getCommentSystemName(), $iEventId);
        return $oComments->getCommentsFirst('comment');
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

        return $this->parseHtmlByName('repost_element_block.html', array(
            'style_prefix' => $sStylePrefix,
            'html_id' => $this->_oConfig->getHtmlIds('repost', 'main') . $aReposted['id'],
            'class' => ($bShowDoRepostAsButton ? $sStylePrefixRepost . 'button' : '') . ($bShowDoRepostAsButtonSmall ? $sStylePrefixRepost . 'button-small' : ''),
            'count' => $aReposted['reposts'],
            'do_repost' => $this->parseHtmlByName('repost_link.html', array(
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
                    'counter' => $this->getRepostCounter($aReposted)
                )
            ),
            'script' => $this->getRepostJsScript()
        ));
    }

    function getRepostCounter($aEvent)
    {
        $sStylePrefix = $this->_oConfig->getPrefix('style');
        $sJsObject = $this->_oConfig->getJsObject('repost');

        return $this->parseHtmlByName('repost_counter.html', array(
            'href' => 'javascript:void(0)',
            'title' => _t('_wall_txt_reposted_by'),
            'bx_repeat:attrs' => array(
                array('key' => 'id', 'value' => $this->_oConfig->getHtmlIds('repost', 'counter') . $aEvent['id']),
                array('key' => 'class', 'value' => $sStylePrefix . '-counter'),
                array('key' => 'onclick', 'value' => 'javascript:' . $sJsObject . '.toggleByPopup(this, ' . $aEvent['id'] . ')')
            ),
            'content' => !empty($aEvent['reposts']) && (int)$aEvent['reposts'] > 0 ? $aEvent['reposts'] : ''
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

    function getRepostJsClick($iOwnerId, $sType, $sAction, $iObjectId)
    {
        $sJsObject = $this->_oConfig->getJsObject('repost');
        $sFormat = "%s.repostItem(this, %d, '%s', '%s', %d);";

        $iOwnerId = !empty($iOwnerId) ? (int)$iOwnerId : $this->_oModule->_getAuthorId(); //--- in whose timeline the content will be reposted
        return sprintf($sFormat, $sJsObject, $iOwnerId, $sType, $sAction, (int)$iObjectId);
    }
}
