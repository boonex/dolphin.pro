<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolFilesDb.php');

class BxPhotosDb extends BxDolFilesDb
{
    /*
     * Constructor.
     */
    function __construct (&$oConfig)
    {
        parent::__construct($oConfig);
        $this->sFileTable = 'bx_photos_main';
        $this->sFavoriteTable = 'bx_photos_favorites';
        $this->aFileFields['medDesc'] = 'Desc';
        $this->aFileFields['medExt']  = 'Ext';
        $this->aFileFields['medSize'] = 'Size';
        $this->aFileFields['Hash'] = 'Hash';
    }

    function getSettingsCategory ()
    {
        return (int)$this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Photos' LIMIT 1");
    }

    function getIdByHash ($sHash)
    {
        $sHash = process_db_input($sHash, BX_TAGS_STRIP);
        return (int)$this->fromMemory('bx_photos_' . $sHash, 'getOne', "
        SELECT `{$this->aFileFields['medID']}`
        FROM `{$this->sFileTable}`
        WHERE `{$this->aFileFields['Hash']}` = '$sHash'");
    }

    function setAvatar($iFileId, $iAlbumId)
    {
        $this->query("UPDATE `sys_albums_objects` SET `obj_order` = `obj_order` + 1 WHERE `id_album` = " . (int)$iAlbumId);
        return $this->query("UPDATE `sys_albums_objects` SET `obj_order` = 0 WHERE `id_object` = " . (int)$iFileId . " AND `id_album` = " . (int)$iAlbumId);
    }
}
