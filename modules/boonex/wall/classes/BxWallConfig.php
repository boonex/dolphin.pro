<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

class BxWallConfig extends BxDolConfig
{
    var $_oDb;
    var $_bAllowDelete;
    var $_bFullCompilation;
    var $_bJsMode;
    var $_sDividerDateFormat;
    var $_sCommonPostPrefix;
    var $_sCommentSystemName;
    var $_sVotingSystemName;
    var $_iPerPageProfileTl;
    var $_iPerPageAccountTl;
    var $_iPerPageIndex;
    var $_iCharsDisplayMax;
    var $_iRssLength;
    var $_aHideEventsTimeline;
    var $_aHideEventsOutline;
    var $_aHideUploadersTimeline;
	var $_aRepostDefaults;

	var $_sAnimationEffect;
    var $_iAnimationSpeed;

	var $_aHtmlIds;
    var $_aPrefixes;
    var $_aJsClasses;
    var $_aJsObjects;

    var $_aHandlers;

    function __construct($aModule)
    {
        parent::__construct($aModule);

        $sName = 'bx_wall';

        $this->_bJsMode = false;

        $this->_sAlertSystemName = $sName;
        $this->_sCommentSystemName = $sName;
        $this->_sVotingSystemName = $sName;

        $this->_aRepostDefaults = array(
            'show_do_repost_as_button' => false,
            'show_do_repost_as_button_small' => false,
            'show_do_repost_icon' => false,
            'show_do_repost_label' => true,
            'show_counter' => true
        );

        $this->_sCommonPostPrefix = 'wall_common_';
        $this->_aPrefixes = array(
        	'style' => 'wall',
        	'language' => '_wall',
        	'option' => 'wall_',
        	'common_post' => $this->_sCommonPostPrefix
        );

        $this->_sAnimationEffect = 'fade';
        $this->_iAnimationSpeed = 'slow';
        $this->_sDividerDateFormat = getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB);

        $this->_aHideEventsTimeline = array();
        $this->_aHideEventsOutline = array();
        $this->_aHideUploadersTimeline = array();
        $this->_aHandlers = array();

        $this->_aJsClasses = array(
            'post' => 'BxWallPost',
        	'repost' => 'BxWallRepost',
            'view' => 'BxWallView',
            'outline' => 'BxWallOutline'
        );

        $this->_aJsObjects = array(
            'post' => 'oWallPost',
        	'repost' => 'oWallRepost',
            'view' => 'oWallView',
            'outline' => 'oWallOutline',
        	'voting' => 'oWallVoting',
        );

        $sHtmlPrefix = str_replace('_', '-', $sName);
        $this->_aHtmlIds = array(
        	'post' => array(
        		'loading' => $sHtmlPrefix . '-post-loading',
        	),
        	'repost' => array(
				'main' => $sHtmlPrefix . '-repost-',
				'counter' => $sHtmlPrefix . '-repost-counter-',
				'by_popup' => $sHtmlPrefix . '-repost-by-',
        	)
        );
    }

    function init(&$oDb)
    {
        $this->_oDb = &$oDb;

        $this->_bAllowDelete = $this->_oDb->getParam('wall_enable_delete') == 'on';
        $this->_iPerPageProfileTl = (int)$this->_oDb->getParam('wall_events_per_page_profile_tl');
        $this->_iPerPageAccountTl = (int)$this->_oDb->getParam('wall_events_per_page_account_tl');
        $this->_iPerPageIndexTl = (int)$this->_oDb->getParam('wall_events_per_page_index_tl');
        $this->_iPerPageIndexOl = (int)$this->_oDb->getParam('wall_events_per_page_index_ol');
        $this->_iCharsDisplayMax = (int)$this->_oDb->getParam('wall_events_chars_display_max');
        $this->_iRssLength = (int)$this->_oDb->getParam('wall_rss_length');

        $sHideUploadersTimeline = $this->_oDb->getParam('wall_uploaders_hide_timeline');
        if(!empty($sHideUploadersTimeline))
            $this->_aHideUploadersTimeline = explode(',', $sHideUploadersTimeline);

        $sHideEventsTimeline = $this->_oDb->getParam('wall_events_hide_timeline');
        if(!empty($sHideEventsTimeline))
            $this->_aHideEventsTimeline = explode(',', $sHideEventsTimeline);

        $sHideEventsOutline = $this->_oDb->getParam('wall_events_hide_outline');
        if(!empty($sHideEventsOutline))
            $this->_aHideEventsOutline = explode(',', $sHideEventsOutline);

        $aHandlers = $this->_oDb->getHandlers();
        foreach($aHandlers as $aHandler)
           $this->_aHandlers[$aHandler['alert_unit'] . '_' . $aHandler['alert_action']] = $aHandler;
    }
    function getDividerDateFormat()
    {
        return $this->_sDividerDateFormat;
    }
    function getAlertSystemName()
    {
        return $this->_sAlertSystemName;
    }
    function getCommonPostPrefix()
    {
        return $this->_sCommonPostPrefix;
    }
    function getCommentSystemName()
    {
        return $this->_sCommentSystemName;
    }
	function getVotingSystemName()
    {
        return $this->_sVotingSystemName;
    }
    function getPerPage($sPage = 'profile')
    {
        $iResult = 10;
        switch($sPage) {
            case 'profile':
                $iResult = $this->_iPerPageProfileTl;
                break;
            case 'account':
                $iResult = $this->_iPerPageAccountTl;
                break;
            case 'index_tl':
                $iResult = $this->_iPerPageIndexTl;
                break;
			case 'index_ol':
                $iResult = $this->_iPerPageIndexOl;
                break;
        }

        return $iResult;
    }
    function getRepostDefaults()
    {
        return $this->_aRepostDefaults;
    }
    function getAnimationEffect()
    {
        return $this->_sAnimationEffect;
    }
    function getAnimationSpeed()
    {
        return $this->_iAnimationSpeed;
    }
    public function getCharsDisplayMax()
    {
        return $this->_iCharsDisplayMax;
    }
    function getRssLength()
    {
        return $this->_iRssLength;
    }
	function getPrefix($sType = '')
    {
    	if(empty($sType))
            return $this->_aPrefixes;

        return isset($this->_aPrefixes[$sType]) ? $this->_aPrefixes[$sType] : '';
    }
	function getJsClass($sType)
    {
        return $this->_aJsClasses[$sType];
    }
    function getJsObject($sType)
    {
        return $this->_aJsObjects[$sType];
    }
    function getHandlersHidden($sType)
    {
        $aResult = array();

        switch($sType) {
            case BX_WALL_VIEW_TIMELINE:
                $aResult = $this->_aHideEventsTimeline;
                break;
            case BX_WALL_VIEW_OUTLINE:
                $aResult = $this->_aHideEventsOutline;
                break;
        }

        return $aResult;
    }
	function getUploadersHidden($sType)
    {
        $aResult = array();

        switch($sType) {
            case BX_WALL_VIEW_TIMELINE:
                $aResult = $this->_aHideUploadersTimeline;
                break;
            case BX_WALL_VIEW_OUTLINE:
                $aResult = array();
                break;
        }

        return $aResult;
    }
	function getHtmlIds($sType, $sKey = '')
    {
        if(empty($sKey))
            return isset($this->_aHtmlIds[$sType]) ? $this->_aHtmlIds[$sType] : array();

        return isset($this->_aHtmlIds[$sType][$sKey]) ? $this->_aHtmlIds[$sType][$sKey] : '';
    }
    function getHandlers($sKey = '')
    {
        if($sKey == '')
            return $this->_aHandlers;

        return $this->_aHandlers[$sKey];
    }
    function isHandler($sKey = '')
    {
        return isset($this->_aHandlers[$sKey]);
    }
    function setJsMode($bJsMode)
    {
        $this->_bJsMode = $bJsMode;
    }
    function getJsMode()
    {
        return $this->_bJsMode;
    }
    function isJsMode()
    {
        return $this->_bJsMode;
    }
	function isSystem($sType, $sAction)
    {
        $sPrefix = $this->getCommonPostPrefix();
        return strpos($sType, $sPrefix) === false && !empty($sAction);
    }
    function getSystemData(&$aEvent, $sDisplayType = BX_WALL_VIEW_TIMELINE)
    {
		$sHandler = $aEvent['type'] . '_' . $aEvent['action'];
        if(!$this->isHandler($sHandler))
            return false;

        $aHandler = $this->getHandlers($sHandler);
        if(empty($aHandler['module_uri']) || empty($aHandler['module_class']) || empty($aHandler['module_method']))
        	return false; 

		return BxDolService::call($aHandler['module_uri'], $aHandler['module_method'] . ($sDisplayType == BX_WALL_VIEW_OUTLINE ? '_' . BX_WALL_VIEW_OUTLINE : ''), array($aEvent), $aHandler['module_class']);
    }
    function getSystemDataByDescriptor($sType, $sAction, $iObjectId, $sDisplayType = BX_WALL_VIEW_TIMELINE)
    {
    	$aDescriptor = array(
    		'type' => $sType, 
    		'action' => $sAction,
    		'object_id' => $iObjectId
    	);
    	return $this->getSystemData($aDescriptor, $sDisplayType);
    }

	function isSystemComment($sType, $sAction)
    {
        return strcmp($sType, 'comment') === 0 && strcmp($sAction, 'add') === 0;
    }

	function isGrouped($sType, $sAction, $mixedObjectId)
    {
    	$sHandler = $sType . '_' . $sAction;
        if(!$this->isHandler($sHandler))
            return false;
            
		$aHandler = $this->getHandlers($sHandler);
		if((int)$aHandler['groupable'] == 0 || empty($aHandler['group_by']))
			return false;

        return $this->isGroupedObject($mixedObjectId);
    }

    function isGroupedObject($mixedObjectId)
    {
        return strpos($mixedObjectId, BX_WALL_DIVIDER_OBJECT_ID) !== false;
    }

    function getCommonType($sName)
    {
    	return strtolower(str_replace('bx_', '', $sName));
    }

    function getCommonName($sType)
    {
    	return 'bx_' . $sType;
    }
}
