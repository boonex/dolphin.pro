<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../../../inc/header.inc.php' );
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'admin.inc.php');

//require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $aModule['class_prefix'] . 'Module.php');
bx_import('BxDolModuleDb');
require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/ads/classes/BxAdsModule.php');

// --------------- page variables and login
$_page['name_index'] 	= 151;

$oModuleDb = new BxDolModuleDb();
$aModule = $oModuleDb->getModuleByUri('ads');

$oAds = new BxAdsModule($aModule);
$oAds->sCurrBrowsedFile = $oAds->sHomeUrl . 'classifieds.php';
$_page['header'] = $oAds->GetHeaderString();
$_page['header_text'] = $oAds->GetHeaderString();

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompAds($oAds);

$oAds->_oTemplate->addCss(array('ads.css', 'categories.css'));

function PageCompAds($oAds)
{
    $sRetHtml = '';

    $sRetHtml .= $oAds->PrintCommandForms();

    if ($_REQUEST) {
        if (false !== bx_get('tag')) {
            $sTag = uri2title(process_db_input(bx_get('tag'), BX_TAGS_STRIP));
            $sRetHtml .= $oAds->PrintAdvertisementsByTag($sTag);
        }
    }

    return $sRetHtml;
}

PageCode($oAds->_oTemplate);
