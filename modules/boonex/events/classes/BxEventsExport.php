<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');
bx_import('BxDolInstallerUtils');

class BxEventsExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_events_admins' => '`id_profile` = {profile_id}',
            'bx_events_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_events_cmts_track' => '`cmt_rate_author_id` = {profile_id}',
            'bx_events_files' => array(
                'query' => "SELECT `f`.* FROM `bx_events_files` AS `f` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `f`.`entry_id`) WHERE `m`.`ResponsibleID` = {profile_id}"),
            'bx_events_images' => array(
                'query' => "SELECT `f`.* FROM `bx_events_images` AS `f` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `f`.`entry_id`) WHERE `m`.`ResponsibleID` = {profile_id}"),
            'bx_events_main' => '`ResponsibleID` = {profile_id}',
            'bx_events_participants' => '`id_profile` = {profile_id}',
            'bx_events_rating' => array(
                'query' => "SELECT `f`.* FROM `bx_events_rating` AS `f` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `f`.`gal_id`) WHERE `m`.`ResponsibleID` = {profile_id}"),
            'bx_events_rating_track' => array(
                'query' => "SELECT `t`.`gal_id`, 0, `t`.`gal_date` FROM `bx_events_rating_track` AS `t` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `t`.`gal_id`) WHERE `m`.`ResponsibleID` = {profile_id}"), // anonymize some data 
            'bx_events_shoutbox' => '`OwnerID` = {profile_id}',
            'bx_events_sounds' => array(
                'query' => "SELECT `f`.* FROM `bx_events_sounds` AS `f` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `f`.`entry_id`) WHERE `m`.`ResponsibleID` = {profile_id}"),
            'bx_events_videos' => array(
                'query' => "SELECT `f`.* FROM `bx_events_videos` AS `f` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `f`.`entry_id`) WHERE `m`.`ResponsibleID` = {profile_id}"),
            'bx_events_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_events_views_track` AS `t` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`ResponsibleID` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data

            // events forum
            'bx_events_forum' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum` AS `f` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `f`.`entry_id`) WHERE `m`.`ResponsibleID` = {profile_id}"),
            'bx_events_forum_actions_log' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_actions_log` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user_name`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_attachments' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_attachments` AS `f` INNER JOIN `bx_events_forum_post` AS `m` ON (`m`.`post_id` = `f`.`post_id`) INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `m`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_flag' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_flag` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_post' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_post` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_signatures' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_signatures` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_topic' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_topic` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`first_post_user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_user_activity' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_user_activity` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_user_stat' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_user_stat` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user`) WHERE `p`.`ID` = {profile_id}"),
            'bx_events_forum_vote' => array(
                'query' => "SELECT `f`.* FROM `bx_events_forum_vote` AS `f` INNER JOIN `Profiles` AS `p` ON (`p`.`NickName` = `f`.`user_name`) WHERE `p`.`ID` = {profile_id}"),
        );
        $this->_sFilesBaseDir = 'modules/boonex/forum/data/attachments/';
        $this->_aTablesWithFiles = array(
            'bx_events_forum_attachments' => array( // table name
                'att_hash' => array ( // field name
                    '', // prefixes & extensions
                ),
            ),
        );

        if (BxDolInstallerUtils::isModuleInstalled('wmap')) {
            $this->_aTables['bx_wmap_locations'] = array(
                'query' => "SELECT `t`.* FROM `bx_wmap_locations` AS `t` INNER JOIN `bx_events_main` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`ResponsibleID` = {profile_id} AND `part` = 'events'");
        }
    }

    protected function _getFilePath($sTableName, $sField, $sFileName, $sPrefix, $sExt)
    {
        $s = $sFileName;
        return $this->_sFilesBaseDir . substr($s, 0, 1) . '/' . substr($s, 0, 2) . '/' . substr($s, 0, 3) . '/' . $s;
    }
}
