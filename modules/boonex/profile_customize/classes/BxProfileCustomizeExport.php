<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxProfileCustomizeExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_profile_custom_main' => '`user_id` = {profile_id}',
            'bx_profile_custom_themes' => '`ownerid` = {profile_id}',
        );
        $this->_sFilesBaseDir = 'modules/boonex/profile_customize/data/images/';
        $this->_aTablesWithFiles = array(
            'bx_profile_custom_main' => array( // table name
                'css' => array ( // field name
                    // prefixes & extensions
                    '' => '', 
                    's_' => '', 
                ),
            ),
        );
    }

    protected function _getFilePath($sTableName, $sField, $sFileName, $sPrefix, $sExt)
    {
        if (!($a = @unserialize($sFileName)))
            return false;

        $sImg = false;
        foreach ($a as $aa) {
            foreach ($aa as $r) {
                if (isset($r['image'])) {
                    $sImg = $r['image'];
                    break;
                }
            }
        }

        return $this->_sFilesBaseDir . $sPrefix . $sImg . $sExt;
    }
}
