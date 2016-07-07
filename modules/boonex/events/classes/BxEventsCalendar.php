<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolTwigCalendar');

class BxEventsCalendar extends BxDolTwigCalendar
{
    var $oTemplate;

    function __construct ($iYear, $iMonth, &$oDb, &$oConfig, &$oTemplate)
    {
        parent::__construct($iYear, $iMonth, $oDb, $oConfig);
        $this->oTemplate = &$oTemplate;
    }

    function getEntriesNames ()
    {
        return array(_t('_bx_events_single'), _t('_bx_events_plural'));
    }

}
