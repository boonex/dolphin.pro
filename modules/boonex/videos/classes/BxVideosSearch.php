<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplSearchResultSharedMedia');

class BxVideosSearch extends BxTemplSearchResultSharedMedia
{
    function __construct ($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '')
    {
        parent::__construct('BxVideosModule');
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
            'name' => 'bx_videos',
            'title' => '_bx_videos',
            'table' => 'RayVideoFiles'
        );
        $this->aCurrent = array_merge($aMain, $this->aCurrent);
        $this->aCurrent['ownFields'] = array_merge($this->aCurrent['ownFields'], array('Views', 'Source', 'Video'));
        $this->aCurrent['rss']['title'] = _t('_bx_videos');

        $this->aAddPartsConfig['favorite']['table'] = 'bx_videos_favorites';
        $this->oModule = BxDolModule::getInstance('BxVideosModule');
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
                $this->aCurrent['title'] = _t('_bx_videos_caption_browse_by_day') . sprintf("%04u-%02u-%02u", $sParamValue, $sParamValue1, $sParamValue2);
                break;
            case 'top':
                $this->aCurrent['sorting'] = 'top';
                break;
            case 'popular':
                $this->aCurrent['sorting'] = 'popular';
                break;
            case 'featured':
                $this->aCurrent['restriction']['featured'] = array(
                    'value'=>'1', 'field'=>'Featured', 'operator'=>'=', 'paramName'=>'bx_videos_mode'
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

    function serviceGetFileUrl ($iId, $sImgType = 'browse')
    {
        return $this->getImgUrl($iId, $sImgType);
    }

    function serviceGetVideoConcept ($aVideo)
    {
        return $this->oTemplate->getFileConcept($aVideo['ID'], array('ext'=>$aVideo['video'], 'source'=>$aVideo['source']));
    }

    function serviceGetEntry($iId, $sType = 'browse')
    {
        $iId = (int)$iId;
        $sqlQuery = "SELECT a.`ID` as `id`,
                            a.`Title` as `title`,
                            a.`Description` as `description`,
                            a.`Uri` as `uri`,
                            a.`Owner` as `owner`,
                            a.`Date` as `date`,
                            a.`Video`,
                            a.`Source`,
                            a.`Time`,
                            a.`Rate` AS `rate`,
                            a.`RateCount` AS `rate_count`,
                            a.`CommentsCount` AS `comments_count`,
                            a.`Views` AS `views_count`,
                            a.`Status` AS `status`,
                            b.`id_album` as `album_id`
                        FROM `RayVideoFiles` as a
                        LEFT JOIN `sys_albums_objects` as b ON b.`id_object` = a.`ID`
                        LEFT JOIN `sys_albums` as c ON c.`ID`=b.`id_album`
                        WHERE a.`ID`='$iId' AND c.`Type`='bx_videos'";
        $aImageInfo = db_arr($sqlQuery);

        if(empty($aImageInfo) || !is_array($aImageInfo))
            return array();

        $sFileName = $sFilePath = '';
        $sImg = $aImageInfo['id'] . $this->aConstants['picPostfix'][$sType];
        if($sImg != '' && extFileExists($this->aConstants['filesDir'] . $sImg)) {
            $sFileName = $this->aConstants['filesUrl'] . $sImg;
            $sFilePath = $this->aConstants['filesDir'] . $sImg;
        }

        return array(
            'id' => $aImageInfo['id'],
            'file' => $sFileName,
            'file_path' => $sFilePath,
            'title' => $aImageInfo['title'],
            'owner' => $aImageInfo['owner'],
            'description' => $aImageInfo['description'],
            'width' => (int)$this->oModule->_oConfig->aFilesConfig['browse']['w'] + 2 * 2,
            'height' => (int)$this->oModule->_oConfig->aFilesConfig['browse']['h'] + 2 * 2,
            'url' => $this->getCurrentUrl('file', $iId, $aImageInfo['uri']),
            'video' => $aImageInfo['Video'],
            'source' => $aImageInfo['Source'],
            'duration' => $aImageInfo['Time'],
            'duration_f' => _format_time(round(($aImageInfo['Time'])/1000)),
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

    function serviceGetVideoArray ($iId, $sType = 'browse')
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

        $this->aCurrent['ownFields'][] = 'Video';
        $this->aCurrent['ownFields'][] = 'Source';
        $aFiles = $this->getFilesInAlbumArray($iAlbumId, $aLimits);
        foreach ($aFiles as $k => $aRow) {
            $aFiles[$k]['thumb'] = $this->getImgUrl($aRow['id'], 'browse');
            $aFiles[$k]['file'] = $this->getImgUrl($aRow['id'], 'file');
            $aFiles[$k]['main'] = $this->getImgUrl($aRow['id'], 'main');
            $aFiles[$k]['video'] = $aFiles[$k]['Video'];
            $aFiles[$k]['source'] = $aFiles[$k]['Source'];
        }
        return $aFiles;
    }

    function serviceGetAllProfileVideos ($iProfId, $aLimits = array())
    {
        $aFiles = $this->getProfileFiles($iProfId, $aLimits);
        foreach ($aFiles as $k => $aRow) {
            $aFiles[$k]['thumb'] = $this->getImgUrl($aRow['id'], 'browse');
            $aFiles[$k]['file'] = $this->getImgUrl($aRow['id'], 'file');
            $aFiles[$k]['main'] = $this->getImgUrl($aRow['id'], 'main');
            $aFiles[$k]['video'] = $aFiles[$k]['Video'];
            $aFiles[$k]['source'] = $aFiles[$k]['Source'];
        }
        return $aFiles;
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPost($aEvent)
    {
        return $this->oModule->getWallPost($aEvent, 'film');
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostOutline($aEvent)
    {
        return $this->oModule->getWallPostOutline($aEvent, 'film');
    }

    function serviceProfileVideoBlock($iProfileId)
    {
        if(!$this->checkMemAction($iProfileId, 'view'))
            return '';
        $aVars = array (
            'title' => false,
            'prefix' => 'id' . time() . '_' . rand(1, 999999),
            'default_height' => getSettingValue('video', 'player_height'),
            'bx_repeat:videos' => array (),
            'bx_repeat:icons' => array (),
        );

        $aFiles = $this->serviceGetProfileAlbumFiles($iProfileId);
        foreach($aFiles as $aFile) {
            $aVars['bx_repeat:videos'][] = array (
                'style' => false === $aVars['title'] ? '' : 'display:none;',
                'id' => $aFile['id'],
                'video' => $this->oTemplate->getFileConcept($aFile['id'], ($aFile['source'] == 'youtube' ? array('ext' => $aFile['video']) : array())),
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
        return $this->oTemplate->parseHtmlByName('entry_view_block_videos.html', $aVars);
    }
    function getSearchUnitShort($aData)
    {
        $sContent = parent::getSearchUnitShort($aData);
        return $this->oTemplate->parseHtmlByContent($sContent, array(
            'size' => _format_time($aData['size'] / 1000)
        ));
    }
    function getSearchUnit($aData)
    {
        $sContent = parent::getSearchUnit($aData);
        return $this->oTemplate->parseHtmlByContent($sContent, array(
            'size' => _format_time($aData['size'] / 1000)
        ));
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
            'view' => 'Views',
            'voteTime' => 'gal_date',
            'source' => 'Source',
            'video' => 'Video'
        );
    }
}
