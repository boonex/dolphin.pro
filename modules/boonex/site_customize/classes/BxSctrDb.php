<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

class BxSctrDb extends BxDolModuleDb
{
    var $_oConfig;
    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);

        $this->_oConfig = $oConfig;
    }

    function getSite()
    {
        return $this->getRow("SELECT * FROM `" . $this->_sPrefix . "main` WHERE 1 LIMIT 1");
    }

    function getSiteTmp()
    {
        $aStyle = $this->getSite();
        if(!empty($aStyle))
            return unserialize($aStyle['tmp']);

        return array();
    }

    function getSiteCss()
    {
        $aStyle = $this->getSite();
        if(!empty($aStyle))
            return unserialize($aStyle['css']);

        return '';
    }

    function updateSite($sStyle, $sType)
    {
        $aRow = $this->getSite();
        if(empty($aRow))
            return $this->query("INSERT INTO `" . $this->_sPrefix . "main` (`" . $sType . "`) VALUES('" . $sStyle . "')");
        else
            return $this->query("UPDATE `" . $this->_sPrefix . "main` SET `" . $sType . "`='" . $sStyle . "' WHERE 1 LIMIT 1");
    }

    function saveSite()
    {
        return $this->query("UPDATE `" . $this->_sPrefix . "main` SET `css`=`tmp` WHERE 1 LIMIT 1");
    }

    function updateSiteTmp($aTmp)
    {
        return $this->updateSite(serialize($aTmp), 'tmp');
    }

    function updateSiteCss($aCss)
    {
        return $this->updateSite(serialize($aCss), 'css');
    }

    function resetSite()
    {
        return $this->query("DELETE FROM `" . $this->_sPrefix . "main` WHERE 1 LIMIT 1");
    }

    function getUnits()
    {
        $aResult = array();
        $aRows = $this->getAll("SELECT `name`, `caption`, `css_name`, `type` FROM `" . $this->_sPrefix . "units`");

        foreach ($aRows as $aValue) {
            $aResult[$aValue['type']][$aValue['name']] = array(
                'name' => $aValue['caption'],
                'css_name' => $aValue['css_name']
            );
        }

        return $aResult;
    }

    function getUnitById($iUnitId)
    {
        return $this->getRow("SELECT * FROM `" . $this->_sPrefix . "units` WHERE `id` = ? LIMIT 1", [$iUnitId]);
    }

    function deleteUnit($iUnitId)
    {
        return $this->query("DELETE FROM `" . $this->_sPrefix . "units` WHERE `id` = ?", [$iUnitId]);
    }

    function getAllThemes()
    {
        return $this->getAll("SELECT * FROM `" . $this->_sPrefix . "themes` WHERE 1 ORDER BY `id`");
    }

    function getSharedThemes()
    {
        return $this->getAll("SELECT * FROM `" . $this->_sPrefix . "themes` WHERE `ownerid` = ? ORDER BY `id`", [0]);
    }

    function getThemeByName($sName)
    {
        return $this->getRow("SELECT * FROM `" . $this->_sPrefix . "themes` WHERE `name` = ? LIMIT 1", [$sName]);
    }

    function getThemeById($iThemeId)
    {
        return $this->getRow("SELECT * FROM `" . $this->_sPrefix . "themes` WHERE `id` = ? LIMIT 1", [$iThemeId]);
    }

    function getThemeStyle($iThemeId)
    {
        if ((int)$iThemeId) {
            $aTheme = $this->getRow("SELECT * FROM `" . $this->_sPrefix . "themes` WHERE `id` = ? LIMIT 1", [$iThemeId]);

            if (!empty($aTheme))
                return unserialize($aTheme['css']);
        }

        return array();
    }

    function addTheme($sName, $iOwnerId, $sCss)
    {
        if($this->query("INSERT INTO `" . $this->_sPrefix . "themes` (`name`, `ownerid`, `css`) VALUES(?, ?, ?)", [$sName, $iOwnerId, $sCss]))
			return $this->lastId();

        return -1;
    }

    function deleteTheme($iThemeId)
    {
        return $this->query("DELETE FROM `" . $this->_sPrefix . "themes` WHERE `id` = ?", [$iThemeId]);
    }

    function addImage($sExt)
    {
        if (strlen($sExt) > 0 && $this->query("INSERT INTO `" . $this->_sPrefix . "images` (`ext`, `count`) VALUES(?, 1)", [$sExt]))
            return $this->lastId() . '.' . $sExt;

        return '';
    }

    function copyImage($sFileName)
    {
        if (strlen($sFileName) > 0) {
            $sId = basename($sFileName, '.' . pathinfo($sFileName, PATHINFO_EXTENSION));
            return strlen($sId) > 0 ? $this->query("UPDATE `" . $this->_sPrefix . "images` SET `count` = `count` +  1 WHERE `id` = ?", [$sId]) : 0;
        }

        return 0;
    }

    function deleteImage($sFileName)
    {
        $sResult = true;

        if (strlen($sFileName) > 0) {
            $sId = basename($sFileName, '.' . pathinfo($sFileName, PATHINFO_EXTENSION));
            if (strlen($sId) > 0 && $this->query("UPDATE `" . $this->_sPrefix . "images` SET `count` = `count` -  1 WHERE `id` = ?", [$sId])) {
                $aRow = $this->getRow("SELECT * FROM `" . $this->_sPrefix . "images` WHERE `id` = $sId LIMIT 1");
                if ($aRow['count'] < 1)
                    $this->query("DELETE FROM `" . $this->_sPrefix . "images` WHERE `id` = $sId");
                else
                    $sResult = false;
            }
        }

        return $sResult;
    }

    function getSettingsCategory()
    {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Profile Customizer' LIMIT 1");
    }
}
