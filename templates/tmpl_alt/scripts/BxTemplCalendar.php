<?php

bx_import ('BxBaseCalendar');

/**
 * @see BxDolCalendar
 */
class BxTemplCalendar extends BxBaseCalendar
{
    function __construct($iYear, $iMonth)
    {
        parent::__construct($iYear, $iMonth);
    }
}
