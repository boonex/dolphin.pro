<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigTemplate');

class BxChatPlusTemplate extends BxDolTwigTemplate
{
    function BxChatPlusTemplate(&$oConfig, &$oDb)
    {
        parent::BxDolModuleTemplate($oConfig, $oDb);
    }

    function pageCodeAdminStart()
    {
        $this->pageStart();
    }
}
