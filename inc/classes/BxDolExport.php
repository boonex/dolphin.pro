<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExportQuery');

/**
 * Base class for export user data 
 *
 * To add export to your module you need to add a record to 'sys_objects_exports' table and custom class:
 *
 * id - autoincremented id for internal usage
 * object - your unique module name, with vendor prefix, lowercase and spaces are underscored
 * title - title of the export, or short description
 * class_name - your custom class name
 * class_file - file where your class_name is stored
 * order - order in which this sitemap is generated
 * active - is object active, allowed values 0 or 1
 *
 * You can refer to BoonEx modules for sample record in this table and sample classes.
 */
class BxDolExport
{
    protected $_aSystem = array (); ///< current export system array
    protected $_oQuery = null;
    protected $_aTables = array(); ///< array of tables for export, where key is table name and value can be string with condition (example: `a` = 'b') or array with full query (example: SELECT * FROM `a` WHERE `b` = 'c'). '{profile_id}' replacement marker can be used in condition or query
    protected $_sFilesBaseDir = ''; ///< base dir for files
    protected $_aTablesWithFiles = array(); ///< array of tables with files, where key is table name and value is array of fields, where key is field name and values are files prefixes

    protected function __construct($aSystem)
    {
        $this->_aSystem = $aSystem;
        $this->_oQuery = new BxDolExportQuery($this->_aSystem);
    }

    /**
     * Get export object instance by object name
     * @param $sObject object name
     * @return object instance or false on error
     */
    static public function getObjectInstance($sObject)
    {
        if (isset($GLOBALS['bxDolClasses']['BxDolExport!'.$sObject]))
            return $GLOBALS['bxDolClasses']['BxDolExport!'.$sObject];

        $aSystems =& self::getSystems ();
        if (!$aSystems || !isset($aSystems[$sObject]))
            return false;

        $aObject = $aSystems[$sObject];

        if (!($sClass = $aObject['class_name']))
            return false;

        if (!empty($aObject['class_file']))
            require_once(BX_DIRECTORY_PATH_ROOT . $aObject['class_file']);
        else
            bx_import($sClass);

        $o = new $sClass($aObject);

        return ($GLOBALS['bxDolClasses']['BxDolExport!'.$sObject] = $o);
    }

    /**
     * get all systems
     */
    static public function & getSystems () {
        if (!isset($GLOBALS['bx_dol_export_systems']))
            $GLOBALS['bx_dol_export_systems'] = BxDolExportQuery::getAllActiveSystemsFromCache ();
        return $GLOBALS['bx_dol_export_systems'];
    }

    /**
     * get all modules exports
     */
    static public function generateAllExports ($iProfileId)
    {
        $aSystems =& self::getSystems ();
        $aExports = array ();
        foreach ($aSystems as $sSystem => $aSystem) {
            if (!($o = self::getObjectInstance($sSystem)))
                continue;

            $a = $o->export($iProfileId);
            if (!empty($a['sql']) || !empty($a['files']))
                $aExports[$sSystem] = $a;
        }

        // combine all together and zip

        return $aExports;
    }

    /**
     * Generate export for current object and profile
     * @param $iProfileId - profile ID to export data for 
     * @return array with 2 elements: 'files' and 'sql'
     *     - files - array of files belonging to the user
     *     - sql - SQL queries string
     */
    public function export ($iProfileId)
    {
        return array(
            'sql' => $this->exportSQL($iProfileId), 
            'files' => $this->exportFiles($iProfileId),
        );
    }

    /** 
     * Generate files export for current object and profile
     * @param $iProfileId - profile ID to export data for 
     * @return array of files with full paths
     */ 
    public function exportFiles($iProfileId)
    {
        $a = array();
        foreach ($this->_aTablesWithFiles as $sTableName => $aFields)
            $a = array_merge($a, $this->_getFiles($sTableName, $aFields, $iProfileId));
        return $a;
    }

    /** 
     * Generate SQL export for current object and profile
     * @param $iProfileId - profile ID to export data for 
     * @return string with SQL queries
     */ 
    public function exportSQL($iProfileId)
    {
        $s = '';
        foreach ($this->_aTables as $sTableName => $mixedCond)
            $s .= $this->_getRows($sTableName, $mixedCond, $iProfileId);
        return $s;
    }

    protected function _getFiles($sTableName, $aFields, $iProfileId)
    {
        $mixedCond = $this->_aTables[$sTableName];
        $sQuery = $this->_getQuery($sTableName, $mixedCond, $iProfileId);
        $oStmt = $this->_oQuery->res($sQuery);
        return $this->_getFilesFromStmt($sTableName, $oStmt, $aFields);
    }
    
    protected function _getRows($sTableName, $mixedCond, $iProfileId)
    {        
        $sQuery = $this->_getQuery($sTableName, $mixedCond, $iProfileId);
        $oStmt = $this->_oQuery->res($sQuery);
        return $this->_getRowsFromStmt($sTableName, $oStmt);
    }

    protected function _getQuery($sTableName, $mixedCond, $iProfileId)
    {
        if (is_string($mixedCond)) {
            $sWhere = str_replace('{profile_id}', $iProfileId, $mixedCond);
            $sQuery = "SELECT * FROM `$sTableName` WHERE $sWhere";
        }
        elseif (is_array($mixedCond) && isset($mixedCond['query'])) {
            $sQuery = str_replace('{profile_id}', $iProfileId, $mixedCond['query']);
        }
        return $sQuery;
    }

    protected function _getFilePath($sTableName, $sField, $sFileName, $sPrefix, $sExt)
    {
        return BX_DIRECTORY_PATH_ROOT . $this->_sFilesBaseDir . (is_string($sPrefix) ? $sPrefix : '') . $sFileName . $sExt;
    }

    protected function _getFilesFromStmt($sTableName, $oStmt, $aFields)
    {
        if (!$oStmt->rowCount())
            return array();
        $aFiles = array();
        while ($r = $oStmt->fetch(PDO::FETCH_ASSOC)) {
            foreach ($aFields as $sField => $aPrefix2Ext) {
                foreach ($aPrefix2Ext as $sPrefix => $sExt) {                    
                    $sPath = $this->_getFilePath($sTableName, $sField, $r[$sField], $sPrefix, $sExt);
                    if (file_exists($sPath))
                        $aFiles[] = $sPath;
                }
            }
        }
        return $aFiles;
    }
    
    protected function _getRowsFromStmt($sTableName, $oStmt)
    {
        if (!$oStmt->rowCount())
            return '';

        $s .= "INSERT INTO `{$sTableName}` VALUES\n";
        while ($r = $oStmt->fetch(PDO::FETCH_NUM)) {
            $s .= "(";
            for ($j = 0; $j < count($r); $j++ ) {
                if (is_null($r[$j]))
                    $s .= "NULL, ";
                else
                    $s .= $this->_oQuery->escape($r[$j], true) . ", ";
            }
            $s = trim($s, ', ');
            $s .= "),\n";
        }
        $s = trim($s, ",\n");
        $s .= ";\n\n";
        return $s;
    }
}
