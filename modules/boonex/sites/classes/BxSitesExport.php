<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxSitesExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_sites_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_sites_cmts_track' => '`cmt_rate_author_id` = {profile_id}',
            'bx_sites_main' => '`ownerid` = {profile_id}',
            'bx_sites_rating' => array(
                'query' => "SELECT `r`.* FROM `bx_sites_rating` AS `r` INNER JOIN `bx_sites_main` AS `m` ON (`m`.`id` = `r`.`sites_id`) WHERE `m`.`ownerid` = {profile_id}"),
            'bx_sites_rating_track' => array(
                'query' => "SELECT `t`.`sites_id`, 0, `t`.`sites_date` FROM `bx_sites_rating_track` AS `t` INNER JOIN `bx_sites_main` AS `m` ON (`m`.`id` = `t`.`sites_id`) WHERE `m`.`ownerid` = {profile_id}"), // anonymize some data
            'bx_sites_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_sites_views_track` AS `t` INNER JOIN `bx_sites_main` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`ownerid` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
        );
    }
}
