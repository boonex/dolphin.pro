<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplVotingView');

class BxEventsVoting extends BxTemplVotingView
{
    /**
     * Constructor
     */
    function __construct($sSystem, $iId)
    {
        parent::__construct($sSystem, $iId);
    }

    function getMain()
    {
        $aPathInfo = pathinfo(__FILE__);
        require_once ($aPathInfo['dirname'] . '/BxEventsSearchResult.php');
        return (new BxEventsSearchResult())->getMain();
    }

    function checkAction ($bPerformAction = false)
    {
        if (!parent::checkAction($bPerformAction))
            return false;

        $oMain = $this->getMain();
        $aEvent = $oMain->_oDb->getEntryByIdAndOwner($this->getId (), 0, true);
        return $oMain->isAllowedRate($aEvent);
    }
}
