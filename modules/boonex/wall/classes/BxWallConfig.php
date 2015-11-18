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
    var $_sAnimationEffect;
    var $_iAnimationSpeed;
    var $_iCharsDisplayMax;
    var $_iRssLength;
    var $_aHideTimeline;
    var $_aHideOutline;

    var $_aHandlers;
    var $_aJsClasses;
    var $_aJsObjects;

    /**
     * Constructor
     */
    function BxWallConfig($aModule)
    {
        parent::BxDolConfig($aModule);

        $sName = 'bx_wall';

        $this->_bJsMode = false;

        $this->_sAlertSystemName = $sName;
        $this->_sCommentSystemName = $sName;
        $this->_sVotingSystemName = $sName;

        $this->_sCommonPostPrefix = 'wall_common_';

        $this->_sAnimationEffect = 'fade';
        $this->_iAnimationSpeed = 'slow';
        $this->_sDividerDateFormat = getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB);

        $this->_aHideTimeline = array();
        $this->_aHideOutline = array();
        $this->_aHandlers = array();

        $this->_aJsClasses = array(
            'post' => 'BxWallPost',
            'view' => 'BxWallView',
            'outline' => 'BxWallOutline'
        );

        $this->_aJsObjects = array(
            'post' => 'oWallPost',
            'view' => 'oWallView',
            'outline' => 'oWallOutline',
        	'voting' => 'oWallVoting',
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

        $sHideTimeline = $this->_oDb->getParam('wall_events_hide_timeline');
        if(!empty($sHideTimeline))
            $this->_aHideTimeline = explode(',', $sHideTimeline);

        $sHideOutline = $this->_oDb->getParam('wall_events_hide_outline');
        if(!empty($sHideOutline))
            $this->_aHideOutline = explode(',', $sHideOutline);

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
                $aResult = $this->_aHideTimeline;
                break;
            case BX_WALL_VIEW_OUTLINE:
                $aResult = $this->_aHideOutline;
                break;
        }

        return $aResult;
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
}
