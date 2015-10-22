<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

$aConfig = array(
    /**
     * Main Section.
     */
    'vendor' => 'Boonex',
    'title' => 'Sites',
    'version' => '1.2.1',
    'update_url' => '',

    'compatible_with' => array(
        '7.2.1'
    ),

    /**
     * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
     */
    'home_dir' => 'boonex/sites/',
    'home_uri' => 'sites',

    'db_prefix' => 'bx_sites_',
    'class_prefix' => 'BxSites',
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
        'clear_db_cache' => 1,
        'recompile_injections' => 0,
        'recompile_permalinks' => 1,
        'recompile_alerts' => 1,
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
        'clear_db_cache' => 1,
        'recompile_injections' => 0,
        'recompile_permalinks' => 1,
        'recompile_alerts' => 1,
        'show_conclusion' => 1,
    ),
    /**
     * Dependencies Section
     */
    'dependencies' => array(
       'photos' => 'Photos Module'
    ),
    /**
     * Category for language keys.
     */
    'language_category' => 'Boonex Sites',
    /**
     * Permissions Section
     */
    'install_permissions' => array(
        'writable' => array(
            'data/images/thumbs',
        ),
    ),
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
