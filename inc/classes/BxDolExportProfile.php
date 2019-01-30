<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxDolExportProfile extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'Profiles' => '`ID` = {profile_id}',
            'sys_acl_actions_track' => '`IDMember` = {profile_id}',
            'sys_acl_levels_members' => '`IDMember` = {profile_id}',
            'sys_admin_ban_list' => '`ProfID` = {profile_id}',
            'sys_albums' => '`Owner` = {profile_id}',
            'sys_albums_objects' => array(
                'query' => "SELECT `o`.* FROM `sys_albums_objects` AS `o` INNER JOIN `sys_albums` AS `a` ON (`o`.`id_album` = `a`.`ID`) WHERE `a`.`Owner` = {profile_id}"),
            'sys_antispam_block_log' => '`member_id` = {profile_id}',
            'sys_block_list' => '`Profile` = {profile_id}',
            'sys_categories' => '`Owner` = {profile_id}',
            'sys_cmts_profile' => '`cmt_author_id` = {profile_id}',
            'sys_cmts_track' => '`cmt_rate_author_id` = {profile_id}',
            'sys_fave_list' => '`Profile` = {profile_id} OR `ID` = {profile_id}',
            'sys_friend_list' => '`Profile` = {profile_id} OR `ID` = {profile_id}',
            'sys_greetings' => '`Profile` = {profile_id} OR `ID` = {profile_id}',
            'sys_ip_members_visits' => '`MemberID` = {profile_id}',
            'sys_messages' => '`Recipient` = {profile_id} OR `Sender` = {profile_id}',
            'sys_privacy_defaults' => '`owner_id` = {profile_id}',
            'sys_privacy_groups' => '`owner_id` = {profile_id}',
            'sys_privacy_members' => array(
                'query' => "SELECT `m`.* FROM `sys_privacy_members` AS `m` INNER JOIN `sys_privacy_groups` AS `g` ON (`g`.`id` = `m`.`group_id`) WHERE `g`.`owner_id` = {profile_id}"),
            'sys_profiles_match' => '`profile_id` = {profile_id}',
            'sys_profiles_match_mails' => '`profile_id` = {profile_id}',
            'sys_profile_rating' => '`pr_id` = {profile_id}',
            'sys_profile_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `sys_profile_views_track` AS `t` WHERE `t`.`id` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'sys_profile_voting_track' => array(
                'query' => "SELECT `pr_id`, 0, `pr_date` FROM `sys_profile_voting_track` WHERE `pr_id` = {profile_id}"), // anonymize some data 
            'sys_sbs_entries' => '`subscriber_id` = {profile_id}',
            'sys_tags' => "`Type` = 'profile' AND `ObjID` = {profile_id}",
        );
    }
}
