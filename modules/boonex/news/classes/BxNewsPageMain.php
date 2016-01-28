<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextPageMain');

class BxNewsPageMain extends BxDolTextPageMain
{
    function __construct(&$oObject)
    {
        parent::__construct('news_home', $oObject);
    }
}
