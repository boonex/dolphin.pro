<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );

$GLOBALS['iAdminPage'] = 1;

require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
bx_import('BxDolAdminSettings');

$logged['admin'] = member_auth( 1, true, true );

$oSettings = new BxDolAdminSettings(0);

if (bx_get('c'))
    $oSettings->setActiveCategory(bx_get('c'));

//--- Process submit ---//
$sResult = '';
if(isset($_POST['save']) && isset($_POST['cat'])) {
    $sResult = $oSettings->saveChanges($_POST);
}

$iNameIndex = 3;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('forms_adv.css', 'settings.css'),
    'header' => _t('_adm_page_cpt_settings_advanced')
);
$_page_cont[$iNameIndex]['page_main_code'] = DesignBoxAdmin(_t('_adm_box_cpt_settings_advanced'), $sResult . $oSettings->getForm(array('1','3','6','11','12','14','25','26')), '', '', 11);

define('BX_PROMO_CODE', adm_hosting_promo());

PageCodeAdmin();
