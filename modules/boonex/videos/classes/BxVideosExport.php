<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxVideosExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_videos_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_videos_cmts_albums' => '`cmt_author_id` = {profile_id}',
            'bx_videos_favorites' => '`Profile` = {profile_id}',
            'bx_videos_rating' => array(
                'query' => "SELECT `r`.* FROM `bx_videos_rating` AS `r` INNER JOIN `RayVideoFiles` AS `m` ON (`m`.`ID` = `r`.`gal_id`) WHERE `m`.`Owner` = {profile_id}"),
            'bx_videos_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_videos_views_track` AS `t` INNER JOIN `RayVideoFiles` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`Owner` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'bx_videos_voting_track' => array(
                'query' => "SELECT `t`.`gal_id`, 0, `t`.`gal_date` FROM `bx_videos_voting_track` AS `t` INNER JOIN `RayVideoFiles` AS `m` ON (`m`.`ID` = `t`.`gal_id`) WHERE `m`.`Owner` = {profile_id}"), // anonymize some data 
        );
    }
}
