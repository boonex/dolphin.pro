<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextDb');

class BxNewsDb extends BxDolTextDb
{
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);
    }
}
