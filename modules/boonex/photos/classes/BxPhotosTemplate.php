<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesTemplate');

class BxPhotosTemplate extends BxDolFilesTemplate
{
    function __construct (&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }
}
