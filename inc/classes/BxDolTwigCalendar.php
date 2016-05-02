<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxTemplCalendar');

/**
 * Base calendar class for modules like events/groups/store
 */
class BxDolTwigCalendar extends BxTemplCalendar
{
    var $oDb, $oConfig;

    function __construct ($iYear, $iMonth, &$oDb, &$oConfig)
    {
        parent::__construct($iYear, $iMonth);
        $this->oDb = &$oDb;
        $this->oConfig = &$oConfig;
    }

    /**
     * return records for current month, there is mandatory field `Day` - a day for current row
     * use the following class variables to pass to your database query
     * $this->iYear, $this->iMonth, $this->iNextYear, $this->iNextMonth
     *
     * for example:
     *
     * return $db->getAll ("
     *  SELECT *, DAYOFMONTH(FROM_UNIXTIME(`EventStart`)) AS `Day`
     *  FROM `my_table`
     *  WHERE `Date` >= UNIX_TIMESTAMP('{$this->iYear}-{$this->iMonth}-1') AND `Date` < UNIX_TIMESTAMP('{$this->iNextYear}-{$this->iNextMonth}-1') AND `Status` = 'approved'");
     *
     */
    function getData ()
    {
        return $this->oDb->getEntriesByMonth ($this->iYear, $this->iMonth, $this->iNextYear, $this->iNextMonth);
    }

    /**
     * return base calendar url
     * year and month will be added to this url automatically
     * so if your base url is /m/some_module/calendar/, it will be transormed to
     * /m/some_module/calendar/YEAR/MONTH, like /m/some_module/calendar/2009/3
     */
    function getBaseUri ()
    {
        return BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . "calendar/";
    }

    function getBrowseUri ()
    {
        return BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . "browse/calendar/";
    }

    function getEntriesNames ()
    {
        // override this
    }

    function getMonthUrl ($isNextMoths, $isMiniMode = false)
    {
        if ($isMiniMode && $this->iBlockID && $this->sDynamicUrl)
            return "javascript:loadDynamicBlock('" . $this->iBlockID . "', '" . bx_append_url_params($this->sDynamicUrl, 'date=' . ($isNextMoths ? "{$this->iNextYear}/{$this->iNextMonth}" : "{$this->iPrevYear}/{$this->iPrevMonth}")) . "');";
        else
            return parent::getMonthUrl ($isNextMoths, $isMiniMode);
    }

    function setBlockId($iBlockID)
    {
        $this->iBlockID = $iBlockID;
    }

    function setDynamicUrl($s)
    {
        $this->sDynamicUrl = $s;
    }
}
