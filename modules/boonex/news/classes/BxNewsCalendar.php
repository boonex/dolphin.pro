<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextCalendar');

class BxNewsCalendar extends BxDolTextCalendar
{
    function __construct($iYear, $iMonth, &$oDb, &$oConfig)
    {
        parent::__construct($iYear, $iMonth, $oDb, $oConfig);

        $this->sCssPrefix = 'news';
    }
}
