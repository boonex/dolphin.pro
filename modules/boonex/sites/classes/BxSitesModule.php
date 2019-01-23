<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

function bx_sites_import ($sClassPostfix, $aModuleOverwright = array())
{
    global $aModule;
    $a = $aModuleOverwright ? $aModuleOverwright : $aModule;
    if (!$a || $a['uri'] != 'sites') {
        $oMain = BxDolModule::getInstance('BxSitesModule');
        $a = $oMain->_aModule;
    }
    bx_import ($sClassPostfix, $a) ;

}

function getEntryUri($sTitle)
{
    $sUri = preg_replace('/[^a-zA-Z0-9]/', ' ', $sTitle);
    $sUri = preg_replace('/ +/', '_', trim($sUri));

    return $sUri;
}

bx_import('BxDolTwigModule');

require_once('BxSitesPrivacy.php');

/**
 * Sites module
 *
 * This module allow users to post description sites,
 * users can rate, comment, discuss it.
 *
 *
 *
 * Profile's Wall:
 * 'add site' site are displayed in profile's wall
 *
 *
 *
 * Spy:
 * 'add site' site is displayed in spy
 *
 *
 *
 * Memberships/ACL:
 * sites view - BX_SITES_VIEW
 * sites browse - BX_SITES_BROWSE
 * sites edit any site - BX_SITES_EDIT_ANY_SITE
 * sites delete any site - BX_SITES_DELETE_ANY_SITE
 * sites mark as featured - BX_SITES_MARK_AS_FEATURED
 *
 *
 *
 * Alerts:
 * Alerts type/unit - 'bx_sites'
 * The following alerts are rised
 *
 *  add - new site was added
 *      $iObjectId - site id
 *      $iSenderId - creator of an site
 *      $aExtras['Status'] - status of added site
 *
 *  change - site's info was changed
 *      $iObjectId - site id
 *      $iSenderId - editor user id
 *      $aExtras['Status'] - status of changed site
 *
 *  delete - site was deleted
 *      $iObjectId - site id
 *      $iSenderId - deleter user id
 *
 *  mark_as_featured - site was marked/unmarked as featured
 *      $iObjectId - site id
 *      $iSenderId - performer id
 *      $aExtras['Featured'] - 1 - if site was marked as featured and 0 - if site was removed from featured
 *
 *
 *  Using service for get thumbnail sites
 *
 *  1. Register on site "http://www.shrinktheweb.com"
 *  2. Login and get "Access Key ID" and "Secret Key"(see in block "Website Thumbnails" section "Your Access Keys")
 *  3. Insert them in Administration -> Extensions -> Sites -> Settings "Access key id" and "Password" respectively
 *
 */
class BxSitesModule extends BxDolTwigModule
{
    var $_sPrefix = 'bx_sites';
    var $oPrivacy;
    var $iOwnerId;

    // BEGIN STW INTEGRATION

    var $sHomeUrl;
    var $sHomePath;
    var $sModuleUrl;

    var $sThumbPath;
    var $sThumbUrl;

    // END STW INTEGRATION

    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);
        $this->_oConfig->init($this->_oDb);
        $this->oPrivacy = new BxSitesPrivacy($this);
        $this->iOwnerId = isLogged() ? getLoggedId() : 0;
        $GLOBALS['oBxSitesModule'] = &$this;

        // BEGIN STW INTEGRATION
        $this->sHomeUrl = $this->_oConfig->getHomeUrl();
        $this->sHomePath = $this->_oConfig->getHomePath();
        $this->sModuleUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();

        $sThumbSuffix = 'data/images/thumbs/';
        $this->sThumbPath = $this->sHomePath.$sThumbSuffix;
        $this->sThumbUrl = $this->sHomeUrl.$sThumbSuffix;
        // END STW INTEGRATION
    }

    function actionHome()
    {
        bx_sites_import ('PageMain');
        $oPage = new BxSitesPageMain ($this);
        $this->_oTemplate->addCss(array('main.css', 'block_percent.css'));
        $this->_oTemplate->pageStart();
        echo $oPage->getCode();
        $this->_oTemplate->pageCode(_t('_bx_sites_caption_home'), false, false);
    }

    function actionCalendar($iYear = '', $iMonth = '')
    {
        bx_sites_import('Calendar');
        $oCalendar = new BxSitesCalendar($iYear, $iMonth, $this);
        $this->_oTemplate->pageStart();
        echo $oCalendar->display();
        $this->_oTemplate->pageCode(_t('_bx_sites_caption_browse_calendar'), true, false);
    }

    function actionDelete($iSiteId)
    {
        $iSiteId = (int)$iSiteId;
        if (!($aSite = $this->_oDb->getSiteById($iSiteId))) {
            $this->_oTemplate->displayPageNotFoundExt (_t('_bx_sites_action_title_delete'));
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        if (!$this->isAllowedDelete($aSite)) {
            echo MsgBox(_t('_bx_events_msg_access_denied')) . genAjaxyPopupJS($iSiteId, 'ajaxy_popup_result_div');
            exit;
        }

        if ($this->deleteSite($iSiteId)) {
            $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/my';
            $sJQueryJS = genAjaxyPopupJS($iSiteId, 'ajaxy_popup_result_div', $sRedirect);
            echo MsgBox(_t('_bx_sites_site_was_deleted')) . $sJQueryJS;
            exit;
        }

        echo MsgBox(_t('_bx_sites_error_occured')) . genAjaxyPopupJS($iSiteId, 'ajaxy_popup_result_div');
        exit;
    }

    function actionEdit($iSiteId)
    {
        $iSiteId = (int)$iSiteId;

        if (!($aSite = $this->_oDb->getSiteById($iSiteId))) {
            $this->_oTemplate->displayPageNotFoundExt (_t('_bx_site_caption_edit'));
            return;
        }

        if (!$this->isAllowedEdit($aSite)) {
            $this->_oTemplate->displayAccessDeniedExt (_t('_bx_site_caption_edit'));
            return;
        }

        bx_sites_import('FormEdit');
        $oForm = new BxSitesFormEdit($this, $aSite);
        $oForm->initChecker($aSite);

        $this->_oTemplate->addCss(array('main.css'));

        if ($oForm->isSubmittedAndValid ()) {
            $sStatus = $this->_oDb->getParam('bx_sites_autoapproval') == 'on' || $this->isAdmin() ? 'approved' : 'pending';
            $sCategories = implode(';', array_unique(explode(';', $oForm->getCleanValue('categories'))));
            unset($oForm->aInputs['categories']);
            $aValsAdd = array (
                'photo' => $oForm->checkUploadPhoto(),
                'categories' => $sCategories,
                'status' => $sStatus
            );

            if ($oForm->update($iSiteId, $aValsAdd)) {
                $this->isAllowedEdit($aSite, true);
                $this->onSiteChanged($iSiteId, $sStatus);
                if ($sStatus == 'approved')
                    header('Location:' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aSite['entryUri']);
                else
                    header('Location:' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri());
            } else {
                $this->_oTemplate->pageStart();
                echo MsgBox(_t('_bx_sites_err_edit_site'));
            }
        } else {
            $this->_oTemplate->pageStart();
            echo $oForm->getCode();
        }

        $this->_oTemplate->pageCode(_t('_bx_site_caption_edit'));
    }

    function actionView($mixedVar)
    {
        $GLOBALS['oTopMenu']->setCustomSubHeader(_t('_bx_sites'));

        $aSite = is_numeric($mixedVar) ? $this->_oDb->getSiteById((int)$mixedVar) : $this->_oDb->getSiteByEntryUri(process_db_input($mixedVar));

        if (empty($aSite)) {
            $this->_oTemplate->displayPageNotFoundExt (_t('_bx_sites'));
            return;
        }

        if (!$this->isAllowedView($aSite)) {
            $this->_oTemplate->displayAccessDeniedExt($aSite['title']);
            return;
        }

        if ($aSite['status'] == 'pending' && !$this->isAdmin() && !($aSite['ownerid'] == $this->iOwnerId && $aEvent['ownerid']))  {
            $this->_oTemplate->displayAccessDeniedExt($aSite['title']);
            return;
        }

        if ($aSite['Status'] == 'pending') {
            $this->_oTemplate->displayPendingApproval($aSite['title']);
            return;
        }

        bx_sites_import ('PageView');
        $oPage = new BxSitesPageView ($this, $aSite);
        $this->_oTemplate->addJsTranslation(array('_Are_you_sure'));
        $this->_oTemplate->addCss(array('main.css', 'cmts.css'));
        $this->_oTemplate->pageStart();
        echo $oPage->getCode();
        $GLOBALS['oTopMenu']->setCustomSubHeader($aSite['title']);
        $GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aSite['entryUri']);
        $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
            _t('_bx_sites') => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'home/',
            $aSite['title'] => '',
        ));
        $this->_oTemplate->pageCode($aSite['title'], false, false);

        bx_import ('BxDolViews');
        new BxDolViews('bx_sites', $aSite['id']);

        $this->isAllowedView($aSite, true);
    }

    function actionFeatured($iSiteId)
    {
        $iSiteId = (int)$iSiteId;

        if (!($aSite = $this->_oDb->getSiteById($iSiteId))) {
            $this->_oTemplate->displayPageNotFoundExt (_t('_bx_sites_featured_top_menu_sitem'));
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        if (!$this->isAllowedMarkAsFeatured($aSite)) {
            echo MsgBox(_t('_bx_events_msg_access_denied')) . genAjaxyPopupJS($iSiteId, 'ajaxy_popup_result_div');
            exit;
        }

        if ($this->_oDb->markFeatured($iSiteId)) {
            $this->isAllowedMarkAsFeatured($aSite, true);
            $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aSite['entryUri'];
            $sJQueryJS = genAjaxyPopupJS($iSiteId, 'ajaxy_popup_result_div', $sRedirect);
            echo MsgBox($aSite['featured'] ? _t('_bx_sites_msg_removed_from_featured') : _t('_bx_sites_msg_added_to_featured')) . $sJQueryJS;
            exit;
        }

        echo MsgBox(_t('_bx_sites_error_occured')) . genAjaxyPopupJS($iSiteId, 'ajaxy_popup_result_div');
        exit;
    }

    function actionShare($iSiteId)
    {
    }

    function actionHon()
    {
        bx_sites_import('PageHon');
        $oPage = new BxSitesPageHon($this);
        $this->_oTemplate->addCss(array('main.css', 'block_percent.css'));
        $this->_oTemplate->pageStart();
        echo $oPage->getCode();
        $this->_oTemplate->pageCode(_t('_bx_sites_hon'), false, false);
    }

    function actionSearch()
    {
        if (!$this->isAllowedSearch()) {
            $this->_oTemplate->displayAccessDeniedExt(_t('_bx_sites_caption_browse_search'), false);
            return;
        }

        bx_sites_import ('FormSearch');
        $oForm = new BxSitesFormSearch($this->_oConfig);
        $oForm->initChecker();

        $this->_oTemplate->addCss(array('main.css'));

        if ($oForm->isSubmittedAndValid ()) {

            bx_sites_import('SearchResult');
            $o = new BxSitesSearchResult('search', $oForm->getCleanValue('Keyword'));

            if ($o->isError) {
                $this->_oTemplate->displayPageNotFoundExt (_t('_bx_sites_caption_browse_search'));
                return;
            }

            if ($s = $o->processing()) {
                $this->_oTemplate->pageStart();
                echo $s;
            } else {
                $this->_oTemplate->displayNoDataExt (_t('_bx_sites_caption_browse_search'));
                return;
            }

            $this->isAllowedSearch(true);
            $this->_oTemplate->pageCode($o->aCurrent['title'], false, false);

        } else {
            $this->_oTemplate->pageStart();
            echo $oForm->getCode ();
            $this->_oTemplate->pageCode(_t('_bx_sites_caption_browse_search'));
        }
    }

    function actionBrowse($sMode = '', $sValue = '', $sValue2 = '', $sValue3 = '')
    {
        $bAjaxMode = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;

        if (('user' == $sMode || 'my' == $sMode) && $this->iOwnerId > 0) {
            $aProfile = getProfileInfo($this->iOwnerId);
            if (0 == strcasecmp($sValue, $aProfile['NickName']) || 'my' == $sMode) {
                $this->browseMy ($aProfile, process_db_input($sValue));
                return;
            }
        }

        if (!$this->isAllowedBrowse() || ('my' == $sMode && $this->iOwnerId == 0)) {
            $this->_oTemplate->displayAccessDeniedExt(_t('_bx_sites'), $bAjaxMode);
            return;
        }

        bx_sites_import ('SearchResult');
        $o = new BxSitesSearchResult(
                process_db_input($sMode),
                process_db_input($sValue),
                process_db_input($sValue2),
                process_db_input($sValue3)
            );

        if ($o->isError) {
            $this->_oTemplate->displayNoDataExt($o->aCurrent['title'], $bAjaxMode);
            return;
        }

        if(bx_get('rss') !== false && bx_get('rss')) {
            echo $o->rss();
            exit;
        }

        $s = $bAjaxMode ? $o->displayResultBlock(true, true) : $o->processing();

        if ($s) {
            if (!$bAjaxMode) {
                $this->_oTemplate->pageStart();
                echo $s;
                $this->_oTemplate->pageCode($o->aCurrent['title'], false, false);
            } else
                echo $s;
        } else
            $this->_oTemplate->displayNoDataExt($o->aCurrent['title'], $bAjaxMode);
    }

    function actionDeleteProfileSites ($iProfileId)
    {
        $iProfileId = (int)$iProfileId;

        if (!$iProfileId || !defined('BX_SITES_ON_PROFILE_DELETE'))
            return;

        $aSites = $this->_oDb->getSitesByAuthor($iProfileId);
        foreach ($aSites as $aSiteRow)
            $this->deleteSite($aSiteRow['id']);
    }

    function actionSharePopup ($iSiteId)
    {
        parent::_actionSharePopup ($iSiteId, _t('_bx_sites_caption_share_site'), true);
    }

    function actionIndex()
    {
        echo $this->_getSitesIndex();
    }

    function actionProfile($sNickName)
    {
        echo $this->_getSitesProfile($sNickName);
    }

    function actionAdministration($sUrl = '')
    {
        if (!$this->isAdmin()) {
            $this->_oTemplate->displayAccessDeniedExt (_t('_bx_sites'));
            return;
        }

        $aMenu = array(
            'home' => array(
                'title' => _t('_bx_sites_pending_approval'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/home',
                '_func' => array ('name' => '_actionAdministrationManage', 'params' => array(false)),
            ),
            'admin_entries' => array(
                'title' => _t('_bx_sites_administration_admin_sites'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/admin_entries',
                '_func' => array ('name' => '_actionAdministrationManage', 'params' => array(true)),
            ),
            'add' => array(
                'title' => _t('_bx_sites_administration_add_site'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/add',
                '_func' => array ('name' => '_actionAdministrationAdd', 'params' => array()),
            ),
            'settings' => array(
                'title' => _t('_bx_sites_administration_settings'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/settings',
                '_func' => array ('name' => '_actionAdministrationSettings', 'params' => array()),
            ),
        );

        if (empty($aMenu[$sUrl]))
            $sUrl = 'home';

        $aMenu[$sUrl]['active'] = 1;
        $sContent = call_user_func_array(array($this, $aMenu[$sUrl]['_func']['name']), $aMenu[$sUrl]['_func']['params']);

        $this->_oTemplate->pageStart();
        echo $this->_oTemplate->adminBlock ($sContent, _t('_bx_sites_administration'), $aMenu);
        $this->_oTemplate->addCssAdmin(array('forms_adv.css', 'main.css', 'twig.css'));
        $this->_oTemplate->pageCodeAdmin (_t('_bx_sites_administration'));
    }

    function actionAdd()
    {
        if (!$this->isAllowedAdd()) {
            $this->_oTemplate->displayAccessDeniedExt(_t('_bx_sites'));
            return;
        }

        $this->_oTemplate->addCss(array('main.css'));
        $this->_oTemplate->pageStart();
        echo $this->_addSiteForm();
        $this->_oTemplate->pageCode(_t('_bx_sites_bcaption_site_add'), true, false);
    }

    function actionTags()
    {
        bx_import('BxTemplTagsModule');
        $aParam = array(
            'type' => 'bx_sites',
            'orderby' => 'popular'
            );
            $oTags = new BxTemplTagsModule($aParam, '', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'tags');
            $this->_oTemplate->pageStart();
            echo $oTags->getCode();
            $this->_oTemplate->pageCode(_t('_bx_sites_caption_browse_tags'), false, false);
    }

    function actionCategories()
    {
        bx_import('BxTemplCategoriesModule');
        $aParam = array(
            'type' => 'bx_sites'
            );
            $oCateg = new BxTemplCategoriesModule($aParam, '', BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'categories');
            $this->_oTemplate->pageStart();
            echo $oCateg->getCode();
            $this->_oTemplate->pageCode(_t('_bx_sites_caption_browse_categories'), false, false);
    }

    /**
     * Service methods
     */
    function serviceIndexBlock()
    {
        return $this->_getSitesIndex();
    }

    function serviceProfileBlock($sNickName)
    {
        return $this->_getSitesProfile($sNickName);
    }

    function serviceGetSubscriptionParams ($sAction, $iEntryId)
    {
        $aDataEntry = $this->_oDb->getSiteById($iEntryId);
        if (empty($aDataEntry) || $aDataEntry['status'] != 'approved') {
            return array('skip' => true);
        }

        $aActionList = array(
            'commentPost' => '_bx_sites_sbs_comment'
        );

        $sActionName = isset($aActionList[$sAction]) ? ' (' . _t($aActionList[$sAction]) . ')' : '';
        return array (
            'skip' => false,
            'template' => array (
                'Subscription' => $aDataEntry['title'] . $sActionName,
                'ViewLink' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry['entryUri'],
            ),
        );
    }

    function serviceGetWallPost ($aEvent)
    {
        if (!($aProfile = getProfileInfo($aEvent['owner_id'])))
            return '';

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',', $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $iDeleted = 0;
        $aItems = array();
        foreach($aObjectIds as $iId) {
            $aItem = $this->_oDb->getSiteById($iId);
            if(empty($aItem))
                $iDeleted++;
            else if($aItem['status'] == 'approved' && $this->oPrivacy->check('view', $aItem['id'], $this->iOwnerId))
                $aItems[] = $aItem;
        }

        if($iDeleted == count($aObjectIds))
            return array('perform_delete' => true);

        $iOwner = 0;
        if(!empty($aEvent['owner_id']))
            $iOwner = (int)$aEvent['owner_id'];

        $iDate = 0;
        if(!empty($aEvent['date']))
            $iDate = (int)$aEvent['date'];

        $bItems = !empty($aItems) && is_array($aItems);
        if($iOwner == 0 && $bItems && !empty($aItems[0]['ownerid']))
            $iOwner = (int)$aItems[0]['ownerid'];

        if($iDate == 0 && $bItems && !empty($aItems[0]['date']))
            $iDate = (int)$aItems[0]['date'];

        if($iOwner == 0 || !$bItems)
            return '';

        $sCss = '';
        $sCssPrefix = str_replace('_', '-', $this->_sPrefix);
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'main.css', 'twig.css'), true);
        else
            $this->_oTemplate->addCss(array('wall_post.css', 'main.css', 'twig.css'));

        $iItems = count($aItems);
        $sOwner = getNickName($iOwner);

        bx_import('BxTemplVotingView');
        $oVoting = new BxTemplVotingView ('bx_sites', 0, 0);

        //--- Grouped events
        if($iItems > 1) {
            if($iItems > 4)
                $aItems = array_slice($aItems, 0, 4);

            $aTmplItems = array();
            foreach($aItems as $aItem)
                $aTmplItems[] = array(
                    'unit' => $this->_oTemplate->unit ($aItem, 'unit_wall', $oVoting),
                );

            return array(
                'owner_id' => $iOwner,
                'title' => _t('_bx_sites_wall_added_new_title_items', $sOwner, $iItems),
                'description' => '',
                'content' => $sCss . $this->_oTemplate->parseHtmlByName('modules/boonex/wall/|timeline_post_twig_grouped.html', array(
	            	'mod_prefix' => $sCssPrefix,
					'mod_icon' => 'link',
	                'cpt_user_name' => $sOwner,
	                'cpt_added_new' => _t('_bx_sites_wall_added_new_items', $iItems),
	                'bx_repeat:items' => $aTmplItems,
	                'post_id' => $aEvent['id']
	            )),
	            'date' => $iDate
            );
        }

        //--- Single public event
        $sTxtWallObject = _t('_bx_sites_wall_object');

        $aItem = $aItems[0];
        return array(
        	'owner_id' => $iOwner,
            'title' => _t('_bx_sites_wall_added_new_title', $sOwner, $sTxtWallObject),
            'description' => $aItem['description'],
            'content' => $sCss . $this->_oTemplate->parseHtmlByName('modules/boonex/wall/|timeline_post_twig.html', array(
        		'mod_prefix' => $sCssPrefix,
				'mod_icon' => 'link',
                'cpt_user_name' => $sOwner,
                'cpt_added_new' => _t('_bx_sites_wall_added_new'),
                'cpt_object' => $sTxtWallObject,
                'cpt_item_url' => $sBaseUrl . $aItem['entryUri'],
                'post_id' => $aEvent['id'],
                'content' => $this->_oTemplate->unit ($aItem, 'unit_wall', $oVoting),
	        )),
	        'date' => $iDate
        );
    }

    function serviceGetWallPostOutline($aEvent)
    {
        $sIcon = 'link';
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';

        $aOwner = db_assoc_arr("SELECT `ID` AS `id`, `NickName` AS `username` FROM `Profiles` WHERE `ID`='" . (int)$aEvent['owner_id'] . "' LIMIT 1");

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',', $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $iItems = count($aObjectIds);
        $iItemsLimit = 3;
        if($iItems > $iItemsLimit)
            $aObjectIds = array_slice($aObjectIds, 0, $iItemsLimit);

        $aContent = array();
        if(!empty($aEvent['content']))
            $aContent = unserialize($aEvent['content']);

        $iDeleted = 0;
        $aItems = $aTmplItems = array();
        foreach($aObjectIds as $iId) {
            $aItem = $this->_oDb->getSiteById($iId);
            if(empty($aItem))
                $iDeleted++;
            else if($aItem['status'] == 'approved' && $this->oPrivacy->check('view', $aItem['id'], $this->iOwnerId)) {
                $aItem['thumb_file'] = '';
                if($aItem[$this->_oDb->_sFieldThumb]) {
                    $aImage = BxDolService::call('photos', 'get_entry', array($aItem[$this->_oDb->_sFieldThumb], 'browse'), 'Search');
                    $aItem['thumb_file'] = $aImage['no_image'] || empty($aImage) ? '' : $aImage['file'];
                }

                $aItem[$this->_oDb->_sFieldUri] = $sBaseUrl . $aItem[$this->_oDb->_sFieldUri];
                $aItem['url'] = strncasecmp($aItem['url'], 'http://', 7) !== 0 && strncasecmp($aItem['url'], 'https://', 8) !== 0 ? 'http://' . $aItem['url'] : $aItem['url'];
                $aItems[] = $aItem;

                // BEGIN STW INTEGRATION
                if ($aItem['thumb_file'] == '' && getParam('bx_sites_account_type') != 'No Automated Screenshots') {
                    bx_sites_import('STW');
                    $sThumbHTML = getThumbnailHTML($aItem['url'], array());
                }
                // END STW INTEGRATION

                $aTmplItems[] = array(
                    'mod_prefix' => $this->_sPrefix,
                    'item_title' => $aItem[$this->_oDb->_sFieldTitle],
                    // BEGIN STW INTEGRATION
                    'bx_if:is_image' => array(
                        'condition' => $sThumbHTML == false,
                        'content' => array('item_page' => $aItem[$this->_oDb->_sFieldUri], 'image' => $aImage['file'] ? $aImage['file'] : $this->_oTemplate->getImageUrl('no-image-thumb.png'))
                    ),
                    'bx_if:is_thumbhtml' => array(
                        'condition' => $sThumbHTML != '',
                        'content' => array('item_page' => $aItem[$this->_oDb->_sFieldUri], 'thumbhtml' => $sThumbHTML)
                    ),
                    // END STW INTEGRATION
                );
            }
        }

        if($iDeleted == count($aObjectIds))
            return array('perform_delete' => true);

        if(empty($aOwner) || empty($aItems))
            return "";

        $sCss = '';
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss(array('wall_outline.css'), true);
        else
            $this->_oTemplate->addCss(array('wall_outline.css'));

        $aResult = array();
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);
        $sOwnerLink = getProfileLink($iOwner);

        //--- Grouped events
        $iItems = count($aItems);
        if($iItems > 1) {
            $aResult['content'] = $sCss . $this->_oTemplate->parseHtmlByName('wall_outline_grouped.html', array(
                'mod_prefix' => $this->_sPrefix,
                'mod_icon' => $sIcon,
                'user_name' => $sOwner,
                'user_link' => $sOwnerLink,
                'bx_repeat:items' => $aTmplItems,
                'album_url' => '',
                'album_title' => '',
                'album_description' => '',
                'album_comments' => 0 ? _t('_wall_n_comments', 0) : _t('_wall_no_comments'),
                'album_comments_link' => '',
                'post_id' => $aEvent['id'],
                'post_ago' => $aEvent['ago']
            ));

            return $aResult;
        }

        //--- Single public event
        $aItem = $aItems[0];
        $aTmplItem = $aTmplItems[0];

        $aResult['content'] =  $sCss . $this->_oTemplate->parseHtmlByName('wall_outline.html', array_merge($aTmplItem, array(
            'mod_prefix' => $this->_sPrefix,
            'mod_icon' => $sIcon,
            'user_name' => $sOwner,
            'user_link' => $sOwnerLink,
            'item_page' => $aItem[$this->_oDb->_sFieldUri],
            'item_title' => $aItem[$this->_oDb->_sFieldTitle],
            'item_description' => $this->_formatSnippetText($aItem, 200),
            'item_site_url' => $aItem['url'],
            'item_site_url_title' => $this->_oTemplate->_getDomain($aItem['url']),
            'item_comments' => (int)$aItem['commentsCount'] > 0 ? _t('_wall_n_comments', $aItem['commentsCount']) : _t('_wall_no_comments'),
            'item_comments_link' => $aItem[$this->_oDb->_sFieldUri] . '#cmta-' . $this->_sPrefix . '-' . $aItem['id'],
            'post_id' => $aEvent['id'],
            'post_ago' => $aEvent['ago']
        )));

        return $aResult;
    }

	function serviceGetWallAddComment($aEvent)
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = $iOwner != 0 ? getNickName($iOwner) : _t('_Anonymous');

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || empty($aContent['object_id']))
            return '';

		$iItem = (int)$aContent['object_id'];
        $aItem = $this->_oDb->getSiteById($iItem);
        if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        if(!$this->oPrivacy->check('view', $iItem, $this->iOwnerId))
            return;

        bx_import('Cmts', $this->_aModule);
        $oCmts = new BxSitesCmts('bx_sites', $iItem);
        if(!$oCmts->isEnabled())
            return '';

        $aComment = $oCmts->getCommentRow($iId);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sImage = '';
        if($aItem['photo']) {
            $a = array('ID' => $aItem['id'], 'Avatar' => $aItem['photo']);
            $aImage = BxDolService::call('photos', 'get_image', array($a, 'browse'), 'Search');
            $sImage = $aImage['no_image'] ? '' : $aImage['file'];
        }

        $sCss = '';
        $sCssPrefix = str_replace('_', '-', $this->_sPrefix);
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'main.css', 'twig.css'), true);
        else
            $this->_oTemplate->addCss(array('wall_post.css', 'main.css', 'twig.css'));

        bx_import('BxTemplVotingView');
        $oVoting = new BxTemplVotingView ('bx_sites', 0, 0);

        $sTextWallObject = _t('_bx_sites_wall_object');
        return array(
            'title' => _t('_bx_sites_wall_added_new_title_comment', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content' => $sCss . $this->_oTemplate->parseHtmlByName('modules/boonex/wall/|timeline_comment.html', array(
        		'mod_prefix' => $sCssPrefix,
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t('_bx_sites_wall_added_new_comment'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $sBaseUrl . $aItem['entryUri'],
	            'cnt_comment_text' => $aComment['cmt_text'],
	            'snippet' => $this->_oTemplate->unit ($aItem, 'unit_wall', $oVoting),
	        ))
        );
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function serviceGetWallPostComment($aEvent)
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aItem = $this->_oDb->getSiteById($iId);
        if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        if(!$this->oPrivacy->check('view', $iId, $this->iOwnerId))
            return;

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || !isset($aContent['comment_id']))
            return '';

        bx_import('Cmts', $this->_aModule);
        $oCmts = new BxSitesCmts('bx_sites', $iId);
        if(!$oCmts->isEnabled())
            return '';

        $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sImage = '';
        if($aItem['photo']) {
            $a = array('ID' => $aItem['id'], 'Avatar' => $aItem['photo']);
            $aImage = BxDolService::call('photos', 'get_image', array($a, 'browse'), 'Search');
            $sImage = $aImage['no_image'] ? '' : $aImage['file'];
        }

        $sCss = '';
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'main.css', 'twig.css'), true);
        else
            $this->_oTemplate->addCss(array('wall_post.css', 'main.css', 'twig.css'));

        bx_import('BxTemplVotingView');
        $oVoting = new BxTemplVotingView ('bx_sites', 0, 0);

        $sTextWallObject = _t('_bx_sites_wall_object');
        return array(
            'title' => _t('_bx_sites_wall_added_new_title_comment', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content' => $sCss . $this->_oTemplate->parseHtmlByName('modules/boonex/wall/|timeline_comment.html', array(
        		'mod_prefix' => str_replace('_', '-', $this->_sPrefix),
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t('_bx_sites_wall_added_new_comment'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $sBaseUrl . $aItem['entryUri'],
	            'cnt_comment_text' => $aComment['cmt_text'],
	            'snippet' => $this->_oTemplate->unit ($aItem, 'unit_wall', $oVoting),
	        ))
        );
    }

    function serviceGetSpyData ()
    {
        return array(
            'handlers' => array(
                array('alert_unit' => 'bx_sites', 'alert_action' => 'add', 'module_uri' => 'sites', 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => 'bx_sites', 'alert_action' => 'change', 'module_uri' => 'sites', 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => 'bx_sites', 'alert_action' => 'rate', 'module_uri' => 'sites', 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => 'bx_sites', 'alert_action' => 'commentPost', 'module_uri' => 'sites', 'module_class' => 'Module', 'module_method' => 'get_spy_post')
            ),
            'alerts' => array(
                array('unit' => 'bx_sites', 'action' => 'add'),
                array('unit' => 'bx_sites', 'action' => 'change'),
                array('unit' => 'bx_sites', 'action' => 'rate'),
                array('unit' => 'bx_sites', 'action' => 'delete'),
                array('unit' => 'bx_sites', 'action' => 'commentPost'),
                array('unit' => 'bx_sites', 'action' => 'commentRemoved')
            )
        );
    }

    function serviceGetSpyPost($sAction, $iObjectId, $iSenderId, $aExtraParams = array())
    {
        $aRet = array();

        switch($sAction) {
            case 'add' :
            case 'change' :
            case 'rate' :
            case 'commentPost' :
                $aSite = $this->_oDb->getSiteById($iObjectId);
                if (!empty($aSite))
                    $aRet = array(
                        'lang_key'  => '_bx_sites_poll_' . $sAction,
                        'params'    => array(
                            'profile_link' => $iSenderId ? getProfileLink($iSenderId) : 'javascript:void(0)',
                            'profile_nick' => $iSenderId ? getNickName($iSenderId) : _t('_Guest'),
                            'site_url' => !empty($aSite) ? $this->_oConfig->getBaseUri() . 'view/' . $aSite['entryUri'] : '',
                            'site_caption' => !empty($aSite) ? $aSite['title'] : ''
                        ),
                        'recipient_id' => $aSite['ownerid'],
                        'spy_type' => 'content_activity',
                    );
                break;

        }

        return $aRet;
    }

    function serviceGetMemberMenuItem ()
    {
        return parent::_serviceGetMemberMenuItem (_t('_bx_sites'), _t('_bx_sites'), 'link');
    }

    function serviceGetMemberMenuItemAddContent ()
    {
        if (!$this->isAllowedAdd())
            return '';
        return parent::_serviceGetMemberMenuItem (_t('_bx_sites_site'), _t('_bx_sites_site'), 'link', false, 'add');
    }

    function browseMy($aProfile, $sValue = '')
    {
        bx_sites_import ('PageProfile');
        if (strlen($sValue))
            $sTitle = _t('_bx_sites_caption_browse_' . $sValue);
        else
            $sTitle = _t('_bx_sites_caption_browse_my');
        $oPage = new BxSitesPageProfile($this, $aProfile, $sValue);
        $this->_oTemplate->addCss(array('main.css'));
        $this->_oTemplate->pageStart();
        echo $oPage->getCode();
        $this->_oTemplate->pageCode($sTitle, false, false, true);
    }

    function isAdmin()
    {
        return isAdmin($this->iOwnerId);
    }

    function isAllowedEdit($aSite, $isPerformAction = false)
    {
        if ($this->isAdmin() || ($GLOBALS['logged']['member'] && $aSite['ownerid'] == $this->iOwnerId && isProfileActive($this->iOwnerId)))
            return true;
        $this->_defineActions();
        $aCheck = checkAction($this->iOwnerId, BX_SITES_EDIT_ANY_SITE, $isPerformAction);
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedAdd ($isPerformAction = false)
    {
        if ($this->isAdmin())
            return true;
        if (!$GLOBALS['logged']['member'])
            return false;
        $this->_defineActions();
        $aCheck = checkAction($this->iOwnerId, BX_SITES_ADD, $isPerformAction);
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedMarkAsFeatured($aSite, $isPerformAction = false)
    {
        if ($this->isAdmin())
            return true;
        $this->_defineActions();
        $aCheck = checkAction($this->iOwnerId, BX_SITES_MARK_AS_FEATURED, $isPerformAction);
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedDelete(&$aSite, $isPerformAction = false)
    {
        if ($this->isAdmin() || ($GLOBALS['logged']['member'] && $aSite['ownerid'] == $this->iOwnerId && isProfileActive($this->iOwnerId)))
            return true;
        $this->_defineActions();
        $aCheck = checkAction($this->iOwnerId, BX_SITES_DELETE_ANY_SITE, $isPerformAction);
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedShare(&$aDataEntry)
    {
    	if($aDataEntry['allowView'] != BX_DOL_PG_ALL)
    		return false;

        return true;
    }

    function isAllowedView ($aSite, $isPerformAction = false)
    {
        // admin and owner always have access
        if ($this->isAdmin() || $aSite['ownerid'] == $this->iOwnerId)
            return true;

        // check admin acl
        $this->_defineActions();
        $aCheck = checkAction($this->iOwnerId, BX_SITES_VIEW, $isPerformAction);
        if ($aCheck[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED)
            return false;

        // check user group
        return $this->oPrivacy->check('view', $aSite['id'], $this->iOwnerId);
    }

    function isAllowedBrowse ($isPerformAction = false)
    {
        if ($this->isAdmin()) return true;
        $this->_defineActions();
        $aCheck = checkAction($this->iOwnerId, BX_SITES_BROWSE, $isPerformAction);
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function isAllowedSearch ($isPerformAction = false)
    {
        if ($this->isAdmin())
            return true;
        $this->_defineActions();
        $aCheck = checkAction($this->iOwnerId, BX_SITES_SEARCH, $isPerformAction);
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function deleteSite($iSiteId)
    {
        $aSite = $this->_oDb->getSiteById($iSiteId);

        if (count($aSite) > 0 && $this->_oDb->deleteSiteById($iSiteId)) {
            if ($aSite['photo'] != 0)
                BxDolService::call('photos', 'remove_object', array($aSite['photo']), 'Module');

            // BEGIN STW INTEGRATION
            bx_sites_import('STW');
            deleteThumbnail($aSite['url']);
            // END STW INTEGRATION

            $this->isAllowedDelete($aSite, true);
            $this->onSiteDeleted($iSiteId);

            return true;
        }

        return false;
    }

    // BEGIN STW INTEGRATION

    function refreshSiteThumb($iSiteId)
    {
        $aSite = $this->_oDb->getSiteById($iSiteId);

        if (count($aSite) > 0) {
            $aSTWOptions = array(
                'RefreshOnDemand' => true,
            );

            bx_sites_import('STW');
            deleteThumbnail($aSite['url']);
            $sThumbHTML = getThumbnailHTML($aSite['url'], $aSTWOptions);

            return $sThumbHTML;
        }

        return false;
    }

    function clearSiteThumbCache()
    {
        if (!($rHandler = opendir($this->sThumbPath)))
            return 0;

        while (($sFile = readdir($rHandler)) !== false)
            @unlink($this->sThumbPath . $sFile);

        closedir($rHandler);

        return 1;
    }

    // END STW INTEGRATION

    function setStatusSite($iSiteId, $sStatus)
    {
        $this->_oDb->setStatusSite($iSiteId, $sStatus);
        $this->onSiteChanged($iSiteId, $sStatus);
    }

    function _defineActions ()
    {
        defineMembershipActions(array('sites view', 'sites browse', 'sites search', 'sites add', 'sites edit any site', 'sites delete any site', 'sites mark as featured', 'sites approve'));
    }

    // ================================== tags/cats reparse functions

    function reparseTags ($iSiteId)
    {
        bx_import('BxDolTags');
        $o = new BxDolTags ();
        $o->reparseObjTags('bx_sites', $iSiteId);
    }

    function reparseCategories ($iSiteId)
    {
        bx_import('BxDolCategories');
        $o = new BxDolCategories ();
        $o->reparseObjTags('bx_sites', $iSiteId);
    }

    // ================================== events

    function onSiteCreate ($iSiteId, $sStatus)
    {
        if ('approved' == $sStatus) {
            $this->reparseTags ($iSiteId);
            $this->reparseCategories ($iSiteId);
        }

        bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('bx_sites', 'add', $iSiteId, $this->iOwnerId, array('Status' => $sStatus));
        $oAlert->alert();
    }

    function onSiteChanged ($iSiteId, $sStatus)
    {
        $this->reparseTags ($iSiteId);
        $this->reparseCategories ($iSiteId);

        bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('bx_sites', 'change', $iSiteId, $this->iOwnerId, array('Status' => $sStatus));
        $oAlert->alert();
    }

    function onSiteDeleted ($iSiteId)
    {
        // delete associated tags and categories
        $this->reparseTags ($iSiteId);
        $this->reparseCategories ($iSiteId);

        // delete sites votings
        bx_import('BxDolVoting');
        $oVotingProfile = new BxDolVoting ('bx_sites', 0, 0);
        $oVotingProfile->deleteVotings ($iSiteId);

        // delete sites comments
        bx_import('BxDolCmts');
        $oCmts = new BxDolCmts ('bx_sites', $iSiteId);
        $oCmts->onObjectDelete ();

        // delete views
        bx_import ('BxDolViews');
        $oViews = new BxDolViews('bx_sites', $iSiteId, false);
        $oViews->onObjectDelete($iSiteId);

        //delete all subscriptions
		$oSubscription = BxDolSubscription::getInstance();
		$oSubscription->unsubscribe(array('type' => 'object_id', 'unit' => 'bx_sites', 'object_id' => $iSiteId));

        // arise alert
        bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('bx_sites', 'delete', $iSiteId, $this->iOwnerId);
        $oAlert->alert();
    }

    function onSiteMarkAsFeatured ($aSite)
    {
        // arise alert
        bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('bx_sites', 'mark_as_featured', $aSite['id'], $aSite['Featured']);
        $oAlert->alert();
    }

    // private functions

    function _actionAdministrationManage($isAdminEntries, $sKeyBtnDelete = '', $sKeyBtnActivate = '', $sUrl = false)
    {
        if ($_POST['action_activate'] && is_array($_POST['entry'])) {
            foreach ($_POST['entry'] as $iSiteId)
                $this->setStatusSite($iSiteId, 'approved');
        } elseif ($_POST['action_delete'] && is_array($_POST['entry'])) {
            foreach ($_POST['entry'] as $iSiteId)
                $this->deleteSite($iSiteId);
        }
        // refresh sites thumbnail
        if ($_POST['action_refresh_thumb'] && is_array($_POST['entry']))
            foreach ($_POST['entry'] as $iSiteId)
                $this->refreshSiteThumb($iSiteId);

        $aButtons = array(
            'action_delete' => '_bx_sites_admin_delete',
            );

        if (getParam('bx_sites_redo') == 'on' && getParam('bx_sites_account_type') == 'Enabled') {
            $aButtons['action_refresh_thumb'] = '_bx_sites_admin_refresh_thumb';
        }

            if (!$isAdminEntries)
            $aButtons['action_activate'] = '_bx_sites_admin_activate';

            $sForm = $this->_manageSites($isAdminEntries ? 'admin' : 'adminpending', '', $aButtons);
            return $this->_oTemplate->parseHtmlByName('my_sites_manage.html', array('form' => $sForm));
    }

    function _actionAdministrationAdd()
    {
        return $GLOBALS['oSysTemplate']->parseHtmlByName('default_padding.html', array('content' => $this->_addSiteForm()));
    }

    function _actionAdministrationSettings($sSettingsCatName = 'Sites')
    {
        $iId = $this->_oDb->getSettingsCategory($sSettingsCatName);
        if(empty($iId))
            return MsgBox(_t('_sys_request_page_not_found_cpt'));

        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if(isset($_POST['save']) && isset($_POST['cat'])) {
            $oSettings = new BxDolAdminSettings($iId);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        if (!empty($_POST['clear_cache'])) {
            $this->clearSiteThumbCache();
        }

        bx_sites_import('STW');

        if (getParam('bx_sites_key_id') != '' && getParam('bx_sites_secret_key') != '') {
            $aResponse = saveAccountInfo();
            if ($aResponse['stw_response_status'] == 'Success') {
                $sCodeSTW = MsgBox(_t('_bx_sites_administration_stw_acc_success'), 5);
            } else {
                $sCodeSTW = MsgBox(_t('_bx_sites_administration_stw_acc_failed'), 5);
            }
        } else {
            $sCodeSTW = MsgBox(_t('_bx_sites_administration_stw_acc_no_data'), 5);
        }

        $oSettings = new BxDolAdminSettings($iId);
        $sForm = $oSettings->getForm();

        $aAccInfo = $this->_oDb->getAccountInfo(getParam('bx_sites_key_id'));
        $aVars = array (
            'actual_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/settings',
            'response_status' => $aResponse['stw_response_status'] == 'Success' ? 1 : 0,
            'account_level' => $aAccInfo['account_level'] != 0 ? $aAccInfo['account_level'] : 0,
            'inside_pages' => $aAccInfo['inside_pages'] == 1 ? 1 : 0,
            'custom_size' => $aAccInfo['custom_size'] == 1 ? 1 : 0,
            'full_length' => $aAccInfo['full_length'] == 1 ? 1 : 0,
            'refresh_ondemand' => $aAccInfo['refresh_ondemand'] == 1 ? 1 : 0,
            'custom_delay' => $aAccInfo['custom_delay'] == 1 ? 1 : 0,
            'custom_quality' => $aAccInfo['custom_quality'] == 1 ? 1 : 0,
            'custom_resolution' => $aAccInfo['custom_resolution'] == 1 ? 1 : 0,
            'custom_messages' => $aAccInfo['custom_messages'] == 1 ? 1 : 0,
        );
        $sCode = $this->_oTemplate->parseHtmlByName('settings_info.html', $aVars);

        $sResult = $sCodeSTW;
        if($mixedResult !== true && !empty($mixedResult))
            $sResult .= $mixedResult;
        $sResult .= $sCode . $sForm;

        $aVars = array (
            'content' => $sResult
        );

        return $this->_oTemplate->parseHtmlByName('default_padding.html', $aVars);
    }

    function _addSiteForm()
    {
        global $dir;

        bx_sites_import('FormAdd');
        $oForm = new BxSitesFormAdd($this);
        $sMsgBox = '';

        if (isset($_POST['url'])) {
            if (isset($_POST['title'])) {
                $aParam = array('url' => process_pass_data($_POST['url']));
                if (isset($_POST['thumbnail_html']))
                    $this->_addThumbToForm($_POST['thumbnail_html'], $aParam);
                $oForm = new BxSitesFormAdd($this, $aParam);
                $oForm->initChecker();
                if ($oForm->isSubmittedAndValid()) {
                    $sCategories = implode(';', array_unique(explode(';', $oForm->getCleanValue('categories'))));
                    $sEntryUri = getEntryUri($_POST['title']);
                    unset($oForm->aInputs['categories']);
                    $aValsAdd = array (
                        'date' => time(),
                        'entryUri' => $oForm->generateUri(),
                        'status' => $this -> _oConfig -> _bAutoapprove || $this->isAdmin() ? 'approved' : 'pending',
                        'categories' => $sCategories
                    );

                    //TODO: Continue from here
                    if (isset($_FILES['photo']['tmp_name']) && $_FILES['photo']['tmp_name'])
                        $aValsAdd['photo'] = $oForm->uploadPhoto($_FILES['photo']['tmp_name']);
                    else {
                        $aSiteInfo = getSiteInfo($aParam['url'], array(
                            'thumbnailUrl' => array('tag' => 'link', 'content_attr' => 'href'),
                            'OGImage' => array('name_attr' => 'property', 'name' => 'og:image'),
                        ));

                        $sSiteThumbnailUrl = '';
                        if(!empty($aSiteInfo['thumbnailUrl']))
                            $sSiteThumbnailUrl = $aSiteInfo['thumbnailUrl'];
                        else if(!empty($aSiteInfo['OGImage']))
                            $sSiteThumbnailUrl = $aSiteInfo['OGImage'];

                        $bImage = false;
                        $aHeaders = get_headers($sSiteThumbnailUrl);
                        foreach ($aHeaders as $sHeader) {
                            $aMatches = array();
                            if(preg_match("/^Content-Type:\s*([a-z]*)\/([a-z]*)$/i", $sHeader, $aMatches))
                                if($aMatches[1] == 'image' && in_array($aMatches[2], array('png', 'jpeg', 'gif'))) {
                                    $bImage = true;
                                    break;
                                }
                        }

                        if($bImage)
                            $aValsAdd['photo'] = $oForm->uploadPhoto($sSiteThumbnailUrl, true);
                    }

                    $aValsAdd['ownerid'] = $this->iOwnerId;

                    if ($iSiteId = $oForm->insert($aValsAdd)) {
                        $this->isAllowedAdd(true);
                        $this->onSiteCreate($iSiteId, $aValsAdd['status']);
                        header('Location:' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/my');
                    } else
                        $sMsgBox = MsgBox(_t('_bx_sites_error_occured'));
                }
            } else {
                $oForm->initChecker();
                if ($oForm->isSubmittedAndValid()) {
                    $sUrl = process_pass_data($_POST['url']);
                    $sUrlFull = strncasecmp($sUrl, 'http://', 7) !== 0 && strncasecmp($sUrl, 'https://', 8) !== 0 ? 'http://' . $sUrl : $sUrl;
                    $aSite = $this->_oDb->getSiteByUrl(process_db_input($sUrl, BX_TAGS_STRIP));

                    if (empty($aSite) || !is_array($aSite)) {
                        $aInfo = getSiteInfo($sUrlFull);

                        if (!empty($aInfo)) {
                            $aParam = array(
                                'url' => $sUrl,
                                'title' => $aInfo['title'],
                                'description' => $aInfo['description']
                            );

                            // BEGIN STW INTEGRATION
                            if (getParam('bx_sites_account_type') != 'No Automated Screenshots') {
                                $aSTWOptions = array(
                                );

                                bx_sites_import('STW');
                                $sThumbHTML = getThumbnailHTML($sUrlFull, $aSTWOptions, false, false);
                                if ($sThumbHTML)
                                    $this->_addThumbToForm($sThumbHTML, $aParam);
                            }
                            // END STW INTEGRATION

                            $oForm = new BxSitesFormAdd($this, $aParam);
                        } else {
                            $sMsgBox = MsgBox(_t('_bx_sites_site_link_error'));
                            $oForm->aInputs['url']['value'] = $sUrl;
                        }
                    } else
                        header('Location:' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aSite['entryUri']);
                }
            }
        }

        return $sMsgBox . $this->_oTemplate->parseHtmlByName('form.html', array('form' => $oForm->getCode()));
    }

    // BEGIN STW INTEGRATION

    function _addThumbToForm($sThumbHTML, &$aParam)
    {
        $aParam['thumbnail'] = process_pass_data($sThumbHTML);
        $aParam['thumbnail_html'] = process_pass_data($sThumbHTML);
    }

    // END STW INTEGRATION

    function _getSitesIndex()
    {
        require_once(BX_DIRECTORY_PATH_MODULES . '/boonex/sites/classes/BxSitesSearchResult.php');
        $this->_oTemplate->addCss(array('main.css'));
        $o = new BxSitesSearchResult('index');

        return $o->displayResultBlock(true, true);
    }

    function _getSitesProfile($sNickName)
    {
        require_once(BX_DIRECTORY_PATH_MODULES . '/boonex/sites/classes/BxSitesSearchResult.php');
        $this->_oTemplate->addCss(array('main.css'));
        $o = new BxSitesSearchResult('profile', $sNickName);

        return $o->displayResultBlock(true, true);
    }

    function _manageSites($sMode, $sValue, $aButtons)
    {
        bx_sites_import('SearchResult');
        $oSearchResult = new BxSitesSearchResult($sMode, $sValue);
        $oSearchResult->sUnitTemplate = 'unit_admin';
        $sActionsPanel = '';

        $sFormName = 'manageSitesForm';

        if ($sContent = $oSearchResult->displayResultBlock(true))
        $sActionsPanel = $oSearchResult->showAdminActionsPanel($sFormName, $aButtons);
        else
        $sContent = MsgBox(_t('_Empty'));

        $aVars = array(
            'form_name' => $sFormName,
            'content' => $sContent,
            'actions_panel' => $sActionsPanel
        );

        return $this->_oTemplate->parseHtmlByName('manage.html', $aVars);
    }

}
