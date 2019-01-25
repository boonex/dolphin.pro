<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxPfwExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_pfw_cart' => '`client_id` = {profile_id}',
            'bx_pfw_transactions' => '`client_id` = {profile_id} OR `seller_id` = {profile_id}',
            'bx_pfw_transactions_pending' => '`client_id` = {profile_id} OR `seller_id` = {profile_id}',
            'bx_pfw_user_values' => '`user_id` = {profile_id}'
        );
    }
}
