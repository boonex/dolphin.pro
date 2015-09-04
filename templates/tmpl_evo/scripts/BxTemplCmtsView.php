<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseCmtsView.php' );

/**
 * @see BxDolCmts
 */
class BxTemplCmtsView extends BxBaseCmtsView
{
    function BxTemplCmtsView( $sSystem, $iId, $iInit = 1 )
    {
        BxBaseCmtsView::BxBaseCmtsView( $sSystem, $iId, $iInit );
    }
}
