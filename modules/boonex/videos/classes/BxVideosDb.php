<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolFilesDb.php');

class BxVideosDb extends BxDolFilesDb
{
    /*
     * Constructor.
     */
    function BxVideosDb (&$oConfig)
    {
        parent::BxDolFilesDb($oConfig);
        $this->aFileFields['medExt'] = 'Video';
        $this->aFileFields['medSource'] = 'Source';
        $this->sFileTable = 'RayVideoFiles';
        $this->sFavoriteTable = 'bx_videos_favorites';
    }

    function getSettingsCategory ()
    {
        return (int)$this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Videos' LIMIT 1");
    }
}
