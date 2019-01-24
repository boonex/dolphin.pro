<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

$aConfig = array(
    /**
     * Main Section.
     */
    'title' => 'Videos',
    'version' => '1.4.0',
    'vendor' => 'Boonex',
    'update_url' => '',

    'compatible_with' => array(
        '7.4.0'
    ),

    /**
     * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
     */
    'home_dir' => 'boonex/videos/',
    'home_uri' => 'videos',

    'db_prefix' => 'bx_videos',
    'class_prefix' => 'BxVideos',
    /**
     * Installation/Uninstallation Section.
     */
    'install' => array(
        'show_introduction' => 1,
        'change_permissions' => 1,
        'execute_sql' => 1,
        'update_languages' => 1,
        'recompile_global_paramaters' => 1,
        'recompile_main_menu' => 1,
        'recompile_member_menu' => 1,
        'recompile_site_stats' => 1,
        'recompile_page_builder' => 1,
        'recompile_profile_fields' => 0,
        'recompile_comments' => 1,
        'recompile_member_actions' => 1,
        'recompile_tags' => 1,
        'recompile_votes' => 1,
        'recompile_categories' => 1,
        'clear_db_cache' => 1,
        'recompile_injections' => 0,
        'recompile_permalinks' => 1,
        'recompile_alerts' => 1,
        'show_conclusion' => 1
    ),
    'uninstall' => array (
        'show_introduction' => 1,
        'change_permissions' => 0,
        'execute_sql' => 1,
        'update_languages' => 1,
        'recompile_global_paramaters' => 1,
        'recompile_main_menu' => 1,
        'recompile_member_menu' => 1,
        'recompile_site_stats' => 1,
        'recompile_page_builder' => 1,
        'recompile_profile_fields' => 0,
        'recompile_comments' => 1,
        'recompile_member_actions' => 1,
        'recompile_tags' => 1,
        'recompile_votes' => 1,
        'recompile_categories' => 1,
        'clear_db_cache' => 1,
        'recompile_injections' => 0,
        'recompile_permalinks' => 1,
        'recompile_alerts' => 1,
        'show_conclusion' => 1
    ),
    /**
     * Dependencies Section
     */
    'dependencies' => array(),
    /**
     * Category for language keys.
     */
    'language_category' => 'Boonex Videos',
    /**
     * Permissions Section
     */
    'install_permissions' => array(),
    'uninstall_permissions' => array(),
    /**
     * Introduction and Conclusion Section.
     */
    'install_info' => array(
        'introduction' => 'inst_intro.html',
        'conclusion' => 'inst_concl.html'
    ),
    'uninstall_info' => array(
        'introduction' => 'uninst_intro.html',
        'conclusion' => 'uninst_concl.html'
    )
);
