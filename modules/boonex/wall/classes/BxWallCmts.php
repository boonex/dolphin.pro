<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplCmtsView');

class BxWallCmts extends BxTemplCmtsView
{
    var $_oModule;

    /**
     * Constructor
     */
    function BxWallCmts($sSystem, $iId, $iInit = 1)
    {
        parent::BxTemplCmtsView($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxWallModule');
        $this->iAutoHideRootPostForm = 1;
    }

	function init ($iId)
    {
        if(empty($this->iId) && $iId)
            $this->setId($iId);
    }

    function actionCmtPost ()
    {
        $mixedResult = parent::actionCmtPost();
        if(empty($mixedResult))
            return $mixedResult;

        $aEvent = $this->_oModule->_oDb->getEvents(array('browse' => 'id', 'object_id' => (int)$this->getId()));
        if(isset($aEvent['owner_id']) && (int)$aEvent['owner_id'] > 0) {
            //--- Wall -> Update for Alerts Engine ---//
            bx_import('BxDolAlerts');
            $oAlert = new BxDolAlerts('bx_' . $this->_oModule->_oConfig->getUri(), 'update', $aEvent['owner_id']);
            $oAlert->alert();
            //--- Wall -> Update for Alerts Engine ---//
        }

        return $mixedResult;
    }

    /**
     * get full comments block with initializations
     */
    function getCommentsFirst($sType)
    {
        $iObjectId = $this->getId();
        return $this->_oModule->_oTemplate->parseHtmlByTemplateName('comments', array(
            'cmt_actions' => $this->getActions(0, $sType, $iObjectId),
            'cmt_system' => $this->_sSystem,
            'cmt_object' => $iObjectId,
        	'bx_if:show_replies' => array(
                'condition' => false,
                'content' => array()
            ),
            'cmt_addon' => $this->getCmtsInit()
        ));
    }

    /**
     * get full comments(system) block with initializations
     */
    function getCommentsFirstSystem($sType, $iEventId, $iCommentId = 0)
    {
        return $this->_oModule->_oTemplate->parseHtmlByTemplateName('comments', array(
            'cmt_actions' => $this->getActions($iCommentId, $sType, $iEventId),
            'cmt_system' => $this->_sSystem,
            'cmt_object' => $this->getId(),
            'bx_if:show_replies' => array(
                'condition' => $iCommentId != 0,
                'content' => array(
                    'id' => $iCommentId,
                )
            ),
            'cmt_addon' => $this->getCmtsInit()
        ));
    }

    function getActions($iCmtId, $sType = 'comment', $iEventId = 0)
    {
        $aEvent = $this->_oModule->_oDb->getEvents(array('browse' => 'id', 'object_id' => $iEventId));

        //--- Comment
        $iObjectId = $this->getId();
        $aParams = array(
            'cmt_id' => $iCmtId,
            'cmt_replies' => $this->_oQuery->getObjectCommentsCount($iObjectId, $iCmtId),
            'cmt_type' => $sType
        );

        //--- Reply
        $aReply = array();
        $bReply = $this->isEnabled() && $this->isPostReplyAllowed();
        if($bReply)
			$aReply = array(
				'js_object' => $this->_sJsObjName,
				'id' => $aParams['cmt_id']
			);

		//--- Replies
		$aReplies = array();
		$bReplies = $this->isEnabled() && (int)$aParams['cmt_replies'] > 0;
		if($bReplies)
			$aReplies = array(
				'js_object' => $this->_sJsObjName,
				'id' => $aParams['cmt_id'], 
				'show_attr' => _t('_wall_show_n_comments', $aParams['cmt_replies']),
				'show' => $aParams['cmt_replies'],
				'hide_attr' => _t('_wall_hide_n_comments', $aParams['cmt_replies']),
				'hide' => $aParams['cmt_replies'],
			);

		//--- Vote
		$aVote = array();
        $oVote = $this->_oModule->_getObjectVoting($aEvent);
        $bVote = $oVote->isEnabled() && $oVote->isVotingAllowed();
        if($bVote)
        	$aVote = array(
				'content' => $oVote->getVotingTimeline()
        	);

        //--- Repost
        $sRepost = '';
        $bRepost = $this->_oModule->_isRepostAllowed($aEvent);
        if($bRepost) {
        	$iOwnerId = $this->_oModule->_getAuthorId(); //--- in whose timeline the content will be shared
        	$iObjectId = $this->_oModule->_oConfig->isSystem($aEvent['type'], $aEvent['action']) ? $aEvent['object_id'] : $aEvent['id'];

        	$sRepost = $this->_oModule->serviceGetRepostElementBlock($iOwnerId, $aEvent['type'], $aEvent['action'], $iObjectId, array(
        		'show_do_repost_icon' => true,
        		'show_do_repost_label' => false
        	));
        }

        $sRet = $this->_oModule->_oTemplate->parseHtmlByTemplateName('comments_actions', array(
            'date' => $aEvent['ago'],
            'bx_if:show_delete' => array(
                'condition' => $this->_oModule->_isCommentDeleteAllowed($aEvent),
                'content' => array(
                    'js_view_object' => $this->_oModule->_oConfig->getJsObject('view'),
                    'id' => $aEvent['id']
                )
            ),
            'bx_if:show_reply' => array(
                'condition' => $bReply,
                'content' => $aReply
            ),
            'bx_if:show_replies' => array(
                'condition' => $bReplies,
                'content' => $aReplies
            ),
            'bx_if:show_vote' => array(
                'condition' => $bVote,
                'content' => $aVote
            ),
            'bx_if:show_repost' => array(
                'condition' => $bRepost,
                'content' => array(
                    'content' => $sRepost
                )
            ),
        ));

        return $sRet;
    }
}
