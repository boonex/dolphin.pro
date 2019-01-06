<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxTemplProfileView');
bx_import ('BxTemplProfileGenerator');

class BxDolProfileInfoPageView extends BxTemplProfileView
{
    // contain informaion about viewed profile ;
    var $aMemberInfo = array();
    // logged member ID ;
    var $iMemberID;
    var $oProfilePV;

    /**
     * Class constructor ;
     */
    function __construct( $sPageName, &$aMemberInfo )
    {
        global $site, $dir;

        $this->oProfileGen = new BxTemplProfileGenerator( $aMemberInfo['ID'] );
        $this->aConfSite = $site;
        $this->aConfDir  = $dir;
        BxDolPageView::__construct($sPageName);

        $this->iMemberID  = getLoggedId();
        $this->aMemberInfo = &$aMemberInfo;
    }

    /**
     * Function will generate profile's  general information ;
     *
     * @return : (text) - html presentation data;
     */
    function getBlockCode_GeneralInfo($iBlockID)
    {
        return $this -> getBlockCode_PFBlock($iBlockID, 17);
    }

    /**
     * Function will generate profile's additional information ;
     *
     * @return : (text) - html presentation data;
     */
    function getBlockCode_AdditionalInfo($iBlockID)
    {
        return $this -> getBlockCode_PFBlock($iBlockID, 20);
    }

}
