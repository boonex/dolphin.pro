<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxNewsExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_news_comments' => '`cmt_author_id` = {profile_id}',
            'bx_news_comments_track' => '`cmt_rate_author_id` = {profile_id}',
            'bx_news_entries' => '`author_id` = {profile_id}',
            'bx_news_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_news_views_track` AS `t` INNER JOIN `bx_news_entries` AS `m` ON (`m`.`id` = `t`.`id`) WHERE `m`.`author_id` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'bx_news_voting' => array(
                'query' => "SELECT `v`.* FROM `bx_news_voting` AS `v` INNER JOIN `bx_news_entries` AS `m` ON (`m`.`id` = `v`.`news_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_news_voting_track' => array(
                'query' => "SELECT `t`.`news_id`, 0, `t`.`news_date` FROM `bx_news_voting_track` AS `t` INNER JOIN `bx_news_entries` AS `m` ON (`m`.`id` = `t`.`news_id`) WHERE `m`.`author_id` = {profile_id}"), // anonymize some data 
        );
    }
}
