<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( 'inc/header.inc.php' );
    require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php' );

    bx_import( 'BxTemplProfileView' );

    $sPageCaption = _t( '_Profile info' );

    $_page['name_index'] 	= 7;
    $_page['header'] 		= $sPageCaption;
    $_page['header_text'] 	= $sPageCaption;
    $_page['css_name']		= 'profile_view.css';

    //-- init some needed variables --//;

    $iViewedID = false != bx_get('ID') ? (int) bx_get('ID') : 0;
    if (!$iViewedID) {
        $iViewedID = getLoggedId();
    }

    // check profile membership, status, privacy and if it is exists
    bx_check_profile_visibility($iViewedID, getLoggedId());

    $GLOBALS['oTopMenu'] -> setCurrentProfileID($iViewedID);

    // fill array with all profile informaion
    $aMemberInfo  = getProfileInfo($iViewedID);

    // build page;
    $_ni = $_page['name_index'];

    // prepare all needed keys ;
    $aMemberInfo['anonym_mode'] 	= $oTemplConfig -> bAnonymousMode;
    $aMemberInfo['member_pass'] 	= $aMemberInfo['Password'];
    $aMemberInfo['member_id'] 		= $aMemberInfo['ID'];

    $aMemberInfo['url'] = BX_DOL_URL_ROOT;

    bx_import('BxDolProfileInfoPageView');
    $oProfileInfo = new BxDolProfileInfoPageView('profile_info', $aMemberInfo);
    $sOutputHtml  = $oProfileInfo->getCode();

    $_page_cont[$_ni]['page_main_code'] = $sOutputHtml;

    PageCode();
