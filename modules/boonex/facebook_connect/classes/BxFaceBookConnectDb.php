<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

    class BxFaceBookConnectDb extends BxDolModuleDb
    {
        var $_oConfig;
        var $sTablePrefix;

        /**
         * Constructor.
         */
        function BxFaceBookConnectDb(&$oConfig)
        {
            parent::BxDolModuleDb();

            $this -> _oConfig = $oConfig;
            $this -> sTablePrefix = $oConfig -> getDbPrefix();
        }

        /**
         * Process big number
         *
         * @param $mValue mixed
         * @return integer
         */
        function _processBigNumber($mValue)
        {
            return preg_replace('/[^0-9]/', '', $mValue);
        }

        /**
         * Check fb profile id
         *
         * @param $iFbUid integer
         * @return integer
         */
        function getProfileId($iFbUid)
        {
            $iFbUidCopy = (int) $iFbUid;
            $iFbUid = $this -> _processBigNumber($iFbUid);


            //-- handle 64 bit number on 32bit system ( will need remove it in a feature version)--//
            if($iFbUidCopy != $iFbUid) {
                //update id
                $sQuery = "UPDATE `{$this -> sTablePrefix}accounts` SET `fb_profile` = '{$iFbUid}'
                    WHERE `fb_profile` = '{$iFbUidCopy}'";

                $this -> query($sQuery);
            }
            //--

            //-- new auth method --//
            $sQuery = "SELECT `id_profile` FROM `{$this -> sTablePrefix}accounts` WHERE
                `fb_profile` = '{$iFbUid}' LIMIT 1";

            $iProfileId = $this -> getOne($sQuery);
            //--

            return $iProfileId;
        }

        /**
         *  Save new Fb uid
         *
         * @param $iProfileId integer
         * @param $iFbUid integer
         * @return void
         */
        function saveFbUid($iProfileId, $iFbUid)
        {
            $iFbUid = $this -> _processBigNumber($iFbUid);
            $iProfileId = (int) $iProfileId;

            $sQuery = "REPLACE INTO `{$this -> sTablePrefix}accounts`
                        SET `id_profile` = {$iProfileId}, `fb_profile` = '{$iFbUid}'";

            $this -> query($sQuery);
        }

        /**
         * Delete Fb's uid
         *
         * @param $iProfileId integer
         * @return void
         */
        function deleteFbUid($iProfileId)
        {
            $iProfileId = (int) $iProfileId;
            $sQuery = "DELETE FROM `{$this -> sTablePrefix}accounts`
                WHERE `id_profile` = {$iProfileId}";

            $this -> query($sQuery);
        }

        /**
         * Make as friends
         *
         * @param $iMemberId integer
         * @param $iProfileId intger
         * @return void
         */
        function makeFriend($iMemberId, $iProfileId)
        {
            $iMemberId = (int) $iMemberId;
            $iProfileId = (int) $iProfileId;

            $sQuery = "INSERT INTO `sys_friend_list` SET
                `ID` = '{$iMemberId}', `Profile` = '{$iProfileId}', `Check` = 1";

            $this -> query($sQuery);
        }

        /**
         * Create new profile;
         *
         * @param  : (array) $aProfileFields    - `Profiles` table's fields;
         * @return : (integer)  - profile's Id;
         */
        function createProfile(&$aProfileFields)
        {
            $sFields = null;

            // procces all recived fields;
            foreach($aProfileFields as $sKey => $mValue) {
                $mValue = process_db_input($mValue, BX_TAGS_VALIDATE, BX_SLASHES_AUTO);
                $sKey = process_db_input($sKey, BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $sFields .= "`{$sKey}` = '{$mValue}', ";
            }

            $sFields = preg_replace( '/,$/', '', trim($sFields) );

            $sQuery = "INSERT INTO `Profiles` SET {$sFields}";
            $this -> query($sQuery);

            return db_last_id();
        }

        /**
         * Function will update  profile's status;
         *
         * @param  : $iProfileId (integer) - profile's Id;
         * @param  : $sStatus    (string)  - profile's status;
         * @return : void;
         */
        function updateProfileStatus($iProfileId, $sStatus)
        {
            $iProfileId = (int)$iProfileId;
            $sStatus	= process_db_input($sStatus);
            
            $sQuery = "UPDATE `Profiles` SET `Status` = '{$sStatus}' WHERE `ID` = {$iProfileId}";
            return $this -> query($sQuery);
        }

        /**
         * Function will check field name in 'Profiles` table;
         *
         * @param $sFieldName string
         * @return : (boolean);
         */
        function isFieldExist($sFieldName)
        {
            $sFieldName = process_db_input($sFieldName);
            $sQuery = "SELECT `ID` FROM `sys_profile_fields` WHERE `Name` = '{$sFieldName}' LIMIT 1";
            return $this -> getOne($sQuery) ? true : false;
        }

        /**
         * Check existing email
         *
         * @param $sEmail string
         * @return boolean
         */
        function isEmailExisting($sEmail)
        {
            $sEmail = process_db_input($sEmail, BX_TAGS_STRIP, BX_SLASHES_AUTO);

            $sQuery = "SELECT `ID` FROM `Profiles` WHERE `Email` = '{$sEmail}'";
            return $this -> getOne($sQuery);
        }

        /**
         * Get country's ISO code;
         *
         * @param : $sCountry (string) - country name;
         * @return: (string); - country ISO code;
         */
        function getCountryCode($sCountry)
        {
            $sCountry = process_db_input($sCountry);
            $sQuery = "SELECT `ISO2` FROM `sys_countries` WHERE `Country` = '{$sCountry}' LIMIT 1";
            return $this -> getOne($sQuery);
        }

        /**
         * Function will return category's id;
         *
         * @param  : $sCatName (string) - catregory's name;
         * @return : (integer) - category's id;
         */
        function getSettingsCategoryId($sCatName)
        {
            $sCatName = process_db_input($sCatName);
            return $this -> getOne('SELECT `kateg` FROM `sys_options` WHERE `Name` = "' . $sCatName . '"');
        }
    }
