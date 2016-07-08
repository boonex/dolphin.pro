<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

    class BxShoutBoxConfig extends BxDolConfig
    {
        // contain Db table's name ;
        var $sTablePrefix;
        var $iLifeTime;

        var $iUpdateTime;
        var $iAllowedMessagesCount;

        /**
         * Class constructor;
         */
        function __construct($aModule)
        {
            parent::__construct($aModule);

            // define the tables prefix ;
            $this -> sTablePrefix 			= $this -> getDbPrefix();
            $this -> iLifeTime 				= (int) getParam('shoutbox_clean_oldest'); //in seconds

            $this -> iUpdateTime            = (int) getParam('shoutbox_update_time'); //(in milliseconds)
            $this -> iAllowedMessagesCount  = (int) getParam('shoutbox_allowed_messages');

            $this -> iBlockExpirationSec   = (int) getParam('shoutbox_block_sec'); //in seconds
        }
    }
