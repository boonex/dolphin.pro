<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigPageView');

class BxEventsPageView extends BxDolTwigPageView
{
    function __construct(&$oEventsMain, &$aEvent)
    {
        parent::__construct('bx_events_view', $oEventsMain, $aEvent);
    }

    function getBlockCode_Info()
    {
        return array($this->_oTemplate->blockInfo ($this->aDataEntry));
    }

    function getBlockCode_Desc()
    {
        return array($this->_oTemplate->blockDesc ($this->aDataEntry));
    }

    function getBlockCode_Photos()
    {
        return $this->_blockPhoto ($this->_oDb->getMediaIds($this->aDataEntry['ID'], 'images'), $this->aDataEntry['ResponsibleID']);
    }

    function getBlockCode_Videos()
    {
        return $this->_blockVideo ($this->_oDb->getMediaIds($this->aDataEntry['ID'], 'videos'), $this->aDataEntry['ResponsibleID']);
    }

    function getBlockCode_Sounds()
    {
        return $this->_blockSound ($this->_oDb->getMediaIds($this->aDataEntry['ID'], 'sounds'), $this->aDataEntry['ResponsibleID']);
    }

    function getBlockCode_Files()
    {
        return $this->_blockFiles ($this->_oDb->getMediaIds($this->aDataEntry['ID'], 'files'), $this->aDataEntry['ResponsibleID']);
    }

    function getBlockCode_Rate()
    {
        bx_events_import('Voting');
        $o = new BxEventsVoting ('bx_events', (int)$this->aDataEntry['ID']);
        if (!$o->isEnabled()) return '';
        return array($o->getBigVoting ($this->_oMain->isAllowedRate($this->aDataEntry)));
    }

    function getBlockCode_Comments()
    {
        bx_events_import('Cmts');
        $o = new BxEventsCmts ('bx_events', (int)$this->aDataEntry['ID']);
        if (!$o->isEnabled())
            return '';
        return $o->getCommentsFirst ();
    }

    function getBlockCode_Actions()
    {
        global $oFunctions;

        if ($this->_oMain->_iProfileId || $this->_oMain->isAdmin()) {
			$sCode = '';

            $oSubscription = BxDolSubscription::getInstance();
            $aSubscribeButton = $oSubscription->getButton($this->_oMain->_iProfileId, 'bx_events', '', (int)$this->aDataEntry['ID']);
            $sCode .= $oSubscription->getData();

            $isFan = $this->_oDb->isFan((int)$this->aDataEntry['ID'], $this->_oMain->_iProfileId, 0) || $this->_oDb->isFan((int)$this->aDataEntry['ID'], $this->_oMain->_iProfileId, 1);

            $this->aInfo = array (
                'BaseUri' => $this->_oMain->_oConfig->getBaseUri(),
                'iViewer' => $this->_oMain->_iProfileId,
                'ownerID' => (int)$this->aDataEntry['ResponsibleID'],
                'ID' => (int)$this->aDataEntry['ID'],
                'URI' => $this->aDataEntry['EntryUri'],
                'ScriptSubscribe' => $aSubscribeButton['script'],
                'TitleSubscribe' => $aSubscribeButton['title'],
                'TitleEdit' => $this->_oMain->isAllowedEdit($this->aDataEntry) ? _t('_bx_events_action_title_edit') : '',
                'TitleDelete' => $this->_oMain->isAllowedDelete($this->aDataEntry) ? _t('_bx_events_action_title_delete') : '',
                'TitleJoin' => $this->_oMain->isAllowedJoin($this->aDataEntry) ? ($isFan ? _t('_bx_events_action_title_leave') : _t('_bx_events_action_title_join')) : '',
                'IconJoin' => $isFan ? 'sign-out' : 'sign-in',
                'TitleInvite' => $this->_oMain->isAllowedSendInvitation($this->aDataEntry) ? _t('_bx_events_action_title_invite') : '',
                'TitleShare' => $this->_oMain->isAllowedShare($this->aDataEntry) ? _t('_bx_events_action_title_share') : '',
                'TitleBroadcast' => $this->_oMain->isAllowedBroadcast($this->aDataEntry) ? _t('_bx_events_action_title_broadcast') : '',
                'AddToFeatured' => $this->_oMain->isAllowedMarkAsFeatured($this->aDataEntry) ? ($this->aDataEntry['Featured'] ? _t('_bx_events_action_remove_from_featured') : _t('_bx_events_action_add_to_featured')) : '',
                'TitleManageFans' => $this->_oMain->isAllowedManageFans($this->aDataEntry) ? _t('_bx_events_action_manage_fans') : '',
                'TitleUploadPhotos' => $this->_oMain->isAllowedUploadPhotos($this->aDataEntry) ? _t('_bx_events_action_upload_photos') : '',
                'TitleUploadVideos' => $this->_oMain->isAllowedUploadVideos($this->aDataEntry) ? _t('_bx_events_action_upload_videos') : '',
                'TitleUploadSounds' => $this->_oMain->isAllowedUploadSounds($this->aDataEntry) ? _t('_bx_events_action_upload_sounds') : '',
                'TitleUploadFiles' => $this->_oMain->isAllowedUploadFiles($this->aDataEntry) ? _t('_bx_events_action_upload_files') : '',
                'TitleActivate' => method_exists($this->_oMain, 'isAllowedActivate') && $this->_oMain->isAllowedActivate($this->aDataEntry) ? _t('_bx_events_admin_activate') : '',
            );

            $this->aInfo['repostCpt'] = $this->aInfo['repostScript'] = '';
			if(BxDolRequest::serviceExists('wall', 'get_repost_js_click')) {
				$sCode .= BxDolService::call('wall', 'get_repost_js_script');

				$this->aInfo['repostCpt'] = _t('_Repost');
				$this->aInfo['repostScript'] = BxDolService::call('wall', 'get_repost_js_click', array($this->_oMain->_iProfileId, 'bx_events', 'add', (int)$this->aDataEntry['ID']));
			}

            $sCodeActions = $oFunctions->genObjectsActions($this->aInfo, 'bx_events');
            if(empty($sCodeActions))
                return '';

            return $sCode . $sCodeActions;
        }

        return '';
    }

    function getBlockCode_Participants()
    {
        return parent::_blockFans ($this->_oDb->getParam('bx_events_perpage_participants'), 'isAllowedViewParticipants', 'getFans');
    }

    function getBlockCode_ParticipantsUnconfirmed()
    {
        return parent::_blockFansUnconfirmed (BX_EVENTS_MAX_FANS);
    }

    function getCode()
    {
        $this->_oMain->_processFansActions ($this->aDataEntry, BX_EVENTS_MAX_FANS);

        return parent::getCode();
    }
}
