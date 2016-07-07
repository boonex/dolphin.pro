<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseProfileView.php' );

class BxTemplProfileView extends BxBaseProfileView
{
    function __construct(&$oPr, &$aSite, &$aDir)
    {
        parent::__construct($oPr, $aSite, $aDir);
    }
}
