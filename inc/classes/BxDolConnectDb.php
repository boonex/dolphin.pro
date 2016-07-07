<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

class BxDolConnectDb extends BxDolModuleDb
{
    var $sTablePrefix;

    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);

        $this -> sTablePrefix = $oConfig -> getDbPrefix();
    }
    
    /**
     * Check remote profile id
     *
     * @param $iRemoteId integer
     * @return local profile id
     */
    function getProfileId($iRemoteId)
    {
        $iRemoteId = (int) $iRemoteId;

        $sQuery = "SELECT `local_profile` FROM `{$this -> sTablePrefix}accounts` WHERE `remote_profile` = '{$iRemoteId}' LIMIT 1";
        return $this -> getOne($sQuery);
    }

    /**
     * Save new remote ID
     *
     * @param $iProfileId integer
     * @param $iRemoteId integer
     * @return bool
     */
    function saveRemoteId($iProfileId, $iRemoteId)
    {
        $iRemoteId = (int) $iRemoteId;
        $iProfileId = (int) $iProfileId;

        $sQuery = "REPLACE INTO `{$this -> sTablePrefix}accounts` SET `local_profile` = {$iProfileId}, `remote_profile` = '{$iRemoteId}'";
        return $this -> query($sQuery);
    }

    /**
     * Delete remote account
     *
     * @param $iProfileId integer
     * @return void
     */
    function deleteRemoteAccount($iProfileId)
    {
        $iProfileId = (int) $iProfileId;

        $sQuery = "DELETE FROM `{$this -> sTablePrefix}accounts` WHERE `local_profile` = {$iProfileId}";
        return $this -> query($sQuery);
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

        $sQuery = "INSERT INTO `sys_friend_list` SET `ID` = '{$iMemberId}', `Profile` = '{$iProfileId}', `Check` = 1";
        return $this -> query($sQuery);
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
}
