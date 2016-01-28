<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxTemplCalendar');

class BxAdsCalendar extends BxTemplCalendar
{
    var $oAdsModule;

    function __construct ($iYear, $iMonth, &$oModule)
    {
        parent::__construct($iYear, $iMonth);
        $this->oAdsModule = &$oModule;
    }

    /**
     * return records for current month, there is mandatory field `Day` - a day for current row
     * use the following class variables to pass to your database query
     * $this->iYear, $this->iMonth, $this->iNextYear, $this->iNextMonth
     *
     * for example:
     *
     * return $db->getAll ("
     *  SELECT *, DAYOFMONTH(FROM_UNIXTIME(`DateTime`)) AS `Day`
     *  FROM `my_table`
     *  WHERE `Date` >= UNIX_TIMESTAMP('{$this->iYear}-{$this->iMonth}-1') AND `Date` < UNIX_TIMESTAMP('{$this->iNextYear}-{$this->iNextMonth}-1') AND `Status` = 'approved'");
     *
     */
    function getData ()
    {
        return $this->oAdsModule->_oDb->getAdsByMonth($this->iYear, $this->iMonth, $this->iNextYear, $this->iNextMonth);
    }

    /**
     * return base calendar url
     * year and month will be added to this url automatically
     * so if your base url is /m/some_module/calendar/, it will be transormed to
     * /m/some_module/calendar/YEAR/MONTH, like /m/some_module/calendar/2009/3
     */
    function getBaseUri ()
    {
        return $this->oAdsModule->_oConfig->sCurrBrowsedFile . "?action=show_calendar&date=";
    }

    function getBrowseUri ()
    {
        return $this->oAdsModule->_oConfig->sCurrBrowsedFile . "?action=show_calendar_ads&date=";
    }

    function getEntriesNames ()
    {
        return array(_t('_bx_ads_Ad'), _t('_bx_ads_Ads'));
    }
}
