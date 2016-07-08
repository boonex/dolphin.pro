<?php

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseCmtsView.php' );

/**
 * @see BxDolCmts
 */
class BxTemplCmtsView extends BxBaseCmtsView
{
    function __construct( $sSystem, $iId, $iInit = 1 )
    {
        parent::__construct( $sSystem, $iId, $iInit );
    }
}
