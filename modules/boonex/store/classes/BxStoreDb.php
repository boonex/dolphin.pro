<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigModuleDb');

/*
 * Store module Data
 */

class BxStoreDb extends BxDolTwigModuleDb
{
    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);

        $this->_sTableMain        = 'products';
        $this->_sTableMediaPrefix = 'product_';
        $this->_sFieldId          = 'id';
        $this->_sFieldAuthorId    = 'author_id';
        $this->_sFieldUri         = 'uri';
        $this->_sFieldTitle       = 'title';
        $this->_sFieldDescription = 'desc';
        $this->_sFieldTags        = 'tags';
        $this->_sFieldThumb       = 'thumb';
        $this->_sFieldStatus      = 'status';
        $this->_sFieldFeatured    = 'featured';
        $this->_sFieldCreated     = 'created';
        $this->_sTableFans        = '';
        $this->_sTableAdmins      = '';
        $this->_sFieldAllowViewTo = 'allow_view_product_to';
    }

    function deleteEntryByIdAndOwner($iId, $iOwner, $isAdmin)
    {
        if ($iRet = parent::deleteEntryByIdAndOwner($iId, $iOwner, $isAdmin)) {
            $this->deleteEntryMediaAll($iId, 'images');
            $this->deleteEntryMediaAll($iId, 'videos');
            $this->deleteEntryMediaFileAll($iId, 'files');
        }

        return $iRet;
    }

    // media files

    function toggleProductFileVisibility($iFileId)
    {
        $a          = $this->getRow("SELECT `hidden`, `entry_id` FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` WHERE `id` = ?",
            [$iFileId]);
        $iHiddenNew = $a['hidden'] ? 0 : 1;
        if (!$this->query("UPDATE `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` SET `hidden` = $iHiddenNew WHERE `id` = ?",
            [$iFileId])
        ) {
            return false;
        }
        $this->updatePriceRange($a['entry_id']);

        return $iHiddenNew;
    }

    function updatePriceRange($iEntryId)
    {
        $aRange = $this->getRow("SELECT MIN(`price`) AS `min`, MAX(`price`) AS `max` FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` WHERE `entry_id` = ? AND `hidden` = 0",
            [$iEntryId]);
        if (!$aRange || ('' == $aRange['min'] && '' == $aRange['max'])) {
            $sPriceRange = '';
        } elseif (0 == $aRange['min'] && 0 == $aRange['max']) {
            $sPriceRange = 'Free';
        } elseif ($aRange['min'] == $aRange['max']) {
            $sPriceRange = '%s' . $aRange['min'];
        } else {
            $sPriceRange = '%s' . $aRange['min'] . '-' . '%s' . $aRange['max'];
        }
        $this->query("UPDATE `" . $this->_sPrefix . $this->_sTableMain . "` SET `price_range` = '$sPriceRange' WHERE `id` = '$iEntryId'");

        return $aRange;
    }

    function insertMediaFiles($iEntryId, $aMedia, $iProfileId)
    {
        $i = 0;
        foreach ($aMedia as $r) {
            $i += $this->query("INSERT INTO `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` VALUES (NULL, '$iProfileId', '$iEntryId', '{$r['id']}', '{$r['price']}', '{$r['privacy']}', 0)") ? 1 : 0;
        }
        if ($i) {
            $this->updatePriceRange($iEntryId);
        }

        return $i;
    }

    function deleteMediaFile($iMediaId, $sMediaType)
    {
        $aEntries = $this->getAll("SELECT `entry_id` FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "{$sMediaType}` WHERE `media_id` = '$iMediaId'");
        if (parent::deleteMediaFile($iMediaId, $sMediaType)) {
            $this->query("DELETE FROM `" . $this->_sPrefix . "customers` WHERE `file_id` = $iMediaId");
            if ($aEntries) {
                foreach ($aEntries as $r) {
                    $this->updatePriceRange($r['entry_id']);
                }
            }

            return true;
        }

        return false;
    }

    function deleteEntryMediaFileAll($iEntryId, $sMediaType)
    {
        $aMedia = $this->getAll("SELECT `media_id` FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "{$sMediaType}` WHERE `entry_id` = '$iEntryId'");
        foreach ($aMedia as $r) {
            $this->deleteMediaFile($r['media_id'], $sMediaType);
        }
    }

    function getFileInfo($iEntryId, $iMediaId)
    {
        return $this->getRow("SELECT `ti`.* FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` AS `ti` WHERE `media_id` = ? AND `entry_id` = ? LIMIT 1",
            [$iMediaId, $iEntryId]);
    }

    function getFileInfoByFileId($iId)
    {
        return $this->getRow("SELECT `ti`.*, `tp`.`{$this->_sFieldTitle}`, `tp`.`{$this->_sFieldUri}` 
               FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` AS `ti` LEFT JOIN `" . $this->_sPrefix . $this->_sTableMain . "`
               AS `tp` ON (`ti`.`entry_id` = `tp`.`{$this->_sFieldId}`) WHERE `ti`.`id` = ? LIMIT 1", [$iId]);
    }

    function getFiles($iEntryId, $isFilterHidden = false)
    {
        $sWhere = '';
        if ($isFilterHidden) {
            $sWhere = ' AND `hidden` = 0';
        }

        return $this->getAll("SELECT * FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` WHERE `entry_id` = ? $sWhere", [$iEntryId]);
    }

    function getFilesByAuthor($iAuthorId)
    {
        return $this->getAll("SELECT `ti`.*, `tp`.`{$this->_sFieldTitle}`, `tp`.`{$this->_sFieldUri}` 
               FROM `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` AS `ti` LEFT JOIN `" . $this->_sPrefix . $this->_sTableMain . "` 
               AS `tp` ON (`ti`.`entry_id` = `tp`.`id`) WHERE  `ti`.`{$this->_sFieldAuthorId}` = ?", [$iAuthorId]);
    }

    function registerCustomer($iClientId, $iItemId, $sOrderId, $iCount, $iDate)
    {
        return $this->query("INSERT INTO `" . $this->_sPrefix . "customers` SET `file_id` = '$iItemId', `client_id` = '$iClientId', `order_id` = '$sOrderId', `count` = '$iCount', `date` = '$iDate'");
    }

    function unregisterCustomer($iClientId, $iItemId, $sOrderId)
    {
        return $this->query("DELETE FROM `" . $this->_sPrefix . "customers` WHERE `file_id` = '$iItemId' AND `client_id` = '$iClientId' AND `order_id` = '$sOrderId'");
    }

    function isCustomer($iClientId, $iProductId)
    {
        return $this->query("SELECT 1 FROM `" . $this->_sPrefix . "customers` AS `tc` INNER JOIN `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` AS `tf` ON (`tf`.`id` = `tc`.`file_id` AND `tf`.`entry_id` = '$iProductId') WHERE `tc`.`client_id` = '$iClientId' LIMIT 1");
    }

    function isPurchasedItem($iClientId, $iFileId)
    {
        return $this->getOne("SELECT 1 FROM `" . $this->_sPrefix . "customers` WHERE `file_id` = '$iFileId' AND `client_id` = '$iClientId' LIMIT 1") ? true : false;
    }

    function removeCustomersFromAllEntries($iProfileId)
    {
        $iProfileId = (int)$iProfileId;
        if (!$iProfileId) {
            return false;
        }

        return $this->query("DELETE FROM `" . $this->_sPrefix . "customers` WHERE `client_id` = " . $iProfileId);
    }

    function getBroadcastRecipients($iProductId)
    {
        return $this->getAll("SELECT DISTINCT `p`.`ID`, `p`.`Email` FROM `" . $this->_sPrefix . "customers` AS `tc` INNER JOIN `" . $this->_sPrefix . $this->_sTableMediaPrefix . "files` AS `tf` ON (`tf`.`id` = `tc`.`file_id` AND `tf`.`entry_id` = '$iProductId') INNER JOIN `Profiles` as `p` ON (`p`.`ID` = `tc`.`client_id` AND `p`.`Status` = 'Active')");
    }
}
