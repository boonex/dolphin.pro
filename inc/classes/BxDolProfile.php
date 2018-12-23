<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolProfileQuery');

class BxDolProfile
{
    var $_iProfileID;
    var $_aProfile;
    var $bCouple;
    var $_iCoupleID;
    var $_aCouple;

    /**
     * Constructor
     *
     * @return User
     */
    function __construct( $vProfileID, $bWithEmail = 1 )
    {
        $this -> _iProfileID = $this -> getID( $vProfileID, $bWithEmail );
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $ID
     * @param unknown_type $float
     */
    function getProfileThumbnail( $float )
    {
        $ret = $this -> getProfileImageUrl( $iProfileID );
    }

    /**
     * return link to profile image only.
     *
     * @param unknown_type $ID
     * @param unknown_type $imageNum
     */
    function getProfileImageUrl( $imageNum )
    {

    }

    /**
     *  return assoc array of all frofile fields
     */
    function getProfileData()
    {
        global $aUser;

        $bUseCacheSystem = ( getParam('enable_cache_system') == 'on' ) ? true : false;

        $oPDb = new BxDolProfileQuery();
        $sProfileCache = BX_DIRECTORY_PATH_CACHE . 'user' . $this -> _iProfileID . '.php';
        if( $bUseCacheSystem && file_exists( $sProfileCache ) && is_file( $sProfileCache ) ) {
            require_once($sProfileCache);
            $this -> _aProfile = $aUser[$this -> _iProfileID];
        } else
            $this -> _aProfile = $oPDb -> getProfileDataById( $this -> _iProfileID );

        //get couple data
        if( $this -> _aProfile['Couple'] ) {
            $this -> bCouple = true;
            $this -> _iCoupleID = $this -> _aProfile['Couple'];

            $sProfileCache = BX_DIRECTORY_PATH_CACHE . 'user' . $this -> _iCoupleID . '.php';
            if( $bUseCacheSystem && file_exists( $sProfileCache ) && is_file( $sProfileCache ) ) {
                require_once($sProfileCache);
                $this -> _aCouple = $aUser[$this -> _iCoupleID];
            } else
                $this -> _aCouple = $oPDb -> getProfileDataById( $this -> _iCoupleID );
        }

        return $this -> _aProfile;
    }

    /**
     * Update profile info to database
     *
     *
     * @param int   $iUserID
     * @param array $aData
     * where the key of the array is name of database table field
     *
     * example:
     * $aData['Sex'] = 'male';
     *
     */
    function updateProfileData( $aData )
    {
        if( is_array( $aData ) ) {
            $sQueryAdd = '';
            foreach($aData as $key => $value ) {
                $sQueryAdd .= " `$key` = '$value', ";
            }
        }

        $this -> updateProfileDataFile( $iProfileID );
    }

    /**
     * function create cache data file
     *
     * @param int $iProfileID
     */
    function updateProfileDataFile( $iProfileID )
    {

    }

    /**
     * Print code for membership status
     * $memberID - member ID
     * $offer_upgrade - will this code be printed at [c]ontrol [p]anel
     * $credits - will print credits status if $credits == 1
     */
    function getMembershipStatus( $iPrifileID, $offer_upgrade = true, $credits = 0 )
    {

    }

    /**
     * Shows how many days, hours, minutes member was onine last time
     *
     * @param  $lastNavTime
     *
     * @return int
     */
    /*function  getProfileLastOnlinePeriod( $lastNavTime )
    {

    }*/

    function getNickName()
    {
        $oProfileQuery = new BxDolProfileQuery();
        return process_line_output( $oProfileQuery -> getNickName( $this -> _iProfileID ) );
    }

    function getPassword()
    {

    }

    function getID( $vID = false, $bWithEmail = 1 )
    {
        if (false === $vID)
            return $this -> _iProfileID;

        $oPDb = new BxDolProfileQuery();

        if ( $bWithEmail ) {
            bx_import('BxDolForm');
            if (BxDolFormCheckerHelper::checkEmail($vID)) {
                $aMail = $oPDb -> getIdByEmail( $vID );
                if ( (int)$aMail['ID'] ) {
                    return (int)$aMail['ID'];
                }
            }
        }

        $iID = (int)$vID;
        if ( strcmp("$vID", "$iID") === 0 ) {
            return $iID;
        } else {
            $aNick = $oPDb -> getIdByNickname( $vID );
            if ( (int)$aNick['ID'] ) {
                return (int)$aNick['ID'];
            }
        }

        return false;
    }

}
