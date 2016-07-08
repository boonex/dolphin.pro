<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../../../inc/header.inc.php' );
require_once(BX_DIRECTORY_PATH_INC . 'admin.inc.php');

bx_import('BxDolModuleDb');
require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/ads/classes/BxAdsModule.php');

// --------------- page variables and login

$_page['name_index'] 	= 151;

check_logged();

$oModuleDb = new BxDolModuleDb();
$aModule = $oModuleDb->getModuleByUri('ads');

$oAds = new BxAdsModule($aModule);
$oAds->sCurrBrowsedFile = bx_html_attribute($_SERVER['PHP_SELF']);
$_page['header'] = $oAds->GetHeaderString();
$_page['header_text'] = $oAds->GetHeaderString();

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompAds($oAds);

$oAds->_oTemplate->addCss(array('ads.css', 'categories.css', 'entry_view.css'));

function PageCompAds($oAds)
{
    $sRetHtml = '';

    $sRetHtml .= $oAds->PrintCommandForms();

    if ($_REQUEST) {

        if (false !== bx_get('action')) {

            switch(bx_get('action')) {
                case '3':
                    echo $oAds->actionSearch();exit;
                    break;
                case '2':
                    $iClassifiedSubID = (int)bx_get('FilterSubCat');
                    $sRetHtml .= $oAds->PrintSubRecords($iClassifiedSubID);
                    break;
                case '1':
                    $iClassifiedID = (int)bx_get('FilterCat');
                    $sRetHtml .= $oAds->PrintAllSubRecords($iClassifiedID);
                    break;
                case 'report':
                    $iCommentID = (int)bx_get('commentID');
                    print $oAds->GenReportSubmitForm($iCommentID);
                    exit();
                case 'post_report':
                    print $oAds->ActionReportSubmit();
                    exit();

                case 'show_calendar':
                    $sRetHtml .= $oAds->GenAdsCalendar();
                    break;
                case 'show_calendar_ads':
                    $sRetHtml .= $oAds->GenAdsByDate();
                    break;

                case 'show_featured':
                    $sRetHtml .= $oAds->GenAllAds('featured');
                    break;

                case 'show_categories':
                    $sRetHtml .= $oAds->genCategoriesBlock();
                    break;

                case 'show_all_ads':
                    $sRetHtml .= $oAds->GenAllAds();
                    break;
                case 'show_popular':
                    $sRetHtml .= $oAds->GenAllAds('popular');
                    break;
                case 'show_top_rated':
                    $sRetHtml .= $oAds->GenAllAds('top');
                    break;

                case 'my_page':
                    $sRetHtml .= $oAds->GenMyPageAdmin();
                    break;

                case 'tags':
                    $sRetHtml .= $oAds->GenTagsPage();
                    break;

            }

        } elseif ((false !== bx_get('bClassifiedID') && (int)bx_get('bClassifiedID') > 0) || (false !== bx_get('catUri') && bx_get('catUri')!='')) {
            $iClassifiedID = ((int)bx_get('bClassifiedID') > 0) ? (int)bx_get('bClassifiedID') : (int)db_value("SELECT `ID` FROM `{$oAds->_oConfig->sSQLCatTable}` WHERE `CEntryUri`='" . process_db_input(bx_get('catUri'), BX_TAGS_STRIP) . "' LIMIT 1");
            if ($iClassifiedID > 0) {
                $sRetHtml .= $oAds->PrintAllSubRecords($iClassifiedID);
            }
        } elseif ((false !== bx_get('bSubClassifiedID') && (int)bx_get('bSubClassifiedID') > 0) || (false !== bx_get('scatUri') && bx_get('scatUri')!='')) {
            $iSubClassifiedID = ((int)bx_get('bSubClassifiedID') > 0) ? (int)bx_get('bSubClassifiedID') : (int)db_value("SELECT `ID` FROM `{$oAds->_oConfig->sSQLSubcatTable}` WHERE `SEntryUri`='" . process_db_input(bx_get('scatUri'), BX_TAGS_STRIP) . "' LIMIT 1");
            if ($iSubClassifiedID > 0) {
                $sRetHtml .= $oAds->PrintSubRecords($iSubClassifiedID);
            }
        } elseif ((false !== bx_get('ShowAdvertisementID') && (int)bx_get('ShowAdvertisementID')>0) || (false !== bx_get('entryUri') && bx_get('entryUri')!='')) {
            $iID = ((int)bx_get('ShowAdvertisementID') > 0) ? (int)bx_get('ShowAdvertisementID') : (int)db_value("SELECT `ID` FROM `{$oAds->_oConfig->sSQLPostsTable}` WHERE `EntryUri`='" . process_db_input(bx_get('entryUri'), BX_TAGS_STRIP) . "' LIMIT 1");
            $oAds->ActionPrintAdvertisement($iID);

            bx_import('PageView', $oAds->_aModule);
            $oAPV = new BxAdsPageView($oAds, $iID);
            $sRetHtml .= $oAPV->getCode();

        } elseif (false !== bx_get('UsersOtherListing')) {
            $iProfileID = (int)bx_get('IDProfile');
            if ($iProfileID > -1) {
                $sRetHtml .= $oAds->getMemberAds($iProfileID);
            }
        }
        //non safe functions
        elseif (false !== bx_get('DeleteAdvertisementID')) {
            $id = (int)bx_get('DeleteAdvertisementID');
            if ($id > 0) {
                $sRetHtml .= $oAds->ActionDeleteAdvertisement($id);
                $sRetHtml .= $oAds->GenMyPageAdmin('manage');
            }
        } elseif (false !== bx_get('ActivateAdvertisementID')) {
            $iAdID = (int)bx_get('ActivateAdvertisementID');
            if ($iAdID > 0 && ($oAds->bAdminMode || isModerator($oAds->_iVisitorID))) {
                $sStatus = bx_get('ActType') == 'active' ? 'active' : 'inactive';
                $oAds->_oDb->setPostStatus($iAdID, $sStatus);
                $oAds->ActionPrintAdvertisement($iAdID);

                bx_import('PageView', $oAds->_aModule);
                $oAPV = new BxAdsPageView($oAds, $iAdID);
                $sRetHtml .= $oAPV->getCode();
            }
        } elseif (false !== bx_get('BuyNow')) {
            $advId = (int)bx_get('IDAdv');
            if ($advId > 0) {
                $sRetHtml .= $oAds->ActionBuyAdvertisement($advId);
            }
        } elseif (false !== bx_get('BuySendNow')) {
            $advId = (int)bx_get('IDAdv');
            if ($advId > 0) {
                $sRetHtml .= $oAds->ActionBuySendMailAdvertisement($advId);
            }
        } else {
            $sRetHtml .= $oAds->getAdsMainPage();
        }
    } else {
        $sRetHtml .= $oAds->getAdsMainPage();
    }

    return $sRetHtml;
}

if ($oAds->_iVisitorID) {
    $aOpt = array('only_menu' => 1);
    $GLOBALS['oTopMenu']->setCustomSubActions($aOpt, 'bx_ads', true);
}

PageCode($oAds->_oTemplate);
