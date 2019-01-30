<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxShoutBoxExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_shoutbox_messages' => '`OwnerID` = {profile_id}',
        );
    }
}
