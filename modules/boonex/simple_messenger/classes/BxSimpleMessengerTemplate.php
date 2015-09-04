<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolModuleTemplate');

    class BxSimpleMessengerTemplate extends BxDolModuleTemplate
    {
        /**
         * Class constructor
         */
        function BxSimpleMessengerTemplate( &$oConfig, &$oDb )
        {
            parent::BxDolModuleTemplate($oConfig, $oDb);
        }
    }
