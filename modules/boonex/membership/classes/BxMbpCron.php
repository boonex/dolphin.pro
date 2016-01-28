<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolCron');

class BxMbpCron extends BxDolCron
{
    var $_oModule;

    function __construct()
    {
        parent::__construct();

        $this->_oModule = BxDolModule::getInstance('BxMbpModule');
    }

    function processing()
    {
        $this->_oModule->serviceProlongSubscriptions();
    }
}
