<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');
bx_import('BxDolInstallerUtils');

class BxAdsExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_ads_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_ads_main' => '`IDProfile` = {profile_id}',
            'bx_ads_main_media' => array(
                'query' => "SELECT `f`.* FROM `bx_ads_main_media` AS `f` INNER JOIN `bx_ads_main` AS `m` ON (`m`.`Media` = `f`.`MediaID`) WHERE `m`.`IDProfile` = {profile_id}"),
            'bx_ads_rating' => array(
                'query' => "SELECT `r`.* FROM `bx_ads_rating` AS `r` INNER JOIN `bx_ads_main` AS `m` ON (`m`.`ID` = `r`.`ads_id`) WHERE `m`.`IDProfile` = {profile_id}"),
            'bx_ads_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_ads_views_track` AS `t` INNER JOIN `bx_ads_main` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`IDProfile` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'bx_ads_voting_track' => array(
                'query' => "SELECT `t`.`ads_id`, 0, `t`.`ads_date` FROM `bx_ads_voting_track` AS `t` INNER JOIN `bx_ads_main` AS `m` ON (`m`.`ID` = `t`.`ads_id`) WHERE `m`.`IDProfile` = {profile_id}"), // anonymize some data 
        );
        $this->_sFilesBaseDir = 'media/images/classifieds/';
        $this->_aTablesWithFiles = array(
            'bx_ads_main_media' => array( // table name
                'MediaFile' => array ( // field name
                    // prefixes & extensions
                    'big_thumb_' => '', 
                    'icon_' => '', 
                    'img_' => '', 
                    'thumb_' => ''),
            ),
        );

        if (BxDolInstallerUtils::isModuleInstalled('wmap')) {
            $this->_aTables['bx_wmap_locations'] = array(
                'query' => "SELECT `t`.* FROM `bx_wmap_locations` AS `t` INNER JOIN `bx_ads_main` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`IDProfile` = {profile_id} AND `part` = 'ads'");
        }
    }
}
