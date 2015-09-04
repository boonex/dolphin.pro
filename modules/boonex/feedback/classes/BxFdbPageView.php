<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextPageView');

class BxFdbPageView extends BxDolTextPageView
{
    function BxFdbPageView($sName, &$oObject)
    {
        parent::BxDolTextPageView('feedback', $sName, $oObject);
    }
}
