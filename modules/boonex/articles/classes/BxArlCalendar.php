<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextCalendar');

class BxArlCalendar extends BxDolTextCalendar
{
    function BxArlCalendar($iYear, $iMonth, &$oDb, &$oConfig)
    {
        parent::BxDolTextCalendar($iYear, $iMonth, $oDb, $oConfig);

        $this->sCssPrefix = 'arl';
    }
}
