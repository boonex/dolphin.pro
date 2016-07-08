<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesPageHome');

class BxPhotosPageHome extends BxDolFilesPageHome
{
    function __construct (&$oShared)
    {
        parent::__construct($oShared);
    }

    function getBlockCode_Cover ()
    {
    	$bUseFeatured = $this->oConfig->getGlParam('cover_featured') == 'on';

    	$iRows = (int)$this->oConfig->getGlParam('cover_rows');
    	$iColumns = (int)$this->oConfig->getGlParam('cover_columns');
    	$iExcess = 20;

    	$iCountRequired = $iRows * $iColumns + $iExcess;
    	$this->oSearch->clearFilters(array('activeStatus', 'allow_view', 'album_status', 'albumType', 'ownerStatus'), array('albumsObjects', 'albums'));
    	if($bUseFeatured)
	    	$this->oSearch->aCurrent['restriction']['featured'] = array(
	            'field' => 'Featured',
	            'value' => '1',
	            'operator' => '=',
	            'param' => 'featured'
	        );
    	$this->oSearch->aCurrent['paginate']['perPage'] = $iCountRequired;
        $aFiles = $this->oSearch->getSearchData();
        if(empty($aFiles))
        	return '';

        $iCount = count($aFiles);
        if($iCount < $iCountRequired)
        	while($iCount < $iCountRequired) {
        		$aFiles = array_merge($aFiles, $aFiles);
        		$iCount = count($aFiles);
        	}

		$sViewUrl = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'view/';

        $aTmplVarsImages = array();
        foreach($aFiles as $aFile)
        	$aTmplVarsImages[] = array(
        		'src' => $this->oSearch->getImgUrl($aFile['Hash'], 'browse'),
        		'link' => $sViewUrl . $aFile['uri'],
        		'title' => bx_html_attribute($aFile['title'])
        	);

		$this->oTemplate->addCss(array('cover.css'));
        $this->oTemplate->addJs(array('modernizr.js', 'jquery.gridrotator.js'));
        return $this->oTemplate->parseHtmlByName('cover.html', array(
        	'loading' => $GLOBALS['oFunctions']->loadingBoxInline(),
        	'bx_repeat:images' => $aTmplVarsImages,
        	'rows' => $iRows,
        	'columns' => $iColumns
        ));
    }

    function getBlockCode_LatestFile ()
    {
        $this->oSearch->clearFilters(array('activeStatus', 'allow_view', 'album_status', 'albumType', 'ownerStatus'), array('albumsObjects', 'albums'));
        $this->oSearch->aCurrent['restriction']['featured'] = array(
            'field' => 'Featured',
            'value' => '1',
            'operator' => '=',
            'param' => 'featured'
        );
        $this->oSearch->aCurrent['paginate']['perPage'] = 1;
        $aFiles = $this->oSearch->getSearchData();
        return $this->oSearch->getSwitcherUnit($aFiles[0], array('showLink'=>1, 'showRate' => 1, 'showDate' => 1, 'showFrom' => 1));
    }
}
