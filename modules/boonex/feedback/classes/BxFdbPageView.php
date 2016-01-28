<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextPageView');

class BxFdbPageView extends BxDolTextPageView
{
    function __construct($sName, &$oObject)
    {
        parent::__construct('feedback', $sName, $oObject);
    }
}
