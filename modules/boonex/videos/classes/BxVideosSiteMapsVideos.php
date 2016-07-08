<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');
bx_import('BxDolPrivacy');

/**
 * Sitemaps generator for Videos
 */
class BxVideosSiteMapsVideos extends BxDolSiteMaps
{
    protected $_oModule;

    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`e`.`ID`, `e`.`Uri`, `e`.`Date`", // fields list
            'field_date' => "Date", // date field name
            'field_date_type' => "timestamp", // date field type
            'table' => "`RayVideoFiles` AS `e`", // table name
            'join' => " INNER JOIN `sys_albums_objects` AS `o` ON (`o`.`id_object` = `e`.`ID`)
                        INNER JOIN `sys_albums` AS `a` ON (`a`.`Type` = 'bx_videos' AND `a`.`Status` = 'active' AND `a`.`AllowAlbumView` = '" . BX_DOL_PG_ALL . "' AND `a`.`ID` = `o`.`id_album`)", // join SQL part
            'where' => "AND `e`.`Status` = 'approved'", // SQL condition, without WHERE
            'order' => " `e`.`Date` ASC ", // SQL order, without ORDER BY
        );

        $this->_oModule = BxDolModule::getInstance('BxVideosModule');
    }

    protected function _genUrl ($a)
    {
        return BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'view/' . $a['Uri'];
    }
}
