<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectDb');

class BxChatPlusDb extends BxDolConnectDb
{
    /**
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);
    }
}
