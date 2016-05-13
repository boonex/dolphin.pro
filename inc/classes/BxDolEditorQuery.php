<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

/**
 * Database queries for editor objects.
 * @see BxDolEditor
 */
class BxDolEditorQuery extends BxDolDb
{
    protected $_aObject;

    public function __construct($aObject)
    {
        parent::__construct();
        $this->_aObject = $aObject;
    }

    static public function getEditorObject ($sObject)
    {
        $oDb = $GLOBALS['MySQL'];
        $sQuery = "SELECT * FROM `sys_objects_editor` WHERE `object` = ?";
        $aObject = $oDb->getRow($sQuery, [$sObject]);
        if (!$aObject || !is_array($aObject))
            return false;

        return $aObject;
    }

}
