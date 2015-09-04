<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseProfileView.php' );

class BxTemplProfileView extends BxBaseProfileView
{
    function BxTemplProfileView(&$oPr, &$aSite, &$aDir)
    {
        BxBaseProfileView::BxBaseProfileView($oPr, $aSite, $aDir);
    }
}
