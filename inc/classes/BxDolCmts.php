<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolCmtsQuery.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolMistake.php' );
require_once( BX_DIRECTORY_PATH_PLUGINS . 'Services_JSON.php' );

define('BX_OLD_CMT_VOTES', 365*86400); // comment votes older than this number of seconds will be deleted automatically

/**
 * Comments for any content
 *
 * Related classes:
 *  BxDolCmtsQuery - comments database queries
 *  BxBaseCmtsView - comments base representation
 *  BxTemplCmtsView - custom template representation
 *
 * AJAX comments for any content.
 * Self moderated - users rate all comments, and if comment is
 * below viewing treshold it is hidden by default.
 *
 * To add comments section to your module you need to add a record to 'sys_objects_cmts' table:
 *
 * ID - autoincremented id for internal usage
 * ObjectName - your unique module name, with vendor prefix, lowercase and spaces are underscored
 * TableCmts - table name where comments are stored
 * TableTrack - table name where comments ratings are stored
 * AllowTags - 0 or 1 allow HTML tags in comments or not
 * Nl2br - convert new lines to <br /> on saving
 * SecToEdit - number of second to allow author to delete or edit comment
 * PerView - number of comments on a page
 * IsRatable - 0 or 1 allow to rate comments or not
 * ViewingThreshold - comment viewing treshost, if comment is below this number it is hidden by default
 * AnimationEffect - animation effect for comments, slide or fade
 * AnimationSpeed - animation speen in milliseconds
 * IsOn - is this comment object enabled
 * IsMood - allow user to select comment as negative, positive or neutral
 * RootStylePrefix - toot comments style prefix, if you need root comments look different
 * TriggerTable - table to be updated upon each comment
 * TriggerFieldId - TriggerTable table field with unique record id of
 * TriggerFieldComments - TriggerTable table field with comments count, it will be updated automatically upon eaech comment
 * ClassName - your custom class name if you need to override default class, this class must have the same constructor arguments
 * ClassFile - file where your ClassName is stored.
 *
 * You can refer to BoonEx modules for sample record in this table.
 *
 *
 *
 * Example of usage:
 * After filling in the table you can show comments section in any place, using the following code:
 *
 * bx_import ('BxTemplCmtsView');
 * $o = new BxTemplCmtsView ('value of ObjectName field', $iYourEntryId);
 * if ($o->isEnabled())
 *     echo $o->getCommentsFirst ();
 *
 * Please note that you never need to use BxDolCmts class directly, use BxTemplCmtsView instead.
 * Also if you override comments class with your own then make it child of BxTemplCmtsView class.
 *
 *
 *
 * Memberships/ACL:
 * comments post - ACTION_ID_COMMENTS_POST
 * comments vote - ACTION_ID_COMMENTS_VOTE
 * comments edit own - ACTION_ID_COMMENTS_EDIT_OWN
 * comments remove own - ACTION_ID_COMMENTS_REMOVE_OWN
 *
 *
 *
 * Alerts:
 * Alerts type/unit - every module has own type/unit, it equals to ObjectName
 * The following alerts are rised
 *
 * commentPost - comment was posted
 *      $iObjectId - entry id
 *      $iSenderId - author of comment
 *      $aExtra['comment_id'] - just added comment id
 *
 * commentRemoved - comments was removed
 *      $iObjectId - entry id
 *      $iSenderId - comment deleter id
 *      $aExtra['comment_id'] - removed comment id
 *
 * commentUpdated - comments was updated
 *      $iObjectId - entry id
 *      $iSenderId - comment deleter id
 *      $aExtra['comment_id'] - updated comment id
 *
 * commentRated - comments was rated
 *      $iObjectId - entry id
 *      $iSenderId - comment rater id
 *      $aExtra['comment_id'] - rated comment id
 *      $aExtra['rate'] - comment rate 1 or -1
 *
 */
class BxDolCmts extends BxDolMistake
{

    var $_iId = 0;  // obect id to be commented
    var $sTextAreaId;
    var $iGlobAllowHtml;
    var $iGlobUseTinyMCE;
    var $iAutoHideRootPostForm = 0;

    var $_sSystem = 'profile';  // current comment system name
    var $_aSystem = array ();   // current comments system array
    var $_aCmtElements = array (); // comment submit form elements
    var $_oQuery = null;
    var $_sOrder = 'desc';
    var $_aMoodText = array(
        '-1' => '_sys_txt_cmt_mood_negative',
        '0' => '_sys_txt_cmt_mood_neutral',
        '1' => '_sys_txt_cmt_mood_positive',
    );

    /**
     * Constructor
     * $sSystem - comments system name
     * $iId - obect id to be commented
     */
    function BxDolCmts( $sSystem, $iId, $iInit = 1)
    {
        $this->_aCmtElements = array (
            'CmtParent'	=> array ( 'reg' => '^[0-9]+$', 'msg' => str_replace('"', '\\"', trim(_t('_bad comment parent id'))) ),
            'CmtText' 	=> array ( 'reg' => '^.{3,2048}$', 'msg' => str_replace('"', '\\"', trim(_t('_Please enter 3-2048 characters'))) ),
            'CmtMood' 	=> array ( 'reg' => '^-?[0-9]?$', 'msg' => str_replace('"', '\\"', trim(_t('_You need to select the mood'))) ),
        );

        $this->_aSystems =& $this->getSystems ();

        $this->_sSystem = $sSystem;
        if (isset($this->_aSystems[$sSystem]))
            $this->_aSystem = $this->_aSystems[$sSystem];
        else
            return;

        $this->_oQuery = new BxDolCmtsQuery($this->_aSystem);

        if ($iInit)
            $this->init($iId);

        $this->sTextAreaId = "cmt" . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->_sSystem))) . "TextAreaParent";
        $this->iGlobAllowHtml = (getParam("enable_tiny_in_comments") == "on") ? 1 : 0;
        $this->iGlobUseTinyMCE = $this->iGlobAllowHtml;
    }

    /**
     * get comments object instanse
     * @param $sSys comments object name 
     * @param $iId associated content id, where comments are postred in
     * @param $iInit perform initialization
     * @return null on error, or ready to use class instance
     */ 
    static function getObjectInstance($sSys, $iId, $iInit = true) 
    {
        $aSystems = self::getSystems ();
        if (!isset($aSystems[$sSys]))
            return null;

        bx_import('BxTemplCmtsView');
        $sClassName = 'BxTemplCmtsView';
        if ($aSystems[$sSys]['class_name']) {
            require_once (BX_DIRECTORY_PATH_ROOT . $aSystems[$sSys]['class_file']);
            $sClassName = $aSystems[$sSys]['class_name'];  
        } 

        $oCmts = new $sClassName($sSys, $iId, true);
        return $oCmts;
    }

    function & getSystems ()
    {
        if (!isset($GLOBALS['bx_dol_cmts_systems'])) {
            $GLOBALS['bx_dol_cmts_systems'] = $GLOBALS['MySQL']->fromCache('sys_objects_cmts', 'getAllWithKey', '
                SELECT
                    `ID` as `system_id`,
                    `ObjectName` AS `name`,
                    `TableCmts` AS `table_cmts`,
                    `TableTrack` AS `table_track`,
                    `AllowTags` AS `allow_tags`,
                    `Nl2br` AS `nl2br`,
                    `SecToEdit` AS `sec_to_edit`,
                    `PerView` AS `per_view`,
                    `IsRatable` AS `is_ratable`,
                    `ViewingThreshold` AS `viewing_threshold`,
                    `AnimationEffect` AS `animation_effect`,
                    `AnimationSpeed` AS `animation_speed`,
                    `IsOn` AS `is_on`,
                    `IsMood` AS `is_mood`,
                    `RootStylePrefix` AS `root_style_prefix`,
                    `TriggerTable` AS `trigger_table`,
                    `TriggerFieldId` AS `trigger_field_id`,
                    `TriggerFieldComments` AS `trigger_field_comments`,
                    `ClassName` AS `class_name`,
                    `ClassFile` AS `class_file`
                FROM `sys_objects_cmts`', 'name');
        }
        return $GLOBALS['bx_dol_cmts_systems'];
    }

    function init ($iId)
    {
        if (!$this->isEnabled()) return;
        if (empty($this->iId) && $iId) {
            $this->setId($iId);
        }
    }

    /**
     * check if user can post/edit or delete own comments
     */
    function checkAction ($iAction, $isPerformAction = false)
    {
        $iId = $this->_getAuthorId();
        $check_res = checkAction($iId, $iAction, $isPerformAction);
        return $check_res[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function checkActionErrorMsg ($iAction)
    {
        $iId = $this->_getAuthorId();
        $check_res = checkAction($iId, $iAction);
        return $check_res[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED ? $check_res[CHECK_ACTION_MESSAGE] : '';
    }

    function getId ()
    {
        return $this->_iId;
    }

    function isEnabled ()
    {
        return isset($this->_aSystem['is_on']) && $this->_aSystem['is_on'];
    }

    function getSystemName()
    {
        return $this->_sSystem;
    }

    function getOrder ()
    {
        return $this->_sOrder;
    }

    /**
     * set id to operate with votes
     */
    function setId ($iId)
    {
        if ($iId == $this->getId()) return;
        $this->_iId = $iId;
    }

    function getBaseUrl ()
    {
    	return '';
    }

    function isValidSystem ($sSystem)
    {
        return isset($this->_aSystems[$sSystem]);
    }

    function isTagsAllowed ()
    {
        return $this->_aSystem['allow_tags'];
    }

    function isNl2br ()
    {
        return $this->_aSystem['nl2br'];
    }

    function isRatable ()
    {
        return $this->_aSystem['is_ratable'];
    }

    function getAllowedEditTime ()
    {
        return $this->_aSystem['sec_to_edit'];
    }

    function getPerView ()
    {
        return $this->_aSystem['per_view'];
    }

    function getSystemId ()
    {
        return $this->_aSystem['system_id'];
    }

    /**
     * it is called on cron every day or similar period to clean old comment votes
     */
    function maintenance ()
    {
        $iDeletedRecords = 0;
        foreach ($this->_aSystems as $aSystem) {
            if (!$aSystem['is_on'])
                continue;
            $oQuery = new BxDolCmtsQuery($aSystem);
            $iDeletedRecords += $oQuery->maintenance();
            unset($oQuery);
        }
        return $iDeletedRecords;
    }

    /** comments functions
     *********************************************/

    function getCommentsArray ($iCmtParentId, $sCmtOrder, $iStart = 0, $iCount = -1)
    {
        return $this->_oQuery->getComments ($this->getId(), $iCmtParentId, $this->_getAuthorId(), $sCmtOrder, $iStart, $iCount);
    }

    function getCommentRow ($iCmtId)
    {
        return $this->_oQuery->getComment ($this->getId(), $iCmtId, $this->_getAuthorId());
    }

    function onObjectDelete ($iObjectId = 0)
    {
        return $this->_oQuery->deleteObjectComments ($iObjectId ? $iObjectId : $this->getId());
    }

    // delete all profiles comments in all systems, if some replies exist, set this comment to anonymous

    function onAuthorDelete ($iAuthorId)
    {
        for ( reset($this->_aSystems) ; list ($sSystem, $aSystem) = each ($this->_aSystems) ; ) {
            $oQuery = new BxDolCmtsQuery($aSystem);
            $oQuery->deleteAuthorComments ($iAuthorId);
        }
        return true;
    }

    function getCommentsTableName ()
    {
        return $this->_oQuery->getTableName ();
    }

    function getObjectCommentsCount ($iObjectId = 0)
    {
        return $this->_oQuery->getObjectCommentsCount ($iObjectId ? $iObjectId : $this->getId());
    }

    /** permissions functions

    *********************************************/

    // is rate comment allowed
    function isRateAllowed ($isPerformAction = false)
    { 
        return $this->checkAction (ACTION_ID_COMMENTS_VOTE, $isPerformAction); 
    }

    function msgErrRateAllowed ()
    { 
        return $this->checkActionErrorMsg(ACTION_ID_COMMENTS_VOTE);
    }

    // is post comment allowed
    function isPostReplyAllowed ($isPerformAction = false)
    {
        return $this->checkAction (ACTION_ID_COMMENTS_POST, $isPerformAction);
    }

    function msgErrPostReplyAllowed ()
    {
        return $this->checkActionErrorMsg(ACTION_ID_COMMENTS_POST);
    }

    // is edit own comment allowed
    function isEditAllowed ($isPerformAction = false)
    {
        return $this->checkAction (ACTION_ID_COMMENTS_EDIT_OWN, $isPerformAction);
    }

    function msgErrEditAllowed ()
    {
        return $this->checkActionErrorMsg (ACTION_ID_COMMENTS_EDIT_OWN);
    }

    // is removing own comment allowed
    function isRemoveAllowed ($isPerformAction = false)
    {
        return $this->checkAction (ACTION_ID_COMMENTS_REMOVE_OWN, $isPerformAction);
    }

    function msgErrRemoveAllowed ()
    {
        return $this->checkActionErrorMsg(ACTION_ID_COMMENTS_REMOVE_OWN);
    }

    // is edit any comment allowed
    function isEditAllowedAll ()
    {
        return isAdmin() ? true : false;
    }

    // is removing any comment allowed
    function isRemoveAllowedAll ()
    {
        return isAdmin() ? true : false;
    }

    /**
     * actions functions
     */
    function actionPaginateGet ()
    {
        if (!$this->isEnabled())
           return '';

        $iCmtStart = isset($_REQUEST['CmtStart']) ? (int)$_REQUEST['CmtStart'] : 0;
        $iCmtPerPage= isset($_REQUEST['CmtPerPage']) ? (int)$_REQUEST['CmtPerPage'] : $this->getPerView();

        return $this->getPaginate($iCmtStart, $iCmtPerPage);
    }

    function actionFormGet ()
    {
        if (!$this->isEnabled())
           return '';

        $sCmtType = isset($_REQUEST['CmtType']) ? $_REQUEST['CmtType'] : 'reply';
        $iCmtParentId= isset($_REQUEST['CmtParent']) ? (int)$_REQUEST['CmtParent'] : 0;

        return $this->getForm($sCmtType, $iCmtParentId);
    }

    function actionCmtsGet ()
    {
        if (!$this->isEnabled())
           return '';

        $iCmtParentId = (int)$_REQUEST['CmtParent'];
        $sCmtOrder = isset($_REQUEST['CmtOrder']) ? $_REQUEST['CmtOrder'] : $this->_sOrder;
        $iCmtStart = isset($_REQUEST['CmtStart']) ? (int)$_REQUEST['CmtStart'] : 0;
        $iCmtPerPage= isset($_REQUEST['CmtPerPage']) ? (int)$_REQUEST['CmtPerPage'] : -1;

        return $this->getComments($iCmtParentId, $sCmtOrder, $iCmtStart, $iCmtPerPage);
    }

    function actionCmtGet ()
    {
        if (!$this->isEnabled())
           return '';

        $iCmtId = (int)$_REQUEST['Cmt'];
        return $this->getComment ($iCmtId, (isset($_REQUEST['Type']) ? $_REQUEST['Type'] : 'new'));
    }

    function actionCmtPost()
    {
        if(!$this->isEnabled())
        	return '';

        if(!$this->isPostReplyAllowed())
        	return $this->msgErrPostReplyAllowed();

        $iCmtObjectId = (int)$this->getId();
        $iCmtParentId = (int)$_REQUEST['CmtParent'];
        $iCmtAuthorId = $this->_getAuthorId();

        if ($this->_isSpam($_REQUEST['CmtText']))
            return sprintf(_t("_sys_spam_detected"), BX_DOL_URL_ROOT . 'contact.php');

        $sText = $this->_prepareTextForSave ($_REQUEST['CmtText']);

        $iMood = (int)$_REQUEST['CmtMood'];

        $iCmtNewId = $this->_oQuery->addComment ($iCmtObjectId, $iCmtParentId, $iCmtAuthorId, $sText, $iMood);

        if(false === $iCmtNewId)
            return '';

        $this->_triggerComment();

        $this->isPostReplyAllowed(true);

        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts($this->_sSystem, 'commentPost', $iCmtObjectId, $iCmtAuthorId, array('comment_id' => $iCmtNewId, 'comment_author_id' => $iCmtAuthorId));
        $oZ->alert();

        $oZ = new BxDolAlerts('comment', 'add', $iCmtNewId, $iCmtAuthorId, array('object_system' => $this->_sSystem, 'object_id' => $iCmtObjectId));
        $oZ->alert();

        return $iCmtNewId;
    }

    // returns error string on error, or empty string on success

    function actionCmtRemove ()
    {
        if(!$this->isEnabled())
        	return '';

        $iCmtId = (int)$_REQUEST['Cmt'];
        $iObjectId = (int)$this->getId();
        $iAuthorId = $this->_getAuthorId();

        $aCmt = $this->_oQuery->getCommentSimple ($iObjectId, $iCmtId);
        if(!$aCmt)
			return _t('_No such comment');

        if($aCmt['cmt_replies'] > 0)
			return _t('_Can not delete comments with replies');

        $isRemoveAllowed = $this->isRemoveAllowedAll() || ($aCmt['cmt_author_id'] == $iAuthorId && $this->isRemoveAllowed());
        if (!$isRemoveAllowed && $aCmt['cmt_secs_ago'] > ($this->getAllowedEditTime()+20))
            return $aCmt['cmt_author_id'] == $iAuthorId && !$this->isRemoveAllowed() ? strip_tags($this->msgErrRemoveAllowed()) : _t('_Access denied');

        if (!$this->_oQuery->removeComment ($iObjectId, $aCmt['cmt_id'], $aCmt['cmt_parent_id']))
            return _t('_Database Error');

        $this->_triggerComment();

        if ($aCmt['cmt_author_id'] == $iAuthorId)
           $this->isRemoveAllowed(true);

        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts($this->_sSystem, 'commentRemoved', $iObjectId, $iAuthorId, array('comment_id' => $aCmt['cmt_id'], 'comment_author_id' => $aCmt['cmt_author_id']));
        $oZ->alert();

        $oZ = new BxDolAlerts('comment', 'delete', $aCmt['cmt_id'], $iAuthorId, array('object_system' => $this->_sSystem, 'object_id' => $iObjectId));
        $oZ->alert();

        return '';
    }

    // returns string with "err" prefix on error, or string with html form on success

    function actionCmtEdit ()
    {
        if (!$this->isEnabled()) return '';

        $iCmtId = (int)$_REQUEST['Cmt'];
        $aCmt = $this->_oQuery->getCommentSimple ($this->getId(), $iCmtId);

        if (!$aCmt)
            return 'err'._t('_No such comment');

        $isEditAllowed = $this->isEditAllowedAll() || ($aCmt['cmt_author_id'] == $this->_getAuthorId() && $this->isEditAllowed());
        if (!$isEditAllowed && $aCmt['cmt_secs_ago'] > ($this->getAllowedEditTime()+20))
            return 'err' . ($aCmt['cmt_author_id'] == $this->_getAuthorId() && !$this->isEditAllowed() ? strip_tags($this->msgErrEditAllowed()) : _t('_Access denied'));

        return $this->_getFormBox (0, $this->_prepareTextForEdit($aCmt['cmt_text']), 'updateComment(this, \''.$iCmtId.'\')');
    }

    function actionCmtEditSubmit()
    {
        if (!$this->isEnabled()) return '{}';

        $oJson = new Services_JSON();

        $iCmtId = (int)$_REQUEST['Cmt'];

        if ($this->_isSpam($_REQUEST['CmtText']))
            return $oJson->encode(array('err' => sprintf(_t("_sys_spam_detected"), BX_DOL_URL_ROOT . 'contact.php')));

		$iObjectId = $this->getId();
        $iAuthorId = $this->_getAuthorId();

        $sText = $this->_prepareTextForSave ($_REQUEST['CmtText']);
		$sTextRet = stripslashes($sText);

        $iCmtMood = (int)$_REQUEST['CmtMood'];

        $aCmt = $this->_oQuery->getCommentSimple ($iObjectId, $iCmtId);
        if(!$aCmt)
            return '{}';

        $isEditAllowed = $this->isEditAllowedAll() || ($aCmt['cmt_author_id'] == $iAuthorId && $this->isEditAllowed());

        if (!$isEditAllowed && $aCmt['cmt_secs_ago'] > ($this->getAllowedEditTime()+20))
            return '{}';

        if (($sTextRet != $aCmt['cmt_text'] || $iCmtMood != $aCmt['cmt_mood']) && $this->_oQuery->updateComment ($iObjectId, $aCmt['cmt_id'], $sText, $iCmtMood)) {
            if ($aCmt['cmt_author_id'] == $iAuthorId)
               $this->isEditAllowed(true);

            bx_import('BxDolAlerts');
            $oZ = new BxDolAlerts($this->_sSystem, 'commentUpdated', $iObjectId, $iAuthorId, array('comment_id' => $aCmt['cmt_id'], 'comment_author_id' => $aCmt['cmt_author_id']));
            $oZ->alert();

			$oZ = new BxDolAlerts('comment', 'change', $aCmt['cmt_id'], $iAuthorId, array('object_system' => $this->_sSystem, 'object_id' => $iObjectId));
			$oZ->alert();
        }

        $aCmt = $this->_oQuery->getCommentSimple($iObjectId, $iCmtId);

        return $oJson->encode(array('text' => $aCmt['cmt_text'], 'mood' => $aCmt['cmt_mood'], 'mood_text' => _t($this->_aMoodText[$aCmt['cmt_mood']])));
    }

    function actionCmtRate ()
    {
        if (!$this->isEnabled()) return _t('_Error occured');
        if (!$this->isRatable()) return _t('_Error occured');
        if (!$this->isRateAllowed()) return strip_tags($this->msgErrRateAllowed());

        $iCmtId = (int)$_REQUEST['Cmt'];
        $iRate = (int)$_REQUEST['Rate'];

        if($iRate >= 1)
            $iRate = 1;
        elseif($iRate <= -1)
            $iRate = -1;
        else
            return _t('_Error occured');

        if(!$this->_oQuery->rateComment($this->getSystemId(), $iCmtId, $iRate, $this->_getAuthorId(), $this->_getAuthorIp()))
            return _t('_Duplicate vote');

        $aCmt = $this->_oQuery->getCommentSimple ($this->getId(), $iCmtId);

        $this->isRateAllowed(true);

        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts($this->_sSystem, 'commentRated', $this->getId(), $this->_getAuthorId(), array('comment_id' => $iCmtId, 'comment_author_id' => $aCmt['cmt_author_id'], 'rate' => $iRate));
        $oZ->alert();        

        return '';
    }

    /** private functions
    *********************************************/

    function _getAuthorId ()
    {
        return isMember() ? (int)$_COOKIE['memberID'] : 0;
    }

    function _getAuthorPassword ()
    {
        return isMember() ? $_COOKIE['memberPassword'] : "";
    }

    function _getAuthorIp ()
    {
        return getVisitorIP();
    }

    function _prepareTextForEdit ($s)
    {
        if ($this->isNl2br())
            return str_replace(array('<br />', '&shy;'), '', $s);
        return $s;
    }

    function _prepareTextForSave ($s)
    {
        if ($this->iGlobUseTinyMCE || $this->isTagsAllowed()) {
            $iTagsAction = BX_TAGS_VALIDATE;
        } 
        elseif (!$this->iGlobUseTinyMCE && $this->isNl2br()) {
            $s = WordWrapStr($s);
            $iTagsAction = BX_TAGS_STRIP_AND_NL2BR;
        } 
        else {
            $iTagsAction = BX_TAGS_STRIP;
        }

        return process_db_input($s, $iTagsAction);
    }

    function _prepareTextForOutput ($s)
    {
    	return 0 === strpos($s, '<iframe') ? $s : bx_linkify_html($s, 'class="' . BX_DOL_LINK_CLASS . '"');
    }

    function _triggerComment()
    {
        if (!$this->_aSystem['trigger_table'])
            return false;
        $iId = $this->getId();
        if (!$iId)
            return false;
        $iCount = $this->_oQuery->getObjectCommentsCount ($iId);
        return $this->_oQuery->updateTriggerTable($iId, $iCount);
    }

    function _isSpam($s)
    {
        return bx_is_spam($s);
    }

    function _getAnchor($iId)
    {
    	$aReplacement = array(
    		' ' => '-',
    		'_' => '-'
    	);

    	return str_replace(array_keys($aReplacement), array_values($aReplacement), $this->_sSystem . '-' . $this->getId() . '-' . $iId);
    }
}
