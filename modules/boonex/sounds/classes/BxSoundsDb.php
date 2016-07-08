<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolFilesDb.php');

class BxSoundsDb extends BxDolFilesDb
{
    /*
     * Constructor.
     */
    function __construct (&$oConfig)
    {
        parent::__construct($oConfig);
        $this->aFileFields['medViews'] = 'Listens';
        $this->sFileTable = 'RayMp3Files';
        $this->sFavoriteTable = 'bx_sounds_favorites';
    }

    function getSettingsCategory ()
    {
        return (int)$this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Sounds' LIMIT 1");
    }
}
