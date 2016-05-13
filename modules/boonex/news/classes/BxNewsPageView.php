<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextPageView');

class BxNewsPageView extends BxDolTextPageView
{
    function __construct($sName, &$oObject)
    {
        parent::__construct('news_single', $sName, $oObject);
    }
}
