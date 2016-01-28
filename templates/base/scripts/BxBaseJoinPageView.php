<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import("BxDolJoinProcessor");
bx_import("BxDolPageView");

class BxBaseJoinPageView extends BxDolPageView
{
    function __construct()
    {
        parent::__construct('join');
    }

    function getBlockCode_JoinForm()
    {
        $oJoinProc = new BxDolJoinProcessor();
        return array($oJoinProc->process(), array(), array(), false);
    }
}
