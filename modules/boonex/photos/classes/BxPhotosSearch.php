<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplSearchResultSharedMedia');

class BxPhotosSearch extends BxTemplSearchResultSharedMedia
{
    function __construct ($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '')
    {
        parent::__construct('BxPhotosModule');
        $this->aConstants['linksTempl'] = array(
            'home' => 'home',
            'file' => 'view/{uri}',
            'category' => 'browse/category/{uri}',
            'browseAll' => 'browse/',
            'browseUserAll' => 'albums/browse/owner/{uri}',
            'browseAllTop' => 'browse/top',
            'tag' => 'browse/tag/{uri}',
            'album' => 'browse/album/{uri}',
            'add' => 'browse/my/add',
            'manageProfilePhoto' => 'albums/my/manage_profile_photos/{uri}',
        );
        $aMain = array(
            'name' => 'bx_photos',
            'title' => '_bx_photos',
            'table' => 'bx_photos_main'
        );
        $this->aCurrent = array_merge($aMain, $this->aCurrent);
        $this->aCurrent['ownFields'] = array('ID', 'Title', 'Uri', 'Date', 'Size', 'Views', 'Rate', 'RateCount', 'Hash');
        $this->aCurrent['searchFields'] = array('Title', 'Tags', 'Desc', 'Categories');
        $this->aCurrent['rss']['title'] = _t('_bx_photos');
        $this->aCurrent['rss']['fields']['Image'] = 'Hash';

        // redeclaration some unique fav fields
        $this->aAddPartsConfig['favorite']['table'] = 'bx_photos_favorites';
        $this->aAddPartsConfig['favorite']['mainField'] = 'ID';

        $this->oTemplate = &$this->oModule->_oTemplate;
        $this->aConstants['filesUrl'] = $this->oModule->_oConfig->getFilesUrl();
        $this->aConstants['filesDir'] = $this->oModule->_oConfig->getFilesPath();
        $this->aConstants['filesInAlbumCover'] = 17;
        $this->aConstants['filesInEmptyAlbumCover'] = 1;
        $this->aConstants['picPostfix'] = $this->oModule->_oConfig->aFilePostfix;

        $this->aCurrent['restriction']['albumType']['value'] = $this->aCurrent['name'];

        switch ($sParamName) {
            case 'calendar':
                $this->aCurrent['restriction']['calendar-min'] = array('value' => "UNIX_TIMESTAMP('{$sParamValue}-{$sParamValue1}-{$sParamValue2} 00:00:00')", 'field' => 'Date', 'operator' => '>=', 'no_quote_value' => true);
                $this->aCurrent['restriction']['calendar-max'] = array('value' => "UNIX_TIMESTAMP('{$sParamValue}-{$sParamValue1}-{$sParamValue2} 23:59:59')", 'field' => 'Date', 'operator' => '<=', 'no_quote_value' => true);
                $this->aCurrent['title'] = _t('_bx_photos_caption_browse_by_day') . sprintf("%04u-%02u-%02u", $sParamValue, $sParamValue1, $sParamValue2);
                break;
            case 'top':
                $this->aCurrent['sorting'] = 'top';
                break;
            case 'popular':
                $this->aCurrent['sorting'] = 'popular';
                break;
            case 'featured':
                $this->aCurrent['restriction']['featured'] = array(
                    'value'=>'1', 'field'=>'Featured', 'operator'=>'=', 'paramName'=>'bx_photos_mode'
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
                $this->aCurrent['restriction']['albumType'] = array(
                    'value'=>$this->aCurrent['name'], 'field'=>'Type', 'operator'=>'=', 'paramName'=>'albumType', 'table'=>'sys_albums'
                );
                if ($sParamValue1 == 'owner' && strlen($sParamValue2) > 0) {
                    $this->aCurrent['restriction']['owner'] = array(
                        'value'=>$sParamValue2, 'field'=>'NickName', 'operator'=>'=', 'paramName'=>'ownerName', 'table' => 'Profiles'
                    );
                }
                break;
        }
    }

    function addCustomParts ()
    {
        if (!$this->bCustomParts) {
            $this->bCustomParts = true;
            $this->oModule->_oTemplate->addCss(array('search.css'));
            return '';
        }
    }

    function addAlbumJsCss($bDynamic = false) {
    	$sResult = parent::addAlbumJsCss($bDynamic);
    	$sResult .= $this->oTemplate->addCss(array(
    		'album.css'
    	), $bDynamic);

    	return $bDynamic ? $sResult : '';
    }

    function getAlbumCovers ($iAlbumId, $aParams = array())
    {
        $iAlbumId = (int)$iAlbumId;
        $iLimit = isset($aParams['filesInAlbumCover']) ? (int)$aParams['filesInAlbumCover'] : null;
        $aPics = $this->oModule->oAlbums->getAlbumCoverFiles($iAlbumId, array('table'=>$this->aCurrent['table'], 'field'=>'ID', 'fields_list'=>array('Uri', 'Hash')), array(array('field'=>'Status', 'value'=>'approved')), $iLimit);
        return $aPics;
    }

    function getAlbumsBlock ($aSectionParams = array(), $aAlbumParams = array(), $aCustom = array())
    {
    	$aCustom['unit_css_class'] = '.sys_album_unit_wrp';

    	$aResult = parent::getAlbumsBlock($aSectionParams, $aAlbumParams, $aCustom);
    	if(!empty($aResult[0])) {
    		$aResult[3] = true;

    		$aResult[0] = $this->oTemplate->parseHtmlByName('default_margin_thd.html', array(
    			'content' => $aResult[0]
    		));
    	}

    	return $aResult;
    }
    
    function getAlbumCoverUrl (&$aIdent)
    {
        return $this->getImgUrl($aIdent['Hash'], 'browse');
    }

    function displayAlbumUnit ($aData, $bCheckPrivacy = true)
    {
    	$sContent = parent::displayAlbumUnit($aData, $bCheckPrivacy);
    	return $this->oTemplate->parseHtmlByContent($sContent, array(
    		'bx_if:show_activation' => array(
    			'condition' => (int)$aData['ObjCount'] > 1,
    			'content' => array()
    		)
    	));
    }

    function _getAlbumUnitItem($iIndex, $aPicture, $aParams = array())
    {
    	$aResult = parent::_getAlbumUnitItem($iIndex, $aPicture, $aParams);
    	$aResult['bx_if:exist']['content']['url'] = '';

    	$sClass = '';
    	if(empty($aPicture)){
    		if($iIndex == 0) {
    			$aResult['bx_if:not-exist']['condition'] = true;
    			$aResult['bx_if:not-exist']['content']['class'] = ' sys-ai-empty';
    		}
    		else
    			$aResult['bx_if:not-exist']['condition'] = false;
    	}
    	else { 
	    	switch($iIndex) {
	    		case 0:
	    			$sClass = 'sys-ai-front';
	    			break;
	    		case 1:
	    			$sClass = 'sys-ai-middle';
	    			break;
	    		case 2:
	    			$sClass = 'sys-ai-back';
	    			break;
	    		default:
	    			$sClass = 'sys-ai-out';
	    	}
    		$aResult['bx_if:exist']['content']['class'] = ' ' . $sClass;

    		if(!empty($aPicture['Uri']))
    			$aResult['bx_if:exist']['content']['url'] = $this->getCurrentUrl('file', $aPicture['id_object'], $aPicture['Uri']);
    		if(empty($aResult['bx_if:exist']['content']['url']) && !empty($aParams['album_url']))
    			$aResult['bx_if:exist']['content']['url'] = $aParams['album_url'];
    	}

    	return $aResult;
    }

    function getImgUrl ($sHash, $sImgType = 'browse')
    {
        return BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'get_image/' . $sImgType .'/' . $sHash . '.jpg';
    }

    function getLength ($sSize)
    {
        return $sSize;
    }

    function getLatestFile ()
    {
        $aParams['DisplayPagination'] = 0;
        $aParams['DisplayWhenAgo'] = 1;
        $aParams['DisplayViews'] = 0;
        $aParams['DisplayLink'] = 1;
        $aParams['DisplayProfile'] = 1;

        if (isset($this->aCurrent['restriction']['owner']['value']) && (int)$this->aCurrent['restriction']['owner']['value'] != 0)
            $aParams['PID'] = $this->aCurrent['restriction']['owner']['value'];

        if (isset($this->aCurrent['restriction']['category']['value']) && strlen($this->aCurrent['restriction']['category']['value']) > 0)
            $aParams['Category'] = $this->aCurrent['restriction']['category']['value'];

        if (isset($this->aCurrent['restriction']['tag']['value']) && strlen($this->aCurrent['restriction']['tag']['value']) > 0)
            $aParams['Tag'] = $this->aCurrent['restriction']['tag']['value'];

        return  '<div class="latestFile">'.$this->servicePhotoBlock($aParams).'</div>';
    }

    /**
     * Get image of the specified type by image id
     * @param $aImageInfo image info array with the following info
     *          $aImageInfo['Avatar'] - photo id, NOTE: it not relatyed to profiles avataras module at all
     * @param $sImgType image type
     */
    function serviceGetImage ($aImageInfo, $sImgType = 'thumb')
    {
        $iPicID = (int)$aImageInfo['Avatar'];
        $aImg = $this->_getImageFullInfo($iPicID, $sImgType);
        if (strlen($aImg['file']) > 0) {
            $sFileName = $aImg['file'];
            $isNoImage = false;
        }
        return array('file' => $sFileName, 'title' => $aImg['title'], 'width' => $aImg['width'], 'height' => $aImg['height'], 'no_image'=>$isNoImage);
    }

    function serviceGetEntry($iId, $sType = 'thumb')
    {
        $aImageInfo = $this->_getImageFullInfo($iId, $sType);
        return empty($aImageInfo['file']) ? array() : $aImageInfo;
    }

    function serviceGetItemArray($iId, $sType = 'browse')
    {
        return $this->serviceGetEntry($iId, $sType);
    }

    function serviceGetPhotoArray($iId, $sType = 'thumb')
    {
        return $this->serviceGetEntry($iId, $sType);
    }

    function _getImageFullInfo($iId, $sType = 'thumb')
    {
        $aImageInfo = $this->_getImageDbInfo($iId);

        $iWidth = (int)$this->oModule->_oConfig->getGlParam($sType . '_width');
        $iHeight = (int)$this->oModule->_oConfig->getGlParam($sType . '_height');

        $iWidth = $iWidth == 0 ? $this->oModule->_oConfig->aFilesConfig[$sType]['size_def'] : $iWidth;
        $iHeight = $iHeight == 0 ? $this->oModule->_oConfig->aFilesConfig[$sType]['size_def'] : $iHeight;

        $sImagePath = $sImageUrl = $sBrowseUrl = '';
        if(is_array($aImageInfo) && !empty($aImageInfo)) {
            $sImageUrl = $this->_getImageFullUrl($aImageInfo, $sType);
            $sImagePath = $this->oModule->_oConfig->getFilesPath() . $aImageInfo['id'] . $this->aConstants['picPostfix'][$sType];
            $sBrowseUrl = !empty($aImageInfo['uri']) ? $this->getCurrentUrl('file', $iId, $aImageInfo['uri']) : '';
        }
        return array(
            'id' => $iId,
            'file' => $sImageUrl,
            'file_path' => $sImagePath,
            'path' => $sImagePath,
            'title' => $aImageInfo['title'],
            'owner' => $aImageInfo['owner'],
            'description' => $aImageInfo['description'],
            'width' => $iWidth + 2 * 2,
            'height' => $iHeight + 2 * 2,
            'url' => $sBrowseUrl,
            'date' => $aImageInfo['date'],
            'rate' => $aImageInfo['rate'],
            'rate_count' => $aImageInfo['rate_count'],
            'comments_count' => $aImageInfo['comments_count'],
            'views_count' => $aImageInfo['views_count'],
            'status' => $aImageInfo['status'],
            'album_id' => $aImageInfo['album_id']
        );
    }

    function _getImageDbInfo ($iId)
    {
        $iId = (int)$iId;
        $sqlQuery = "SELECT a.`ID` as `id`,
                            a.`Ext` as `ext`,
                            a.`Title` as `title`,
                            a.`Desc` as `description`,
                            a.`Uri` as `uri`,
                            a.`Owner` as `owner`,
                            a.`Date` as `date`,
                            a.`Rate` as `rate`,
                            a.`RateCount` as `rate_count`,
                            a.`CommentsCount` as `comments_count`,
                            a.`Views` as `views_count`,
                            a.`Hash`,
                            a.`Status` AS `status`,
                            b.`id_album` as `album_id`
                            FROM `bx_photos_main` as a
                            LEFT JOIN `sys_albums_objects` as b ON b.`id_object` = a.`ID`
                            LEFT JOIN `sys_albums` as c ON c.`ID`=b.`id_album`
                            WHERE a.`ID`='" . $iId . "' AND c.`Type`='bx_photos'";
        $aImageInfo = ($iId) ? db_arr($sqlQuery) : null;
        return $aImageInfo;
    }

    // get image source url
    function _getImageFullUrl ($aImageInfo, $sType = 'thumb')
    {
        $sName = $aImageInfo['id'] . $this->aConstants['picPostfix'][$sType];
        $sName = str_replace('{ext}', $aImageInfo['ext'], $sName);
        $sImageUrl = !empty($aImageInfo['id']) && extFileExists($this->oModule->_oConfig->getFilesPath() . $sName) ? $this->getImgUrl($aImageInfo['Hash'], $sType) : '';
        return $sImageUrl;
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
            'voteTime' => 'gal_date'
        );
    }

    function serviceGetProfileAlbumBlock($iProfileId, $sSpecUrl = '')
    {
        $sCaption = str_replace('{nickname}', getUsername($iProfileId), $this->oModule->_oConfig->getGlParam('profile_album_name'));
        $sUri = uriFilter($sCaption);

        $oAlbum = new BxDolAlbums('bx_photos');
        $aAlbum = $oAlbum->getAlbumInfo(array('fileUri' => $sUri, 'owner' => $iProfileId));
        if((int)$aAlbum['ObjCount'] <= 0)
            return '';

		$aAlbum['show_as_list'] = true;
		$aAlbum['enable_center'] = true;
        return array($this->displayAlbumUnit($aAlbum), array(), array(), false);
    }

    function servicePhotoBlock ($aParams)
    {
        return $this->getPhotoBlock($aParams);
    }

    function serviceProfilePhotoBlock ($aParams)
    {
        if(!isset($aParams['PID']) || empty($aParams['PID']))
            return '';

        $sOwner = getUsername($aParams['PID']);
        $sCaption = str_replace('{nickname}', $sOwner, $this->oModule->_oConfig->getGlParam('profile_album_name'));

        if((int)$aParams['PID'] == getLoggedId())
        	$aParams['LinkUnitTo'] = $this->getCurrentUrl('manageProfilePhoto', 0, uriFilter($sCaption)) . '/owner/' . $sOwner;

        $aParams['DisplayRate'] = 0;
        $aParams['DisplayPagination'] = 0;
        $aParams['DisplayLink'] = 0;
        $aParams['Limit'] = 1;

        return $this->getProfilePhotoBlock($aParams);
    }

    function serviceProfilePhoto ($iProfileId, $sType = 'icon', $sReturnType = 'link')
    {
        return $this->getProfilePhoto(array(
        	'profile_id' => $iProfileId,
        	'album' => 'profile_album_name',
        	'type' => $sType,
        	'return_type' => $sReturnType
        ));
    }

	function serviceProfileCover ($iProfileId, $sType = 'file', $sReturnType = 'link')
    {
        return $this->getProfilePhoto(array(
        	'profile_id' => $iProfileId,
        	'album' => 'profile_cover_album_name',
        	'type' => $sType,
        	'return_type' => $sReturnType
        ));
    }

    function serviceProfilePhotoSwitcherBlock ($aParams)
    {
        if(!isset($aParams['PID']) || empty($aParams['PID']))
            return '';

        $aParams['DisplayScroller'] = 1;
        $aParams['DisplayPagination'] = 1;
        $aParams['DisplayLink'] = 1;

        return $this->getProfilePhotoBlock($aParams);
    }

    function serviceGetFilesInCat ($iId, $sCategory = '')
    {
        $aFiles = $this->getFilesInCatArray($iId, $sCategory);
        foreach ($aFiles as $k => $aRow) {
            $aFiles[$k]['icon']  = $this->getImgUrl($aRow['Hash'], 'icon');
            $aFiles[$k]['thumb'] = $this->getImgUrl($aRow['Hash'], 'thumb');
            $aFiles[$k]['file']  = $this->getImgUrl($aRow['Hash'], 'file');
        }
        return $aFiles;
    }

    function serviceGetFilesInAlbum ($iAlbumId, $isCheckPrivacy = false, $iViewer = 0, $aLimits = array())
    {
        if (!$iViewer)
            $iViewer = $this->oModule->_iProfileId;
        if ($isCheckPrivacy && !$this->oModule->oAlbumPrivacy->check('album_view', (int)$iAlbumId, $iViewer))
            return array();
        $aFiles = $this->getFilesInAlbumArray($iAlbumId, $aLimits);
        foreach ($aFiles as $k => $aRow) {
            $aFiles[$k]['icon']  = $this->getImgUrl($aRow['Hash'], 'icon');
            $aFiles[$k]['thumb'] = $this->getImgUrl($aRow['Hash'], 'thumb');
            $aFiles[$k]['file']  = $this->getImgUrl($aRow['Hash'], 'file');
        }
        return $aFiles;
    }

    function serviceGetAllProfilePhotos ($iProfId, $aLimits = array())
    {
        $aFiles = $this->getProfileFiles($iProfId, $aLimits);
        foreach ($aFiles as $k => $aRow) {
            foreach ($this->oModule->_oConfig->aFilesConfig as $sType => $aFileConfig) {
                if (isset($aFileConfig['size_def']))
                    $aFiles[$k][$sType]  = $this->getImgUrl($aRow['Hash'], $sType);
            }
        }
        return $aFiles;
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPost($aEvent)
    {
        return $this->oModule->getWallPost($aEvent, 'picture-o');
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostOutline($aEvent)
    {
        return $this->oModule->getWallPostOutline($aEvent, 'picture-o');
    }

    function getProfilePhotoBlock($aParams)
    {
        $sCaption = str_replace('{nickname}', getUsername($aParams['PID']), $this->oModule->_oConfig->getGlParam('profile_album_name'));
        $sUri = uriFilter($sCaption);
        $oAlbum = new BxDolAlbums('bx_photos');
        $aAlbumInfo = $oAlbum->getAlbumInfo(array('fileUri' => $sUri, 'owner' => $aParams['PID']), array('ID'));
        if(empty($aAlbumInfo) && $this->oModule->_iProfileId == (int)$aParams['PID']) {
            $aData = array(
                'caption' => $sCaption,
                'owner' => $this->oModule->_iProfileId,
                'AllowAlbumView' => $this->oModule->oAlbumPrivacy->_oDb->getDefaultValueModule('photos', 'album_view'),
            );
            $aAlbumInfo['ID'] = $oAlbum->addAlbum($aData, false);
        }

        if(!$this->oModule->oAlbumPrivacy->check('album_view', $aAlbumInfo['ID'], $this->oModule->_iProfileId))
            return '';

        $this->aCurrent['sorting'] = 'album_order';
        $this->aCurrent['restriction']['album'] = array(
            'value' => $sUri, 'field' => 'Uri', 'operator' => '=', 'paramName' => 'albumUri', 'table' => 'sys_albums'
        );

        return $this->getPhotoBlock($aParams);
    }

	function getProfilePhoto($aParams)
    {
    	$iProfileId = !empty($aParams['profile_id']) ? (int)$aParams['profile_id'] : 0;
    	$sAlbum = !empty($aParams['album']) ? $aParams['album'] : 'profile_album_name';
    	$sType = !empty($aParams['type']) ? $aParams['type'] : 'icon';
    	$sReturnType = !empty($aParams['return_type']) ? $aParams['return_type'] : 'link';

    	$aDefaultAlbums = $this->oModule->_oConfig->getDefaultAlbums();
		if(!empty($sAlbum) && in_array($sAlbum, $aDefaultAlbums)) {
			bx_import('BxDolAlbums');
			$sAlbum = BxDolAlbums::getAbumUri($this->oModule->_oConfig->getGlParam($sAlbum), $iProfileId);
		}

        $oAlbum = new BxDolAlbums('bx_photos');
        $aAlbumInfo = $oAlbum->getAlbumInfo(array('fileUri' => $sAlbum, 'owner' => $iProfileId), array('ID'));
        if (!$this->oModule->oAlbumPrivacy->check('album_view', $aAlbumInfo['ID'], getLoggedId()))
            return '';

        $sKeywordGet = $sKeywordPost = null;
        
        if (isset($_GET['keyword']))
        {
            $sKeywordGet = $_GET['keyword'];
            unset($_GET['keyword']);
        }
        elseif(isset($_POST['keyword']))
        {
            $sKeywordPost = $_POST['keyword'];
            unset($_POST['keyword']);
        }

        $aSavePaginate = array();
        if(isset($_GET['page'], $_GET['per_page']))
        	$aSavePaginate = array($_GET['page'], $_GET['per_page']);

        unset($_GET['page']);
        unset($_GET['per_page']);

        $this->aCurrent['paginate']['perPage'] = 1;
        $this->aCurrent['paginate']['page'] = 1;
        $this->aCurrent['restriction']['owner']['value'] = $iProfileId;
        $this->aCurrent['sorting'] = 'album_order';
        $this->aCurrent['restriction']['album'] = array(
            'value' => $sAlbum, 'field' => 'Uri', 'operator' => '=', 'paramName' => 'albumUri', 'table' => 'sys_albums'
        );

        $aFilesList = $this->getSearchData();

        if(!empty($aSavePaginate))
        	list($_GET['page'], $_GET['per_page']) = $aSavePaginate;

        if (!is_null($sKeywordGet))
            $_GET['keyword'] = clear_xss($sKeywordGet);
        elseif(!is_null($sKeywordPost))
            $_POST['keyword'] = clear_xss($sKeywordPost);

        if (!$this->aCurrent['paginate']['totalNum'])
            return '';

        $aFile = array_pop($aFilesList);
        $aFile['file_url'] = $this->getImgUrl($aFile['Hash'], $sType);
        $aFile['view_url'] = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri() . 'view/' . $aFile['uri'];

        if($sReturnType == 'full')
        	return $aFile;

        return $aFile['file_url'];
    }

    function getPhotoBlock ($aParams = array())
    {
        $this->aCurrent['paginate']['perPage'] = 20;
        $aShowParams = array('showScroller' => 0, 'showRate' => 1, 'showPaginate' => 0, 'showViews' => 0, 'showDate' => 0, 'showLink' => 0, 'showFrom' => 0);

        if(count($aParams) > 0) {
            foreach( $aParams as $sKeyName => $sKeyValue ) {
                switch ($sKeyName) {
                    case 'PID':
                        $this->aCurrent['restriction']['owner']['value'] = (int)$sKeyValue;
                        break;
                    case 'Category':
                        $this->aCurrent['restriction']['category']['value'] = strip_tags($sKeyValue);
                        break;
                    case 'Tag':
                        $this->aCurrent['restriction']['tag']['value'] = strip_tags($sKeyValue);
                        break;
                    case 'Limit':
                        $this->aCurrent['paginate']['perPage'] = (int)$sKeyValue;
                        break;
                    case 'DisplayScroller':
                        $aShowParams['showScroller'] = $sKeyValue == 1 ? 1 : 0;
                        break;
                    case 'DisplayRate':
                        $aShowParams['showRate'] = $sKeyValue == 1 ? 1 : 0;
                        break;
                    case 'DisplayPagination':
                        $aShowParams['showPaginate'] = $sKeyValue == 1 ? 1 : 0;
                        break;
                    case 'DisplayViews':
                        $aShowParams['showViews'] = $sKeyValue == 1 ? 1 : 0;
                        break;
                    case 'DisplayWhenAgo':
                        $aShowParams['showDate'] = $sKeyValue == 1 ? 1 : 0;
                        break;
                    case 'DisplayLink':
                        $aShowParams['showLink'] = $sKeyValue == 1 ? 1 : 0;
                        break;
                    case 'DisplayProfile':
                        $aShowParams['showFrom'] = $sKeyValue == 1 ? 1 : 0;
                        break;
                    case 'LinkUnitTo':
                        $aShowParams['linkUnitTo'] = $sKeyValue;
                        break;
                }
            }
        }

        $aFilesList = $this->getSearchData();
        $iCnt = $this->aCurrent['paginate']['totalNum'];

        if($iCnt) {
            $aUnit = array();
            $aUnits = array();
            if (defined('BX_PROFILE_PAGE') || defined('BX_MEMBER_PAGE')) {
                $iPhotoWidth = 259;
                $sImgWidth = 'style="width:' . $iPhotoWidth . 'px;"';
            } else {
                $iPhotoWidth = (int)$this->oModule->_oConfig->getGlParam('file_width');
                $iPhotoWidth = ($iPhotoWidth > 1) ? $iPhotoWidth : 600;
                $sImgWidth = '';
            }

            foreach ($aFilesList as $iKey => $aData) {
                $sPicUrl = $this->getImgUrl($aData['Hash'], 'icon');
                $aUnits[] = array(
                    'imageId' => $iKey + 1,
                    'picUrl' => $sPicUrl
                );
                $sPicLinkElements .= 'aPicLink['.($iKey + 1).'] = '.$aData['id'].';';
                if ($iKey == 0) {
                    $aAdd = array('switchWidth' => ($iPhotoWidth + 2), 'imgWidth' => $sImgWidth);
                    $aUnit['switcherUnit'] = $this->getSwitcherUnit($aData, $aShowParams, $aAdd);
                }
            }

            $aUnit['moduleUrl'] = BX_DOL_URL_ROOT . $this->oModule->_oConfig->getBaseUri();
            $aUnit['count'] = $iCnt;
            $aUnit['picWidth'] = $iPhotoWidth;
            $aUnit['picBoxWidth'] = $aUnit['switchWidth'] = $iPhotoWidth + 2;
            $aUnit['switchWidthOut'] = $aUnit['switchWidth'] + 2;
            $aUnit['bx_if:show_scroller'] = array(
                'condition' => false,
                'content' => array()
            );

            if((int)$aShowParams['showScroller'] == 1) {
                $bScroller = false;
                $iContainerWidth = $iContentWidth = $iCnt * 40;
                if($iContentWidth > $aUnit['picWidth']) {
                    $bScroller = true;
                    $iContainerWidth = $aUnit['picBoxWidth'] - 72;
                }

                $aUnit['bx_if:show_scroller'] = array(
                    'condition' => true,
                    'content' => array(
                        'switchWidthOut' => $aUnit['switchWidthOut'],
                        'containerWidth' => $iContainerWidth,
                        'contWidth' => $iContentWidth,
                        'bx_if:scrollerBack' => array(
                            'condition' => $bScroller,
                            'content' => array(1)
                        ),
                        'bx_repeat:iconBlock' => $aUnits,
                        'bx_if:scrollerNext' => array(
                            'condition' => $bScroller,
                            'content' => array(1),
                        )
                    )
                );
            }

            $aUnit['picLinkElements'] = $sPicLinkElements;
            if ($aShowParams['showPaginate'] == 1) {
                $aLinkAddon = $this->getLinkAddByPrams();
                $oPaginate = new BxDolPaginate(array(
                    'page_url' => $aUnit['changeUrl'],
                    'count' => $iCnt,
                    'info' => false,
                    'per_page' => 1,
                    'page' => $this->aCurrent['paginate']['page'],
                    'on_change_page' => 'getCurrentImage({page})',
                ));
                $aUnit['paginate'] = $oPaginate->getPaginate();
            } else
                $aUnit['paginate'] = '';

            $this->oTemplate->addCss('search.css');
            return $this->oTemplate->parseHtmlByName('photo_switcher.html', $aUnit);
        } elseif ($this->oModule->_iProfileId != 0 && $this->oModule->_iProfileId == (int)$this->aCurrent['restriction']['owner']['value']) {
            ob_start();
            ?>
            <div class="paginate bx-def-padding-sec-left bx-def-padding-sec-right">
                <div class="view_all">
                    <a href="__lnk_url__" title="__lnk_title__">__lnk_content__</a>
                </div>
             </div>
            <?php
            $sCode = ob_get_clean();

            bx_import('BxDolAlbums');
            $sCaption = BxDolAlbums::getAbumUri($this->oModule->_oConfig->getGlParam('profile_album_name'), $this->oModule->_iProfileId);

            $sLinkTitle = _t('_bx_photos_add');
            return MsgBox(_t('_Empty')) . $this->oTemplate->parseHtmlByContent($sCode, array(
                'lnk_url' => $this->oModule->_oConfig->getBaseUri() . 'albums/my/add_objects/' . $sCaption . '/owner/' . getUsername($this->oModule->_iProfileId),
                'lnk_title' => $sLinkTitle,
                'lnk_content' => $sLinkTitle
            ));
        }
        return MsgBox(_t('_Empty'));
    }

    function getSwitcherUnit (&$aData, $aShowParams = array(), $aAddElems = array())
    {
        if (!is_array($aData))
            return;

        $iPhotoWidth = (int)$this->oModule->_oConfig->getGlParam('file_width') ? (int)$this->oModule->_oConfig->getGlParam('file_width') : 602;
        $iWidth = (int)$aAddElems['switchWidth'] > 0 ? (int)$aAddElems['switchWidth'] : $iPhotoWidth;
        $sImgUrl = $this->getCurrentUrl('file', $aData['id'], $aData['uri']);
        $aUnit = array(
            'switchWidth' => $iWidth,
            'imgWidth' => !empty($aAddElems['imgWidth']) ? $aAddElems['imgWidth']: '',
            'picUrl' => $this->getImgUrl($aData['Hash'], 'file'),
            'href' => isset($aShowParams['linkUnitTo']) && !empty($aShowParams['linkUnitTo']) ? $aShowParams['linkUnitTo'] : $sImgUrl,
            'bx_if:href' => array(
                'condition' => (int)$aShowParams['showLink'] != 0,
                'content' => array(
                    'href' => $sImgUrl,
                    'title' => $aData['title']
                )
            ),
            'bx_if:rate' => array(
                'condition' => (int)$aShowParams['showRate'] != 0,
                'content' => array(
                    'rate' => $this->oRate && $this->oRate->isEnabled() ? $this->oRate->getJustVotingElement(1, $aData['id'], $aData['Rate']) : $this->oRate->getJustVotingElement(0, 0, $aData['Rate'])
                )
            ),
            'bx_if:date' => array(
                'condition' => (int)$aShowParams['showDate'] != 0,
                'content' => array(
                    'date' => defineTimeInterval($aData['date'])
                )
            ),
            'bx_if:from' => array(
                'condition' => (int)$aShowParams['showFrom'] != 0,
                'content' => array(
                    'profileUrl' => getProfileLink($aData['ownerId']),
                    'nick' => getNickName($aData['ownerId'])
                )
            )
        );
        return $this->oTemplate->parseHtmlByName('switcher_unit.html', $aUnit);
    }

    function getModuleFolder ()
    {
        return 'boonex/photos';
    }

    function getRssUnitImage (&$a, $sField)
    {
        return $this->getImgUrl($a[$sField]);
    }
}
