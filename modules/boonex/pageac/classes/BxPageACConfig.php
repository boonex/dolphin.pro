<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConfig');

require_once( BX_DIRECTORY_PATH_INC . 'membership_levels.inc.php' );

class BxPageACConfig extends BxDolConfig
{
    /**
     * Constructor
     */
    var $_aMemberships;
    function __construct($aModule)
    {
        parent::__construct($aModule);
        $this->_aMemberships = getMemberships();
    }
}
