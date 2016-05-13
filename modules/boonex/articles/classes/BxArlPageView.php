<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextPageView');

class BxArlPageView extends BxDolTextPageView
{
    function __construct($sName, &$oObject)
    {
        parent::__construct('articles_single', $sName, $oObject);
    }
}
