<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

class BxCRSSConfig extends BxDolConfig
{
    var $_iAnimationSpeed;

    /*
    * Constructor.
    */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_iAnimationSpeed = 'normal';
    }

    function getAnimationSpeed()
    {
        return $this->_iAnimationSpeed;
    }
}
