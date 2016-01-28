<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxTemplCalendar');

class BxBlogsCalendar extends BxTemplCalendar
{
    var $sDynamicUrl = '';
    var $iBlockID = 0;
    var $oBlogsModule;

    function __construct ($iYear, $iMonth, &$oModule)
    {
        parent::__construct($iYear, $iMonth);
        $this->oBlogsModule = &$oModule;
    }

    /**
     * return records for current month, there is mandatory field `Day` - a day for current row
     * use the following class variables to pass to your database query
     * $this->iYear, $this->iMonth, $this->iNextYear, $this->iNextMonth
     *
     * for example:
     *
     * return $db->getAll ("
     *  SELECT *, DAYOFMONTH(FROM_UNIXTIME(`Blogstart`)) AS `Day`
     *  FROM `my_table`
     *  WHERE `Date` >= UNIX_TIMESTAMP('{$this->iYear}-{$this->iMonth}-1') AND `Date` < UNIX_TIMESTAMP('{$this->iNextYear}-{$this->iNextMonth}-1') AND `Status` = 'approved'");
     *
     */
    function getData ()
    {
        $sStatus = 'approval';
        if($this -> oBlogsModule -> isAllowedApprove()
            || $this -> oBlogsModule -> isAllowedPostEdit(-1)
            || $this -> oBlogsModule -> isAllowedPostDelete(-1) ) {

            $sStatus = '';
        }

        return $this->oBlogsModule->_oDb->getBlogPostsByMonth($this->iYear
            , $this->iMonth, $this->iNextYear, $this->iNextMonth, $sStatus);
    }

    /**
     * return html for data unit for some day, it is:
     * - icon 32x32 with link if data have associated image, use $GLOBALS['oFunctions']->sysIcon() to return small icon
     * - data title with link if data have no associated image
     */
    function getUnit(&$aData)
    {
        $iPostID = (int)$aData['PostID'];
        $sPostUri = $aData['PostUri'];
        $sName = $aData['PostCaption'];
        $sUrl = $this->oBlogsModule->genUrl($iPostID, $sPostUri, 'entry');

        return <<<EOF
<div style="width:95%;">
    <a title="{$sName}" href="{$sUrl}">{$sName}</a>
</div>
EOF;
    }

    /**
     * return base calendar url
     * year and month will be added to this url automatically
     * so if your base url is /m/some_module/calendar/, it will be transormed to
     * /m/some_module/calendar/YEAR/MONTH, like /m/some_module/calendar/2009/3
     */
    function getBaseUri ()
    {
        return $this->oBlogsModule->sHomeUrl . $this->oBlogsModule->_oConfig->sUserExFile . "?action=show_calendar&date=";
    }

    function getBrowseUri ()
    {
        return $this->oBlogsModule->sHomeUrl . $this->oBlogsModule->_oConfig->sUserExFile . "?action=show_calendar_day&date=";
    }

    function getEntriesNames ()
    {
        return array(_t('_bx_blog_single'), _t('_bx_blog_plural'));
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
