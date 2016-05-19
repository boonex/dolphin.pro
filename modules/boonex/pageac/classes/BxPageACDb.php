<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');

class BxPageACDb extends BxDolModuleDb
{
    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);

        $this->_oConfig = &$oConfig;
    }

    function fromCache($sName, $sFunc)
    {
        $aArgs = func_get_args();
        array_shift($aArgs); // shift $sName
        array_shift($aArgs); // shift $sFunc
        $sQuery = array_shift($aArgs);
        $sExtra = array_shift($aArgs);

        $sName = 'sys_modules_'.$this->_oConfig->getUri().'_'.$sName;
        $sHash = md5($sName.$GLOBALS['site']['ver'] . $GLOBALS['site']['build'] . $GLOBALS['site']['url']);
        return parent::fromCache($sName.'_'.$sHash, $sFunc, $sQuery, $sExtra);
    }

    function cleanCache($sName)
    {
        $sName = 'sys_modules_'.$this->_oConfig->getUri().'_'.$sName;
        $sHash = md5($sName.$GLOBALS['site']['ver'] . $GLOBALS['site']['build'] . $GLOBALS['site']['url']);
        return parent::cleanCache($sName.'_'.$sHash);
    }
    function getAllRules()
    {
        $aRules = $this->fromCache('rules', 'getAll', "SELECT * FROM `{$this->_sPrefix}rules` ORDER BY `ID`");
        if (!empty($aRules))
            foreach ($aRules as $iID => $aRule)
                $aRules[$iID]['MemLevels'] = empty($aRules[$iID]['MemLevels']) ? array() : unserialize($aRules[$iID]['MemLevels']);
           return $aRules;
    }
    function addRule($sRule, $aMemLevels)
    {
        $this->query("INSERT INTO `{$this->_sPrefix}rules` SET `Rule` = '".process_db_input($sRule)."', `MemLevels` = '".serialize($aMemLevels)."'");
        $this->cleanCache('rules');
    }
    function deleteRule($iID)
    {
        $this->query("DELETE FROM `{$this->_sPrefix}rules` WHERE `ID` = {$iID} LIMIT 1");
        $this->cleanCache('rules');
    }
    function updateRule($iID, $sRule, $aMemLevels)
    {
        $this->query("UPDATE `{$this->_sPrefix}rules` SET `Rule` = '".process_db_input($sRule)."', `MemLevels` = '".serialize($aMemLevels)."' WHERE `ID` = {$iID} LIMIT 1");
        $this->cleanCache('rules');
    }
    function getTopMenuArray()
    {
        $aTopItems = array();
        $aCustomItems = array();
        $aSystemItems = array();

        $rTopItems = $this->res("SELECT `ID`, `Name` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='top' ORDER BY `Order`");
        while( $aTopItem =  $rTopItems ->fetch() ) {
            $aTopItems[$aTopItem['ID']] = $aTopItem['Name'];
            $aCustomItems[$aTopItem['ID']] = array();

            $rCustomItems = $this->res("SELECT `ID`, `Name` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='custom' AND `Parent`={$aTopItem['ID']} ORDER BY `Order`");
            while( $aCustomItem =  $rCustomItems ->fetch() ) {
                $aCustomItems[$aTopItem['ID']][$aCustomItem['ID']] = $aCustomItem['Name'];
            }
        }

        $rSysItems = $this->res("SELECT `ID`, `Name` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='system' ORDER BY `Order`");
        while( $aSystemItem =  $rSysItems ->fetch() ) {
            $aSystemItems[$aSystemItem['ID']] = $aSystemItem['Name'];
            $aCustomItems[$aSystemItem['ID']] = array();

            $rCustomItems = $this->res( "SELECT `ID`, `Name` FROM `sys_menu_top` WHERE `Active`=1 AND `Type`='custom' AND `Parent`={$aSystemItem['ID']} ORDER BY `Order`" );
            while( $aCustomItem =  $rCustomItems ->fetch() ) {
                $aCustomItems[$aSystemItem['ID']][$aCustomItem['ID']] = $aCustomItem['Name'];
            }
        }
        return array(
            'TopItems' => $aTopItems,
            'CustomItems' => $aCustomItems,
            'SystemItems' => $aSystemItems,
        );
    }
    function getMemberMenuArray()
    {
        $rTopItems = $this->res("SELECT `ID`, `Name` FROM `sys_menu_member` WHERE `Active`='1' AND `Type` <> 'linked_item' ORDER BY `Position`, `Order`");

        $aTopItems = array();
        while( $aTopItem =  $rTopItems ->fetch() ) {
            $aTopItems[$aTopItem['ID']] = $aTopItem['Name'];
        }
        return $aTopItems;
    }

    function getMenuItemVisibility($sType, $iMenuItemID)
    {
        $aRes = $this->getOne("SELECT `MemLevels` FROM `{$this->_sPrefix}{$sType}_menu_visibility` WHERE `MenuItemID` = {$iMenuItemID} LIMIT 1");
        return $iRes !== false ? unserialize($aRes) : array();
    }
    function setMenuItemVisibility($sType, $iMenuItemID, $aVisibleTo)
    {
        if (empty($aVisibleTo)) $this->query("DELETE FROM `{$this->_sPrefix}{$sType}_menu_visibility` WHERE `MenuItemID` = {$iMenuItemID}");
        else $this->query("REPLACE `{$this->_sPrefix}{$sType}_menu_visibility` SET `MemLevels` = '".serialize($aVisibleTo)."', `MenuItemID` = {$iMenuItemID}");
        $this->cleanCache($sType.'_menu');
    }
    function getPageBlockVisibility($iID)
    {
        $aRes = $this->getOne("SELECT `MemLevels` FROM `{$this->_sPrefix}page_blocks_visibility` WHERE `PageBlockID` = {$iID} LIMIT 1");
        return $iRes !== false ? unserialize($aRes) : array();
    }
    function setPageBlockVisibility($iID, $aMemLevels)
    {
        if (empty($aMemLevels)) $this->query("DELETE FROM `{$this->_sPrefix}page_blocks_visibility` WHERE `PageBlockID` = {$iID}");
        else $this->query("REPLACE `{$this->_sPrefix}page_blocks_visibility` SET `MemLevels` = '".serialize($aMemLevels)."', `PageBlockID` = {$iID}");
        $this->cleanCache('page_blocks');
    }
    function getAvailablePages()
    {
        return $this->getAll("SELECT `Name`, `Title` FROM `sys_page_compose_pages` ORDER BY `Order`");
    }
    function getPageBlocks($sPage)
    {
        $aColumns = array();
        $sPage = process_db_input($sPage);
        $rColumns = $this->res("SELECT DISTINCT `Column` FROM `sys_page_compose` WHERE `Page` = '{$sPage}' AND `Column` != 0 ORDER BY `Column`");
        while( $aColumn =  $rColumns ->fetch() ) {
            $aColumns[$aColumn['Column']] = $this->getAll("SELECT `ID`, `Caption` FROM `sys_page_compose` WHERE `Page` = '{$sPage}' AND `Column` = {$aColumn['Column']} ORDER BY `Order`");
        }
        return $aColumns;
    }
    function getAllMenuItems($sType)
    {
        $aCache = $this->fromCache($sType.'_menu', 'getAllWithKey', "SELECT `MenuItemID`, `MemLevels` FROM `{$this->_sPrefix}{$sType}_menu_visibility`", 'MenuItemID');
        if (!empty($aCache))
            foreach ($aCache as $iID => $aItem)
                $aCache[$iID]['MemLevels'] = empty($aCache[$iID]['MemLevels']) ? array() : unserialize($aCache[$iID]['MemLevels']);
           return $aCache;
    }
    function getAllPageBlocks()
    {
        $aCache = $this->fromCache('page_blocks', 'getAllWithKey', "SELECT `PageBlockID`, `MemLevels` FROM `{$this->_sPrefix}page_blocks_visibility`", 'PageBlockID');
        if (!empty($aCache))
            foreach ($aCache as $iID => $aItem)
                $aCache[$iID]['MemLevels'] = empty($aCache[$iID]['MemLevels']) ? array() : unserialize($aCache[$iID]['MemLevels']);
           return $aCache;
    }
}
