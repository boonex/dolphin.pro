<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxDolAlbums');
bx_import("BxTempFormView");

class BxDolFilesModule extends BxDolModule
{
    var $_iProfileId;
    var $aPageTmpl;
    var $oPrivacy;
    var $oAlbumPrivacy;

    var $oAlbums;

    var $_aMemActions = array('add', 'view', 'delete', 'approve', 'edit');

    var $aSectionsAdmin = array();

    /**
     * Constructor
     */
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
        $this->_iProfileId = $this->_oDb->iViewer;
        $this->aPageTmpl = array(
            'name_index'  => 1,
            'header'      => $GLOBALS['site']['title'],
            'header_text' => '',
        );
        $sClassName      = $this->_oConfig->getClassPrefix() . 'Privacy';
        bx_import('Privacy', $aModule);
        $this->oPrivacy      = new $sClassName();
        $this->oAlbumPrivacy = new $sClassName('sys_albums');
        $this->oAlbums       = new BxDolAlbums($this->_oConfig->getMainPrefix(), $this->_iProfileId);

        $this->aSectionsAdmin = array(
            'approved'    => array(
                'exclude_btns' => array('activate')
            ),
            'disapproved' => array(
                'exclude_btns' => array('deactivate', 'featured', 'unfeatured')
            ),
            'pending'     => array(
                'exclude_btns' => array('activate', 'deactivate', 'featured', 'unfeatured')
            ),
        );
    }

    function _checkVisible($aParam = array())
    {
        $aVis = array(BX_DOL_PG_ALL);
        if ($this->_iProfileId > 0) {
            $aVis[] = BX_DOL_PG_MEMBERS;
        }

        return $aVis;
    }

    function _defineActionsArray()
    {
        $aNewActions = array();
        foreach ($this->_aMemActions as $sValue) {
            $aNewActions[] = $this->_oConfig->getUri() . ' ' . $sValue;
        }

        return $aNewActions;
    }

    function _defineActions()
    {
        $aActionList = $this->_defineActionsArray();
        defineMembershipActions($aActionList);
    }

    function _defineActionName($sAction)
    {
        $sConstName = strtoupper(str_replace(' ', '_', $this->_oConfig->getMainPrefix()) . '_' . $sAction);

        return constant($sConstName);
    }

    function _deleteFile($iFileId)
    {
        $aInfo = $this->serviceCheckDelete($iFileId);
        if (!$aInfo) {
            return false;
        }
        if ($this->_oDb->deleteData($iFileId)) {
            $aFilesPostfix = $this->_oConfig->aFilePostfix;
            //delete temp files
            $aFilesPostfix['temp'] = '';
            if (isset($aFilesPostfix['original'])) {
                $aFilesPostfix['original'] = $this->_getOriginalExt($aInfo, $aFilesPostfix['original']);
            }
            foreach ($aFilesPostfix as $sValue) {
                $sFilePath = $this->_oConfig->getFilesPath() . $iFileId . $sValue;
                @unlink($sFilePath);
            }
            bx_import('BxDolVoting');
            $oVoting = new BxDolVoting($this->_oConfig->getMainPrefix(), 0, 0);
            $oVoting->deleteVotings($iFileId);
            bx_import('BxDolCmts');
            $oCmts = new BxDolCmts($this->_oConfig->getMainPrefix(), $iFileId);
            $oCmts->onObjectDelete();

            bx_import('BxDolCategories');
            //tags & categories parsing
            $oTag = new BxDolTags();
            $oTag->reparseObjTags($this->_oConfig->getMainPrefix(), $iFileId);
            $oCateg = new BxDolCategories();
            $oCateg->reparseObjTags($this->_oConfig->getMainPrefix(), $iFileId);

            $bUpdateCounter = $aInfo['Approved'] == 'approved' ? true : false;
            $this->oAlbums->removeObjectTotal($iFileId, $bUpdateCounter);

            //delete all subscriptions
            $oSubscription = BxDolSubscription::getInstance();
            $oSubscription->unsubscribe(array(
                'type'      => 'object_id',
                'unit'      => $this->_oConfig->getMainPrefix(),
                'object_id' => $iFileId
            ));

            bx_import('BxDolAlerts');
            $oAlert = new BxDolAlerts($this->_oConfig->getMainPrefix(), 'delete', $iFileId, $this->_iProfileId, $aInfo);
            $oAlert->alert();

            $this->isAllowedDelete($aInfo, true);
        } else {
            return false;
        }

        return true;
    }

    function _deleteAlbumUnits($iAlbumId)
    {
        $iAlbumId = (int)$iAlbumId;
        $aObjects = $this->oAlbums->getAlbumObjList($iAlbumId);
        $iCount   = 0;
        foreach ($aObjects as $iValue) {
            $iObj = (int)$iValue;
            if (!$this->_deleteFile($iObj)) {
                $iCount++;
            }
        }

        return $iCount;
    }

    function _getOriginalExt(&$aInfo, $sTmpl, $sKey = '{ext}')
    {
        return str_replace($sKey, $aInfo['medExt'], $sTmpl);
    }

    function actionAdministration($sParam = '', $sParam1 = '')
    {
        if (!isAdmin($this->_iProfileId)) {
            return;
        }
        $this->checkActions();

        if (isset($_GET['action']) && $_GET['action'] == 'findMembers') {
            echo $this->getMemberList();
            exit;
        }

        $aMenu = array(
            $this->_oConfig->getMainPrefix() . '_admin_main'     => array(
                'title' => _t('_' . $this->_oConfig->getMainPrefix() . '_admin_main'),
                'href'  => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/home'
            ),
            $this->_oConfig->getMainPrefix() . '_admin_settings' => array(
                'title' => _t('_' . $this->_oConfig->getMainPrefix() . '_admin_settings'),
                'href'  => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/settings'
            ),
        );

        switch ($sParam) {
            case 'settings':
                $aMenu[$this->_oConfig->getMainPrefix() . '_admin_settings']['active'] = 1;
                $sCode                                                                 = $this->getAdminSettings($aMenu);
                break;
            default:
                $aMenu[$this->_oConfig->getMainPrefix() . '_admin_main']['active'] = 1;
                $sCode                                                             = $this->getAdminMainPage($aMenu,
                    $sParam1);
                break;
        }
        $this->aPageTmpl['name_index'] = 9;
        $this->aPageTmpl['header']     = _t('_' . $this->_oConfig->getMainPrefix() . '_admin_title');
        $this->aPageTmpl['css_name']   = array('forms_adv.css', 'my.css', 'search.css', 'search_admin.css');
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode), array(), array(), true);
    }

    function actionHome()
    {
        $sClassName = $this->_oConfig->getClassPrefix() . 'PageHome';
        bx_import('PageHome', $this->_aModule);
        $oPage                       = new $sClassName($this);
        $sCode                       = $oPage->getCode();
        $this->aPageTmpl['css_name'] = array('browse.css');
        $this->aPageTmpl['header']   = _t('_' . $this->_oConfig->getMainPrefix() . '_home');
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    function actionCategories()
    {
        bx_import('BxTemplCategoriesModule');
        $aParam                    = array(
            'type' => $this->_oConfig->getMainPrefix(),
        );
        $oCateg                    = new BxTemplCategoriesModule($aParam, _t('_categ_users'),
            BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'categories');
        $sCode                     = $oCateg->getCode();
        $this->aPageTmpl['header'] = _t('_' . $aParam['type'] . '_top_menu_categories');
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    function actionTags()
    {
        bx_import('BxTemplTagsModule');
        $aParam                    = array(
            'type'    => $this->_oConfig->getMainPrefix(),
            'orderby' => 'popular'
        );
        $oTags                     = new BxTemplTagsModule($aParam,
            _t('_' . $this->_oConfig->getMainPrefix() . '_bcaption_all'),
            BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'tags');
        $sCode                     = $oTags->getCode();
        $this->aPageTmpl['header'] = _t('_' . $this->_oConfig->getMainPrefix() . '_top_menu_tags');
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    function actionView($sUri)
    {
        $aIdent = array(
            'fileUri' => $sUri,
        );
        $aInfo  = $this->_oDb->getFileInfo($aIdent);
        if (!empty($aInfo)) {
            if ($aInfo['AllowAlbumView'] == BX_DOL_PG_HIDDEN || !$this->isAllowedView($aInfo)) {
                $sKey  = _t('_' . $this->_oConfig->getMainPrefix() . '_forbidden');
                $sCode = DesignBoxContent($sKey, MsgBox($sKey), 1);
            } else {
                $aInfo['medTitle'] = stripslashes($aInfo['medTitle']);
                $aInfo['medDesc']  = stripslashes($aInfo['medDesc']);
                $aInfo['NickName'] = getUsername($aInfo['medProfId']);
                //meta keywords and descriptions
                $this->_oTemplate->setPageDescription(substr(strip_tags($aInfo['medDesc']), 0, 255));
                $this->_oTemplate->addPageKeywords($aInfo['medTags']);

                // album data about prev and next files
                // calculation of un-approved files in album
                $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
                bx_import('Search', $this->_aModule);
                $oSearch                                                      = new $sClassName();
                $oSearch->aCurrent['restriction']['albumId']                  = array(
                    'value'    => $aInfo['albumId'],
                    'field'    => 'ID',
                    'operator' => '=',
                    'table'    => 'sys_albums'
                );
                $oSearch->aCurrent['restriction']['activeStatus']['operator'] = '<>';

                $aIds = array();
                $aExcludeList = $oSearch->getSearchData();
                if (!empty($aExcludeList))
                    foreach ($aExcludeList as $aValue)
                        $aIds[] = $aValue['id'];

                $aInfo['prevItem'] = $this->oAlbums->getClosestObj($aInfo['albumId'], $aInfo['medID'], 'prev',
                    $aInfo['obj_order'], $aIds);
                $aInfo['nextItem'] = $this->oAlbums->getClosestObj($aInfo['albumId'], $aInfo['medID'], 'next',
                    $aInfo['obj_order'], $aIds);

                $aInfo['favorited'] = $this->_oDb->checkFavoritesIn($aInfo['medID']);

                bx_import('PageView', $this->_aModule);
                $sClassName                  = $this->_oConfig->getClassPrefix() . 'PageView';
                $oPage                       = new $sClassName($this, $aInfo);
                $sCode                       = $this->_oTemplate->getJsInclude() . $oPage->getCode();
                $this->aPageTmpl['header']   = $sKey = $aInfo['medTitle'];
                $this->aPageTmpl['js_name']  = 'BxDolFiles.js';
                $this->aPageTmpl['css_name'] = 'explanation.css';

                if ($this->_iProfileId != $aInfo['medProfId']) {
                    $this->isAllowedView($aInfo, true);
                }
            }
        } else {
            $this->_oTemplate->displayPageNotFound();
        }
        $GLOBALS['oTopMenu']->setCustomSubHeader(_t('_sys_album_x_photo_x', $aInfo['albumCaption'], $sKey));
        $GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/album/' . $aInfo['albumUri'] . '/owner/' . $aInfo['NickName']);
        $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
            _t('_' . $this->_oConfig->getMainPrefix()) => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'home/',
            $aInfo['albumCaption']                     => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/album/' . $aInfo['albumUri'] . '/owner/' . $aInfo['NickName'],
            $sKey                                      => '',
        ));

        $this->_oTemplate->addJsTranslation(array('_Are_you_sure'));
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    function actionBrowse(
        $sParamName = '',
        $sParamValue = '',
        $sParamValue1 = '',
        $sParamValue2 = '',
        $sParamValue3 = ''
    ) {
        $bAlbumView = false;
        if ($sParamName == 'album' && $sParamValue1 == 'owner') {
            $bAlbumView = true;
            $aAlbumInfo = $this->oAlbums->getAlbumInfo(array(
                'fileUri' => $sParamValue,
                'owner'   => getID($sParamValue2)
            ));
            if (empty($aAlbumInfo)) {
                $this->_oTemplate->displayPageNotFound();
            } else {
                if (!$this->oAlbumPrivacy->check('album_view', $aAlbumInfo['ID'], $this->_iProfileId)) {
                    $sKey                      = _t('_' . $this->_oConfig->getMainPrefix() . '_access_denied');
                    $sCode                     = DesignBoxContent($sKey, MsgBox($sKey), 1);
                    $this->aPageTmpl['header'] = $sKey;
                    $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));

                    return;
                }

                $GLOBALS['oTopMenu']->setCustomSubHeader(_t('_sys_album_x', $aAlbumInfo['Caption']));
                $GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/album/' . $aAlbumInfo['Uri'] . '/owner/' . $sParamValue2);
                $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
                    _t('_' . $this->_oConfig->getMainPrefix()) => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'home/',
                    $aAlbumInfo['Caption']                     => '',
                ));

                if ($aAlbumInfo['Owner'] == $this->_iProfileId && $sParamValue2 === getUsername($this->_iProfileId)) {
                    $this->actionAlbumsViewMy('main_objects', $sParamValue, $sParamValue1, $sParamValue2,
                        $sParamValue3);

                    return;
                }
            }
        }

        if ('calendar' == $sParamName) {
            $sParamValue  = (int)$sParamValue;
            $sParamValue1 = (int)$sParamValue1;
            $sParamValue2 = (int)$sParamValue2;
        }

        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch = new $sClassName($sParamName, $sParamValue, $sParamValue1, $sParamValue2);
        $sRss    = bx_get('rss');
        if ($sRss !== false && $sRss) {
            $oSearch->aCurrent['paginate']['perPage'] = 10;
            header('Content-Type: text/xml; charset=UTF-8');
            echo $oSearch->rss();
            exit;
        }

        $sTopPostfix = isset($oSearch->aCurrent['restriction'][$sParamName]) || $oSearch->aCurrent['sorting'] == $sParamName ? $sParamName : 'all';
        $sCaption    = _t('_' . $this->_oConfig->getMainPrefix() . '_top_menu_' . $sTopPostfix);
        if (!empty($sParamValue) && isset($oSearch->aCurrent['restriction'][$sParamName])) {
            $sParamValue                                            = $this->getBrowseParam($sParamName, $sParamValue);
            $oSearch->aCurrent['restriction'][$sParamName]['value'] = $sParamValue;
            $sCaption                                               = _t('_' . $this->_oConfig->getMainPrefix() . '_browse_by_' . $sParamName,
                htmlspecialchars_adv(process_pass_data($sParamValue)));
        }
        if ($bAlbumView) {
            $oSearch->aCurrent['restriction']['allow_view']['value'] = array($aAlbumInfo['AllowAlbumView']);
            $sCaption                                                = _t('_' . $this->_oConfig->getMainPrefix() . '_browse_by_' . $sParamName,
                $aAlbumInfo['Caption']);
            $this->_oTemplate->setPageDescription(substr(strip_tags($aAlbumInfo['Description']), 0, 255));
        } else {
            $oSearch->aCurrent['restriction']['not_allow_view']['value'] = array(BX_DOL_PG_HIDDEN);
        }

        $oSearch->aCurrent['paginate']['perPage'] = (int)$this->_oConfig->getGlParam('number_all');
        $sCode                                    = $oSearch->displayResultBlock();
        if ($oSearch->aCurrent['paginate']['totalNum'] > 0) {
            $sCode = $GLOBALS['oFunctions']->centerContent($sCode, '.sys_file_search_unit');
            $sCode = $this->_oTemplate->parseHtmlByName('default_padding_thd.html', array(
                'content' => $sCode
            ));

            $aAdd = array($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);
            foreach ($aAdd as $sValue) {
                if (strlen($sValue) > 0) {
                    $sArg .= '/' . rawurlencode($sValue);
                } else {
                    break;
                }
            }
            $sLink     = $this->_oConfig->getBaseUri() . 'browse' . $sArg;
            $oPaginate = new BxDolPaginate(array(
                'page_url'           => $sLink . '&page={page}&per_page={per_page}',
                'count'              => $oSearch->aCurrent['paginate']['totalNum'],
                'per_page'           => $oSearch->aCurrent['paginate']['perPage'],
                'page'               => $oSearch->aCurrent['paginate']['page'],
                'on_change_per_page' => 'document.location=\'' . BX_DOL_URL_ROOT . $sLink . '&page=1&per_page=\' + this.value;'
            ));
            $sPaginate = $oPaginate->getPaginate();
        } else {
            $sCode = MsgBox(_t('_Empty'));
        }

        if ($sParamName == 'calendar') {
            $sCaption = _t('_' . $this->_oConfig->getMainPrefix() . '_caption_browse_by_day')
                . ': ' . getLocaleDate(strtotime("{$sParamValue}-{$sParamValue1}-{$sParamValue2}")
                    , BX_DOL_LOCALE_DATE_SHORT);
        }
        $aMenu = array();
        $sCode = DesignBoxContent($sCaption, $sCode . $sPaginate, 1,
            $this->_oTemplate->getExtraTopMenu($aMenu, BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri()));
        if ($bAlbumView) {
            $sCode = $this->getAlbumPageView($aAlbumInfo, $sCode);
        }
        $this->aPageTmpl['css_name'] = array('browse.css');
        $this->aPageTmpl['header']   = $sCaption;
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    function actionEdit($iFileId)
    {
        $bAjax   = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;
        $iFileId = (int)$iFileId > 0 ? (int)$iFileId : (int)bx_get('fileId');
        if (!$iFileId || !$bAjax) {
            return;
        }

        $aManageArray = array('medTitle', 'medTags', 'medDesc', 'medProfId', 'Categories', 'medUri');
        $aInfo        = $this->_oDb->getFileInfo(array('fileId' => $iFileId), false, $aManageArray);
        $sLangPref    = '_' . $this->_oConfig->getMainPrefix();

        if (!$this->isAllowedEdit($aInfo)) {
            $sCode = MsgBox(_t($sLangPref . '_access_denied')) . $sJsCode;
        } else {
            $oCategories = new BxDolCategories();
            $oCategories->getTagObjectConfig();
            $aCategories          = $oCategories->getGroupChooser($this->_oConfig->getMainPrefix(), $this->_iProfileId,
                true);
            $aCategories['value'] = explode(CATEGORIES_DIVIDER, $aInfo['Categories']);

            $sUrlPref = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();
            $aForm    = array(
                'form_attrs' => array(
                    'id'       => $sLangPref . '_upload_form',
                    'method'   => 'post',
                    'action'   => $sUrlPref . 'edit/' . $iFileId,
                    'onsubmit' => "return bx_ajax_form_check(this)",
                ),
                'params'     => array(
                    'db'             => array(
                        'submit_name' => 'do_submit',
                    ),
                    'checker_helper' => 'BxSupportCheckerHelper',
                ),
                'inputs'     => array(
                    'header'      => array(
                        'type'    => 'block_header',
                        'caption' => _t('_Info'),
                    ),
                    'title'       => array(
                        'type'     => 'text',
                        'name'     => 'medTitle',
                        'caption'  => _t('_Title'),
                        'required' => true,
                        'checker'  => array(
                            'func'   => 'length',
                            'params' => array(3, 128),
                            'error'  => _t('_td_err_incorrect_length'),
                        ),
                        'value'    => $aInfo['medTitle'],
                    ),
                    'tags'        => array(
                        'type'    => 'text',
                        'name'    => 'medTags',
                        'caption' => _t('_Tags'),
                        'info'    => _t('_Tags_desc'),
                        'value'   => $aInfo['medTags']
                    ),
                    'description' => array(
                        'type'    => 'textarea',
                        'name'    => 'medDesc',
                        'caption' => _t('_Description'),
                        'value'   => $aInfo['medDesc'],
                    ),
                    'categories'  => $aCategories,
                    'fileId'      => array(
                        'type'  => 'hidden',
                        'name'  => 'fileId',
                        'value' => $iFileId,
                    ),
                    'medProfId'   => array(
                        'type'  => 'hidden',
                        'name'  => 'medProfId',
                        'value' => $this->_iProfileId,
                    ),
                    'do_submit'   => array(
                        'type'  => 'hidden',
                        'name'  => 'do_submit', // hidden submit field for AJAX submit
                        'value' => 1,
                    ),
                    'submit'      => array(
                        'type'    => 'submit',
                        'name'    => 'submit_press',
                        'value'   => _t('_Submit'),
                        'colspan' => true,
                    ),
                ),
            );
            $oForm    = new BxTemplFormView($aForm);
            $oForm->initChecker($aInfo);
            if ($oForm->isSubmittedAndValid()) {
                $aValues = array();
                array_pop($aManageArray);
                foreach ($aManageArray as $sKey) {
                    if ($sKey != 'Categories')
                        $aValues[$sKey] = $_POST[$sKey];
                    else
                        $aValues[$sKey] = implode(CATEGORIES_DIVIDER, $_POST[$sKey]);
                }
				if ($this->_oDb->updateData($iFileId, $aValues)) {
					$sType = $this->_oConfig->getMainPrefix();

					bx_import('BxDolCategories');
					$oTag = new BxDolTags();
					$oTag->reparseObjTags($sType, $iFileId);

					$oCateg = new BxDolCategories();
					$oCateg->reparseObjTags($sType, $iFileId);

					$oAlert = new BxDolAlerts($sType, 'change', $iFileId, $this->_iProfileId, array('Info' => $this->_oDb->getFileInfo(array('fileId' => $iFileId), false, $aManageArray)));
					$oAlert->alert();

					$sCode = $GLOBALS['oFunctions']->msgBox(_t($sLangPref . '_save_success'), 3, 'window.location="' . $sUrlPref . 'view/' . $aInfo['medUri'] . '";');

                } 
				else
                    $sCode = $GLOBALS['oFunctions']->msgBox(_t('_sys_save_nothing'));
            }
			else {
                $sCode = $this->_oTemplate->parseHtmlByName('default_padding.html',
                    array('content' => $oForm->getCode()));
                $sCode = $this->_oTemplate->parseHtmlByName('popup.html',
                    array('title' => $aInfo['medTitle'], 'content' => $sCode));
                $sCode = $GLOBALS['oFunctions']->transBox($sCode, true);
            }
        }
        header('Content-type:text/html;charset=utf-8');
        echo $sCode;
        exit;
    }

    function actionRate()
    {
        $sClassPath = $this->_oConfig->getClassPath() . $this->_oConfig->getClassPrefix() . 'Rate.php';
        if (file_exists($sClassPath)) {
            require_once($sClassPath);
            $sClassName                = $this->_oConfig->getClassPrefix() . 'Rate';
            $oPage                     = new $sClassName($this->_oConfig->getMainPrefix());
            $sCode                     = $oPage->getCode();
            $this->aPageTmpl['header'] = _t('_' . $this->_oConfig->getMainPrefix() . '_top_menu_rate');
        } else {
            $sKey  = _t('_sys_request_page_not_found_cpt');
            $sCode = DesignBoxContent($sKey, MsgBox($sKey), 1);
        }
        $this->aPageTmpl['css_name'] = array('search.css', 'browse.css');
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    function actionRss($sParamName, $sParamValue, $sParamValue1, $sParamValue2)
    {
        if ($this->_oConfig->getGlParam('rss_feed_on') == 'on') {
            switch ($sParamName) {
                case 'album':
                    $aUnits     = array();
                    $aAlbumInfo = $this->oAlbums->getAlbumInfo(array(
                        'fileUri' => $sParamValue,
                        'owner'   => getID($sParamValue2)
                    ), array('ID', 'Owner'));
                    if (!empty($aAlbumInfo)) {
                        $aFileCopycat = array(
                            'albumId'   => $aAlbumInfo['ID'],
                            'medProfId' => $aAlbumInfo['Owner'],
                            'Approved'  => 'approved',
                        );
                        if ($this->isAllowedView($aFileCopycat)) {
                            $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
                            bx_import('Search', $this->_aModule);
                            $oSearch                                  = new $sClassName();
                            $oSearch->aCurrent['paginate']['perPage'] = 1000;
                            $aUnits                                   = $oSearch->serviceGetFilesInAlbum($aAlbumInfo['ID']);
                        }
                    }
                    $sCode = $this->_oTemplate->getAlbumFeed($aUnits);
                    break;
            }
            header('Content-Type: text/xml; charset=UTF-8');
            echo $sCode;
        }
    }

    function actionReport($sFileUri)
    {
        $sLangPref = '_' . $this->_oConfig->getMainPrefix() . '_';
        $sFileUrl  = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $sFileUri;
        $aForm     = $this->getSubmitForm($sFileUri, 'report');
        $oForm     = new BxTemplFormView($aForm);
        $oForm->initChecker();
        if ($oForm->isSubmittedAndValid()) {
            if ($this->sendFileInfo($GLOBALS['site']['email'], nl2br(process_pass_data($_POST['messageText'])),
                $sFileUrl, $_POST['mediaAction'])
            ) {
                $sCode = $GLOBALS['oFunctions']->msgBox(_t('_File info was sent'));
            } else {
                $sCode = $GLOBALS['oFunctions']->msgBox(_t('_Error'));
            }
        } else {
            $sCode = $this->_oTemplate->parseHtmlByName('default_padding.html', array('content' => $oForm->getCode()));
            $sCode = $this->_oTemplate->parseHtmlByName('popup.html',
                array('title' => _t($sLangPref . 'action_report'), 'content' => $sCode));
            $sCode = $GLOBALS['oFunctions']->transBox($sCode, true);
        }
        header('Content-type:text/html;charset=utf-8');
        echo $sCode;
        exit;
    }

    function actionShare($sFileUri)
    {
        $sLangPref = '_' . $this->_oConfig->getMainPrefix() . '_';
        $sFileUrl  = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $sFileUri;
        $aForm     = $this->getSubmitForm($sFileUri, 'share');
        $oForm     = new BxTemplFormView($aForm);
        $oForm->initChecker();
        if ($oForm->isSubmittedAndValid()) {
            if ($this->sendFileInfo($_POST['email'], nl2br(process_pass_data($_POST['messageText'])), $sFileUrl,
                $_POST['mediaAction'])
            ) {
                $sCode = $GLOBALS['oFunctions']->msgBox(_t('_File info was sent'));
            } else {
                $sCode = $GLOBALS['oFunctions']->msgBox(_t('_Error'));
            }
        } else {
            $sCode = $this->_oTemplate->parseHtmlByName('default_padding.html', array('content' => $oForm->getCode()));
            $sCode = $this->_oTemplate->parseHtmlByName('popup.html',
                array('title' => _t('_Share'), 'content' => $sCode));
            $sCode = $GLOBALS['oFunctions']->transBox($sCode, true);
        }
        header('Content-type:text/html;charset=utf-8');
        echo $sCode;
        exit;
    }

    function actionFavorite($iFileId)
    {
        if (!$this->_oDb->checkFavoritesIn($iFileId)) {
            $sMessPost = 'add';
            $this->_oDb->addToFavorites($iFileId);
        } else {
            $sMessPost = 'remove';
            $this->_oDb->removeFromFavorites($iFileId);
        }
        $sJQueryJS = genAjaxyPopupJS($iFileId);
        header('Content-Type: text/html; charset=UTF-8');
        echo MsgBox(_t('_' . $this->_oConfig->getMainPrefix() . '_fav_' . $sMessPost)) . $sJQueryJS;
        exit;
    }

    function actionFeature($iFileId, $iFeatureId = 0)
    {
        $iFileId = (int)$iFileId;
        if ($iFileId > 0 && isAdmin($this->_iProfileId)) {
            $iFeatureId = (int)$iFeatureId;
            if ($iFeatureId > 0) {
                $this->adminMakeUnfeatured($iFileId);
            } else {
                $this->adminMakeFeatured($iFileId);
            }
            $sJQueryJS = genAjaxyPopupJS($iFileId);
            header('Content-Type: text/html; charset=UTF-8');
            echo MsgBox(_t('_Saved')) . $sJQueryJS;
            exit;
        }
    }

    function actionApprove($iFileId, $iApprove = 1)
    {
        $iFileId = (int)$iFileId;
        if ($iFileId) {
            $iApprove  = (int)$iApprove;
            $aFile     = array('medID' => $iFileId, 'medProfId' => 0);
            $sJQueryJS = genAjaxyPopupJS($iFileId);
            if ($this->isAllowedApprove($aFile)) {
                if ($iApprove) {
                    $this->adminApproveFile($iFileId);
                } else {
                    $this->adminDisapproveFile($iFileId);
                }
                $this->isAllowedApprove($aFile, true);
                $sMsg = '_Saved';
            } else {
                $sMsg = '_Access denied';
            }
            header('Content-Type: text/html; charset=UTF-8');
            echo MsgBox(_t($sMsg)) . $sJQueryJS;
            exit;
        }
    }

    function actionDelete($iFileId, $sAlbumUri = '', $sOwnerNick = '')
    {
        $iFileId   = (int)$iFileId;
        $sJQueryJS = '';
        $sLangKey  = '_' . $this->_oConfig->getMainPrefix() . '_delete';
        if ($this->_deleteFile($iFileId)) {
            $sRedirectMain = 'albums/my/main/';
            if (!empty($sAlbumUri)) {
                $sAlbumUri     = clear_xss($sAlbumUri);
                $sOwnerNick    = clear_xss($sOwnerNick);
                $sRedirectMain = 'browse/album/' . $sAlbumUri . '/owner/' . $sOwnerNick;
            }
            $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . $sRedirectMain;
            $sJQueryJS = genAjaxyPopupJS($iFileId, 'ajaxy_popup_result_div', $sRedirect);
        } else {
            $sLangKey .= '_error';
        }
        header('Content-Type: text/html; charset=UTF-8');
        echo MsgBox(_t($sLangKey)) . $sJQueryJS;
        exit;
    }

    function actionAlbums(
        $sParamName = '',
        $sParamValue = '',
        $sParamValue1 = '',
        $sParamValue2 = '',
        $sParamValue3 = ''
    ) {
        if ($sParamName == 'my') {
            $this->actionAlbumsViewMy($sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);

            return;
        }

        if ($sParamName == 'browse' && $sParamValue == 'all') {
            $sContent = $this->getAlbumPageBrowse(array(
                $sParamName,
                $sParamValue,
                $sParamValue1,
                $sParamValue2,
                $sParamValue3
            ));
            $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sContent));

            return;
        }

        if ($sParamName == 'browse' && $sParamValue == 'owner') {
            $iIdOwner = getID($sParamValue1);
            if (!$iIdOwner) {
                $this->_oTemplate->displayPageNotFound();
                exit;
            }
            $GLOBALS['oTopMenu']->setCurrentProfileID($iIdOwner);
            $this->aPageTmpl['header'] = _t('_' . $this->_oConfig->getMainPrefix() . '_browse_by_owner', $sParamValue1);
        }

        $sClassName = $this->_oConfig->getClassPrefix() . 'PageAlbumsOwner';
        bx_import('PageAlbumsOwner', $this->_aModule);
        $oPage = new $sClassName($this, array($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3));
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $oPage->getCode()));
    }

    function actionAlbumsViewMy($sParamValue = '', $sParamValue1 = '', $sParamValue2 = '', $sParamValue3 = '')
    {
        $sAction = bx_get('action');
        if ($sAction !== false) {
            header('Content-Type: text/html; charset=UTF-8');

            if (!isLogged() && bx_get('oid') && bx_get('pwd')) { // in case of request from flash, cookies are not passed, and we have to set it explicitly
                $_COOKIE['memberID']       = bx_get('oid');
                $_COOKIE['memberPassword'] = bx_get('pwd');
                check_logged();
            }

            if (!isLogged()) {
                echo MsgBox(_t('_Access denied'));
                exit;
            }

            $sUpl = 'Uploader';
            bx_import($sUpl, $this->_aModule);
            $sClassName = $this->_oConfig->getClassPrefix() . $sUpl;
            $oUploader  = new $sClassName();
            $this->processUpload($oUploader, $sAction);
            exit;
        }
        $bNotAllowView = $this->_iProfileId == 0 || !isLoggedActive();
        $aAlbumInfo    = array();
        if (!$bNotAllowView && !empty($sParamValue1)) {
            $aAlbumInfo = $this->oAlbums->getAlbumInfo(array(
                'fileUri' => $sParamValue1,
                'owner'   => $this->_iProfileId
            ));
            if (!empty($aAlbumInfo)) {
                $bNotAllowView = $aAlbumInfo['AllowAlbumView'] == BX_DOL_PG_HIDDEN;
            }
        }
        if ($bNotAllowView) {
            $sKey  = _t('_' . $this->_oConfig->getMainPrefix() . '_access_denied');
            $sCode = DesignBoxContent($sKey, MsgBox($sKey), 1);
            $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode), '', '', false);

            return;
        }

        //album actions check
        if (is_array($_POST['entry'])) {
            foreach ($_POST['entry'] as $iValue) {
                $iValue = (int)$iValue;
                switch (true) {
                    case isset($_POST['action_delete']):
                        $iCount = $this->_deleteAlbumUnits($iValue);
                        if ($iCount == 0) {
                            $this->oAlbums->removeAlbum($iValue);
                        }
                        break;

                    case isset($_POST['action_move_to']):
                        $this->oAlbums->moveObject((int)$_POST['album_id'], (int)$_POST['new_album'], $iValue);
                        break;

                    case isset($_POST['action_delete_object']):
                        $this->_deleteFile($iValue);
                        break;
                }
            }
        }

        $sCode = '';
        switch ($sParamValue) {
            case 'main':
                bx_import('PageAlbumsOwner', $this->_aModule);
                $sClassName = $this->_oConfig->getClassPrefix() . 'PageAlbumsOwner';
                $oPage      = new $sClassName($this, array('browse', 'owner', getUsername($this->_iProfileId)));
                $sCode .= $oPage->getCode();
                break;

            case 'main_objects':
                $sCode .= $this->getAlbumPageView($aAlbumInfo);
                break;
        }

        bx_import('PageAlbumsMy', $this->_aModule);
        $sClassName = $this->_oConfig->getClassPrefix() . 'PageAlbumsMy';
        $oPage      = new $sClassName($this, $this->_iProfileId,
            array($sParamValue, $sParamValue1, $sParamValue2, $sParamValue3));

        $sClassPostfix = $oPage->getViewLevel() == 0 ? 'PageAlbumsOwner' : 'PageAlbumView';
        bx_import($sClassPostfix, $this->_aModule);
        $sClassName     = $this->_oConfig->getClassPrefix() . $sClassPostfix;
        $oPageViewOwner = new $sClassName($this, array());
        $iPageWidth     = $oPageViewOwner->getPageWidth();
        if ($iPageWidth != 0) {
            $oPage->forcePageWidth($iPageWidth);
        }

        $GLOBALS['oTopMenu']->setCurrentProfileID($this->_iProfileId);
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $oPage->getCode() . $sCode), '', '',
            false);
    }

    function actionAlbumOrganize($sAlbumUri)
    {
        $aSort = $_POST['unit'];
        $this->oAlbums->sortObjects($sAlbumUri, $aSort);
    }

    function actionAlbumReverse($sAlbumUri)
    {
        $this->oAlbums->sortObjects($sAlbumUri);

        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch                                                = new $sClassName('album', $sAlbumUri, 'owner',
            getUsername($this->_iProfileId));
        $oSearch->bAdminMode                                    = false;
        $oSearch->aCurrent['view']                              = 'short';
        $oSearch->aCurrent['restriction']['album']['value']     = $sAlbumUri;
        $oSearch->aCurrent['restriction']['albumType']['value'] = $oSearch->aCurrent['name'];
        $oSearch->aCurrent['paginate']['perPage']               = 1000;
        $aUnits                                                 = $oSearch->getSearchData();
        if (is_array($aUnits)) {
            foreach ($aUnits as $aData) {
                $sCode .= $oSearch->displaySearchUnit($aData);
            }
        }
        header('Content-Type: text/html; charset=UTF-8');
        echo $sCode . '<div class="clear_both"></div>';
    }

    function actionAlbumDelete($sAlbumUri)
    {
        $sLangPref  = '_' . $this->_oConfig->getMainPrefix();
        $aAlbumInfo = $this->oAlbums->getAlbumInfo(array('fileUri' => $sAlbumUri));
        if (!$this->isAllowedDeleteAlbum($aAlbumInfo['ID'], $aAlbumInfo)) {
            $sMessage = _t($sLangPref . '_access_denied');
        } else {
            $iCount = $this->_deleteAlbumUnits($aAlbumInfo['ID']);
            if ($iCount > 0) {
                $sMessage = _t($sLangPref . '_album_delete_error', $iCount);
            } else {
                $sMessage = _t($sLangPref . '_album_delete_success');
                $this->oAlbums->removeAlbum($aAlbumInfo['ID']);
                $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'albums/';
                if ($this->_iProfileId != $aAlbumInfo['Owner']) {
                    $sRedirect .= 'browse/all/';
                } else {
                    $sRedirect .= 'my/main/';
                }
                $sJQueryJS = genAjaxyPopupJS($aAlbumInfo['ID'], 'ajaxy_popup_result_div', $sRedirect);
            }
        }
        header('Content-Type: text/html; charset=UTF-8');
        echo MsgBox($sMessage) . $sJQueryJS;
        exit;
    }

    function actionCalendar($iYear = '', $iMonth = '')
    {
        $sClassName = $this->_oConfig->getClassPrefix() . 'Calendar';
        bx_import('Calendar', $this->_aModule);
        $oCalendar                 = new $sClassName($iYear, $iMonth, $this->_oDb, $this->_oTemplate, $this->_oConfig);
        $sTitle                    = _t('_' . $this->_oConfig->getMainPrefix() . '_top_menu_calendar');
        $sCode                     = DesignBoxContent($sTitle, $oCalendar->display(), 1);
        $this->aPageTmpl['header'] = $sTitle;
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    /**
     * Get quick upload form in popup.
     *
     * @param string $sSelected - album URI (optional)
     */
    function actionUpload($sSelected = '')
    {
        header('Content-Type: text/html; charset=UTF-8');

        $sLangPref = '_' . $this->_oConfig->getMainPrefix();
        if (!$this->_iProfileId || !$this->isAllowedAdd()) {
            $sKey = _t($sLangPref . '_access_denied');
            echo DesignBoxContent($sKey, MsgBox($sKey), 1);
            exit;
        }

        $this->checkDefaultAlbums($this->_iProfileId);

        $aAlbumParams = array('owner' => $this->_iProfileId, 'show_empty' => true, 'hide_default' => true);
        $iAlbumsCount = $this->oAlbums->getAlbumCount($aAlbumParams);

        $aAlbums = array();
        if ($iAlbumsCount) {
            $aAlbumsList = $this->oAlbums->getAlbumList($aAlbumParams, 1, $iAlbumsCount);
            foreach ($aAlbumsList as $aAlbum) {
                $aAlbums[$aAlbum['ID']] = $aAlbum['Caption'];
            }

            if (!empty($sSelected)) {
                $aAlbum = $this->oAlbums->getAlbumInfo(array('fileuri' => $sSelected), array('ID', 'Uri'));

                $sSelected = !empty($aAlbum) && is_array($aAlbum) ? (int)$aAlbum['ID'] : '';
            }
        } else {
            $aDefaultAlbums = $this->_oConfig->getDefaultAlbums(true,
                array('{nickname}' => getUsername($this->_iProfileId)));
            foreach ($aDefaultAlbums as $sDefaultAlbum) {
                $aAlbums[$sDefaultAlbum] = $sDefaultAlbum;
            }
        }

        $aPrivFieldView = $this->oAlbumPrivacy->getGroupChooser($this->_iProfileId, $this->_oConfig->getUri(),
            'album_view', array(), _t($sLangPref . '_album_view'));
        $aForm          = $this->getInstanceUploadFormArray($aAlbums, $aPrivFieldView);
        if (!empty($sSelected)) {
            $aForm['inputs']['albums']['value'] = $sSelected;

            $aForm['inputs']['title']['tr_attrs']['style']      = 'display:none';
            $aForm['inputs']['allow_view']['tr_attrs']['style'] = 'display:none';
        }
        $oForm = new BxTemplFormView($aForm);

        $sCode = $this->_oTemplate->parseHtmlByName('popup.html', array(
            'title'   => _t($sLangPref . '_upload_instance'),
            'content' => $this->_oTemplate->parseHtmlByName('default_padding.html', array(
                'content' => $this->_oTemplate->addJs(array('albums.js'), true) . $oForm->getCode()
            ))
        ));

        echo $GLOBALS['oFunctions']->transBox($sCode, true);
        exit;
    }

    function actionUploadSubmit()
    {
        header('Content-Type:text/javascript; charset=utf-8');

        $mixedAlbum = bx_get('album');
        if (is_numeric($mixedAlbum)) {
            $iAlbumId = (int)$mixedAlbum;
            if ($iAlbumId == 0) {
                $sTitle = clear_xss(bx_get('title'));
                if (empty($sTitle)) {
                    echo json_encode(array('status' => 'Fail', 'error_msg' => _t('_title_min_lenght', 1)));
                    exit;
                }

                $aNew     = array(
                    'caption'        => $sTitle,
                    'AllowAlbumView' => (int)bx_get('AllowAlbumView'),
                    'owner'          => $this->_iProfileId,
                );
                $iAlbumId = $this->oAlbums->addAlbum($aNew);
            }

            $aAlbumInfo = $this->oAlbums->getAlbumInfo(array('fileid' => $iAlbumId), array('Uri', 'Owner'));
            if (!empty($aAlbumInfo) && $aAlbumInfo['Owner'] == $this->_iProfileId) {
                $mixedAlbum = $aAlbumInfo['Uri'];
            }
        } else {
            $mixedAlbum = uriFilter(clear_xss($mixedAlbum));
        }

        $sOwnerNick = getUsername($this->_iProfileId);
        echo json_encode(array('status' => 'OK', 'album_uri' => $mixedAlbum, 'owner_name' => $sOwnerNick));
        exit;
    }

    function isAllowedAdd($isPerformAction = false, $isNotDefineActions = false, $isCheckMemberStatus = true)
    {
        if ($this->isAdmin($this->_iProfileId)) {
            return true;
        }
        if (!isMember($this->_iProfileId)) {
            return false;
        }
        if (!$isDefineActions) {
            $this->_defineActions();
        }
        $aCheck = checkAction($this->_iProfileId, $this->_defineActionName('add'), $isPerformAction, 0,
            $isCheckMemberStatus);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedApprove($aFile, $isPerformAction = false)
    {
        if (in_array($aFile['Approved'], array('pending', 'processing', 'failed'))) {
            return false;
        } elseif ($this->isAdmin($this->_iProfileId)) {
            return true;
        } elseif ($aFile['medProfId'] == $this->_iProfileId) {
            return false;
        }

        $this->_defineActions();
        $aCheck = checkAction($this->_iProfileId, $this->_defineActionName('approve'), $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedEdit(&$aFile, $isPerformAction = false)
    {
        if ($this->isAdmin($this->_iProfileId)) {
            return true;
        }
        if ($aFile['medProfId'] == $this->_iProfileId) {
            return true;
        } else {
            if (!isMember($this->_iProfileId)) {
                return false;
            }
            $this->_defineActions();
            $aCheck = checkAction($this->_iProfileId, $this->_defineActionName('edit'), $isPerformAction);

            return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
        }
    }

    function isAllowedDelete(&$aFile, $isPerformAction = false)
    {
        if ($this->isAdmin($this->_iProfileId) || $aFile['medProfId'] == $this->_iProfileId) {
            return true;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iProfileId, $this->_defineActionName('delete'), $isPerformAction);
        if ($aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED) {
            return true;
        }

        return false;
    }

    function isAllowedDeleteAlbum($iAlbumId, $aAlbumInfo = null)
    {
        $iAlbumId = (int)$iAlbumId;
        if ($this->isAdmin($this->_iProfileId)) {
            return true;
        }
        $aAlbumInfo = is_null($aAlbumInfo) ? $this->oAlbums->getAlbumInfo(array('fileid' => $iAlbumId)) : $aAlbumInfo;
        if ($aAlbumInfo['Owner'] == $this->_iProfileId) {
            return true;
        }

        return false;
    }

    function isAllowedView(&$aFile, $isPerformAction = false)
    {
        $bAdmin = $this->isAdmin($this->_iProfileId);
        if ($bAdmin || $aFile['medProfId'] == $this->_iProfileId) {
            return true;
        }
        if (!$bAdmin && $aFile['Approved'] != 'approved') {
            return false;
        }
        $aOwnerInfo = getProfileInfo($aFile['medProfId']);
        if ($aOwnerInfo['Status'] == 'Rejected' || $aOwnerInfo['Status'] == 'Suspended') {
            return false;
        }
        if (!$this->oAlbumPrivacy->check('album_view', $aFile['albumId'], $this->_iProfileId)) {
            return false;
        }
        $this->_defineActions();
        $aCheck = checkAction($this->_iProfileId, $this->_defineActionName('view'), $isPerformAction);
        if ($aCheck[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED) {
            return false;
        }

        return true;
    }

    function isAdmin($iId = 0)
    {
        if (isAdmin($iId)) {
            return true;
        } else {
            return isModerator($iId);
        }
    }

    function adminApproveFile($iFileId)
    {
        $iFileId = (int)$iFileId;
        $aInfo   = $this->_oDb->getFileInfo(array('fileId' => $iFileId), true, array('Approved'));
        if ($aInfo['Approved'] != 'approved') {
            $this->_oDb->approveFile($iFileId);
            $this->oAlbums->updateObjCounterById($iFileId);
            bx_import('BxDolCategories');
            //tags & categories parsing
            $oTag = new BxDolTags();
            $oTag->reparseObjTags($this->_oConfig->getMainPrefix(), $iFileId);
            $oCateg = new BxDolCategories();
            $oCateg->reparseObjTags($this->_oConfig->getMainPrefix(), $iFileId);
        }
    }

    function adminDisapproveFile($iFileId)
    {
        $iFileId = (int)$iFileId;
        $aInfo   = $this->_oDb->getFileInfo(array('fileId' => $iFileId), true, array('Approved'));
        $this->_oDb->disapproveFile($iFileId);
        if ($aInfo['Approved'] == 'approved') {
            $this->oAlbums->updateObjCounterById($iFileId, false);
            bx_import('BxDolCategories');
            //tags & categories parsing
            $oTag = new BxDolTags();
            $oTag->reparseObjTags($this->_oConfig->getMainPrefix(), $iFileId);
            $oCateg = new BxDolCategories();
            $oCateg->reparseObjTags($this->_oConfig->getMainPrefix(), $iFileId);
        }
    }

    function adminMakeFeatured($iFileId)
    {
        $this->_oDb->makeFeatured($iFileId);
    }

    function adminMakeUnfeatured($iFileId)
    {
        $this->_oDb->makeUnFeatured($iFileId);
    }

    function checkActions()
    {
        $aActionList = $this->_oConfig->getActionArray();
        if (!is_array($_POST['entry'])) {
            return;
        }
        foreach ($aActionList as $sKey => $aValue) {
            if (isset($_POST[$sKey]) && method_exists($this, $aValue['method'])) {
                foreach ($_POST['entry'] as $iValue) {
                    $sComm = '$this->' . $aValue['method'] . '(' . (int)$iValue . ');';
                    eval($sComm);
                }
                break;
            }
        }
    }

    function checkDefaultAlbums($iProfileId)
    {
        $sUri = $this->_oConfig->getUri();

        $aAlbums = $this->_oConfig->getDefaultAlbums(true, array('{nickname}' => getUsername($iProfileId)));
        foreach ($aAlbums as $sAlbum) {
            $aAlbumInfo = $this->oAlbums->getAlbumInfo(array('fileUri' => uriFilter($sAlbum), 'owner' => $iProfileId));
            if (!empty($aAlbumInfo) && is_array($aAlbumInfo)) {
                continue;
            }

            $this->oAlbums->addAlbum(array(
                'caption'        => $sAlbum,
                'owner'          => $iProfileId,
                'AllowAlbumView' => ($sAlbum == getParam('sys_album_default_name')) ? BX_DOL_PG_HIDDEN : $this->oAlbumPrivacy->_oDb->getDefaultValueModule($sUri,
                    'album_view'),
            ), false);
        }
    }

    function getMemberList()
    {
        $sCode = '';
        if (isset($_GET['q'])) {
            $aMemList = $this->_oDb->getMemberList($_GET['q']);
            if (count($aMemList) > 0) {
                foreach ($aMemList as $aData) {
                    $sCode .= $aData['NickName'] . " \n";
                }
            }
        }

        return $sCode;
    }

    function getAdminMainPage(&$aMenu, $sParam = '')
    {
        $GLOBALS['oAdmTemplate']->addLocation($this->_oConfig->getUri(), $this->_oConfig->getHomePath(),
            $this->_oConfig->getHomeUrl());
        $sModPref = $this->_oConfig->getMainPrefix();

        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch = new $sClassName();
        $oSearch->clearFilters(array(), array('albumsObjects', 'albums'));
        $oSearch->bAdminMode                      = true;
        $oSearch->id                              = 1;
        $oSearch->aCurrent['paginate']['perPage'] = (int)getParam($sModPref . '_all_count');

        $aSections = $this->aSectionsAdmin;
        $bAjaxMode = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;

        $sParam = clear_xss($sParam);
        if (mb_strlen($sParam) == 0 || !isset($aSections[$sParam])) {
            $sParam = 'approved';
        }

        $oSearch->aCurrent['restriction']['activeStatus']['value']   = $sParam;
        $oSearch->aCurrent['restriction']['not_allow_view']['value'] = BX_DOL_PG_HIDDEN;
        $oSearch->aCurrent['restriction']['albumType']['value']      = $sModPref;
        $aSections[$sParam]['active']                                = 1;

        // array of buttons
        $aBtnsArray = $this->_oConfig->getActionArray();
        // making search result box menu
        if ($aSections[$sParam]['exclude_btns'] == 'all') {
            $aBtnsArray = array();
        } elseif (is_array($aSections[$sParam]['exclude_btns'])) {
            foreach ($aSections[$sParam]['exclude_btns'] as $sValue) {
                unset($aBtnsArray['action_' . $sValue]);
            }
        }

        foreach ($aSections as $sKey => $aValue) {
            $aSections[$sKey]['href']  = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/home/' . $sKey;
            $aSections[$sKey]['title'] = _t('_' . $sModPref . '_' . $sKey);
        }
        if (!empty($aBtnsArray)) {
            $aBtns = array();
            foreach ($aBtnsArray as $sKey => $aValue) {
                $aBtns[$sKey] = _t($aValue['caption']);
            }
            $sManage = $oSearch->showAdminActionsPanel($oSearch->aCurrent['name'] . '_admin_form', $aBtns);
        } else {
            $sManage             = '';
            $oSearch->bAdminMode = false;
        }

        if ($bAjaxMode) {
            $oSearch->aCurrent['restriction']['activeStatus']['value'] = process_db_input($sParam, BX_TAGS_STRIP);
            $sPostOwner                                                = bx_get('owner');
            $sOwner                                                    = $sPostOwner !== false ? process_db_input($sPostOwner,
                BX_TAGS_STRIP) : '';
            if (strlen($sOwner) > 0) {
                $oSearch->aCurrent['restriction']['owner']['value'] = getID($sOwner);
            }
            $sCode = $oSearch->displayResultBlock();
            $aCode = $this->getResultCodeArray($oSearch, $sCode);
            header('Content-Type: text/html; charset=UTF-8');
            echo $this->_oTemplate->getFilesBox($aCode);
            exit;
        }
        $aInputs['status'] = array('type' => 'hidden');
        $aUnits            = array(
            'head'          => $this->_oTemplate->getHeaderCode(),
            'module_prefix' => $sModPref,
            'search_form'   => DesignBoxAdmin(_t('_' . $sModPref . '_admin'),
                $this->_oTemplate->getSearchForm($aInputs), $aMenu, '', 11),
        );

        $sCode           = $oSearch->displayResultBlock();
        $aCode           = $this->getResultCodeArray($oSearch, $sCode);
        $sCode           = $this->_oTemplate->getFilesBox($aCode, 'page_block_' . $oSearch->id);
        $aUnits['files'] = DesignBoxAdmin(_t('_' . $sModPref), $sCode, $aSections, $sManage);

        return $this->_oTemplate->parseHtmlByName('media_admin.html', $aUnits);
    }

    function getAdminSettings(&$aMenu)
    {
        $iId = $this->_oDb->getSettingsCategory();
        if (empty($iId)) {
            return MsgBox(_t('_' . $this->_oConfig->getMainPrefix() . '_msg_page_not_found'));
        }
        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if (isset($_POST['save']) && isset($_POST['cat'])) {
            $oSettings   = new BxDolAdminSettings($iId);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        $oSettings = new BxDolAdminSettings($iId);
        $sResult   = $oSettings->getForm();

        if ($mixedResult !== true && !empty($mixedResult)) {
            $sResult = $mixedResult . $sResult;
        }

        return DesignBoxAdmin(_t('_' . $this->_oConfig->getMainPrefix() . '_admin'),
            $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $sResult)), $aMenu);
    }

    function getAlbumPageView($aInfo, $sBrowseCode = '')
    {
        $sClassName = $this->_oConfig->getClassPrefix() . 'PageAlbumView';
        bx_import('PageAlbumView', $this->_aModule);
        $oAlbumPage = new $sClassName($this, $aInfo, $sBrowseCode);
        $sCode      = $oAlbumPage->getCode();
        if (empty($sCode)) {
            $sCode = $sBrowseCode;
        }

        return $sCode;
    }

    function getAlbumPageBrowse($aParams)
    {
        list($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3) = $aParams;
        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch = new $sClassName($sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);
        if (!empty($sParamValue) && !empty($sParamValue1) && isset($oSearch->aCurrent['restriction'][$sParamValue])) {
            $oSearch->aCurrent['restriction'][$sParamValue]['value'] = 'owner' == $sParamValue ? getID($sParamValue1) : $sParamValue1;
        }

        $oSearch->aCurrent['paginate']['perPage'] = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (int)$this->_oConfig->getGlParam('number_albums_browse');
        $oSearch->aCurrent['paginate']['page']    = isset($_GET['page']) ? (int)$_GET['page'] : $oSearch->aCurrent['paginate']['page'];
        $sCode                                    = $oSearch->getAlbumList($oSearch->aCurrent['paginate']['page'],
            $oSearch->aCurrent['paginate']['perPage'], array('hide_default' => true));
        if ($oSearch->aCurrent['paginate']['totalAlbumNum'] > 0) {
            $aAdd = array($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);
            foreach ($aAdd as $sValue) {
                if (strlen($sValue) > 0) {
                    $sArg .= '/' . rawurlencode($sValue);
                } else {
                    break;
                }
            }
            $sLink     = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'albums' . $sArg;
            $oPaginate = new BxDolPaginate(array(
                'page_url'           => $sLink . '&page={page}&per_page={per_page}',
                'count'              => $oSearch->aCurrent['paginate']['totalAlbumNum'],
                'per_page'           => $oSearch->aCurrent['paginate']['perPage'],
                'page'               => $oSearch->aCurrent['paginate']['page'],
                'on_change_per_page' => 'document.location=\'' . $sLink . '&page=1&per_page=\' + this.value;'
            ));
            $sPaginate = $oPaginate->getPaginate();
        } else {
            $sCode = MsgBox(_t('_Empty'));
        }

        return DesignBoxContent(_t('_' . $this->_oConfig->getMainPrefix() . '_albums'), $sCode, 11, '', $sPaginate);
    }

    function getBlockActionsAlbum($aAlbumInfo)
    {
        $sAction      = $this->_oConfig->getMainPrefix() . '_album';
        $aReplacement = array(
            'ID'        => $aAlbumInfo['ID'],
            'albumUri'  => $aAlbumInfo['Uri'],
            'moduleUrl' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(),
        );

        return $GLOBALS['oFunctions']->genObjectsActions($aReplacement, $sAction);
    }

    function getBrowseParam($sParamName, $sParamValue)
    {
        $aPredef = array('tag', 'category');

        return in_array($sParamName, $aPredef) ? uri2title($sParamValue) : $sParamValue;
    }

    function getInstanceUploadAlbumTempName($aAlbums, $iAttempt = 1)
    {
        $sTemp    = getLocaleDate(time());
        $sNewName = $iAttempt > 1 ? _t('_sys_album_caption_new', $sTemp, $iAttempt) : $sTemp;
        if (in_array($sNewName, $aAlbums)) {
            $iAttempt++;
            $sNewName = $this->getInstanceUploadAlbumTempName($aAlbums, $iAttempt);
        }

        return $sNewName;
    }

    function getInstanceUploadFormArray(
        $aAlbums,
        $aPrivFieldView,
        $sAlbumsCaption = false,
        $sAlbumTitleCaption = false,
        $sCreateNewAlbumCaption = false
    ) {
        $aAlbums[0] = $sCreateNewAlbumCaption ? $sCreateNewAlbumCaption : _t('_sys_album_create_new');
        ksort($aAlbums);

        $aForm = array(
            'form_attrs' => array(
                'id'       => '_' . $this->_oConfig->getMainPrefix() . '_album_form',
                'method'   => 'post',
                'onSubmit' => 'return submit_quick_upload_form("' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . '", $(this).serialize());'
            ),
            'params'     => array(
                'db'             => array(
                    'submit_name' => 'submit',
                ),
                'checker_helper' => 'BxSupportCheckerHelper',
            ),
            'inputs'     => array(
                'albums'     => array(
                    'type'    => 'select',
                    'caption' => $sAlbumsCaption ? $sAlbumsCaption : _t('_sys_album'),
                    'name'    => 'album',
                    'values'  => $aAlbums,
                    'attrs'   => array(
                        'onchange' => 'check_album_name_for_fields(this)'
                    )
                ),
                'title'      => array(
                    'type'     => 'text',
                    'name'     => 'title',
                    'caption'  => $sAlbumTitleCaption ? $sAlbumTitleCaption : _t('_sys_album_caption_capt'),
                    'required' => true,
                    'value'    => $this->getInstanceUploadAlbumTempName($aAlbums),
                ),
                'allow_view' => $aPrivFieldView,
                'submit'     => array(
                    'type'  => 'submit',
                    'name'  => 'submit',
                    'value' => _t('_Continue'),
                ),
            ),
        );

        return $aForm;
    }

    function getResultCodeArray(&$oSearch, $sCode)
    {
        $aCode  = array(
            'code'     => MsgBox(_t('_Empty')),
            'paginate' => ''
        );
        $iCount = $oSearch->aCurrent['paginate']['totalNum'];
        if ($iCount > 0) {
            $aCode['code'] = $GLOBALS['oFunctions']->centerContent($sCode, '.sys_file_search_unit');
            $sLink         = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/home/' . $oSearch->aCurrent['restriction']['activeStatus']['value'];
            $sKeyWord      = bx_get('keyword');
            if ($sKeyWord !== false) {
                $sLink .= '&keyword=' . clear_xss($sKeyWord);
            }
            $aExclude          = array('r');
            $aLinkAddon        = $oSearch->getLinkAddByPrams($aExclude);
            $oPaginate         = new BxDolPaginate(array(
                'page_url'           => $sLink,
                'count'              => $iCount,
                'per_page'           => $oSearch->aCurrent['paginate']['perPage'],
                'page'               => $oSearch->aCurrent['paginate']['page'],
                'on_change_page'     => 'return !loadDynamicBlock(' . $oSearch->id . ', \'' . $sLink . $aLinkAddon['params'] . $aLinkAddon['paginate'] . '\');',
                'on_change_per_page' => 'return !loadDynamicBlock(' . $oSearch->id . ', \'' . $sLink . $aLinkAddon['params'] . '&page=1&per_page=\' + this.value);'
            ));
            $aCode['paginate'] = $oPaginate->getPaginate();
        }

        return $aCode;
    }

    function getSubmitForm($sFileUri, $sAction)
    {
        $aEmails   = array();
        $sFileLink = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $sFileUri;
        switch ($sAction) {
            case 'share':
                $aEmails     = array(
                    'type'     => 'text',
                    'name'     => 'email',
                    'caption'  => _t("_Enter email(s)"),
                    'required' => true,
                    'checker'  => array(
                        'func'  => 'emailSet',
                        'error' => _t("_Incorrect Email")
                    ),
                );
                $aShareSites = array(
                    'type'    => 'custom',
                    'colspan' => 2,
                    'content' => $this->_oTemplate->getSitesSetBox($sFileLink),
                );
                break;
            case 'report':
                $aEmails     = array(
                    'type'  => 'hidden',
                    'name'  => 'email',
                    'value' => $GLOBALS['site']['email_notify']
                );
                $aShareSites = array(
                    'type'    => 'custom',
                    'content' => ''
                );
                break;
        }
        $aForm = array(
            'form_attrs' => array(
                'name'     => 'submitAction',
                'action'   => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . $sAction . '/' . $sFileUri,
                'method'   => 'post',
                'onsubmit' => "return bx_ajax_form_check(this)",
            ),
            'params'     => array(
                'db'             => array(
                    'submit_name' => 'do_submit',
                ),
                'checker_helper' => 'BxSupportCheckerHelper',
            ),
            'inputs'     => array(
                'sites'       => $aShareSites,
                'email'       => $aEmails,
                'message'     => array(
                    'type'     => 'textarea',
                    'name'     => 'messageText',
                    'caption'  => _t('_Message text'),
                    'value'    => '',
                    'required' => 1,
                    'checker'  => array(
                        'func'   => 'length',
                        'params' => array(3, 65536),
                        'error'  => _t('_td_err_incorrect_length'),
                    ),
                ),
                array(
                    'type' => 'input_set',
                    0      => array(
                        'type'  => 'submit',
                        'name'  => 'send',
                        'value' => _t('_Send')
                    ),
                    1      => array(
                        'type'  => 'reset',
                        'name'  => 'rest',
                        'value' => _t('_Reset')
                    ),
                ),
                'do_submit'   => array(
                    'type'  => 'hidden',
                    'name'  => 'do_submit', // hidden submit field for AJAX submit
                    'value' => 1,
                ),
                'fileUri'     => array(
                    'type'  => 'hidden',
                    'name'  => 'fileUri',
                    'value' => $sFileLink
                ),
                'mediaAction' => array(
                    'type'  => 'hidden',
                    'name'  => 'mediaAction',
                    'value' => $sAction
                )
            )
        );

        return $aForm;
    }

    function processUpload($oUploader, $sAction)
    {
        $sCode = '';
        switch ($sAction) {
            case 'cancel_file':
                $sCode = $oUploader->serviceCancelFileInfo();
                break;
            case 'accept_file_info':
                $sCode = $oUploader->serviceAcceptFileInfo();
                break;
            case 'accept_multi_html5':
                $sCode = $oUploader->serviceAcceptHtml5FilesInfo();
                break;
            default:
                $sCode = $oUploader->serviceAcceptUpload($sAction);
                break;
        }
        echo $sCode;
    }

    function sendFileInfo($sEmail, $sMessage, $sUrl, $sType = 'share')
    {
        $aUser = getProfileInfo($this->_iProfileId);
        $sUrl  = urldecode($sUrl);
        $aPlus = array(
            'MediaType'       => _t('_' . $this->_oConfig->getMainPrefix() . '_single'),
            'MediaUrl'        => $sUrl,
            'SenderNickName'  => $aUser ? getNickName($aUser['ID']) : _t("_Visitor"),
            'UserExplanation' => $sMessage
        );
        bx_import('BxDolEmailTemplates');
        $rEmailTemplate = new BxDolEmailTemplates();
        $sSubject       = 't_' . $this->_oConfig->getMainPrefix() . '_' . $sType;
        $aEmails        = explode(",", $sEmail);
        foreach ($aEmails as $sMail) {
            $aTemplate = $rEmailTemplate->getTemplate($sSubject);
            $sMail     = trim($sMail);
            if (sendMail($sMail, $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus)) {
                return true;
            }
        }

        return false;
    }

    function serviceGetFilesConfig()
    {
        return $this->_oConfig->aFilesConfig;
    }

    function serviceRemoveObject($iFileId)
    {
        $iFileId = (int)$iFileId;

        return $this->_deleteFile($iFileId);
    }

    function serviceGetFavoriteList($iMember, $iFrom = 0, $iPerPage = 10)
    {
        return $this->_oDb->getFavorites($iMember, $iFrom, $iPerPage);
    }

    function serviceGetMemberMenuItem($sIcon = 'square-o')
    {
        $iUser       = getLoggedId();
        $oMemberMenu = bx_instance('BxDolMemberMenu');
        $aLinkInfo   = array(
            'item_img_src' => $sIcon,
            'item_img_alt' => _t('_' . $this->_oConfig->getMainPrefix()),
            'item_link'    => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'albums/my/',
            'item_onclick' => null,
            'item_title'   => _t('_' . $this->_oConfig->getMainPrefix()),
            'extra_info'   => $this->_oDb->getFilesCountByAuthor($iUser),
        );

        return $oMemberMenu->getGetExtraMenuLink($aLinkInfo);
    }

    function serviceGetMemberMenuItemAddContent($sIcon = 'square-o')
    {
        if (!$this->isAllowedAdd()) {
            return '';
        }
        $oMemberMenu = bx_instance('BxDolMemberMenu');
        $aLinkInfo   = array(
            'item_img_src' => $sIcon,
            'item_img_alt' => _t('_' . $this->_oConfig->getMainPrefix()),
            'item_link'    => 'javascript:void(0);',
            'item_onclick' => "showPopupAnyHtml('" . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "upload');",
            'item_title'   => _t('_' . $this->_oConfig->getMainPrefix()),
        );

        return $oMemberMenu->getGetExtraMenuLink($aLinkInfo);
    }

    function serviceGetWallData()
    {
        $sUri    = $this->_oConfig->getUri();
        $sPrefix = $this->_oConfig->getMainPrefix();

        return array(
            'handlers' => array(
                array(
                    'alert_unit'    => $sPrefix,
                    'alert_action'  => 'add',
                    'module_uri'    => $sUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_wall_post',
                    'groupable'     => 1,
                    'group_by'      => 'album',
                    'timeline'      => 1,
                    'outline'       => 1
                ),
                array(
                    'alert_unit'    => $sPrefix,
                    'alert_action'  => 'comment_add',
                    'module_uri'    => $sUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_wall_add_comment',
                    'groupable'     => 0,
                    'group_by'      => '',
                    'timeline'      => 1,
                    'outline'       => 0
                ),

                //DEPRICATED, saved for backward compatibility
                array(
                    'alert_unit'    => $sPrefix,
                    'alert_action'  => 'commentPost',
                    'module_uri'    => $sUri,
                    'module_class'  => 'Search',
                    'module_method' => 'get_wall_post_comment',
                    'groupable'     => 0,
                    'group_by'      => '',
                    'timeline'      => 1,
                    'outline'       => 0
                )
            ),
            'alerts'   => array(
                array('unit' => $sPrefix, 'action' => 'add')
            )
        );
    }

    function serviceGetWallPost($aEvent)
    {
        return $this->getWallPost($aEvent);
    }

    function serviceGetWallPostOutline($aEvent)
    {
        return $this->getWallPostOutline($aEvent);
    }

    function serviceGetWallAddComment($aEvent, $aParams = array())
    {
        $iId    = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = $iOwner != 0 ? getNickName($iOwner) : _t('_Anonymous');

        $aContent = unserialize($aEvent['content']);
        if (empty($aContent) || empty($aContent['object_id'])) {
            return '';
        }

        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch = new $sClassName();

        $iItem = (int)$aContent['object_id'];
        $aItem = $oSearch->serviceGetEntry($iItem, 'browse');
        if (empty($aItem) || !is_array($aItem)) {
            return array('perform_delete' => true);
        }

        if (!$this->oAlbumPrivacy->check('album_view', (int)$aItem['album_id'], $this->_iProfileId)) {
            return '';
        }

        bx_import('BxTemplCmtsView');
        $oCmts = new BxTemplCmtsView($this->_oConfig->getMainPrefix(), $iItem);
        if (!$oCmts->isEnabled()) {
            return '';
        }

        $aComment = $oCmts->getCommentRow($iId);
        if (empty($aComment) || !is_array($aComment)) {
            return array('perform_delete' => true);
        }

        $sCss = '';
        $sUri = $this->_oConfig->getUri();
        if ($aEvent['js_mode']) {
            $sCss = $this->_oTemplate->addCss('wall_post.css', true);
        } else {
            $this->_oTemplate->addCss('wall_post.css');
        }

        $sTextWallObject = _t('_bx_' . $sUri . '_wall_object');

        $sTmplName        = isset($aParams['templates']['main']) ? $aParams['templates']['main'] : 'modules/boonex/wall/|timeline_comment.html';
        $sTmplNameSnippet = isset($aParams['templates']['snippet']) ? $aParams['templates']['snippet'] : 'modules/boonex/wall/|timeline_comment_files.html';

        return array(
            'title'       => _t('_bx_' . $sUri . '_wall_added_new_comment_title', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content'     => $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
                    'mod_prefix'       => 'bx_' . $sUri,
                    'cpt_user_name'    => $sOwner,
                    'cpt_added_new'    => _t('_bx_' . $sUri . '_wall_added_new_comment'),
                    'cpt_object'       => $sTextWallObject,
                    'cpt_item_url'     => $aItem['url'],
                    'cnt_comment_text' => $aComment['cmt_text'],
                    'snippet'          => $this->_oTemplate->parseHtmlByName($sTmplNameSnippet, array(
                        'mod_prefix'           => 'bx_' . $sUri,
                        'cnt_item_page'        => $aItem['url'],
                        'cnt_item_width'       => $aItem['width'],
                        'cnt_item_height'      => $aItem['height'],
                        'cnt_item_icon'        => $aItem['file'],
                        'cnt_item_title'       => $aItem['title'],
                        'cnt_item_title_attr'  => bx_html_attribute($aItem['title']),
                        'cnt_item_description' => $aItem['description'],
                        'post_id'              => $aEvent['id'],
                    ))
                ))
        );
    }

    function getWallPost($aEvent, $sIcon = 'save', $aParams = array())
    {
        $sPrefix = $this->_oConfig->getMainPrefix();

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',',
            $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch = new $sClassName();

        $sItemThumbnailType = isset($aParams['thumbnail_type']) ? $aParams['thumbnail_type'] : 'browse';

        $iDeleted = 0;
        $aItems   = $aTmplItems = array();
        foreach ($aObjectIds as $iId) {
            $aItem = $oSearch->serviceGetItemArray($iId, $sItemThumbnailType);
            if (empty($aItem)) {
                $iDeleted++;
                continue;
            } else {
                if ($aItem['status'] == 'approved' && $this->oAlbumPrivacy->check('album_view', $aItem['album_id'],
                        $this->oModule->_iProfileId)
                ) {
                    $aItems[] = $aItem;
                }
            }

            $aItem2x = $oSearch->serviceGetItemArray($iId, $sItemThumbnailType . '2x');

            $aTmplItems[] = array_merge($aItem, array(
                'mod_prefix'       => $sPrefix,
                'cnt_item_width'   => $aItem['width'],
                'cnt_item_height'  => $aItem['height'],
                'cnt_item_icon'    => $aItem['file'],
                'cnt_item_icon_2x' => !empty($aItem2x['file']) ? $aItem2x['file'] : $aItem['file'],
                'cnt_item_page'    => $aItem['url'],
                'cnt_item_title'   => $aItem['title'],
            ));
        }

        if ($iDeleted == count($aObjectIds)) {
            return array('perform_delete' => true);
        }

        $iOwner = 0;
        if(!empty($aEvent['owner_id']))
            $iOwner = (int)$aEvent['owner_id'];

        $iDate = 0;
        if(!empty($aEvent['date']))
            $iDate = (int)$aEvent['date'];

        $bItems = !empty($aItems) && is_array($aItems);
        if($iOwner == 0 && $bItems && !empty($aItems[0]['owner']))
            $iOwner = (int)$aItems[0]['owner'];

        if($iOwner == 0 || !$bItems)
            return "";

        $sCss = "";
        if ($aEvent['js_mode']) {
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css'), true);
        } else {
            $this->_oTemplate->addCss(array('wall_post.css', 'wall_post_phone.css'));
        }

        $iItems = count($aItems);
        $sOwner = getNickName($iOwner);

        //--- Grouped events
        if ($iItems > 1) {
            if ($iItems > 4) {
                $aItems     = array_slice($aItems, 0, 4);
                $aTmplItems = array_slice($aTmplItems, 0, 4);
            }

            $aExtra    = unserialize($aEvent['content']);
            $sAlbumUri = $aExtra['album'];

            $oAlbum     = new BxDolAlbums($sPrefix);
            $aAlbumInfo = $oAlbum->getAlbumInfo(array('fileUri' => $sAlbumUri, 'owner' => $iOwner));

            $sTemplateName = isset($aParams['templates']['grouped']) ? $aParams['templates']['grouped'] : 'modules/boonex/wall/|timeline_post_files_grouped.html';

            return array(
                'owner_id' => $iOwner,
                'title' => _t('_' . $sPrefix . '_wall_added_new_items_title', $sOwner, $iItems),
                'description' => '',
                'grouped' => array(
                    'group_id' => $aAlbumInfo['ID'],
                    'group_cmts_name' => $sPrefix . '_albums'
                ),
                'content' => $sCss . $this->_oTemplate->parseHtmlByName($sTemplateName, array(
                    'mod_prefix' => $sPrefix,
                    'mod_icon' => $sIcon,
                    'cpt_user_name' => $sOwner,
                    'cpt_added_new' => _t('_' . $sPrefix . '_wall_added_new_items', $iItems),
                    'cpt_album_url' => $oSearch->getCurrentUrl('album', $aAlbumInfo['ID'], $aAlbumInfo['Uri']) . '/owner/' . getUsername($iOwner),
                    'cpt_album_title' => $aAlbumInfo['Caption'],
                    'bx_repeat:items' => $aTmplItems,
                    'post_id' => $aEvent['id']
                )),
                'date' => $iDate
            );
        }

        $aItem     = $aItems[0];
        $aTmplItem = $aTmplItems[0];

        //--- Single public event
        $sItemTxt      = _t('_' . $sPrefix . '_wall_object');
        $sTemplateName = isset($aParams['templates']['single']) ? $aParams['templates']['single'] : 'modules/boonex/wall/|timeline_post_files.html';

        return array(
        	'owner_id' => $iOwner,
            'title' => _t('_' . $sPrefix . '_wall_added_new_title', $sOwner, $sItemTxt),
            'description' => $aItem['description'],
            'grouped' => false,
            'content' => $sCss . $this->_oTemplate->parseHtmlByName($sTemplateName, array_merge($aTmplItem, array(
                'mod_prefix' => $sPrefix,
                'mod_icon' => $sIcon,
                'cpt_user_name' => $sOwner,
                'cpt_added_new' => _t('_' . $sPrefix . '_wall_added_new'),
                'cpt_item_url' => $aItem['url'],
                'cpt_item_title' => $aItem['title'],
                'cpt_item' => $sItemTxt,
                'post_id' => $aEvent['id']
            ))),
            'date' => $iDate
        );
    }

    function getWallPostOutline($aEvent, $sIcon = 'save', $aParams = array())
    {
        $sPrefix      = $this->_oConfig->getMainPrefix();
        $sPrefixAlbum = $sPrefix . '_albums';
        $aOwner       = getProfileInfo((int)$aEvent['owner_id']);

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',',
            $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $iItems      = count($aObjectIds);
        $iItemsLimit = isset($aParams['grouped']['items_limit']) ? (int)$aParams['grouped']['items_limit'] : 3;
        if ($iItems > $iItemsLimit) {
            $aObjectIds = array_slice($aObjectIds, 0, $iItemsLimit);
        }

        $bSave    = false;
        $aContent = array();
        if (!empty($aEvent['content'])) {
            $aContent = unserialize($aEvent['content']);
        }

        if (!isset($aContent['idims'])) {
            $aContent['idims'] = array();
        }

        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch = new $sClassName();

        $sItemThumbnailType = isset($aParams['thumbnail_type']) ? $aParams['thumbnail_type'] : 'browse';

        $iDeleted = 0;
        $aItems   = $aTmplItems = array();
        foreach ($aObjectIds as $iId) {
            $aItem = $oSearch->serviceGetItemArray($iId, $sItemThumbnailType);
            if (empty($aItem)) {
                $iDeleted++;
            } else {
                if ($aItem['status'] == 'approved' && $this->oAlbumPrivacy->check('album_view', $aItem['album_id'],
                        $this->oModule->_iProfileId)
                ) {
                    if (!isset($aContent['idims'][$iId])) {
                        $sPath                   = isset($aItem['file_path']) && file_exists($aItem['file_path']) ? $aItem['file_path'] : $aItem['file'];
                        $aContent['idims'][$iId] = BxDolImageResize::instance()->getImageSize($sPath);
                        $bSave                   = true;
                    }

                    $aItem['dims'] = $aContent['idims'][$iId];
                    $aItems[]      = $aItem;

                    $aItem2x = $oSearch->serviceGetItemArray($iId, $sItemThumbnailType . '2x');

                    $aTmplItems[] = array_merge($aItem, array(
                        'mod_prefix'   => $sPrefix,
                        'item_width'   => $aItem['dims']['w'],
                        'item_height'  => $aItem['dims']['h'],
                        'item_icon'    => $aItem['file'],
                        'item_icon_2x' => !empty($aItem2x['file']) ? $aItem2x['file'] : $aItem['file'],
                        'item_page'    => $aItem['url'],
                        'item_title'   => $aItem['title'],
                    ));
                }
            }
        }

        if ($iDeleted == count($aObjectIds)) {
            return array('perform_delete' => true);
        }

        if (empty($aOwner) || empty($aItems)) {
            return "";
        }

        $aResult = array();
        if ($bSave) {
            $aResult['save']['content'] = serialize($aContent);
        }

        $sCss = "";
        if ($aEvent['js_mode']) {
            $sCss = $this->_oTemplate->addCss('wall_outline.css', true);
        } else {
            $this->_oTemplate->addCss('wall_outline.css');
        }

        $iOwner     = (int)$aEvent['owner_id'];
        $sOwner     = getNickName($iOwner);
        $sOwnerLink = getProfileLink($iOwner);

        //--- Grouped events
        $iItems = count($aItems);
        if ($iItems > 1) {
            $aExtra    = unserialize($aEvent['content']);
            $sAlbumUri = $aExtra['album'];

            $oAlbum     = new BxDolAlbums($sPrefix);
            $aAlbumInfo = $oAlbum->getAlbumInfo(array('fileUri' => $sAlbumUri, 'owner' => $iOwner));

            $oAlbumCmts                   = new BxTemplCmtsView($sPrefixAlbum, $aAlbumInfo['ID']);
            $aAlbumInfo['comments_count'] = (int)$oAlbumCmts->getObjectCommentsCount();
            $aAlbumInfo['Url']            = $oSearch->getCurrentUrl('album', $aAlbumInfo['ID'],
                    $aAlbumInfo['Uri']) . '/owner/' . getUsername($iOwner);

            $sTmplName          = isset($aParams['templates']['grouped']) ? $aParams['templates']['grouped'] : 'modules/boonex/wall/|outline_item_image_grouped.html';
            $aResult['content'] = $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
                    'mod_prefix'          => $sPrefix,
                    'mod_icon'            => $sIcon,
                    'user_name'           => $sOwner,
                    'user_link'           => $sOwnerLink,
                    'bx_repeat:items'     => $aTmplItems,
                    'album_url'           => $aAlbumInfo['Url'],
                    'album_title'         => $aAlbumInfo['Caption'],
                    'album_description'   => strmaxtextlen($aAlbumInfo['Description'], 200),
                    'album_comments'      => (int)$aAlbumInfo['comments_count'] > 0 ? _t('_wall_n_comments',
                        $aAlbumInfo['comments_count']) : _t('_wall_no_comments'),
                    'album_comments_link' => $aAlbumInfo['Url'] . '#cmta-' . $sPrefixAlbum . '-' . $aAlbumInfo['ID'],
                    'post_id'             => $aEvent['id'],
                    'post_ago'            => $aEvent['ago']
                ));

            return $aResult;
        }

        //--- Single public event
        $aItem     = $aItems[0];
        $aTmplItem = $aTmplItems[0];

        $sTmplName          = isset($aParams['templates']['single']) ? $aParams['templates']['single'] : 'modules/boonex/wall/|outline_item_image.html';
        $aResult['content'] = $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array_merge($aTmplItem, array(
                'mod_prefix'         => $sPrefix,
                'mod_icon'           => $sIcon,
                'user_name'          => $sOwner,
                'user_link'          => $sOwnerLink,
                'item_page'          => $aItem['url'],
                'item_title'         => $aItem['title'],
                'item_description'   => strmaxtextlen($aItem['description'], 200),
                'item_comments'      => (int)$aItem['comments_count'] > 0 ? _t('_wall_n_comments',
                    $aItem['comments_count']) : _t('_wall_no_comments'),
                'item_comments_link' => $aItem['url'] . '#cmta-' . $sPrefix . '-' . $aItem['id'],
                'post_id'            => $aEvent['id'],
                'post_ago'           => $aEvent['ago']
            )));

        return $aResult;
    }

    function serviceGetSpyData()
    {
        $sModuleUri = $this->_oConfig->getUri();
        $AlertName  = $this->_oConfig->getMainPrefix();

        return array(
            'handlers' => array(
                array(
                    'alert_unit'    => $AlertName,
                    'alert_action'  => 'add',
                    'module_uri'    => $sModuleUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_spy_post'
                ),
                array(
                    'alert_unit'    => $AlertName,
                    'alert_action'  => 'rate',
                    'module_uri'    => $sModuleUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_spy_post'
                ),
                array(
                    'alert_unit'    => $AlertName,
                    'alert_action'  => 'commentPost',
                    'module_uri'    => $sModuleUri,
                    'module_class'  => 'Module',
                    'module_method' => 'get_spy_post'
                ),
            ),
            'alerts'   => array(
                array('unit' => $AlertName, 'action' => 'add'),
                array('unit' => $AlertName, 'action' => 'rate'),
                array('unit' => $AlertName, 'action' => 'delete'),
                array('unit' => $AlertName, 'action' => 'commentPost'),
                array('unit' => $AlertName, 'action' => 'commentRemoved')
            )
        );
    }

    function serviceGetSpyPost($sAction, $iObjectId = 0, $iSenderId = 0, $aExtraParams = array())
    {
        $aRet  = array();
        $aInfo = $this->_oDb->getFileInfo(array('fileId' => $iObjectId), true,
            array('medUri', 'medTitle', 'medProfId'));
        $aRet  = array(
            'params'       => array(
                'profile_link'     => getProfileLink($iSenderId),
                'profile_nick'     => getNickName($iSenderId),
                'entry_url'        => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aInfo['medUri'],
                'entry_caption'    => $aInfo['medTitle'],
                'recipient_p_link' => getProfileLink($aInfo['medProfId']),
                'recipient_p_nick' => getNickName($aInfo['medProfId']),
            ),
            'recipient_id' => 0,
        );
        switch ($sAction) {
            case 'add' :
                $aRet['lang_key'] = '_' . $this->_oConfig->getMainPrefix() . '_spy_added';
                break;
            case 'rate' :
                $aRet['lang_key']     = '_' . $this->_oConfig->getMainPrefix() . '_spy_rated';
                $aRet['recipient_id'] = $aInfo['medProfId'];
                $aRet['spy_type']     = 'content_activity';
                break;
            case 'commentPost' :
                $aRet['lang_key']     = '_' . $this->_oConfig->getMainPrefix() . '_spy_comment_posted';
                $aRet['recipient_id'] = $aInfo['medProfId'];
                $aRet['spy_type']     = 'content_activity';
                break;
        }

        return $aRet;
    }

    function serviceDeleteProfileData($iProfileId)
    {
        if (!$iProfileId) {
            return false;
        }

        $aDataEntries = $this->_oDb->getFilesByAuthor($iProfileId);
        foreach ($aDataEntries as $iFileId) {
            $this->_deleteFile($iFileId);
        }
    }

    function serviceDeleteProfileAlbums($iProfileId)
    {
        if (!$iProfileId) {
            return false;
        }
        $aDataEntries = $this->oAlbums->getAlbumList(array(
            'owner'      => $iProfileId,
            'status'     => 'any',
            'show_empty' => true
        ), 0, 0, true);
        foreach ($aDataEntries as $aValue) {
            $this->oAlbums->removeAlbum($aValue['ID']);
        }
    }

    function serviceResponseProfileDelete($oAlert)
    {
        if (!($iProfileId = (int)$oAlert->iObject)) {
            return false;
        }

        $this->serviceDeleteProfileData($iProfileId);
        $this->serviceDeleteProfileAlbums($iProfileId);

        return true;
    }

    // return array with info or false result
    function serviceCheckDelete($iFileId)
    {
        return $this->serviceCheckAction('delete', $iFileId);
    }

    function serviceCheckAction($sAction, $iFileId)
    {
        $iFileId = (int)$iFileId;
        $sAction = ucfirst(strip_tags($sAction));
        if ($iFileId == 0 || strlen($sAction) == 0) {
            return false;
        }
        $aFileInfo = $this->_oDb->getFileInfo(array('fileId' => $iFileId), true,
            array('medID', 'medProfId', 'medExt', 'medDate', 'Approved'));
        if (empty($aFileInfo)) {
            return false;
        }
        if (!defined('BX_DOL_CRON_EXECUTE')) {
            $sMethodName = 'isAllowed' . $sAction;
            if (!method_exists($this, $sMethodName)) {
                return false;
            }
            if (!$this->$sMethodName($aFileInfo)) {
                return false;
            }
        }

        return $aFileInfo;
    }

    function serviceGetSubscriptionParams($sAction, $iEntryId)
    {
        $aDataEntry = $this->_oDb->getFileInfo(array('fileId' => $iEntryId), true,
            array('medUri', 'medTitle', 'Approved'));
        if (empty($aDataEntry) || $aDataEntry['Approved'] != 'approved') {
            return array('skip' => true);
        }

        $aActionList = array(
            'commentPost' => '_sbs_comments'
        );

        $sActionName = isset($aActionList[$sAction]) ? ' (' . _t('_' . $this->_oConfig->getMainPrefix() . $aActionList[$sAction]) . ')' : '';

        return array(
            'skip'     => false,
            'template' => array(
                'Subscription' => $aDataEntry['medTitle'] . $sActionName,
                'ViewLink'     => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry['medUri'],
            ),
        );
    }

    // info services
    function serviceGetAllAlbums($iProfId, $sStatus = 'active')
    {
        $aAlbumsArray = $this->oAlbums->getAlbumList(array('owner' => $iProfId, 'status' => $sStatus));
        foreach ($aAlbumsArray as $aAlbum) {
            $aList[$aAlbum['ID']] = $aAlbum;
        }

        return $aList;
    }
}

// support classes
class BxSupportCheckerHelper extends BxDolFormCheckerHelper
{
    function checkEmailSet($sSet)
    {
        $aEmails = explode(',', $sSet);
        foreach ($aEmails as $sEmail) {
            $sEmail = trim($sEmail);
            if (!preg_match("/(([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?/",
                $sEmail)
            ) {
                return false;
            }
        }

        return true;
    }
}
