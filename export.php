<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

check_logged();

bx_import ('BxDolExport');

if ('popup' === bx_get('action')) {
    echo PopupBox('bx_profile_export', _t('_adm_txt_langs_export'), $GLOBALS['oSysTemplate']->parseHtmlByName('export.html', array(
        'content' => $GLOBALS['oFunctions']->loadingBoxInline(),
        'profile_id' => (int)bx_get('profile_id'),
    )));
}
else {
    $mixedRes = false;
    if (0 === strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
        $iProfileId = isAdmin() && (int)bx_get('profile_id') ? (int)bx_get('profile_id') : getLoggedId();
        $mixedRes = BxDolExport::generateAllExports ($iProfileId);
    }

    header('Content-Type: text/html; charset=utf-8');
    if (true === $mixedRes) {    
        $aProfile = getProfileInfo($iProfileId);
        echo json_encode(array('err' => 0, 'msg' => _t('_sys_export_success', $aProfile['Email'])));
    } 
    else {
        echo json_encode(array('err' => 1, 'msg' => $mixedRes ? $mixedRes : _t('_Error occured'))); 
    }
}
