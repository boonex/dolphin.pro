<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseAccountView.php' );

class BxTemplAccountView extends BxBaseAccountView
{
    function __construct($iMember, &$aSite, &$aDir)
    {
        parent::__construct($iMember, $aSite, $aDir);
    }
}
