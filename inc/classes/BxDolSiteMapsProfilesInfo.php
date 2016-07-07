<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');
bx_import('BxDolPrivacy');

/**
 * Sitemaps generator for Profile Info Pages
 */
class BxDolSiteMapsProfilesInfo extends BxDolSiteMaps
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`ID`, `DateLastEdit`", // fields list
            'field_date' => "DateLastEdit", // date field name
            'field_date_type' => "datetime", // date field type (or timestamp)
            'table' => "`Profiles`", // table name
            'join' => "", // join SQL part
            'where' => "AND `Profiles`.`Status` = 'Active' AND `allow_view_to` = '" . BX_DOL_PG_ALL . "' AND (`Profiles`.`Couple` = 0 OR `Profiles`.`Couple` > `Profiles`.`ID`)", // SQL condition, without WHERE
            'order' => " `DateLastNav` ASC ", // SQL order, without ORDER BY
        );
    }

    protected function _genUrl ($a)
    {
        return BX_DOL_URL_ROOT . 'profile_info.php?ID=' . $a['ID'];
    }
}
