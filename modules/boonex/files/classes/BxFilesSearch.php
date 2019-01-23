<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplSearchResult');

class BxFilesSearch extends BxTemplSearchResult
{
    var $oModule;
    var $oTemplate;
    var $bAdminMode = false;
    function __construct ($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '')
    {
        parent::__construct();
        // main settings
        $this->aCurrent = array(
            'name' => 'bx_files',
            'title' => '_bx_files',
            'table' => 'bx_files_main',
            'ownFields' => array('ID', 'Title', 'Uri', 'Desc', 'Date', 'Size', 'Ext', 'Views', 'Rate', 'RateCount', 'Type'),
            'searchFields' => array('Title', 'Tags', 'Desc', 'Categories'),
            'join' => array(
                'profile' => array(
                    'type' => 'left',
                    'table' => 'Profiles',
                    'mainField' => 'Owner',
                    'onField' => 'ID',
                    'joinFields' => array('NickName')
                ),
                'icon' => array(
                    'type' => 'left',
                    'table' => 'bx_files_types',
                    'mainField' => 'Type',
                    'onField' => 'Type',
                    'joinFields' => array('Icon')
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
                'activeStatus' => array('value'=>'approved', 'field'=>'Status', 'operator'=>'=', 'paramName' => 'status'),
                'owner' => array('value'=>'', 'field'=>'NickName', 'operator'=>'=', 'paramName'=>'ownerName', 'table'=>'Profiles'),
                'ownerStatus' => array('value'=>array('Rejected', 'Suspended'), 'operator'=>'not in', 'paramName'=>'ownerStatus', 'table'=>'Profiles', 'field'=>'Status'),
                'tag' => array('value'=>'', 'field'=>'Tags', 'operator'=>'against', 'paramName'=>'tag'),
                'category' => array('value'=>'', 'field'=>'Categories', 'operator'=>'against', 'paramName'=>'categoryUri'),
                'id' => array('value' => '', 'field' => 'ID', 'operator' => 'in'),
                'allow_view' => array('value'=>'', 'field'=>'AllowAlbumView', 'operator'=>'in', 'table'=> 'sys_albums'),
                'not_allow_view' => array('value'=>'', 'field'=>'AllowAlbumView', 'operator'=>'not in', 'table'=> 'sys_albums'),
                'album_status' => array('value'=>'active', 'field'=>'Status', 'operator'=>'=', 'table'=> 'sys_albums'),
                'albumType' => array('value'=>'', 'field'=>'Type', 'operator'=>'=', 'paramName'=>'albumType', 'table'=>'sys_albums'),
            ),
            'paginate' => array('perPage' => 10, 'page' => 1, 'totalNum' => 10, 'totalPages' => 1),
            'sorting' => 'last',
            'view' => 'full',
            'ident' => 'ID',
            'rss' => array(
                'title' => _t('_bx_files'),
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

        // redeclaration some unique fav fields
        $this->aAddPartsConfig['favorite'] = array(
            'type' => 'inner',
            'table' => 'bx_files_favorites',
            'mainField' => 'ID',
            'onField' => 'ID',
            'userField' => 'Profile',
            'joinFields' => ''
        );

        $this->oModule = BxDolModule::getInstance('BxFilesModule');
        $this->oTemplate = $this->oModule->_oTemplate;
        $this->oModule->_oTemplate->addCss('search.css');
        $this->aConstants['filesUrl'] = $this->oModule->_oConfig->getFilesUrl();
        $this->aConstants['filesDir'] = $this->oModule->_oConfig->getFilesPath();
        $this->aConstants['filesInAlbumCover'] = 32;
        $this->aConstants['picPostfix'] = $this->oModule->_oConfig->aFilePostfix;

        //permalinks generation
        $this->aConstants['linksTempl'] = array(
            'home' => 'home',
            'file' => 'view/{uri}',
            'category' => 'browse/category/{uri}',
            'browseAll' => 'browse/',
            'browseUserAll' => 'albums/browse/owner/{uri}',
            'browseAllTop' => 'browse/top',
            'tag' => 'browse/tag/{uri}',
            'album' => 'browse/album/{uri}',
            'add' => 'browse/my/add'
        );

        $this->aCurrent['restriction']['albumType']['value'] = $this->aCurrent['name'];

        //additional modes for browse
        switch ($sParamName) {
            case 'calendar':
                $this->aCurrent['restriction']['calendar-min'] = array('value' => "UNIX_TIMESTAMP('{$sParamValue}-{$sParamValue1}-{$sParamValue2} 00:00:00')", 'field' => 'Date', 'operator' => '>=', 'no_quote_value' => true);
                $this->aCurrent['restriction']['calendar-max'] = array('value' => "UNIX_TIMESTAMP('{$sParamValue}-{$sParamValue1}-{$sParamValue2} 23:59:59')", 'field' => 'Date', 'operator' => '<=', 'no_quote_value' => true);
                break;
            case 'top':
                $this->aCurrent['sorting'] = 'top';
                break;
            case 'popular':
                $this->aCurrent['sorting'] = 'popular';
                break;
            case 'featured':
                $this->aCurrent['restriction']['featured'] = array(
                    'value'=>'1', 'field'=>'Featured', 'operator'=>'=', 'paramName'=>'bx_files_mode'
                );
                break;
            case 'favorited':
                if (isset($this->aAddPartsConfig['favorite']) && !empty($this->aAddPartsConfig['favorite']) && getLoggedId() != 0) {
                    $this->aCurrent['join']['favorite'] = $this->aAddPartsConfig['favorite'];
                    $this->aCurrent['restriction']['fav'] = array(
                        'value' => getLoggedId(),
                        'field' => $this->aAddPartsConfig['favorite']['userField'],
                        'operator' => '=',
                        'table' => $this->aAddPartsConfig['favorite']['table']
                    );
                }
                break;
            case 'album':
                $this->aCurrent['sorting'] = 'album_order';
                $this->aCurrent['restriction']['album'] = array(
                    'value'=>'', 'field'=>'Uri', 'operator'=>'=', 'paramName'=>'albumUri', 'table'=>'sys_albums'
                );
                if ($sParamValue1 == 'owner' && strlen($sParamValue2) > 0)
                    $this->aCurrent['restriction']['owner']['value'] = $sParamValue2;
                break;
        }
    }

    function _getPseud ()
    {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'date' => 'Date',
            'size' => 'Size',
            'uri' => 'Uri',
            'view' => 'Views',
            'ownerId' => 'Owner',
            'ownerName' => 'NickName',
            'desc' => 'Desc'
        );
    }

    function getAlterOrder()
    {
        $aSql = array();
        switch ($this->aCurrent['sorting']) {
            case 'popular':
                $aSql['order'] = " ORDER BY `DownloadsCount` DESC";
                break;

            case 'album_order':
                $aSql['order'] = " ORDER BY `obj_order` ASC, `id_object` DESC";
                break;
        }

        return $aSql;
    }

    function displaySearchUnit ($aData)
    {
        $bShort = isset($this->aCurrent['view']) && $this->aCurrent['view'] == 'short' ? true : false;
        if ($this->oModule->isAdmin($this->oModule->_iProfileId) || is_array($this->aCurrent['restriction']['allow_view']['value']))
            $bVis = true;
        elseif ($this->oModule->oAlbumPrivacy->check('album_view', $aData['id_album'], $this->oModule->_iProfileId))
            $bVis = true;
        else
            $bVis = false;

        if (!$bVis) {
            $aUnit = array(
               'bx_if:show_title' => array(
                   'condition' => !$bShort,
                   'content' => array(1)
               )
            );
            $sCode = $this->oTemplate->parseHtmlByName('browse_unit_private.html', $aUnit);
        } else
            $sCode = $bShort ? $this->getSearchUnitShort($aData) : $this->getSearchUnit($aData);
        return $sCode;
    }

    function displayResultBlock ()
    {
        $sCode = parent::displayResultBlock();
        return !empty($sCode) ? $this->oTemplate->parseHtmlByName('default_margin.html', array('content' => $sCode)) : $sCode;
    }

    function getSearchUnit ($aData)
    {
        $aUnit['unitClass'] = $this->aCurrent['name'];
        $aUnit['bx_if:admin'] = array(
            'condition' => $this->bAdminMode,
            'content' => array('id' => $aData['id'])
        );
        // pic
        $sPicName = empty($aData['Icon']) ? 'default.png': $aData['Icon'];
        $aUnit['pic'] = $this->oModule->_oTemplate->getIconUrl($sPicName);
        $aUnit['spacer'] = $this->oModule->_oTemplate->getIconUrl('spacer.gif');
        // rate
        if (!is_null($this->oRate) && $this->oRate->isEnabled())
            $aUnit['rate'] = $this->oRate->getJustVotingElement(0, 0, $aData['Rate']);

        // title
        $aUnit['titleLink'] = $this->getCurrentUrl('file', $aData['id'], $aData['uri']);
        $aUnit['title'] = stripslashes($aData['title']);

        // from
        $aUnit['fromLink'] = getProfileLink($aData['ownerId']);
        $aUnit['from'] = getNickName($aData['ownerId']);

        //extension
        $aUnit['ext'] = $aData['Ext'];

        //size
        $aUnit['size'] = _t_format_size($aData['size']);

        // when
        $aUnit['when'] = defineTimeInterval($aData['date']);
        // view
        $aUnit['view'] = $aData['view'];
        // desc
        $aUnit['desc'] = stripslashes($aData['desc']);

        $aUnit['id'] = $aData['id'];
        return $this->oModule->_oTemplate->parseHtmlByName('browse_unit.html', $aUnit, array('{','}'));
    }

    function getSearchUnitShort ($aData)
    {
        //var_dump($aData); exit;
        $aUnit = array();
        $aUnit['unitClass'] = $this->aCurrent['name'];

        // title
        $aUnit['titleLink'] = $this->getCurrentUrl('file', $aData['id'], $aData['uri']);
        $aUnit['title'] = $aData['title'];

        // from
        $aUnit['fromLink'] = getProfileLink($aData['ownerId']);
        $aUnit['from'] = getNickName($aData['ownerId']);

        //extension
        $aUnit['ext'] = $aData['Ext'];

        //size
        $aUnit['size'] = _t_format_size($aData['size']);

        // when
        $aUnit['when'] = defineTimeInterval($aData['date']);

        // pic
        $sPicName = is_null($aData['Icon']) ? 'default.png': $aData['Icon'];
        $aUnit['pic'] = $this->oModule->_oTemplate->getIconUrl($sPicName);

        $aUnit['id'] = $aData['id'];
        return $this->oModule->_oTemplate->parseHtmlByName('browse_unit_short.html', $aUnit, array('{','}'));
    }

    function setSorting ()
    {
        $this->aCurrent['sorting'] = isset($_GET[$this->aCurrent['name'].'_mode']) ? $_GET[$this->aCurrent['name'].'_mode'] : $this->aCurrent['sorting'];
    }

    function getTopMenu($aExclude = array())
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

            if(basename( $_SERVER['PHP_SELF'] ) == 'rewrite_name.php' || basename( $_SERVER['PHP_SELF'] ) == 'profile.php')
                $sLink = BX_DOL_URL_ROOT . "profile.php?ID={$this->aCurrent['restriction']['owner']['value']}&";
            else
                $sLink = bx_html_attribute($_SERVER['PHP_SELF']) . "?";
            $sLink .= $this->aCurrent['name'] . "_mode=$sMyMode" . $aLinkAddon['params'] . $aLinkAddon['paginate'] . $aLinkAddon['type'];

              $aDBTopMenu[$sModeTitle] = array('href' => $sLink, 'dynamic' => true, 'active' => ($sMyMode == $this->aCurrent['sorting']));
        }
        return $aDBTopMenu;
    }

    function getCurrentUrl ($sType, $iId, $sUri, $aOwner = '')
    {
        $sLink = $this->aConstants['linksTempl'][$sType];
        return BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . str_replace('{uri}', $sUri, $sLink);
    }

    function getAlbumList ($iPage = 1, $iPerPage = 10, $aCond = array())
    {
        $oSet = new BxDolAlbums($this->aCurrent['name']);
        foreach ($this->aCurrent['restriction'] as $sKey => $aParam)
            $aData[$sKey] = $aParam['value'];
        $aData = array_merge($aData, $aCond);
        $iAlbumCount = $oSet->getAlbumCount($aData);
        if ($iAlbumCount > 0) {
            $this->aCurrent['paginate']['totalAlbumNum'] = $iAlbumCount;
            $sCode = $this->addCustomParts();
            $aList = $oSet->getAlbumList($aData, (int)$iPage, (int)$iPerPage);
            foreach ($aList as $iKey => $aData)
                $sCode .= $this->displayAlbumUnit($aData);
        } else
            $sCode = MsgBox(_t('_Empty'));
        return $sCode;
    }

    function displayAlbumUnit ($aData, $bCheckPrivacy = true)
    {
        if (!$this->bAdminMode && $bCheckPrivacy) {
            if (!$this->oModule->oAlbumPrivacy->check('album_view', $aData['ID'], $this->oModule->_iProfileId)) {
                $aUnit = array(
                   'img_url' => $this->oTemplate->getIconUrl('lock.png'),
                );
                return $this->oTemplate->parseHtmlByName('album_unit_private.html', $aUnit);
            }
        }
        $aUnit['type'] = $this->oModule->_oConfig->getMainPrefix();
        $aUnit['bx_if:editMode'] = array(
            'condition' => $this->bAdminMode,
            'content' => array(
                'id' => $aData['ID'],
                'checked' => $this->sCurrentAlbum == $aData['Uri'] ? 'checked="checked"' : ''
            )
        );

        // from
        $aUnit['fromLink'] = getProfileLink($aData['Owner']);
        $aUnit['from'] = getNickName($aData['Owner']);

        $aUnit['albumUrl'] = $this->getCurrentUrl('album', $aData['ID'], $aData['Uri']) . '/owner/' . getUsername($aData['Owner']);

        // pic
        $aUnit['spacer'] = $this->oTemplate->getIconUrl('spacer.gif');

        // cover
        $iItems = isset($this->aConstants['filesInAlbumCover']) ? (int)$this->aConstants['filesInAlbumCover'] : 15;
        $aItems = $this->getAlbumCovers($aData['ID'], array('filesInAlbumCover' => $iItems));
        if((!is_array($aItems) || count($aItems) == 0) && $this->oModule->_iProfileId != $aData['Owner']) {
            $this->aCurrent['paginate']['totalAlbumNum']--;
            return '';
        }

        $aUnits = array();
        for($i = 0; $i < $iItems; $i++) {
            $aItem = array_shift($aItems);
            $bItem = isset($aItem['id_object']) && (int)$aItem['id_object'] > 0;

            $aUnits[] = array(
                'bx_if:exist' => array(
                    'condition' => $bItem,
                    'content' => array(
                        'unit' => $bItem ? $this->getAlbumCoverUrl($aItem) : '',
                    )
                ),
                'bx_if:not-exist' => array(
                    'condition' => !$bItem,
                    'content' => array()
                )
            );
        }
        $aUnit['bx_repeat:units'] = $aUnits;

        // title
        $aUnit['titleLink'] = $aUnit['albumUrl'];
        $aUnit['title'] = $aData['Caption'];

        // when
        $aUnit['when'] = defineTimeInterval($aData['Date']);

        // view
        $aUnit['view'] = isset($aData['ObjCount']) ? $aData['ObjCount'] . ' ' . _t($this->aCurrent['title']): '';
        return $this->oTemplate->parseHtmlByName('album_unit.html', $aUnit, array('{','}'));
    }

    function getAlbumCovers ($iAlbumId, $aParams = array())
    {
        $iAlbumId = (int)$iAlbumId;
        $iLimit = isset($aParams['filesInAlbumCover']) ? (int)$aParams['filesInAlbumCover'] : null;
        return $this->oModule->oAlbums->getAlbumCoverFiles($iAlbumId, array('table' => $this->aCurrent['table'], 'field' => 'ID', 'fields_list' => array('Type')), array(array('field'=>'Status', 'value'=>'approved')), $iLimit);
    }

    function getAlbumCoverUrl (&$aIdent)
    {
        $sIcon = $this->oModule->_oConfig->getMimeTypeIcon($aIdent['Type']);
        return $this->oTemplate->getIconUrl($sIcon);
    }

    function getImgUrl ($iId, $sImgType = 'browse')
    {
        $iId = (int)$iId;
        $sPostFix = isset($this->aConstants['picPostfix'][$sImgType]) ? $this->aConstants['picPostfix'][$sImgType] : $this->aConstants['picPostfix']['browse'];
        return $this->aConstants['filesUrl'] . $iId . $sPostFix;
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
            'simple_paginate_url' => BX_DOL_URL_ROOT . $this->oModule->_oConfig->getUri() . '/albums/browse'
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
                $aCode['menu_bottom'] = $aCustom['simple_paginate'] ? $oPaginate->getSimplePaginate($aCustom['simple_paginate_url']) : $oPaginate->getPaginate();
                $aCode['code'] = DesignBoxContent($aCustom['caption'], $sCode);
            } else
                $aCode['menu_bottom'] = $aCustom['menu_bottom'];
        }
        $aCode['menu_top'] = $aCustom['menu_top'];
        return array($aCode['code'], $aCode['menu_top'], $aCode['menu_bottom'], (!empty($aCode['code']) ? false : ''));
    }

    //services
    function serviceGetFilesInCat ($iId, $sCategory = '')
    {
        $this->aCurrent['paginate']['perPage'] = 1000;
        $this->aCurrent['join']['category'] = array(
            'type' => 'left',
            'table' => 'sys_categories',
            'mainField' => 'ID',
            'onField' => 'ID',
            'joinFields' => array('Category')
        );

        $this->aCurrent['restriction']['ownerId'] = array(
            'value' => $iId ? $iId : '',
            'field' => 'Owner',
            'operator' => '=',
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
        foreach ($aFiles as $k => $aRow) {
            $sIcon = !empty($aRow['Icon']) ? $aRow['Icon'] : 'default.png';
            $aFiles[$k]['icon'] = $this->oModule->_oTemplate->getIconUrl($sIcon);
            $aFiles[$k]['url'] = $this->getCurrentUrl('file', $aRow['ID'], $aRow['uri']);
        }
        return $aFiles;
    }

    function serviceGetAlbumPrivacy ($iAlbumId, $iViewer = 0)
    {
        if (!$iViewer)
            $iViewer = $this->oModule->_iProfileId;
        return $this->oModule->oAlbumPrivacy->check('album_view', (int)$iAlbumId, $iViewer);
    }

    function serviceGetFilesInAlbum ($iAlbumId, $isCheckPrivacy = false, $iViewer = 0)
    {
        $iAlbumId = (int)$iAlbumId;
        if (!$iAlbumId)
            return array();
        if (!$iViewer)
            $iViewer = $this->oModule->_iProfileId;
        if ($isCheckPrivacy && !$this->oModule->oAlbumPrivacy->check('album_view', (int)$iAlbumId, $iViewer))
            return array();

        $this->aCurrent['paginate']['perPage'] = 1000;
        $this->aCurrent['join']['albumsObjects'] = array(
            'type' => 'left',
            'table' => 'sys_albums_objects',
            'mainField' => 'ID',
            'onField' => 'id_object',
            'joinFields' => array('obj_order')
        );
        $this->aCurrent['sorting'] = 'album_order';
        $this->aCurrent['restriction']['album'] = array(
            'value'=>$iAlbumId, 'field'=>'id_album', 'operator'=>'=', 'paramName'=>'albumId', 'table'=>'sys_albums_objects'
        );
        $aFiles = $this->getSearchData();
        if (!$aFiles)
            $aFiles = array();
        foreach ($aFiles as $k => $aRow) {
            $sIcon = !empty($aRow['Icon']) ? $aRow['Icon'] : 'default.png';
            $aFiles[$k]['icon'] = $this->oModule->_oTemplate->getIconUrl($sIcon);
            $aFiles[$k]['url'] = $this->getCurrentUrl('file', $aRow['ID'], $aRow['uri']);
            $aFiles[$k]['mime_type'] = $aRow['Type'];
            $aFiles[$k]['path'] = $this->aConstants['filesDir'] . $aRow['ID'] . '_' . sha1($aRow['Date']);
        }
        return $aFiles;
    }

    function serviceGetEntry($iId, $sType = 'thumb')
    {
        return $this->serviceGetFileArray($iId);
    }

    function serviceGetItemArray($iId, $sType = 'browse')
    {
        return $this->serviceGetFileArray($iId);
    }

    function serviceGetFileArray ($iId)
    {
        $iId = (int)$iId;
        $sqlQuery = "SELECT a.`ID` as `id`,
                            a.`Title` as `title`,
                            a.`Desc` as `desc`,
                            a.`Uri` as `uri`,
                            a.`Owner` as `owner`,
                            a.`Date` as `date`,
                            a.`Ext`,
                            a.`Type`,
                            a.`Rate`,
                            a.`Status` as `status`,
                            b.`id_album`,
                            d.`Icon`
                        FROM `{$this->aCurrent['table']}` as a
                        LEFT JOIN `sys_albums_objects` as b ON b.`id_object` = a.`ID`
                        LEFT JOIN `sys_albums` as c ON c.`ID` = b.`id_album`
                        LEFT JOIN `bx_files_types` as d ON d.`Type` = a.`Type`
                        WHERE a.`ID`='$iId' AND c.`Type`='{$this->aCurrent['name']}'";
        $aData = db_arr($sqlQuery);
        if (!$aData)
            return array();

        $iSize = (int)$this->oModule->_oConfig->getGlParam('browse_width');
        $sIcon = !empty($aData['Icon']) ? $aData['Icon'] : 'default.png';
        $sFile = $aData['id'];
        if (strlen($aData['Ext']) > 0)
            $sFile .= '_' . sha1($aData['date']);
        $aInfo = array(
            'file' => $this->oModule->_oTemplate->getIconUrl($sIcon),
            'file_path' => $this->oModule->_oTemplate->getIconPath($sIcon),
            'width' => $iSize + 4,
            'height' => $iSize + 4,
            'title' => $aData['title'],
            'owner' => $aData['owner'],
            'description' => $aData['desc'],
            'url' => $this->getCurrentUrl('file', $iId, $aData['uri']),
            'date' => $aData['date'],
            'rate' => $aData['Rate'],
            'path' => $this->aConstants['filesDir'] . $sFile,
            'extension' => $aData['Ext'],
            'mime_type' => $aData['Type'],
            'status' => $aData['status'],
            'album_id' => $aData['id_album']
        );
        return empty($aInfo['file']) ? array() : $aInfo;
    }

    function serviceGetFilesBlock ($aParams = array(), $aCustom = array(), $sLink = '')
    {
        $aCode = $this->getBrowseBlock($aParams, $aCustom, $sLink, false);
        if ($this->aCurrent['paginate']['totalNum'] > 0)
            return array($aCode['code'], $aCode['menu_top'], $aCode['menu_bottom'], $aCode['wrapper']);
    }

    function serviceGetFilePath ($iFile)
    {
        $iFile = (int)$iFile;
        $aInfo = $this->oModule->_oDb->getFileInfo(array('fileId'=>$iFile), true, array('medID', 'medExt'));
        return $this->aConstants['filesDir'] . $aInfo['medID'] . '.' . $aInfo['medExt'];
    }

    function serviceGetProfileAlbumsBlock ($iProfileId, $sSpecUrl = '')
    {
        $iProfileId   = (int)$iProfileId;
        $sNickName    = getUsername($iProfileId);
        $sSimpleUrl   = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'albums/browse/owner/' . $sNickName;
        $sPaginateUrl = mb_strlen($sSpecUrl) > 0 ? strip_tags($sSpecUrl) : getProfileLink($iProfileId);
        return $this->getAlbumsBlock(array('owner' => $iProfileId), array('hide_default' => TRUE), array('enable_center' => false, 'paginate_url' => $sPaginateUrl, 'simple_paginate_url' => $sSimpleUrl));
    }

	/**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPost($aEvent)
    {
        return $this->oModule->serviceGetWallPost($aEvent);
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostOutline($aEvent)
    {
        return $this->oModule->serviceGetWallPostOutline($aEvent);
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostComment($aEvent)
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aItem = $this->serviceGetFileArray($iId);
        if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || !isset($aContent['comment_id']))
            return '';

        bx_import('BxTemplCmtsView');
        $oCmts = new BxTemplCmtsView($this->oModule->_oConfig->getMainPrefix(), $iId);
        if(!$oCmts->isEnabled())
            return '';

        $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);

        $sCss = '';
        if($aEvent['js_mode'])
            $sCss = $this->oModule->_oTemplate->addCss('wall_post.css', true);
        else
            $this->oModule->_oTemplate->addCss('wall_post.css');

        $sTextWallObject = _t('_bx_files_wall_object');
        return array(
            'title' => _t('_bx_files_wall_added_new_comment_title', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content' => $sCss . $this->oModule->_oTemplate->parseHtmlByName('modules/boonex/wall/|timeline_comment.html', array(
        		'mod_prefix' => 'bx_files',
				'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t('_bx_files_wall_added_new_comment'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $aItem['url'],
	            'cnt_comment_text' => $aComment['cmt_text'],
	        	'snippet' => $this->oModule->_oTemplate->parseHtmlByName('wall_post_comment.html', array(
        			'cnt_item_page' => $aItem['url'],
		        	'cnt_item_width' => $aItem['width'],
					'cnt_item_height' => $aItem['height'],
		            'cnt_item_icon' => $aItem['file'],
		            'cnt_item_title' => $aItem['title'],
		            'cnt_item_description' => $aItem['description'],
	        	))
        	))
        );
    }

    function getRssUnitLink (&$a)
    {
        return BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'view/' . $a['uri'];
    }
}
