<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxBlogsExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_blogs_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_blogs_main' => '`OwnerID` = {profile_id}',
            'bx_blogs_posts' => '`OwnerID` = {profile_id}',
            'bx_blogs_rating' => array(
                'query' => "SELECT `r`.* FROM `bx_blogs_rating` AS `r` INNER JOIN `bx_blogs_posts` AS `m` ON (`m`.`PostID` = `r`.`blogp_id`) WHERE `m`.`OwnerID` = {profile_id}"),
            'bx_blogs_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_blogs_views_track` AS `t` INNER JOIN `bx_blogs_posts` AS `m` ON (`m`.`PostID` = `t`.`id`) WHERE `m`.`OwnerID` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'bx_blogs_voting_track' => array(
                'query' => "SELECT `t`.`blogp_id`, 0, `t`.`blogp_date` FROM `bx_blogs_voting_track` AS `t` INNER JOIN `bx_blogs_posts` AS `m` ON (`m`.`PostID` = `t`.`blogp_id`) WHERE `m`.`OwnerID` = {profile_id}"), // anonymize some data 
        );
        $this->_sFilesBaseDir = 'media/images/blog/';
        $this->_aTablesWithFiles = array(
            'bx_blogs_posts' => array( // table name
                'PostPhoto' => array ( // field name
                    // prefixes & extensions
                    'big_' => '', 
                    'browse_' => '', 
                    'orig_' => '', 
                    'small_' => '',
                ),
            ),
        );
    }
}
