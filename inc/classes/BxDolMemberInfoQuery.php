<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

/**
 * Database queries for member info objects.
 *
 * @see BxDolMemberInfo
 */
class BxDolMemberInfoQuery extends BxDolDb
{
    protected $_aObject;

    public function __construct($aObject)
    {
        parent::__construct();
        $this->_aObject = $aObject;
    }

    static public function getMemberInfoObject($sObject)
    {
        $oDb     = BxDolDb::getInstance();
        $sQuery  = "SELECT * FROM `sys_objects_member_info` WHERE `object` = ?";
        $aObject = $oDb->getRow($sQuery, [$sObject]);
        if (!$aObject || !is_array($aObject)) {
            return false;
        }

        return $aObject;
    }

    static public function getMemberInfoKeysByType($sType)
    {
        $oDb      = BxDolDb::getInstance();
        $sQuery   = "SELECT * FROM `sys_objects_member_info` WHERE `type` = ? ORDER BY `title` ASC";
        $aObjects = $oDb->getPairs($sQuery, 'object', 'title', [$sType]);
        if (!$aObjects || !is_array($aObjects)) {
            return false;
        }

        foreach ($aObjects as $k => $v) {
            $aObjects[$k] = _t($v);
        }

        return $aObjects;
    }

}
