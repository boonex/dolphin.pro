<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxSimpleMessengerExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_simple_messenger_messages' => '`SenderID` = {profile_id} OR `RecipientID` = {profile_id}',
            'bx_simple_messenger_privacy' => '`author_id` = {profile_id}',
        );
    }
}
