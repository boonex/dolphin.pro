<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

class BxSitesConfig extends BxDolConfig
{
    var $_oDb;
    var $_bAutoapprove;
    var $_bComments;
    var $_sCommentsSystemName;
    var $_bVotes;
    var $_sVotesSystemName;
    var $_iPerPage;

    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);
    }

    function init(&$oDb)
    {
        $this->_oDb = &$oDb;

        $this->_bAutoapprove = $this->_oDb->getParam('bx_sites_autoapproval') == 'on';
        $this->_bComments = $this->_oDb->getParam('bx_sites_comments') == 'on';
        $this->_sCommentsSystemName = "bx_sites";
        $this->_bVotes = $this->_oDb->getParam('bx_sites_votes') == 'on';
        $this->_sVotesSystemName = "bx_sites";
        $this->_iPerPage = (int)$this->_oDb->getParam('bx_sites_per_page');
    }

    function isAutoapprove()
    {
        return $this->_bAutoapprove;
    }

    function isCommentsAllowed()
    {
        return $this->_bComments;
    }

    function getCommentsSystemName()
    {
        return $this->_sCommentsSystemName;
    }

    function isVotesAllowed()
    {
        return $this->_bVotes;
    }

    function getVotesSystemName()
    {
        return $this->_sVotesSystemName;
    }

    function getPerPage()
    {
        return $this->_iPerPage;
    }
}
