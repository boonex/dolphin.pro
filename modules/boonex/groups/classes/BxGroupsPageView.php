<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigPageView');

class BxGroupsPageView extends BxDolTwigPageView
{
    function __construct(&$oMain, &$aDataEntry)
    {
        parent::__construct('bx_groups_view', $oMain, $aDataEntry);
    }

    function getBlockCode_Info()
    {
        return array($this->_blockInfo ($this->aDataEntry, $this->_oTemplate->blockFields($this->aDataEntry), $this->_oMain->_formatLocation($this->aDataEntry, false, true)));
    }

    function getBlockCode_Desc()
    {
        return array($this->_oTemplate->blockDesc ($this->aDataEntry));
    }

    function getBlockCode_Photo()
    {
        return $this->_blockPhoto ($this->_oDb->getMediaIds($this->aDataEntry['id'], 'images'), $this->aDataEntry['author_id']);
    }

    function getBlockCode_Video()
    {
        return $this->_blockVideo ($this->_oDb->getMediaIds($this->aDataEntry['id'], 'videos'), $this->aDataEntry['author_id']);
    }

    function getBlockCode_Sound()
    {
        return $this->_blockSound ($this->_oDb->getMediaIds($this->aDataEntry['id'], 'sounds'), $this->aDataEntry['author_id']);
    }

    function getBlockCode_Files()
    {
        return $this->_blockFiles ($this->_oDb->getMediaIds($this->aDataEntry['id'], 'files'), $this->aDataEntry['author_id']);
    }

    function getBlockCode_Rate()
    {
        bx_groups_import('Voting');
        $o = new BxGroupsVoting ('bx_groups', (int)$this->aDataEntry['id']);
        if (!$o->isEnabled()) return '';
        return array($o->getBigVoting ($this->_oMain->isAllowedRate($this->aDataEntry)));
    }

    function getBlockCode_Comments()
    {
        bx_groups_import('Cmts');
        $o = new BxGroupsCmts ('bx_groups', (int)$this->aDataEntry['id']);
        if (!$o->isEnabled()) return '';
        return $o->getCommentsFirst ();
    }

    function getBlockCode_Actions()
    {
        global $oFunctions;

        if ($this->_oMain->_iProfileId || $this->_oMain->isAdmin()) {
        	$sCode = '';

            $oSubscription = BxDolSubscription::getInstance();
            $aSubscribeButton = $oSubscription->getButton($this->_oMain->_iProfileId, 'bx_groups', '', (int)$this->aDataEntry['id']);
            $sCode .= $oSubscription->getData();

            $isFan = $this->_oDb->isFan((int)$this->aDataEntry['id'], $this->_oMain->_iProfileId, 0) || $this->_oDb->isFan((int)$this->aDataEntry['id'], $this->_oMain->_iProfileId, 1);

            $aInfo = array (
                'BaseUri' => $this->_oMain->_oConfig->getBaseUri(),
                'iViewer' => $this->_oMain->_iProfileId,
                'ownerID' => (int)$this->aDataEntry['author_id'],
                'ID' => (int)$this->aDataEntry['id'],
                'URI' => $this->aDataEntry['uri'],
                'ScriptSubscribe' => $aSubscribeButton['script'],
                'TitleSubscribe' => $aSubscribeButton['title'],
                'TitleEdit' => $this->_oMain->isAllowedEdit($this->aDataEntry) ? _t('_bx_groups_action_title_edit') : '',
                'TitleDelete' => $this->_oMain->isAllowedDelete($this->aDataEntry) ? _t('_bx_groups_action_title_delete') : '',
                'TitleJoin' => $this->_oMain->isAllowedJoin($this->aDataEntry) ? ($isFan ? _t('_bx_groups_action_title_leave') : _t('_bx_groups_action_title_join')) : '',
                'IconJoin' => $isFan ? 'sign-out' : 'sign-in',
                'TitleInvite' => $this->_oMain->isAllowedSendInvitation($this->aDataEntry) ? _t('_bx_groups_action_title_invite') : '',
                'TitleShare' => $this->_oMain->isAllowedShare($this->aDataEntry) ? _t('_bx_groups_action_title_share') : '',
                'TitleBroadcast' => $this->_oMain->isAllowedBroadcast($this->aDataEntry) ? _t('_bx_groups_action_title_broadcast') : '',
                'AddToFeatured' => $this->_oMain->isAllowedMarkAsFeatured($this->aDataEntry) ? ($this->aDataEntry['featured'] ? _t('_bx_groups_action_remove_from_featured') : _t('_bx_groups_action_add_to_featured')) : '',
                'TitleManageFans' => $this->_oMain->isAllowedManageFans($this->aDataEntry) ? _t('_bx_groups_action_manage_fans') : '',
                'TitleUploadPhotos' => $this->_oMain->isAllowedUploadPhotos($this->aDataEntry) ? _t('_bx_groups_action_upload_photos') : '',
                'TitleUploadVideos' => $this->_oMain->isAllowedUploadVideos($this->aDataEntry) ? _t('_bx_groups_action_upload_videos') : '',
                'TitleUploadSounds' => $this->_oMain->isAllowedUploadSounds($this->aDataEntry) ? _t('_bx_groups_action_upload_sounds') : '',
                'TitleUploadFiles' => $this->_oMain->isAllowedUploadFiles($this->aDataEntry) ? _t('_bx_groups_action_upload_files') : '',
                'TitleActivate' => method_exists($this->_oMain, 'isAllowedActivate') && $this->_oMain->isAllowedActivate($this->aDataEntry) ? _t('_bx_groups_admin_activate') : '',
            );

            $aInfo['repostCpt'] = $aInfo['repostScript'] = '';
	        if(BxDolRequest::serviceExists('wall', 'get_repost_js_click')) {
				$sCode .= BxDolService::call('wall', 'get_repost_js_script');

				$aInfo['repostCpt'] = _t('_Repost');
				$aInfo['repostScript'] = BxDolService::call('wall', 'get_repost_js_click', array($this->_oMain->_iProfileId, 'bx_groups', 'add', (int)$this->aDataEntry['id']));
			}

			$sCodeActions = $oFunctions->genObjectsActions($aInfo, 'bx_groups');
			if(empty($sCodeActions))
                return '';

            return $sCode . $sCodeActions;
        }

        return '';
    }

    function getBlockCode_Fans()
    {
        return parent::_blockFans ($this->_oDb->getParam('bx_groups_perpage_view_fans'), 'isAllowedViewFans', 'getFans');
    }

    function getBlockCode_FansUnconfirmed()
    {
        return parent::_blockFansUnconfirmed (BX_GROUPS_MAX_FANS);
    }

    function getCode()
    {
        $this->_oMain->_processFansActions ($this->aDataEntry, BX_GROUPS_MAX_FANS);

        return parent::getCode();
    }

}
