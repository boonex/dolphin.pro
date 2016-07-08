<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

class BxProfileCustomizeConfig extends BxDolConfig
{
    var $_oDb;
	var $_aJsClasses;
    var $_aJsObjects;

    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

		$this->_aJsClasses = array('main' => 'BxProfileCustimizer');
        $this->_aJsObjects = array('main' => 'oCustomizer');
    }

    function init(&$oDb)
    {
        $this->_oDb = &$oDb;
    }

	function getJsClass($sType = 'main')
    {
        if(empty($sType))
            return $this->_aJsClasses;

        return $this->_aJsClasses[$sType];
    }

    function getJsObject($sType = 'main')
    {
        if(empty($sType))
            return $this->_aJsObjects;

        return $this->_aJsObjects[$sType];
    }
}
