<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolCron');

    require_once('BxSpyModule.php');

    class BxSpyCron extends BxDolCron
    {
        var $oSpyObject;
        var $iDaysForRows;

        /**
         * Class constructor;
         */
        function __construct()
        {
            $this -> oSpyObject = BxDolModule::getInstance('BxSpyModule');
            $this -> iDaysForRows = $this -> oSpyObject -> _oConfig -> iDaysForRows;
        }

        /**
         * Function will delete all unnecessary events;
         */
        function processing()
        {
            if ($this -> iDaysForRows > 0) {
                $this -> oSpyObject -> _oDb -> deleteUselessData($this -> iDaysForRows);
            }
        }
    }
