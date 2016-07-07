<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

/**
 * @see BxDolSiteMaps
 */
class BxDolSiteMapsQuery extends BxDolDb
{
    protected $_aSystem;

    public function __construct ($aSystem)
    {
        parent::__construct();
        $this->_aSystem = $aSystem;
    }

    static public function getAllActiveSystemsFromCache ()
    {
        return $GLOBALS['MySQL']->fromCache('sys_objects_site_maps', 'getAllWithKey', 'SELECT * FROM `sys_objects_site_maps` WHERE `active` = 1 ORDER BY `order`', 'object');
    }

    public function getCount ($aQueryParts)
    {
        $sQuery = 'SELECT COUNT(*) FROM ' . $aQueryParts['table'] . ' ' . $aQueryParts['join'] . ' ' . ' WHERE 1 ' . $aQueryParts['where'];
        return $this->getOne($sQuery);
    }

    public function getRecords ($aQueryParts, $iStart, $iLimit = 25000)
    {
        $sQuery = 'SELECT ' . $aQueryParts['fields'] .
            ' FROM ' . $aQueryParts['table'] . ' ' . $aQueryParts['join'] . ' ' .
            ' WHERE 1 ' . $aQueryParts['where'] . ' ' .
            ($aQueryParts['order'] ? ' ORDER BY ' . $aQueryParts['order'] : '') . ' ' .
            'LIMIT ' . $iStart . ',' . $iLimit;
        return $this->getAll($sQuery);
    }
}
