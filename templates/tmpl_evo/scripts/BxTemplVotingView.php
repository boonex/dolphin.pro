<?php

bx_import('BxBaseVotingView');

/**
 * @see BxDolVoting
 */
class BxTemplVotingView extends BxBaseVotingView
{
    function BxTemplVotingView( $sSystem, $iId, $iInit = 1 )
    {
        BxBaseVotingView::BxBaseVotingView( $sSystem, $iId, $iInit );
    }
}
