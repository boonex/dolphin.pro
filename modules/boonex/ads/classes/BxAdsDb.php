<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

class BxAdsDb extends BxDolDb
{
    var $_oConfig;

    /*
    * Constructor.
    */
    function __construct(&$oConfig)
    {
        parent::__construct();

        $this->_oConfig = $oConfig;
    }

    function getCategoryNameByID($iID)
    {
        $sNameSQL = "SELECT `Name` FROM `{$this->_oConfig->sSQLCatTable}` WHERE `ID` = '{$iID}'";
        return $this->getOne($sNameSQL);
    }
    function getCategoryNameByUri($sUri)
    {
        $sSafeUri = process_db_input($sUri);
        $sNameSQL = "SELECT `Name` FROM `{$this->_oConfig->sSQLCatTable}` WHERE `CEntryUri` = '{$sSafeUri}'";
        return $this->getOne($sNameSQL);
    }

    function getCatSubCatNameBySubCatID($iID)
    {
        $sNamesSQL = "
            SELECT `{$this->_oConfig->sSQLCatTable}`.`Name` , `{$this->_oConfig->sSQLSubcatTable}`.`NameSub`
            FROM `{$this->_oConfig->sSQLCatTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON ( `{$this->_oConfig->sSQLCatTable}`.`ID` = `{$this->_oConfig->sSQLSubcatTable}`.`IDClassified` )
            WHERE `{$this->_oConfig->sSQLSubcatTable}`.`ID` = ?
            LIMIT 1
        ";
        return $this->getRow($sNamesSQL, [$iID]);
    }
    function getCatSubCatNameBySubCatUri($sUri)
    {
        $sSafeUri = process_db_input($sUri);
        $sNamesSQL = "
            SELECT `{$this->_oConfig->sSQLCatTable}`.`Name` , `{$this->_oConfig->sSQLSubcatTable}`.`NameSub`
            FROM `{$this->_oConfig->sSQLCatTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON ( `{$this->_oConfig->sSQLCatTable}`.`ID` = `{$this->_oConfig->sSQLSubcatTable}`.`IDClassified` )
            WHERE `{$this->_oConfig->sSQLSubcatTable}`.`SEntryUri` = ?
            LIMIT 1
        ";
        return $this->getRow($sNamesSQL, [$sSafeUri]);
    }

    function getMemberAds($iMemberID)
    {
        $sMemberAdsSQL = "
            SELECT `ID` FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `IDProfile` = '{$iMemberID}'
        ";
        $vDelSQL = db_res($sMemberAdsSQL);
        return $vDelSQL;
    }

    function getMemberAdsCnt($iMemberID, $sStatus = '', $bTimeCheck = FALSE)
    {
            $sStatus = $sStatus ? " AND`Status`='" . process_db_input($sStatus, BX_TAGS_STRIP) . "'" : "";
            if ($bTimeCheck)
                $this->_oConfig->bAdminMode = FALSE;
            $sTimeRestriction = ($this->_oConfig->bAdminMode == true)
                ? ''
                : "AND UNIX_TIMESTAMP() - `{$this->_oConfig->sSQLPostsTable}`.`LifeTime`*24*60*60 < `{$this->_oConfig->sSQLPostsTable}`.`DateTime`";

            $sMemberAdsSQL = "
                SELECT COUNT(*) FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `IDProfile` = '{$iMemberID}'
                            {$sStatus}
                {$sTimeRestriction}
            ";
            return (int)db_value($sMemberAdsSQL);
        }

    function getOwnerOfAd($iID)
    {
        $sOwnerSQL = "
            SELECT `IDProfile`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `ID`='{$iID}'
        ";
        return (int)$this->getOne($sOwnerSQL);
    }

    function getMediaOfAd($iID)
    {
        $sMediaSQL = "
            SELECT `Media`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `ID`='{$iID}'
        ";
        return $this->getOne($sMediaSQL);
    }

    function getMediaFile($iID)
    {
        $sFileSQL = "
            SELECT `MediaFile`
            FROM `{$this->_oConfig->sSQLPostsMediaTable}`
            WHERE `MediaID` = '{$iID}'
        ";
        return $this->getOne($sFileSQL);
    }

    function deleteMedia($iMedId)
    {
        $sDeleteMediaSQL = "
            DELETE FROM `{$this->_oConfig->sSQLPostsMediaTable}`
            WHERE `MediaID` = '{$iMedId}'
            LIMIT 1
        ";
        return $this->query($sDeleteMediaSQL);
    }

    function deleteAd($iID)
    {
        $sDeleteSQL = "
            DELETE FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `ID` = '{$iID}'
            LIMIT 1
        ";
        return $this->query($sDeleteSQL);
    }

    function getMediaInfo($iMedId)
    {
        $sMediaSQL = "SELECT * FROM `{$this->_oConfig->sSQLPostsMediaTable}` WHERE `MediaID` = ?";
        return $this->getRow($sMediaSQL, [$iMedId]);
    }

    function getCatAndSubInfoBySubID($iSubCatID)
    {
        $sSQL = "
            SELECT `{$this->_oConfig->sSQLCatTable}`.`ID` AS 'ClassifiedsID', `{$this->_oConfig->sSQLCatTable}`.`Name`,
                `{$this->_oConfig->sSQLCatTable}`.`CEntryUri` , `{$this->_oConfig->sSQLSubcatTable}`.`ID` AS 'ClassifiedsSubsID',
                `{$this->_oConfig->sSQLSubcatTable}`.`NameSub` , `{$this->_oConfig->sSQLSubcatTable}`.`Description`
            FROM `{$this->_oConfig->sSQLCatTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON `{$this->_oConfig->sSQLCatTable}`.`ID` = `{$this->_oConfig->sSQLSubcatTable}`.`IDClassified`
            WHERE `{$this->_oConfig->sSQLSubcatTable}`.`ID` = ?
            LIMIT 1
        ";
        return $this->getRow($sSQL, [$iSubCatID]);
    }

    /**
     * SQL Get all Categories
     *
     * @return SQL data
     */
    function getAllCatsInfo()
    {
        $sSQL = "
            SELECT *
            FROM `{$this->_oConfig->sSQLCatTable}`
            ORDER BY `{$this->_oConfig->sSQLCatTable}`.`Name` ASC
        ";
        $vSqlRes = db_res($sSQL);
        return $vSqlRes;
    }

    function getAllSubCatsInfo($iID)
    {
        $sSQL = "
            SELECT * FROM `{$this->_oConfig->sSQLSubcatTable}`
            WHERE `IDClassified` = '{$iID}'
            ORDER BY `{$this->_oConfig->sSQLSubcatTable}`.`NameSub` ASC
        ";
        return db_res($sSQL);
    }

    function getCountOfAdsInSubCat($iID)
    {
        $sTimeRestriction = ($this->_oConfig->bAdminMode == true)
            ? ''
            : "AND UNIX_TIMESTAMP() - `{$this->_oConfig->sSQLPostsTable}`.`LifeTime`*24*60*60 < `{$this->_oConfig->sSQLPostsTable}`.`DateTime`";

        $sAdsCntSQL = "
            SELECT COUNT(`{$this->_oConfig->sSQLPostsTable}`.`ID`) AS 'Count'
            FROM `{$this->_oConfig->sSQLPostsTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON `{$this->_oConfig->sSQLPostsTable}`.`IDClassifiedsSubs` = `{$this->_oConfig->sSQLSubcatTable}`.`ID`
            WHERE `{$this->_oConfig->sSQLSubcatTable}`.`ID`='{$iID}'
            {$sTimeRestriction}";

        return (int)$this->getOne($sAdsCntSQL);
    }

    function getCountOfAdsInCat($iID)
    {
        $sAdsCntSQL = "
            SELECT COUNT(`{$this->_oConfig->sSQLPostsTable}`.`ID`) AS 'Count'
            FROM `{$this->_oConfig->sSQLCatTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON `{$this->_oConfig->sSQLCatTable}`.`ID` = `{$this->_oConfig->sSQLSubcatTable}`.`IDClassified`
            INNER JOIN `{$this->_oConfig->sSQLPostsTable}` ON `{$this->_oConfig->sSQLSubcatTable}`.`ID` = `{$this->_oConfig->sSQLPostsTable}`.`IDClassifiedsSubs`
            WHERE `{$this->_oConfig->sSQLCatTable}`.`ID` = '{$iID}'
        ";
        return (int)$this->getOne($sAdsCntSQL);
    }

    function insertMedia($iMemberID, $sBaseName, $sExt)
    {
        $sQuery = "INSERT INTO `{$this->_oConfig->sSQLPostsMediaTable}` SET
                    `MediaProfileID`='{$iMemberID}',
                    `MediaType`='photo',
                    `MediaFile`='{$sBaseName}{$sExt}',
                    `MediaDate`=UNIX_TIMESTAMP()";
        $vSqlRes = $this->query($sQuery);
        return $vSqlRes ? $this->lastId() : false;
    }

    function getFeaturedStatus($iID)
    {
        $sFeaturedStatusSQL = "SELECT `Featured` FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `ID`='{$iID}'";
        return (int)$this->getOne($sFeaturedStatusSQL);
    }

    function UpdateFeatureStatus($iID, $iStatus)
    {
        $sUpdateAdFeatureSQL = "
            UPDATE `{$this->_oConfig->sSQLPostsTable}` SET
            `Featured`='{$iStatus}'
            WHERE `ID`='{$iID}'
        ";
        return $this->query($sUpdateAdFeatureSQL);
    }

    function getAdInfo($iID)
    {
        $sAdInfoSQL = "
            SELECT
                `{$this->_oConfig->sSQLPostsTable}`.*, `{$this->_oConfig->sSQLCatTable}`.`CustomFieldName1`, `{$this->_oConfig->sSQLCatTable}`.`CustomFieldName2`,
                `{$this->_oConfig->sSQLSubcatTable}`.`NameSub`,
                `{$this->_oConfig->sSQLSubcatTable}`.`SEntryUri`, `{$this->_oConfig->sSQLSubcatTable}`.`ID` AS 'SubID', `{$this->_oConfig->sSQLCatTable}`.`Name`,
                `{$this->_oConfig->sSQLCatTable}`.`CEntryUri`, `{$this->_oConfig->sSQLCatTable}`.`ID` AS 'CatID', `{$this->_oConfig->sSQLCatTable}`.`Unit1`, `{$this->_oConfig->sSQLCatTable}`.`Unit2`,
                (UNIX_TIMESTAMP() - `{$this->_oConfig->sSQLPostsTable}`.`DateTime`) AS 'sec',
                `{$this->_oConfig->sSQLPostsTable}`.`DateTime` AS 'DateTime_UTS',
                `{$this->_oConfig->sSQLPostsTable}`.`IDProfile` AS 'OwnerID',
                `{$this->_oConfig->sSQLPostsTable}`.`Views`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON `{$this->_oConfig->sSQLPostsTable}`.`IDClassifiedsSubs`=`{$this->_oConfig->sSQLSubcatTable}`.`ID`
            INNER JOIN `{$this->_oConfig->sSQLCatTable}` ON `{$this->_oConfig->sSQLCatTable}`.`ID`=`{$this->_oConfig->sSQLSubcatTable}`.`IDClassified`
            WHERE `{$this->_oConfig->sSQLPostsTable}`.`ID`= ?
        ";

        return $this->getRow($sAdInfoSQL, [$iID]);
    }

    /**
     * SQL Get all Advertisement data, units take into mind LifeDate of Adv
     *
      * @param $iClsID
      * @param $sAddon - string addon of Limits (for pagination)
      * @param $bSub - present that current ID is SubCategory
     * @return SQL data
     */
    function getAdsByDate($iCatSubcatID, $sLimitAddon, $bSub = false)
    {
        $sWhereAdd = ($bSub) ? "`{$this->_oConfig->sSQLSubcatTable}`" : "`{$this->_oConfig->sSQLCatTable}`";
        $sTimeRestriction = ($this->_oConfig->bAdminMode == true) ? '' : "AND UNIX_TIMESTAMP() - `{$this->_oConfig->sSQLPostsTable}`.`LifeTime`*24*60*60 < `{$this->_oConfig->sSQLPostsTable}`.`DateTime`";
        $sSQL = "
            SELECT `{$this->_oConfig->sSQLPostsTable}`.* , `{$this->_oConfig->sSQLCatTable}`.`Name`, `{$this->_oConfig->sSQLCatTable}`.`Description`, `{$this->_oConfig->sSQLCatTable}`.`Unit1`, `{$this->_oConfig->sSQLCatTable}`.`Unit2`, (UNIX_TIMESTAMP() - `{$this->_oConfig->sSQLPostsTable}`.`DateTime`) AS 'sec',
            `{$this->_oConfig->sSQLPostsTable}`.`DateTime` AS 'DateTime_UTS'
            FROM `{$this->_oConfig->sSQLPostsTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON `{$this->_oConfig->sSQLPostsTable}`.`IDClassifiedsSubs` = `{$this->_oConfig->sSQLSubcatTable}`.`ID`
            INNER JOIN `{$this->_oConfig->sSQLCatTable}` ON `{$this->_oConfig->sSQLSubcatTable}`.`IDClassified` = `{$this->_oConfig->sSQLCatTable}`.`ID`
            WHERE {$sWhereAdd}.`ID` = '{$iCatSubcatID}'
            {$sTimeRestriction}
            ORDER BY `{$this->_oConfig->sSQLPostsTable}`.`DateTime` DESC
        ".$sLimitAddon;

        $vSqlRes = db_res ($sSQL);
        return $vSqlRes;
    }

    function getAdsByDateCnt($iCatSubcatID, $bSub = false)
    {
        $sWhereAdd = ($bSub) ? "`{$this->_oConfig->sSQLSubcatTable}`" : "`{$this->_oConfig->sSQLCatTable}`";
        $sTimeRestriction = ($this->_oConfig->bAdminMode == true) ? '' : "AND UNIX_TIMESTAMP() - `{$this->_oConfig->sSQLPostsTable}`.`LifeTime`*24*60*60 < `{$this->_oConfig->sSQLPostsTable}`.`DateTime`";
        $sSQL = "
            SELECT COUNT(`{$this->_oConfig->sSQLPostsTable}`.`ID`) AS 'Cnt'
            FROM `{$this->_oConfig->sSQLPostsTable}`
            INNER JOIN `{$this->_oConfig->sSQLSubcatTable}` ON `{$this->_oConfig->sSQLPostsTable}`.`IDClassifiedsSubs` = `{$this->_oConfig->sSQLSubcatTable}`.`ID`
            INNER JOIN `{$this->_oConfig->sSQLCatTable}` ON `{$this->_oConfig->sSQLSubcatTable}`.`IDClassified` = `{$this->_oConfig->sSQLCatTable}`.`ID`
            WHERE {$sWhereAdd}.`ID` = '{$iCatSubcatID}'
            {$sTimeRestriction}
        ";

        return (int)$this->getOne($sSQL);
    }

    function getAdUriByID($iID)
    {
        $sSQL = "
            SELECT `EntryUri`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `ID`='{$iID}'
        ";

        return $this->getOne($sSQL);
    }

    function getAdSubjectByID($iID)
    {
        $sSQL = "
            SELECT `Subject`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `ID`='{$iID}'
        ";

        return $this->getOne($sSQL);
    }
    function getAdSubjectByUri($sUri)
    {
        $sSQL = "
            SELECT `Subject`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `EntryUri`='{$sUri}'
        ";

        return $this->getOne($sSQL);
    }

    function getMemberAdsRSS($iPID)
    {
        $sUnitsSQL = "
                SELECT DISTINCT `{$this->_oConfig->sSQLPostsTable}`.`ID` AS 'UnitID',
                    `{$this->_oConfig->sSQLPostsTable}`.`IDProfile` AS 'OwnerID',
                    `{$this->_oConfig->sSQLPostsTable}`.`Subject` AS 'UnitTitle',
                    `{$this->_oConfig->sSQLPostsTable}`.`EntryUri` AS 'UnitUri',
                    `{$this->_oConfig->sSQLPostsTable}`.`Message` AS 'UnitDesc',
                    `{$this->_oConfig->sSQLPostsTable}`.`DateTime` AS 'UnitDateTimeUTS',
                        `{$this->_oConfig->sSQLPostsTable}`.`Media` AS 'UnitIcon'
                FROM `{$this->_oConfig->sSQLPostsTable}`
                WHERE `{$this->_oConfig->sSQLPostsTable}`.`Status` = 'active'
                AND `{$this->_oConfig->sSQLPostsTable}`.`IDProfile` = '{$iPID}'
                ORDER BY `{$this->_oConfig->sSQLPostsTable}`.`DateTime` DESC
                LIMIT 10
        ";

        $aRssUnits = $this->getAll($sUnitsSQL);
        return $aRssUnits;
    }

    function setPostStatus($iPostID, $sStatus = 'inactive')
    {
        $sUpdateSQL = "
            UPDATE `{$this->_oConfig->sSQLPostsTable}`
            SET `Status`='{$sStatus}'
            WHERE `ID`='{$iPostID}'
            LIMIT 1";
        $this->query($sUpdateSQL);
    }

    function updatePostMedia($iPostID, $sValue)
    {
        $sSQL = "
            UPDATE `{$this->_oConfig->sSQLPostsTable}` SET `Media` = '{$sValue}' WHERE `{$this->_oConfig->sSQLPostsTable}`.`ID` = {$iPostID} LIMIT 1
        ";
        return $this->query($sSQL);
    }

    function getSubsNameIDCountAdsByAdID($iCategoryID) { //for tree
        $sSubsSQL = "
            SELECT `{$this->_oConfig->sSQLSubcatTable}`.`ID`, `{$this->_oConfig->sSQLSubcatTable}`.`NameSub` AS `Name`,
            `{$this->_oConfig->sSQLSubcatTable}`.`SEntryUri`,
            COUNT(`{$this->_oConfig->sSQLPostsTable}`.`ID`) AS 'Count'
            FROM `{$this->_oConfig->sSQLSubcatTable}`
            LEFT JOIN `{$this->_oConfig->sSQLPostsTable}`
            ON (`{$this->_oConfig->sSQLPostsTable}`.`IDClassifiedsSubs` = `{$this->_oConfig->sSQLSubcatTable}`.`ID`)
            WHERE `{$this->_oConfig->sSQLSubcatTable}`.`IDClassified`='{$iCategoryID}'
            GROUP BY `Name`
        ";
        return db_res($sSubsSQL);
    }

    function getAdsByMonth($iYear, $iMonth, $iNextYear, $iNextMonth)
    {
        $sTimeRestriction = ($this->_oConfig->bAdminMode == true)
            ? ''
            : "AND UNIX_TIMESTAMP() - `{$this->_oConfig->sSQLPostsTable}`.`LifeTime`*24*60*60 < `{$this->_oConfig->sSQLPostsTable}`.`DateTime`";

        return $this->getAll ("
            SELECT `{$this->_oConfig->sSQLPostsTable}`.*, DAYOFMONTH(FROM_UNIXTIME(`{$this->_oConfig->sSQLPostsTable}`.`DateTime`)) AS `Day`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE
                `{$this->_oConfig->sSQLPostsTable}`.`DateTime` >= UNIX_TIMESTAMP('{$iYear}-{$iMonth}-1')
                AND `{$this->_oConfig->sSQLPostsTable}`.`DateTime` < UNIX_TIMESTAMP('{$iNextYear}-{$iNextMonth}-1')
                AND `{$this->_oConfig->sSQLPostsTable}`.`Status` = 'active'
                {$sTimeRestriction}
        ");
    }

    function getSettingsCategory()
    {
        return (int)$this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Ads' LIMIT 1");
    }

    function deleteCat($iID)
    {
        $sSQL = "
            DELETE FROM `{$this->_oConfig->sSQLCatTable}` WHERE `{$this->_oConfig->sSQLCatTable}`.`ID` = {$iID}
        ";
        return $this->query($sSQL);
    }

    function deleteSubCat($iID)
    {
        $sSQL = "
            DELETE FROM `{$this->_oConfig->sSQLSubcatTable}` WHERE `{$this->_oConfig->sSQLSubcatTable}`.`ID` = {$iID}
        ";
        return $this->query($sSQL);
    }
    function getSubcatInfo($iID)
    {
        $sSQL = "SELECT * FROM `{$this->_oConfig->sSQLSubcatTable}` WHERE `ID` = {$iID}";
        return $this->getAll($sSQL);
    }
    function getCatInfo($iID)
    {
        $sSQL = "SELECT * FROM `{$this->_oConfig->sSQLCatTable}` WHERE `ID` = {$iID}";
        return $this->getAll($sSQL);
    }
}
