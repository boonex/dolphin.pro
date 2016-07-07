<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConfig');

class BxDolTextConfig extends BxDolConfig
{
    var $_oDb;
    var $_bAutoapprove;
    var $_bComments;
    var $_sCommentsSystemName;
    var $_bVotes;
    var $_sVotesSystemName;
    var $_sViewsSystemName;
    var $_sSubscriptionsSystemName;
    var $_sActionsViewSystemName;
    var $_sCategoriesSystemName;
    var $_sTagsSystemName;
    var $_sAlertsSystemName;
    var $_sSearchSystemName;
    var $_sDateFormat;
    var $_sAnimationEffect;
    var $_iAnimationSpeed;
    var $_iIndexNumber;
    var $_iMemberNumber;
    var $_iSnippetLength;
    var $_iPerPage;
    var $_sSystemPrefix;
    var $_aJsClasses;
    var $_aJsObjects;
    var $_iRssLength;

    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }
    function init(&$oDb)
    {
        $this->_oDb = &$oDb;
    }
    function isAutoapprove()
    {
        return $this->_bAutoapprove;
    }
    function isCommentsEnabled()
    {
        return $this->_bComments;
    }
    function getCommentsSystemName()
    {
        return $this->_sCommentsSystemName;
    }
    function isVotesEnabled()
    {
        return $this->_bVotes;
    }
    function getVotesSystemName()
    {
        return $this->_sVotesSystemName;
    }
    function getViewsSystemName()
    {
        return $this->_sViewsSystemName;
    }
    function getSubscriptionsSystemName()
    {
        return $this->_sSubscriptionsSystemName;
    }
    function getActionsViewSystemName()
    {
        return $this->_sActionsViewSystemName;
    }
    function getCategoriesSystemName()
    {
        return $this->_sCategoriesSystemName;
    }
    function getTagsSystemName()
    {
        return $this->_sTagsSystemName;
    }
    function getAlertsSystemName()
    {
        return $this->_sAlertsSystemName;
    }
    function getSearchSystemName()
    {
        return $this->_sSearchSystemName;
    }
    function getDateFormat()
    {
        return $this->_sDateFormat;
    }
    function getAnimationEffect()
    {
        return $this->_sAnimationEffect;
    }
    function getAnimationSpeed()
    {
        return $this->_iAnimationSpeed;
    }
    function getIndexNumber()
    {
        return $this->_iIndexNumber;
    }
    function getMemberNumber()
    {
        return $this->_iMemberNumber;
    }
    function getSnippetLength()
    {
        return $this->_iSnippetLength;
    }
    function getPerPage()
    {
        return $this->_iPerPage;
    }
    function getSystemPrefix()
    {
        return $this->_sSystemPrefix;
    }
    function getJsClass($sType = 'main')
    {
        if(empty($sType))
            return $this->_aJsClasses;

        return $this->_aJsClasses[$sType];
    }
    function getJsObject($sType = 'main')
    {
        if(empty($sType))
            return $this->_aJsObjects;

        return $this->_aJsObjects[$sType];
    }
    function getRssLength()
    {
        return $this->_iRssLength;
    }
}
