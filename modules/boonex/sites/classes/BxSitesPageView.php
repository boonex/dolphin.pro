<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigPageView');
bx_import('BxDolSubscription');

require_once('BxSitesCmts.php');

class BxSitesPageView extends BxDolTwigPageView
{
    var $_oSites;
    var $_aSite;
    var $_oTemplate;
    var $_oConfig;

    function __construct(&$oSites, $aSite)
    {
        parent::__construct('bx_sites_view', $oSites, $aSite);

        $this->_oSites = &$oSites;
        $this->_aSite = $aSite;

        $this->_oTemplate = $oSites->_oTemplate;
        $this->_oConfig = $oSites->_oConfig;
    }

    function getBlockCode_ViewActions()
    {
        global $oFunctions;

        if ($this->_oSites->iOwnerId || $this->_oSites->isAdmin()) {
        	$sCode = '';

            $aInfo = array(
                'iViewer' => $this->_oSites->iOwnerId,
                'ownerID' => (int)$this->_aSite['ownerid'],
                'ID' => (int)$this->_aSite['id'],
                'TitleEdit' => $this->_oSites->isAllowedEdit($this->_aSite) ? _t('_bx_sites_action_title_edit') : '',
                'TitleDelete' => $this->_oSites->isAllowedDelete($this->_aSite) ? _t('_bx_sites_action_title_delete') : '',
                'TitleShare' => $this->_oSites->isAllowedShare($this->_aSite) ? _t('_Share') : '',
                'AddToFeatured' => ($this->_oSites->isAllowedMarkAsFeatured($this->_aSite) && (int)$this->_aSite['allowView'] == BX_DOL_PG_ALL) ?
                                    ((int)$this->_aSite['featured'] == 1  ? _t('_bx_sites_action_remove_from_featured') : _t('_bx_sites_action_add_to_featured')) : ''
            );

            $oSubscription = BxDolSubscription::getInstance();
            $aButton = $oSubscription->getButton($this->_oSites->iOwnerId, 'bx_sites', '', $this->_aSite['id']);
			$sCode .= $oSubscription->getData();

            $aInfo['sbs_sites_title'] = $aButton['title'];
            $aInfo['sbs_sites_script'] = $aButton['script'];

            if (!$aInfo['TitleEdit'] && !$aInfo['TitleDelete'] && !$aInfo['TitleShare'] && !$aInfo['AddToFeatured'] && !$aInfo['sbs_sites_title'])
                return '';

            if ($aInfo['TitleShare']) {
                $sUrlSharePopup = BX_DOL_URL_ROOT . $this->_oSites->_oConfig->getBaseUri() . "share_popup/" . $this->_aSite['id'];
                $sCode .= <<<EOF
                    <script type="text/javascript">
                    function bx_site_show_share_popup ()
                    {
                        if (!$('#bx_sites_share_popup').length) {
                            $('<div id="bx_sites_share_popup" style="display: none;"></div>').prependTo('body');
                        }

                        $('#bx_sites_share_popup').load(
                            '{$sUrlSharePopup}',
                            function() {
                                $(this).dolPopup();
                            }
                        );
                    }
                    </script>
EOF;
            }

            $aInfo['repostCpt'] = $aInfo['repostScript'] = '';
	        if(BxDolRequest::serviceExists('wall', 'get_repost_js_click')) {
				$sCode .= BxDolService::call('wall', 'get_repost_js_script');

				$aInfo['repostCpt'] = _t('_Repost');
				$aInfo['repostScript'] = BxDolService::call('wall', 'get_repost_js_click', array($this->_oSites->iOwnerId, 'bx_sites', 'add', (int)$this->_aSite['id']));
			}

            $aCodeActions = $oFunctions->genObjectsActions($aInfo, 'bx_sites');
            if(empty($aCodeActions))
            	return '';

            return $sCode . $aCodeActions;
        }

        return '';
    }

    function getBlockCode_ViewInformation()
    {
        $sContent = $this->_oTemplate->blockInformation($this->_aSite);
        return array($sContent, array(), array(), false);
    }

    function getBlockCode_ViewImage()
    {
        $sSiteUrl = $this->_aSite['url'];

        $aFile = BxDolService::call('photos', 'get_photo_array', array($this->_aSite['photo'], 'file'), 'Search');
        $sImage = $aFile['no_image'] ? '' : $aFile['file'];

        // BEGIN STW INTEGRATION
        if (getParam('bx_sites_account_type') != 'No Automated Screenshots') {
            if ($sImage == '') {
                $aSTWOptions = array(
                );

                bx_sites_import('STW');
                $sThumbHTML = getThumbnailHTML($sSiteUrl, $aSTWOptions, false, false);
            }
        }
        // END STW INTEGRATION

        $sVote = '';

        if (strncasecmp($sSiteUrl, 'http://', 7) !== 0 && strncasecmp($sSiteUrl, 'https://', 8) !== 0)
            $sSiteUrl = 'http://' . $sSiteUrl;

        if ($this->_oConfig->isVotesAllowed() &&
            $this->_oSites->oPrivacy->check('rate',
            $this->_aSite['id'], $this->_oSites->iOwnerId))
        {
            bx_import('BxTemplVotingView');
            $oVotingView = new BxTemplVotingView('bx_sites', $this->_aSite['id']);

            if ($oVotingView->isEnabled())
                $sVote = $oVotingView->getBigVoting();
        }

        $sContent = $this->_oTemplate->parseHtmlByName('view_image.html', array(
            'title' => $this->_aSite['title'],
            'site_url' => $sSiteUrl,
            'site_url_view' => $this->_aSite['url'],
            // BEGIN STW INTEGRATION
            'bx_if:is_image' => array(
                'condition' => $sThumbHTML == false,
                'content' => array('image' => $sImage ? $sImage : $this->_oTemplate->getImageUrl('no-image-thumb.png'))
            ),
            'bx_if:is_thumbhtml' => array(
                'condition' => $sThumbHTML != '',
                'content' => array('thumbhtml' => $sThumbHTML)
            ),
            // END STW INTEGRATION
            'vote' => $sVote,
            'view_count' => $this->_aSite['views']
        ));

        return array($sContent, array(), array(), false);
    }

    function getBlockCode_ViewDescription()
    {
        $sContent = $this->_oTemplate->parseHtmlByName('view_description.html', array(
            'description' => $this->_aSite['description']
        ));

        return array($sContent, array(), array(), false);
    }

    function getBlockCode_ViewComments()
    {
        if ($this->_oConfig->isCommentsAllowed() && $this->_oSites->oPrivacy->check('comments', $this->_aSite['id'], $this->_oSites->iOwnerId)) {
        	$o = new BxSitesCmts('bx_sites', $this->_aSite['id']);

            if ($o->isEnabled())
                return $o->getCommentsFirst();
        }

        return '';
    }

}
