<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');
bx_import('BxDolCategories');
bx_import('BxTemplVotingView');

class BxDolFilesTemplate extends BxDolModuleTemplate
{
    /**
     * Constructor
     */
    function __construct (&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    // function of output
    function pageCode ($aPage = array(), $aPageCont = array(), $aCss = array(), $aJs = array(), $bAdminMode = false, $isSubActions = true)
    {
        if (!empty($aPage)) {
            foreach ($aPage as $sKey => $sValue)
                $GLOBALS['_page'][$sKey] = $sValue;
        }
        if (!empty($aPageCont)) {
            foreach ($aPageCont as $sKey => $sValue)
                $GLOBALS['_page_cont'][$aPage['name_index']][$sKey] = $sValue;
        }
        if (!empty($aCss))
            $this->addCss($aCss);
        if (!empty($aJs))
            $this->addJs($aJs);

        if ($isSubActions) {
            $aVars = array ('BaseUri' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri());
            $GLOBALS['oTopMenu']->setCustomSubActions($aVars, $this->_oConfig->getMainPrefix() . '_title', false);
        }

        if (!$bAdminMode)
            PageCode($this);
        else
            PageCodeAdmin();
    }

    function getFileInfo ($aInfo)
    {
        return $this->getFileAuthor($aInfo);
    }

    function getFileAuthor ($aInfo)
    {
        if (empty($aInfo))
            return '';
        return $this->parseHtmlByName('media_info.html', array('memberPic' => get_member_thumbnail($aInfo['medProfId'], 'none', TRUE)));
    }

    function getBasicFileInfoForm (&$aInfo, $sUrlPref = '')
    {
        $aForm = array(
            'date' => array(
                'type' => 'value',
                'value' => getLocaleDate($aInfo['medDate'], BX_DOL_LOCALE_DATE_SHORT) . ' (' . defineTimeInterval($aInfo['medDate'], false) . ')',
                'caption' => _t('_Date'),
            ),

        );

        if(!empty($aInfo['Categories']))
            $aForm['category'] = array(
                'type' => 'value',
                'value' => getLinkSet($aInfo['Categories'], $sUrlPref . 'browse/category/', CATEGORIES_DIVIDER),
                'caption' => _t('_Category'),
            );

        if(!empty($aInfo['medTags']))
            $aForm['tags'] = array(
                'type' => 'value',
                'value' => getLinkSet($aInfo['medTags'], $sUrlPref . 'browse/tag/'),
                'caption' => _t('_Tags'),
            );

        return $aForm;
    }

    function getCompleteFileInfoForm (&$aInfo, $sUrlPref = '')
    {
        return $this->getBasicFileInfoForm($aInfo, $sUrlPref);
    }

    function getFileInfoMain (&$aInfo)
    {
        $sUrlPref = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();
        $aForm = array(
            'form_attrs' => array('id' => $this->_oConfig->getMainPrefix() . '_upload_form'),
            'params'=> array('remove_form'=>true),
            'inputs' => $this->getCompleteFileInfoForm($aInfo, $sUrlPref)
        );
        $oForm = new BxTemplFormView($aForm);
        return array($oForm->getCode(), array(), array(), false);
    }

    function getRate ($iFile)
    {
        $iFile = (int)$iFile;
        $sCode = '<center>' . _t('_rating not enabled') . '</center>';
        $oVotingView = new BxTemplVotingView($this->_oConfig->getMainPrefix(), $iFile);
        if ($oVotingView->isEnabled())
            $sCode = $oVotingView->getBigVoting ();
        return $sCode;
    }

    function getFilePic ($iFile)
    {
        $aIdent = array('fileId' => (int)$iFile);
        return $this->_oDb->getFileInfo($aIdent, true, array('medID', 'Type'));
    }

    function getFilesBox ($aCode, $sWrapperId = '')
    {
        if (!is_array($aCode))
            return '';
        else {
            ob_start();
            ?>
            <div class="searchContentBlock">
                __code__
            </div>
            __paginate__
            <?php
            $sCode = ob_get_clean();
            foreach ($aCode as $sKey => $sValue)
                $sCode = str_replace('__' . $sKey . '__', $sValue, $sCode);
            if (strlen($sWrapperId) > 0)
                $sCode = '<div id="' . $sWrapperId . '">' . $sCode . '</div>';
        }
        return $sCode;
    }

    function getAdminShort ($iNumber, $sAlbumUri, $sNickName)
    {
        $iNumber     = (int)$iNumber;
        $sAlbumUri   = process_db_input($sAlbumUri, BX_TAGS_STRIP);
        $sNickName   = process_db_input($sNickName, BX_TAGS_STRIP);
        $sLinkPref   = $this->_oConfig->getBaseUri() . 'albums/my/';
        $sLinkAdd    = $sLinkPref . 'add_objects/' . $sAlbumUri . '/owner/' . $sNickName;
        $sLinkBrowse = $sLinkPref . 'manage_objects/' . $sAlbumUri . '/owner/' . $sNickName;
        $sLangPref   = '_' . $this->_oConfig->getMainPrefix();
        $aUnit = array(
            'fileStatCount' => _t($sLangPref . '_count_info', $iNumber, $sLinkBrowse),
            'fileStatAdd' => _t($sLangPref . '_add_info', $sLinkAdd),
            'spec_class' => ''
        );
        return $this->parseHtmlByName('admin_short.html', $aUnit);
    }

    function getAdminAlbumShort ($iNumber)
    {
        $iNumber = (int)$iNumber;
        $sLinkPref = $this->_oConfig->getBaseUri() . 'albums/my/';
        $sLinkAdd = $sLinkPref . 'add/';
        $sLinkBrowse = $sLinkPref . 'manage/';
        $sLangPref = '_' . $this->_oConfig->getMainPrefix() . '_albums_';
        $aUnit = array(
            'fileStatCount' => _t($sLangPref . 'count_info', $iNumber, $sLinkBrowse),
            'fileStatAdd' => _t($sLangPref . 'add_info', $sLinkAdd),
            'spec_class' => ''
        );
        return $this->parseHtmlByName('admin_short.html', $aUnit);
    }

    function getSitesSetBox ($sLink)
    {
        require_once(BX_DIRECTORY_PATH_INC . 'shared_sites.inc.php');
        $aSites = getSitesArray($sLink);
        $aUnits = array();
        foreach ($aSites as $sName => $aValue) {
            $aUnits[] = array(
                'icon' => $this->getIconUrl($aValue['icon']),
                'url' => $aValue['url'],
                'name' => $sName
            );
        }
        return $this->parseHtmlByName('popup_share.html', array('bx_repeat:sites' => $aUnits));
    }

    function getSearchForm ($aRedInputs = array(), $aRedForm = array())
    {
        $aForm = array(
            'form_attrs' => array(
               'id' => 'searchForm',
               'action' => '',
               'method' => 'post',
               'enctype' => 'multipart/form-data',
               'onsubmit' => '',
            ),
            'inputs' => array(
                'keyword' => array(
                    'type' => 'text',
                    'name' => 'keyword',
                    'caption' => _t('_Keyword')
                ),
                'ownerName' => array(
                    'type' => 'text',
                    'name' => 'owner',
                    'caption' => _t('_Member'),
                    'attrs' => array('id'=>'ownerName')
                ),
                'status' => array(
                    'type' => 'select',
                    'name' => 'status',
                    'caption' => _t('_With status'),
                    'values' => array(
                        'any'=> _t('_' . $this->_oConfig->getMainPrefix() . '_any'),
                        'approved' => _t('_' . $this->_oConfig->getMainPrefix() . '_approved'),
                        'disapproved'=> _t('_' . $this->_oConfig->getMainPrefix() . '_disapproved'),
                        'pending'=> _t('_' . $this->_oConfig->getMainPrefix() . '_pending')
                    ),
                ),
                'search' => array(
                    'type' => 'submit',
                    'name' => 'search',
                    'value' => _t('_Search')
                )
            )
        );
        if (!empty($aRedInputs) && is_array($aRedInputs))
            $aForm['inputs'] = array_merge($aForm['inputs'], $aRedInputs);
        if (!empty($aRedForm) && is_array($aRedForm))
            $aForm['form_attrs'] = array_merge($aForm['form_attrs'], $aRedForm);
        $oForm = new BxTemplFormView($aForm);
        return $oForm->getCode();
    }

    function getHeaderCode ()
    {
        $aUnit = array(
            'site_admin_url' => BX_DOL_URL_ADMIN,
            'site_plugins' => BX_DOL_URL_PLUGINS,
            'users_processing' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/',
            'current_tmpl' => $GLOBALS['tmpl']
        );
        return $this->parseHtmlByName('media_admin_header.html', $aUnit, array('{','}'));
    }

    function getJsTimeOutRedirect ($sUrl, $iTime = 1)
    {
        $aUnits['url'] = bx_js_string($sUrl);
        $aUnits['time'] = (int)$iTime * 1000;
        if ($aUnits['time'] <= 0)
            $aUnits['time'] = 1000;
        ob_start();
        ?>
            <script language="javascript" type="text/javascript">
                setTimeout(function() {
                    window.location = '__url__';
                }, __time__);
            </script>
        <?php
        $sCode = ob_get_clean();
        return $this->parseHtmlByContent($sCode, $aUnits);
    }

    function getBrowseBlock ($sContent, $iUnitWidth, $iUnitCount, $iWishWidth = 0)
    {
        $iAllWidth = $iWishWidth > 0 ? $iWishWidth : (int)getParam('main_div_width');
        $iDestWidth = getBlockWidth($iAllWidth, $iUnitWidth, $iUnitCount);
        $aUnit = array(
            'content' => $sContent,
            'bx_if:dest_width' => array(
                'condition' => $iDestWidth > 0,
                'content' => array('width' => $iDestWidth)
            )
        );
        return $this->parseHtmlByName('centered_block.html', $aUnit);
    }

    function getExtraTopMenu ($aMenu = array(), $sUrlPrefix = BX_DOL_URL_ROOT)
    {
        $aUnits = array();
        foreach ($aMenu as $aValue) {
            $aValue['link'] = $sUrlPrefix . $aValue['link'];
            $aUnits[] = array(
                'bx_if:active' => array(
                    'condition' => $aValue['active'] == true,
                    'content' => $aValue
                ),
                'bx_if:not_active' => array(
                    'condition' => $aValue['active'] == false,
                    'content' => $aValue
                )
            );
        }
        $aUnit['bx_repeat:menu'] = $aUnits;
        return $this->parseHtmlByName('extra_top_menu.html', $aUnit);
    }

    function getExtraSwitcher ($aMenu = array(), $sHeadKey, $iBoxId = 1)
    {
        $aUnits = array();
        foreach ($aMenu as $sName => $aItem) {
            $aUnits[] = array(
                'href' => $aItem['href'],
                'name' => $sName,
                'selected' => $aItem['active'] == true ? 'selected' : ''
            );
        }

        $sContent = $this->parseHtmlByName('extra_switcher.html', array(
            'bx_repeat:options' => $aUnits,
            'head_key' => _t($sHeadKey),
            'block_id' => $iBoxId
        ));
        return $this->parseHtmlByName('designbox_top_controls.html', array('top_controls' => $sContent));
    }

    function getAlbumFeed (&$aUnits)
    {
        $sItemType = $this->getItemType();
        ob_start();
        ?>
        <item>
            <title>__title__</title>
            <media:description>__title__</media:description>
            <link>__link__</link>
            <media:thumbnail url="__thumb__"/>
            <media:content <?=$sItemType?> url="__main__"/>
        </item>
        <?php
        $sTempl = ob_get_clean();
                $sCode = '';
        if (is_array($aUnits) && !empty($aUnits)) {
            foreach ($aUnits as $aData) {
                $aData['link'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aData['uri'];
                if (!isset($aData['main']))
                    $aData['main'] = $aData['file'];
                $sCode .= $this->parseHtmlByContent($sTempl, $aData);
            }
        } else
                    $sCode = _t('_Empty');
        return $this->getRssTemplate($sCode);
    }

    function getItemType ()
    {
        return '';
    }

    function getRssTemplate ($sContent = '')
    {
        ob_start();
        ?>
        <rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom">
            <channel>
                <?=$sContent?>
            </channel>
        </rss>
        <?php
        $sCode = ob_get_clean();

        return '<?xml version="1.0" encoding="utf-8" standalone="yes"?>' . $sCode;
    }

	function getAlbumInfo (&$aInfo)
    {
        $aForm = array(
            'form_attrs' => array('id' => $this->_oConfig->getMainPrefix() . '_album_view_form'),
            'params'=> array('remove_form' => true),
            'inputs' => array(
				'location' => array(
	                'type' => 'value',
	                'value' => bx_linkify(process_text_output($aInfo['Location'])),
	                'caption' => _t('_Location'),
	            ),
        		'description' => array(
	                'type' => 'value',
	                'value' => bx_linkify(process_html_output($aInfo['Description'])),
	                'caption' => _t('_Description'),
	            ),
        		'date' => array(
	                'type' => 'value',
	                'value' => getLocaleDate($aInfo['Date'], BX_DOL_LOCALE_DATE_SHORT) . ' (' . defineTimeInterval($aInfo['Date'], false) . ')',
	                'caption' => _t('_Date'),
	            ),
        	)
        );
        $aForm['inputs'] = array_filter($aForm['inputs'], function($aInput) {
            return !!$aInput['value'];
        });
        $oForm = new BxTemplFormView($aForm);
        return array($oForm->getCode(), array(), array(), false);
    }

    function getAlbumPreview ($sRssLink)
    {
    }

    function getAlbumFormAddArray ($aReInputs = array(), $aReForm = array())
    {
        $aForm = array(
            'form_attrs' => array(
                'id' => $this->_oConfig->getMainPrefix() . '_upload_form',
                'method' => 'post',
                'action' => $this->_oConfig->getBaseUri() . 'albums/my/add'
            ),
            'params' => array (
                'db' => array(
                    'submit_name' => 'submit',
                ),
                'checker_helper' => 'BxSupportCheckerHelper',
            ),
            'inputs' => array(
                'header' => array(
                    'type' => 'block_header',
                    'caption' => _t('_Info'),
                ),
                'title' => array(
                    'type' => 'text',
                    'name' => 'caption',
                    'caption' => _t('_Title'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3, 128),
                        'error' => _t('_td_err_incorrect_length'),
                    ),
                ),
                'location' => array(
                    'type' => 'text',
                    'name' => 'location',
                    'caption' => _t('_Location'),
                ),
                'description' => array(
                    'type' => 'textarea',
                    'name' => 'description',
                    'caption' => _t('_Description'),
                ),
                'allow_view' => array(),
                'owner' => array(
                    'type' => 'hidden',
                    'name' => 'owner',
                    'value' => $this->_oDb->iViewer,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'name' => 'submit',
                    'value' => _t('_Submit'),
                ),
            ),
        );
        if (is_array($aReInputs) && !empty($aReInputs))
            $aForm['inputs'] = array_merge($aForm['inputs'], $aReInputs);
        if (is_array($aReForm) && !empty($aReForm))
            $aForm['form_attrs'] = array_merge($aForm['form_attrs'], $aReForm);
        return $aForm;
    }

    function getAlbumFormEditArray ($aReInputs = array(), $aReForm = array())
    {
        $aForm = $this->getAlbumFormAddArray(array(), $aReForm);
        if (is_array($aReInputs) && !empty($aReInputs)) {
            foreach ($aReInputs as $sKey => $aValue) {
                if (array_key_exists($sKey, $aForm['inputs']))
                    $aForm['inputs'][$sKey] = array_merge($aForm['inputs'][$sKey], $aValue);
                else
                    $aForm['inputs'][$sKey] = $aValue;
            }
        }
        return $aForm;
    }

    function getJsInclude($sType = 'main', $bWrap = true)
    {
        $sConten = '';
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();

        switch($sType) {
            case 'main':
                $sConten = "var oBxDolFiles = new BxDolFiles({sBaseUrl: '" . $sBaseUrl . "'});";
            break;
        }

        return $bWrap ? $this->_wrapInTagJsCode($sConten) : $sConten;
    }
}
