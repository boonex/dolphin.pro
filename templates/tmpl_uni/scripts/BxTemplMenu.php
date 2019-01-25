<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxBaseMenu');

/**
* @see BxBaseMenu;
*/
class BxTemplMenu extends BxBaseMenu
{
    /**
    * Class constructor;
    */
    function __construct()
    {
        parent::__construct();
    }
}

// Creating template navigation menu class instance
$oTopMenu = new BxTemplMenu();
