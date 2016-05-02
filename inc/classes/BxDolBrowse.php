<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php');

    class BxDolBrowse  extends BxDolPageView
    {
        // consit all member's information ;
        var $aMembersInfo;

        // consit all Date of birth range ;
        var $aDateBirthRange;

        // consit all Sex ranges ;
        var $aSexRange;

        // consit all Sex ranges ;
        var $aCountryRange;

        // consist true value if isset permalink mode ;
        var $bPermalinkMode;

        // need for array searching ;
        var $sKeyName;

        var $iMemberOnlineTime;

        /**
         * @description : class constructor ;
        */

        function __construct($sPageName = null)
        {
            global $aPreValues;

            // read data from cache file ;
            $oCache = $GLOBALS['MySQL']->getDbCacheObject();
            $this -> aMembersInfo = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_browse_people'));
            if (null === $this -> aMembersInfo)
                $this -> aMembersInfo = array ();

            // fill aDateBirthRange array ;
            $iStartDate = getParam('search_start_age');
            $iLastDate  = getParam('search_end_age');

            // fill date of birth array
            while ( $iStartDate <= $iLastDate ) {
                $this -> aDateBirthRange[$iStartDate . '-' . ($iStartDate + 2)] = 0;
                $iStartDate +=3;
            }

            // check permalink mode ;

            $this -> bPermalinkMode = ( getParam('permalinks_browse') )
                ? true
                : false;

            // check member on line time ;

            $this -> iMemberOnlineTime = (int)getParam( "member_online_time" );

            // fill sex array ;
            ksort($aPreValues['Sex'], SORT_STRING);
            foreach( $aPreValues['Sex'] AS $sKey => $aItems ) {
                $this -> aSexRange[$sKey] = 0;
            }

            // fill country array ;
            ksort($aPreValues['Country'], SORT_STRING);
            foreach( $aPreValues['Country'] AS $sKey => $aItems ) {
                $this -> aCountryRange[$sKey] = 0;
            }

            if($sPageName) {
                parent::__construct($sPageName);
            }

        }

        /**
         * @description : function will return count of male and female;
         * @param 		: $PrimaryQuery (string) - additional sql query;
         * @return 		: Array hash with `sex` list;
        */

        function getSexCount( $PrimaryQuery )
        {
            $aSexArray = array();

            foreach( $this -> aSexRange AS $sKey => $sValue ) {
                $sKey = process_db_input($sKey, BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION);
                $sWhereParam = " AND `Sex` = '{$sKey}' ";
                $iCount = db_value( $PrimaryQuery . $sWhereParam );

                if ( $iCount )
                    $aSexArray[$sKey] = $iCount;
            }

            return $aSexArray;
        }

        /**
         * @description : function will return count of age ranges;
         * @param 		: $PrimaryQuery (string) - additional sql query;
         * @return 		: Array hash with `ages` list;
        */

        function getAgesCount( $PrimaryQuery )
        {
            $aAgeArray = array();

            foreach( $this -> aDateBirthRange AS $sKey => $sValue ) {
                $sWhereParam = null;
                $aDateRange = explode('-', $sKey);

                $iFrom  = (int) $aDateRange[0];
                $iTo	= (int) $aDateRange[1];

                $sWhereParam .=
                "
                        AND
                    ((YEAR(CURDATE())-YEAR(`DateOfBirth`)) - (RIGHT(CURDATE(),5)<RIGHT(`DateOfBirth`,5))) >= {$iFrom}
                        AND
                    ((YEAR(CURDATE())-YEAR(`DateOfBirth`)) - (RIGHT(CURDATE(),5)<RIGHT(`DateOfBirth`,5))) <= {$iTo}
                ";

                $iCount = db_value( $PrimaryQuery . $sWhereParam);
                if ( $iCount ) {
                    $aAgeArray[$sKey] = $iCount;
                }
            }

            return $aAgeArray;
        }

        /**
         * @description : function will return count of mens into counries;
         * @param 		: $PrimaryQuery (string) - additional sql query;
         * @return 		: Array with `countries` list;
        */

        function getCountriesCount( $PrimaryQuery )
        {
            $aCountryArray = null;

            foreach( $this -> aCountryRange AS $sKey => $sValue ) {
                $sKey = process_db_input($sKey, BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION);
                $sWhereParam = " AND `Country` = '{$sKey}' ";
                $iCount = db_value( $PrimaryQuery . $sWhereParam );

                if ( $iCount )
                    $aCountryArray[$sKey] = $iCount;
            }

            return $aCountryArray;
        }

        /**
         * @description : function will generate array with all global statistics ;
         * @param 		: $sSex (string) human's sex ;
         * @param 		: $sAge (string) human's age range (possibility format - '15-60' ) ;
         * @param 		: $sCountry (string) human's country ( use ISO2 format ) ;
         * @param 		: $sPhoto (string) if isset user's primary photo;
         * @param 		: $sOnline (string) if user online ;
         * @param 		: $sType (string) set specific type of member information ;
         * @return 		: array ;
        */

        function _getGlobalStatistics( $sSex = '', $sAge = '', $sCountry = ''
            , $sPhoto = '', $sOnline = '', $sType = '' ) {

            $sCurrentKey = ( $this -> sKeyName )
                ? $this -> sKeyName
                : 'public';

            $aSexArray = $aAgeArray = $aCountryArray = array();

            // collect the SQL queries ;

            $sWhereParam = null;
            $sWhereParam .= ( $sSex )
                ? " AND `Sex` = '{$sSex}' "
                : null;

            $sWhereParam .= ( $sCountry )
                ? " AND `Country` = '{$sCountry}' "
                : null;

            if ( $sAge ) {

                $aDateRange = explode('-', $sAge);

                $iFrom  = (int)$aDateRange[0] - 1;
                $iTo	= (int)$aDateRange[1];

                unset($aDateRange);

                if ( !$iFrom or !$iTo )
                    return null;

                $sWhereParam .=
                "
                        AND
                    ((YEAR(CURDATE())-YEAR(`DateOfBirth`)) - (RIGHT(CURDATE(),5)<RIGHT(`DateOfBirth`,5))) >= {$iFrom}
                        AND
                    ((YEAR(CURDATE())-YEAR(`DateOfBirth`)) - (RIGHT(CURDATE(),5)<RIGHT(`DateOfBirth`,5))) <= {$iTo}
                ";

            }

            // primary photo ;

            $sWhereParam .= (  $sPhoto  )
                ? ' AND `Profiles`.`Avatar` <> 0'
                : null;

            // online ;

            $sWhereParam .= (  $sOnline  )
                ? " AND (`Profiles`.`DateLastNav` > SUBDATE(NOW(), INTERVAL " . $this -> iMemberOnlineTime . " MINUTE)) "
                : null;

            $sQuery = "SELECT COUNT(*) FROM `Profiles` WHERE `Status` = 'Active' {$sWhereParam}";

            // # end of collect SQL queries ;

            // if cache file not consist any of rows with received keyname ;

            if ( !isset($this -> aMembersInfo[$sCurrentKey]) ) {
                // if KeyName empty the function will generate all information block ;

                if ( !$sSex ) {
                    $this -> aMembersInfo[$sCurrentKey]['sex']= $this -> getSexCount($sQuery);
                }

                if ( !$sCountry ) {
                    $this -> aMembersInfo[$sCurrentKey]['country'] = $this -> getCountriesCount($sQuery);
                }

                if ( !$sAge ) {
                    $this -> aMembersInfo[$sCurrentKey]['age'] = $this -> getAgesCount($sQuery);
                }

                // online param must be non cacheble ( write into cache ) ;

                if ( !$sAge or !$sCountry or !$sSex ) {
                    // online param must be non cacheble ( write into cache ) ;

                    if ( !$sOnline ) {
                        $this -> writeCache($this -> aMembersInfo);
                    }

                }

            }

            // gen selected block ;

            if ( $sType ) {
                // this keyname need for already selected block ;

                $sBackKey = 'back_path_' . $sCurrentKey;

                switch ( $sType ) {
                    case 'sex' :
                        if ( !isset($this -> aMembersInfo[$sBackKey]['sex']) ) {
                            $aSexArray[$sBackKey]['sex'] = $this -> getSexCount($sQuery);

                            if ( !$sOnline ) {
                                $this -> writeCache($aSexArray);
                            }

                            return $aSexArray[$sBackKey]['sex'];
                        } else {
                            return $this -> aMembersInfo[$sBackKey]['sex'];
                        }
                    break;
                    case 'age' :
                        if ( !isset($this -> aMembersInfo[$sBackKey]['age']) ) {
                            $aAgeArray[$sBackKey]['age'] = $this -> getAgesCount($sQuery);

                            if ( !$sOnline ) {
                                $this -> writeCache($aAgeArray);
                            }

                            return $aAgeArray[$sBackKey]['age'];
                        } else {
                            return $this -> aMembersInfo[$sBackKey]['age'];
                        }
                    break;
                    case 'country' :
                        if ( !isset($this -> aMembersInfo[$sBackKey]['country']) ) {
                            $aCountryArray[$sBackKey]['country'] = $this -> getCountriesCount($sQuery);

                            if ( !$sOnline ) {
                                $this -> writeCache($aCountryArray);
                            }

                            return $aCountryArray[$sBackKey]['country'];
                        } else {
                            return $this -> aMembersInfo[$sBackKey]['country'];
                        }
                    break;
                }

            }

            return $this -> aMembersInfo[$sCurrentKey];

        }

        /**
         * @description : function will write data into cache file ;
         * @param 		:  $aMembersInfo (array) with all display parts ;
         * @return		:
        */

        function writeCache( $aMembersInfo )
        {

            $oCache = $GLOBALS['MySQL']->getDbCacheObject();
            $aTmpData = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_browse_people'));
            if (null === $aTmpData)
                $aTmpData = array ();

            if ( is_array($aMembersInfo) and !empty($aMembersInfo) ) {
                foreach( $aMembersInfo AS $sKey => $aFields )
                    foreach( $aFields AS $sFieldName => $aItems ) {
                        if ( is_array($aItems) and !empty($aItems) ) {
                            foreach( $aItems AS $sItemName => $sItemValue )
                                $aTmpData[$sKey][$sFieldName][$sItemName] = $sItemValue;
                        }

                    }

                $oCache->setData ($GLOBALS['MySQL']->genDbCacheKey('sys_browse_people'), $aTmpData);
            }
        }

    }
