<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolXMLRPCMedia
{
    // ----------------- albums list

    function _getMediaAlbums ($sType, $iIdProfile, $iIdProfileViewer, $isShowEmptyAlbums = false)
    {
        $aAlbums = (new BxDolXMLRPCMedia)->_getMediaAlbumsArray ($sType, $iIdProfile, $iIdProfileViewer, $isShowEmptyAlbums);

        $aXmlRpc = array ();

        foreach ($aAlbums as $r) {
            $a = array (
                'Id' => new xmlrpcval($r['Id']),
                'Title' => new xmlrpcval($r['Title']),
                'Num' => new xmlrpcval($r['Num']),
                'DefaultAlbum' => new xmlrpcval($r['DefaultAlbum']),
            );
            $aXmlRpc[] = new xmlrpcval($a, 'struct');
        }

        return new xmlrpcval ($aXmlRpc, "array");
    }

    function _getMediaCount ($sType, $iIdProfile, $iIdProfileViewer)
    {
        $a = (new BxDolXMLRPCMedia)->_getMediaAlbumsArray ($sType, $iIdProfile, $iIdProfileViewer);
        $iNum = 0;
        foreach ($a as $r)
            $iNum += $r['Num'];
        return $iNum;
    }

    function _getMediaAlbumsArray ($sType, $iIdProfile, $iIdProfileViewer, $isShowEmptyAlbums = false)
    {
        switch ($sType) {
            case 'photo':
                $sModuleName = 'photos';
                $sType = 'bx_photos';
                $sMemAction = 'BX_PHOTOS_VIEW';
                break;
            case 'video':
                $sModuleName = 'videos';
                $sType = 'bx_videos';
                $sMemAction = 'BX_VIDEOS_VIEW';
                break;
            case 'music':
                $sModuleName = 'sounds';
                $sType = 'bx_sounds';
                $sMemAction = 'BX_SOUNDS_VIEW';
                break;
            default:
                return array();
        }

        if (!BxDolXMLRPCMedia::_isMembershipEnabledFor($iIdProfileViewer, $sMemAction))
            return array ();

        bx_import('BxDolMemberInfo');
        $oMemberInfo = BxDolMemberInfo::getObjectInstance(getParam('sys_member_info_thumb'));
        $isSetAvatarFromDefaultAlbumOnly = $oMemberInfo->isSetAvatarFromDefaultAlbumOnly();

        bx_import('BxDolAlbums');
        $o = new BxDolAlbums ($sType, (int)$iIdProfile);
        $aList = $o->getAlbumList (array('owner' => (int)$iIdProfile, 'show_empty' => $isShowEmptyAlbums), 1, 1000);
        $aRet = array ();
        foreach ($aList as $r) {
            if (!BxDolService::call ($sModuleName, 'get_album_privacy', array((int)$r['ID'], $iIdProfileViewer), 'Search'))
                continue;
            
            if ($isSetAvatarFromDefaultAlbumOnly) {
            	bx_import('BxDolAlbums');
                $isDefaulAlbum = $r['Uri'] == BxDolAlbums::getAbumUri(getParam($sType . '_profile_album_name'), $iIdProfile) ? 1 : 0;
            }
            else
                $isDefaulAlbum = 1;

            $aRet[] = array (
                'Id' => $r['ID'],
                'Title' => $r['Caption'],
                'Num' => $r['ObjCount'],
                'DefaultAlbum' => $isDefaulAlbum,
            );
        }
        return $aRet;
    }

    // ----------------- file list in albums

    function _getFilesInAlbum ($sModuleName, $iIdProfile, $iIdProfileViewer, $iAlbumId, $sWidget = '', $sFuncToken = '', $sTokenUrl = '')
    {
        if ($sWidget && preg_match('/^[a-zA-Z0-9_]+$/', $sWidget)) {
            require_once (BX_DIRECTORY_PATH_ROOT . "flash/modules/global/inc/db.inc.php");
            require_once (BX_DIRECTORY_PATH_ROOT . "flash/modules/{$sWidget}/inc/header.inc.php");
            require_once (BX_DIRECTORY_PATH_ROOT . "flash/modules/{$sWidget}/inc/constants.inc.php");
            require_once (BX_DIRECTORY_PATH_ROOT . "flash/modules/{$sWidget}/inc/functions.inc.php");
        }

        $a = BxDolService::call ($sModuleName, 'get_files_in_album', array((int)$iAlbumId, $iIdProfileViewer != $iIdProfile, $iIdProfileViewer, array('per_page' => 100)), 'Search');
        if (!$a)
            return new xmlrpcval (array(), "array");
        foreach ($a as $k => $aRow) {
            if ('youtube' == $aRow['Source']) {
                $sUrl = $aRow['Video'];
            } else {
                $sToken = '';
                if ($sFuncToken)
                    $sToken = $sFuncToken($aRow['id']);

                $sUrl = $sTokenUrl && $sToken ? BX_DOL_URL_ROOT . $sTokenUrl . $aRow['id'] . '&token=' . $sToken : $aRow['file'];
            }

            $a = array (
                'id' => new xmlrpcval($aRow['id']),
                'title' => new xmlrpcval($aRow['title']),
                'desc' => new xmlrpcval(BxDolService::call ($sModuleName, 'get_length', array($aRow['size']), 'Search')),
                'icon' => new xmlrpcval($aRow['icon']),
                'thumb' => new xmlrpcval($aRow['thumb']),
                'file' => new xmlrpcval($sUrl),
                'cat' => new xmlrpcval($sCat),
                'rate' => new xmlrpcval($aRow['Rate']),
                'rate_count' => new xmlrpcval((int)$aRow['RateCount']),
            );
            $aFiles[] = new xmlrpcval($a, 'struct');
        }
        return new xmlrpcval ($aFiles, "array");
    }

    function _isMembershipEnabledFor ($iProfileId, $sMembershipActionConstant, $isPerformAction = false)
    {
        defineMembershipActions (array('photos add', 'photos view', 'sounds view', 'sounds add', 'videos view', 'videos add'));
        if (!defined($sMembershipActionConstant))
            return false;
        $aCheck = checkAction($iProfileId ? $iProfileId : (int)$_COOKIE['memberID'], constant($sMembershipActionConstant), $isPerformAction);
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    // ----------------- upload


    function _uploadFile ($sType, $sUser, $sPwd, $sAlbum, $binData, $iDataLength, $sTitle, $sTags, $sDesc, $sExt)
    {
        $sFieldTitle = 'title';
        $sFieldDesc = 'desc';
        $sFieldTags = 'tags';
        $sFieldCats = 'categories';
        $sFieldAlbum = 'album';
        switch ($sType) {
            case 'photo':
                $sModuleName = 'photos';
                $sService = 'perform_photo_upload';
                $sMemAction = 'BX_PHOTOS_ADD';
                $sFieldTitle = 'medTitle';
                $sFieldDesc = 'medDesc';
                $sFieldTags = 'medTags';
                $sFieldCats = 'Categories';
                $sFieldAlbum = 'album';
                $sModuleUnit = 'bx_photos';
                break;
            case 'video':
                $sModuleName = 'videos';
                $sService = 'perform_video_upload';
                $sMemAction = 'BX_VIDEOS_ADD';
                $sModuleUnit = 'bx_videos';
                break;
            case 'music':
                $sModuleName = 'sounds';
                $sService = 'perform_sound_upload';
                $sMemAction = 'BX_SOUNDS_ADD';
                $sModuleUnit = 'bx_sounds';
                break;
            default:
                return array();
        }

        if (!($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        if (!BxDolXMLRPCMedia::_isMembershipEnabledFor($iId, $sMemAction, true))
            return new xmlrpcval ("fail access");

        if (!BxDolService::call($sModuleName, 'is_ext_allowed', array($sExt), 'Uploader'))
            return new xmlrpcval ("fail wrong extension - " . $sExt);

        // write tmp file

        $sTmpFilename = BX_DIRECTORY_PATH_ROOT . "tmp/" . time() . $sType . $iId . '.' . $sExt;
        $f = fopen($sTmpFilename, "wb");
        if (!$f)
            return new xmlrpcval ("fail fopen");
        if (!fwrite ($f, $binData, (int)$iDataLength)) {
            fclose($f);
            return new xmlrpcval ("fail write");
        }
        fclose($f);

        // upload
        $aFileInfo = array();
        $aFileInfo[$sFieldTitle] = $sTitle;
        $aFileInfo[$sFieldDesc] = $sDesc;
        $aFileInfo[$sFieldTags] = $sTags;
        $aFileInfo[$sFieldCats] = 'photo' == $sType ? array ($sAlbum) : $sAlbum;
        $aFileInfo[$sFieldAlbum] = $sAlbum;

        if ('photo' == $sType && BxDolService::call($sModuleName, $sService, array($sTmpFilename, $aFileInfo, 0, $iId), 'Uploader')) {
            return new xmlrpcval ("ok");
        } elseif ('photo' != $sType && ($iFileId = BxDolService::call($sModuleName, $sService, array($sTmpFilename, $aFileInfo, true), 'Uploader'))) {
            $oZ = new BxDolAlerts($sModuleUnit, 'add', $iFileId , $iId);
            $oZ->alert();
            return new xmlrpcval ("ok");
        } else {
            return new xmlrpcval ("fail upload");
        }
    }    

}
