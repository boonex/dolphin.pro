<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSiteMaps');

define('BX_ORCA_INTEGRATION', 'dolphin');

require_once(BX_DIRECTORY_PATH_ROOT . 'modules/boonex/forum/inc/header.inc.php');

/**
 * Sitemaps generator for Forum
 */
class BxForumSiteMaps extends BxDolSiteMaps
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);

        $this->_aQueryParts = array (
            'fields' => "`t`.`topic_id`, `t`.`topic_uri`, `t`.`last_post_when`", // fields list
            'field_date' => "last_post_when", // date field name
            'field_date_type' => "timestamp", // date field type
            'table' => "`bx_forum_topic` AS `t`", // table name
            'join' => " INNER JOIN `bx_forum` AS `f` ON (`f`.`forum_id` = `t`.`forum_id` AND `f`.`forum_type` = 'public')", // join SQL part
            'where' => "AND `t`.`topic_hidden` = 0 AND `t`.`topic_posts` > 0", // SQL condition, without WHERE
            'order' => " `t`.`last_post_when` ASC ", // SQL order, without ORDER BY
        );
    }

    protected function _genUrl ($a)
    {
        return BX_DOL_URL_ROOT . 'forum/' . sprintf($GLOBALS['gConf']['rewrite']['topic'], $a['topic_uri']);
    }

}
