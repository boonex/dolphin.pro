<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolTwigTemplate');

/*
 * Map module View
 */
class BxGSearchTemplate extends BxDolTwigTemplate
{
    /**
     * Constructor
     */
    function BxGSearchTemplate(&$oConfig, &$oDb)
    {
        parent::BxDolTwigTemplate($oConfig, $oDb);
        $this->_iPageIndex = 401;
    }
}
