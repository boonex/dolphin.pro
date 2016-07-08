<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import ('BxTemplCalendar');

    class BxPollCalendar extends BxTemplCalendar
    {
        var $oDb, $oTemplate, $oConfig;

        var $sActionViewResult = 'view_calendar/';
        var $sActionBase       = 'calendar/';

        function __construct ($iYear, $iMonth, &$oDb, &$oTemplate, &$oConfig)
        {
            parent::__construct($iYear, $iMonth);
            $this->oDb = &$oDb;
            $this->oTemplate = &$oTemplate;
            $this->oConfig = &$oConfig;
        }

        function getData ()
        {
            return $this->oDb->getPollsByMonth ($this->iYear, $this->iMonth, $this->iNextYear, $this->iNextMonth);
        }

        function getBaseUri ()
        {
            return BX_DOL_URL_ROOT . $this -> oConfig->getBaseUri() . $this ->sActionBase;
        }

        function getBrowseUri ()
        {
            return BX_DOL_URL_ROOT . $this -> oConfig->getBaseUri() . $this -> sActionViewResult;
        }

        function getEntriesNames ()
        {
            return array(_t('_bx_poll'), _t('_bx_polls'));
        }
    }
