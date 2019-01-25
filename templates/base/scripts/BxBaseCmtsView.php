<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolCmts' );
bx_import('BxDolPaginate');

/**
 * @see BxDolCmts
 */
class BxBaseCmtsView extends BxDolCmts
{
    var $_oPaginate;
    var $_sStylePrefix;

    function __construct( $sSystem, $iId, $iInit = 1 )
    {
        BxDolCmts::__construct( $sSystem, $iId, $iInit );
        if (empty($sSystem) || !$this->_aSystem)
            return;

        $this->_sJsObjName = 'oCmts' . ucfirst($sSystem) . $iId;
        $this->_oPaginate = new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'start' => 0,
            'count' => $this->_oQuery->getObjectCommentsCount($this->getId(), 0),
            'per_page' => $this->getPerView(),
            'sorting' => $this->_sOrder,
            'per_page_step' => 2,
            'per_page_interval' => 3,
            'on_change_page' => $this->_sJsObjName . '.changePage(this, {start}, {per_page})',
            'on_change_per_page' => $this->_sJsObjName . '.changePerPage(this)',
            'on_change_sorting' => $this->_sJsObjName . '.changeOrder(this)',
            'info' => false,
        ));
        $this->_sStylePrefix = isset($this->_aSystem['root_style_prefix']) ? $this->_aSystem['root_style_prefix'] : 'cmt';

        $GLOBALS['oSysTemplate']->addJsTranslation('_sys_txt_cmt_loading');
    }

    /**
     * get full comments block with initializations
     */
    function getCommentsFirst ()
    {
        $sPaginate = $this->getPaginate();

        return $GLOBALS['oSysTemplate']->parseHtmlByName('cmts_main.html', array(
            'html_id' => $this->_sSystem . '-' . $this->getId(),
            'top_controls' => $this->_getBrowse(),
            'list' => $this->getComments (0, $this->_sOrder),
            'bx_if:show_paginate' => array(
                'condition' => $sPaginate !== "",
                'content' => array(
                    'content' => $sPaginate
                )
            ),
            'bx_if:show_post' => array(
                'condition' => $this->isPostReplyAllowed(),
                'content' => array(
                    'content' => $this->_getPostReplyBox()
                )
            ),
            'js_code' => $this->getCmtsInit(),
        ));
    }

    /**
     * get comments list for specified parent comment
     *
     * @param int $iCmtsParentId - parent comment to get child comments from
     */
    function getComments ($iCmtsParentId = 0, $sCmtOrder = 'asc', $iStart = 0, $iPerPage = -1)
    {
        if($iCmtsParentId == 0 && $iPerPage == -1)
            $iPerPage = $this->getPerView();

        $sRet = '<ul class="cmts">';

        $aCmts = $this->getCommentsArray ($iCmtsParentId, $sCmtOrder, $iStart, $iPerPage);
        if (!$aCmts)
            $sRet .= '<li class="cmt-no">' . _t('_There are no comments yet') . '</li>';
        else {
            $i = 0;
            foreach ($aCmts as $k => $r) {
            	$sAnchor = $this->_getAnchor($r['cmt_id']);

                $sClass = '';
                if($r['cmt_rated'] == -1 || $r['cmt_rate'] < $this->_aSystem['viewing_threshold']) {
                    $sRet .= '<li id="cmt' . $r['cmt_id'] . '-hidden" class="cmt-replacement">';
                    $sRet .= _t('_hidden_comment', getNickName($r['cmt_author_id'])) . ' ' . ($r['cmt_replies'] > 0 ? _t('_Show N replies', $r['cmt_replies']) . '. ' : '') . '<a href="javascript:void(0)" onclick="javascript:' . $this->_sJsObjName . '.showReplacement(' . $r['cmt_id'] . ')">' . _t('_show_replacement') . '</a>.';
                    if($this->isRatable())
                        $sRet .= $this->_getRateBox($r);
                    $sRet .= '</li>';

                    $sClass = ' cmt-hidden';
                }

                $isOwnComment = $r['cmt_author_id'] == $this->_getAuthorId();
                if ($isOwnComment)
                    $sClass .= ' cmt-mine';

                $sRet .= '<li id="cmt' . $r['cmt_id'] . '" class="cmt' . $sClass . '"><a id="' . $sAnchor . '" name="' . $sAnchor . '"></a>';

                $sRet .= '<div class="cmt-cont">';
                $sRet .= $this->_getAuthorIcon($r);

                $sRet .= '<table class="cmt-balloon bx-def-round-corners-with-border">';
                $sRet .= $this->_getCommentHeadBox($r);
                $sRet .= '<tr class="cmt-cont">' . $this->_getCommentBodyBox($r) . '</tr>';

                $sRet .= $this->_getActionsBox ($r, false);
                $sRet .= '</table>';

                if($this->isRatable())
                    $sRet .= $this->_getRateBox($r);
                $sRet .= '</div></li>';
            }
        }
        $sRet .= '</ul>';

        return $sRet;
    }

    /**
     * get one just posted comment
     *
     * @param  int    $iCmtId - comment id
     * @return string
     */
    function getComment($iCmtId, $sType = 'new')
    {
        $r = $this->getCommentRow ($iCmtId);

        $sAnchor = $this->_getAnchor($iCmtId);

		$sRet = $sClass = '';
        if($r['cmt_rated'] == -1 || $r['cmt_rate'] < $this->_aSystem['viewing_threshold']) {
            $sRet .= '<li id="cmt' . $r['cmt_id'] . '-hidden" class="cmt-replacement">';
            $sRet .= _t('_hidden_comment', $r['cmt_author_name']) . ' ' . ($r['cmt_replies'] > 0 ? _t('_Show N replies', $r['cmt_replies']) . '. ' : '') . '<a href="javascript:void(0)" onclick="javascript:' . $this->_sJsObjName . '.showReplacement(' . $r['cmt_id'] . ')">' . _t('_show_replacement') . '</a>.';
            if($this->isRatable())
                $sRet .= $this->_getRateBox($r);
            $sRet .= '</li>';

            $sClass = ' cmt-hidden';
        }

        $sRet .= '<li id="cmt' . $r['cmt_id'] . '" class="cmt cmt-mine cmt-just-posted' . $sClass . '"><a id="' . $sAnchor . '" name="' . $sAnchor . '"></a>';
        $sRet .= '<div class="cmt-cont">';

        if($this->isRatable())
            $sRet .= $this->_getRateBox($r);

        $sRet .= $this->_getAuthorIcon($r);
        $sRet .= '<table class="cmt-balloon bx-def-round-corners-with-border">';
        $sRet .= $this->_getCommentHeadBox($r);
        $sRet .= '<tr class="cmt-cont">' . $this->_getCommentBodyBox($r) . '</tr>';
           $sRet .= $this->_getActionsBox ($r, true);
        $sRet .= '</table>';
        return $sRet;
    }
    function getPaginate($iStart = -1, $iPerPage = -1)
    {
        return $this->_oPaginate->getPaginate($iStart, $iPerPage);
    }
    function getForm($sType, $iParentId)
    {
        return $this->_getPostReplyBox($sType, $iParentId);
    }
    function getActions($iCmtId, $sType = 'reply')
    {
        $aParams = array(
            'cmt_id' => $iCmtId,
            'cmt_replies' => $this->_oQuery->getObjectCommentsCount($this->getId(), 0),
            'cmt_type' => $sType
        );

        $sRet = '<div class="cmt-replies">';
        if($aParams['cmt_replies'])
            $sRet .= $this->_getRepliesBox($aParams);
        $sRet .= '</div>';

        $sRet .= '<div class="cmt-post-reply-to">';
        if($this->isPostReplyAllowed())
            $sRet .= $this->_getPostReplyBoxTo($aParams);
        $sRet .= '</div>';

        $sRet .= '<div class="clear_both">&nbsp;</div>';
        return $sRet;
    }

    /**
     * Get comments css file string
     *
     * @return string
     */
    function getExtraCss ()
    {
        $GLOBALS['oSysTemplate']->addCss(array('cmts.css', 'cmts_phone.css'));
    }

    /**
     * Get comments js file string
     *
     * @return string
     */
    function getExtraJs ()
    {
        $GLOBALS['oSysTemplate']->addJs('BxDolCmts.js');
        $GLOBALS['oSysTemplate']->addJsTranslation(array(
        	'_Error occured',
        	'_Are_you_sure'
        ));
    }

    /**
     * Get initialization section of comments box
     *
     * @return string
     */
    function getCmtsInit ()
    {
    	$this->getExtraJs();
        $this->getExtraCss();

        $aParams = array(
        	'sObjName' => $this->_sJsObjName,
        	'sBaseUrl' => BX_DOL_URL_ROOT,
        	'sSystem' => $this->getSystemName(),
        	'sSystemTable' => $this->_aSystem['table_cmts'],
        	'iAuthorId' => $this->_getAuthorId(),
        	'iObjId' => $this->getId(),
        	'sOrder' => $this->getOrder(),
        	'sDefaultErrMsg' => _t('_Error occured'),
        	'sConfirmMsg' => _t('_Are_you_sure'),
        	'sAnimationEffect' => $this->_aSystem['animation_effect'],
        	'sAnimationSpeed' => $this->_aSystem['animation_speed'],
        	'isEditAllowed' => $this->isEditAllowed() || $this->isEditAllowedAll() ? 1 : 0,
        	'isRemoveAllowed' => $this->isRemoveAllowed() || $this->isRemoveAllowedAll() ? 1 : 0,
        	'iAutoHideRootPostForm' => $this->iAutoHideRootPostForm,
        	'iGlobAllowHtml' => $this->iGlobAllowHtml == 1 ? 1 : 0,
        	'iSecsToEdit' => (int)$this->getAllowedEditTime(),
        	'oCmtElements' => $this->_aCmtElements
        );
        return $GLOBALS['oSysTemplate']->_wrapInTagJsCode("var " . $this->_sJsObjName . " = new BxDolCmts(" . json_encode($aParams) . ");");
    }

    /**
     * private functions
     */
    function _getCommentHeadBox (&$a)
    {
        if ($a['cmt_author_id'] && $a['cmt_author_name'])
            $sAuthor = '<a href="' . getProfileLink($a['cmt_author_id']) . '" class="cmt-author">' . getNickName($a['cmt_author_id']) . '</a>';
        else
            $sAuthor = _t('_Anonymous');

        $sRet = '<tr class="cmt-head bx-def-font-small"><td class="' . $this->_sStylePrefix . '-head-l">&nbsp;</td><td class="' . $this->_sStylePrefix . '-head-m">' . $sAuthor . ':</td><td class="' . $this->_sStylePrefix . '-head-r">&nbsp;</td></tr>';

        return $sRet;
    }

    function _getCommentBodyBox(&$a)
    {
        return '
                <td class="' . $this->_sStylePrefix . '-cont-l">&nbsp;</td>
                <td class="' . $this->_sStylePrefix . '-cont-m">' .
                    ($this->_aSystem['is_mood'] ? '<div class="cmt-mood">' . _t($this->_aMoodText[$a['cmt_mood']]) . '</div>' : '') .
                    '<div class="cmt-body">' . $this->_prepareTextForOutput($a['cmt_text']) . '</div>
                </td>
                <td class="' . $this->_sStylePrefix . '-cont-r">&nbsp;</td>';
    }

    function _getRateBox(&$a)
    {
        $sClass = '';
        if ($a['cmt_rated'] || $a['cmt_rate'] < $this->_aSystem['viewing_threshold'])
            $sClass = ' cmt-rate-disabled';

        return '
            <div class="cmt-rate'.$sClass.'">
                <div class="cmt-points">'._t( (1 == $a['cmt_rate'] || -1 == $a['cmt_rate'])  ? '_N point' : '_N points', $a['cmt_rate']).'</div>
                <div class="cmt-buttons"><a title="'._t('_Thumb Up').'" href="javascript:void(0)" onclick="' . $this->_sJsObjName . '.cmtRate(this);" id="cmt-pos-'.$a['cmt_id'].'" class="cmt-pos"><i class="sys-icon plus-circle"></i></a><a title="'._t('_Thumb Down').'" href="javascript:void(0)" onclick="' . $this->_sJsObjName . '.cmtRate(this);" id="cmt-neg-'.$a['cmt_id'].'" class="cmt-neg"><i class="sys-icon minus-circle"></i></a></div>
                <div class="clear_both">&nbsp;</div>
            </div>';
    }

    function _getActionsBox(&$a, $isJustPosted)
    {
        $n = $this->getAllowedEditTime();
        $isEditAllowedPermanently = ($a['cmt_author_id'] == $this->_getAuthorId() && $this->isEditAllowed()) || $this->isEditAllowedAll();
        $isRemoveAllowedPermanently = ($a['cmt_author_id'] == $this->_getAuthorId() && $this->isRemoveAllowed()) || $this->isRemoveAllowedAll();

        $sAgo = $a['cmt_ago'];

        $sBaseUrl = $this->getBaseUrl();
        if(!empty($sBaseUrl))
        	$sAgo = '<a href="' . $sBaseUrl . '#' . $this->_getAnchor($a['cmt_id']) . '">' . $sAgo . '</a>';

        $sRet = '<tr id="cmt-jp-'.$a['cmt_id'].'" class="cmt-foot bx-def-font-small"><td class="' . $this->_sStylePrefix . '-cont-l">&nbsp;</td><td class="' . $this->_sStylePrefix . '-cont-m">';
        $sRet .= '<span class="cmt-posted-ago">' . $sAgo . '</span>';

        if($this->_aSystem['is_mood'])
            $sRet .= '<span class="sys-bullet"></span><span class="cmt-mood-text">' . _t($this->_aMoodText[$a['cmt_mood']]) . '</span>';

        if($isRemoveAllowedPermanently)
            $sRet .= '<span class="sys-bullet"></span><a class="cmt-comment-manage-delete" title="' . _t('_Delete') . '" href="javascript:void(0)" onclick="' . $this->_sJsObjName . '.cmtRemove(this, \'' . $a['cmt_id'] . '\'); return false;">'._t('_Delete').'</a>';

        if((($isJustPosted && $n) || $isEditAllowedPermanently) && strpos($a['cmt_text'], 'video_comments') === false)
            $sRet .= '<span class="sys-bullet"></span><a class="cmt-comment-manage-edit" title="'._t('_Edit').'" href="javascript:void(0)" onclick="' . $this->_sJsObjName . '.cmtEdit(this, \'' . $a['cmt_id'] . '\'); return false;">'._t('_Edit').'</a>';

        if($a['cmt_replies'])
            $sRet .= '<span class="sys-bullet"></span>' . $this->_getRepliesBox($a);

        if(!$isJustPosted && $this->isPostReplyAllowed())
            $sRet .= '<span class="sys-bullet"></span>' . $this->_getPostReplyBoxTo($a);

        if ($isJustPosted && $n && !$isEditAllowedPermanently)
            $sRet .= '<span class="sys-bullet"></span>' . _t('_edit_available_for_N_seconds', $n);

        $sRet .= '</td><td class="' . $this->_sStylePrefix . '-cont-r">&nbsp;</td></tr>';
        return $sRet;
    }
    function _getRepliesBox(&$a)
    {
    	$bComments = isset($a['cmt_type']) && $a['cmt_type'] == 'comment';
    	$sClassPrefix = $bComments ? 'cmt-comments' : 'cmt-replies';

    	$sCmtReplies = '<span class="' . $sClassPrefix . '-count">' . $a['cmt_replies'] . '</span>';

        $sContentShow = _t(($bComments ? '_Show N comments' : '_Show N replies'), $sCmtReplies);
        $sContentHide = _t(($bComments ? '_Hide N comments' : '_Hide N replies'), $sCmtReplies);

        $sRet = '';
        $sRet .= '<a class="' . $sClassPrefix . ' ' . $sClassPrefix . '-show" href="javascript:void(0)" onclick="' . $this->_sJsObjName . '.toggleCmts(this, \'' . $a['cmt_id'] . '\'); return false;">' . $sContentShow . '</a>';
        $sRet .= '<a class="' . $sClassPrefix . ' ' . $sClassPrefix . '-hide" href="javascript:void(0)" onclick="' . $this->_sJsObjName . '.toggleCmts(this, \'' . $a['cmt_id'] . '\'); return false;">' . $sContentHide . '</a>';

        return $sRet;
    }
    function _getPostReplyBoxTo(&$a)
    {
        $sContent = _t(isset($a['cmt_type']) && $a['cmt_type'] == 'comment' ? '_Comment to this comment' : '_Reply to this comment');
        return '<a class="cmt-reply-toggle" href="javascript:void(0)" onclick="' . $this->_sJsObjName . '.toggleReply(this, \''.$a['cmt_id'].'\'); return false;">' . $sContent . '</a>';
    }
    function _getPostReplyBox($sType = 'comment', $iCmtParentId = 0)
    {
        if($sType == 'comment')
            $sSwitcher = '<a class="cmt-post-reply-text inactive" href="javascript:void(0)" onclick="javascript:' . $this->_sJsObjName . '.toggleType(this)"><i class="sys-icon pencil"></i>&#160;' . _t('_Add Your Comment') . '</a>' . (getSettingValue("video_comments", "status", "main") == "enabled" ? '<a class="cmt-post-reply-video" href="javascript:void(0)" onclick="javascript:' . $this->_sJsObjName . '.toggleType(this)"><i class="sys-icon film"></i>&#160;' . _t('_Record Your Comment') . '</a>' : '');
        else if($sType == 'reply')
            $sSwitcher = '<a class="cmt-post-reply-text inactive" href="javascript:void(0)" onclick="javascript:' . $this->_sJsObjName . '.toggleType(this)"><i class="sys-icon pencil"></i>&#160;' . _t('_Reply as text') . '</a>' . (getSettingValue("video_comments", "status", "main") == "enabled" ? '<a class="cmt-post-reply-video" href="javascript:void(0)" onclick="javascript:' . $this->_sJsObjName . '.toggleType(this)"><i class="sys-icon film"></i>&#160;' . _t('_Reply as video') . '</a>' : '');

        return '
                <div class="cmt-post-reply">
                    ' . $this->_getAuthorIcon(array('cmt_author_id' => $this->_getAuthorId())) . '
                    <table class="cmt-balloon bx-def-round-corners-with-border">
                        <tr class="cmt-head">
                            <td class="cmt-head-l">&nbsp;</td>
                            <td class="cmt-head-m">' . $sSwitcher . '<div class="clear_both"></div></td>
                            <td class="cmt-head-r">&nbsp;</td>
                        </tr>
                        <tr class="cmt-cont">
                            <td class="cmt-cont-l">&nbsp;</td>
                            <td class="cmt-cont-m">' . $this->_getFormBox('post', array('parent_id' => $iCmtParentId)) . '</td>
                            <td class="cmt-cont-r">&nbsp;</td>
                        </tr>
                        <tr class="cmt-foot">
                            <td class="cmt-foot-l">&nbsp;</td>
                            <td class="cmt-foot-m">&nbsp;</td>
                            <td class="cmt-foot-r">&nbsp;</td>
                        </tr>
                    </table>
                </div>';
    }
    function _getFormBox($sType, $aCmt = array())
    {
    	$iCmtId = !empty($aCmt['id']) ? (int)$aCmt['id'] : 0;
    	$iCmtParentId = !empty($aCmt['parent_id']) ? (int)$aCmt['parent_id'] : 0;
    	$sCmtText = !empty($aCmt['text']) ? $this->_prepareTextForEdit($aCmt['text']) : '';

    	$sTextareaId = $this->_sSystem . "_cmt_" . $sType . "_textarea_" . $this->_iId . "_";
    	switch($sType) {
    		case 'post':
    			$sFunction = "submitComment(this)";
    			$sTextareaId .= $iCmtParentId;
    			break;

    		case 'edit':
    			$sFunction = "updateComment(this, '" . $iCmtId . "')";
    			$sTextareaId .= $iCmtId;   				
        		break;
    	}

        if($this->_aSystem['is_mood'])
            $sMood = '
                    <div class="cmt-post-reply-mood">
                        <div class="cmt-post-mood-ctl"><input type="radio" name="CmtMood" value="1" id="' . $this->_sSystem . '-mood-positive" /></div>
                        <div class="cmt-post-mood-lbl"><label for="' . $this->_sSystem . '-mood-positive">' . _t('_Comment Positive') . '</label></div>
                        <div class="cmt-post-mood-ctl"><input type="radio" name="CmtMood" value="-1" id="' . $this->_sSystem . '-mood-negative" /></div>
                        <div class="cmt-post-mood-lbl"><label for="' . $this->_sSystem . '-mood-negative">' . _t('_Comment Negative') . '</label></div>
                        <div class="cmt-post-mood-ctl"><input type="radio" name="CmtMood" value="0" id="' . $this->_sSystem . '-mood-neutral" checked="checked" /></div>
                        <div class="cmt-post-mood-lbl"><label for="' . $this->_sSystem . '-mood-neutral">' . _t('_Comment Neutral') . '</label></div>
                        <div class="clear_both">&nbsp;</div>
                    </div>';

		$sContent = '
			<form name="cmt-post-reply" onsubmit="' . $this->_sJsObjName . '.' . $sFunction . '; return false;">
				<input type="hidden" name="CmtParent" value="' . $iCmtParentId . '" />
				<input type="hidden" name="CmtType" value="text" />
				<div class="cmt-post-reply-text">
					<textarea class="cmt-text-' . $sType . ' bx-def-round-corners-with-border" id="' . $sTextareaId . '" name="CmtText">' . $sCmtText . '</textarea>
				</div>
				<div class="cmt-post-reply-video">' . getApplicationContent('video_comments', 'recorder', array('user' => $this->_getAuthorId(), 'password' => $this->_getAuthorPassword(), 'extra' => implode('_', array($this->_sSystem . '-' . $this->getId(), $iCmtParentId))), true) . '</div>
				<div class="cmt-post-reply-post"><button class="bx-btn bx-btn-small" type="submit">' . _t('_Submit Comment') . '</button></div>
				' . $sMood . '
			</form>';

		if($this->iGlobAllowHtml == 1) { 
            bx_import('BxDolEditor');
            $oEditor = BxDolEditor::getObjectInstance();
            $sContent .= $oEditor ? $oEditor->attachEditor ('#' . $sTextareaId, BX_EDITOR_MINI, $this->bDynamic) : '';
        }

        return $sContent;
    }
    function _getAuthorIcon ($a)
    {
        global $oFunctions;
        if (!$a['cmt_author_id'] || !getProfileInfo($a['cmt_author_id'])) {
            if (!@include_once (BX_DIRECTORY_PATH_MODULES . 'boonex/avatar/include.php'))
                return '';
            return '<div class="thumbnail_block thumbnail_block_icon" style="float:none;"><div class="thumbnail_image"><img class="thumbnail_image_file bx-def-thumbnail bx-def-shadow bx-img-retina" src="' . $oFunctions->getSexPic('', 'small') . '" /></div></div>';
        } else {
            return $oFunctions->getMemberIcon($a['cmt_author_id']);
        }
    }
    function _getBrowse()
    {
        return $GLOBALS['oSysTemplate']->parseHtmlByName('cmts_top_controls.html', array(
            'js_object' => $this->_sJsObjName,
            'sorting' => $this->_oPaginate->getSorting(array('asc' => '_oldest first', 'desc' => '_newest first')),
            'expand_all' => _t('_expand all'),
            'pages' => $this->_oPaginate->getPages()
        ));
    }
}
