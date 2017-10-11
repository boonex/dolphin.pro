<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplSearchResultSharedMedia');

class BxSoundsSearch extends BxTemplSearchResultSharedMedia
{
    function __construct($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '')
    {
        parent::__construct('BxSoundsModule');
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
        // main part of aCurrent settings, usual most unique part of every module
        $aMain = array(
            'name' => 'bx_sounds',
            'title' => '_bx_sounds',
            'table' => 'RayMp3Files'
        );

        $this->aCurrent = array_merge($aMain, $this->aCurrent);
        $this->aCurrent['ownFields'][] = 'Listens';
        $this->aCurrent['rss']['title'] = _t('_bx_sounds');

        $this->aAddPartsConfig['favorite']['table'] = 'bx_sounds_favorites';
        $this->oModule = BxDolModule::getInstance('BxSoundsModule');
        $this->oTemplate = &$this->oModule->_oTemplate;
        $this->aConstants['filesUrl'] = $this->oModule->_oConfig->getFilesUrl();
        $this->aConstants['filesDir'] = $this->oModule->_oConfig->getFilesPath();
        $this->aConstants['filesInAlbumCover'] = 12;
        $this->aConstants['picPostfix'] = $this->oModule->_oConfig->aFilePostfix;

        $this->aCurrent['restriction']['albumType']['value'] = $this->aCurrent['name'];

        switch ($sParamName) {
            case 'calendar':
                $this->aCurrent['restriction']['calendar-min'] = array('value' => "UNIX_TIMESTAMP('{$sParamValue}-{$sParamValue1}-{$sParamValue2} 00:00:00')", 'field' => 'Date', 'operator' => '>=', 'no_quote_value' => true);
                $this->aCurrent['restriction']['calendar-max'] = array('value' => "UNIX_TIMESTAMP('{$sParamValue}-{$sParamValue1}-{$sParamValue2} 23:59:59')", 'field' => 'Date', 'operator' => '<=', 'no_quote_value' => true);
                $this->aCurrent['title'] = _t('_bx_sounds_caption_browse_by_day') . sprintf("%04u-%02u-%02u", $sParamValue, $sParamValue1, $sParamValue2);
                break;
            case 'top':
                $this->aCurrent['sorting'] = 'top';
                break;
            case 'popular':
                $this->aCurrent['sorting'] = 'popular';
                break;
            case 'featured':
                $this->aCurrent['restriction']['featured'] = array(
                    'value'=>'1', 'field'=>'Featured', 'operator'=>'=', 'paramName'=>'bx_sounds_mode'
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
                if ($sParamValue1 == 'owner' && strlen($sParamValue2) > 0) {
                    $this->aCurrent['restriction']['owner'] = array(
                        'value'=>$sParamValue2, 'field'=>'NickName', 'operator'=>'=', 'paramName'=>'ownerName', 'table' => 'Profiles'
                    );
                }
                break;
        }
    }

    function _getPseud ()
    {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'date' => 'Date',
            'size' => 'Time',
            'uri' => 'Uri',
            'ownerId' => 'Owner',
            'ownerName' => 'NickName',
            'view' => 'Listens',
            'voteTime' => 'gal_date'
        );
    }

    function getImgUrl ($iId, $sImgType = 'browse')
    {
    	$sImgUrl = parent::getImgUrl($iId, $sImgType);
		if(empty($sImgUrl))
			$sImgUrl = $this->aConstants['filesUrl'] . 'default.png';

        return $sImgUrl;
    }

    function getImgPath ($iId, $sType = 'browse')
    {
        $iId = (int)$iId;
        if (!isset($this->aConstants['picPostfix'][$sType]))
            $sType = 'browse';

        $s = $this->aConstants['filesDir'] . $iId . $this->aConstants['picPostfix'][$sType];
        if (!file_exists($s) && isset($this->oModule->_oConfig->aFilePostfix[$sType]['fallback']))
            $s = $this->aConstants['filesDir'] . $this->oModule->_oConfig->aFilePostfix[$sType]['fallback'];
        
        return $s;
    }

    function serviceGetFileUrl ($iId, $sImgType = 'browse')
    {
        return $this->getImgUrl($iId, $sImgType);
    }

    function serviceGetSoundConcept ($aSound)
    {
        return $this->oTemplate->getFileConcept($aSound['ID'], $aSound);
    }

    function serviceGetEntry($iId, $sType)
    {
        $iId = (int)$iId;
        $sqlQuery = "SELECT
                a.`ID` as `id`,
                a.`Title` as `title`,
                a.`Description` as `description`,
                a.`Uri` as `uri`,
                a.`Owner` as `owner`,
                a.`Date` as `date`,
                a.`Rate` AS `rate`,
                a.`RateCount` AS `rate_count`,
                a.`CommentsCount` AS `comments_count`,
                a.`Listens` AS `views_count`,
                a.`Status` AS `status`,
                b.`id_album` as `album_id`
            FROM `RayMp3Files` as a
            LEFT JOIN `sys_albums_objects` as b ON b.`id_object` = a.`ID`
            LEFT JOIN `sys_albums` as c ON c.`ID`=b.`id_album`
            WHERE a.`ID`='$iId' AND c.`Type`='bx_sounds'";
        $aImageInfo = db_arr($sqlQuery);
        if(empty($aImageInfo) || !is_array($aImageInfo))
            return array();

        $sFileName = $this->getImgUrl($iId, $sType);
        $sFilePath = $this->getImgPath($iId, $sType);
        $sUrl = $this->getCurrentUrl('file', $iId, $aImageInfo['uri']);
        return array(
            'id' => $aImageInfo['id'],
            'file' => $sFileName,
            'file_path' => $sFilePath,
            'title' => $aImageInfo['title'],
            'owner' => $aImageInfo['owner'],
            'description' => $aImageInfo['description'],
            'width' => (int)$this->oModule->_oConfig->getGlParam($sType . '_width') + 2 * 2,
            'height' => (int)$this->oModule->_oConfig->getGlParam($sType . '_height') + 2 * 2,
            'url' => $sUrl,
            'date' => $aImageInfo['date'],
            'rate' => $aImageInfo['rate'],
            'rate_count' => $aImageInfo['rate_count'],
            'comments_count' => $aImageInfo['comments_count'],
            'views_count' => $aImageInfo['views_count'],
            'status' => $aImageInfo['status'],
            'album_id' => $aImageInfo['album_id']
        );
    }

    function serviceGetItemArray($iId, $sType = 'browse')
    {
        return $this->serviceGetEntry($iId, $sType);
    }

	function serviceGetSoundArray($iId, $sType)
    {
        return $this->serviceGetEntry($iId, $sType);
    }

    function serviceGetMusicArray($iId, $sType)
    {
        return $this->serviceGetEntry($iId, $sType);
    }

    function serviceGetFilesInCat ($iId, $sCategory = '')
    {
        $aFiles = $this->getFilesInCatArray($iId, $sCategory);
        foreach ($aFiles as $k => $aRow) {
            $aFiles[$k]['thumb'] = $this->getImgUrl($aRow['id'], 'browse');
            $aFiles[$k]['file'] = $this->getImgUrl($aRow['id'], 'file');
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
            $aFiles[$k]['thumb'] = $this->getImgUrl($aRow['id'], 'browse');
            $aFiles[$k]['file'] = $this->getImgUrl($aRow['id'], 'file');
        }
        return $aFiles;
    }

    function serviceGetAllProfileSounds ($iProfId, $aLimits = array())
    {
        $aFiles = $this->getProfileFiles($iProfId, $aLimits);
        foreach ($aFiles as $k => $aRow) {
            $aFiles[$k]['thumb'] = $this->getImgUrl($aRow['id'], 'browse');
            $aFiles[$k]['file'] = $this->getImgUrl($aRow['id'], 'file');
        }
        return $aFiles;
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPost($aEvent)
    {
        return $this->oModule->getWallPost($aEvent, 'music');
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostOutline($aEvent)
    {
        return $this->oModule->getWallPostOutline($aEvent, 'music');
    }

    function serviceProfileSoundBlock($iProfileId)
    {
        if(!$this->checkMemAction($iProfileId, 'view'))
            return '';
        $aVars = array (
            'title' => false,
            'prefix' => 'id' . time() . '_' . rand(1, 999999),
            'default_height' => 350,
            'bx_repeat:sounds' => array (),
            'bx_repeat:icons' => array (),
        );

        $aFiles = $this->serviceGetProfileAlbumFiles($iProfileId);
        foreach($aFiles as $aFile) {
            $aVars['bx_repeat:sounds'][] = array (
                'style' => false === $aVars['title'] ? '' : 'display:none;',
                'id' => $aFile['id'],
                'sound' => $this->oTemplate->getFileConcept($aFile['id']),
            );
            $aVars['bx_repeat:icons'][] = array (
                'id' => $aFile['id'],
                'icon_url' => $aFile['file'],
                'title' => $aFile['title'],
            );
            if (false === $aVars['title'])
                $aVars['title'] = $aFile['title'];
        }

        if (!$aVars['bx_repeat:icons'])
            return '';

        $this->oTemplate->addCss('entry_view.css');
        return $this->oTemplate->parseHtmlByName('entry_view_block_sounds.html', $aVars);
    }

    function getAlterOrder()
    {
        $aSql = array();
        switch ($this->aCurrent['sorting']) {
            case 'popular':
                $aSql['order'] = " ORDER BY `Listens` DESC";
                break;

            case 'album_order':
                $aSql['order'] = " ORDER BY `obj_order` ASC, `id_object` DESC";
                break;

            default:
                $aSql = parent::getAlterOrder();
        }
        return $aSql;
    }
}
