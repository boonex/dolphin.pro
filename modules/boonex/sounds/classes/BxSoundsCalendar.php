<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesCalendar');

class BxSoundsCalendar extends BxDolFilesCalendar
{
    function BxSoundsCalendar ($iYear, $iMonth, &$oDb, &$oTemplate, &$oConfig)
    {
        parent::BxDolFilesCalendar($iYear, $iMonth, $oDb, $oTemplate, $oConfig);
    }
}
