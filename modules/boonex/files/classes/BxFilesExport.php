<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxFilesExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_files_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_files_cmts_albums' => '`cmt_author_id` = {profile_id}',
            'bx_files_favorites' => '`Profile` = {profile_id}',
            'bx_files_main' => '`Owner` = {profile_id}',
            'bx_files_rating' => array(
                'query' => "SELECT `r`.* FROM `bx_files_rating` AS `r` INNER JOIN `bx_files_main` AS `m` ON (`m`.`ID` = `r`.`gal_id`) WHERE `m`.`Owner` = {profile_id}"),
            'bx_files_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_files_views_track` AS `t` INNER JOIN `bx_files_main` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`Owner` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'bx_files_voting_track' => array(
                'query' => "SELECT `t`.`gal_id`, 0, `t`.`gal_date` FROM `bx_files_voting_track` AS `t` INNER JOIN `bx_files_main` AS `m` ON (`m`.`ID` = `t`.`gal_id`) WHERE `m`.`Owner` = {profile_id}"), // anonymize some data 
        );

        $this->_sFilesBaseDir = 'modules/boonex/files/data/files/';
        $this->_aTablesWithFiles = array(
            'bx_files_main' => new BxFilesExportFiles($this->_sFilesBaseDir),
        );
    }
}

class BxFilesExportFiles extends BxDolExportFiles
{
    public function perform($aRow, &$aFiles)
    {
        $sFile = $this->_sBaseDir . $aRow['ID'] . '_' . sha1($aRow['Date']);
        if(file_exists($sFile))
            $aFiles[] = $sFile;
    }
}
