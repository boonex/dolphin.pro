<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxPollExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_poll_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_poll_cmts_track' => '`cmt_rate_author_id` = {profile_id}',
            'bx_poll_data' => '`id_profile` = {profile_id}',
            'bx_poll_rating' => array(
                'query' => "SELECT `f`.* FROM `bx_poll_rating` AS `f` INNER JOIN `bx_poll_data` AS `m` ON (`m`.`id_poll` = `f`.`id`) WHERE `m`.`id_profile` = {profile_id}"),        
            'bx_poll_voting_track' => array(
                'query' => "SELECT `t`.`id`, 0, `t`.`date` FROM `bx_poll_voting_track` AS `t` INNER JOIN `bx_poll_data` AS `m` ON (`m`.`id_poll` = `t`.`id`) WHERE `m`.`id_profile` = {profile_id}"), // anonymize some data
        );
    }
}
