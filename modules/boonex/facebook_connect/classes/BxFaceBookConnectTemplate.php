<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectTemplate');

class BxFaceBookConnectTemplate extends BxDolConnectTemplate
{
    function BxFaceBookConnectTemplate(&$oConfig, &$oDb)
    {
        parent::BxDolConnectTemplate($oConfig, $oDb);
        $this->_sPageIcon = 'facebook';
    }
}
