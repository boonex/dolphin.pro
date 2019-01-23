<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesModule');

class BxFilesModule extends BxDolFilesModule
{
    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_oConfig->init($this->_oDb);

        $this->_aMemActions[] = 'download';
        $this->aSectionsAdmin['pending'] = array(
            'exclude_btns' => array('featured', 'unfeatured')
        );
    }

    function _getOriginalExt (&$aInfo, $sTmpl, $sKey = '{ext}')
    {
        return str_replace($sKey, sha1($aInfo['medDate']), $sTmpl);
    }

    function actionGetFile ($sFileName)
    {
        $iFileId = (int)$sFileName;
        $aInfo = $this->_oDb->getFileInfo(array('fileId'=>(int)$iFileId), false, array('medID', 'medProfId', 'medExt', 'medDate', 'Type', 'medUri'));
        if ($aInfo && $this->isAllowedDownload($aInfo)) {
            $sPathFull = $this->_oConfig->getHomePath() . 'data/files/' . $aInfo['medID'] . '_' . sha1($aInfo['medDate']);
            if (file_exists($sPathFull)) {
                header('Connection: close');
                header('Content-Type: ' . $aInfo['Type']);
                header('Content-Length: ' . filesize($sPathFull));
                header('Last-Modified: ' . gmdate('r', filemtime($sPathFull)));
                header('Content-Disposition: attachment; filename="' . $aInfo['medUri'] . '.' . $aInfo['medExt'] . '";');
                readfile($sPathFull);
                $this->_oDb->updateDownloadsCount($sFileUri);
                $this->isAllowedDownload($aInfo, true);
                exit;
            } else {
                $this->_oTemplate->displayPageNotFound();
            }
        } elseif (!$aInfo) {
            $this->_oTemplate->displayPageNotFound();
        } else {
            $this->_oTemplate->displayAccessDenied();
        }
    }

    function isAllowedDownload (&$aFile, $isPerformAction = false)
    {
        if ($this->isAdmin($this->_iProfileId) || $aFile['medProfId'] == $this->_iProfileId) return true;
        if (!$this->oPrivacy->check('download', $aFile['medID'], $this->_iProfileId))
            return false;
        $this->_defineActions();
        $aCheck = checkAction($this->_iProfileId, BX_FILES_DOWNLOAD, $isPerformAction);
        if ($aCheck[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED)
            return false;
        return true;
    }

	function isAllowedShare(&$aDataEntry)
    {
    	if($aDataEntry['AllowAlbumView'] != BX_DOL_PG_ALL)
    		return false;

        return true;
    }

    function actionAlbumsViewMy ($sParamValue = '', $sParamValue1 = '', $sParamValue2 = '', $sParamValue3 = '')
    {
        $sAction = bx_get('action');
        if($sAction !== false) {
            header('Content-Type: text/html; charset=UTF-8');

            require_once('BxFilesUploader.php');
            $oUploader = new BxFilesUploader();

            switch($sAction) {
                case 'cancel_file':
                    echo $oUploader->serviceCancelFileInfo();
                    return;
                case 'accept_file_info':
                    echo $oUploader->serviceAcceptFileInfo(); 
                    return;
                default:
                    parent::processUpload($oUploader, $sAction);
                    return;
            }
        }
            $bNotAllowView = $this->_iProfileId == 0 || !isLoggedActive();
            $aAlbumInfo = array();
            if (!$bNotAllowView && !empty($sParamValue1)) {
                $aAlbumInfo = $this->oAlbums->getAlbumInfo(array('fileUri' => $sParamValue1, 'owner' => $this->_iProfileId));
                if (!empty($aAlbumInfo))
                    $bNotAllowView = $aAlbumInfo['AllowAlbumView'] == BX_DOL_PG_HIDDEN;
            }
            if ($bNotAllowView) {
            $sKey  = _t('_' . $this->_oConfig->getMainPrefix() . '_access_denied');
            $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => DesignBoxContent($sKey, MsgBox($sKey), 1)), '', '', false);
            return;
        }

        if(is_array($_POST['entry']))
            foreach ($_POST['entry'] as $iValue) {
                $iValue = (int)$iValue;
                switch (true) {
                    case isset($_POST['action_delete']):
                        $iCount = $this->_deleteAlbumUnits($iValue);
                        if ($iCount == 0)
                            $this->oAlbums->removeAlbum($iValue);
                        break;
                    case isset($_POST['action_move_to']):
                        $this->oAlbums->moveObject((int)$_POST['album_id'], (int)$_POST['new_album'], $iValue);
                        break;
                    case isset($_POST['action_delete_object']):
                        $this->_deleteFile($iValue);
                        break;
                }
            }

        bx_import('PageAlbumsMy', $this->_aModule);
        $sClassName = $this->_oConfig->getClassPrefix() . 'PageAlbumsMy';
        $oPage = new $sClassName($this, $this->_iProfileId, array($sParamValue, $sParamValue1, $sParamValue2, $sParamValue3));
        $sCode = $oPage->getCode();

        switch($sParamValue) {
            case 'main':
                bx_import('PageAlbumsOwner', $this->_aModule);
                $sClassName = $this->_oConfig->getClassPrefix() . 'PageAlbumsOwner';
                $oPage = new $sClassName($this, array('browse', 'owner', getUsername($this->_iProfileId)));
                $sCode .= $oPage->getCode();
                break;

            case 'main_objects':
                $sCode .= $this->getAlbumPageView($aAlbumInfo);
                break;
        }

        $GLOBALS['oTopMenu']->setCurrentProfileID($this->_iProfileId);
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode), '', '', false);
    }

    function actionAlbums ($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '', $sParamValue3 = '')
    {
        $sCode = '';

        switch ($sParamName) {
            case 'my':
                $this->actionAlbumsViewMy($sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);
                break;

            case 'browse':
                if ('owner' == $sParamValue) {                    
                    $iIdOwner = getID($sParamValue1);
                    if (!$iIdOwner) {
                        $this->_oTemplate->displayPageNotFound();
                        exit;
                    }
                    $GLOBALS['oTopMenu']->setCurrentProfileID($iIdOwner);
                    $this->aPageTmpl['header'] = _t('_' . $this->_oConfig->getMainPrefix() . '_browse_by_owner', $sParamValue1);
                }

            default:
                $sClassName = $this->_oConfig->getClassPrefix() . 'PageAlbumsOwner';
                bx_import('PageAlbumsOwner', $this->_aModule);
                $oPage = new $sClassName($this, array($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3));
                $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $oPage->getCode()));
        }
    }

    function actionBrowse ($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '', $sParamValue3 = '')
    {
        $bAlbumView = false;
        if ($sParamName == 'album' && $sParamValue1 == 'owner') {
            $bAlbumView = true;
            $aAlbumInfo = $this->oAlbums->getAlbumInfo(array('fileUri' => $sParamValue, 'owner' => getID($sParamValue2)), array('ID', 'Caption', 'Owner', 'AllowAlbumView', 'Description'));
            if (empty($aAlbumInfo)) {
                $this->_oTemplate->displayPageNotFound();
                exit;
            } else {
                if ($aAlbumInfo['Owner'] == $this->_iProfileId && $sParamValue2 === getUsername($this->_iProfileId)) {
                    $this->actionAlbumsViewMy('main_objects', $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);
                    return;
                } elseif (!empty($aAlbumInfo['AllowAlbumView']) && !$this->oAlbumPrivacy->check('album_view', $aAlbumInfo['ID'], $this->_iProfileId)) {
                    $sKey  = _t('_' . $this->_oConfig->getMainPrefix() . '_access_denied');
                    $sCode = DesignBoxContent($sKey, MsgBox($sKey), 1);
                    $this->aPageTmpl['header'] = $sKey;
                    $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
                    return;
                } else {
                    $GLOBALS['oTopMenu']->setCustomSubHeader($aAlbumInfo['Caption']);
                }
            }
        }

        if ('calendar' == $sParamName) {
            $sParamValue = (int)$sParamValue;
            $sParamValue1 = (int)$sParamValue1;
            $sParamValue2 = (int)$sParamValue2;
        }
        
        $sClassName = $this->_oConfig->getClassPrefix() . 'Search';
        bx_import('Search', $this->_aModule);
        $oSearch = new $sClassName($sParamName, $sParamValue, $sParamValue1, $sParamValue2);
        $sRss = bx_get('rss');
        if ($sRss !== false && $sRss) {
            $oSearch->aCurrent['paginate']['perPage'] = 10;
            echo $oSearch->rss();
            exit;
        }

        $sTopPostfix = isset($oSearch->aCurrent['restriction'][$sParamName]) || $oSearch->aCurrent['sorting'] == $sParamName ? $sParamName : 'all';
        $sCaption = _t('_' . $this->_oConfig->getMainPrefix() . '_top_menu_' . $sTopPostfix);
        if (!empty($sParamValue) && isset($oSearch->aCurrent['restriction'][$sParamName])) {
            $sParamValue = $this->getBrowseParam($sParamName, $sParamValue);
            $oSearch->aCurrent['restriction'][$sParamName]['value'] = $sParamValue;
            $sCaption = _t('_' . $this->_oConfig->getMainPrefix() . '_browse_by_' . $sParamName, htmlspecialchars_adv(process_pass_data($sParamValue)));
        }
        if ($bAlbumView) {
            $oSearch->aCurrent['restriction']['allow_view']['value'] = array($aAlbumInfo['AllowAlbumView']);
            $sCaption = _t('_' . $this->_oConfig->getMainPrefix() . '_browse_by_' . $sParamName, $aAlbumInfo['Caption']);
            $this->_oTemplate->setPageDescription(substr(strip_tags($aAlbumInfo['Description']), 0, 255));
        } else
            $oSearch->aCurrent['restriction']['not_allow_view']['value'] = array(BX_DOL_PG_HIDDEN);

        if($sParamName == 'calendar') {
            $sCaption = _t('_' . $this->_oConfig->getMainPrefix() . '_caption_browse_by_day')
                . ': ' . getLocaleDate( strtotime("{$sParamValue}-{$sParamValue1}-{$sParamValue2}")
                , BX_DOL_LOCALE_DATE_SHORT);
        }

        $oSearch->aCurrent['paginate']['perPage'] = (int)$this->_oConfig->getGlParam('number_all');
        $sCode = $oSearch->displayResultBlock();
        $sPaginate = '';
        if ($oSearch->aCurrent['paginate']['totalNum'] > 0) {
            $aAdd = array($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);
            foreach ($aAdd as $sValue) {
                if (strlen($sValue) > 0)
                    $sArg .= '/' . $sValue;
                else
                    break;
            }
            $sLink  = $this->_oConfig->getBaseUri() . 'browse' . $sArg;
            $oPaginate = new BxDolPaginate(array(
                'page_url' => $sLink . '&page={page}&per_page={per_page}',
                'count' => $oSearch->aCurrent['paginate']['totalNum'],
                'per_page' => $oSearch->aCurrent['paginate']['perPage'],
                'page' => $oSearch->aCurrent['paginate']['page'],
                'on_change_per_page' => 'return !loadDynamicBlock(1, \'' . $sLink . '&page=1&per_page=\' + this.value);'
            ));
            $sPaginate = $oPaginate->getPaginate();
        } else
            $sCode = MsgBox(_t('_Empty'));
        $aMenu = array();
        $sCode = DesignBoxContent($sCaption, $sCode . $sPaginate, 1);
        if ($bAlbumView)
            $sCode = $this->getAlbumPageView($aAlbumInfo, $sCode);
        $this->aPageTmpl['css_name'] = array('browse.css');
        $this->aPageTmpl['header'] = $sCaption;
        $this->_oTemplate->pageCode($this->aPageTmpl, array('page_main_code' => $sCode));
    }

    function actionEdit ($iFileId)
    {
        $bAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? TRUE : FALSE;
        $iFileId = (int)$iFileId > 0 ? (int)$iFileId : (int)bx_get('fileId');
        if (!$iFileId || !$bAjax) return;

        $aManageArray = array('medTitle', 'medTags', 'medDesc', 'medProfId', 'Categories', 'AllowDownload', 'medUri');
        $aInfo = $this->_oDb->getFileInfo(array('fileId'=>$iFileId), false, $aManageArray);
        $sLangPref = '_' . $this->_oConfig->getMainPrefix();

        if (!$this->isAllowedEdit($aInfo))
           $sCode = MsgBox(_t($sLangPref . '_access_denied')) . $sJsCode;
        else {
            $oCategories = new BxDolCategories();
            $oCategories->getTagObjectConfig();
            $aCategories = $oCategories->getGroupChooser($this->_oConfig->getMainPrefix(), $this->_iProfileId, true);
            $aCategories['value'] = explode(CATEGORIES_DIVIDER, $aInfo['Categories']);

            $aAllowDownload = $this->oPrivacy->getGroupChooser($this->_iProfileId, $this->_oConfig->getUri(), 'download');
            $aAllowDownload['value'] = $aInfo['AllowDownload'];
            $sUrlPref = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();
            $aForm = array(
                'form_attrs' => array(
                    'id' => $sLangPref . '_upload_form',
                    'method' => 'post',
                    'action' => $sUrlPref . 'edit/' . $iFileId,
                    'onsubmit' => "return bx_ajax_form_check(this)",
                ),
                'params' => array (
                    'db' => array(
                        'submit_name' => 'do_submit',
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
                        'name' => 'medTitle',
                        'caption' => _t('_Title'),
                        'required' => true,
                        'checker' => array (
                            'func' => 'length',
                            'params' => array(3, 128),
                            'error' => _t('_td_err_incorrect_length'),
                        ),
                        'value' => $aInfo['medTitle'],
                    ),
                    'tags' => array(
                        'type' => 'text',
                        'name' => 'medTags',
                        'caption' => _t('_Tags'),
                        'info' => _t('_Tags_desc'),
                        'value' => $aInfo['medTags']
                    ),
                    'description' => array(
                        'type' => 'textarea',
                        'name' => 'medDesc',
                        'caption' => _t('_Description'),
                        'value' => $aInfo['medDesc'],
                    ),
                    'categories' => $aCategories,
                    'AllowDownload' => $aAllowDownload,
                    'fileId' => array(
                        'type' => 'hidden',
                        'name' => 'fileId',
                        'value' => $iFileId,
                    ),
                    'medProfId' => array(
                        'type' => 'hidden',
                        'name' => 'medProfId',
                        'value' => $this->_iProfileId,
                    ),
                    'do_submit' => array(
                        'type' => 'hidden',
                        'name' => 'do_submit', // hidden submit field for AJAX submit
                        'value' => 1,
                    ),
                    'submit' => array(
                        'type' => 'submit',
                        'name' => 'submit_press',
                        'value' => _t('_Submit'),
                        'colspan' => true,
                    ),
                ),
            );
            $oForm = new BxTemplFormView($aForm);
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

                    $sCode = $GLOBALS['oFunctions']->msgBox(_t($sLangPref . '_save_success'), 3, 'window.location="' . $sUrlPref . 'view/' . $aInfo['medUri'] . '";');
                } else
                    $sCode = $GLOBALS['oFunctions']->msgBox(_t('_sys_save_nothing'));
            } else {
                $sCode = $this->_oTemplate->parseHtmlByName('default_padding.html', array('content' => $oForm->getCode()));
                $sCode = $this->_oTemplate->parseHtmlByName('popup.html', array('title' => $aInfo['medTitle'], 'content' => $sCode));
                $sCode = $GLOBALS['oFunctions']->transBox($sCode, TRUE);
            }
        }
        header('Content-type:text/html;charset=utf-8');
        echo $sCode;
        exit;
    }

    function serviceGetMemberMenuItem ($sIcon = 'save')
    {
        return parent::serviceGetMemberMenuItem ($sIcon);
    }

    function serviceGetMemberMenuItemAddContent ($sIcon = 'save')
    {
        return parent::serviceGetMemberMenuItemAddContent ($sIcon);
    }

    function serviceGetWallPost($aEvent)
    {
        return $this->getWallPost($aEvent, 'save', array(
            'templates' => array(
                'single' => 'wall_post.html',
                'grouped' => 'wall_post_grouped.html'
            )
        ));
    }

    function serviceGetWallPostOutline($aEvent)
    {
        return $this->getWallPostOutline($aEvent, 'save', array(
            'templates' => array(
                'single' => 'wall_outline.html',
                'grouped' => 'wall_outline_grouped.html'
            ),
            'grouped' => array(
                'items_limit' => 2
            )
        ));
    }

    function serviceGetWallAddComment($aEvent, $aParams = array())
    {
    	return parent::serviceGetWallAddComment($aEvent, array(
    		'templates' => array(
    			'snippet' => 'wall_post_comment.html'
    		)
    	));
    }

    function getInstanceUploadFormArray($aAlbums, $aPrivFieldView, $sAlbumsCaption = false, $sAlbumTitleCaption = false, $sCreateNewAlbumCaption = false)
    {
        $sLangPref = '_' . $this->_oConfig->getMainPrefix();
        return parent::getInstanceUploadFormArray($aAlbums, $aPrivFieldView, _t($sLangPref . '_album'), _t($sLangPref . '_album_title'), _t($sLangPref . '_albums_add_new'));
    }

}
