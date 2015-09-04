<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');
bx_import('BxDolPrivacy');

/**
 * Sitemaps generator for Polls
 */
class BxPollSiteMaps extends BxDolSiteMaps
{
    protected $_oModule;

    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`id_poll`, `poll_date`", // fields list
            'field_date' => "poll_date", // date field name
            'field_date_type' => "timestamp", // date field type
            'table' => "`bx_poll_data`", // table name
            'join' => "", // join SQL part
            'where' => "AND `poll_status` = 'active' AND `allow_view_to` = '" . BX_DOL_PG_ALL . "'", // SQL condition, without WHERE
            'order' => " `poll_date` ASC ", // SQL order, without ORDER BY
        );

        $this->_oModule = BxDolModule::getInstance('BxPollModule');
    }

    protected function _genUrl ($a)
    {
        return BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . '&action=show_poll_info&id=' . $a['id_poll'];
    }
}
