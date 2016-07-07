<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseSearchProfile.php' );

class BxTemplSearchProfile extends BxBaseSearchProfile
{
    function __construct($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '')
    {
        parent::__construct($sParamName, $sParamValue, $sParamValue1, $sParamValue2);
    }
}
