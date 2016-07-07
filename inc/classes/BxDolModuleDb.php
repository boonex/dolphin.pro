<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('BxDolDb.php');
require_once('BxDolConfig.php');

class BxDolModuleDb extends BxDolDb
{
    var $_sPrefix;
    /*
     * Constructor.
     */
    function __construct($oConfig = null)
    {
        parent::__construct();

        if(is_a($oConfig,'BxDolConfig'))
            $this->_sPrefix = $oConfig->getDbPrefix();
    }
    function getPrefix()
    {
        return $this->_sPrefix;
    }
	function getModulesBy($aParams = array())
	{
		$sMethod = 'getAll';
        $sPostfix = $sWhereClause = "";

        $sOrderClause = "ORDER BY `title`";
        switch($aParams['type']) {
            case 'path':
            	$sMethod = 'getRow';
                $sPostfix .= '_path';
                $sWhereClause .= "AND `path`='" . $aParams['value'] . "'";
                break;
        }

        $sSql = "SELECT `id`, `title`, `vendor`, `version`, `update_url`, `path`, `uri`, `class_prefix`, `db_prefix`, `dependencies`, `date` FROM `sys_modules` WHERE 1 " . $sWhereClause . " " . $sOrderClause;
        return $this->fromMemory('sys_modules' . $sPostfix, $sMethod, $sSql);
    }
    function getModuleById($iId)
    {
        $sSql = "SELECT `id`, `title`, `vendor`, `version`, `update_url`, `path`, `uri`, `class_prefix`, `db_prefix`, `dependencies`, `date` FROM `sys_modules` WHERE `id`= ? LIMIT 1";
        return $this->fromMemory('sys_modules_' . $iId, 'getRow', $sSql, [$iId]);
    }
    function getModuleByUri($sUri)
    {
        $sSql = "SELECT `id`, `title`, `vendor`, `version`, `update_url`, `path`, `uri`, `class_prefix`, `db_prefix`, `dependencies`, `date` FROM `sys_modules` WHERE `uri`= ? LIMIT 1";
        return $this->fromMemory('sys_modules_' . $sUri, 'getRow', $sSql, [$sUri]);
    }
    function isModule($sUri)
    {
        $sSql = "SELECT `id` FROM `sys_modules` WHERE `uri`= ? LIMIT 1";
        return (int)$this->getOne($sSql, [$sUri]) > 0;
    }
    function isModuleParamsUsed($sUri, $sPath, $sPrefixDb, $sPrefixClass)
    {
        $sSql = "SELECT `id` FROM `sys_modules` WHERE `uri`= ? || `path`= ? || `db_prefix`= ? || `class_prefix`= ? LIMIT 1";
        return (int)$this->getOne($sSql, [$sUri, $sPath, $sPrefixDb, $sPrefixClass]) > 0;
    }
    function getModules()
    {
        $sSql = "SELECT `id`, `title`, `vendor`, `version`, `update_url`, `path`, `uri`, `class_prefix`, `db_prefix`, `dependencies`, `date` FROM `sys_modules` ORDER BY `title`";
        return $this->fromMemory('sys_modules', 'getAll', $sSql);
    }
    function getDependent($sUri)
    {
        $sSql = "SELECT `id`, `title` FROM `sys_modules` WHERE `dependencies` LIKE ?";
        return $this->getAll($sSql, ["%{$sUri}%"]);
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
        return $this -> getOne('SELECT `kateg` FROM `sys_options` WHERE `Name` = ?', [$sCatName]);
    }    
}
