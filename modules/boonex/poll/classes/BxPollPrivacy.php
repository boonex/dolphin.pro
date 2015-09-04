<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolPrivacy');

    class BxPollPrivacy extends BxDolPrivacy
    {
        /**
         * Class constructor;
         */
        function BxPollPrivacy(&$oModule)
        {
            parent::BxDolPrivacy($oModule -> _oDb -> sTablePrefix . 'data', 'id_poll', 'id_profile');
        }
    }
