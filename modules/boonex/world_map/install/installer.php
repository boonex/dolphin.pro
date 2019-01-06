<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import("BxDolInstaller");

class BxWmapInstaller extends BxDolInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }

    function install($aParams)
    {
        $aResult = parent::install($aParams);

        if ($aResult['result']) {

            BxDolService::call('wmap', 'part_install', array('profiles', array(
                'part' => 'profiles',
                'title' => '_Profiles',
                'title_singular' => '_Profile',
                'icon' => 'map_marker_profiles.png',
                'icon_site' => 'user',
                'join_table' => 'Profiles',
                'join_where' => "AND `p`.`Status` = 'Active'",
                'join_field_id' => 'ID',
                'join_field_country' => 'Country',
                'join_field_city' => 'City',
                'join_field_state' => '',
                'join_field_zip' => 'zip',
                'join_field_address' => '',
                'join_field_title' => 'NickName',
                'join_field_uri' => 'ID',
                'join_field_author' => 'ID',
                'join_field_privacy' => 'allow_view_to',
                'permalink' => 'profile.php?ID=',
            )));

        }

        return $aResult;
    }

    function uninstall($aParams)
    {
        $ret = parent::uninstall(array());

        return $ret;
    }
}
