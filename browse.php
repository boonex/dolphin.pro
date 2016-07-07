<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( 'inc/header.inc.php' );
    require_once( BX_DIRECTORY_PATH_INC  . 'design.inc.php' );
    require_once( BX_DIRECTORY_PATH_INC  . 'profiles.inc.php' );
    require_once( BX_DIRECTORY_PATH_ROOT . "templates/tmpl_{$tmpl}/scripts/BxTemplBrowse.php" );

    // define some page variables
    $_page['name_index'] 	= 7;
    $_page['header'] 		= _t('_Browse Profiles', BX_DOL_URL_ROOT);
    $_page['header_text'] 	= _t("_Browse Profiles");
    $_page['css_name'] 		= 'browse.css';
    $_page['js_name']  		= 'browse_members.js';

    $_ni = $_page['name_index'];

    // init some needed `GET` parameters ;

    $sSex     = ( isset($_GET['sex']) )	    ? $_GET['sex'] : '';
    $sAge     = ( isset($_GET['age']) )	    ? $_GET['age'] : '';
    $sCountry = ( isset($_GET['country']) ) ? $_GET['country'] : '';
    $sSort    = ( isset($_GET['sort']) )	? $_GET['sort']	: '';

    //-- change page title --//

    if($sSex && $sSex != 'all') {
         $_page['header'] .= ' / ' . strip_tags($sSex);
    }

    if($sAge && $sAge != 'all') {
         $_page['header'] .= ' / ' . strip_tags($sAge);
    }

    if($sCountry && $sCountry != 'all') {
         $_page['header'] .= ' / ' . strip_tags($sCountry);
    }

    //--

    $sPhotos  = ( isset($_GET['photos_only']) )	? $_GET['photos_only']	: '';
    $sOnline  = ( isset($_GET['online_only']) )	? $_GET['online_only']	: '';

    $sInfoMode = ( isset($_GET['mode']) and $_GET['mode'] == 'extended' ) ? 'extended' : '';

    $iPage    = ( isset($_GET['page']) )	? (int) $_GET['page'] : 1;
    if ( $iPage	<= 0 ) {
        $iPage = 1;
    }

    if ( isset($_GET['per_page']) ) {
        $iPerPage = (int) $_GET['per_page'];
    } else {
        if ( $sInfoMode == 'extended' )
            $iPerPage = 10;
        else
            $iPerPage = 50;
    }

    if($iPerPage <= 0)
        $iPerPage = 50;

    // fill array with get parameters ;
    $aFilteredSettings = array
    (
        'sex' 			=> $sSex,
        'age' 			=> $sAge,
        'country' 		=> $sCountry,
        'photos_only'	=> $sPhotos,
        'online_only'	=> $sOnline,
    );

    // fill array with some browse settings ;
    $aDisplaySettings = array(
        'page' 			=> $iPage,
        'per_page' 		=> $iPerPage,
        'sort'			=> $sSort,
        'mode'			=> $sInfoMode,
    );

    $oBrowsePage = new BxTemplBrowse( $aFilteredSettings, $aDisplaySettings, 'browse_page' );
    $sOutputHtml = $oBrowsePage -> getCode();

    $_page_cont[$_ni]['page_main_code'] = $sOutputHtml;

    PageCode();
