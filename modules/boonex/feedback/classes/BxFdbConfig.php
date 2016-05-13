<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextConfig');

class BxFdbConfig extends BxDolTextConfig
{
    function __construct($aModule)
    {
        parent::__construct($aModule);
    }
    function init(&$oDb)
    {
        parent::init($oDb);

        $sUri = $this->getUri();
        $sName = 'bx_' . $sUri;

        $this->_bAutoapprove = $this->_oDb->getParam('feedback_autoapprove') == 'on';
        $this->_bComments = $this->_oDb->getParam('feedback_comments') == 'on';
        $this->_sCommentsSystemName = $sName;
        $this->_bVotes = $this->_oDb->getParam('feedback_votes') == 'on';
        $this->_sVotesSystemName = $sName;
        $this->_sViewsSystemName = $sName;
        $this->_sSubscriptionsSystemName = $sName;
        $this->_sActionsViewSystemName = $sName;
        $this->_sCategoriesSystemName = $sName;
        $this->_sTagsSystemName = $sName;
        $this->_sAlertsSystemName = $sName;
        $this->_sSearchSystemName = $sName;
        $this->_sDateFormat = getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB);
        $this->_sAnimationEffect = 'fade';
        $this->_iAnimationSpeed = 'slow';
        $this->_iIndexNumber = (int)$this->_oDb->getParam('feedback_index_number');
        $this->_iSnippetLength = (int)$this->_oDb->getParam('feedback_snippet_length');
        $this->_iPerPage = (int)$this->_oDb->getParam('feedback_per_page');
        $this->_sSystemPrefix = 'feedback';
        $this->_aJsClasses = array('main' => 'BxFeedbackMain');
        $this->_aJsObjects = array('main' => 'oFeedbackMain');
        $this->_iRssLength = (int)$this->_oDb->getParam('feedback_rss_length');
    }
}
