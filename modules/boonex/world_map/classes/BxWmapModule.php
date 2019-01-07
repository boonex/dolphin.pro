<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxDolPaginate');

define('BX_WMAP_ZOOM_DEFAULT_ENTRY', 10);
define('BX_WMAP_ZOOM_DEFAULT_EDIT', 5);
define('BX_WMAP_PRIVACY_DEFAULT', 3);

class BxWmapModule extends BxDolModule
{
    var $_iProfileId;
    var $_aParts;
    var $_sProto = 'http';
    var $aIconsSizes = array(
        'group.png' => array('w' => 24, 'h' => 24, 'url' => ''),
        'default'   => array('w' => 24, 'h' => 24, 'url' => ''),
    );

    function __construct(&$aModule)
    {
        parent::__construct($aModule);
        $this->_iProfileId   = getLoggedId();
        $this->_aParts       = $this->_oDb->getParts();
        $this->_oDb->_aParts = &$this->_aParts;
        $this->_sProto       = bx_proto();
    }

    function actionHome()
    {
        $this->_oTemplate->pageStart();

        bx_import('PageMain', $this->_aModule);
        $oPage = new BxWmapPageMain ($this);
        echo $oPage->getCode();

        $this->_oTemplate->addJs($this->_sProto . '://www.google.com/jsapi?key=' . getParam('bx_wmap_key'));
        $this->_oTemplate->addJs('BxWmap.js');
        $this->_oTemplate->addCss('main.css');
        $this->_oTemplate->pageCode(_t('_bx_wmap_block_title_block_map'), false, false);
    }

    function actionEdit($iEntryId, $sPart)
    {
        if (!isset($this->_aParts[$sPart])) {
            $this->_oTemplate->displayPageNotFound();

            return;
        }

        $iEntryId  = (int)$iEntryId;
        $aLocation = $this->_iProfileId ? $this->_oDb->getDirectLocation($iEntryId, $this->_aParts[$sPart]) : false;

        if (!$aLocation || !$this->isAllowedEditOwnLocation($aLocation)) {
            $this->_oTemplate->displayAccessDenied();

            return;
        }

        if ('profiles' == $sPart) {
            $aLocation['title'] = getNickName($aLocation['id']);
        }

        $this->_oTemplate->pageStart();

        bx_import('PageEdit', $this->_aModule);
        $oPage = new BxWmapPageEdit ($this, $aLocation);
        echo $oPage->getCode();

        $this->_oTemplate->addJs($this->_sProto . '://www.google.com/jsapi?key=' . getParam('bx_wmap_key'));
        $this->_oTemplate->addJs('BxWmap.js');
        $this->_oTemplate->addCss('main.css');
        $this->_oTemplate->pageCode(sprintf(_t('_bx_wmap_edit'), $aLocation['title'],
            _t($this->_aParts[$sPart]['title_singular'])), false, false);
    }

    function actionSaveData(
        $iEntryId,
        $sPart,
        $iZoom,
        $sMapType,
        $fLat,
        $fLng,
        $sMapClassInstanceName,
        $sAddress,
        $sCountry
    ) {
        $iRet = $this->_saveData($iEntryId, $sPart, $iZoom, $sMapType, $fLat, $fLng, $sMapClassInstanceName, $sAddress,
            $sCountry);

        switch ((int)$iRet) {
            case 404:
                echo 404;
                break;
            case 403:
                $this->_oTemplate->displayAccessDenied();
                break;
            case 1:
                echo 1;
                break;
        }
    }

    function actionSaveLocationPartHome($sPart, $iZoom, $sMapType, $fLat, $fLng)
    {
        $this->_saveLocationByPrefix('bx_wmap_home_' . $sPart, $iZoom, $sMapType, $fLat, $fLng);
    }

    function actionSaveLocationHomepage($iZoom, $sMapType, $fLat, $fLng)
    {
        $this->_saveLocationByPrefix('bx_wmap_homepage', $iZoom, $sMapType, $fLat, $fLng);
    }

    function actionSaveLocationSeparatePage($iZoom, $sMapType, $fLat, $fLng)
    {
        $this->_saveLocationByPrefix('bx_wmap_separate', $iZoom, $sMapType, $fLat, $fLng);
    }

    function actionGetDataLocation($iId, $sPart, $sMapClassInstanceName)
    {
        if (!isset($this->_aParts[$sPart])) {
            return;
        }

        $iEntryId = (int)$iId;
        $r        = $this->_oDb->getDirectLocation($iEntryId, $this->_aParts[$sPart]);
        if (!$r || empty($r['lat'])) {
            return;
        }

        if ('profiles' == $sPart) {
            $r['title'] = getNickName($r['id']);
        }

        $oPermalinks = new BxDolPermalinks();
        $sIcon       = $this->_aParts[$r['part']]['icon_site'];
        $sIcon       = $GLOBALS['oFunctions']->sysImage(false === strpos($sIcon,
            '.') ? $sIcon : $this->_oTemplate->getIconUrl($sIcon));
        $aVars       = array(
            'icon'  => $sIcon,
            'title' => $r['title'],
            'link'  => BX_DOL_URL_ROOT . $oPermalinks->permalink($this->_aParts[$r['part']]['permalink'] . $r['uri'])
        );
        $sHtml       = $this->_oTemplate->parseHtmlByName('popup_location', $aVars);

        $aIconJSON = $this->_getIconArray($sMapClassInstanceName == 'glBxWmapEdit' ? '' : $this->_aParts[$r['part']]['icon']);

        $aRet   = array();
        $aRet[] = array(
            'lat'  => $r['lat'],
            'lng'  => $r['lng'],
            'data' => $sHtml,
            'icon' => $aIconJSON,
        );

        echo json_encode($aRet);
    }

    function actionGetData(
        $iZoom,
        $fLatMin,
        $fLatMax,
        $fLngMin,
        $fLngMax,
        $sMapClassInstanceName,
        $sCustomParts = '',
        $sCustom = ''
    ) {
        $fLatMin = (float)$fLatMin;
        $fLatMax = (float)$fLatMax;
        $fLngMin = (float)$fLngMin;
        $fLngMax = (float)$fLngMax;
        $iZoom   = (int)$iZoom;

        echo $this->_getLocationsData($fLatMin, $fLatMax, $fLngMin, $fLngMax, $sCustomParts, $sCustom);
    }

    function _getLocationsData($fLatMin, $fLatMax, $fLngMin, $fLngMax, $sCustomParts = '', $sCustom = '')
    {
        bx_import('BxDolPrivacy');

        $oPermalinks = new BxDolPermalinks();

        $aCustomParts = $this->_validateParts($sCustomParts);
        $a            = $this->_oDb->getLocationsByBounds('', (float)$fLatMin, (float)$fLatMax, (float)$fLngMin,
            (float)$fLngMax, $aCustomParts, getLoggedId() ? array(BX_DOL_PG_ALL, BX_DOL_PG_MEMBERS) : BX_DOL_PG_ALL);

        $aa = array();
        foreach ($a as $r) {
            if (!$this->_oDb->getDirectLocation($r['id'], $this->_aParts[$r['part']], true))
                continue;

            if ('profiles' == $r['part']) {
                $r['title'] = getNickName($r['id']);
            }

            $sKey = $r['lat'] . 'x' . $r['lng'];

            $sIcon = $this->_aParts[$r['part']]['icon_site'];
            $sIcon = $GLOBALS['oFunctions']->sysImage(false === strpos($sIcon,
                '.') ? $sIcon : $this->_oTemplate->getIconUrl($sIcon));

            $aVars = array(
                'icon'  => $sIcon,
                'title' => $r['title'],
                'link'  => BX_DOL_URL_ROOT . $oPermalinks->permalink($this->_aParts[$r['part']]['permalink'] . $r['uri'])
            );

            $aa[$sKey][] = array(
                'lat'   => $r['lat'],
                'lng'   => $r['lng'],
                'title' => $r['title'],
                'icon'  => $this->_aParts[$r['part']]['icon'],
                'html'  => $this->_oTemplate->parseHtmlByName('popup_location', $aVars),
            );
        }

        $aRet = array();
        foreach ($aa as $k => $a) {
            $sHtml   = '';
            $aTitles = array();
            $sIcon   = '';
            foreach ($a as $r) {
                $sHtml .= $r['html'];
                $aTitles[] = $r['title'];
                $sIcon     = $r['icon'];
            }
            $aVars  = array('content' => $sHtml);
            $aRet[] = array(
                'lat'    => $r['lat'],
                'lng'    => $r['lng'],
                'titles' => $aTitles,
                'data'   => $this->_oTemplate->parseHtmlByName('popup_locations', $aVars),
                'icon'   => $this->_getIconArray((count($a) > 1 ? 'group.png' : $sIcon)),
            );
        }

        return json_encode($aRet);
    }

    // ================================== admin actions

    function actionUpdateLocations($iLimit = 4, $iDelay = 6)
    {
        if (!$this->isAdmin()) {
            $this->_oTemplate->displayAccessDenied();

            return;
        }

        $iLimit = (int)$iLimit;
        $iDelay = (int)$iDelay;

        $a = $this->_oDb->getUndefinedLocations($iLimit);
        if ($a) {
            foreach ($a as $r) {
                $this->_updateLocation($iDelay, $r);
            }

            $aVars = array(
                'refresh' => 1,
                'msg'     => 'Entries update is in progress, please wait...',
            );
            echo $this->_oTemplate->parseHtmlByName('updating', $aVars);
        } else {
            $this->_oTemplate->displayMsg('Entries locations update has been completed');
        }
    }

    function actionAdministrationParts($sPart)
    {
        if (!$this->isAdmin()) {
            $this->_oTemplate->displayAccessDenied();

            return;
        }

        if (!isset($this->_aParts[$sPart])) {
            $this->_oTemplate->displayPageNotFound();

            return;
        }

        $this->_oTemplate->pageStart();

        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if (isset($_POST['save']) && isset($_POST['cat']) && (int)$_POST['cat']) {
            $oSettings   = new BxDolAdminSettings((int)$_POST['cat']);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        foreach ($_POST as $k => $v) {
            unset ($_POST[$k]);
        }

        $aCats = array(
            array(
                'cat'   => 'World Map Home: ' . ucfirst($sPart),
                'title' => _t('_bx_wmap_admin_settings_part_home', _t($this->_aParts[$sPart]['title'])),
                'extra' => 'return $this->_saveLocationForm ("PartHome", $this->serviceHomepagePartBlock ("' . $sPart . '"));',
            ),
            array(
                'cat'   => 'World Map Entry: ' . ucfirst($sPart),
                'title' => _t('_bx_wmap_admin_settings_part_entry', _t($this->_aParts[$sPart]['title'])),
                'extra' => '',
            ),
            array(
                'cat'   => 'World Map Edit Location: ' . ucfirst($sPart),
                'title' => _t('_bx_wmap_admin_settings_edit_location', _t($this->_aParts[$sPart]['title'])),
                'extra' => '',
            ),
        );

        foreach ($aCats as $a) {
            $iId     = $this->_oDb->getSettingsCategory($a['cat']);
            $sResult = '';
            if ($iId) {
                $oSettings = new BxDolAdminSettings($iId);
                $sResult   = $oSettings->getForm();
                if ($mixedResult !== true && !empty($mixedResult) && $_POST['cat'] == $iId) {
                    $sResult = $mixedResult . $sResult;
                }
            }
            $sExtra = '';
            if ($a['extra']) {
                $aVars  = array('content' => eval($a['extra']));
                $sExtra = $this->_oTemplate->parseHtmlByName('extra_wrapper', $aVars);
            }
            $aVars = array('content' => $sResult . $sExtra);
            echo $this->_oTemplate->adminBlock($this->_oTemplate->parseHtmlByName('default_padding', $aVars),
                $a['title']);
        }

        $this->_oTemplate->addJsAdmin($this->_sProto . '://www.google.com/jsapi?key=' . getParam('bx_wmap_key'));
        $this->_oTemplate->addJsAdmin('modules/boonex/world_map/js/|BxWmap.js');
        $this->_oTemplate->addCssAdmin('main.css');
        $this->_oTemplate->addCssAdmin('forms_adv.css');
        $this->_oTemplate->pageCodeAdmin(_t('_bx_wmap_administration') . ' ' . _t($this->_aParts[$sPart]['title']));
    }

    function actionAdministration()
    {
        if (!$this->isAdmin()) {
            $this->_oTemplate->displayAccessDenied();

            return;
        }

        $this->_oTemplate->pageStart();

        $aPartsForVars = array();
        foreach ($this->_aParts as $k => $r) {
            $aPartsForVars[] = array(
                'part'      => $k,
                'title'     => _t($r['title']),
                'icon'      => $GLOBALS['oFunctions']->sysImage(false === strpos($r['icon_site'],
                    '.') ? $r['icon_site'] : $this->_oTemplate->getIconUrl($r['icon_site'])),
                'link_base' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration_parts/',
            );
        }

        $aVars    = array(
            'module_url'      => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(),
            'bx_repeat:parts' => $aPartsForVars,
        );
        $sContent = $this->_oTemplate->parseHtmlByName('admin_links', $aVars);
        echo $this->_oTemplate->adminBlock($sContent, _t('_bx_wmap_admin_links'), false, false, 11);

        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if (isset($_POST['save']) && isset($_POST['cat']) && (int)$_POST['cat']) {
            $oSettings   = new BxDolAdminSettings((int)$_POST['cat']);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        foreach ($_POST as $k => $v) {
            unset ($_POST[$k]);
        }

        $aCats = array(
            array(
                'cat'   => 'World Map General',
                'title' => _t('_bx_wmap_admin_settings_general'),
                'extra' => '',
            ),
            array(
                'cat'   => 'World Map Homepage',
                'title' => _t('_bx_wmap_admin_settings_homepage'),
                'extra' => 'return $this->_saveLocationForm ("Home", $this->serviceHomepageBlock ());',
            ),
            array(
                'cat'   => 'World Map Separate',
                'title' => _t('_bx_wmap_admin_settings_separate'),
                'extra' => 'return $this->_saveLocationForm ("Page", $this->serviceSeparatePageBlock ());',
            ),
        );

        foreach ($aCats as $a) {
            $iId     = $this->_oDb->getSettingsCategory($a['cat']);
            $sResult = '';
            if ($iId) {
                $oSettings = new BxDolAdminSettings($iId);
                $sResult   = $oSettings->getForm();
                if ($mixedResult !== true && !empty($mixedResult) && $_POST['cat'] == $iId) {
                    $sResult = $mixedResult . $sResult;
                }
            }
            $sExtra = '';
            if ($a['extra']) {
                $aVars  = array('content' => eval($a['extra']));
                $sExtra = $this->_oTemplate->parseHtmlByName('extra_wrapper', $aVars);
            }
            $aVars = array('content' => $sResult . $sExtra);
            echo $this->_oTemplate->adminBlock($this->_oTemplate->parseHtmlByName('default_padding', $aVars),
                $a['title']);
        }

        $this->_oTemplate->addJsAdmin($this->_sProto . '://www.google.com/jsapi?key=' . getParam('bx_wmap_key'));
        $this->_oTemplate->addJsAdmin(BX_DOL_URL_MODULES . $this->_aModule['path'] . 'js/BxWmap.js');
        $this->_oTemplate->addCssAdmin('main.css');
        $this->_oTemplate->addCssAdmin('forms_adv.css');
        $this->_oTemplate->pageCodeAdmin(_t('_bx_wmap_administration'));
    }

    // ================================== service actions

    /**
     * Get location array
     *
     * @param $sPart    module/part name
     * @param $iEntryId entry's id which location is edited
     * @param $iViewer  viewer profile id
     * @return false - location undefined, -1 - access denied, array - all good
     */
    function serviceGetLocation($sPart, $iEntryId, $iViewer = false)
    {
        if (false === $iViewer) {
            $iViewer = getLoggedId();
        }

        if ('profiles' == $sPart) {
            if (!bx_check_profile_visibility($iEntryId, $iViewer, true)) {
                return -1;
            }
        } else {
            bx_import('BxDolPrivacy');
            $oPrivacy = new BxDolPrivacy($this->_aParts[$sPart]['join_table'], $this->_aParts[$sPart]['join_field_id'],
                $this->_aParts[$sPart]['join_field_author']);
            if (!$oPrivacy->check('view', $iEntryId, $iViewer)) {
                return -1;
            }
        }

        $aLocation = $this->_oDb->getDirectLocation($iEntryId, $this->_aParts[$sPart]);
        if (!$aLocation || (!$aLocation['lat'] && $aLocation['lng'])) {
            return false;
        }

        if (false === $aLocation['zoom'] || -1 == $aLocation['zoom']) {
            $aLocation['zoom'] = getParam("bx_wmap_edit_{$sPart}_zoom");
        }

        if (!$aLocation['type']) {
            $aLocation['type'] = getParam("bx_wmap_edit_{$sPart}_map_type");
        }

        return $aLocation;
    }

    /**
     * Update location
     *
     * @param $sPart    module/part name
     * @param $iEntryId entry's id which location is edited
     * @param $fLat     latitude
     * @param $fLng     longitude
     * @param $iZoom    zoom level
     * @param $sMapType map type: normal, satellite, hybrid, terrain
     * @param $sCountry
     * @param $sState
     * @param $sCity
     * @param $sAddress
     * @return false - location undefined, -1 - access denied, array - all good
     */
    function serviceUpdateLocationManually(
        $sPart,
        $iEntryId,
        $fLat,
        $fLng,
        $iZoom,
        $sMapType,
        $sCountry = '',
        $sState = '',
        $sCity = '',
        $sAddress = ''
    ) {
        $a = array('fLat', 'fLng', 'iZoom', 'sMapType', 'sCountry', 'sState', 'sCity', 'sAddress');
        foreach ($a as $sVar) {
            if ('' === $$sVar || false === $$sVar) {
                $$sVar = 'null';
            }
        }

        return $this->_saveData($iEntryId, $sPart, $iZoom, $sMapType, $fLat, $fLng, '', $sAddress, $sCountry);
    }

    /**
     * Edit location block
     *
     * @param $sPart    module/part name
     * @param $iEntryId entry's id which location is edited
     * @return html with clickable map
     */
    function serviceEditLocation($sPart, $iEntryId)
    {
        if (!isset($this->_aParts[$sPart])) {
            return false;
        }

        $iEntryId  = (int)$iEntryId;
        $aLocation = $this->_oDb->getDirectLocation($iEntryId, $this->_aParts[$sPart]);
        if ('profiles' == $sPart) {
            $aLocation['title'] = getNickName($aLocation['id']);
        }

        if (!$aLocation) {
            return false;
        }

        $fLat     = false;
        $fLng     = false;
        $iZoom    = false;
        $sMapType = false;

        if ($aLocation && !empty($aLocation['lat'])) {
            $fLat     = $aLocation['lat'];
            $fLng     = $aLocation['lng'];
            $iZoom    = $aLocation['zoom'];
            $sMapType = $aLocation['type'];
        }

        if (false === $fLat || false === $fLng) {
            $aLocationCountry = $this->_geocode($aLocation['country'], $aLocation['country']);
            $fLat             = isset($aLocationCountry[0]) ? $aLocationCountry[0] : 0;
            $fLng             = isset($aLocationCountry[1]) ? $aLocationCountry[1] : 0;
            $iZoom            = BX_WMAP_ZOOM_DEFAULT_EDIT;
        }

        if (false === $iZoom || -1 == $iZoom) {
            $iZoom = getParam("bx_wmap_edit_{$sPart}_zoom");
        }

        if (!$sMapType) {
            $sMapType = getParam("bx_wmap_edit_{$sPart}_map_type");
        }

        $aVars = array(
            'msg_incorrect_google_key' => trim(_t('_bx_wmap_msg_incorrect_google_key')),
            'loading'                  => _t('_loading ...'),
            'map_control'              => getParam("bx_wmap_edit_{$sPart}_control_type"),
            'map_is_type_control'      => getParam("bx_wmap_edit_{$sPart}_is_type_control") == 'on' ? 1 : 0,
            'map_is_scale_control'     => getParam("bx_wmap_edit_{$sPart}_is_scale_control") == 'on' ? 1 : 0,
            'map_is_overview_control'  => getParam("bx_wmap_edit_{$sPart}_is_overview_control") == 'on' ? 1 : 0,
            'map_is_dragable'          => getParam("bx_wmap_edit_{$sPart}_is_map_dragable") == 'on' ? 1 : 0,
            'map_type'                 => $sMapType,
            'map_lat'                  => $fLat,
            'map_lng'                  => $fLng,
            'map_zoom'                 => $iZoom,
            'parts'                    => $sPart,
            'custom'                   => '',
            'suffix'                   => 'Edit',
            'subclass'                 => 'bx_wmap_edit',
            'data_url'                 => BX_DOL_URL_MODULES . "?r=wmap/get_data_location/$iEntryId/{parts}/{instance}/{ts}",
            'save_data_url'            => BX_DOL_URL_MODULES . "?r=wmap/save_data/$iEntryId/{parts}/{zoom}/{map_type}/{lat}/{lng}/{instance}/{address}/{country}/{ts}",
            'save_location_url'        => '',
            'shadow_url'               => '',
            'key'                      => getParam('bx_wmap_key'),
        );
        $sMap = $this->_oTemplate->parseHtmlByName('map', $aVars);

        $oPermalinks = new BxDolPermalinks();
        $sBackLink   = BX_DOL_URL_ROOT . $oPermalinks->permalink($this->_aParts[$aLocation['part']]['permalink'] . $aLocation['uri']);
        $aVars       = array(
            'info' => sprintf(_t('_bx_wmap_edit'), "<a href=\"{$sBackLink}\">{$aLocation['title']}</a>",
                _t($this->_aParts[$sPart]['title_singular'])),
            'map'  => $sMap,
        );

        return array($this->_oTemplate->parseHtmlByName('map_edit', $aVars));
    }

    /**
     * Homepage block with world map
     *
     * @return html with world map
     */
    function serviceHomepageBlock()
    {
        $this->_oTemplate->addJs($this->_sProto . '://www.google.com/jsapi?key=' . getParam('bx_wmap_key'));
        $this->_oTemplate->addJs('BxWmap.js');
        $this->_oTemplate->addCss('main.css');

        return $this->serviceSeparatePageBlock(false, false, false, '', '', 'bx_wmap_homepage', 'bx_wmap_homepage',
            'Home', 'homepage');
    }

    /**
     * Module Homepage block with world map
     *
     * @return html with world map
     */
    function serviceHomepagePartBlock($sPart)
    {
        if (!isset($this->_aParts[$sPart])) {
            return '';
        }
        $this->_oTemplate->addJs($this->_sProto . '://www.google.com/jsapi?key=' . getParam('bx_wmap_key'));
        $this->_oTemplate->addJs('BxWmap.js');
        $this->_oTemplate->addCss('main.css');

        return $this->serviceSeparatePageBlock(false, false, false, $sPart, '', 'bx_wmap_homepage_part',
            'bx_wmap_home_' . $sPart, 'PartHome', 'part_home/' . $sPart, false);
    }

    /**
     * Separate page block with world map
     *
     * @return html with world map
     */
    function serviceSeparatePageBlock(
        $fLat = false,
        $fLng = false,
        $iZoom = false,
        $sPartsCustom = '',
        $sCustom = '',
        $sSubclass = 'bx_wmap_separate',
        $sParamPrefix = 'bx_wmap_separate',
        $sSuffix = 'Page',
        $sSaveLocationSuffix = 'separate_page',
        $isPartsSelector = true
    ) {
        if (false === $fLat) {
            $fLat = getParam($sParamPrefix . '_lat');
        }
        if (false === $fLng) {
            $fLng = getParam($sParamPrefix . '_lng');
        }
        if (false === $iZoom) {
            $iZoom = getParam($sParamPrefix . '_zoom');
        }

        $aVars = array(
            'msg_incorrect_google_key' => trim(_t('_bx_wmap_msg_incorrect_google_key')),
            'loading'                  => _t('_loading ...'),
            'map_control'              => getParam($sParamPrefix . '_control_type'),
            'map_is_type_control'      => getParam($sParamPrefix . '_is_type_control') == 'on' ? 1 : 0,
            'map_is_scale_control'     => getParam($sParamPrefix . '_is_scale_control') == 'on' ? 1 : 0,
            'map_is_overview_control'  => getParam($sParamPrefix . '_is_overview_control') == 'on' ? 1 : 0,
            'map_is_dragable'          => getParam($sParamPrefix . '_is_map_dragable') == 'on' ? 1 : 0,
            'map_type'                 => getParam($sParamPrefix . '_map_type'),
            'map_lat'                  => $fLat,
            'map_lng'                  => $fLng,
            'map_zoom'                 => $iZoom,
            'parts'                    => $sPartsCustom,
            'custom'                   => $sCustom,
            'suffix'                   => $sSuffix,
            'subclass'                 => $sSubclass,
            'data_url'                 => BX_DOL_URL_MODULES . "?r=wmap/get_data/{zoom}/{lat_min}/{lat_max}/{lng_min}/{lng_max}/{instance}/{parts}/{custom}",
            'save_data_url'            => '',
            'save_location_url'        => $this->isAdmin() ? BX_DOL_URL_MODULES . "?r=wmap/save_location_{$sSaveLocationSuffix}/{zoom}/{map_type}/{lat}/{lng}" : '',
            'shadow_url'               => $this->_oTemplate->getIconUrl('flag_icon_shadow.png'),
            'lang'                     => bx_lang_name(),
            'key'                      => getParam('bx_wmap_key'),
        );
        $sMap = $this->_oTemplate->parseHtmlByName('map', $aVars);

        if (!$isPartsSelector) {
            return array($sMap);
        }

        $aVarsParts     = array(
            'suffix'          => $aVars['suffix'],
            'subclass'        => $aVars['subclass'],
            'bx_repeat:parts' => array(),
        );
        $aPartsSelected = $this->_validateParts($sPartsCustom);
        foreach ($this->_aParts AS $k => $r) {
            $aVarsParts['bx_repeat:parts'][] = array(
                'part'    => $k,
                'title'   => _t($r['title']),
                'icon'    => $GLOBALS['oFunctions']->sysImage(false === strpos($r['icon_site'],
                    '.') ? $r['icon_site'] : $this->_oTemplate->getIconUrl($r['icon_site'])),
                'suffix'  => $aVars['suffix'],
                'checked' => isset($aPartsSelected[$k]) ? 'checked' : '',
            );
        }
        $sMapParts = $this->_oTemplate->parseHtmlByName('map_parts', $aVarsParts);

        return array($sMapParts . $sMap);
    }

    /**
     * Block with entry's location map
     *
     * @param $sPart    module/part name
     * @param $iEntryId entry's id which location is shown on the map
     * @return html with entry's location map
     */
    function serviceLocationBlock($sPart, $iEntryId)
    {
        if (!isset($this->_aParts[$sPart])) {
            return '';
        }

        $sParamPrefix = 'bx_wmap_entry_' . $sPart;
        $iEntryId     = (int)$iEntryId;
        $r            = $this->_oDb->getDirectLocation($iEntryId, $this->_aParts[$sPart]);

        $sBoxContent = '';
        if ($r && !empty($r['lat'])) {

            $aVars = array(
                'msg_incorrect_google_key' => _t('_bx_wmap_msg_incorrect_google_key'),
                'loading'                  => _t('_loading ...'),
                'map_control'              => getParam($sParamPrefix . '_control_type'),
                'map_is_type_control'      => getParam($sParamPrefix . '_is_type_control') == 'on' ? 1 : 0,
                'map_is_scale_control'     => getParam($sParamPrefix . '_is_scale_control') == 'on' ? 1 : 0,
                'map_is_overview_control'  => getParam($sParamPrefix . '_is_overview_control') == 'on' ? 1 : 0,
                'map_is_dragable'          => getParam($sParamPrefix . '_is_map_dragable') == 'on' ? 1 : 0,
                'map_lat'                  => $r['lat'],
                'map_lng'                  => $r['lng'],
                'map_zoom'                 => -1 != $r['zoom'] ? $r['zoom'] : (getParam($sParamPrefix . '_zoom') ? getParam($sParamPrefix . '_zoom') : BX_WMAP_ZOOM_DEFAULT_ENTRY),
                'map_type'                 => $r['type'] ? $r['type'] : (getParam($sParamPrefix . '_map_type') ? getParam($sParamPrefix . '_map_type') : 'normal'),
                'parts'                    => $sPart,
                'custom'                   => '',
                'suffix'                   => 'Location',
                'subclass'                 => 'bx_wmap_location_box',
                'data_url'                 => BX_DOL_URL_MODULES . "' + '?r=wmap/get_data_location/" . $iEntryId . "/" . $sPart . "/{instance}",
                'save_data_url'            => '',
                'save_location_url'        => '',
                'shadow_url'               => '',
                'lang'                     => bx_lang_name(),
                'key'                      => getParam('bx_wmap_key'),
            );
            $this->_oTemplate->addJs($this->_sProto . '://www.google.com/jsapi?key=' . getParam('bx_wmap_key'));
            $this->_oTemplate->addJs('BxWmap.js');
            $this->_oTemplate->addCss('main.css');

            $aVars2 = array(
                'map' => $this->_oTemplate->parseHtmlByName('map', $aVars),
            );
            $sBoxContent = $this->_oTemplate->parseHtmlByName('entry_location', $aVars2);
        }

        $sBoxFooter = '';
        if ($r['author_id'] == $this->_iProfileId || $this->isAdmin()) {
            $aVars      = array(
                'icon'  => $this->_oTemplate->getIconUrl('more.png'),
                'url'   => $this->_oConfig->getBaseUri() . "edit/$iEntryId/$sPart",
                'title' => _t('_bx_wmap_box_footer_edit'),
            );
            $sBoxFooter = $this->_oTemplate->parseHtmlByName('box_footer', $aVars);
            if (!$sBoxContent) {
                $sBoxContent = MsgBox(_t('_bx_wmap_msg_locations_is_not_defined'));
            }
        }

        if ($sBoxContent || $sBoxFooter) {
            return array($sBoxContent, array(), $sBoxFooter);
        }

        return '';
    }

    function serviceResponseEntryDelete($sPart, $iEntryId)
    {
        if (!isset($this->_aParts[$sPart])) {
            return false;
        }

        $aPart = $this->_aParts[$sPart];

        return $this->_oDb->deleteLocation((int)$iEntryId, $sPart);
    }

    function serviceResponseEntryAdd($sPart, $iEntryId)
    {
        return $this->serviceResponseEntryChange($sPart, $iEntryId);
    }

    function serviceResponseEntryChange($sPart, $iEntryId)
    {
        if (!isset($this->_aParts[$sPart])) {
            return false;
        }

        $aPart = $this->_aParts[$sPart];

        $a = $this->_oDb->getDirectLocation($iEntryId, $aPart);
        if (!$a) {
            return false;
        }

        if ($a['lat'] && $a['lng'] && $a['type']) {
            // Don't update location (just update privacy) 
            // if it is already geocoded automatically.
            // The manual update will not be erased (detected by 'type')
            $this->_oDb->updateLocationPrivacy((int)$a['id'],
                !empty($a['privacy']) ? $a['privacy'] : BX_WMAP_PRIVACY_DEFAULT);

            return true;
        }

        return $this->_updateLocation(0, $a);
    }

    function servicePartEnable($sPart, $isEnable, $isClearPartLocations = false)
    {
        if (!$this->_oDb->enablePart($sPart, (int)$isEnable)) {
            return false;
        }

        if ($isClearPartLocations) {
            $this->_oDb->clearLocations($sPart, false);
        }

        return true;
    }

    function servicePartUpdate($sPart, $a)
    {        
        if (!$this->_oDb->updatePart($sPart, $a))
            return false;

        return true;        
    }

    function servicePartInstall($sPart, $a)
    {
        $aDefaults = array(
            'part'                 => $sPart,
            'title'                => '',
            'title_singular'       => '',
            'icon'                 => '',
            'icon_site'            => '',
            'join_table'           => '',
            'join_where'           => '',
            'join_field_id'        => 'id',
            'join_field_country'   => '',
            'join_field_city'      => '',
            'join_field_state'     => '',
            'join_field_zip'       => '',
            'join_field_address'   => '',
            'join_field_latitude'  => '',
            'join_field_longitude' => '',
            'join_field_title'     => '',
            'join_field_uri'       => '',
            'join_field_author'    => '',
            'join_field_privacy'   => '',
            'permalink'            => '',
            'enabled'              => 1
        );

        $aOptions = array_merge($aDefaults, $a);

        if (!$this->_oDb->addPart($aOptions)) {
            return false;
        }

        return true;
    }

    function servicePartUninstall($sPart)
    {
        $this->_oDb->clearLocations($sPart, false);

        return $this->_oDb->removePart($sPart);
    }

    // ================================== events

    function onEventGeolocateProfile($iProfileId, $aLocation)
    {
        bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('bx_wmap', 'geolocate_profile', $iProfileId, $this->_iProfileId,
            array('location' => $aLocation));
        $oAlert->alert();
    }

    function onEventLocationManuallyUpdated($sPart, $iEntryId, $aLocation)
    {
        bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('bx_wmap', 'location_manually_updated', $iEntryId, $this->_iProfileId,
            array('location' => $aLocation, 'part' => $sPart));
        $oAlert->alert();
    }

    // ================================== permissions

    function isAllowedEditOwnLocation(&$aLocation)
    {
        if (!$this->_iProfileId) {
            return false;
        }
        if ($this->isAdmin()) {
            return true;
        }
        if ($aLocation && $aLocation['author_id'] == $this->_iProfileId) {
            return true;
        }

        return false;
    }

    function isAdmin()
    {
        return $GLOBALS['logged']['admin'] || $GLOBALS['logged']['moderator'];
    }

    // ================================== other

    function _geocode($sAddress, $sCountryCode = '')
    {
        $sStatus = false;

        $sAddress = rawurlencode($sAddress);

        $sUrl = bx_proto() . "://maps.googleapis.com/maps/api/geocode/json";

        $s = bx_file_get_contents($sUrl, array(
            'address' => $sAddress,
            'sensor'  => 'false'
        ));

        $oData = json_decode($s);
        if (null == $oData) {
            return false;
        }

        if ('OK' != $oData->status) {
            return false;
        }

        foreach ($oData->results as $oResult) {
            $sShortNameCountry = '';
            foreach ($oResult->address_components as $oAddressComponent) {
                if (in_array('country', $oAddressComponent->types)) {
                    $sShortNameCountry = $oAddressComponent->short_name;
                }
            }

            if (!$sCountryCode || ($sShortNameCountry && $sCountryCode == $sShortNameCountry)) {
                $oLocation = $oResult->geometry->location;

                return array($oLocation->lat, $oLocation->lng, $sShortNameCountry);
            }
        }

        return false;
    }

    function _updateLocation($iDelay, &$r)
    {
        $iDelay = (int)$iDelay;

        $iId = (int)$r['id'];
        $a   = false;

        if (isset($r['latitude']) && isset($r['longitude'])) {
            $r['latitude']  = floatval($r['latitude']);
            $r['longitude'] = floatval($r['longitude']);
            if (is_float($r['latitude']) && is_float($r['longitude'])) {
                if ($iDelay) {
                    sleep($iDelay);
                }
                $a = $this->_geocode($r['latitude'] . ', ' . $r['longitude']);
            }
        } else {
            $sState = '';
            if (isset($r['state']) && trim($r['state'])) {
                $sState = ' ' . $r['state'];
            }

            if (isset($r['address']) && trim($r['address'])) {
                if ($iDelay) {
                    sleep($iDelay);
                }
                $a = $this->_geocode($r['address'] . ' ' . $r['city'] . ' ' . $r['state'] . $r['country'],
                    $r['country']);
            }

            if (!$a && isset($r['zip']) && trim($r['zip'])) {
                if ($iDelay) {
                    sleep($iDelay);
                }
                $a = $this->_geocode($r['zip'] . ' ' . $r['country'], $r['country']);
            }

            if (!$a) {
                if ($iDelay) {
                    sleep($iDelay);
                }
                $a = $this->_geocode($r['city'] . ' ' . $r['state'] . $r['country'], $r['country']);
            }
        }

        $sTitle = process_db_input($r['title'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION);

        $mixedPrivacy = !empty($r['privacy']) ? $r['privacy'] : BX_WMAP_PRIVACY_DEFAULT;

        if ($a) {
            $this->_oDb->insertLocation($iId, $r['part'], $sTitle, $r['uri'], $a[0], $a[1], -1, '',
                process_db_input($r['city'] . ', ' . $r['country'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
                process_db_input($r['city'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
                process_db_input($r['country'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION), $mixedPrivacy, 0);
            $bRet = true;
        } else {
            $this->_oDb->insertLocation($iId, $r['part'], $sTitle, $r['uri'], 0, 0, -1, '', '', '', '', $mixedPrivacy,
                1);
            $bRet = false;
        }

        $this->onEventGeolocateProfile($iId, array(
            'lat'     => (isset($a[0]) ? $a[0] : false),
            'lng'     => (isset($a[1]) ? $a[1] : false),
            'country' => $sCountryCode
        ));

        return $bRet;
    }

    function _saveLocationByPrefix($sPrefix, $iZoom, $sMapType, $fLat, $fLng)
    {
        if (!$this->isAdmin()) {
            echo 'Access denied';

            return;
        }

        if ($iZoom = (int)$iZoom) {
            setParam($sPrefix . '_zoom', $iZoom);
        }

        switch ($sMapType) {
            case 'normal':
            case 'satellite':
            case 'hybrid':
                setParam($sPrefix . '_map_type', $sMapType);
        }

        if ($fLat = (float)$fLat) {
            setParam($sPrefix . '_lat', $fLat);
        }

        if ($fLng = (float)$fLng) {
            setParam($sPrefix . '_lng', $fLng);
        }

        echo 'ok';
    }

    function _saveLocationForm($sSuffix, $sMap)
    {
        if (is_array($sMap)) {
            $sMap = $sMap[0];
        }

        if (!preg_match('/^[A-Za-z0-9]+$/', $sSuffix)) {
            return '';
        }

        $aCustomForm = array(

            'form_attrs' => array(
                'name'     => "bx_wmap_save_location_{$sSuffix}",
                'onsubmit' => "return glBxWmap{$sSuffix}.saveLocation();",
                'method'   => 'post',
            ),

            'inputs' => array(

                'Map' => array(
                    'type'    => 'custom',
                    'content' => "<div class=\"bx_wmap_form_map\">$sMap</div>",
                    'name'    => 'Map',
                    'caption' => _t('_bx_wmap_admin_map'),
                ),

                'Submit' => array(
                    'type'  => 'submit',
                    'name'  => 'submit_form',
                    'value' => _t('_bx_wmap_admin_save_location'),
                ),
            ),
        );

        $f = new BxTemplFormView ($aCustomForm);

        return $f->getCode();
    }

    /**
     * @return 404 - not found, 403 - access denied, false - error occured, 1 - succesfully saved
     */
    function _saveData(
        $iEntryId,
        $sPart,
        $iZoom,
        $sMapType,
        $fLat,
        $fLng,
        $sMapClassInstanceName = '',
        $sAddress = 'null',
        $sCountry = 'null'
    ) {
        if (!isset($this->_aParts[$sPart])) {
            return 404;
        }

        $iEntryId  = (int)$iEntryId;
        $aLocation = $this->_iProfileId ? $this->_oDb->getDirectLocation($iEntryId, $this->_aParts[$sPart]) : false;

        if (!$aLocation || !$this->isAllowedEditOwnLocation($aLocation)) {
            return 403;
        }

        if (!$aLocation && ('null' == $fLat || 'null' == $fLng)) {
            return false;
        }

        $fLat     = 'null' != $fLat ? (float)$fLat : $aLocation['lat'];
        $fLng     = 'null' != $fLng ? (float)$fLng : $aLocation['lng'];
        $iZoom    = 'null' != $iZoom ? (int)$iZoom : ($aLocation ? $aLocation['zoom'] : -1);
        $sMapType = $sMapType && 'null' != $sMapType ? $sMapType : ($aLocation ? $aLocation['type'] : '');
        $sAddress = $sAddress && 'null' != $sAddress ? process_db_input($sAddress,
            BX_TAGS_STRIP) : ($aLocation ? process_db_input($aLocation['address'], BX_TAGS_NO_ACTION,
            BX_SLASHES_NO_ACTION) : '');
        $sCountry = $sCountry && 'null' != $sCountry ? process_db_input($sCountry,
            BX_TAGS_STRIP) : ($aLocation ? process_db_input($aLocation['country'], BX_TAGS_NO_ACTION,
            BX_SLASHES_NO_ACTION) : '');

        switch ($sMapType) {
            case 'normal':
            case 'satellite':
            case 'hybrid':
                break;
            default:
                $sMapType = 'normal';
        }

        $aLocation['city']  = process_db_input($aLocation['city'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION);
        $aLocation['title'] = process_db_input($aLocation['title'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION);
        $mixedPrivacy       = !empty($aLocation['privacy']) ? $aLocation['privacy'] : BX_WMAP_PRIVACY_DEFAULT;

        if (!$this->_oDb->insertLocation($iEntryId, $sPart, $aLocation['title'], $aLocation['uri'], $fLat, $fLng,
            $iZoom, $sMapType, $sAddress, $aLocation['city'], $sCountry, $mixedPrivacy)
        ) {
            return false;
        }

        $this->onEventLocationManuallyUpdated($sPart, $iEntryId, array(
            'lat'      => $fLat,
            'lng'      => $fLng,
            'zoom'     => $iZoom,
            'map_type' => $sMapType,
            'address'  => $sAddress,
            'country'  => $sCountry
        ));

        return true;
    }

    function _validateParts($sParts)
    {
        $aPartsRet = array();
        $aPartsTmp = explode(',', $sParts);
        foreach ($aPartsTmp as $sPart) {
            if (isset($this->_aParts[$sPart])) {
                $aPartsRet[$sPart] = $sPart;
            }
        }
        if (!$aPartsRet) {
            foreach ($this->_aParts as $sPart => $r) {
                $aPartsRet[$sPart] = $sPart;
            }
        }

        return $aPartsRet;
    }

    function _getIconArray($sBaseFilename = '', $isCountryFlag = false)
    {
        if ($isCountryFlag) {
            $this->aIconsSizes['country_flag']['url'] = $sBaseFilename;

            return $this->aIconsSizes['country_flag'];
        }

        if (!$sBaseFilename) {
            return $this->aIconsSizes['default'];
        }

        if (empty($this->aIconsSizes[$sBaseFilename])) {
            $this->aIconsSizes[$sBaseFilename] = $this->aIconsSizes['default'];
        }

        if (empty($this->aIconsSizes[$sBaseFilename]['url'])) {
            $this->aIconsSizes[$sBaseFilename]['url'] = $this->_oTemplate->getIconUrl($sBaseFilename);
        }

        return $this->aIconsSizes[$sBaseFilename];
    }

}
