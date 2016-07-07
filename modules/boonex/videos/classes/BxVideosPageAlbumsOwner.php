<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolFilesPageAlbumsOwner.php');

class BxVideosPageAlbumsOwner extends BxDolFilesPageAlbumsOwner
{
    function __construct (&$oShared, $aParams = array())
    {
        parent::__construct('bx_videos_albums_owner', $oShared, $aParams);
    }
}
