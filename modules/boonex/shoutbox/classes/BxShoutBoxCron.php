<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolCron');
    require_once('BxShoutBoxModule.php');

    class BxShoutBoxCron extends BxDolCron
    {
        var $oModule;
        var $iLifeTime;

        /**
         * Class constructor;
         */
        function __construct()
        {
            $this -> oModule     = BxDolModule::getInstance('BxShoutBoxModule');
            $this -> iLifeTime   = $this -> oModule -> _oConfig -> iLifeTime;
        }

        /**
         * Function will delete all old data;
         */
        function processing()
        {
            $this -> oModule -> _oDb -> deleteOldMessages($this -> iLifeTime);
        }
    }
