<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlbums.php');

class BxDolFilesPageAlbumsOwner extends BxDolPageView
{
    var $oAlbum;
    var $oModule;
    var $oTemplate;
    var $oConfig;
    var $oDb;
    var $iOwnerId;
    var $aAddParams;

    function __construct($sPageName, &$oShared, $aParams = array())
    {
        parent::__construct($sPageName);
        $this->oModule   = $oShared;
        $this->oTemplate = $oShared->_oTemplate;
        $this->oConfig   = $oShared->_oConfig;
        $this->oDb       = $oShared->_oDb;

        $this->aAddParams = $aParams;
        list($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3) = $this->aAddParams;

        $this->iOwnerId = getID($sParamValue1);
        $this->oAlbum   = new BxDolAlbums($this->oConfig->getMainPrefix(), $this->iOwnerId);
    }

    function getBlockCode_Browse($iBlockId)
    {
        foreach ($this->aAddParams as $sValue) {
            if (strlen($sValue) > 0) {
                $sArg .= '/' . rawurlencode($sValue);
            } else {
                break;
            }
        }
        $aCustom['simple_paginate_url']      = $aCustom['paginate_url'] = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'albums' . $sArg;
        $aCustom['simple_paginate_view_all'] = false;

        $oSearch = $this->getSearchObject();
        $aCode   = $oSearch->getAlbumsBlock(array(), array('hide_default' => true), $aCustom);
        if (!$oSearch->aCurrent['paginate']['totalAlbumNum']) {
            return MsgBox(_t('_Empty'));
        }

        return $aCode;
    }

    function getBlockCode_Favorited($aParams = array())
    {
        $sEmpty = MsgBox(_t('_Empty'));

        if ($this->iOwnerId == 0) {
            return $sEmpty;
        }

		$sUnitCssClass = isset($aParams['unit_css_class']) ? $aParams['unit_css_class'] : '.sys_file_search_unit';

        $oSearch = $this->getSearchObject();
        $oSearch->clearFilters(array('activeStatus', 'allow_view', 'album_status', 'albumType', 'ownerStatus'),
            array('albumsObjects', 'albums', 'icon'));
        if (isset($oSearch->aAddPartsConfig['favorite']) && !empty($oSearch->aAddPartsConfig['favorite'])) {
            $oSearch->aCurrent['join']['favorite']   = $oSearch->aAddPartsConfig['favorite'];
            $oSearch->aCurrent['restriction']['fav'] = array(
                'value'    => $this->iOwnerId,
                'field'    => $oSearch->aAddPartsConfig['favorite']['userField'],
                'operator' => '=',
                'table'    => $oSearch->aAddPartsConfig['favorite']['table']
            );
        }
        $oSearch->aCurrent['paginate']['perPage'] = (int)$this->oConfig->getGlParam('number_top');

        $sCode = $oSearch->displayResultBlock();
        if(!empty($sUnitCssClass))
        	$sCode = $GLOBALS['oFunctions']->centerContent($sCode, $sUnitCssClass);

        if($oSearch->aCurrent['paginate']['totalNum'] > 0) {
            $oSearch->aConstants['linksTempl']['favorited'] = 'browse/favorited';

            $aBottomMenu = $oSearch->getBottomMenu('favorited', 0, '');

            return array($sCode, array(), $aBottomMenu, false);
        } else {
            return array($sEmpty, array(), array(), '');
        }
    }

    function getSearchObject()
    {
        list($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3) = $this->aAddParams;

        bx_import('Search', $this->oModule->_aModule);
        $sClassName = $this->oConfig->getClassPrefix() . 'Search';
        $oSearch    = new $sClassName($sParamValue, $sParamValue1, $sParamValue2, $sParamValue3);

        if (!empty($sParamValue) && !empty($sParamValue1) && isset($oSearch->aCurrent['restriction'][$sParamValue])) {
            $oSearch->aCurrent['restriction'][$sParamValue]['value'] = 'owner' == $sParamValue ? getID($sParamValue1) : $sParamValue1;
        }

        return $oSearch;
    }
}
