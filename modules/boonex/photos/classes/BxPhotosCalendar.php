<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesCalendar');

class BxPhotosCalendar extends BxDolFilesCalendar
{
    function __construct ($iYear, $iMonth, &$oDb, &$oTemplate, &$oConfig)
    {
        parent::__construct($iYear, $iMonth, $oDb, $oTemplate, $oConfig);
    }
}
