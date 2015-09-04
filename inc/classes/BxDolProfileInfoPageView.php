<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxTemplProfileView');

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
    function BxDolProfileInfoPageView( $sPageName, &$aMemberInfo )
    {
        global $site, $dir;

        $this->oProfileGen = new BxBaseProfileGenerator( $aMemberInfo['ID'] );
        $this->aConfSite = $site;
        $this->aConfDir  = $dir;
        parent::BxDolPageView($sPageName);

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
