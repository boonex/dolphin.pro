<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolTwigTemplate');

/*
 * Avatar module View
 */
class BxAvaTemplate extends BxDolTwigTemplate
{
    /**
     * Constructor
     */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
        $this->_iPageIndex = 500;
    }
}
