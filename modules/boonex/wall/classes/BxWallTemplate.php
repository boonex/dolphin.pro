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

        $this->_aTemplates = array('divider', 'balloon', 'comments', 'comments_actions', 'common_media');
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
        $sResult = "";

        $sHandler = $aEvent['type'] . '_' . $aEvent['action'];
        if(!$this->_oConfig->isHandler($sHandler))
            return '';

        $aHandler = $this->_oConfig->getHandlers($sHandler);
        if(empty($aHandler['module_uri']) && empty($aHandler['module_class']) && empty($aHandler['module_method'])) {
            $sMethod = 'display' . str_replace(' ', '', ucwords(str_replace('_', ' ', $aHandler['alert_unit'] . '_' . $aHandler['alert_action'])));
            if(!method_exists($this, $sMethod))
                return '';

            $aResult = $this->$sMethod($aEvent, $sDisplayType);
        } else {
            $aEvent['js_mode'] = $this->_oConfig->getJsMode();

            $sMethod = $aHandler['module_method'] .  ($sDisplayType == BX_WALL_VIEW_OUTLINE ? '_' . BX_WALL_VIEW_OUTLINE : '');
            $aResult = BxDolService::call($aHandler['module_uri'], $sMethod, array($aEvent), $aHandler['module_class']);

            if(isset($aResult['save']))
                $this->_oDb->updateEvent($aResult['save'], $aEvent['id']);
        }

        $bResult = !empty($aResult);
        if($bResult && isset($aResult['perform_delete']) && $aResult['perform_delete'] == true) {
            $this->_oDb->deleteEvent(array('id' => $aEvent['id']));
            return '';
        } else if(!$bResult || ($bResult && empty($aResult['content'])))
            return '';

        $sIcon = $sComments = "";
        switch($sDisplayType) {
			case BX_WALL_VIEW_TIMELINE:
	        	$sIcon = get_member_thumbnail($aEvent['owner_id'], 'none');

	            if((empty($aEvent['title']) && !empty($aResult['title'])) || (empty($aEvent['description']) && !empty($aResult['description'])))
	                $this->_oDb->updateEvent(array(
	                    'title' => process_db_input($aResult['title'], BX_TAGS_STRIP),
	                    'description' => process_db_input($aResult['description'], BX_TAGS_STRIP)
	                ), $aEvent['id']);
	
	            if(!in_array($aEvent['type'], array('profile', 'friend')) && $aEvent['action'] != 'commentPost') {
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
	            } else
	                $sComments = $this->getDefaultComments($aEvent['id']);

				break;

			case BX_WALL_VIEW_OUTLINE:
				$sIcon = get_member_icon($aEvent['owner_id'], 'none');
				break;
        }

        return $this->parseHtmlByContent($aResult['content'], array(
            'post_id' => $aEvent['id'],
            'post_owner_icon' => $sIcon,
            'comments_content' => $sComments
        ));
    }

    function getCommon($aEvent)
    {
        $sPrefix = $this->_oConfig->getCommonPostPrefix();
        if(strpos($aEvent['type'], $sPrefix) !== 0)
            return '';

        if(in_array($aEvent['type'], array($sPrefix . 'photos', $sPrefix . 'sounds', $sPrefix . 'videos'))) {
            $aContent = unserialize($aEvent['content']);
            $aEvent = array_merge($aEvent, $this->getCommonMedia($aContent['type'], (int)$aContent['id']));
            if(empty($aEvent['content']) || (int)$aEvent['content'] > 0)
                return '';
        }
        else if(in_array($aEvent['type'], array($sPrefix . 'text'))) {
        	$aEvent['content'] = bx_linkify_html($aEvent['content'], 'class="' . BX_DOL_LINK_CLASS . '"');
        }

        $aAuthor = $this->_oDb->getUser($aEvent['object_id']);
        $aVariables = array (
            'author_url' => getProfileLink($aAuthor['id']),
            'author_username' => getNickName($aAuthor['id']),
        	'bx_if:show_wall_owner' => array(
        		'condition' => (int)$aEvent['owner_id'] != 0 && (int)$aEvent['owner_id'] != (int)$aEvent['object_id'],
        		'content' => array(
        			'owner_url' => getProfileLink($aEvent['owner_id']),
        			'owner_username' => getNickName($aEvent['owner_id']),
        		)
        	),
            'post_id' => $aEvent['id'],
            'post_owner_icon' => get_member_thumbnail($aAuthor['id'], 'none'),
            'post_content' => $aEvent['content'],
        );

        $oComments = new BxWallCmts($this->_oConfig->getCommentSystemName(), $aEvent['id']);
        $aVariables = array_merge($aVariables, array('comments_content' => $oComments->getCommentsFirst('comment')));

        return $this->parseHtmlByTemplateName('balloon', $aVariables);
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
        $aEvents = $this->_oDb->getEvents(array('type' => 'last', 'owner_id' => $this->_oModule->_iOwnerId, 'filter' => $sFilter, 'modules' => $aModules));
        if(empty($aEvents) || !is_array($aEvents))
            return "";

        $iMaxDuration = (int)$aEvents[0]['ago_days'] + 1;
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

        $sTextAddedNew = _t('_wall_added_new_comment_profile');
        $sTextWallObject = _t('_wall_object_profile');
        $aTmplVars = array(
            'cpt_user_name' => $sOwner,
            'cpt_added_new' => $sTextAddedNew,
            'cpt_object' => $sTextWallObject,
            'cpt_item_url' => $aItem['url'],
            'cnt_comment_text' => $aComment['cmt_text'],
            'cnt_item_page' => $aItem['url'],
            'cnt_item_icon' => get_member_thumbnail($iId, 'none', true),
            'cnt_item_title' => $aItem['title'],
            'cnt_item_description' => $aItem['description'],
            'post_id' => $aEvent['id'],
        );
        return array(
            'title' => $sOwner . ' ' . $sTextAddedNew . ' ' . $sTextWallObject,
            'description' => $aComment['cmt_text'],
            'content' => $this->parseHtmlByName('p_comment.html', $aTmplVars)
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

    function getCommonMedia($sType, $iObject)
    {
        $aConverter = array('photos' => 'photo', 'sounds' => 'music', 'videos' => 'video');

        $aMediaInfo = BxDolService::call($sType, 'get_' . $aConverter[$sType] . '_array', array($iObject, 'browse'), 'Search');
        $aOwner = $this->_oDb->getUser($aMediaInfo['owner']);

        $sAddedMediaTxt = _t('_wall_added_' . $sType);

        $sContent = '';
        if(!empty($aMediaInfo) && is_array($aMediaInfo) && !empty($aMediaInfo['file']))
            $aContent = array(
                'title' => $aOwner['username'] . ' ' . $sAddedMediaTxt,
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
        else
            $aContent = array('title' => '', 'description' => '', 'content' => $iObject);

        return $aContent;
    }

    function getDefaultComments($iEventId)
    {
        $oComments = new BxWallCmts($this->_oConfig->getCommentSystemName(), $iEventId);
        return $oComments->getCommentsFirst('comment');
    }

    function getJsCode($sType, $aParams = array(), $aRequestParams = array())
    {
    	$sJsClass = $this->_oConfig->getJsClass($sType);
    	$sJsObject = $this->_oConfig->getJsObject($sType);

    	$aParams = array_merge(array(
    		'sActionUrl' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(), 
    		'sObjName' => $sJsObject,
    		'iOwnerId' => 0,
    		'sAnimationEffect' => $this->_oConfig->getAnimationEffect(),
    		'iAnimationSpeed' => $this->_oConfig->getAnimationSpeed(),
    		'oRequestParams' => $aRequestParams
    	), $aParams);

        return $this->_wrapInTagJsCode("var " . $sJsObject . " = new " . $sJsClass . "(" . json_encode($aParams) . ");");
    }
}
