<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectTemplate');

class BxDolphConTemplate extends BxDolConnectTemplate
{
    function BxDolphConTemplate(&$oConfig, &$oDb)
    {
        parent::BxDolConnectTemplate($oConfig, $oDb);
        $this->_sPageIcon = 'sign-in';
    }
}
