<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolPageView');

/**
 * Base entry view class for modules like events/groups/store
 */
class BxDolTwigPageView extends BxDolPageView
{
    var $_oTemplate;
    var $_oMain;
    var $_oDb;
    var $_oConfig;
    var $aDataEntry;

    function __construct($sName, &$oMain, &$aDataEntry)
    {
        parent::__construct($sName);
        $this->_oMain = $oMain;
        $this->_oTemplate = $oMain->_oTemplate;
        $this->_oDb = $oMain->_oDb;
        $this->_oConfig = $oMain->_oConfig;
        $this->aDataEntry = &$aDataEntry;
    }

    function getBlockCode_SocialSharing()
    {
    	if(!$this->_oMain->isAllowedShare($this->aDataEntry))
    		return '';

        $sUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $this->aDataEntry[$this->_oDb->_sFieldUri];
        $sTitle = $this->aDataEntry[$this->_oDb->_sFieldTitle];

        $aCustomParams = false;
        if ($this->aDataEntry[$this->_oDb->_sFieldThumb]) {
            $a = array('ID' => $this->aDataEntry[$this->_oDb->_sFieldAuthorId], 'Avatar' => $this->aDataEntry[$this->_oDb->_sFieldThumb]);
            $aImage = BxDolService::call('photos', 'get_image', array($a, 'browse'), 'Search');
            $sImgUrl = $aImage['no_image'] ? '' : $aImage['file'];
            if ($sImgUrl) {
                $aCustomParams = array (
                    'img_url' => $sImgUrl,
                    'img_url_encoded' => rawurlencode($sImgUrl),
                );
            }
        }

        bx_import('BxTemplSocialSharing');
        $sCode = BxTemplSocialSharing::getInstance()->getCode($sUrl, $sTitle, $aCustomParams);
        return array($sCode, array(), array(), false);
    }

    function getBlockCode_ForumFeed()
    {
    	if (!$this->_oMain->isAllowedReadForum($this->aDataEntry))
            return '';

        $oModuleDb = new BxDolModuleDb(); 
        if (!$oModuleDb->getModuleByUri('forum'))
            return '';

        $sRssId = 'forum|' . $this->_oConfig->getUri() . '|' . rawurlencode($this->aDataEntry[$this->_oDb->_sFieldUri]);
        return '<div class="RSSAggrCont" rssid="' . $sRssId . '" rssnum="8" member="' . getLoggedId() . '">' . $GLOBALS['oFunctions']->loadingBoxInline() . '</div>';
    }

    function _blockInfo ($aData, $sFields = '', $sLocation = '')
    {
        $aAuthor = getProfileInfo($aData['author_id']);

        $aVars = array (
            'date' => getLocaleDate($aData['created'], BX_DOL_LOCALE_DATE_SHORT),
            'date_ago' => defineTimeInterval($aData['created'], false),
            'cats' => $this->_oTemplate->parseCategories($aData['categories']),
            'tags' => $this->_oTemplate->parseTags($aData['tags']),
            'fields' => $sFields,
            'author_unit' => $GLOBALS['oFunctions']->getMemberThumbnail($aAuthor['ID'], 'none', true),
            'location' => $sLocation,
        );
        return $this->_oTemplate->parseHtmlByName('entry_view_block_info', $aVars);
    }

    function _blockPhoto (&$aReadyMedia, $iAuthorId, $sPrefix = false)
    {
        if (!$aReadyMedia)
            return '';

        $aImages = array ();

        foreach ($aReadyMedia as $iMediaId) {

            $a = array ('ID' => $iAuthorId, 'Avatar' => $iMediaId);

            $aImageFile = BxDolService::call('photos', 'get_image', array($a, 'file'), 'Search');
            if ($aImageFile['no_image'])
                continue;

            $aImageIcon = BxDolService::call('photos', 'get_image', array($a, 'icon'), 'Search');
            if ($aImageIcon['no_image'])
                continue;

            $aImages[] = array (
                'icon_url' => $aImageIcon['file'],
                'image_url' => $aImageFile['file'],
                'title' => $aImageIcon['title'],
            );
        }

        if (!$aImages)
            return '';

        return $GLOBALS['oFunctions']->genGalleryImages($aImages);
    }

    function _blockVideo ($aReadyMedia, $iAuthorId, $sPrefix = false)
    {
        if (!$aReadyMedia)
            return '';

        $aVars = array (
            'title' => false,
            'prefix' => $sPrefix ? $sPrefix : 'id'.time().'_'.rand(1, 999999),
            'default_height' => getSettingValue('video', 'player_height'),
            'bx_repeat:videos' => array (),
            'bx_repeat:icons' => array (),
        );

        foreach ($aReadyMedia as $iMediaId) {

            $a = BxDolService::call('videos', 'get_video_array', array($iMediaId), 'Search');
            $a['ID'] = $iMediaId;

            $aVars['bx_repeat:videos'][] = array (
                'style' => false === $aVars['title'] ? '' : 'display:none;',
                'id' => $iMediaId,
                'video' => BxDolService::call('videos', 'get_video_concept', array($a), 'Search'),
            );
            $aVars['bx_repeat:icons'][] = array (
                'id' => $iMediaId,
                'icon_url' => $a['file'],
                'title' => $a['title'],
            );
            if (false === $aVars['title'])
                $aVars['title'] = $a['title'];
        }

        if (!$aVars['bx_repeat:icons'])
            return '';

        return $this->_oTemplate->parseHtmlByName('entry_view_block_videos', $aVars);
    }

    function _blockFiles ($aReadyMedia, $iAuthorId = 0)
    {
        if (!$aReadyMedia)
            return '';

        $aVars = array (
            'bx_repeat:files' => array (),
        );

        foreach ($aReadyMedia as $iMediaId) {

            $a = BxDolService::call('files', 'get_file_array', array($iMediaId), 'Search');
            if (!$a['date'])
                continue;

            bx_import('BxTemplFormView');
            $oForm = new BxTemplFormView(array());

            $aInputBtnDownload = array (
                'type' => 'submit',
                'name' => 'download',
                'value' => _t ('_download'),
                'attrs' => array(
                    'class' => 'bx-btn-small',
                    'onclick' => "window.open ('" . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "download/".$this->aDataEntry[$this->_oDb->_sFieldId]."/{$iMediaId}','_self');",
                ),
            );

            $aVars['bx_repeat:files'][] = array (
                'id' => $iMediaId,
                'title' => $a['title'],
                'icon' => $a['file'],
                'date' => defineTimeInterval($a['date']),
                'btn_download' => $oForm->genInputButton ($aInputBtnDownload),
            );
        }

        if (!$aVars['bx_repeat:files'])
            return '';

        return $this->_oTemplate->parseHtmlByName('entry_view_block_files', $aVars);
    }

    function _blockSound ($aReadyMedia, $iAuthorId, $sPrefix = false)
    {
        if (!$aReadyMedia)
            return '';

        $aVars = array (
            'title' => false,
            'prefix' => $sPrefix ? $sPrefix : 'id'.time().'_'.rand(1, 999999),
            'default_height' => 350,
            'bx_repeat:sounds' => array (),
            'bx_repeat:icons' => array (),
        );

        foreach ($aReadyMedia as $iMediaId) {

            $a = BxDolService::call('sounds', 'get_music_array', array($iMediaId, 'browse'), 'Search');
            $a['ID'] = $iMediaId;

            $aVars['bx_repeat:sounds'][] = array (
                'style' => false === $aVars['title'] ? '' : 'display:none;',
                'id' => $iMediaId,
                'sound' => BxDolService::call('sounds', 'get_sound_concept', array($a), 'Search'),
            );
            $aVars['bx_repeat:icons'][] = array (
                'id' => $iMediaId,
                'icon_url' => $a['file'],
                'title' => $a['title'],
            );
            if (false === $aVars['title'])
                $aVars['title'] = $a['title'];
        }

        if (!$aVars['bx_repeat:icons'])
            return '';

        return $this->_oTemplate->parseHtmlByName('entry_view_block_sounds', $aVars);
    }

    function _blockFans($iPerPage, $sFuncIsAllowed = 'isAllowedViewFans', $sFuncGetFans = 'getFans')
    {
        if (!$this->_oMain->$sFuncIsAllowed($this->aDataEntry))
            return '';

        $iPage = (int)$_GET['page'];
        if( $iPage < 1)
            $iPage = 1;
        $iStart = ($iPage - 1) * $iPerPage;

        $aProfiles = array ();
        $iNum = $this->_oDb->$sFuncGetFans($aProfiles, $this->aDataEntry[$this->_oDb->_sFieldId], true, $iStart, $iPerPage);
        if (!$iNum || !$aProfiles)
            return MsgBox(_t("_Empty"));

        bx_import('BxTemplSearchProfile');
        $oBxTemplSearchProfile = new BxTemplSearchProfile();
        $sMainContent = '';
        foreach ($aProfiles as $aProfile) {
            $sMainContent .= $oBxTemplSearchProfile->displaySearchUnit($aProfile, array ('ext_css_class' => 'bx-def-margin-sec-top-auto'));
        }
        $ret .= $sMainContent;
        $ret .= '<div class="clear_both"></div>';

        $oPaginate = new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'count' => $iNum,
            'per_page' => $iPerPage,
            'page' => $iPage,
            'on_change_page' => 'return !loadDynamicBlock({id}, \'' . bx_append_url_params(BX_DOL_URL_ROOT . $this->_oMain->_oConfig->getBaseUri() . "view/" . $this->aDataEntry[$this->_oDb->_sFieldUri], 'page={page}&per_page={per_page}') . '\');',
        ));
        $sAjaxPaginate = $oPaginate->getSimplePaginate('', -1, -1, false);

        return array($ret, array(), $sAjaxPaginate);
    }

    function _blockFansUnconfirmed($iFansLimit = 1000)
    {
        if (!$this->_oMain->isEntryAdmin($this->aDataEntry))
            return '';

        $aProfiles = array ();
        $iNum = $this->_oDb->getFans($aProfiles, $this->aDataEntry[$this->_oDb->_sFieldId], false, 0, $iFansLimit);
        if (!$iNum)
            return MsgBox(_t('_Empty'));

        $sActionsUrl = bx_append_url_params(BX_DOL_URL_ROOT . $this->_oMain->_oConfig->getBaseUri() . "view/" . $this->aDataEntry[$this->_oDb->_sFieldUri], array('ajax_action' => ''));
        $aButtons = array (
            array (
                'type' => 'submit',
                'name' => 'fans_reject',
                'value' => _t('_sys_btn_fans_reject'),
                'onclick' => "onclick=\"getHtmlData('sys_manage_items_unconfirmed_fans_content', '{$sActionsUrl}reject&ids=' + sys_manage_items_get_unconfirmed_fans_ids(), false, 'post'); return false;\"",
            ),
            array (
                'type' => 'submit',
                'name' => 'fans_confirm',
                'value' => _t('_sys_btn_fans_confirm'),
                'onclick' => "onclick=\"getHtmlData('sys_manage_items_unconfirmed_fans_content', '{$sActionsUrl}confirm&ids=' + sys_manage_items_get_unconfirmed_fans_ids(), false, 'post'); return false;\"",
            ),
        );
        bx_import ('BxTemplSearchResult');
        $sControl = BxTemplSearchResult::showAdminActionsPanel('sys_manage_items_unconfirmed_fans', $aButtons, 'sys_fan_unit');
        $aVars = array(
            'suffix' => 'unconfirmed_fans',
            'content' => $this->_oMain->_profilesEdit($aProfiles),
            'control' => $sControl,
        );
        return $this->_oMain->_oTemplate->parseHtmlByName('manage_items_form', $aVars);
    }
}
