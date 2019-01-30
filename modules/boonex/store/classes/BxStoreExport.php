<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxStoreExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_store_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_store_cmts_track' => '`cmt_rate_author_id` = {profile_id}',
            'bx_store_customers' => '`client_id` = {profile_id}',
            'bx_store_products' => '`author_id` = {profile_id}',
            'bx_store_product_files' => array(
                'query' => "SELECT `f`.* FROM `bx_store_product_files` AS `f` INNER JOIN `bx_store_products` AS `m` ON (`m`.`id` = `f`.`entry_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_store_product_images' => array(
                'query' => "SELECT `f`.* FROM `bx_store_product_images` AS `f` INNER JOIN `bx_store_products` AS `m` ON (`m`.`id` = `f`.`entry_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_store_product_videos' => array(
                'query' => "SELECT `f`.* FROM `bx_store_product_videos` AS `f` INNER JOIN `bx_store_products` AS `m` ON (`m`.`id` = `f`.`entry_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_store_rating' => array(
                'query' => "SELECT `f`.* FROM `bx_store_rating` AS `f` INNER JOIN `bx_store_products` AS `m` ON (`m`.`id` = `f`.`gal_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_store_rating_track' => array(
                'query' => "SELECT `t`.`gal_id`, 0, `t`.`gal_date` FROM `bx_store_rating_track` AS `t` INNER JOIN `bx_store_products` AS `m` ON (`m`.`id` = `t`.`gal_id`) WHERE `m`.`author_id` = {profile_id}"), // anonymize some data 
            'bx_store_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_store_views_track` AS `t` INNER JOIN `bx_store_products` AS `m` ON (`m`.`id` = `t`.`id`) WHERE `m`.`author_id` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data

            // events forum
            'bx_store_forum' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum` AS `f` INNER JOIN `bx_store_products` AS `m` ON (`m`.`id` = `f`.`entry_id`) WHERE `m`.`author_id` = {profile_id}"),
            'bx_store_forum_actions_log' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_actions_log` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user_name`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_attachments' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_attachments` AS `f` INNER JOIN `bx_store_forum_post` AS `m` ON (`m`.`post_id` = `f`.`post_id`) INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `m`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_flag' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_flag` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_post' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_post` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_signatures' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_signatures` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_topic' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_topic` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`first_post_user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_user_activity' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_user_activity` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_user_stat' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_user_stat` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_store_forum_vote' => array(
                'query' => "SELECT `f`.* FROM `bx_store_forum_vote` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user_name`) WHERE `p`.`ID` = {profile_id}"),
        );
        $this->_sFilesBaseDir = 'modules/boonex/forum/data/attachments/';
        $this->_aTablesWithFiles = array(
            'bx_store_forum_attachments' => array( // table name
                'att_hash' => array ( // field name
                    '', // prefixes & extensions
                ),
            ),
        );
    }

    protected function _getFilePath($sTableName, $sField, $sFileName, $sPrefix, $sExt)
    {
        $s = $sFileName;
        return $this->_sFilesBaseDir . substr($s, 0, 1) . '/' . substr($s, 0, 2) . '/' . substr($s, 0, 3) . '/' . $s;
    }
}
