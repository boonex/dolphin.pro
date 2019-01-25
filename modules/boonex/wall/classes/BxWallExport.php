<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxWallExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_wall_comments' => "`cmt_author_id` = {profile_id}",
            'bx_wall_comments_track' => "`cmt_rate_author_id` = {profile_id}",
            'bx_wall_events' => "`owner_id` = {profile_id} OR IF(SUBSTRING(`type`, 1, 11) = 'wall_common', `object_id` = {profile_id}, 0)",
            'bx_wall_repost_track' => "`author_id` = {profile_id}",
            'bx_wall_voting' => array(
                'query' => "SELECT `v`.* FROM `bx_wall_voting` AS `v` INNER JOIN `bx_wall_events` AS `m` ON (`m`.`id` = `v`.`wall_id`) WHERE `owner_id` = {profile_id} OR IF(SUBSTRING(`type`, 1, 11) = 'wall_common', `object_id` = {profile_id}, 0)"),
            'bx_wall_voting_track' => array(
                'query' => "SELECT `t`.`wall_id`, 0, `t`.`wall_date` FROM `bx_wall_voting_track` AS `t` INNER JOIN `bx_wall_events` AS `m` ON (`m`.`id` = `t`.`wall_id`) WHERE `owner_id` = {profile_id} OR IF(SUBSTRING(`type`, 1, 11) = 'wall_common', `object_id` = {profile_id}, 0)"), // anonymize some data 
        );
    }
}
