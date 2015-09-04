<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplVotingView');

class BxStoreVoting extends BxTemplVotingView
{
    /**
     * Constructor
     */
    function BxStoreVoting($sSystem, $iId)
    {
        parent::BxTemplVotingView($sSystem, $iId);
    }

    function getMain()
    {
        $aPathInfo = pathinfo(__FILE__);
        require_once ($aPathInfo['dirname'] . '/BxStoreSearchResult.php');
        return BxStoreSearchResult::getMain();
    }

    function checkAction ()
    {
        if (!parent::checkAction())
            return false;
        $oMain = $this->getMain();
        $aDataEntry = $oMain->_oDb->getEntryById($this->getId ());
        return $oMain->isAllowedRate($aDataEntry);
    }
}
