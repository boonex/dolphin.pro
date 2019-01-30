<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxPhotosExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_photos_cmts' => '`cmt_author_id` = {profile_id}',
            'bx_photos_cmts_albums' => '`cmt_author_id` = {profile_id}',
            'bx_photos_favorites' => '`Profile` = {profile_id}',
            'bx_photos_main' => '`Owner` = {profile_id}',
            'bx_photos_rating' => array(
                'query' => "SELECT `r`.* FROM `bx_photos_rating` AS `r` INNER JOIN `bx_photos_main` AS `m` ON (`m`.`ID` = `r`.`gal_id`) WHERE `m`.`Owner` = {profile_id}"),
            'bx_photos_views_track' => array(
                'query' => "SELECT `t`.`id`, IF(`t`.`viewer` = {profile_id}, `t`.`viewer`, 0), IF(`t`.`viewer` = {profile_id}, `t`.`ip`, 0), `t`.`ts` FROM `bx_photos_views_track` AS `t` INNER JOIN `bx_photos_main` AS `m` ON (`m`.`ID` = `t`.`id`) WHERE `m`.`Owner` = {profile_id} OR `t`.`viewer` = {profile_id}"), // anonymize some data
            'bx_photos_voting_track' => array(
                'query' => "SELECT `t`.`gal_id`, 0, `t`.`gal_date` FROM `bx_photos_voting_track` AS `t` INNER JOIN `bx_photos_main` AS `m` ON (`m`.`ID` = `t`.`gal_id`) WHERE `m`.`Owner` = {profile_id}"), // anonymize some data 
        );

        $this->_sFilesBaseDir = 'modules/boonex/photos/data/files/';
        $this->_aTablesWithFiles = array(
            'bx_photos_main' => new BxPhotosExportFiles($this->_sFilesBaseDir),
        );
    }
}

class BxPhotosExportFiles extends BxDolExportFiles
{
    protected $_aPostfixes;

    public function __construct($sBaseDir)
    {
        parent::__construct($sBaseDir);

        $this->_aPostfixes = array('', '_m', '_ri', '_rt', '_t', '_t_2x');
    }

    public function perform($aRow, &$aFiles)
    {
        foreach($this->_aPostfixes as $sPostfix) {
            $sFile = $this->_sBaseDir . $aRow['ID'] . $sPostfix . '.' . $aRow['Ext'];
            if(file_exists($sFile))
                $aFiles[] = $sFile;
        }
    }
}
