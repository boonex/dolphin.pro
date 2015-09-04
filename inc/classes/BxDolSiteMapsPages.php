<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');

/**
 * Sitemaps generator for pages created using admin page builder
 */
class BxDolSiteMapsPages extends BxDolSiteMaps
{
    protected $_bPermalinks = true;

    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`Name`, 0 AS `Date`", // fields list
            'field_date' => "Date", // date field name
            'field_date_type' => "timestamp", // date field type (or timestamp)
            'table' => "`sys_page_compose_pages`", // table name
            'join' => "", // join SQL part
            'where' => "AND `System` = 0", // SQL condition, without WHERE
            'order' => " `Order` ASC ", // SQL order, without ORDER BY
        );
    }

    protected function _genUrl ($a)
    {        
        return BX_DOL_URL_ROOT . ($this->_bPermalinks ? 'page/' : 'viewPage.php?ID=') . rawurlencode($a['Name']);
    }
}
