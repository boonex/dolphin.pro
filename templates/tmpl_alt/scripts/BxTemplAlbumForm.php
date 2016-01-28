<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxBaseAlbumForm');

class BxTemplAlbumForm extends BxBaseAlbumForm
{
    function __construct($sType, $iAlbum = 0)
    {
        parent::__construct($sType, $iAlbum);
    }
}
