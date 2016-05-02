<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolFilesPageAlbumsOwner.php');

class BxPhotosPageAlbumsOwner extends BxDolFilesPageAlbumsOwner
{
    function __construct(&$oShared, $aParams = array())
    {
        parent::__construct('bx_photos_albums_owner', $oShared, $aParams);
    }

    function getBlockCode_ProfilePhotos()
    {
        list($sParamName, $sParamValue, $sParamValue1, $sParamValue2, $sParamValue3) = $this->aAddParams;
        if($sParamValue != 'owner')
            return '';

        $oSearch = $this->getSearchObject();
        $oSearch->aCurrent['restriction']['album'] = array(
            'value'=>'', 'field'=>'Uri', 'operator'=>'=', 'paramName'=>'albumUri', 'table'=>'sys_albums'
        );

        $oSearch->aCurrent['restriction']['album_owner'] = array(
            'value'=>'', 'field'=>'Owner', 'operator'=>'=', 'paramName'=>'albumOwner', 'table'=>'sys_albums'
        );

        $sUri = BxDolAlbums::getAbumUri($this->oConfig->getGlParam('profile_album_name'), $this->iOwnerId);
        $aParams = array('album' => $sUri, 'owner' => $this->iOwnerId);
        $aCustom = array(
            'per_page' => $this->oConfig->getGlParam('number_top'),
            'simple_paginate' => FALSE
        );
        $aHtml = $oSearch->getBrowseBlock($aParams, $aCustom);
        return array($aHtml['code'], $aHtml['menu_top'], $aHtml['menu_bottom'], '');
    }
}
