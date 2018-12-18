<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolDb.php' );

class BxBlogsDb extends BxDolDb
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

    function getMembershipActions()
    {
        $sSql = "SELECT `ID` AS `id`, `Name` AS `name` FROM `sys_acl_actions` WHERE `Name`='use blog' OR `Name`='view blog'";
        return $this->getAll($sSql);
    }

    function getPostCaptionByID($iPostID)
    {
        $sSQL = "
            SELECT `PostCaption`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `PostID`='{$iPostID}'
        ";
        return $this->getOne($sSQL);
    }

    function getPostCaptionAndUriByID($iPostID)
    {
        $sSQL = "
            SELECT `PostCaption`, `PostUri`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `PostID`= ?
        ";
        return $this->getRow($sSQL, [$iPostID]);
    }

    function getPostCaptionByUri($sPostUri)
    {
        $sSQL = "
            SELECT `PostCaption`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `PostUri`='{$sPostUri}'
        ";
        return $this->getOne($sSQL);
    }

    function getAllBlogsCnt($sStatusFilter)
    {
        $sBlogsSQL = "
            SELECT COUNT(DISTINCT(`{$this->_oConfig->sSQLBlogsTable}`.`ID`)) AS 'Cnt'
            FROM `{$this->_oConfig->sSQLBlogsTable}`
            INNER JOIN `{$this->_oConfig->sSQLPostsTable}` ON `{$this->_oConfig->sSQLPostsTable}`.`OwnerID` = `{$this->_oConfig->sSQLBlogsTable}`.`OwnerID`
            WHERE {$sStatusFilter}
        ";
        return $this->getOne($sBlogsSQL);
    }

    function getTopBlogs($sStatusFilter, $sqlLimit)
    {
        $sBlogsSQL = "
            SELECT `{$this->_oConfig->sSQLBlogsTable}`.`ID`, `{$this->_oConfig->sSQLBlogsTable}`.`OwnerID`, `{$this->_oConfig->sSQLBlogsTable}`.`Description`, COUNT(`{$this->_oConfig->sSQLPostsTable}`.`PostID`) AS 'PostCount'
            FROM `{$this->_oConfig->sSQLBlogsTable}`
            INNER JOIN `{$this->_oConfig->sSQLPostsTable}` ON `{$this->_oConfig->sSQLPostsTable}`.`OwnerID` = `{$this->_oConfig->sSQLBlogsTable}`.`OwnerID`
            WHERE {$sStatusFilter}
            GROUP BY `{$this->_oConfig->sSQLBlogsTable}`.`ID`
            ORDER BY `PostCount` DESC
            {$sqlLimit}
        ";
        $vBlogsRes = db_res($sBlogsSQL);
        return $vBlogsRes;
    }

    function getLastBlogs($sStatusFilter, $sqlLimit)
    {
        $sBlogsSQL = "
            SELECT `{$this->_oConfig->sSQLBlogsTable}`.`ID`, `{$this->_oConfig->sSQLBlogsTable}`.`OwnerID`, `{$this->_oConfig->sSQLBlogsTable}`.`Description`, COUNT(`{$this->_oConfig->sSQLPostsTable}`.`PostID`) AS 'PostCount'
            FROM `{$this->_oConfig->sSQLBlogsTable}`
            INNER JOIN `{$this->_oConfig->sSQLPostsTable}` ON `{$this->_oConfig->sSQLPostsTable}`.`OwnerID` = `{$this->_oConfig->sSQLBlogsTable}`.`OwnerID`
            WHERE {$sStatusFilter}
            GROUP BY `{$this->_oConfig->sSQLBlogsTable}`.`ID`
            ORDER BY `PostDate` DESC
            {$sqlLimit}
        ";

        $vBlogsRes = db_res( $sBlogsSQL );
        return $vBlogsRes;
    }

    function getTagsInfo($iMemberID, $sStatusFilter, $sCategoryName)
    {
        $sCategJoin = $sCategFilter = '';
        if ($sCategoryName != '') {
            $sCategJoin = "
                LEFT JOIN `{$this->_oConfig->sSQLCategoriesTable}` ON `{$this->_oConfig->sSQLCategoriesTable}`.`ID` = `{$this->_oConfig->sSQLPostsTable}`.`PostID`
            ";
            $sCategFilter = "
                AND `{$this->_oConfig->sSQLCategoriesTable}`.`Category` = '{$sCategoryName}' AND {$this->_oConfig->sSQLCategoriesTable}`.`Type`='bx_blogs'
            ";
        }

        $sPostsSQL = "
            SELECT
                `Tags`,`OwnerID`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            {$sCategJoin}
            WHERE
                `{$this->_oConfig->sSQLPostsTable}`.`OwnerID` = {$iMemberID}
                {$sCategFilter}
                {$sStatusFilter}
        ";
        $vTags = db_res($sPostsSQL);
        return $vTags;
    }

    function getPostsInCategory($sStatusFilter, $sCategoryName, $iOwnerID)
    {
        $sCategJoin = $sCategFilter = '';
        if ($sCategoryName != '') {
            $sCategJoin = "
                LEFT JOIN `{$this->_oConfig->sSQLCategoriesTable}` ON `{$this->_oConfig->sSQLCategoriesTable}`.`ID` = `{$this->_oConfig->sSQLPostsTable}`.`PostID`
            ";
            $sCategFilter = "
                AND `{$this->_oConfig->sSQLCategoriesTable}`.`Category` = '{$sCategoryName}' AND `{$this->_oConfig->sSQLCategoriesTable}`.`Type`='bx_blogs'
            ";
        }
        $sPostsSQL = "
            SELECT
                `PostID`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            {$sCategJoin}
            WHERE
                {$sStatusFilter}
                {$sCategFilter}
                AND `{$this->_oConfig->sSQLPostsTable}`.`OwnerID`='{$iOwnerID}'
            ORDER BY `PostDate` ASC
        ";
        $vPostsInCat = db_res($sPostsSQL);

        $aPosts = array();
        while ($aPost = $vPostsInCat->fetch()) {
            $aPosts[] = (int)$aPost['PostID'];
        }
        return $aPosts;
    }

    function getPostsCntInCategory($sCategoryName, $sStatusFilter, $iOwnerID)
    {
        $sCategJoin = "
            LEFT JOIN `{$this->_oConfig->sSQLCategoriesTable}` ON `{$this->_oConfig->sSQLCategoriesTable}`.`ID` = `{$this->_oConfig->sSQLPostsTable}`.`PostID`
        ";
        $sCategFilter = "
            AND `{$this->_oConfig->sSQLCategoriesTable}`.`Category` = '{$sCategoryName}' AND `{$this->_oConfig->sSQLCategoriesTable}`.`Type`='bx_blogs'
        ";

        $sCountPostCatSQL = "
            SELECT COUNT(*)
            FROM `{$this->_oConfig->sSQLPostsTable}`
            {$sCategJoin}
            WHERE 1
            {$sCategFilter}
            AND `{$this->_oConfig->sSQLPostsTable}`.`OwnerID`='{$iOwnerID}'
            {$sStatusFilter}
        ";

        $iCountCatPost = (int)$this->getOne($sCountPostCatSQL);
        return $iCountCatPost;
    }

    function getFeaturedPosts($iMemberID)
    {
        $sFeaturedSQL = "
            SELECT `{$this->_oConfig->sSQLPostsTable}`.*
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `{$this->_oConfig->sSQLPostsTable}`.`OwnerID` = {$iMemberID} AND `{$this->_oConfig->sSQLPostsTable}`.`Featured`='1'
            ORDER BY `PostDate` DESC
        ";
        $vFeaturedPosts = db_res($sFeaturedSQL);
        return $vFeaturedPosts;
    }

    function setPostStatus($iPostID, $sStatus = 'disapproval')
    {
        $sUpdateSQL = "
            UPDATE `{$this->_oConfig->sSQLPostsTable}`
            SET `PostStatus`='{$sStatus}'
            WHERE `PostID`='{$iPostID}'
            LIMIT 1";
        return $this->query($sUpdateSQL);
    }

    function getBlogInfo($iMemberID)
    {
        $sBlogsSQL = "
            SELECT * FROM `{$this->_oConfig->sSQLBlogsTable}`
            WHERE `{$this->_oConfig->sSQLBlogsTable}`.`OwnerID` = ?
            LIMIT 1
        ";

        return $this->getRow($sBlogsSQL, [$iMemberID]);
    }

    function getPostOwnerByID($iPostID)
    {
        $sCheckPostSQL = "
            SELECT `{$this->_oConfig->sSQLPostsTable}`.`OwnerID`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `PostID`='{$iPostID}'
        ";
        $iOwnerID = $this->getOne($sCheckPostSQL);
        return $iOwnerID;
    }

    function getOwnerByBlogID($iBlogID)
    {
        $sCheckSQL = "
            SELECT `OwnerID`
            FROM `{$this->_oConfig->sSQLBlogsTable}`
            WHERE `ID`='{$iBlogID}'
        ";
        $iOwnerID = $this->getOne($sCheckSQL);
        return $iOwnerID;
    }

    function getPostPhotoByID($iPostID)
    {
        $sPhotosSQL = "SELECT `PostPhoto` FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `PostID` = '{$iPostID}' LIMIT 1";
        $sFileName = $this->getOne($sPhotosSQL);
        return $sFileName;
    }

    function performUpdatePostWithPhoto($iPostID, $sPhotoFilename = '')
    {
        $sUpdateSQL = "
            UPDATE `{$this->_oConfig->sSQLPostsTable}` SET
            `PostPhoto`='{$sPhotoFilename}'
            WHERE `PostID`='{$iPostID}'
        ";

        $vSqlRes = db_res($sUpdateSQL);
        return $vSqlRes;
    }

    function deletePost($iPostID)
    {
        $sDelSQL = "DELETE FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `{$this->_oConfig->sSQLPostsTable}`.`PostID` = '{$iPostID}' LIMIT 1";
        $vSqlRes = db_res($sDelSQL);
        return $vSqlRes;
    }

    function getPostIDByUri($sPostUri)
    {
        $sPostIdSQL = "SELECT `PostID` FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `PostUri`='{$sPostUri}'";
        $iPostID = (int)$this->getOne($sPostIdSQL);
        return $iPostID;
    }
    function getPostUriByID($iPostID)
    {
        $sPostUriSQL = "SELECT `PostUri` FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `PostID`='{$iPostID}'";
        $sPostUri = $this->getOne($sPostUriSQL);
        return $sPostUri;
    }

    function getPostInfo($iPostID)
    {
        $sAllBlogPostInfoSQL = "
            SELECT `{$this->_oConfig->sSQLPostsTable}`. * , `{$this->_oConfig->sSQLPostsTable}`.`PostCaption`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `{$this->_oConfig->sSQLPostsTable}`.`PostID` = ?
            LIMIT 1
        ";

        $aAllBlogPostInfo = $this->getRow($sAllBlogPostInfoSQL, [$iPostID]);
        return $aAllBlogPostInfo;
    }

    function getJustPostInfo($iPostID)
    {
        $sBlogPostsSQL = "SELECT * FROM `{$this->_oConfig->sSQLPostsTable}` WHERE `PostID` = ? LIMIT 1";
        $aBlogPost = $this->getRow($sBlogPostsSQL, [$iPostID]);
        return $aBlogPost;
    }

    function getFeaturedStatus($iPostID)
    {
        $sCheckSQL = "
            SELECT `Featured`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `PostID`='{$iPostID}'
        ";
        $iFeatured = $this->getOne($sCheckSQL);
        return $iFeatured;
    }

    function getActiveStatus($iPostID)
    {
        $sCheckSQL = "
            SELECT `PostStatus`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE `PostID`='{$iPostID}'
        ";
        $sStatus = $this->getOne($sCheckSQL);
        return $sStatus;
    }

    function performUpdateFeatureStatus($aParams)
    {
        $iPostID = $aParams['postID'];
        $sStatus = $aParams['status'];

        $sUpdateSQL = "
            UPDATE `{$this->_oConfig->sSQLPostsTable}`
            SET
                `Featured`='{$sStatus}'
            WHERE
                `PostID`='{$iPostID}'
        ";
        $this->query($sUpdateSQL);
    }

    function performUpdateBlog($aParams)
    {
        $iBlogID = $aParams['blogID'];
        $sDesc = $aParams['description'];

        $sUpdateSQL = "
            UPDATE `{$this->_oConfig->sSQLBlogsTable}`
            SET
                `Description` = '{$sDesc}'
            WHERE
                `{$this->_oConfig->sSQLBlogsTable}`.`ID` = '{$iBlogID}'
            LIMIT 1
        ";
        $this->query($sUpdateSQL);
    }

    function deleteBlog($iBlogID)
    {
        $sDelSQL = "DELETE FROM `{$this->_oConfig->sSQLBlogsTable}` WHERE `ID` = '{$iBlogID}'";
        $this->query($sDelSQL);
    }

    function getMemberIDByNickname($sNickName)
    {
        $sCheckSQL = "SELECT `ID` FROM `Profiles` WHERE `NickName`='{$sNickName}'";
        $iMemberID = (int)$this->getOne($sCheckSQL);
        return $iMemberID;
    }

    function getMemberPostsRSS($iPID)
    {
        $sUnitsSQL = "
                SELECT DISTINCT `{$this->_oConfig->sSQLPostsTable}`.`PostID` AS 'UnitID',
                    `{$this->_oConfig->sSQLPostsTable}`.`OwnerID`,
                    `{$this->_oConfig->sSQLPostsTable}`.`PostCaption` AS 'UnitTitle',
                    `{$this->_oConfig->sSQLPostsTable}`.`PostUri` AS 'UnitUri',
                    `{$this->_oConfig->sSQLPostsTable}`.`PostText` AS 'UnitDesc',
                    `PostDate` AS 'UnitDateTimeUTS',
                    `{$this->_oConfig->sSQLPostsTable}`.`PostPhoto` AS 'UnitIcon'
                FROM `{$this->_oConfig->sSQLPostsTable}`
                WHERE `{$this->_oConfig->sSQLPostsTable}`.`PostStatus` = 'approval'
                AND `{$this->_oConfig->sSQLPostsTable}`.`OwnerID` = '{$iPID}'
                ORDER BY `{$this->_oConfig->sSQLPostsTable}`.`PostDate` DESC
                LIMIT 10
        ";
        $aRssUnits = $this->getAll($sUnitsSQL);
        return $aRssUnits;
    }

    function getBlogPostsByMonth($iYear, $iMonth, $iNextYear, $iNextMonth, $sStatus = 'approval')
    {
        $sExtra = !empty($sStatus) ? " AND `{$this->_oConfig->sSQLPostsTable}`.`PostStatus` = " . $this -> escape($sStatus) : '';

        return $this->getAll ("
            SELECT `{$this->_oConfig->sSQLPostsTable}`.*, DAYOFMONTH(FROM_UNIXTIME(`{$this->_oConfig->sSQLPostsTable}`.`PostDate`)) AS `Day`
            FROM `{$this->_oConfig->sSQLPostsTable}`
            WHERE
                `{$this->_oConfig->sSQLPostsTable}`.`PostDate` >= UNIX_TIMESTAMP('{$iYear}-{$iMonth}-1')
                AND `{$this->_oConfig->sSQLPostsTable}`.`PostDate` < UNIX_TIMESTAMP('{$iNextYear}-{$iNextMonth}-1')
                $sExtra
        ");
    }

    function getMemberPostsCnt($iPID)
    {
        $sUnitsSQL = "
                SELECT COUNT(`{$this->_oConfig->sSQLPostsTable}`.`PostID`)
                FROM `{$this->_oConfig->sSQLPostsTable}`
                WHERE `{$this->_oConfig->sSQLPostsTable}`.`PostStatus` = 'approval'
                AND `{$this->_oConfig->sSQLPostsTable}`.`OwnerID` = '{$iPID}'
        ";
        return (int)$this->getOne($sUnitsSQL);
    }

    function getSettingsCategory()
    {
        return (int)$this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Blogs' LIMIT 1");
    }
}
