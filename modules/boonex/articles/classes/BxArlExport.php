<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxArlExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_arl_comments' => '`cmt_author_id` = {profile_id}',
            'bx_arl_comments_track' => '`cmt_rate_author_id` = {profile_id}',
            'bx_arl_entries' => '`author_id` = {profile_id}',
            'bx_arl_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_arl_views_track` AS `t` INNER JOIN `bx_arl_entries` AS `m` ON (`m`.`id` = `t`.`id`) WHERE `m`.`author_id` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'bx_arl_voting' => array(
                'query' => "SELECT `v`.* FROM `bx_arl_voting` AS `v` INNER JOIN `bx_arl_entries` AS `m` ON (`m`.`id` = `v`.`arl_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_arl_voting_track' => array(
                'query' => "SELECT `t`.`arl_id`, 0, `t`.`arl_date` FROM `bx_arl_voting_track` AS `t` INNER JOIN `bx_arl_entries` AS `m` ON (`m`.`id` = `t`.`arl_id`) WHERE `m`.`author_id` = {profile_id}"), // anonymize some data 
        );
    }
}
