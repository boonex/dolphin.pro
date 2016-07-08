<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigPageView');

class BxStorePageView extends BxDolTwigPageView
{
    function __construct(&$oMain, &$aDataEntry)
    {
        parent::__construct('bx_store_view', $oMain, $aDataEntry);
    }

    function getBlockCode_Info()
    {
        $sContent = $this->_blockInfo ($this->aDataEntry, $this->_oTemplate->blockFields($this->aDataEntry));
        return array($sContent, array(), array(), false);
    }

    function getBlockCode_Desc()
    {
        $sContent = $this->_oTemplate->blockDesc ($this->aDataEntry);
        return array($sContent, array(), array(), false);
    }

    function getBlockCode_Photo()
    {
        return $this->_blockPhoto ($this->_oDb->getMediaIds($this->aDataEntry['id'], 'images'), $this->aDataEntry['author_id']);
    }

    function getBlockCode_Video()
    {
        return $this->_blockVideo ($this->_oDb->getMediaIds($this->aDataEntry['id'], 'videos'), $this->aDataEntry['author_id']);
    }

    function getBlockCode_Files()
    {
        return $this->_oTemplate->blockFiles ($this->aDataEntry);
    }

    function getBlockCode_Rate()
    {
        bx_store_import('Voting');
        $o = new BxStoreVoting ('bx_store', (int)$this->aDataEntry['id']);
        if (!$o->isEnabled())
            return '';

        $sContent = $o->getBigVoting ($this->_oMain->isAllowedRate($this->aDataEntry));
        return array($sContent, array(), array(), false);
    }

    function getBlockCode_Comments()
    {
        bx_store_import('Cmts');
        $o = new BxStoreCmts ('bx_store', (int)$this->aDataEntry['id']);
        if (!$o->isEnabled()) return '';
        return $o->getCommentsFirst ();
    }

    function getBlockCode_Actions()
    {
        global $oFunctions;

        if ($this->_oMain->_iProfileId || $this->_oMain->isAdmin()) {
        	$sCode = '';

            $oSubscription = BxDolSubscription::getInstance();
            $aSubscribeButton = $oSubscription->getButton($this->_oMain->_iProfileId, 'bx_store', '', (int)$this->aDataEntry['id']);
            $sCode .= $oSubscription->getData();

            $aInfo = array (
                'BaseUri' => $this->_oMain->_oConfig->getBaseUri(),
                'iViewer' => $this->_oMain->_iProfileId,
                'ownerID' => (int)$this->aDataEntry['author_id'],
                'ID' => (int)$this->aDataEntry['id'],
                'URI' => (int)$this->aDataEntry['uri'],
                'ScriptSubscribe' => $aSubscribeButton['script'],
                'TitleSubscribe' => $aSubscribeButton['title'],
                'TitleEdit' => $this->_oMain->isAllowedEdit($this->aDataEntry) ? _t('_bx_store_action_title_edit') : '',
                'TitleDelete' => $this->_oMain->isAllowedDelete($this->aDataEntry) ? _t('_bx_store_action_title_delete') : '',
                'TitleShare' => $this->_oMain->isAllowedShare($this->aDataEntry) ? _t('_bx_store_action_title_share') : '',
                'TitleBroadcast' => $this->_oMain->isAllowedBroadcast($this->aDataEntry) ? _t('_bx_store_action_title_broadcast') : '',
                'AddToFeatured' => $this->_oMain->isAllowedMarkAsFeatured($this->aDataEntry) ? ($this->aDataEntry['featured'] ? _t('_bx_store_action_remove_from_featured') : _t('_bx_store_action_add_to_featured')) : '',
                'TitleActivate' => method_exists($this->_oMain, 'isAllowedActivate') && $this->_oMain->isAllowedActivate($this->aDataEntry) ? _t('_bx_store_admin_activate') : '',
            );

            $aInfo['repostCpt'] = $aInfo['repostScript'] = '';
        	if(BxDolRequest::serviceExists('wall', 'get_repost_js_click')) {
				$sCode .= BxDolService::call('wall', 'get_repost_js_script');

				$aInfo['repostCpt'] = _t('_Repost');
				$aInfo['repostScript'] = BxDolService::call('wall', 'get_repost_js_click', array($this->_oMain->_iProfileId, 'bx_store', 'add', (int)$this->aDataEntry['id']));
			}

			$sCodeActions = $oFunctions->genObjectsActions($aInfo, 'bx_store');
            if(empty($sCodeActions))
                return '';

            return $sCode . $sCodeActions;
        }

        return '';
    }

}
