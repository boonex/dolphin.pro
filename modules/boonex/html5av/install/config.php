<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

$aConfig = array(
    /**
     * Main Section.
     */
    'title' => 'HTML5 Audio/Video Player',
    'version' => '1.2.1',
    'vendor' => 'BoonEx',
    'update_url' => '',

    'compatible_with' => array(
        '7.2.1'
    ),

    /**
     * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
     */
    'home_dir' => 'boonex/html5av/',
    'home_uri' => 'h5av',

    'db_prefix' => 'bx_h5av_',
    'class_prefix' => 'BxH5av',

    /**
     * Installation/Uninstallation Section.
     */
    'install' => array(
        'show_introduction' => 0,
        'change_permissions' => 0,
        'execute_sql' => 1,
        'update_languages' => 1,
        'recompile_main_menu' => 0,
        'recompile_member_menu' => 0,
        'recompile_site_stats' => 0,
        'recompile_page_builder' => 0,
        'recompile_profile_fields' => 0,
        'recompile_comments' => 0,
        'recompile_member_actions' => 0,
        'recompile_tags' => 0,
        'recompile_votes' => 0,
        'recompile_categories' => 0,
        'recompile_search' => 0,
        'recompile_injections' => 0,
        'recompile_permalinks' => 0,
        'recompile_alerts' => 1,
        'recompile_global_paramaters' => 0,
        'clear_db_cache' => 1,
        'show_conclusion' => 0,
    ),
    'uninstall' => array (
        'show_introduction' => 0,
        'change_permissions' => 0,
        'execute_sql' => 1,
        'update_languages' => 1,
        'recompile_main_menu' => 0,
        'recompile_member_menu' => 0,
        'recompile_site_stats' => 0,
        'recompile_page_builder' => 0,
        'recompile_profile_fields' => 0,
        'recompile_comments' => 0,
        'recompile_member_actions' => 0,
        'recompile_tags' => 0,
        'recompile_votes' => 0,
        'recompile_categories' => 0,
        'recompile_search' => 0,
        'recompile_injections' => 0,
        'recompile_permalinks' => 0,
        'recompile_alerts' => 1,
        'recompile_global_paramaters' => 0,
        'clear_db_cache' => 1,
        'show_conclusion' => 0,
    ),
    /**
     * Dependencies Section
     */
    'dependencies' => array(),

    /**
     * Category for language keys.
     */
    'language_category' => 'HTML5 Audio/Video Player',

    /**
     * Permissions Section
     */
    'install_permissions' => array(),
    'uninstall_permissions' => array(),

    /**
     * Introduction and Conclusion Section.
     */
    'install_info' => array(
        'introduction' => '',
        'conclusion' => ''
    ),
    'uninstall_info' => array(
        'introduction' => '',
        'conclusion' => ''
    )
);
