<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConfig');

class BxAdsConfig extends BxDolConfig
{
    var $_iAnimationSpeed;

    var $bUseFriendlyLinks;
    var $bAdminMode;
    var $sCurrBrowsedFile;
    var $sSpacerPath;

    // SQL tables

    var $sSQLPostsTable;
    var $sSQLPostsMediaTable;
    var $sSQLCatTable;
    var $sSQLSubcatTable;

    var $_sCommentSystemName;

    /*
    * Constructor.
    */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->_iAnimationSpeed = 'normal';

        $this->sSpacerPath = getTemplateIcon('spacer.gif');

        $this->sSQLPostsTable = 'bx_ads_main';
        $this->sSQLPostsMediaTable = 'bx_ads_main_media';
        $this->sSQLCatTable = 'bx_ads_category';
        $this->sSQLSubcatTable = 'bx_ads_category_subs';

        $this->_sCommentSystemName = "ads";
    }

    function getAnimationSpeed()
    {
        return $this->_iAnimationSpeed;
    }

    function getCommentSystemName()
    {
        return $this->_sCommentSystemName;
    }
}
