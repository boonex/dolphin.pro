<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolXMLRPCMediaAudio extends BxDolXMLRPCMedia
{

    function removeAudio5 ($sUser, $sPwd, $iFileId)
    {
        if (!($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        if (BxDolService::call('sounds', 'remove_object', array((int)$iFileId)))
            return new xmlrpcval ("ok");
        return new xmlrpcval ("fail");
    }

    function getAudioAlbums ($sUser, $sPwd, $sNick)
    {
        $iIdProfile = BxDolXMLRPCUtil::getIdByNickname ($sNick);
        if (!$iIdProfile || !($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        return BxDolXMLRPCMedia::_getMediaAlbums ('music', $iIdProfile, $iId);
    }

    function getAudioInAlbum($sUser, $sPwd, $sNick, $iAlbumId)
    {
        $iIdProfile = BxDolXMLRPCUtil::getIdByNickname ($sNick);
        if (!$iIdProfile || !($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        return BxDolXMLRPCMedia::_getFilesInAlbum ('sounds', $iIdProfile, $iId, $iAlbumId, 'mp3', 'getMp3Token', 'flash/modules/mp3/get_file.php?id=');
    }    
}
