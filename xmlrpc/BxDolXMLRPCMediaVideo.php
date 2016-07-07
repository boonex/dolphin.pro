<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolXMLRPCMediaVideo extends BxDolXMLRPCMedia
{

    function removeVideo5 ($sUser, $sPwd, $iFileId)
    {
        if (!($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        if (BxDolService::call('videos', 'remove_object', array((int)$iFileId)))
            return new xmlrpcval ("ok");
        return new xmlrpcval ("fail");
    }

    function getVideoAlbums ($sUser, $sPwd, $sNick)
    {
        $iIdProfile = BxDolXMLRPCUtil::getIdByNickname ($sNick);
        if (!$iIdProfile || !($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        // create user's default album if there is no one
        if ($sUser == $sNick) {
            $sCaption = str_replace('{nickname}', $sUser, getParam('bx_videos_profile_album_name'));
            bx_import('BxDolAlbums');
            $oAlbum = new BxDolAlbums('bx_videos');
            $aData = array(
                'caption' => $sCaption,
                'location' => _t('_bx_videos_undefined'),
                'owner' => $iId,
                'AllowAlbumView' => BX_DOL_PG_ALL,
            );
            $oAlbum->addAlbum($aData);
        }

        return BxDolXMLRPCMedia::_getMediaAlbums ('video', $iIdProfile, $iId, true);
    }

    function uploadVideo5 ($sUser, $sPwd, $sAlbum, $binImageData, $iDataLength, $sTitle, $sTags, $sDesc, $sExt)
    {
        return BxDolXMLRPCMedia::_uploadFile ('video', $sUser, $sPwd, $sAlbum, $binImageData, $iDataLength, $sTitle, $sTags, $sDesc, $sExt);
    }

    function getVideoInAlbum($sUser, $sPwd, $sNick, $iAlbumId)
    {
        $iIdProfile = BxDolXMLRPCUtil::getIdByNickname ($sNick);
        if (!$iIdProfile || !($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        return BxDolXMLRPCMedia::_getFilesInAlbum ('videos', $iIdProfile, $iId, $iAlbumId, 'video', 'getToken', 'flash/modules/video/get_mobile.php?id=');
    }    
}
