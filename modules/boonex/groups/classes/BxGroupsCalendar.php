<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolTwigCalendar');

class BxGroupsCalendar extends BxDolTwigCalendar
{
    function __construct ($iYear, $iMonth, &$oDb, &$oConfig, &$oTemplate)
    {
        parent::__construct($iYear, $iMonth, $oDb, $oConfig);
    }

    function getEntriesNames ()
    {
        return array(_t('_bx_groups_group_single'), _t('_bx_groups_group_plural'));
    }
}
