<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

class BxDolPrivacyQuery extends BxDolDb
{
    var $_sTable;
    var $_sFieldId;
    var $_sFieldOwnerId;

    var $_sGroupCache;
    var $_sGroupsByOwnersCache;
    var $_sGroupMembersCache;
    var $_sObjectCache;
    var $_sActionCache;
    var $_sActionDefaultCache;

    /**
     * constructor
     */
    function __construct($sTable = '', $sFieldId = '', $sFieldOwnerId = '')
    {
        parent::__construct();

        $this->_sTable = $sTable;
        $this->_sFieldId = $sFieldId;
        $this->_sFieldOwnerId = $sFieldOwnerId;

        $this->_sGroupCache = 'sys_ps_group_';
        $this->_sGroupsByOwnersCache = 'sys_ps_groups_owners_';
        $this->_sGroupMembersCache = 'sys_ps_group_members_';
        $this->_sObjectCache = 'sys_ps_object_';
        $this->_sActionCache = 'sys_ps_action_';
        $this->_sActionDefaultCache = 'sys_ps_action_default_';
    }
    function getObjectInfo($sAction, $iObjectId)
    {
        if(empty($this->_sTable) || empty($this->_sFieldId) || empty($this->_sFieldOwnerId))
            return array();

        return $this->fromMemory(
            $this->_sObjectCache . $this->_sTable . '_' . $sAction . '_' . $iObjectId,
            "getRow",
            "SELECT `" . $this->_sFieldOwnerId . "` AS `owner_id`, `" . $sAction . "` AS `group_id` FROM `" . $this->_sTable . "` WHERE `" . $this->_sFieldId . "`= ? LIMIT 1",
            [$iObjectId]
        );
    }
    function isGroupMember($mixedObjectGroupId, $iObjectOwnerId, $iViewerId)
    {
        $iGroupId = (int)$mixedObjectGroupId;

        $aGroup = $this->getGroupsBy(array('type' => 'id', 'id' => $iGroupId));
        if(empty($aGroup) || !is_array($aGroup))
            return false;

        //--- Check in group's direct members ---//
        if((int)$aGroup['owner_id'] != 0) {
            $aGroupMembers = $this->fromMemory(
                $this->_sGroupMembersCache . $iGroupId,
                "getAllWithKey",
                "SELECT `id`, `member_id` FROM `sys_privacy_members` WHERE `group_id`='" . $iGroupId . "'",
                "member_id"
            );

            if(array_key_exists($iViewerId, $aGroupMembers))
               return true;
        }
        //--- Check in system groups('All', 'Friends', etc) ---//
        if($this->getParam('sys_ps_enabled_group_' . $aGroup['id']) == 'on' && (int)$aGroup['owner_id'] == 0 && !empty($aGroup['get_content'])) {
            $oFunction = function($arg0, $arg1, $arg2) use ($aGroup) {
                return eval($aGroup['get_content']);
            };

            if($oFunction($this, $iObjectOwnerId, $iViewerId))
               return true;
        }
        //--- Check in 'Default' group ---//
        if((int)$aGroup['owner_id'] == 0 && !empty($aGroup['get_parent'])) {
            $oFunction = function($arg0, $arg1, $arg2) use ($aGroup) {
                return eval($aGroup['get_parent']);
            };

            $iId = $oFunction($this, $iObjectOwnerId, $iViewerId);
            if($this->isGroupMember($iId, $iObjectOwnerId, $iViewerId))
                return true;
        }
        //--- Check in extended groups ---//
        if((int)$aGroup['parent_id'] != 0 && $this->isGroupMember($aGroup['parent_id'], $iObjectOwnerId, $iViewerId))
            return true;

        return false;
    }
    function getGroupsBy($aParams)
    {
        switch($aParams['type']) {
            case 'id':
                $sCacheFunction = 'fromCache';
                $sCacheName = $this->_sGroupCache . $aParams['id'];
                $sMethod = 'getRow';
                $sWhereClause = "`id`='" . (int)$aParams['id'] . "'";
                break;

            case 'owner':
                $aIds = array($aParams['owner_id']);
                if(isset($aParams['full']))
                    $aIds[] = '0';

                $sCacheFunction = 'fromMemory';
                $sCacheName = $this->_sGroupsByOwnersCache . implode("_", $aIds);
                $sMethod = 'getAll';
                $sWhereClause = "`owner_id` IN ('" . implode("','", $aIds) . "')";
                break;

            case 'extendable':
                $$sCacheFunction = '';
                $sCacheName = '';
                $sMethod = 'getAll';
                $sWhereClause = "(`owner_id`='0' AND `get_content`<>'') OR `owner_id`='" . (int)$aParams['owner_id'] . "'";
                break;
        }
        $sSql = "SELECT
                   `id`,
                   `owner_id`,
                   `parent_id`,
                   `title`,
                   `home_url`,
                   `get_parent`,
                   `get_content`,
                   `members_count`
                FROM `sys_privacy_groups`
                WHERE " . $sWhereClause;

        return !empty($sCacheFunction) && !empty($sCacheName) ? $this->$sCacheFunction($sCacheName, $sMethod, $sSql) : $this->$sMethod($sSql);
    }
    function deleteGroupsById($mixedValues)
    {
        if(is_array($mixedValues)) {
            foreach ($mixedValues as $k => $v)
                $mixedValues[$k] = (int)$v;
            $sGroupIds = implode("','", $mixedValues);
        } else if(is_string($mixedValues)) {
            $sGroupIds = process_db_input($mixedValues, BX_TAGS_STRIP);
        }

        $this->query("DELETE FROM `sys_privacy_members` WHERE `group_id` IN ('" . $sGroupIds . "')");
        $this->query("DELETE FROM `sys_privacy_groups` WHERE `id` IN ('" . $sGroupIds . "')");
    }
    function getMembersIds($iGroupId)
    {
        $sSql = "SELECT `member_id` AS `id` FROM `sys_privacy_members` WHERE `group_id`= ?";
        return $this->getAll($sSql, [$iGroupId]);
    }
    function addToGroup($iGroupId, $aMemberIds)
    {
        $iCount = 0;
        foreach($aMemberIds as $iMemberId)
           $iCount += $this->query("INSERT IGNORE INTO `sys_privacy_members` SET `group_id`='" . $iGroupId . "', `member_id`='" . (int)$iMemberId . "'");
        $this->cleanCache($this->_sGroupMembersCache . $iGroupId);

        $this->query("UPDATE `sys_privacy_groups` SET `members_count`=`members_count`+'" . $iCount . "' WHERE `id`='" . $iGroupId . "' LIMIT 1");
        $this->cleanCache($this->_sGroupCache . $iGroupId);
    }
    function deleteFromGroup($iGroupId, $aMemberIds)
    {
        foreach ($aMemberIds as $k => $v)
            $aMemberIds[$k] = (int)$v;
        $iCount = $this->query("DELETE FROM `sys_privacy_members` WHERE `group_id`='" . $iGroupId . "' AND `member_id` IN ('" . implode("','", $aMemberIds) . "')");
        $this->cleanCache($this->_sGroupMembersCache . $iGroupId);

        $this->query("UPDATE `sys_privacy_groups` SET `members_count`=`members_count`-'" . $iCount . "' WHERE `id`='" . $iGroupId . "' LIMIT 1");
        $this->cleanCache($this->_sGroupCache . $iGroupId);
    }

    function getDefaultGroup($iOwnerId)
    {
        $sSql = "SELECT `PrivacyDefaultGroup` FROM `Profiles` WHERE `ID`='" . $iOwnerId . "' LIMIT 1";
        return $this->getOne($sSql);
    }
    function setDefaultGroup($iOwnerId, $iGroupId)
    {
        $sSql = "UPDATE `Profiles` SET `PrivacyDefaultGroup`='" . $iGroupId . "' WHERE `ID`='" . $iOwnerId . "'";
        return (int)$this->query($sSql);
    }
    function getActions($iOwnerId)
    {
        $sSql = "SELECT
                    `tm`.`uri` AS `module_uri`,
                    `tm`.`title` AS `module_title`,
                    `ta`.`id` AS `action_id`,
                    `ta`.`title` AS `action_title`,
                    `ta`.`default_group` AS `action_default_value`,
                    `td`.`group_id` AS `default_value`
                FROM `sys_privacy_actions` AS `ta`
                LEFT JOIN `sys_privacy_defaults` AS `td` ON `ta`.`id`=`td`.`action_id` AND `td`.`owner_id`= ?
                INNER JOIN `sys_modules` AS `tm` ON `ta`.`module_uri`=`tm`.`uri`
                WHERE 1
                ORDER BY `tm`.`title`";
        return $this->getAll($sSql, [$iOwnerId]);
    }
    function getDefaultValue($iOwnerId, $sModuleUri, $sActionName)
    {
        $sSql = "SELECT
               `td`.`group_id`
            FROM `sys_privacy_actions` AS `ta`
            LEFT JOIN `sys_privacy_defaults` AS `td` ON `ta`.`id`=`td`.`action_id` AND `td`.`owner_id`='" . $iOwnerId . "'
            WHERE `ta`.`module_uri`='" . $sModuleUri . "' AND `ta`.`name`='" . $sActionName . "'
            LIMIT 1";
        return $this->fromMemory($this->_sActionDefaultCache . $sModuleUri . '_' . $sActionName . '_' . $iOwnerId, 'getOne', $sSql);
    }
    function getDefaultValueModule($sModuleUri, $sActionName)
    {
        $aAction = $this->_getAction($sModuleUri, $sActionName);
        return !empty($aAction) && isset($aAction['default_group']) ? $aAction['default_group'] : BX_DOL_PG_ALL;
    }
    function replaceDefaulfValue($iOwnerId, $iActionId, $iGroupId)
    {
        $sSql = "REPLACE INTO `sys_privacy_defaults` SET `owner_id`='" . $iOwnerId . "', `action_id`='" . $iActionId . "', `group_id`='" . $iGroupId . "'";
        return $this->query($sSql);
    }
    function getFieldActionTitle($sModuleUri, $sActionName)
    {
        $aAction = $this->_getAction($sModuleUri, $sActionName);
        return !empty($aAction) && isset($aAction['title']) ? $aAction['title'] : '';
    }

    /**
     * Private methods.
     */
    function _getAction($sModuleUri, $sActionName)
    {
        $sSql = "SELECT
                `id`,
                `module_uri`,
                `name`,
                `title`,
                `default_group`
            FROM `sys_privacy_actions` AS `ta`
            WHERE `module_uri`= ? AND `name`= ?
            LIMIT 1";
        return $this->fromCache($this->_sActionCache . $sModuleUri . '_' . $sActionName, 'getRow', $sSql, [$sModuleUri, $sActionName]);
    }
}
