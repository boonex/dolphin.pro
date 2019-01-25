<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxFdbExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_fdb_comments' => '`cmt_author_id` = {profile_id}',
            'bx_fdb_comments_track' => '`cmt_rate_author_id` = {profile_id}',
            'bx_fdb_entries' => '`author_id` = {profile_id}',
            'bx_fdb_voting' => array(
                'query' => "SELECT `v`.* FROM `bx_fdb_voting` AS `v` INNER JOIN `bx_fdb_entries` AS `m` ON (`m`.`id` = `v`.`fdb_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_fdb_voting_track' => array(
                'query' => "SELECT `t`.`fdb_id`, 0, `t`.`fdb_date` FROM `bx_fdb_voting_track` AS `t` INNER JOIN `bx_fdb_entries` AS `m` ON (`m`.`id` = `t`.`fdb_id`) WHERE `m`.`author_id` = {profile_id}"), // anonymize some data 
        );
    }
}
