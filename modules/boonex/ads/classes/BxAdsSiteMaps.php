<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');
bx_import('BxDolPrivacy');

/**
 * Sitemaps generator for Ads
 */
class BxAdsSiteMaps extends BxDolSiteMaps
{
    protected $_oModule;

    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`ID`, `EntryUri`, `DateTime`", // fields list
            'field_date' => "DateTime", // date field name
            'field_date_type' => "timestamp", // date field type
            'table' => "`bx_ads_main`", // table name
            'join' => "", // join SQL part
            'where' => "AND `Status` = 'active' AND `AllowView` = '" . BX_DOL_PG_ALL . "'", // SQL condition, without WHERE
            'order' => " `DateTime` ASC ", // SQL order, without ORDER BY
        );

        $this->_oModule = BxDolModule::getInstance('BxAdsModule');
    }

    protected function _genUrl ($a)
    {
        return $this->_oModule->genUrl($a['ID'], $a['EntryUri']);
    }
}
