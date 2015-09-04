<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolTwigCalendar');

class BxStoreCalendar extends BxDolTwigCalendar
{
    function BxStoreCalendar ($iYear, $iMonth, &$oDb, &$oConfig, &$oTemplate)
    {
        parent::BxDolTwigCalendar($iYear, $iMonth, $oDb, $oConfig);
    }

    function getEntriesNames ()
    {
        return array(_t('_bx_store_products_single'), _t('_bx_store_products_plural'));
    }

}
