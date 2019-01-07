<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

define('BX_WMAP_CAT_HOME', 'home');
define('BX_WMAP_CAT_ENTRY', 'entry');
define('BX_WMAP_CAT_EDIT', 'edit');

class BxWmapDb extends BxDolModuleDb
{
    var $_aCategs = array (
            BX_WMAP_CAT_HOME => 'World Map Home: {Part}',
            BX_WMAP_CAT_ENTRY => 'World Map Entry: {Part}',
            BX_WMAP_CAT_EDIT => 'World Map Edit Location: {Part}',
        );

    var $_aParts;

    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct();
        $this->_sPrefix = $oConfig->getDbPrefix();
    }

    function updateLocation ($iId, $sPart, $fLat, $fLng, $iZoom, $iType)
    {
        return $this->query ("INSERT INTO `" . $this->_sPrefix . "locations` SET `id` = '$iId', `part` = '$sPart', `ts` = UNIX_TIMESTAMP(), `lat` = '$fLat', `lng` = '$fLng', `zoom` = '$iZoom', `type` = '$iType' ON DUPLICATE KEY UPDATE `ts` = UNIX_TIMESTAMP(), `lat` = '$fLat', `lng` = '$fLng', `zoom` = '$iZoom', `type` = '$iType'");
    }

    function deleteLocation ($iId, $sPart)
    {
        return $this->query ("DELETE FROM `" . $this->_sPrefix . "locations` WHERE `id` = '$iId' AND `part` = '$sPart' LIMIT 1");
    }

    function updateLocationPrivacy ($iId, $mixedPrivacy = BX_WMAP_PRIVACY_DEFAULT)
    {
        return $this->query ("UPDATE `" . $this->_sPrefix . "locations` SET `privacy` = '$mixedPrivacy' WHERE `id` = '$iId'");
    }

    function insertLocation ($iId, $sPart, $sTitle, $sUri, $fLat, $fLng, $iMapZoom, $sMapType, $sAddress, $sCity, $sCountry, $mixedPrivacy = BX_WMAP_PRIVACY_DEFAULT, $isFailed = 0)
    {
        $sFields = '';
        if ($sAddress)
            $sFields .= ", `address` = '$sAddress' ";

        if ($sCity)
            $sFields .= ", `city`= '$sCity' ";

        return $this->query ("INSERT INTO `" . $this->_sPrefix . "locations` SET `id` = '$iId', `part` = '$sPart', `lat` = '$fLat', `lng` = '$fLng', `zoom` = '$iMapZoom', `type` = '$sMapType', `country`= '$sCountry', `title`= '$sTitle', `uri`= '$sUri', `ts` = UNIX_TIMESTAMP(), `privacy` = '$mixedPrivacy', `failed` = '$isFailed' $sFields ON DUPLICATE KEY UPDATE `lat` = '$fLat', `lng` = '$fLng', `zoom` = '$iMapZoom', `type` = '$sMapType', `country`= '$sCountry', `ts` = UNIX_TIMESTAMP(), `privacy` = '$mixedPrivacy', `failed` = '$isFailed' $sFields");
    }

    function getUndefinedLocations ($iLimit)
    {
        $aRet = array ();
        foreach ($this->_aParts as $sPart => $aPart) {
            $sFields = '';
            if ($aPart['join_field_country'])
                $sFields .= ", `p`.`{$aPart['join_field_country']}` AS `country` ";
            if ($aPart['join_field_city'])
                $sFields .= ", `p`.`{$aPart['join_field_city']}` AS `city` ";
            if ($aPart['join_field_state'])
                $sFields .= ", `p`.`{$aPart['join_field_state']}` AS `state` ";
            if ($aPart['join_field_zip'])
                $sFields .= ", `p`.`{$aPart['join_field_zip']}` AS `zip` ";
            if ($aPart['join_field_address'])
                $sFields .= ", `p`.`{$aPart['join_field_address']}` AS `address` ";
            if ($aPart['join_field_latitude'])
                $sFields .= ", `p`.`{$aPart['join_field_latitude']}` AS `latitude` ";
            if ($aPart['join_field_longitude'])
                $sFields .= ", `p`.`{$aPart['join_field_longitude']}` AS `longitude` ";
            if ($aPart['join_field_privacy'])
                $sFields .= ", `p`.`{$aPart['join_field_privacy']}` AS `privacy` ";

            $sSql = "SELECT '$sPart' AS `part`, `p`.`{$aPart['join_field_id']}` AS `id`, `p`.`{$aPart['join_field_title']}`
                     AS `title`, `p`.`{$aPart['join_field_uri']}` AS `uri` $sFields FROM `{$aPart['join_table']}` AS `p` 
                     LEFT JOIN `" . $this->_sPrefix . "locations` AS `m` ON (`m`.`id` = `p`.`{$aPart['join_field_id']}` AND `m`.`part` = '$sPart') 
                     WHERE ISNULL(`m`.`id`) LIMIT $iLimit";

            $a = $this->getAll ($sSql);
            $aRet = array_merge ($aRet, $a);
        }
        return $aRet;
    }

    function getDirectLocation ($iEntryId, $aPart, $bProcessHidden = false)
    {
        $sPart = $aPart['part'];

        $sFields = '';
        if ($aPart['join_field_country'])
            $sFields .= ", `p`.`{$aPart['join_field_country']}` AS `country` ";
        if ($aPart['join_field_city'])
            $sFields .= ", `p`.`{$aPart['join_field_city']}` AS `city` ";
        if ($aPart['join_field_state'])
            $sFields .= ", `p`.`{$aPart['join_field_state']}` AS `state` ";
        if ($aPart['join_field_zip'])
            $sFields .= ", `p`.`{$aPart['join_field_zip']}` AS `zip` ";
        if ($aPart['join_field_address'])
            $sFields .= ", `p`.`{$aPart['join_field_address']}` AS `address` ";
        if ($aPart['join_field_latitude'])
            $sFields .= ", `p`.`{$aPart['join_field_latitude']}` AS `latitude` ";
        if ($aPart['join_field_longitude'])
            $sFields .= ", `p`.`{$aPart['join_field_longitude']}` AS `longitude` ";
        if ($aPart['join_field_privacy'])
            $sFields .= ", `p`.`{$aPart['join_field_privacy']}` AS `privacy` ";

        $sSql = "SELECT '$sPart' AS `part`, `p`.`{$aPart['join_field_id']}` AS `id`, `p`.`{$aPart['join_field_title']}` AS `title`, `p`.`{$aPart['join_field_uri']}` AS `uri`, `p`.`{$aPart['join_field_author']}` AS `author_id`, `l`.`lat`, `l`.`lng`, `l`.`zoom`, `l`.`type` $sFields
            FROM `{$aPart['join_table']}` AS `p`
            LEFT JOIN `" . $this->_sPrefix . "locations` AS `l` ON (`l`.`id` = `p`.`{$aPart['join_field_id']}` AND `l`.`part` = '$sPart')
            WHERE `p`.`{$aPart['join_field_id']}` = ? " . ($bProcessHidden ? $aPart['join_where'] : '') . " LIMIT 1";

        return $this->getRow ($sSql, [$iEntryId]);
    }

    function clearLocations ($sPart, $isClearFailedOnly)
    {
        $sWhere = '';
        if ($isClearFailedOnly) {
            $sWhere = " AND `failed` != 0 ";
        }
        if ($ret = $this->query ("DELETE FROM `" . $this->_sPrefix . "locations` WHERE `part` = '$sPart' $sWhere")) {
            $this->query ("OPTIMIZE TABLE `" . $this->_sPrefix . "locations`");
        }
        return $ret;
    }

    function getLocationById($sPart, $iProfileId)
    {
        return $this->getRow("SELECT `m`.`id`, `m`.`part`, `m`.`lat`, `m`.`lng`, `m`.`zoom`, `m`.`type`, `m`.`address`, `m`.`country`, `m`.`allow_view_location_to` 
               FROM `" . $this->_sPrefix . "locations` AS `m` WHERE `m`.`failed` = 0 AND `p`.`Status` = 'Active' 
               AND `m`.`id` = ? AND `m`.`part` = ? LIMIT 1", [$iProfileId, $sPart]); // INNER JOIN to profiles was removed here
    }

    function getLocationsByBounds($sPart, $fLatMin, $fLatMax, $fLngMin, $fLngMax, $aCustomParts, $mixedPrivacyIds = '')
    {
        $sCustomPartsCondition = $this->_getCustomPartsCondition ($aCustomParts, 'm');

        $sWhere = $this->_getLatLngWhere ($fLatMin, $fLatMax, $fLngMin, $fLngMax);
        if ($sPart)
            $sWhere .= " AND `m`.`part` = '$sPart' ";
        else
            $sWhere .= $sCustomPartsCondition;

        $sWhere .= $this->_getPrivacyCondition($mixedPrivacyIds, 'm');

        return $this->getAll("SELECT `m`.`id`, `m`.`part`, `m`.`title`, `m`.`uri`, `m`.`lat`, `m`.`lng` FROM `" . $this->_sPrefix . "locations` AS `m` WHERE `m`.`failed` = 0 $sWhere ORDER BY `m`.`id` DESC LIMIT 100");
    }

    function _getLatLngWhere ($fLatMin, $fLatMax, $fLngMin, $fLngMax)
    {
        $sWhere = " AND `m`.`lat` < $fLatMax AND `m`.`lat` > $fLatMin ";
        if ($fLngMin < $fLngMax)
            $sWhere .= " AND `m`.`lng` < $fLngMax AND `m`.`lng` > $fLngMin ";
        else
            $sWhere .= " AND ((`m`.`lng` < $fLngMax AND `m`.`lng` > -180) OR (`m`.`lng` < 180 AND `m`.`lng` > $fLngMin)) ";
        return $sWhere;
    }

    function getSettingsCategory($s)
    {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = '$s' LIMIT 1");
    }

    function getParts ()
    {
        return $this->getAllWithKey ("SELECT * FROM `" . $this->_sPrefix . "parts` WHERE `enabled` != 0", 'part');
    }

    function enablePart ($sPart, $isEnable)
    {
        return $this->query ("UPDATE `" . $this->_sPrefix . "parts` SET `enabled` = '{$isEnable}' WHERE `part` = '$sPart'");
    }

    function addPart($aOptions)
    {
        $sQuery = "INSERT INTO `" . $this->_sPrefix . "parts` SET ";
        foreach ($aOptions as $sField => $sValue)
            $sQuery .= "`$sField` = {$this->escape($sValue)},";
        $sQuery = trim ($sQuery, ', ');
        if (!$this->query($sQuery))
            return false;

        if (!$this->_addPartSettings($aOptions['part'])) {
            $this->removePart($aOptions['part']);
            return false;
        }

        return true;
    }

    function updatePart($sPart, $aOptions)
    {
        $sQuery = "UPDATE `" . $this->_sPrefix . "parts` SET ";
        foreach ($aOptions as $sField => $sValue)
            $sQuery .= "`$sField` = {$this->escape($sValue)},";
        $sQuery = trim ($sQuery, ', ');
        $sQuery .= " WHERE `part` = ?";
        if (!$this->query($sQuery, [$sPart]))
            return false;

        return true;
    }

    function removePart($sPart)
    {
        $this->_removePartSettings($sPart);
        return $this->query ("DELETE FROM `" . $this->_sPrefix . "parts` WHERE `part` = '$sPart'");
    }

    function _getPartsJoinCount ($aCustomParts)
    {
        $sPartsJoin = '';
        $sPatrsCounts = '';
        foreach ($this->_aParts as $sPart => $a) {
            if (!isset($aCustomParts[$sPart]))
                continue;
            $sPatrsCounts .= " , COUNT(DISTINCT `pm_$sPart`.`id`) AS `num_$sPart` ";
            $sPartsJoin .= " LEFT JOIN `" . $this->_sPrefix . "locations` AS `pm_$sPart` ON (`pm_$sPart`.`failed` = 0 AND `pm_$sPart`.`id` = `pm`.`id` AND `pm_$sPart`.`part` = `pm`.`part` AND `pm_$sPart`.`part` = '$sPart') \n";
        }
        return array ($sPatrsCounts, $sPartsJoin);
    }

    function _getCustomPartsCondition ($aCustomParts, $sTableAlias)
    {
        $sRet = ' AND (';
        foreach ($aCustomParts as $sPart)
            $sRet .= "`$sTableAlias`.`part` = '$sPart' OR ";
        $sRet = substr ($sRet, 0, -4);
        $sRet .= ')';
        return $sRet;
    }

    function _getPrivacyCondition($mixedPrivacyIds, $sTableAlias)
    {
        if (!$mixedPrivacyIds)
            return '';
        if (!is_array($mixedPrivacyIds))
            $mixedPrivacyIds = array ($mixedPrivacyIds);
        $sRet = ' AND (';
        foreach ($mixedPrivacyIds as $iPrivacyId)
            $sRet .= "`$sTableAlias`.`privacy` = '$iPrivacyId' OR ";
        $sRet = substr ($sRet, 0, -4);
        $sRet .= ')';
        return $sRet;
    }

    function _removePartSettings($sPart)
    {
        foreach ($this->_aCategs as $sType => $sCateg) {
            $sCateg = str_replace('{Part}', ucfirst($sPart), $sCateg);
            $iCategId = (int)$this->getSettingsCategory($sCateg);
            if (!$iCategId)
                continue;
            $this->query("DELETE FROM `sys_options_cats` WHERE `ID` = " . $iCategId);
            $this->query("DELETE FROM `sys_options` WHERE `kateg` = " . $iCategId);
        }

        $sHiddenSettingsSql = "DELETE FROM `sys_options` WHERE `Name` = 'bx_wmap_home_{part}_lat' OR `Name` = 'bx_wmap_home_{part}_lng' OR `Name` = 'bx_wmap_home_{part}_zoom' OR `Name` = 'bx_wmap_home_{part}_map_type'";
        $sHiddenSettingsSql = str_replace('{part}', $sPart, $sHiddenSettingsSql);
        $this->query($sHiddenSettingsSql);

        return true;
    }

    function _addPartSettings($sPart)
    {
        $aSettings = array (
            'bx_wmap_{type}_{part}_control_type' => array (
                'title' => 'Map control type',
                'type' => 'select',
                'values' => 'none,small,large',
                'defaults' => array (BX_WMAP_CAT_HOME => 'large', BX_WMAP_CAT_ENTRY => 'small', BX_WMAP_CAT_EDIT => 'small'),
            ),
            'bx_wmap_{type}_{part}_is_type_control' => array (
                'title' => 'Display map type controls',
                'type' => 'checkbox',
                'values' => '',
                'defaults' => array (BX_WMAP_CAT_HOME => 'on', BX_WMAP_CAT_ENTRY => 'on', BX_WMAP_CAT_EDIT => 'on'),
            ),
            'bx_wmap_{type}_{part}_is_scale_control' => array (
                'title' => 'Display map scale control',
                'type' => 'checkbox',
                'values' => '',
                'defaults' => array (BX_WMAP_CAT_HOME => 'on', BX_WMAP_CAT_ENTRY => '', BX_WMAP_CAT_EDIT => ''),
            ),
            'bx_wmap_{type}_{part}_is_overview_control' => array (
                'title' => 'Display map overview control',
                'type' => 'checkbox',
                'values' => '',
                'defaults' => array (BX_WMAP_CAT_HOME => 'on', BX_WMAP_CAT_ENTRY => '', BX_WMAP_CAT_EDIT => ''),
            ),
            'bx_wmap_{type}_{part}_is_map_dragable' => array (
                'title' => 'Is map dragable?',
                'type' => 'checkbox',
                'values' => '',
                'defaults' => array (BX_WMAP_CAT_HOME => 'on', BX_WMAP_CAT_ENTRY => 'on', BX_WMAP_CAT_EDIT => 'on'),
            ),
            'bx_wmap_{type}_{part}_zoom' => array (
                'title' => 'Default zoom',
                'type' => 'digit',
                'values' => '',
                'defaults' => array (BX_WMAP_CAT_HOME => false, BX_WMAP_CAT_ENTRY => '10', BX_WMAP_CAT_EDIT => 10),
            ),
            'bx_wmap_{type}_{part}_map_type' => array (
                'title' => 'Default map type',
                'type' => 'select',
                'values' => 'normal,satellite,hybrid,terrain',
                'defaults' => array (BX_WMAP_CAT_HOME => false, BX_WMAP_CAT_ENTRY => 'normal', BX_WMAP_CAT_EDIT => 'normal'),
            ),
        );

        $iOrderCateg = $this->getOne("SELECT `menu_order` FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1");

        foreach ($this->_aCategs as $sType => $sCateg) {

            $sCateg = str_replace('{Part}', ucfirst($sPart), $sCateg);
            if (!$this->query("INSERT INTO `sys_options_cats` SET `name` = {$this->escape($sCateg)}, `menu_order` = " . (++$iOrderCateg)))
                return false;

            $iCategId = $this->lastId();
            $iOrderInCateg = 0;

            foreach ($aSettings as $sName => $aFields) {

                if (false === $aFields['defaults'][$sType])
                    continue;

                $sName = str_replace('{part}', $sPart, $sName);
                $sName = str_replace('{type}', $sType, $sName);

                $bRes = $this->query("INSERT INTO `sys_options` SET
                    `Name` = ?,
                    `VALUE` = ?,
                    `kateg` = ?,
                    `desc` = ?,
                    `Type` = ?,
                    `order_in_kateg` = ?,
                    `AvailableValues` = ?",
                    [
                        $sName,
                        $aFields['defaults'][$sType],
                        $iCategId,
                        $aFields['title'],
                        $aFields['type'],
                        ++$iOrderInCateg,
                        $aFields['values']
                    ]
                );

                if (!$bRes)
                    return false;
            }
        }

        $iCategHiddenId = (int)$this->getSettingsCategory('World Map Hidden');

        $sHiddenSettingsSql = "INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
            ('bx_wmap_home_{part}_lat', '20', {categ_id}, 'Home map latitude: {Part}', 'digit', '', '', '0', ''),
            ('bx_wmap_home_{part}_lng', '35', {categ_id}, 'Home map longitude: {Part}', 'digit', '', '', '0', ''),
            ('bx_wmap_home_{part}_zoom', '2', {categ_id}, 'Home map zoom: {Part}', 'digit', '', '', '0', ''),
            ('bx_wmap_home_{part}_map_type', 'normal', {categ_id}, 'Home map type: {Part}', 'digit', '', '', '0', '')";

        $sHiddenSettingsSql = str_replace('{part}', $sPart, $sHiddenSettingsSql);
        $sHiddenSettingsSql = str_replace('{Part}', ucfirst($sPart), $sHiddenSettingsSql);
        $sHiddenSettingsSql = str_replace('{categ_id}', $iCategHiddenId, $sHiddenSettingsSql);

        if (!$this->query($sHiddenSettingsSql))
            return false;

        return true;
    }

}
