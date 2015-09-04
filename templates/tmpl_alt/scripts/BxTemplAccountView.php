<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseAccountView.php' );

class BxTemplAccountView extends BxBaseAccountView
{
    function BxTemplAccountView($iMember, &$aSite, &$aDir)
    {
        parent::BxBaseAccountView($iMember, $aSite, $aDir);
    }
}
