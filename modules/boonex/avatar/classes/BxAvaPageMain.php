<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxAvaPageMain extends BxDolPageView
{
    var $_oMain;
    var $_oTemplate;
    var $_oConfig;
    var $_oDb;

    function __construct(&$oMain)
    {
        $this->_oMain = &$oMain;
        $this->_oTemplate = $oMain->_oTemplate;
        $this->_oConfig = $oMain->_oConfig;
        $this->_oDb = $oMain->_oDb;

        parent::__construct('bx_avatar_main');

        $GLOBALS['oTopMenu']->setCurrentProfileID($this->_oMain->_iProfileId);
    }

    function getBlockCode_Tight()
    {
        $aMyAvatars = array ();
        $aVars = array (
            'my_avatars' => $this->_oMain->serviceGetMyAvatars ($this->_oMain->_iProfileId),
            'bx_if:is_site_avatars_enabled' => array (
                'condition' => 'on' == getParam('bx_avatar_site_avatars'),
                'content' => array (
                    'site_avatars' => getParam('bx_avatar_site_avatars') ? $this->_oMain->serviceGetSiteAvatars (0) : _t('_Empty'),
                ),
            ),
        );
        return array($this->_oTemplate->parseHtmlByName('block_tight', $aVars), array(), array(), false);
    }

    function getBlockCode_Wide()
    {
        $sUploadErr = '';

        if (isset($_FILES['image'])) {
            $sUploadErr = $this->_oMain->_uploadImage () ? '' : _t('_bx_ava_upload_error');
            if (!$sUploadErr)
                send_headers_page_changed();
        }

        $aVars = array (
            'avatar' => $GLOBALS['oFunctions']->getMemberThumbnail ($this->_oMain->_iProfileId),
            'bx_if:allow_upload' => array (
                'condition' => $this->_oMain->isAllowedAdd(),
                'content' => array (
                    'action' => $this->_oConfig->getBaseUri(),
                    'upload_error' => $sUploadErr,
                ),
            ),
            'bx_if:allow_crop' => array (
                'condition' => $this->_oMain->isAllowedAdd(),
                'content' => array (
                    'crop_tool' => $this->_oMain->serviceCropTool (array (
                        'dir_image' => BX_AVA_DIR_TMP . $this->_oMain->_iProfileId . BX_AVA_EXT,
                        'url_image' => BX_AVA_URL_TMP . $this->_oMain->_iProfileId . BX_AVA_EXT . '?' . time(),
                    )),
                ),
            ),
            'bx_if:display_premoderation_notice' => array (
                'condition' => getParam('autoApproval_ifProfile') != 'on',
                'content' => array (),
            ),
        );

        return array($this->_oTemplate->parseHtmlByName('block_wide', $aVars), array(), array(), false);
    }
}
