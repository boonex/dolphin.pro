<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');

class BxWallPrivacy extends BxDolPrivacy
{
    function __construct(&$oModule)
    {
        parent::__construct('Profiles', 'ID', 'ID');
    }
}
