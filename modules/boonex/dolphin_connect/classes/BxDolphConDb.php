<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectDb');

class BxDolphConDb extends BxDolConnectDb
{
    /**
     * Constructor.
     */
    function BxDolphConDb(&$oConfig)
    {
        parent::BxDolConnectDb($oConfig);
    }
}
