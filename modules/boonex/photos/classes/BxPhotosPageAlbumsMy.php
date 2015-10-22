<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlbums.php');
require_once('BxPhotosSearch.php');

class BxPhotosPageAlbumsMy extends BxDolPageView
{
    var $oTemplate;
    var $oConfig;
    var $oDb;
    var $oSearch;
    var $iOwnerId;
    var $aAddParams;

    var $oAlbum;
    var $oAlbumPrivacy;

    var $aCurrentBlocks;
    var $aSystemBlocks = array(
        'main' => array(
            'blocks' => array('adminShort'),
            'level' => 0
        ),
        'add' => array(
            'blocks' => array('add', 'my'),
            'level' => 0
        ),
        'manage' => array(
            'blocks' => array('adminFull', 'my'),
            'level' => 0
        ),
        'main_objects' => array(
            'blocks' => array('adminAlbumShort'),
            'level' => 1,
            'link' => 'browse/album/'
        ),
        'edit' => array(
            'blocks' => array('edit', 'albumObjects'),
            'level' => 1
        ),
        'organize' => array(
            'blocks' => array('organize', 'albumObjects'),
            'level' => 1
        ),
        'add_objects' => array(
            'blocks' => array('addObjects', 'albumObjects'),
            'level' => 1
        ),
        'manage_objects' => array(
            'blocks' => array('manageObjects', 'albumObjects'),
            'level' => 1
        ),
        'manage_objects_disapproved' => array(
            'blocks' => array('manageObjectsDisapproved', 'albumObjects'),
            'level' => 1
        ),
        'manage_objects_pending' => array(
            'blocks' => array('manageObjectsPending', 'albumObjects'),
            'level' => 1
        ),
        'manage_profile_photos' => array(
            'blocks' => array('organize', 'addObjects'),
            'level' => -1,
            'nomenu' => 1
        ),
    );

    function BxPhotosPageAlbumsMy (&$oShared, $iOwnerId, $aParams = array())
    {
        parent::BxDolPageView('bx_photos_albums_my');

        $this->oTemplate = $oShared->_oTemplate;
        $this->oConfig = $oShared->_oConfig;
        $this->oDb = $oShared->_oDb;
        $this->iOwnerId = $iOwnerId;
        $this->aAddParams = $aParams;
        $this->oSearch = new BxPhotosSearch('album', $this->aAddParams[1], 'owner', getUsername($this->iOwnerId));
        $this->oAlbum = new BxDolAlbums('bx_photos', $this->iOwnerId);
        $this->oAlbumPrivacy = $oShared->oAlbumPrivacy;

        if (isset($this->aSystemBlocks[$this->aAddParams[0]]))
           $this->aCurrentBlocks = $this->aSystemBlocks[$this->aAddParams[0]];
        else
           $this->aCurrentBlocks = $this->aSystemBlocks['main'];
        $this->oTemplate->addCss(array('my.css', 'browse.css'));

        $this->oSearch->aCurrent['restriction']['ownerId'] = array(
            'value' => $this->iOwnerId,
            'field' => 'Owner',
            'operator' => '=',
            'paramName' => 'ownerId'
        );

        $oShared->checkDefaultAlbums($this->iOwnerId);
    }

    function getViewLevel()
    {
        return !empty($this->aCurrentBlocks) ? (int)$this->aCurrentBlocks['level'] : 0;
    }

    // constant block
    function getBlockCode_my ($iBoxId)
    {
        if (!in_array('my', $this->aCurrentBlocks['blocks']))
            return '';
        $this->oSearch->clearFilters(array('allow_view', 'album_status'), array('albumsObjects', 'albums'));
        $this->oSearch->bAdminMode = false;
        $this->oSearch->aCurrent['view'] = 'full';
        $this->oSearch->aCurrent['restriction']['activeStatus']['value'] = 'approved';
        $iPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (int)$this->oConfig->getGlParam('number_albums_home');
        $iPage = isset($_GET['page']) ? (int)$_GET['page'] : $this->oSearch->aCurrent['paginate']['page'];
        $sCode = $this->oSearch->getAlbumList($iPage, $iPerPage, array('owner'=>$this->iOwnerId, 'show_empty' => true, 'hide_default' => true));
        $sPgn = '';
        if ($this->oSearch->aCurrent['paginate']['totalAlbumNum'] > 0) {
            $aLinkAddon = $this->oSearch->getLinkAddByPrams();
            $sLink = $sLinkJs = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/my';
                if ($this->oConfig->isPermalinkEnabled)
                    $sLinkJs .= '?';
                $sLinkJs .= $aLinkAddon['params'];

            $oPaginate = new BxDolPaginate(array(
                'page_url' => $sLink,
                'count' => $this->oSearch->aCurrent['paginate']['totalAlbumNum'],
                'per_page' => $iPerPage,
                'page' => $iPage,
                'on_change_page' => 'return !loadDynamicBlock(' . $iBoxId . ', \'' . $sLinkJs . '&page={page}&per_page={per_page}\');',
                'on_change_per_page' => 'return !loadDynamicBlock(' . $iBoxId . ', \'' . $sLinkJs . '&page=1&per_page=\' + this.value);'
            ));
          $sPgn = $oPaginate->getSimplePaginate(BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/browse/owner/' . getUsername($this->iOwnerId));
        }
        return array($sCode, array(), $sPgn, false);
    }

    function getBlockCode_adminShort ()
    {
        if (in_array('adminShort', $this->aCurrentBlocks['blocks'])) {
            $iNumber = $this->oAlbum->getAlbumCount(array('owner' => $this->iOwnerId, 'show_empty' => true, 'hide_default' => true));
            return array($this->oTemplate->getAdminAlbumShort($iNumber), $this->getTopMenu('main'), array());
        }
    }

    function getBlockCode_adminAlbumShort ()
    {
        if (in_array('adminAlbumShort', $this->aCurrentBlocks['blocks'])) {
            $iNumber = $this->oAlbum->getObjCount(array('fileUri' => $this->aAddParams[1], 'owner' => $this->iOwnerId));
            $sCode = $this->oTemplate->getAdminShort($iNumber, $this->aAddParams[1], $this->aAddParams[3]);
            $aInfo = $this->oAlbum->getAlbumInfo(array('fileUri' => $this->aAddParams[1], 'owner' => $this->iOwnerId));
            if ($aInfo['AllowAlbumView'] == BX_DOL_PG_NOBODY) {
                $aDraw = array(
                    'fileStatCount' => _t('_sys_album_privacy_me_info'),
                    'fileStatAdd' => _t('_sys_album_edit_info', BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/my/edit/' . $this->aAddParams[1] . '/owner/' . $this->aAddParams[3]),
                    'spec_class' => 'sys_warning_text'
                );
                $sCode .= $this->oTemplate->parseHtmlByName('admin_short.html', $aDraw);
            }
            return array($sCode, $this->getTopMenu('main_objects'), array());
        }
    }

    function getBlockCode_adminFull ($iBoxId)
    {
        if(!in_array('adminFull', $this->aCurrentBlocks['blocks']))
            return '';

        return array($this->getAdminPart(array(), array('section'=>'manage', 'page_block_id' => $iBoxId)), $this->getTopMenu('manage'), array(), '');
    }

    function getBlockCode_add ()
    {
        if (!in_array('add', $this->aCurrentBlocks['blocks']))
            return '';

        $aPrivFieldView = $this->oAlbumPrivacy->getGroupChooser($this->iOwnerId, $this->oConfig->getUri(), 'album_view');
        $aForm = $this->oTemplate->getAlbumFormAddArray(array('allow_view' => $aPrivFieldView));
        $oForm = new BxTemplFormView($aForm);
        $oForm->initChecker();

        if ($oForm->isSubmittedAndValid()) {
            $aFields = array('caption', 'location', 'description', 'AllowAlbumView', 'owner');
            $aData = array();
            foreach ($aFields as $sValue) {
                if (isset($_POST[$sValue]))
                    $aData[$sValue] = $_POST[$sValue];
            }
            $iNewId = $this->oAlbum->addAlbum($aData);
            if ($iNewId > 0) {
                $aNew = $this->oAlbum->getAlbumInfo(array('fileId' => $iNewId), array('Uri'));
                $sUrlAdd = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/my/add_objects/' . $aNew['Uri'] . '/owner/' . getUsername($this->iOwnerId);
                $sCode = MsgBox(_t('_' . $this->oConfig->getMainPrefix() . '_album_save_redirect')) . $this->oTemplate->getJsTimeOutRedirect($sUrlAdd, 3);
            }
        } else
            $sCode = $oForm->getCode();
        return array($sCode, $this->getTopMenu('add'));
    }

    function getBlockCode_addObjects ($iBoxId)
    {
        if (!in_array('addObjects', $this->aCurrentBlocks['blocks']))
            return '';

        $aAlbumInfo = $this->oAlbum->getAlbumInfo(array('fileUri' => $this->aAddParams[1], 'owner' => $this->iOwnerId));
        if($aAlbumInfo['Owner'] != $this->iOwnerId)
            $sCode = MsgBox(_t('_Access denied'));

        $sSubMenu = '';
        if (!$this->oSearch->oModule->isAllowedAdd()) {
            $sCode = MsgBox(_t('_' . $this->oConfig->getMainPrefix() . '_access_denied'));
        } else {
            require_once('BxPhotosUploader.php');
            $sLink = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/my/add_objects/' . rawurlencode($this->aAddParams[1]) . '/'. $this->aAddParams[2] . '/' . $this->aAddParams[3];
            $aMenu = $this->oConfig->getUploaderSwitcher($sLink);
            if (count($aMenu) > 1)
                $sSubMenu = $this->oTemplate->getExtraSwitcher($aMenu, '_' . $this->oConfig->getMainPrefix() . '_choose_uploader', $iBoxId);
            $oUploader = new BxPhotosUploader();
            $oUploader->sWorkingFile = $sLink;
            $sCode = $this->oTemplate->parseHtmlByName('default_margin.html', array('content' => $oUploader->GenMainAddFilesForm(array('album' => $this->aAddParams[1]))));
        }

        $GLOBALS['oTopMenu']->setCustomSubHeader($aAlbumInfo['Caption']);
        return array($sSubMenu . $sCode, $this->getTopMenu('add_objects'), '', '');
    }

    function getBlockCode_manageObjects ($iBoxId)
    {
        if(!in_array('manageObjects', $this->aCurrentBlocks['blocks']))
            return '';

        return array($this->getAdminObjectPart(array('activeStatus'=>'approved'), $iBoxId, true), $this->getTopMenu('manage_objects'), array(), '');
    }

    function getBlockCode_manageObjectsDisapproved ($iBoxId)
    {
        if(!in_array('manageObjectsDisapproved', $this->aCurrentBlocks['blocks']))
            return '';

        return array($this->getAdminObjectPart(array('activeStatus'=>'disapproved'), $iBoxId), $this->getTopMenu('manage_objects_disapproved'), array(), '');
    }

    function getBlockCode_manageObjectsPending ($iBoxId)
    {
        if(!in_array('manageObjectsPending', $this->aCurrentBlocks['blocks']))
            return '';

        return array($this->getAdminObjectPart(array('activeStatus'=>'pending'), $iBoxId), $this->getTopMenu('manage_objects_pending'), array(), '');
    }

    function getBlockCode_edit ()
    {
        if (!in_array('edit', $this->aCurrentBlocks['blocks']))
            return '';

        $aInfo = $this->oAlbum->getAlbumInfo(array('fileUri' => $this->aAddParams[1], 'owner' => $this->iOwnerId));
        if ($aInfo['Owner'] != $this->iOwnerId)
            $sCode = MsgBox(_t('_Access denied'));

        $aPrivFieldView = $this->oAlbumPrivacy->getGroupChooser($this->iOwnerId, $this->oConfig->getUri(), 'album_view');
        $aPrivFieldView['value'] = $aInfo['AllowAlbumView'];

        $aReInputs = array(
            'title' => array(
                'name' => 'Caption',
                'value' => $aInfo['Caption']
            ),
            'location' => array(
                'name' => 'Location',
                'value' => $aInfo['Location']
            ),
            'description' => array(
                'name' => 'Description',
                'value' => $aInfo['Description']
            ),
            'allow_view' => $aPrivFieldView,
            'id' => array(
                'type' => 'hidden',
                'name' => 'ID',
                'value' => (int)$aInfo['ID'],
            ),
            'uri' => array(
                'type' => 'hidden',
                'name' => 'Uri',
                'value' => $this->aAddParams[1],
            ),
        );

        $aReForm = array(
            'id' => $this->oConfig->getMainPrefix() . '_upload_form',
            'method' => 'post',
            'action' => $this->oConfig->getBaseUri().'albums/my/edit/' . strip_tags($this->aAddParams[1] . '/' . strip_tags($this->aAddParams[2]) . '/' . strip_tags($this->aAddParams[3]))
        );

        $aForm = $this->oTemplate->getAlbumFormEditArray($aReInputs, $aReForm);
        $oForm = new BxTemplFormView($aForm);
        $oForm->initChecker();
        if ($oForm->isSubmittedAndValid()) {
            $aFields = array('Caption', 'Location', 'Description', 'AllowAlbumView');
            $aData = array();
            foreach ($aFields as $sValue) {
                if (isset($_POST[$sValue]))
                    $aData[$sValue] = $_POST[$sValue];
            }
            if ($this->oAlbum->updateAlbumById((int)$_POST['ID'], $aData))
                $sKey = $this->oConfig->getMainPrefix() . '_album_save_success';
            else
                $sKey = 'Error';
            $sCode = MsgBox(_t('_' . $sKey));
        } else
            $sCode = $oForm->getCode();

        $GLOBALS['oTopMenu']->setCustomSubHeader($aInfo['Caption']);
        return array($sCode, $this->getTopMenu('edit'));
    }

    function getBlockCode_organize ()
    {
        if (!in_array('organize', $this->aCurrentBlocks['blocks']))
            return '';

        $aInfo = $this->oAlbum->getAlbumInfo(array('fileUri' => $this->aAddParams[1], 'owner' => $this->iOwnerId));
        if($aInfo['Owner'] != $this->iOwnerId)
            $sCode = MsgBox(_t('_Access denied'));

        $this->oSearch->clearFilters(array('activeStatus', 'allow_view', 'album_status'), array('albumsObjects', 'albums'));
        $this->oSearch->bAdminMode = false;
        $this->oSearch->aCurrent['view'] = 'short';

        $this->oSearch->aCurrent['restriction']['album']['value'] = $this->aAddParams[1];
        $this->oSearch->aCurrent['restriction']['albumType']['value'] = $this->oSearch->aCurrent['name'];
        $this->oSearch->aCurrent['restriction']['ownerId']['value'] = $this->iOwnerId;
        $this->oSearch->aCurrent['sorting'] = 'album_order';
        $this->oSearch->aCurrent['paginate']['perPage'] = 1000;
        $aUnits = $this->oSearch->getSearchData();
        if ($this->oSearch->aCurrent['paginate']['totalNum'] > 0) {
            $sMainUrl = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri();
            foreach ($aUnits as $aData)
                $sCode .= $this->oSearch->displaySearchUnit($aData);
            $aBtns = array(
                'action_reverse' => array(
                    'value' => _t('_' . $this->oConfig->getMainPrefix() . '_album_organize_reverse'),
                    'type' => 'submit',
                    'name' => 'reverse',
                    'onclick' => 'onclick=\'getHtmlData("unit_area", "' . $sMainUrl . 'album_reverse/' . $this->aAddParams[1] . '"); return false;\''
                )
            );
            $sAreaId = 'unit_area';
            $sManage = $this->oSearch->showAdminActionsPanel('', $aBtns, 'entry', false, false);
            $aUnit = array(
                'main_code_id' => $sAreaId,
                'main_code' => $sCode,
                'paginate' => '',
                'manage' => $sManage,
                'bx_if:hidden' => array(
                    'condition' => false,
                    'content' => array()
                )
            );
            $sJsCode = $this->oTemplate->parseHtmlByName('js_organize.html', array(
                'url' => $sMainUrl,
                'unit_area_id' => $sAreaId,
                'album_name' => $this->aAddParams[1],
                'add_params' => $this->aAddParams[2] . '/' . $this->aAddParams[3]
            ));
            $this->oTemplate->addCss('organize.css');
            $this->oTemplate->addJs(array('jquery.ui.core.min.js', 'jquery.ui.widget.min.js', 'jquery.ui.mouse.min.js', 'jquery.ui.sortable.min.js'));
            $sCode = $sJsCode . $this->oTemplate->parseHtmlByName('manage_form.html', $aUnit);
        } else
            $sCode = MsgBox(_t('_Empty'));

        $GLOBALS['oTopMenu']->setCustomSubHeader($aInfo['Caption']);
        return array($sCode, $this->getTopMenu('organize'), array(), '');
    }

    function getBlockCode_albumObjects ()
    {
        if(!in_array('albumObjects', $this->aCurrentBlocks['blocks']))
            return '';

        $this->oSearch->clearFilters(array('allow_view', 'album_status'), array('albumsObjects', 'albums'));
        $this->oSearch->aCurrent['sorting'] = 'album_order';
        $this->oSearch->bAdminMode = false;
        $this->oSearch->aCurrent['view'] = 'full';

        $this->oSearch->aCurrent['restriction']['activeStatus']['value'] = 'approved';
        $this->oSearch->aCurrent['restriction']['album']['value'] = $this->aAddParams[1];
        $this->oSearch->aCurrent['restriction']['albumType']['value'] = $this->oSearch->aCurrent['name'];
        $this->oSearch->aCurrent['restriction']['ownerId']['value'] = $this->iOwnerId;
        $this->oSearch->aCurrent['paginate']['perPage'] = $this->oConfig->getGlParam('number_all');

        $sCode = $this->oSearch->displayResultBlock();
        if(strlen($sCode) > 0) {
            $sUrlBody = '';
            for ($i = 1; $i < 4; $i++)
                $sUrlBody .= '/' . $this->aAddParams[$i];
            $sUrl = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'browse/album' . $sUrlBody . '&per_page=' . $this->oSearch->aCurrent['paginate']['totalNum'];
            $aBottomMenu = $this->oSearch->getBottomMenu($sUrl, $this->iOwnerId, getUsername($this->iOwnerId));
        } else {
            $sCode = $this->oSearch->addCustomParts() . MsgBox(_t("_Empty"));
            $aBottomMenu = array();
        }
        $aSections = array(
            'disapproved' => NULL,
            'pending' => NULL,
        );
        $sLangKey = '_' . $this->oConfig->getMainPrefix() . '_count_status_info';
        $sUnitKey = '_' . $this->oConfig->getMainPrefix() . '_album_manage_objects_';
        $sHref = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/my/manage_objects_{section}/' . $this->aAddParams[1] . '/owner/' . $this->aAddParams[3];
        $sInfo = '';
        $sMessage = '';
        foreach ($aSections as $sSection => $mixedStatus) {
            $mixedStatus = is_null($mixedStatus) ? $sSection : $mixedStatus;
            $aParams = array('albumUri' => $this->aAddParams[1], 'Approved' => $mixedStatus, 'medProfId' => $this->iOwnerId);
            $iCount = $this->oDb->getFilesCountByParams($aParams);
            if ($iCount > 0) {
                $sLangUnitKey = _t($sUnitKey . $sSection);
                $sMessage .= _t($sLangKey, $iCount, str_replace('{section}', $sSection, $sHref), $sLangUnitKey) . ' ';
            }
        }

        $sInfo = $this->oTemplate->parseHtmlByName('pending_approval_plank.html', array('msg' => $sMessage));
        $sCode = $this->oTemplate->parseHtmlByName('default_margin_thd.html', array('content' => $sCode));
        return array($sInfo . $sCode, array(), $aBottomMenu, '');
    }

    // support functions
    function getTopMenu ($sMode = 'main')
    {
        $aTopMenu = array();
        // do not display a menu add all
        if (!is_null($this->aCurrentBlocks['nomenu']))
            return $aTopMenu;

        if (strlen($this->aAddParams[1]) > 0) {
            $iCheck = 1;
            $sName = $this->aAddParams[1] . '/' . strip_tags($this->aAddParams[2]) . '/' . strip_tags($this->aAddParams[3]) . '/';
        } else
            $iCheck = 0;
        $sHrefPref = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri();
        foreach ($this->aSystemBlocks as $sKey => $aValue) {
            $sAdd  = $aValue['const'] == true ? '' : $sName;
            $sOwnLink = isset($aValue['link']) ? $aValue['link'] : 'albums/my/' . $sKey . '/';
            if ($aValue['level'] == $iCheck)
                $aTopMenu[_t('_bx_' . $this->oConfig->getUri() . '_album_' . $sKey)] = array(
                    'href' => $sHrefPref . $sOwnLink . $sAdd,
                    'active' => ($sMode == $sKey )
                );
        }
        return $aTopMenu;
    }

    function getAdminPart ($aCondition = array(), $aCustom = array())
    {
        $this->oSearch->clearFilters(array('allow_view', 'album_status'), array('albumsObjects', 'albums'));
        $iPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (int)$this->oConfig->getGlParam('number_albums_home');
        $iPage = isset($_GET['page']) ? (int)$_GET['page'] : $this->oSearch->aCurrent['paginate']['page'];
        $this->oSearch->bAdminMode = true;
        $aCondition['show_empty'] = true;
        $aCondition['hide_default'] = true;
        $aCondition['owner'] = $this->iOwnerId;

        $sCode = $this->oSearch->getAlbumList($iPage, $iPerPage, $aCondition);

        $iCount = $this->oSearch->aCurrent['paginate']['totalAlbumNum'];
        $aBtns = array(
            0 => array(
                'type' => 'submit',
                'name' => 'action_delete',
                'value' => _t('_Delete'),
                'onclick' => 'onclick="return confirm(\'' . bx_js_string(_t('_Are_you_sure')) . '\');"',
            )
        );
        $sPaginate = '';
        if($iCount > $iPerPage) {
            $sSection = isset($aCustom['section']) ? strip_tags($aCustom['section']) : '';
            if ($this->oConfig->isPermalinkEnabled)
                $sSection .= '?';
            $iId = isset($aCustom['page_block_id']) ? (int)$aCustom['page_block_id'] : 1;
            $aLinkAddon = $this->oSearch->getLinkAddByPrams();
            $sLink = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/my/';
            $sLinkJs = $sLink . $sSection . $aLinkAddon['params'];
            $oPaginate = new BxDolPaginate(array(
                'page_url' => $sLink,
                'count' => $iCount,
                'per_page' => $iPerPage,
                'page' => $iPage,
                'on_change_page' => 'return !loadDynamicBlock(' . $iId . ', \'' . $sLinkJs . '&page={page}&per_page={per_page}\');',
                'on_change_per_page' => 'return !loadDynamicBlock(' . $iId . ', \'' . $sLinkJs . '&page=1&per_page=\' + this.value);'
            ));
            $sPaginate = $oPaginate->getSimplePaginate(BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums/browse/owner/' . getUsername($this->iOwnerId));
        }
        $sManage = $this->oSearch->showAdminActionsPanel($this->oSearch->aCurrent['name'] . '_admin_form', $aBtns);
        $aUnit = array(
            'main_code' => $sCode,
            'paginate' => $sPaginate,
            'manage' => $sManage
        );
        return $this->oTemplate->parseHtmlByName('manage_form_albums.html', $aUnit);
    }

    function getAdminObjectPart ($aCondition = array(), $iBoxId = 0, $bShowMove = false)
    {
        $aInfo = $this->oAlbum->getAlbumInfo(array('fileUri' => $this->aAddParams[1], 'owner' => $this->iOwnerId));
        if($aInfo['Owner'] != $this->iOwnerId)
            return MsgBox(_t('_Access denied'));

        $GLOBALS['oTopMenu']->setCustomSubHeader($aInfo['Caption']);

        $this->oSearch->clearFilters(array('activeStatus', 'allow_view', 'album_status'), array('albumsObjects', 'albums'));
        $this->oSearch->aCurrent['paginate']['perPage'] = (int)$this->oConfig->getGlParam('number_all');
        $this->oSearch->bAdminMode = true;
        $this->oSearch->aCurrent['sorting'] = 'album_order';
        $this->oSearch->aCurrent['view'] = 'full';

        $aMainParams = array(
            'album' => $this->aAddParams[1],
            'albumType' => $this->oSearch->aCurrent['name'],
            'ownerId' => $this->iOwnerId
        );

        $this->oSearch->fillFilters(array_merge($aCondition, $aMainParams));
        //manage inputs sections
        $aBtns = array(
            'action_delete_object' => _t('_Delete'),
        );

        $aUserAlbums = $this->oAlbum->getAlbumList(array('owner' => $this->iOwnerId, 'show_empty' => true, 'hide_default' => true), 0, 0, true);
        foreach ($aUserAlbums as $aValue) {
            if ($aValue['Uri'] != $this->aAddParams[1]) {
                $aAlbums[] = array(
                    'album_id' => $aValue['ID'],
                    'album_caption' => $aValue['Caption']
                );
            } else {
                $aAlbumInfo = array('ID' => $aValue['ID']);
                $this->oSearch->aCurrent['restriction']['allow_view']['value'] = array($aValue['AllowAlbumView']);
            }
        }

        $sMoveToAlbum = '';
        if (count($aUserAlbums) > 1 && $bShowMove) {
            $aBtns['action_move_to'] = _t('_sys_album_move_to_another');
            $sMoveToAlbum = $this->oTemplate->parseHtmlByName('albums_select.html', array('bx_repeat:choose' => $aAlbums));
        }

        $sCode = $this->oSearch->displayResultBlock();
        if ($this->oSearch->aCurrent['paginate']['totalNum'] == 0)
            $sCode = MsgBox(_t('_Empty'));
        else {
            $sPaginate = $this->oSearch->getBottomMenu('browseUserAll', $this->iOwnerId, getUsername($this->iOwnerId));
            if($iBoxId > 0)
                $sPaginate = str_replace('{id}', $iBoxId, $sPaginate);
        }
        $sManage = $this->oSearch->showAdminActionsPanel($this->oSearch->aCurrent['name'] . '_admin_form', $aBtns, 'entry', true, false, $sMoveToAlbum);
        $aUnit = array(
            'main_code_id' => 'unit_area',
            'main_code' => $sCode,
            'paginate' => $sPaginate,
            'manage' => $sManage,
            'bx_if:hidden' => array(
                'condition' => (int)$aAlbumInfo['ID'] != 0,
                'content' => array(
                    'hidden_name' =>  'album_id',
                    'hidden_value' => (int)$aAlbumInfo['ID']
                )
            )
        );
        return $this->oTemplate->parseHtmlByName('manage_form.html', $aUnit);
    }
}
