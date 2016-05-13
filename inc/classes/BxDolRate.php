<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php');

class BxDolRate extends BxDolPageView
{
    var $sType;
    var $iViewer;
    // array of headers for rate page
    var $aPageCaption = array();
    function __construct($sType)
    {
        parent::__construct($sType . '_rate');

        $this->sType = $sType;
        $this->iViewer = getLoggedId();
    }

    function getVotedItems ()
    {
        $ip = getVisitorIP();
        $oDolVoting = new BxDolVoting($this->sType, 0, 0);
        $aVotedItems = $oDolVoting->getVotedItems ($ip);
        return $this->reviewArray($aVotedItems, $oDolVoting->_aSystem['row_prefix'].'id');
    }

    function reviewArray ($aFiles, $sKey = '')
    {
        $aList = array();
        if (is_array($aFiles)) {
            foreach ($aFiles as $iKey => $aValue) {
                $aList[$iKey] = $aValue[$sKey];
            }
        }
        return $aList;
    }

    //get array or previous rated objects
    function getRatedSet ()
    {
    }

    //get array or previous rated objects
    function getRateObject ()
    {
    }
}
