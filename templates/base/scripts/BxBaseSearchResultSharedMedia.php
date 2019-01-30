<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxBaseSearchResult');
bx_import('BxDolAlbums');
bx_import('BxTemplVotingView');

class BxBaseSearchResultSharedMedia extends BxBaseSearchResult
{
	var $bDynamic = false;
    var $bAdminMode = false;

    var $sTemplUnit;
    var $aConstants = array();

    var $aPermalinks = array();

    var $sProfileCatType;

    // additional tables parameters (rate, favorite ...)
    var $aAddPartsConfig = array();

    var $oTemplate;
    var $sCurrentAlbum;

    var $sModuleClass;
    var $oModule;

    var $oPrivacy;

    function __construct ($sModuleClass = '')
    {
        /* main settings for shared modules
           ownFields - fields which will be got from main table ($this->aCurrent['table'])
           searchFields - fields which using for full text key search
           join - array of join tables
                join array (
                    'type' - type of join
                    'table' - join table
                    'mainField' - field from main table for 'on' condition
                    'onField' - field from joining table for 'on' condition
                    'joinFields' - array of fields from joining table
                )
        */

        $this->aCurrent = array(
            'ownFields' => array('ID', 'Title', 'Uri', 'Date', 'Time', 'Rate', 'RateCount'),
            'searchFields' => array('Title', 'Tags', 'Description', 'Categories'),
            'join' => array(
                'profile' => array(
                    'type' => 'left',
                    'table' => 'Profiles',
                    'mainField' => 'Owner',
                    'onField' => 'ID',
                    'joinFields' => array('NickName')
                ),
                'albumsObjects' => array(
                    'type' => 'left',
                    'table' => 'sys_albums_objects',
                    'mainField' => 'ID',
                    'onField' => 'id_object',
                    'joinFields' => ''
                ),
                'albums' => array(
                    'type' => 'left',
                    'table' => 'sys_albums',
                    'mainField' => 'id_album',
                    'onField' => 'ID',
                    'joinFields' => array('AllowAlbumView'),
                    'mainTable' => 'sys_albums_objects'
                )
            ),
            'restriction' => array(
                'activeStatus' => array('value'=>'approved', 'field'=>'Status', 'operator'=>'=', 'paramName'=>'status'),
                'owner' => array('value'=>'', 'field'=>'Owner', 'operator'=>'=', 'paramName'=>'userID'),
                'ownerStatus' => array('value'=>array('Rejected', 'Suspended'), 'operator'=>'not in', 'paramName'=>'ownerStatus', 'table'=>'Profiles', 'field'=>'Status'),
                'tag' => array('value'=>'', 'field'=>'Tags', 'operator'=>'against', 'paramName'=>'tag'),
                'category'=> array('value'=>'', 'field'=>'Categories', 'operator'=>'against', 'paramName'=>'categoryUri'),
                'id' => array('value'=>'', 'field'=>'ID', 'operator'=>'in'),
                'allow_view' => array('value'=>'', 'field'=>'AllowAlbumView', 'operator'=>'in', 'table'=> 'sys_albums'),
                'not_allow_view' => array('value'=>'', 'field'=>'AllowAlbumView', 'operator'=>'not in', 'table'=> 'sys_albums'),
                'album_status' => array('value'=>'active', 'field'=>'Status', 'operator'=>'=', 'table'=> 'sys_albums'),
                'albumType' => array('value'=>'', 'field'=>'Type', 'operator'=>'=', 'paramName'=>'albumType', 'table'=>'sys_albums'),
            ),
            'paginate' => array('perPage' => 10, 'page' => 1, 'totalNum' => 10, 'totalPages' => 1),
            'sorting' => 'last',
            'view' => 'full',
            'rss' => array(
                'title' => '',
                'link' => '',
                'image' => '',
                'profile' => 0,
                'fields' => array (
                    'Link' => '',
                    'Title' => 'title',
                    'DateTimeUTS' => 'date',
                    'Desc' => 'desc',
                    'Image' => '',
                ),
            ),
        );
        // favorite config, basic for all media modules
        $this->aAddPartsConfig['favorite'] = array(
            'type' => 'inner',
            'table' => '',
            'mainField' => 'ID',
            'onField' => 'ID',
            'userField' => 'Profile',
            'joinFields' => ''
        );
        $this->aPseud = $this->_getPseud();
        parent::__construct();

        $this->sModuleClass = $sModuleClass;
        $this->oModule = BxDolModule::getInstance($this->sModuleClass);
        $this->oTemplate = $GLOBALS['oSysTemplate'];

        $sClassName = $this->oModule->_oConfig->getClassPrefix() . 'Privacy';
        bx_import('Privacy', $this->oModule->_aModule);
        $this->oPrivacy = new $sClassName('sys_albums', 'ID', 'Owner');
        $this->sTemplUnit = 'browse_unit';

        $this->bDynamic = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    function getCurrentUrl ($sType, $iId = 0, $sUri = '', $aOwner = '')
    {
        $sLink = $this->aConstants['linksTempl'][$sType];
        return BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . str_replace('{uri}', $sUri, $sLink);
    }

    function displaySearchUnit ($aData)
    {
        $bFull = isset($this->aCurrent['view']) && $this->aCurrent['view'] == 'short' ? false : true;
        if ($this->oModule->isAdmin($this->oModule->_iProfileId) || is_array($this->aCurrent['restriction']['allow_view']['value']))
            $bVis = true;
        elseif ($this->oPrivacy->check('album_view', $aData['id_album'], $this->oModule->_iProfileId))
            $bVis = true;
        else
            $bVis = false;

        if (!$bVis) {
            $aUnit = array(
               'bx_if:show_title' => array(
                   'condition' => $bFull,
                   'content' => array(1)
               )
            );
            $sCode = $this->oTemplate->parseHtmlByName('browse_unit_private.html', $aUnit);
        } 
        else
            $sCode = !$bFull ? $this->getSearchUnitShort($aData) : $this->getSearchUnit($aData);

        return $sCode;
    }

    function getSearchUnitShort ($aData)
    {
        $sCode = '
            <div class="sys_file_search_unit_short" id="unit_{id}">
                {pic}
            </div>
        ';
        $aUnit['id'] = $aData['id'];
        $sFileLink = $this->getCurrentUrl('file', $aData['id'], $aData['uri']);
        // pic
        $aUnit['pic'] = $this->_getSharedThumb($aData['id'], $sFileLink, $aData['Hash']);
        return $this->_transformData($aUnit, $sCode, $this->sCssPref);
    }

    function getSearchUnit ($aData)
    {
        $aUnit = array();
        $aUnit['main_prefix'] = $this->oModule->_oConfig->getMainPrefix();

        $aUnit['id'] = $aData['id'];
        $sFileLink = $this->getCurrentUrl('file', $aData['id'], $aData['uri']);
        // pic
        $aUnit['pic'] = $this->_getSharedThumb($aData['id'], $sFileLink, $aData['Hash']);
        // rate
        $aUnit['rate'] = '';
        if (!is_null($this->oRate) && $this->oRate->isEnabled()) {
            if ($this->sTemplUnit == 'browse_unit')
                $aUnit['rate'] = $this->oRate->getSmallVoting(0, $aData['Rate']);
            else {
                $oRate = new BxTemplVotingView($this->aCurrent['name'], $aData['id']);
                $aUnit['rate'] = $oRate->getSmallVoting(0);
            }
        }
        // size
        $aUnit['size'] = isset($aData['size']) ? $this->getLength($aData['size']) : '';
        // title
        $aUnit['titleLink'] = $sFileLink;
        $aUnit['title'] = stripslashes($aData['title']);

        // when
        $aUnit['when'] = defineTimeInterval($aData['date']);

        // from
        $aUnit['fromLink'] = getProfileLink($aData['ownerId']);
        $aUnit['from'] = getNickName($aData['ownerId']);

        // view
        $aUnit['view'] = $aData['view'];
        return $this->oTemplate->parseHtmlByName($this->sTemplUnit . '.html', $aUnit, array('{', '}'));
    }

    function getCurrentAlbum ($sAlbumUri)
    {
        $this->sCurrentAlbum = strip_tags($sAlbumUri);
    }

    function getLength ($iTime)
    {
        $iTime = (int)round($iTime/1000);
        if ($iTime < 60) {
            $aLength[1] = '0';
            $aLength[0] = $iTime;
        } elseif ($iTime < 3600) {
            $aLength[1] = (int)($iTime/60);
            $aLength[0] = $iTime%60;
        } else {
            $aLength[2] = (int)($iTime/3600);
            $iOther = $iTime - $aLength[2]*3600;
            $aLength[1] = (int)($iOther/60);
            $aLength[0] = $iOther%60;
        }
        $sCode = '';
        for ($i = count($aLength)-1; $i >= 0; $i--) {
            $sCode .= strlen($aLength[$i]) < 2 ? '0' . $aLength[$i] : $aLength[$i];
            $sCode .= ':';
        }
        return	trim($sCode, ':');
    }

    function displayMenu ()
    {
        $aDBTopMenu = $this->getTopMenu(array($this->aCurrent['name'] . '_mode'));
        $aDBBottomMenu = $this->getBottomMenu();
        return array( $aDBTopMenu, $aDBBottomMenu );
    }

    function getAlterOrder()
    {
        $aSql = array();
        switch ($this->aCurrent['sorting']) {
            case 'popular':
                $aSql['order'] = " ORDER BY `{$this->aCurrent['table']}`.`Views` DESC";
                break;
            case 'album_order':
                $aSql['order'] = " ORDER BY `obj_order` ASC, `id_object` DESC";
                break;
            default:
        }
        return $aSql;
    }

    function getTopMenu ($aExclude = array())
    {
        $aDBTopMenu = array();
        $aLinkAddon = $this->getLinkAddByPrams($aExclude);
        foreach (array('last', 'top') as $sMyMode) {
              switch ($sMyMode) {
                   case 'last':
                         $sModeTitle = '_Latest';
                            break;
                   case 'top':
                        $sModeTitle = '_Top';
                        break;
              }

         if (basename( $_SERVER['PHP_SELF'] ) == 'rewrite_name.php' || basename( $_SERVER['PHP_SELF'] ) == 'profile.php')
                $sLink = BX_DOL_URL_ROOT . "profile.php?ID={$this->aCurrent['restriction']['owner']['value']}&";
            else
                $sLink = bx_html_attribute($_SERVER['PHP_SELF']) . "?";
            $sLink .= $this->aCurrent['name'] . "_mode=$sMyMode" . $aLinkAddon['params'] . $aLinkAddon['paginate'] . $aLinkAddon['type'];

              $aDBTopMenu[$sModeTitle] = array('href' => $sLink, 'dynamic' => true, 'active' => ($sMyMode == $this->aCurrent['sorting']));
        }
        return $aDBTopMenu;
    }
    function getBrowseBlock ($aParams, $aCustom = array(), $sMainUrl = '', $bClearJoins = true)
    {
        if(!isset($aCustom['sorting']))
            $aCustom['sorting'] = 'album_order';

        return parent::getBrowseBlock($aParams, $aCustom, $sMainUrl, $bClearJoins);
    }
    function getLatestFile ()
    {
        $aWhere[] = "1";
        foreach( $this->aCurrent['restriction'] as $sKey => $aValue ) {
            if (isset($aValue['value'])) {
                switch ($sKey) {
                    case 'featured':
                    case 'owner':
                        if ((int)$aValue['value'] != 0)
                            $aWhere[] = "`{$this->aCurrent['table']}`.`{$aValue['field']}` = '" . (int)$aValue['value'] . "'";
                        break;
                    case 'category':
                    case 'tag':
                        if (strlen($aValue['value']) > 0)
                            $aWhere[] = "MATCH(`{$this->aCurrent['table']}`.`{$aValue['field']}`) AGAINST ('" . trim(process_db_input($aValue['value'], BX_TAGS_STRIP)) . "')";
                        break;
                    case 'allow_view':
                        if (is_array($aValue['value'])) {
                            $sqlJoin = "LEFT JOIN `sys_albums_objects` ON `sys_albums_objects`.`id_object`=`{$this->aCurrent['table']}`.`{$this->aCurrent['ident']}`
                                        LEFT JOIN `sys_albums` ON `sys_albums_objects`.`id_album`=`sys_albums`.`ID`
                            ";
                            $sqlCode = "`AllowAlbumView` IN(";
                            foreach ($aValue['value'] as $sValue)
                                $sqlCode .= "$sValue, ";
                            $aWhere[] = rtrim($sqlCode, ", ") . ')';
                        }
                        break;
                }
            }
        }
        $sqlWhere = "WHERE " . implode( ' AND ', $aWhere ) . " AND `{$this->aCurrent['table']}`.`Status`= 'approved'";
        $sqlQuery = "SELECT `{$this->aCurrent['table']}`.`{$this->aCurrent['ident']}` as `{$this->aCurrent['ident']}` FROM `{$this->aCurrent['table']}` $sqlJoin $sqlWhere ORDER BY `{$this->aCurrent['ident']}` DESC LIMIT 1";
        $iFileId = db_value($sqlQuery);
        $sCode = '';
        if ($iFileId != 0) {
            $aInfo = $this->oModule->_oDb->getFileInfo(array('fileId' => $iFileId));

            $oRate = new BxTemplVotingView($this->aCurrent['name'], $aInfo['medID']);            
            $aDraw = array(
                'file' => $this->oTemplate->getFileConcept($aInfo['medID'], array('ext'=>$aInfo['medExt'], 'source'=>$aInfo['medSource'])),
                'file_url' => $this->getCurrentUrl('file', $aInfo['medID'], $aInfo['medUri']),
                'title' => $aInfo['medTitle'],
                'rate' => $oRate->getSmallVoting(0),
                'date' => defineTimeInterval($aInfo['medDate']),
                'owner_url' => getProfileLink($aInfo['medProfId']),
                'owner_nick' => getNickName($aInfo['medProfId'])
            );

            $this->oTemplate->addCss('view.css');
            $sCode = $this->oTemplate->parseHtmlByName('latest_file.html', $aDraw);
        }
        return $sCode;
    }

    function _getSharedThumb ($iId, $sFileLink, $sHash = '')
    {
        $sIdent = strlen($sHash) > 0 ? $sHash : $iId;
        $aUnit = array(
            'imgUrl' => $this->getImgUrl($sIdent, 'browse'),
        	'imgUrl_2x' => $this->getImgUrl($sIdent, 'browse2x'),
            'fileLink' => $sFileLink,
            'bx_if:admin' => array(
                'condition' => $this->bAdminMode,
                'content' => array('id' => $iId)
            )
        );
        return $this->oModule->_oTemplate->parseHtmlByName('thumb.html', $aUnit);
    }

    function displaySearchBox ($sCode, $sPaginate = '', $bAdminBox = false)
    {
        $sCode = $GLOBALS['oFunctions']->centerContent($sCode, '.sys_file_search_unit') . '<div class="clear_both"></div>';
        $sTitle = _t($this->aCurrent['title']);
        $sFunc = !$bAdminBox ? 'Content' : 'Admin';
        $sCode = call_user_func('DesignBox' . $sFunc, $sTitle, '<div class="searchContentBlock">' . $sCode . '</div>' .$sPaginate, 1);
        if (!isset($_GET['searchMode']))
           $sCode = '<div id="page_block_' . $this->id . '">' . $sCode . '</div>';
        return $sCode;
    }

    function getImgUrl ($iId, $sImgType = 'browse')
    {
        $iId = (int)$iId;
        $sPostFix = isset($this->aConstants['picPostfix'][$sImgType]) ? $this->aConstants['picPostfix'][$sImgType] : $this->aConstants['picPostfix']['browse'];
        if(!file_exists($this->aConstants['filesDir'] . $iId . $sPostFix))
        	return '';

        return $this->aConstants['filesUrl'] . $iId . $sPostFix;
    }

    function getFilesInCatArray ($iId, $sCategory = '')
    {
        $this->clearFilters();
        $this->aCurrent['restriction']['owner']['value'] = $iId;
        $this->aCurrent['paginate']['perPage'] = 1000;
        $this->aCurrent['join']['category'] = array(
            'type' => 'left',
            'table' => 'sys_categories',
            'mainField' => $this->aCurrent['ident'],
            'onField' => 'ID',
            'joinFields' => array('Category')
        );
        $this->aCurrent['restriction']['category'] = array(
            'value' => $sCategory,
            'field' => 'Category',
            'operator' => '=',
            'table' => 'sys_categories'
        );

        $this->aCurrent['restriction']['type'] = array(
            'value' => $this->aCurrent['name'],
            'field' => 'Type',
            'operator' => '=',
            'table' => 'sys_categories'
        );

        $aFiles = $this->getSearchData();
        if (!$aFiles)
            $aFiles = array();
        return $aFiles;
    }

    function getFilesInAlbumArray ($iAlbumId, $aLimits = array())
    {
            $iAlbumId = (int)$iAlbumId;
            if (!$iAlbumId)
                return array();
        $this->clearFilters(array('activeStatus'));
        $this->aCurrent['join']['albumsObjects'] = array(
            'type' => 'left',
            'table' => 'sys_albums_objects',
            'mainField' => 'ID',
            'onField' => 'id_object',
            'joinFields' => array('obj_order')
        );
        $this->aCurrent['sorting'] = 'album_order';
        if ($aLimits['page'])
            $this->aCurrent['paginate']['page'] = (int)$aLimits['page'];
        if (isset($aLimits['per_page']) && $aLimits['per_page'] !== false)
            $this->aCurrent['paginate']['perPage'] = (int)$aLimits['per_page'];
        $this->aCurrent['restriction']['album'] = array(
            'value'=>$iAlbumId, 'field'=>'id_album', 'operator'=>'=', 'paramName'=>'albumId', 'table'=>'sys_albums_objects'
        );
        $aFiles = $this->getSearchData();
        if (!$aFiles)
            $aFiles = array();
        return $aFiles;
    }

    function getProfileFiles ($iProfId, $aLimits = array())
    {
        $this->clearFilters(array(), array('albumsObjects', 'albums'));
        if ($aLimits['page'])
            $this->aCurrent['paginate']['page'] = (int)$aLimits['page'];
        if ($aLimits['per_page'])
            $this->aCurrent['paginate']['perPage'] = $aLimits['per_page'];
        $this->aCurrent['restriction']['activeStatus']['value'] = 'approved';
        $this->aCurrent['restriction']['owner']['value'] = (int)$iProfId;
        $aFiles = $this->getSearchData();
        if (!$aFiles)
            $aFiles = array();
        return $aFiles;
    }

    // browse functions
    function addCustomParts ()
    {
        if (!$this->bCustomParts) {
            $this->bCustomParts = true;
            $sModulePart = $this->getModuleFolder() . '/';
            $this->oTemplate->addLocation($this->aCurrent['name'], BX_DIRECTORY_PATH_MODULES . $sModulePart, BX_DOL_URL_MODULES . $sModulePart);
            $this->oTemplate->addCss(array('search.css'));
            //$this->oTemplate->removeLocation($this->aCurrent['name']);
            return '';
        }
    }

    function getModuleFolder ()
    {
        return 'boonex/'.$this->aCurrent['name'];
    }

    function addAlbumJsCss($bDynamic = false)
    {
    	$sResult = $this->oTemplate->addJs(array(
    		'modernizr.js', 
    		'BxDolAlbums.js'
    	), $bDynamic);

    	return $bDynamic ? $sResult : '';
    }

    function getAlbumList ($iPage = 1, $iPerPage = 10, $aCond = array())
    {
        $oSet = new BxDolAlbums($this->aCurrent['name']);
        foreach ($this->aCurrent['restriction'] as $sKey => $aParam) {
            if (!empty($aParam['value']))
                $aData[$sKey] = $aParam['value'];
        }
        $aData = array_merge($aData, $aCond);
        $iAlbumCount = $oSet->getAlbumCount($aData);
        $this->aCurrent['paginate']['totalAlbumNum'] = $iAlbumCount;
        if ($iAlbumCount > 0) {
            $sCode = $this->addCustomParts();
            $aList = $oSet->getAlbumList($aData, (int)$iPage, (int)$iPerPage);
            $bCheckPrivacy = isset($aData['allow_view']) ? false : true;
            foreach ($aList as $aData)
                $sCode .= $this->displayAlbumUnit($aData, $bCheckPrivacy);
        } else
            $sCode = MsgBox(_t('_Empty'));

		$sCode .= $this->addAlbumJsCss($this->bDynamic);
        return $this->oTemplate->parseHtmlByName('album_units.html', array('content' => $sCode));
    }

    function getAlbumsBlock ($aSectionParams = array(), $aAlbumParams = array(), $aCustom = array())
    {
        $aCustomTmpl = array(
            'caption' => _t('_' . $this->oModule->_oConfig->getMainPrefix() .'_albums'),
            'enable_center' => true,
            'unit_css_class' => '.sys_album_unit',
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'per_page' => isset($_GET['per_page']) ? (int)$_GET['per_page']: (int)$this->oModule->_oConfig->getGlParam('number_albums_home'),
            'simple_paginate' => true,
            'menu_top' => '',
            'menu_bottom' => '',
            'paginate_url' => '',
            'simple_paginate_url' => BX_DOL_URL_ROOT . $this->oModule->_oConfig->getUri() . '/albums/browse',
            'simple_paginate_view_all' => true
        );
        $aCustom = array_merge($aCustomTmpl, $aCustom);
        $this->aCurrent['paginate']['perPage'] = $aCustom['per_page'];
        $this->aCurrent['paginate']['page'] = $aCustom['page'];

        $this->fillFilters($aSectionParams);
        $sCode = $this->getAlbumList($this->aCurrent['paginate']['page'], $this->aCurrent['paginate']['perPage'], $aAlbumParams);
        if ($this->aCurrent['paginate']['totalAlbumNum'] > 0) {
            if ($aCustom['enable_center'])
                $sCode = $GLOBALS['oFunctions']->centerContent($sCode, $aCustom['unit_css_class']);
            if (empty($aCustom['menu_bottom'])) {
                $aLinkAddon = $this->getLinkAddByPrams(array('r'));
                $oPaginate = new BxDolPaginate(array(
                    'page_url' => $aCustom['paginate_url'],
                    'count' => $this->aCurrent['paginate']['totalAlbumNum'],
                    'per_page' => $this->aCurrent['paginate']['perPage'],
                    'page' => $this->aCurrent['paginate']['page'],
                    'on_change_page' => 'return !loadDynamicBlock({id}, \'' . $aCustom['paginate_url'] . $aLinkAddon['params'] .'&page={page}&per_page={per_page}\');',
                ));
                $aCode['menu_bottom'] = $aCustom['simple_paginate'] ? $oPaginate->getSimplePaginate($aCustom['simple_paginate_url'], -1, -1, $aCustom['simple_paginate_view_all']) : $oPaginate->getPaginate();
            } else
                $aCode['menu_bottom'] = $aCustom['menu_bottom'];
            $aCode['code'] = DesignBoxContent($aCustom['caption'], $sCode);
        }
        $aCode['menu_top'] = $aCustom['menu_top'];
        return array($aCode['code'], $aCode['menu_top'], $aCode['menu_bottom'], (!empty($aCode['code']) ? false : ''));
    }

    function getAlbumCovers ($iAlbumId, $aParams = array())
    {
        $iAlbumId = (int)$iAlbumId;
        $iLimit = isset($aParams['filesInAlbumCover']) ? (int)$aParams['filesInAlbumCover'] : null;
        return $this->oModule->oAlbums->getAlbumCoverFiles($iAlbumId, array('table'=>$this->aCurrent['table'], 'field'=> 'ID'), array(array('field'=>'Status', 'value'=>'approved')), $iLimit);
    }

    function getAlbumCoverUrl (&$aIdent)
    {
        return $this->getImgUrl($aIdent['id_object'], 'thumb');
    }

    function displayAlbumUnit ($aData, $bCheckPrivacy = true)
    {
    	$bOwner = $this->oModule->_iProfileId == $aData['Owner'];

        if(!$this->bAdminMode && $bCheckPrivacy) {
            if(!$this->oPrivacy->check('album_view', $aData['ID'], $this->oModule->_iProfileId)) {
                $aUnit = array(
                    'type' => $this->aCurrent['name']
                );
                return $this->oTemplate->parseHtmlByName('album_unit_private.html', $aUnit);
            }
        }

        $sLink = $this->getCurrentUrl('album', $aData['ID'], $aData['Uri']) . '/owner/' . getUsername($aData['Owner']);
        $aUnit = array(
            'type' => $aData['Type'],
            'bx_if:editMode' => array(
                'condition' => $this->bAdminMode,
                'content' => array(
                    'id' => $aData['ID'],
                    'checked' => $this->sCurrentAlbum == $aData['Uri'] ? 'checked="checked"' : ''
                )
            ),
            'albumUrl' => $sLink,
            'bx_repeat:units' => array(),
            'title' => $aData['Caption'],
            'titleLink' => stripcslashes($sLink),
            'from' => getNickName($aData['Owner']),
            'fromLink' => getProfileLink($aData['Owner']),
            'view' => isset($aData['ObjCount']) ? $aData['ObjCount'] . ' ' . _t($this->aCurrent['title']): '',
            'when' => defineTimeInterval($aData['Date'])
        );

        $iPics = isset($this->aConstants['filesInAlbumCover']) ? (int)$this->aConstants['filesInAlbumCover'] : 15;
        $aPics = $this->getAlbumCovers($aData['ID'], array('filesInAlbumCover' => $iPics));
        $bPics = is_array($aPics) && count($aPics) > 0;

        if(!$bPics && !$bOwner) {
            $this->aCurrent['paginate']['totalAlbumNum']--;
            return '';
        }
        else if(!$bPics && $bOwner) {
        	if(isset($this->aConstants['filesInEmptyAlbumCover']))
        		$iPics = $this->aConstants['filesInEmptyAlbumCover'];
        }

        $aUnit['bx_repeat:units'] = array();
        for($i = 0; $i < $iPics; $i++)
            $aUnit['bx_repeat:units'][] = $this->_getAlbumUnitItem($i, array_shift($aPics), array('album_url' => $sLink));

		$sResult = $this->oTemplate->parseHtmlByName('album_unit.html', $aUnit, array('{','}'));
		if(!empty($aData['show_as_list'])) {
			$sResult .= $this->addAlbumJsCss($this->bDynamic);
	        $sResult = $this->oTemplate->parseHtmlByName('album_units.html', array('content' => $sResult));

	        if(!empty($aData['enable_center']))
				$sResult = $GLOBALS['oFunctions']->centerContent($sResult, '.sys_album_unit');
		}

        return $sResult;
    }

    function _getAlbumUnitItem($iIndex, $aPicture, $aParams = array())
    {
    	$sClass = '';

		$bPicture = isset($aPicture['id_object']) && (int)$aPicture['id_object'] > 0;
		return array(
        	'bx_if:exist' => array(
            	'condition' => $bPicture,
                'content' => array(
            		'class' => $sClass,
                    'unit' => $bPicture ? $this->getAlbumCoverUrl($aPicture) : '',
				)
			),
            'bx_if:not-exist' => array(
            	'condition' => !$bPicture,
                'content' => array()
			)
		);
    }

    function serviceGetLength ($iTime)
    {
        return $this->getLength ($iTime);
    }

    function serviceGetFilesInCat ($iId, $sCategory = '')
    {
    }

    function serviceGetFilesInAlbum ($iAlbum)
    {
    }

    function serviceGetAlbumPrivacy ($iAlbumId, $iViewer = 0)
    {
        if (!$iViewer)
            $iViewer = $this->oModule->_iProfileId;
        return $this->oModule->oAlbumPrivacy->check('album_view', (int)$iAlbumId, $iViewer);
    }

    function serviceGetProfileAlbumsBlock ($iProfileId, $sSpecUrl = '')
    {
        $iProfileId   = (int)$iProfileId;
        $sNickName    = getUsername($iProfileId);
        $sSimpleUrl   = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'albums/browse/owner/' . $sNickName;
        $sPaginateUrl = mb_strlen($sSpecUrl) > 0 ? strip_tags($sSpecUrl) : getProfileLink($iProfileId);
        return $this->getAlbumsBlock(array(), array('owner' => $iProfileId, 'hide_default' => TRUE), array('enable_center' => true, 'paginate_url' => $sPaginateUrl, 'simple_paginate_url' => $sSimpleUrl));
    }

    function serviceGetProfileAlbumFiles ($iProfileId)
    {
        $iProfileId = (int)$iProfileId;
        $sNickKey = '{nickname}';
        $sNickName = getUsername($iProfileId);
        $sDefAlbumName = $this->oModule->_oConfig->getGlParam('profile_album_name');
        if (strpos($sDefAlbumName, $sNickKey) !== false)
            $sCaption = str_replace($sNickKey, $sNickName, $sDefAlbumName);
        else {
            $sCaption = $sDefAlbumName;
            $this->aCurrent['restriction']['album_owner'] = array(
                'value'=>$iProfileId, 'field'=>'Owner', 'operator'=>'=', 'paramName'=>'albumOwner', 'table'=>'sys_albums'
            );
        }
        $sUri = uriFilter($sCaption);
        $this->aCurrent['sorting'] = 'album_order';
        $this->aCurrent['restriction']['album'] = array(
            'value'=>$sUri, 'field'=>'Uri', 'operator'=>'=', 'paramName'=>'albumUri', 'table'=>'sys_albums'
        );
        $aFiles = $this->getSearchData();
        if (is_array($aFiles)) {
            foreach ($aFiles as $iKey => $aData)
                $aFiles[$iKey]['file'] = $this->getImgUrl($aData['id'], 'icon');
        } else
            $aFiles = array();
        return $aFiles;
    }

    function checkMemAction ($iFileOwner, $sAction = 'view')
    {
        $iFileOwner = (int)$iFileOwner;
        $sAction = clear_xss($sAction);
        if ($this->oModule->isAdmin($this->oModule->_iProfileId) || $iFileOwner == $this->oModule->_iProfileId) return true;
        $this->oModule->_defineActions();
        $aCheck = checkAction($this->oModule->_iProfileId, $this->oModule->_defineActionName($sAction));
        if ($aCheck[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED)
            return false;
        return true;
    }

    function getRssUnitLink (&$a)
    {
        return BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'view/' . $a['uri'];
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostComment($aEvent, $aParams = array())
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aItem = $this->serviceGetEntry($iId, 'browse');
        if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || !isset($aContent['comment_id']))
            return '';

		if(!$this->oPrivacy->check('album_view', (int)$aItem['album_id'], $this->oModule->_iProfileId))
        	return '';

        bx_import('BxTemplCmtsView');
        $oCmts = new BxTemplCmtsView($this->oModule->_oConfig->getMainPrefix(), $iId);
        if(!$oCmts->isEnabled())
            return '';

        $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);

        $sCss = '';
        $sUri = $this->oModule->_oConfig->getUri();
        if($aEvent['js_mode'])
            $sCss = $this->oModule->_oTemplate->addCss('wall_post.css', true);
        else
            $this->oModule->_oTemplate->addCss('wall_post.css');

        $sTextAddedNew = _t('_bx_' . $sUri . '_wall_added_new_comment');
        $sTextWallObject = _t('_bx_' . $sUri . '_wall_object');

        $sTmplName = isset($aParams['templates']['main']) ? $aParams['templates']['main'] : 'modules/boonex/wall/|timeline_comment.html';
        $sTmplNameSnippet = isset($aParams['templates']['snippet']) ? $aParams['templates']['snippet'] : 'modules/boonex/wall/|timeline_comment_files.html';
        return array(
            'title' => $sOwner . ' ' . $sTextAddedNew . ' ' . $sTextWallObject,
            'description' => $aComment['cmt_text'],
            'content' => $sCss . $this->oModule->_oTemplate->parseHtmlByName($sTmplName, array(
        		'mod_prefix' => 'bx_' . $sUri,
        		'cpt_user_name' => $sOwner,
        		'cpt_added_new' => $sTextAddedNew,
        		'cpt_object' => $sTextWallObject,
        		'cpt_item_url' => $aItem['url'],
        		'cnt_comment_text' => $aComment['cmt_text'],
        		'snippet' => $this->oModule->_oTemplate->parseHtmlByName($sTmplNameSnippet, array(
        			'mod_prefix' => 'bx_' . $sUri,
		            'cnt_item_page' => $aItem['url'],
		            'cnt_item_icon' => $aItem['file'],
		            'cnt_item_title' => $aItem['title'],
		        	'cnt_item_title_attr' => bx_html_attribute($aItem['title']),
		            'cnt_item_description' => $aItem['description'],
		            'post_id' => $aEvent['id'],
        		))
        	))
        );
    }
}
