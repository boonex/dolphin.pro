<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplCalendar');

class BxDolFilesCalendar extends BxTemplCalendar
{
    var $iBlockID = 0;
    var $sDynamicUrl = '';

    function __construct($iYear, $iMonth, &$oDb, &$oTemplate, &$oConfig)
    {
        parent::__construct($iYear, $iMonth);
        $this->oDb = &$oDb;
        $this->oTemplate = &$oTemplate;
        $this->oConfig = &$oConfig;
    }

    function getData()
    {
        return $this->oDb->getFilesByMonth($this->iYear, $this->iMonth, $this->iNextYear, $this->iNextMonth);
    }

    function getUnit(&$aData)
    {
    }

    function getBaseUri()
    {
        return BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . "calendar/";
    }

    function getBrowseUri()
    {
        return BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . "browse/calendar/";
    }

    function getEntriesNames()
    {
        return array(
            _t('_bx_' . $this->oConfig->getUri() . '_single'),
            _t('_bx_' . $this->oConfig->getUri() . '_plural')
        );
    }

    function getMonthUrl($isNextMoths, $isMiniMode = false)
    {
        if ($isMiniMode && $this->iBlockID && $this->sDynamicUrl) {
            return "javascript:loadDynamicBlock('" . $this->iBlockID . "', '" . bx_append_url_params($this->sDynamicUrl,
                'date=' . ($isNextMoths ? "{$this->iNextYear}/{$this->iNextMonth}" : "{$this->iPrevYear}/{$this->iPrevMonth}")) . "');";
        } else {
            return parent::getMonthUrl($isNextMoths, $isMiniMode);
        }
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
