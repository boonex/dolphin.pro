<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

global $tmpl;
require_once(BX_DIRECTORY_PATH_ROOT . 'templates/tmpl_' . $tmpl . '/scripts/BxTemplVotingView.php');

class BxSitesVoting extends BxTemplVotingView
{
    /**
     * Constructor
     */
    function BxSitesVoting($sSystem, $iId)
    {
        parent::BxTemplVotingView($sSystem, $iId);
    }
}
