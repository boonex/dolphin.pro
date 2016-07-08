<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

    class BxSpyConfig extends BxDolConfig
    {
        var $_sAlertSystemName;
        var $iPerPage;
        var $iUpdateTime;
        var $iDaysForRows;

        var $iSpeedToggleUp;
        var $iSpeedToggleDown;
        var $iMemberMenuNotifyCount = 5;
        var $bTrackGuestsActivites;

        /**
         * Class constructor;
         */
        function __construct($aModule)
        {
            parent::__construct($aModule);
            $this -> iUpdateTime      = getParam('bx_spy_update_time');
            $this -> iDaysForRows     = getParam('bx_spy_keep_rows_days');
            $this -> iSpeedToggleUp   = getParam('bx_spy_toggle_up');
            $this -> iSpeedToggleDown = getParam('bx_spy_toggle_down');
            $this -> iPerPage         = getParam('bx_spy_per_page');
            $this -> _sAlertSystemName = 'bx_spy_content_activity';
            $this -> bTrackGuestsActivites = getParam('bx_spy_guest_allow') ? true : false;
        }

        function getAlertSystemName()
        {
            return $this -> _sAlertSystemName;
        }
    }
