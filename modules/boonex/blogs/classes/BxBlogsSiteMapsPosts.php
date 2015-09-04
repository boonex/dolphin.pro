<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');
bx_import('BxDolPrivacy');

/**
 * Sitemaps generator for Blog Posts
 */
class BxBlogsSiteMapsPosts extends BxDolSiteMaps
{
    protected $_oModule;

    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`PostID`, `PostUri`, `PostDate`", // fields list
            'field_date' => "PostDate", // date field name
            'field_date_type' => "timestamp", // date field type
            'table' => "`bx_blogs_posts`", // table name
            'join' => "", // join SQL part
            'where' => "AND `PostStatus` = 'approval' AND `allowView` = '" . BX_DOL_PG_ALL . "'", // SQL condition, without WHERE
            'order' => " `PostDate` ASC ", // SQL order, without ORDER BY
        );

        $this->_oModule = BxDolModule::getInstance('BxBlogsModule');
    }

    protected function _genUrl ($a)
    {
        return $this->_oModule->genUrl($a['PostID'], $a['PostUri']);
    }
}
