<?php

bx_import('BxBaseVotingView');

/**
 * @see BxDolVoting
 */
class BxTemplVotingView extends BxBaseVotingView
{
    function __construct( $sSystem, $iId, $iInit = 1 )
    {
        parent::__construct( $sSystem, $iId, $iInit );
    }
}
