<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextPageMain');

class BxArlPageMain extends BxDolTextPageMain
{
    function BxArlPageMain(&$oObject)
    {
        parent::BxDolTextPageMain('articles_home', $oObject);
    }
}
