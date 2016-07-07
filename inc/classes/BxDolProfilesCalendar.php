<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxTemplCalendar');

class BxDolProfilesCalendar extends BxTemplCalendar
{
    var $sMode = 'dor';

    function __construct ($iYear, $iMonth)
    {
        parent::__construct($iYear, $iMonth);
    }

    function setMode($sMode)
    {
        $this->sMode = $sMode;
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
        switch($this->sMode) {
            case 'dor':
                return db_res_assoc_arr ("
                    SELECT `Profiles`.*, DAYOFMONTH(`Profiles`.`DateReg`) AS `Day`
                    FROM `Profiles`
                    WHERE
                        UNIX_TIMESTAMP(`Profiles`.`DateReg`) >= UNIX_TIMESTAMP('{$this->iYear}-{$this->iMonth}-1')
                        AND UNIX_TIMESTAMP(`Profiles`.`DateReg`) < UNIX_TIMESTAMP('{$this->iNextYear}-{$this->iNextMonth}-1')
                        AND `Profiles`.`Status` = 'Active'
                ");
            case 'dob':
                $aWhere[] = "MONTH(`DateOfBirth`) = MONTH(CURDATE()) AND DAY(`DateOfBirth`) = DAY(CURDATE())";
                return db_res_assoc_arr ("
                    SELECT `Profiles`.*, DAYOFMONTH(`DateOfBirth`) AS `Day`
                    FROM `Profiles`
                    WHERE
                        MONTH(`DateOfBirth`) = MONTH('{$this->iYear}-{$this->iMonth}-1') AND
                        `Profiles`.`Status` = 'Active'
                ");
        }

    }

    /**
     * return html for data unit for some day, it is:
     * - icon 32x32 with link if data have associated image
     * - data title with link if data have no associated image
     */
    function getUnit(&$aData)
    {
        //global $oFunctions;

        $iProfileID = (int)$aData['ID'];

        $sName = getNickName($iProfileID);
        $sUrl = getProfileLink($iProfileID);

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
        $sPossibleMode = (isset($_REQUEST['mode']) && $_REQUEST['mode']!='') ? '&mode=' . $_REQUEST['mode'] : '';
        return BX_DOL_URL_ROOT . "calendar.php?{$sPossibleMode}&date=";
    }

    function getBrowseUri ()
    {
        return BX_DOL_URL_ROOT .  "calendar.php?action=browse&date=";
    }

    function getEntriesNames ()
    {
        return array(_t('_sys_profile'), _t('_sys_profiles'));
    }
}
