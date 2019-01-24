<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

$aConfig = array(
    /**
     * Main Section.
     */
    'title' => 'Groups',
    'version' => '1.4.0',
    'vendor' => 'Boonex',
    'update_url' => '',

    'compatible_with' => array(
        '7.4.0'
    ),

    /**
     * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
     */
    'home_dir' => 'boonex/groups/',
    'home_uri' => 'groups',

    'db_prefix' => 'bx_groups_',
    'class_prefix' => 'BxGroups',
    /**
     * Installation/Uninstallation Section.
     */
    'install' => array(
        'check_dependencies' => 1,
        'show_introduction' => 0,
        'change_permissions' => 0,
        'execute_sql' => 1,
        'update_languages' => 1,
        'recompile_main_menu' => 1,
        'recompile_member_menu' => 0,
        'recompile_site_stats' => 1,
        'recompile_page_builder' => 1,
        'recompile_profile_fields' => 0,
        'recompile_comments' => 1,
        'recompile_member_actions' => 1,
        'recompile_tags' => 1,
        'recompile_votes' => 1,
        'recompile_categories' => 1,
        'recompile_search' => 1,
        'recompile_injections' => 0,
        'recompile_permalinks' => 1,
        'recompile_alerts' => 1,
        'clear_db_cache' => 1,
        'show_conclusion' => 1,
    ),
    'uninstall' => array (
        'check_dependencies' => 0,
        'show_introduction' => 0,
        'change_permissions' => 0,
        'execute_sql' => 1,
        'update_languages' => 1,
        'recompile_main_menu' => 1,
        'recompile_member_menu' => 0,
        'recompile_site_stats' => 1,
        'recompile_page_builder' => 1,
        'recompile_profile_fields' => 0,
        'recompile_comments' => 1,
        'recompile_member_actions' => 1,
        'recompile_tags' => 1,
        'recompile_votes' => 1,
        'recompile_categories' => 1,
        'recompile_search' => 1,
        'recompile_injections' => 0,
        'recompile_permalinks' => 1,
        'recompile_alerts' => 1,
        'clear_db_cache' => 1,
        'show_conclusion' => 1,
    ),

    /**
     * Dependencies Section
     */
    'dependencies' => array(
    ),

    /**
     * Category for language keys.
     */
    'language_category' => 'Boonex Groups',

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
        'conclusion' => 'inst_concl.html'
    ),
    'uninstall_info' => array(
        'introduction' => '',
        'conclusion' => 'uninst_concl.html'
    )
);
