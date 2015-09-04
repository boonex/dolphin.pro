<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseSearchProfile.php' );

class BxTemplSearchProfile extends BxBaseSearchProfile
{
    function BxTemplSearchProfile($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '')
    {
        parent::BxBaseSearchProfile($sParamName, $sParamValue, $sParamValue1, $sParamValue2);
    }
}
