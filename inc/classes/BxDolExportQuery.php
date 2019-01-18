<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

/**
 * @see BxDolExport
 */
class BxDolExportQuery extends BxDolDb
{
    protected $_aSystem;

    public function __construct ($aSystem)
    {
        parent::__construct();
        $this->_aSystem = $aSystem;
    }

    static public function getAllActiveSystemsFromCache ()
    {
        return $GLOBALS['MySQL']->fromCache('sys_objects_exports', 'getAllWithKey', 'SELECT * FROM `sys_objects_exports` WHERE `active` = 1 ORDER BY `order`', 'object');
    }
}
