<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxSpyExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_spy_data' => '`sender_id` = {profile_id} OR `recipient_id` = {profile_id}',
            'bx_spy_friends_data' => '`sender_id` = {profile_id} OR `friend_id` = {profile_id}',
        );
    }
}
