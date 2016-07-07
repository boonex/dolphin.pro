<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolCalendar');

/**
 * @see BxDolCalendar
 */
class BxBaseCalendar extends BxDolCalendar
{
    function __construct ($iYear, $iMonth)
    {
        parent::__construct($iYear, $iMonth);
    }

    function display($isMiniMode = false)
    {
        $aVars = array (
            'month_prev_url' => $this->getMonthUrl(false, $isMiniMode),
            'month_next_url' => $this->getMonthUrl(true, $isMiniMode),
            'month_current' => $this->getTitle(),
        );
        $sTopControls = $GLOBALS['oSysTemplate']->parseHtmlByName('calendar' . ($isMiniMode ? '_mini' : '') . '_top_controls.html', $aVars);

        $aVars = array_merge($aVars, array (
            'top_controls' => $sTopControls,
            'bx_repeat:week_names' => $this->_getWeekNames ($isMiniMode),
            'bx_repeat:calendar_row' => $this->_getCalendar (),
            'bottom_controls' => $sTopControls,
        ));
        $sHtml = $GLOBALS['oSysTemplate']->parseHtmlByName($isMiniMode ? 'calendar_mini.html' : 'calendar.html', $aVars);
        $sHtml = preg_replace ('#<bx_repeat:events>.*?</bx_repeat:events>#s', '', $sHtml);
        $GLOBALS['oSysTemplate']->addCss(array('calendar.css', 'calendar_phone.css'));
        return $sHtml;
    }
}
