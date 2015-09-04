<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextPageView');

class BxNewsPageView extends BxDolTextPageView
{
    function BxNewsPageView($sName, &$oObject)
    {
        parent::BxDolTextPageView('news_single', $sName, $oObject);
    }
}
