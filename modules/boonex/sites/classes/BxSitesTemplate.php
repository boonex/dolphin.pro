<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');
bx_import('BxDolCategories');

class BxSitesTemplate extends BxDolModuleTemplate
{
    var $_iPageIndex = 600;
    var $_aCss = array ();
    var $_aJs = array ();
    var $_bObStarted = 0;

    /**
     * Constructor
     */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    function unit($aData, $sTemplateName, &$oVotingView, $sThumbSize = 'browse')
    {
        if (null == $this->_oMain)
            $this->_oMain = BxDolModule::getInstance('BxSitesModule');

        if (!$this->_oMain->isAllowedView($aData))
            return $this->parseHtmlByName('browse_unit_private.html', array());

        $aResult = $this->_getUnit($aData, $sThumbSize);
        $aResult['rate'] = $oVotingView->getJustVotingElement(0, $aData['id'], $aData['rate']);

        return $this->parseHtmlByName($sTemplateName . '.html', $aResult);
    }

    function blockHon($aData)
    {
        $oVoting = new BxTemplVotingView('bx_sites', $aData['id']);
        $aResult = $this->_getUnit($aData, 'file');
        $aResult['rate'] = $oVoting->getBigVoting();
        $aResult['next_url'] = $_SERVER['REQUEST_URI'];

        return $this->parseHtmlByName('block_hon.html', $aResult);
    }

    function blockInformation($aSite)
    {
        $this->addCss(array('block_info.css'));
        $aResult = array(
            'author_unit' => get_member_thumbnail($aSite['ownerid'], 'none', true),
            'date' => getLocaleDate($aSite['date'], BX_DOL_LOCALE_DATE_SHORT),
            'date_ago' => defineTimeInterval($aSite['date'], false),
            'cats' => $this->parseCategories($aSite['categories']),
            'tags' => $this->parseTags($aSite['tags']),
            'fields' => '',
        );

        $this->_checkOwner($aSite, $aResult);

        return $this->parseHtmlByName('entry_view_block_info.html', $aResult);
    }

    function addCssAdmin ($sName)
    {
        $sClassPrefix = 'bx_sites_css';
        $GLOBALS['oAdmTemplate']->addLocation($sClassPrefix, $this->_oConfig->getHomePath(), $this->_oConfig->getHomeUrl());
        $GLOBALS['oAdmTemplate']->addCss($sName);
        $GLOBALS['oAdmTemplate']->removeLocation($sClassPrefix);
    }

    function addJsAdmin ($sName)
    {
        $GLOBALS['oAdmTemplate']->addJs($sName);
    }

    function pageCode($sTitle, $isDesignBox = true, $isWrap = true, $isSubActions = true)
    {

        global $_page;
        global $_page_cont;

        if (null == $this->_oMain)
            $this->_oMain = BxDolModule::getInstance('BxSitesModule');

        $_page['name_index'] = $isDesignBox ? 0 : $this->_iPageIndex;

        $_page['header'] = $sTitle ? $sTitle : $GLOBALS['site']['title'];
        $_page['header_text'] = $sTitle;

        if ($isWrap) {
            $aVars = array (
                'content' => $this->pageEnd(),
            );
            $_page_cont[$_page['name_index']]['page_main_code'] = $this->parseHtmlByName('default_padding.html', $aVars);
        } else
            $_page_cont[$_page['name_index']]['page_main_code'] = $this->pageEnd();

        if ($isSubActions) {
            $aVars = array ('BaseUri' => $this->_oConfig->getBaseUri(), 'isAllowedAdd' => ($this->_oMain->isAllowedAdd() ? 1 : 0));
            $GLOBALS['oTopMenu']->setCustomSubActions($aVars, 'bx_sites_title', false);
        }

        PageCode($this);
    }

    function adminBlock($sContent, $sTitle, $aMenu = array())
    {
        return DesignBoxAdmin($sTitle, $sContent, $aMenu);
    }

    function pageCodeAdmin($sTitle)
    {
        global $_page;
        global $_page_cont;

        $_page['name_index'] = 9;

        $_page['header'] = $sTitle ? $sTitle : $GLOBALS['site']['title'];
        $_page['header_text'] = $sTitle;

        $_page_cont[$_page['name_index']]['page_main_code'] = $this->pageEnd();

        PageCodeAdmin();
    }

    function pageStart()
    {
        if (0 == $this->_bObStarted) {
            ob_start();
            $this->_bObStarted = 1;
        }
    }

    function pageEnd($isGetContent = true)
    {
        if (1 == $this->_bObStarted) {
            $sRet = '';
            if ($isGetContent)
                $sRet = ob_get_clean();
            else
                ob_end_clean();
            $this->_bObStarted = 0;
            return $sRet;
        }
    }

    function parseTags($s)
    {
        return $this->_parseAnything($s, ',', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/tag/');
    }

    function parseCategories($s)
    {
        return $this->_parseAnything($s, CATEGORIES_DIVIDER, BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/category/');
    }

    function _parseAnything($s, $sDiv, $sLinkStart, $sClassName = '')
    {
        $sRet = '';
        $a = explode ($sDiv, $s);
        $sClass = $sClassName ? 'class="'.$sClassName.'"' : '';

        foreach ($a as $sName)
            $sRet .= '<a '.$sClass.' href="' . $sLinkStart . urlencode(title2uri($sName)) . '">'.$sName.'</a>&#160';

        return $sRet;
    }

    function _getDomain($sUrl)
    {
        $aParts = parse_url($sUrl);
        $sHost = $aParts['host'];
        if (in_array("www", explode(".", $sHost))) {
            $aJustDomain = explode("www.", $sHost);
            return $aJustDomain[1];
        } else
            return $sHost;
    }

    function _checkOwner($aData, &$aResult)
    {
        if ($aData['ownerid']) {
            $aOwner = getProfileInfo($aData['ownerid']);
            $aResult['owner_url'] = getProfileLink($aOwner['ID']);
            $aResult['owner'] = getNickName($aOwner['ID']);
        } else {
            $aResult['owner_url'] = 'javascript: void(0)';
            $aResult['owner'] = _t('_bx_sites_admin');
        }
    }

    function _getUnit($aData, $sThumbSize = 'browse')
    {
        if (null == $this->_oMain)
            $this->_oMain = BxDolModule::getInstance('BxSitesModule');

        $sUrl = strncasecmp($aData['url'], 'http://', 7) !== 0 && strncasecmp($aData['url'], 'https://', 8) !== 0 ? 'http://' . $aData['url'] : $aData['url'];

        $aFile = BxDolService::call('photos', 'get_photo_array', array($aData['photo'], $sThumbSize), 'Search');
        $sImage = $aFile['no_image'] ? '' : $aFile['file'];

        // BEGIN STW INTEGRATION
        if (getParam('bx_sites_account_type') != 'No Automated Screenshots') {
            if ($sImage == '') {
                $aSTWOptions = array(
                );

                bx_sites_import('STW');
                $sThumbHTML = getThumbnailHTML($sUrl, $aSTWOptions, false, 'bx-def-shadow bx-def-round-corners');
            }
        }
        // END STW INTEGRATION

        $sSiteLink = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aData['entryUri'];

        $aResult = array(
            'id' => $aData['id'],
            'url' => $sUrl,
            'url_title' => $this->_getDomain($sUrl),
            'title' => $aData['title'],
            'site_url' => $sSiteLink,
            'description' => $this->_oMain->_formatSnippetText($aData),
            // BEGIN STW INTEGRATION
            'bx_if:is_image' => array(
                'condition' => $sThumbHTML == false,
                'content' => array('site_link' => $sSiteLink, 'image' => $sImage ? $sImage : $this->getImageUrl('no-image-thumb.png'))
            ),
            'bx_if:is_thumbhtml' => array(
                'condition' => $sThumbHTML != '',
                'content' => array('site_link' => $sSiteLink, 'thumbhtml' => $sThumbHTML)
            ),
            // END STW INTEGRATION
            'comments' => $aData['commentsCount'] . ' ' . _t('_bx_sites_unit_comments'),
            'date' => defineTimeInterval($aData['date']),
            'owner_str' => _t('_bx_sites_unit_from') . ' ',
            'cats_str' => _t('_Categories') . ':',
            'cats' => $this->parseCategories($aData['categories']),
            'tags_str' => _t('_Tags') . ':',
            'tags' => $this->parseTags($aData['tags'])
        );

        $this->_checkOwner($aData, $aResult);

        return $aResult;
    }

    function displayAccessDeniedExt($sTitle, $isAjaxMode = false)
    {
        $this->_showDisplay($sTitle, _t('_bx_sites_msg_access_denied'), $isAjaxMode);
    }

    function displayNoDataExt($sTitle, $isAjaxMode = false)
    {
        $this->_showDisplay($sTitle, _t('_Empty'), $isAjaxMode);
    }

    function displayPendingApproval($sTitle, $isAjaxMode = false)
    {
        $this->_showDisplay($sTitle, _t('_bx_sites_msg_pending_approval'), $isAjaxMode);
    }

    function displayPageNotFoundExt($sTitle, $isAjaxMode = false)
    {
        header("HTTP/1.0 404 Not Found");
        $this->_showDisplay($sTitle, _t('_bx_sites_msg_page_not_found'), $isAjaxMode);
    }

    function _showDisplay($sTitle, $sText, $isAjaxMode = false)
    {
        if (!$isAjaxMode) {
            $this->pageStart();
            echo MsgBox($sText);
            $this->pageCode ($sTitle, true, false);
        } else
            echo MsgBox($sText);
    }
}
