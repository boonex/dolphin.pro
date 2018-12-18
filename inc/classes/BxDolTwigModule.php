<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolModule');

/**
 * Base module class for modules like events/groups/store
 */
class BxDolTwigModule extends BxDolModule
{
    var $_iProfileId;
    var $_sPrefix;
    var $_sFilterName;

    function __construct(&$aModule)
    {
        parent::__construct($aModule);
        $this->_iProfileId = isLogged() ? getLoggedId() : 0;
    }

    function _actionHome ($sTitle)
    {
        $this->_oTemplate->pageStart();
        bx_import ('PageMain', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'PageMain';
        $oPage = new $sClass ($this);
        echo $oPage->getCode();
        $this->_oTemplate->addCss (array('unit.css', 'main.css', 'twig.css'));
        $this->_oTemplate->pageCode($sTitle, false, false);
    }

    function _actionFiles ($sUri, $sTitle)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri, $sTitle)))
            return;

        $aRestriction = array (
            'id' => $this->_oDb->getMediaIds($aDataEntry[$this->_oDb->_sFieldId], 'files'),
        );
        if (!$aRestriction['id']) {
            $this->_oTemplate->displayNoData ();
            return;
        }

        $this->_oTemplate->pageStart();

        echo BxDolService::call ('files', 'get_browse_block', array($aRestriction, $this->_oConfig->getBaseUri() . 'files/' . $sUri), 'Search');

        $this->_oTemplate->pageCode($sTitle);
    }

    function _actionVideos ($sUri, $sTitle)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri, $sTitle)))
            return;

        $aRestriction = array (
            'id' => $this->_oDb->getMediaIds($aDataEntry[$this->_oDb->_sFieldId], 'videos'),
        );
        if (!$aRestriction['id']) {
            $this->_oTemplate->displayNoData ();
            return;
        }

        $this->_oTemplate->pageStart();

        echo BxDolService::call ('videos', 'get_browse_block', array($aRestriction, $this->_oConfig->getBaseUri() . 'videos/' . $sUri), 'Search');

        $this->_oTemplate->pageCode($sTitle);
    }

    function _actionSounds ($sUri, $sTitle)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri, $sTitle)))
            return;

        $aRestriction = array (
            'id' => $this->_oDb->getMediaIds($aDataEntry[$this->_oDb->_sFieldId], 'sounds'),
        );
        if (!$aRestriction['id']) {
            $this->_oTemplate->displayNoData ();
            return;
        }

        $this->_oTemplate->pageStart();

        echo BxDolService::call ('sounds', 'get_browse_block', array($aRestriction, $this->_oConfig->getBaseUri() . 'sounds/' . $sUri), 'Search');

        $this->_oTemplate->pageCode($sTitle);
    }

    function _actionPhotos ($sUri, $sTitle)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri, $sTitle)))
            return;

        $aRestriction = array (
            'id' => $this->_oDb->getMediaIds($aDataEntry[$this->_oDb->_sFieldId], 'images'),
        );
        if (!$aRestriction['id']) {
            $this->_oTemplate->displayNoData ();
            return;
        }

        $this->_oTemplate->pageStart();

        echo BxDolService::call ('photos', 'get_browse_block', array($aRestriction, $this->_oConfig->getBaseUri() . 'photos/' . $sUri), 'Search');

        $this->_oTemplate->pageCode($sTitle);
    }

    function _actionComments ($sUri, $sTitle)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri, $sTitle)))
            return;

        bx_import('Cmts', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Cmts';
        $o = new $sClass ($this->_sPrefix, (int)$aDataEntry[$this->_oDb->_sFieldId]);
        if (!$o->isEnabled()) {
            $this->_oTemplate->displayPageNotFound ();
            return;
        }

        $sRet = $o->getCommentsFirst ();

        $this->_oTemplate->pageStart();

        echo DesignBoxContent ($sTitle, $sRet, 1);

        $this->_oTemplate->pageCode($sTitle, 0, 0);
    }

    function _actionBrowseFans ($sUri, $sFuncAllowed, $sFuncDbGetFans, $iPerPage, $sUrlBrowse, $sTitle)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri, $sTitle))) {
            return;
        }

        if (!$this->$sFuncAllowed($aDataEntry)) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $iPage = (int)$_GET['page'];
        if( $iPage < 1)
            $iPage = 1;
        $iStart = ($iPage - 1) * $iPerPage;

        $aProfiles = array ();
        $iNum = $this->_oDb->$sFuncDbGetFans($aProfiles, $aDataEntry[$this->_oDb->_sFieldId], $iStart, $iPerPage);
        if (!$iNum || !$aProfiles) {
            $this->_oTemplate->displayNoData ();
            return;
        }
        $iPages = ceil($iNum / $iPerPage);

        bx_import('BxTemplSearchProfile');
        $oBxTemplSearchProfile = new BxTemplSearchProfile();
        $sMainContent = '';
        foreach ($aProfiles as $aProfile) {
            $sMainContent .= $oBxTemplSearchProfile->displaySearchUnit($aProfile);
        }
        $sRet  = $GLOBALS['oFunctions']->centerContent($sMainContent, '.searchrow_block_simple');
        $sRet .= '<div class="clear_both"></div>';

        bx_import('BxDolPaginate');
        $sUrlStart = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . $sUrlBrowse . $aDataEntry[$this->_oDb->_sFieldUri];
        $sUrlStart .= (false === strpos($sUrlStart, '?') ? '?' : '&');

        $oPaginate = new BxDolPaginate(array(
            'page_url' => $sUrlStart . 'page={page}&per_page={per_page}' . (false !== bx_get($this->sFilterName) ? '&' . $this->sFilterName . '=' . bx_get($this->sFilterName) : ''),
            'count' => $iNum,
            'per_page' => $iPerPage,
            'page' => $iPage,
            'on_change_per_page' => "document.location='" . $sUrlStart . "page=1&per_page=' + this.value + '" . (false !== bx_get($this->sFilterName) ? '&' . $this->sFilterName . '=' . bx_get($this->sFilterName) ."';": "';"),
        ));

        $sRet .= $oPaginate->getPaginate();

        $this->_oTemplate->pageStart();

        echo DesignBoxContent ($sTitle, $sRet, 11);

        $this->_oTemplate->pageCode($sTitle, false, false);
    }

    function _actionView ($sUri, $sMsgPendingApproval)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri)))
            return;

        $this->_oTemplate->pageStart();

        bx_import ('PageView', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'PageView';
        $oPage = new $sClass ($this, $aDataEntry);

        if ($aDataEntry[$this->_oDb->_sFieldStatus] == 'pending') {
            $aVars = array ('msg' => $sMsgPendingApproval); // this product is pending approval, please wait until it will be activated
            echo $this->_oTemplate->parseHtmlByName ('pending_approval_plank', $aVars);
        }

        echo $oPage->getCode();

        bx_import('Cmts', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Cmts';
        $oCmts = new $sClass ($this->_sPrefix, 0);

        $this->_oTemplate->setPageDescription (substr(strip_tags($aDataEntry[$this->_oDb->_sFieldDescription]), 0, 255));
        $this->_oTemplate->addPageKeywords ($aDataEntry[$this->_oDb->_sFieldTags]);

        $this->_oTemplate->addJsTranslation(array('_Are_you_sure'));

        $this->_oTemplate->addCss ('view.css');
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->addCss ('entry_view.css');
        $this->_oTemplate->addCss ('unit_fan.css');
        $this->_oTemplate->pageCode($aDataEntry[$this->_oDb->_sFieldTitle], false, false);

        bx_import ('BxDolViews');
        new BxDolViews($this->_sPrefix, $aDataEntry[$this->_oDb->_sFieldId]);
    }

    function _actionUploadMedia ($sUri, $sIsAllowedFuncName, $sMedia, $aMediaFields, $sTitle)
    {
        if (!($aDataEntry = $this->_preProductTabs($sUri, $sTitle)))
            return;

        if (!$this->$sIsAllowedFuncName($aDataEntry)) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        $iEntryId = $aDataEntry[$this->_oDb->_sFieldId];

        bx_import ('FormUploadMedia', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'FormUploadMedia';
        $oForm = new $sClass ($this, $aDataEntry[$this->_oDb->_sFieldAuthorId], $iEntryId, $aDataEntry, $sMedia, $aMediaFields);
        $oForm->initChecker($aDataEntry);

        if ($oForm->isSubmittedAndValid ()) {

            $oForm->processMedia($iEntryId, $this->_iProfileId);

            $this->$sIsAllowedFuncName($aDataEntry, true); // perform action

            header ('Location:' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri]);
            exit;

         } else {

            echo $oForm->getCode ();

        }

        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->addCss ('forms_extra.css');
        $this->_oTemplate->pageCode($sTitle);
    }

    function _actionBroadcast ($iEntryId, $sTitle, $sMsgNoRecipients, $sMsgSent)
    {
        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryById($iEntryId))) {
            $this->_oTemplate->displayPageNotFound ();
            return;
        }

        if (!$this->isAllowedBroadcast($aDataEntry)) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        $GLOBALS['oTopMenu']->setCustomSubHeader($aDataEntry[$this->_oDb->_sFieldTitle]);
        $GLOBALS['oTopMenu']->setCustomVar($this->_sPrefix.'_view_uri', $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
            _t('_'.$this->_sPrefix) => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'home/',
            $aDataEntry[$this->_oDb->_sFieldTitle] => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri],
            $sTitle => '',
        ));

        $aRecipients = $this->_oDb->getBroadcastRecipients ($iEntryId);
        if (!$aRecipients) {
            echo MsgBox ($sMsgNoRecipients);
            $this->_oTemplate->pageCode($sMsgNoRecipients, true, true);
            return;
        }

        bx_import ('FormBroadcast', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'FormBroadcast';
        $oForm = new $sClass ();
        $oForm->initChecker();

        if ($oForm->isSubmittedAndValid ()) {

            $oEmailTemplate = new BxDolEmailTemplates();
            if (!$oEmailTemplate) {
                $this->_oTemplate->displayErrorOccured();
                return;
            }
            $aTemplate = $oEmailTemplate->getTemplate($this->_sPrefix . '_broadcast');
            $aTemplateVars = array (
                'BroadcastTitle' => $this->_oDb->unescape($oForm->getCleanValue ('title')),
                'BroadcastMessage' => nl2br($this->_oDb->unescape($oForm->getCleanValue ('message'))),
                'EntryTitle' => $aDataEntry[$this->_oDb->_sFieldTitle],
                'EntryUrl' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri],
            );
            $iSentMailsCounter = 0;
            foreach ($aRecipients as $aProfile) {
                   $iSentMailsCounter += sendMail($aProfile['Email'], $aTemplate['Subject'], $aTemplate['Body'], $aProfile['ID'], $aTemplateVars);
            }
            if (!$iSentMailsCounter) {
                $this->_oTemplate->displayErrorOccured();
                return;
            }

            echo MsgBox ($sMsgSent);

            $this->isAllowedBroadcast($aDataEntry, true); // perform send broadcast message action
            $this->_oTemplate->addCss ('main.css');
            $this->_oTemplate->pageCode($sMsgSent, true, true);
            return;
        }

        echo $oForm->getCode ();

        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->pageCode($sTitle);
    }

    function _getInviteParams ($aDataEntry, $aInviter)
    {
        // override this
        return array ();
    }

    function _actionInvite ($iEntryId, $sEmailTemplate, $iMaxEmailInvitations, $sMsgInvitationSent, $sMsgNoUsers, $sTitle)
    {
        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryById($iEntryId))) {
            $this->_oTemplate->displayPageNotFound ();
            return;
        }

        if (!$this->isAllowedSendInvitation ($aDataEntry)) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        $GLOBALS['oTopMenu']->setCustomSubHeader($aDataEntry[$this->_oDb->_sFieldTitle]);
        $GLOBALS['oTopMenu']->setCustomVar($this->_sPrefix.'_view_uri', $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
            _t('_'.$this->_sPrefix) => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'home/',
            $aDataEntry[$this->_oDb->_sFieldTitle] => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri],
            $sTitle . $aDataEntry[$this->_oDb->_sFieldTitle] => '',
        ));

        bx_import('BxDolTwigFormInviter');
        $oForm = new BxDolTwigFormInviter ($this, $sMsgNoUsers);
        $oForm->initChecker();

        if ($oForm->isSubmittedAndValid ()) {

            $aInviter = getProfileInfo($this->_iProfileId);
            $aPlusOriginal = $this->_getInviteParams ($aDataEntry, $aInviter);

            $oEmailTemplate = new BxDolEmailTemplates();
            $aTemplate = $oEmailTemplate->getTemplate($sEmailTemplate);
            $iSuccess = 0;

            // send invitation to registered members
            if (false !== bx_get('inviter_users') && is_array(bx_get('inviter_users'))) {
                $aInviteUsers = bx_get('inviter_users');
                foreach ($aInviteUsers as $iRecipient) {
                    $aRecipient = getProfileInfo($iRecipient);
                    $aPlus = array_merge (array ('NickName' => ' ' . getNickName($aRecipient['ID'])), $aPlusOriginal);
                    $iSuccess += sendMail(trim($aRecipient['Email']), $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus) ? 1 : 0;
                }
            }

            // send invitation to additional emails
            $iMaxCount = $iMaxEmailInvitations;
            $aEmails = preg_split ("#[,\s\\b]+#", bx_get('inviter_emails'));
            $aPlus = array_merge (array ('NickName' => ''), $aPlusOriginal);
            if ($aEmails && is_array($aEmails)) {
                foreach ($aEmails as $sEmail) {
                    if (strlen($sEmail) < 5)
                        continue;
                    $iRet = sendMail(trim($sEmail), $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus) ? 1 : 0;
                    $iSuccess += $iRet;
                    if ($iRet && 0 == --$iMaxCount)
                        break;
                }
            }

            $sMsg = sprintf($sMsgInvitationSent, $iSuccess);
            echo MsgBox($sMsg);
            $this->_oTemplate->addCss ('main.css');
            $this->_oTemplate->pageCode ($sMsg, true, false);
            return;
        }

        echo $oForm->getCode ();
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->addCss ('inviter.css');
        $this->_oTemplate->pageCode($sTitle . $aDataEntry[$this->_oDb->_sFieldTitle]);
    }

    function _actionCalendar ($iYear, $iMonth, $sTitle)
    {
        $iYear = (int)$iYear;
        $iMonth = (int)$iMonth;

        if (!$this->isAllowedBrowse()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        bx_import ('Calendar', $this->_aModule);
        $oCalendar = bx_instance ($this->_aModule['class_prefix'] . 'Calendar', array ($iYear, $iMonth, $this->_oDb, $this->_oConfig, $this->_oTemplate));

        echo $oCalendar->display();
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->pageCode($sTitle . $oCalendar->getTitle(), true, false);
    }

    function actionBrowse ($sMode = '', $sValue = '', $sValue2 = '', $sValue3 = '')
    {
        if ('user' == $sMode || 'my' == $sMode) {
            $aProfile = getProfileInfo ($this->_iProfileId);
            if (0 === strcasecmp($sValue, $aProfile['NickName']) || 'my' == $sMode) {
                $this->_browseMy ($aProfile);
                return;
            }
        }

        if (!$this->isAllowedBrowse()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }
        if ('tag' == $sMode || 'category' == $sMode)
            $sValue = uri2title($sValue);

        bx_import ('SearchResult', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'SearchResult';
        $o = new $sClass(process_db_input($sMode, BX_TAGS_STRIP), process_db_input($sValue, BX_TAGS_STRIP), process_db_input($sValue2, BX_TAGS_STRIP), process_db_input($sValue3, BX_TAGS_STRIP));

        if ($o->isError) {
            $this->_oTemplate->displayPageNotFound ();
            return;
        }

        if (bx_get('rss')) {
            echo $o->rss();
            exit;
        }

        $this->_oTemplate->pageStart();

        if ($s = $o->processing()) {
            echo $s;
        } else {
            $this->_oTemplate->displayNoData ();
            return;
        }

        $this->_oTemplate->addCss (array('unit.css', 'main.css', 'twig.css'));
        $this->_oTemplate->pageCode($o->aCurrent['title'], false, false);
    }

    function _actionSearch ($sKeyword, $sCategory, $sTitle)
    {
        if (!$this->isAllowedSearch()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        if ($sKeyword)
            $_GET['Keyword'] = $sKeyword;
        if ($sCategory)
            $_GET['Category'] = explode(',', $sCategory);

        if (is_array($_GET['Category']) && 1 == count($_GET['Category']) && !$_GET['Category'][0]) {
            unset($_GET['Category']);
            unset($sCategory);
        }

        if ($sCategory || $sKeyword) {
            $_GET['submit_form'] = 1;
        }

        bx_import ('FormSearch', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'FormSearch';
        $oForm = new $sClass ();
        $oForm->initChecker();

        if ($oForm->isSubmittedAndValid ()) {

            bx_import ('SearchResult', $this->_aModule);
            $sClass = $this->_aModule['class_prefix'] . 'SearchResult';
            $o = new $sClass('search', $oForm->getCleanValue('Keyword'), $oForm->getCleanValue('Category'));

            if ($o->isError) {
                $this->_oTemplate->displayPageNotFound ();
                return;
            }

            if ($s = $o->processing()) {

                echo $s;

            } else {
                $this->_oTemplate->displayNoData ();
                return;
            }

            $this->isAllowedSearch(true); // perform search action

            $this->_oTemplate->addCss (array('unit.css', 'main.css', 'twig.css'));
            $this->_oTemplate->pageCode($o->aCurrent['title'], false, false);

        } else {

            echo $oForm->getCode ();
            $this->_oTemplate->addCss ('main.css');
            $this->_oTemplate->pageCode($sTitle);

        }
    }

    function _actionAdd ($sTitle)
    {
        if (!$this->isAllowedAdd()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        $this->_addForm(false);

        $this->_oTemplate->addJs ('main.js');
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->addCss ('forms_extra.css');
        $this->_oTemplate->pageCode($sTitle);
    }

    function _actionEdit ($iEntryId, $sTitle)
    {
        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryById($iEntryId))) {
            $this->_oTemplate->displayPageNotFound ();
            return;
        }

        $GLOBALS['oTopMenu']->setCustomSubHeader($aDataEntry[$this->_oDb->_sFieldTitle]);
        $GLOBALS['oTopMenu']->setCustomVar($this->_sPrefix.'_view_uri', $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
            _t('_'.$this->_sPrefix) => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'home/',
            $aDataEntry[$this->_oDb->_sFieldTitle] => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri],
            $sTitle => '',
        ));

        if (!$this->isAllowedEdit($aDataEntry)) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        bx_import ('FormEdit', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'FormEdit';
        $oForm = new $sClass ($this, $aDataEntry[$this->_oDb->_sFieldAuthorId], $iEntryId, $aDataEntry);
        if (isset($aDataEntry[$this->_oDb->_sFieldJoinConfirmation]))
            $aDataEntry[$this->_oDb->_sFieldJoinConfirmation] = (int)$aDataEntry[$this->_oDb->_sFieldJoinConfirmation];

        $oForm->initChecker($aDataEntry);

        if ($oForm->isSubmittedAndValid ()) {

            $sStatus = $this->_oDb->getParam($this->_sPrefix . '_autoapproval') == 'on' || $this->isAdmin() ? 'approved' : 'pending';
            $aValsAdd = array ($this->_oDb->_sFieldStatus => $sStatus);
            if ($oForm->update ($iEntryId, $aValsAdd)) {

                $oForm->processMedia($iEntryId, $this->_iProfileId);

                $this->isAllowedEdit($aDataEntry, true); // perform action

                $this->onEventChanged ($iEntryId, $sStatus);
                header ('Location:' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri]);
                exit;

            } else {

                echo MsgBox(_t('_Error Occured'));

            }

        } else {

            echo $oForm->getCode ();

        }

        $this->_oTemplate->addJs ('main.js');
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->addCss ('forms_extra.css');
        $this->_oTemplate->pageCode($sTitle);
    }

    function _actionDelete ($iEntryId, $sMsgSuccess)
    {
        header('Content-type:text/html;charset=utf-8');

        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner($iEntryId, $this->_iProfileId, $this->isAdmin()))) {
            echo MsgBox(_t('_sys_request_page_not_found_cpt')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        if (!$this->isAllowedDelete($aDataEntry) || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            echo MsgBox(_t('_Access denied')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        if ($this->_oDb->deleteEntryByIdAndOwner($iEntryId, $this->_iProfileId, $this->isAdmin())) {
            $this->isAllowedDelete($aDataEntry, true); // perform action
            $this->onEventDeleted ($iEntryId, $aDataEntry);
            $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/' . ($this->_iProfileId ? 'user/' . $this->_oDb->getProfileNickNameById($this->_iProfileId) : '');
            $sJQueryJS = genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div', $sRedirect);
            echo MsgBox(_t($sMsgSuccess)) . $sJQueryJS;
            exit;
        }

        echo MsgBox(_t('_Error Occured')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
        exit;
    }

    function _actionMarkFeatured ($iEntryId, $sMsgSuccessAdd, $sMsgSuccessRemove)
    {
        header('Content-type:text/html;charset=utf-8');

        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner($iEntryId, $this->_iProfileId, $this->isAdmin()))) {
            echo MsgBox(_t('_sys_request_page_not_found_cpt')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        if (!$this->isAllowedMarkAsFeatured($aDataEntry) || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            echo MsgBox(_t('_Access denied')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        if ($this->_oDb->markAsFeatured($iEntryId)) {
            $this->isAllowedMarkAsFeatured($aDataEntry, true); // perform action
            $this->onEventMarkAsFeatured ($iEntryId, $aDataEntry);
            $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri];
            $sJQueryJS = genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div', $sRedirect);
            echo MsgBox($aDataEntry[$this->_oDb->_sFieldFeatured] ? $sMsgSuccessRemove : $sMsgSuccessAdd) . $sJQueryJS;
            exit;
        }

        echo MsgBox(_t('_Error Occured')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
        exit;
    }

    function _actionJoin ($iEntryId, $iProfileId, $sMsgAlreadyJoined, $sMsgAlreadyJoinedPending, $sMsgJoinSuccess, $sMsgJoinSuccessPending, $sMsgLeaveSuccess)
    {
        header('Content-type:text/html;charset=utf-8');

        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner($iEntryId, 0, true))) {
            echo MsgBox(_t('_sys_request_page_not_found_cpt')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        if (!$this->isAllowedJoin($aDataEntry) || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            echo MsgBox(_t('_Access denied')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        $isFan = $this->_oDb->isFan ($iEntryId, $this->_iProfileId, true) || $this->_oDb->isFan ($iEntryId, $this->_iProfileId, false);

        if ($isFan) {

            if ($this->_oDb->leaveEntry($iEntryId, $this->_iProfileId)) {
                $this->_onEventFanRemove($iEntryId, $this->_iProfileId, $aDataEntry, '');
                
                $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri];
                echo MsgBox($sMsgLeaveSuccess) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div', $sRedirect);
                exit;
            }

        } else {

            $isConfirmed = ($this->isEntryAdmin($aDataEntry) || !$aDataEntry[$this->_oDb->_sFieldJoinConfirmation] ? true : false);

            if ($this->_oDb->joinEntry($iEntryId, $this->_iProfileId, $isConfirmed)) {
                if ($isConfirmed) {
                    $this->onEventJoin ($iEntryId, $this->_iProfileId, $aDataEntry);
                    $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri];
                } else {
                    $this->onEventJoinRequest ($iEntryId, $this->_iProfileId, $aDataEntry);
                    $sRedirect = '';
                }
                echo MsgBox($isConfirmed ? $sMsgJoinSuccess : $sMsgJoinSuccessPending) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div', $sRedirect);
                exit;
            }
        }

        echo MsgBox(_t('_Error Occured')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
        exit;
    }

    function actionActivate ($iEntryId)
    {
        header('Content-type:text/html;charset=utf-8');

        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner($iEntryId, 0, true))) {
            echo MsgBox(_t('_sys_request_page_not_found_cpt')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        if (!$this->isAllowedActivate($aDataEntry) || 0 !== strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {
            echo MsgBox(_t('_Access denied')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
            exit;
        }

        if ($this->_oDb->activateEntry($iEntryId)) {
            $this->isAllowedActivate($aDataEntry, true);
            $sRedirect = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri];
            echo MsgBox(_t('_Success')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div', $sRedirect);
            exit;
        }

        echo MsgBox(_t('_Error Occured')) . genAjaxyPopupJS($iEntryId, 'ajaxy_popup_result_div');
        exit;
    }

    function _actionManageFansPopup ($iEntryId, $sTitle, $sFuncGetFans = 'getFans', $sFuncIsAllowedManageFans = 'isAllowedManageFans', $sFuncIsAllowedManageAdmins = 'isAllowedManageAdmins', $iMaxFans = 1000)
    {
        header('Content-type:text/html;charset=utf-8');

        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner ($iEntryId, 0, true))) {
            echo $GLOBALS['oFunctions']->transBox(MsgBox(_t('_Empty')));
            exit;
        }

        if (!$this->$sFuncIsAllowedManageFans($aDataEntry)) {
            echo $GLOBALS['oFunctions']->transBox(MsgBox(_t('_Access denied')));
            exit;
        }

        $aProfiles = array ();
        $iNum = $this->_oDb->$sFuncGetFans($aProfiles, $iEntryId, true, 0, $iMaxFans);
        if (!$iNum) {
            echo $GLOBALS['oFunctions']->transBox(MsgBox(_t('_Empty')));
            exit;
        }

        $sActionsUrl = bx_append_url_params(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "view/" . $aDataEntry[$this->_oDb->_sFieldUri],  'ajax_action=');
        $aButtons = array (
            array (
                'type' => 'submit',
                'name' => 'fans_remove',
                'value' => _t('_sys_btn_fans_remove'),
                'onclick' => "onclick=\"getHtmlData('sys_manage_items_manage_fans_content', '{$sActionsUrl}remove&ids=' + sys_manage_items_get_manage_fans_ids(), false, 'post'); return false;\"",
            ),
        );

        if ($this->$sFuncIsAllowedManageAdmins($aDataEntry)) {

            $aButtons = array_merge($aButtons, array (
                array (
                    'type' => 'submit',
                    'name' => 'fans_add_to_admins',
                    'value' => _t('_sys_btn_fans_add_to_admins'),
                    'onclick' => "onclick=\"getHtmlData('sys_manage_items_manage_fans_content', '{$sActionsUrl}add_to_admins&ids=' + sys_manage_items_get_manage_fans_ids(), false, 'post'); return false;\"",
                ),
                array (
                    'type' => 'submit',
                    'name' => 'fans_move_admins_to_fans',
                    'value' => _t('_sys_btn_fans_move_admins_to_fans'),
                    'onclick' => "onclick=\"getHtmlData('sys_manage_items_manage_fans_content', '{$sActionsUrl}admins_to_fans&ids=' + sys_manage_items_get_manage_fans_ids(), false, 'post'); return false;\"",
                ),
            ));
        };
        bx_import ('BxTemplSearchResult');
        $sControl = BxTemplSearchResult::showAdminActionsPanel('sys_manage_items_manage_fans', $aButtons, 'sys_fan_unit');

        $aVarsContent = array (
            'suffix' => 'manage_fans',
            'content' => $this->_profilesEdit($aProfiles, false, $aDataEntry),
            'control' => $sControl,
        );
        $aVarsPopup = array (
            'title' => $sTitle,
            'content' => $this->_oTemplate->parseHtmlByName('manage_items_form', $aVarsContent),
        );
        echo $GLOBALS['oFunctions']->transBox($this->_oTemplate->parseHtmlByName('popup', $aVarsPopup), true);
        exit;
    }

    function _actionSharePopup ($iEntryId, $sTitle, $bAddTempleateExt = false)
    {
        header('Content-type:text/html;charset=utf-8');

        $iEntryId = (int)$iEntryId;
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner ($iEntryId, 0, true))) {
            echo MsgBox(_t('_Empty'));
            exit;
        }

        $sEntryUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri];

        require_once (BX_DIRECTORY_PATH_INC . "shared_sites.inc.php");        
        echo getSitesHtml($sEntryUrl, $sTitle);
        exit;
    }

    function _actionTags($sTitle, $sTitleAllTags = '')
    {
        bx_import('BxTemplTagsModule');
        $aParam = array(
            'type' => $this->_sPrefix,
            'orderby' => 'popular'
        );
        $oTags = new BxTemplTagsModule($aParam, $sTitleAllTags, BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'tags');
        $this->_oTemplate->pageStart();
        echo $oTags->getCode();
        $this->_oTemplate->pageCode($sTitle, false, false);
    }

    function _actionCategories($sTitle)
    {
        bx_import('BxTemplCategoriesModule');
        $aParam = array(
            'type' => $this->_sPrefix
        );
        $oCateg = new BxTemplCategoriesModule($aParam, _t('_categ_users'), BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'categories');
        $this->_oTemplate->pageStart();
        echo $oCateg->getCode();
        $this->_oTemplate->pageCode($sTitle, false, false);
    }

    function _actionDownload ($aFileInfo, $sFieldMediaId)
    {
        $aFile = BxDolService::call('files', 'get_file_array', array($aFileInfo[$sFieldMediaId]), 'Search');
        if (!$aFile['date']) {
            $this->_oTemplate->displayPageNotFound ();
            exit;
        }
        $aFile['full_name'] = uriFilter($aFile['title']) . '.' . $aFile['extension'];
        $aPathInfo = pathinfo ($aFile['path']);
        header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header ("Content-type: " . $aFile['mime_type']);
        header ("Content-Length: " . filesize ($aFile['path']));
        header ("Content-Disposition: attachment; filename={$aFile['full_name']}");
        readfile ($aFile['path']);
        exit;
    }

    // ================================== external actions

    function serviceGetForumPermission($iMemberId, $iForumId)
    {
        $iMemberId = (int)$iMemberId;
        $iForumId = (int)$iForumId;

        $aFalse = array (
            'admin' => 0,
            'read' => 0,
            'post' => 0,
        );

        if (!($aForum = $this->_oDb->getForumById ($iForumId)))
            return $aFalse;

        if (!($aDataEntry = $this->_oDb->getEntryById ($aForum['entry_id'])))
            return $aFalse;

        $aTrue = array (
            'admin' => $aDataEntry[$this->_oDb->_sFieldAuthorId] == $iMemberId || $this->isAdmin() ? 1 : 0, // author is admin
            'read' => $this->isAllowedReadForum ($aDataEntry, $iMemberId) ? 1 : 0,
            'post' => $this->isAllowedPostInForum ($aDataEntry, $iMemberId) ? 1 : 0,
        );

        return $aTrue;
    }

    function serviceDeleteProfileData ($iProfileId)
    {
        $iProfileId = (int)$iProfileId;

        if (!$iProfileId)
            return false;

        // delete entries which belongs to particular author
        $aDataEntries = $this->_oDb->getEntriesByAuthor ($iProfileId);
        foreach ($aDataEntries as $iEntryId) {
            if ($this->_oDb->deleteEntryByIdAndOwner($iEntryId, $iProfileId, false))
                $this->onEventDeleted ($iEntryId);
        }

        // delete from list of fans/admins
        $this->_oDb->removeFanFromAllEntries ($iProfileId);
        $this->_oDb->removeAdminFromAllEntries ($iProfileId);
    }

    function serviceResponseProfileDelete ($oAlert)
    {
        if (!($iProfileId = (int)$oAlert->iObject))
            return false;

        $this->serviceDeleteProfileData ($iProfileId);

        return true;
    }

    function serviceResponseMediaDelete ($oAlert)
    {
        $iMediaId = (int)$oAlert->iObject;
        if (!$iMediaId)
            return false;

        switch ($oAlert->sUnit) {
        case 'bx_videos':
            $sMediaType = 'videos';
            break;
        case 'bx_sounds':
            $sMediaType = 'sounds';
            break;
        case 'bx_photos':
            $sMediaType = 'images';
            break;
        case 'bx_files':
            $sMediaType = 'files';
            break;
        default:
            return false;
        }

        return $this->_oDb->deleteMediaFile ($iMediaId, $sMediaType);
    }

    function _serviceGetMemberMenuItem ($sTitle, $sAlt, $sIcon, $isExtraCount = true, $sLinkAppend = '')
    {
        if (!$this->_iProfileId)
            return '';

        $oMemberMenu = bx_instance('BxDolMemberMenu');

        $aLinkInfo = array(
            'item_img_src'  => $sIcon,
            'item_img_alt'  => $sAlt,
            'item_link'     => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/my/' . $sLinkAppend,
            'item_title'    => $sTitle,
            'extra_info'    => $isExtraCount ? $this->_oDb->getCountByAuthorAndStatus($this->_iProfileId, 'approved') + $this->_oDb->getCountByAuthorAndStatus($this->_iProfileId, 'pending') : '',
        );

        return $oMemberMenu -> getGetExtraMenuLink($aLinkInfo);
    }

    function _serviceGetWallPost ($aEvent, &$aParams)
    {
        if (!($aProfile = getProfileInfo($aEvent['owner_id'])))
            return '';

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',', $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $iDeleted = 0;
        $aItems = array();
        foreach($aObjectIds as $iId) {
            $aItem = $this->_oDb->getEntryByIdAndOwner($iId, $aEvent['owner_id'], 1);
            if(empty($aItem))
                $iDeleted++;
            if($aItem[$this->_oDb->_sFieldStatus] == 'approved' && $aParams['obj_privacy']->check($aParams['txt_privacy_view_event'], $iId, $this->_iProfileId))
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
        if($iOwner == 0 && $bItems && isset($aParams['fields']['owner']) && !empty($aItems[0][$aParams['fields']['owner']]))
            $iOwner = (int)$aItems[0][$aParams['fields']['owner']];

        if($iDate == 0 && $bItems && isset($aParams['fields']['date']) && !empty($aItems[0][$aParams['fields']['date']]))
            $iDate = (int)$aItems[0][$aParams['fields']['date']];

        if($iOwner == 0 || empty($aItems))
            return '';

        $sCss = '';
        $sCssPrefix = str_replace('_', '-', $this->_sPrefix);
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'unit.css', 'twig.css'), true);
        else
            $this->_oTemplate->addCss(array('wall_post.css', 'unit.css', 'twig.css'));

        $iItems = count($aItems);
        $sOwner = getNickName($iOwner);

        bx_import('Voting', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Voting';
        $oVoting = new $sClass ($this->_sPrefix, 0, 0);

        //--- Grouped events
        if($iItems > 1) {
            if($iItems > 4)
                $aItems = array_slice($aItems, 0, 4);

            $aTmplItems = array();
            foreach($aItems as $aItem)
                $aTmplItems[] = array(
                    'unit' => $this->_oTemplate->unit($aItem, 'unit', $oVoting, true),
                );

            $sTmplName = isset($aParams['templates']['grouped']) ? $aParams['templates']['grouped'] : 'modules/boonex/wall/|timeline_post_twig_grouped.html';
            return array(
            	'owner_id' => $iOwner,
                'title' => _t($aParams['txt_added_new_title_plural'], $sOwner, $iItems),
                'description' => '',
                'content' => $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
	            	'mod_prefix' => $sCssPrefix,
	            	'mod_icon' => $aParams['icon'],
	                'cpt_user_name' => $sOwner,
	                'cpt_added_new' => _t($aParams['txt_added_new_plural'], $iItems),
	                'bx_repeat:items' => $aTmplItems,
	            )),
	            'date' => $iDate
            );
        }

        //--- Single public event
        $aItem = $aItems[0];

        $sTextWallObject = _t($aParams['txt_object']);

        $sTmplName = isset($aParams['templates']['single']) ? $aParams['templates']['single'] : 'modules/boonex/wall/|timeline_post_twig.html';
        return array(
        	'owner_id' => $iOwner,
            'title' => _t($aParams['txt_added_new_title_single'], $sOwner, $sTextWallObject),
            'description' => $aItem[$this->_oDb->_sFieldDescription],
            'content' => $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
				'mod_prefix' => $sCssPrefix,
				'mod_icon' => $aParams['icon'],
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t($aParams['txt_added_new_single']),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $sBaseUrl . $aItem[$this->_oDb->_sFieldUri],
	            'content' => $this->_oTemplate->unit($aItem, 'unit', $oVoting, true),
	        )),
	        'date' => $iDate
        );
    }

    function _serviceGetWallPostOutline($aEvent, $sIcon, $aParams = array())
    {
        $iNoPhotoWidth = $iNoPhotoHeight = 140;
        $sNoPhoto = $this->_oTemplate->getImageUrl('no-image-thumb.png');
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';

        $aOwner = db_assoc_arr("SELECT `ID` AS `id`, `NickName` AS `username` FROM `Profiles` WHERE `ID`='" . (int)$aEvent['owner_id'] . "' LIMIT 1");

        $aObjectIds = strpos($aEvent['object_id'], ',') !== false ? explode(',', $aEvent['object_id']) : array($aEvent['object_id']);
        rsort($aObjectIds);

        $iItems = count($aObjectIds);
        $iItemsLimit = isset($aParams['grouped']['items_limit']) ? (int)$aParams['grouped']['items_limit'] : 3;
        if($iItems > $iItemsLimit)
            $aObjectIds = array_slice($aObjectIds, 0, $iItemsLimit);

        $bSave = false;
        $aContent = array();
        if(!empty($aEvent['content']))
            $aContent = unserialize($aEvent['content']);

        if(!isset($aContent['idims']))
            $aContent['idims'] = array();

        $iDeleted = 0;
        $aItems = $aTmplItems = array();
        foreach($aObjectIds as $iId) {
            $aItem = $this->_oDb->getEntryByIdAndOwner($iId, $aEvent['owner_id'], 1);
            if(empty($aItem))
                $iDeleted++;
            else if($aItem[$this->_oDb->_sFieldStatus] == 'approved' && $aParams['obj_privacy']->check($aParams['txt_privacy_view_event'], $iId, $this->_iProfileId)) {
                $aItem['thumb_file'] = '';
                $aItem['thumb_dims'] = array();
                if($aItem[$this->_oDb->_sFieldThumb]) {
                    $aImage = BxDolService::call('photos', 'get_entry', array($aItem[$this->_oDb->_sFieldThumb], 'browse'), 'Search');
                    if(!empty($aImage)) {
                        if(!isset($aContent['idims'][$iId])) {
                            $sPath = isset($aImage['file_path']) && file_exists($aImage['file_path']) ? $aImage['file_path'] : $aImage['file'];
                            $aContent['idims'][$iId] = BxDolImageResize::instance()->getImageSize($sPath);
                            $bSave = true;
                        }

                        $aItem['thumb_file'] = $aImage['file'];
                        $aItem['thumb_dims'] = $aContent['idims'][$iId];
                    }

                    $aImage = BxDolService::call('photos', 'get_entry', array($aItem[$this->_oDb->_sFieldThumb], 'browse2x'), 'Search');
                    $aItem['thumb_file_2x'] = !empty($aImage) ? $aImage['file'] : $aItem['thumb_file'];
                }

                $aItem[$this->_oDb->_sFieldUri] = $sBaseUrl . $aItem[$this->_oDb->_sFieldUri];
                $aItems[] = $aItem;

                $aTmplItems[] = array(
                    'mod_prefix' => $this->_sPrefix,
                    'item_width' => isset($aItem['thumb_dims']['w']) ? $aItem['thumb_dims']['w'] : $iNoPhotoWidth,
                    'item_height' => isset($aItem['thumb_dims']['h']) ? $aItem['thumb_dims']['h'] : $iNoPhotoHeight,
                    'item_icon' => !empty($aItem['thumb_file']) ? $aItem['thumb_file'] : $sNoPhoto,
                	'item_icon_2x' => !empty($aItem['thumb_file_2x']) ? $aItem['thumb_file_2x'] : $sNoPhoto,
                    'item_page' => $aItem[$this->_oDb->_sFieldUri],
                    'item_title' => $aItem[$this->_oDb->_sFieldTitle]
                );
            }
        }

        if($iDeleted == count($aObjectIds))
            return array('perform_delete' => true);

        if(empty($aOwner) || empty($aItems))
            return "";

        $aResult = array();
        if($bSave)
            $aResult['save']['content'] = serialize($aContent);

        $sCss = "";
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss('wall_outline.css', true);
        else
            $this->_oTemplate->addCss('wall_outline.css');

        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);
        $sOwnerLink = getProfileLink($iOwner);

        //--- Grouped events
        $iItems = count($aItems);
        if($iItems > 1) {
            $sTmplName = isset($aParams['templates']['grouped']) ? $aParams['templates']['grouped'] : 'modules/boonex/wall/|outline_item_image_grouped';
            $aResult['content'] = $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
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

        $sTmplName = isset($aParams['templates']['single']) ? $aParams['templates']['single'] : 'modules/boonex/wall/|outline_item_image';
        $aResult['content'] = $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array_merge($aTmplItem, array(
            'mod_prefix' => $this->_sPrefix,
            'mod_icon' => $sIcon,
            'user_name' => $sOwner,
            'user_link' => $sOwnerLink,
            'item_page' => $aItem[$this->_oDb->_sFieldUri],
            'item_title' => $aItem[$this->_oDb->_sFieldTitle],
            'item_description' => $this->_formatSnippetTextForOutline($aItem),
            'item_comments' => (int)$aItem[$this->_oDb->_sFieldCommentCount] > 0 ? _t('_wall_n_comments', $aItem[$this->_oDb->_sFieldCommentCount]) : _t('_wall_no_comments'),
            'item_comments_link' => $aItem[$this->_oDb->_sFieldUri] . '#cmta-' . $this->_sPrefix . '-' . $aItem[$this->_oDb->_sFieldId],
            'post_id' => $aEvent['id'],
            'post_ago' => $aEvent['ago']
        )));

        return $aResult;
    }

    function _serviceGetWallAddComment($aEvent, $aParams)
    {
    	$iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = $iOwner != 0 ? getNickName($iOwner) : _t('_Anonymous');

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || empty($aContent['object_id']))
            return '';

		$iItem = (int)$aContent['object_id'];
        $aItem = $this->_oDb->getEntryByIdAndOwner($iItem, $iOwner, 1);
        if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        if(!$aParams['obj_privacy']->check($aParams['txt_privacy_view_event'], $iItem, $this->_iProfileId))
            return '';

        bx_import('Cmts', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Cmts';
        $oCmts = new $sClass($this->_sPrefix, $iItem);
        if(!$oCmts->isEnabled())
            return '';

        $aComment = $oCmts->getCommentRow($iId);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sImage = '';
        if($aItem[$this->_oDb->_sFieldThumb]) {
            $a = array('ID' => $aItem[$this->_oDb->_sFieldAuthorId], 'Avatar' => $aItem[$this->_oDb->_sFieldThumb]);
            $aImage = BxDolService::call('photos', 'get_image', array($a, 'browse'), 'Search');
            $sImage = $aImage['no_image'] ? '' : $aImage['file'];
        }

        $sCss = '';
        $sCssPrefix = str_replace('_', '-', $this->_sPrefix);
        $sUri = $this->_oConfig->getUri();
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';
        $sNoPhoto = $this->_oTemplate->getIconUrl('no-photo.png');
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'unit.css', 'twig.css'), true);
        else
            $this->_oTemplate->addCss(array('wall_post.css', 'unit.css', 'twig.css'));

        bx_import('Voting', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Voting';
        $oVoting = new $sClass ($this->_sPrefix, 0, 0);

        $sTextWallObject = _t('_bx_' . $sUri . '_wall_object');

        $sTmplName = isset($aParams['templates']['main']) ? $aParams['templates']['main'] : 'modules/boonex/wall/|timeline_comment.html';
        return array(
            'title' => _t('_bx_' . $sUri . '_wall_added_new_title_comment', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content' => $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
        		'mod_prefix' => $sCssPrefix,
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t('_bx_' . $sUri . '_wall_added_new_comment'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $sBaseUrl . $aItem[$this->_oDb->_sFieldUri],
	            'cnt_comment_text' => $aComment['cmt_text'],
	            'snippet' => $this->_oTemplate->unit($aItem, 'unit', $oVoting)
        	))
        );
    }

    /**
     * DEPRICATED, saved for backward compatibility
     */
    function _serviceGetWallPostComment($aEvent, $aParams)
    {
        $iId = (int)$aEvent['object_id'];
        $iOwner = (int)$aEvent['owner_id'];
        $sOwner = getNickName($iOwner);

        $aItem = $this->_oDb->getEntryByIdAndOwner($iId, $iOwner, 1);
        if(empty($aItem) || !is_array($aItem))
        	return array('perform_delete' => true);

        if(!$aParams['obj_privacy']->check($aParams['txt_privacy_view_event'], $iId, $this->_iProfileId))
            return '';

        $aContent = unserialize($aEvent['content']);
        if(empty($aContent) || !isset($aContent['comment_id']))
            return '';

        bx_import('Cmts', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Cmts';
        $oCmts = new $sClass($this->_sPrefix, $iId);
        if(!$oCmts->isEnabled())
            return '';

        $aComment = $oCmts->getCommentRow((int)$aContent['comment_id']);
        if(empty($aComment) || !is_array($aComment))
        	return array('perform_delete' => true);

        $sImage = '';
        if($aItem[$this->_oDb->_sFieldThumb]) {
            $a = array('ID' => $aItem[$this->_oDb->_sFieldAuthorId], 'Avatar' => $aItem[$this->_oDb->_sFieldThumb]);
            $aImage = BxDolService::call('photos', 'get_image', array($a, 'browse'), 'Search');
            $sImage = $aImage['no_image'] ? '' : $aImage['file'];
        }

        $sCss = '';
        $sLangPrefix = '_' . trim($this->_oConfig->getDbPrefix(), '_');
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/';
        $sNoPhoto = $this->_oTemplate->getIconUrl('no-photo.png');
        if($aEvent['js_mode'])
            $sCss = $this->_oTemplate->addCss(array('wall_post.css', 'unit.css', 'twig.css'), true);
        else
            $this->_oTemplate->addCss(array('wall_post.css', 'unit.css', 'twig.css'));

        bx_import('Voting', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Voting';
        $oVoting = new $sClass ($this->_sPrefix, 0, 0);

        $sTextWallObject = _t($sLangPrefix . '_wall_object');

        $sTmplName = isset($aParams['templates']['main']) ? $aParams['templates']['main'] : 'modules/boonex/wall/|timeline_comment.html';
        return array(
            'title' => _t($sLangPrefix . '_wall_added_new_title_comment', $sOwner, $sTextWallObject),
            'description' => $aComment['cmt_text'],
            'content' => $sCss . $this->_oTemplate->parseHtmlByName($sTmplName, array(
        		'mod_prefix' => $this->_sPrefix,
	            'cpt_user_name' => $sOwner,
	            'cpt_added_new' => _t($sLangPrefix . '_wall_added_new_comment'),
	            'cpt_object' => $sTextWallObject,
	            'cpt_item_url' => $sBaseUrl . $aItem[$this->_oDb->_sFieldUri],
	            'cnt_comment_text' => $aComment['cmt_text'],
	            'snippet' => $this->_oTemplate->unit($aItem, 'unit', $oVoting)
        	))
        );
    }

    function serviceGetWallData ()
    {
        return array(
            'handlers' => array(
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'add', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_wall_post', 'groupable' => 0, 'group_by' => '', 'timeline' => 1, 'outline' => 1),
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'comment_add', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_wall_add_comment', 'groupable' => 0, 'group_by' => '', 'timeline' => 1, 'outline' => 0),

                //DEPRICATED, saved for backward compatibility
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'commentPost', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_wall_post_comment', 'groupable' => 0, 'group_by' => '', 'timeline' => 1, 'outline' => 0)
            ),
            'alerts' => array(
                array('unit' => $this->_sPrefix, 'action' => 'add')
            )
        );
    }

    function _serviceGetSpyPost($sAction, $iObjectId, $iSenderId, $aExtraParams, $aLangKeys)
    {
        $aProfile = getProfileInfo($iSenderId);
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner ($iObjectId, 0, true)))
            return array();
        if (empty($aLangKeys[$sAction]))
            return array();

        return array(
            'lang_key' => $aLangKeys[$sAction],
            'params' => array(
                'profile_link' => $aProfile ? getProfileLink($iSenderId) : 'javascript:void(0)',
                'profile_nick' => $aProfile ? getNickName($aProfile['ID']) : _t('_Guest'),
                'entry_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri],
                'entry_title' => $aDataEntry[$this->_oDb->_sFieldTitle],
            ),
            'recipient_id' => $aDataEntry[$this->_oDb->_sFieldAuthorId],
            'spy_type' => 'content_activity',
        );
    }

    function serviceGetSpyData ()
    {
        return array(
            'handlers' => array(
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'add', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'change', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'join', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'rate', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
                array('alert_unit' => $this->_sPrefix, 'alert_action' => 'commentPost', 'module_uri' => $this->_aModule['uri'], 'module_class' => 'Module', 'module_method' => 'get_spy_post'),
            ),
            'alerts' => array(
                array('unit' => $this->_sPrefix, 'action' => 'add'),
                array('unit' => $this->_sPrefix, 'action' => 'change'),
                array('unit' => $this->_sPrefix, 'action' => 'join'),
                array('unit' => $this->_sPrefix, 'action' => 'rate'),
                array('unit' => $this->_sPrefix, 'action' => 'delete'),
                array('unit' => $this->_sPrefix, 'action' => 'commentPost'),
                array('unit' => $this->_sPrefix, 'action' => 'commentRemoved')
            )
        );
    }

    function serviceGetShoutboxData ()
    {
        if (!$this->_oDb->_sTableShoutbox)
            return false;

        return array(
            array(
                'name' => $this->_sPrefix,
                'title' => $this->getTitleKey($this->_aModule['uri']),
                'table' => $this->_oDb->_sPrefix . $this->_oDb->_sTableShoutbox,
                'code_allow_use' => method_exists($this, 'isFan') ? 'return BxDolService::call("' . $this->_aModule['uri'] . '", "is_fan", array($iHandler));' : '',
                'code_allow_delete' => method_exists($this, 'isEntryAdmin') ? 'return BxDolService::call("' . $this->_aModule['uri'] . '", "is_entry_admin", array($iHandler));' : '',
                'code_allow_block' => '',
            ),
        );
    }

    function serviceIsFan($iEntryId, $iProfileId = 0)
    {
        return $this->_serviceIsSomebody('isFan', $iEntryId, $iProfileId);
    }

    function serviceIsEntryAdmin($iEntryId, $iProfileId = 0)
    {
        return $this->_serviceIsSomebody('isEntryAdmin', $iEntryId, $iProfileId);
    }

    function _serviceIsSomebody($sFunc, $iEntryId, $iProfileId = 0)
    {
        if (!method_exists($this, $sFunc))
            return false;
        if (!$iProfileId)
            $iProfileId = getLoggedId();

        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner ($iEntryId, 0, true)))
            return false;

        return $this->$sFunc($aDataEntry, $iProfileId);
    }

    function _serviceGetSubscriptionParams ($sAction, $iEntryId, $aAction2Name)
    {
        if (!($aDataEntry = $this->_oDb->getEntryByIdAndOwner ($iEntryId, 0, true)) || $aDataEntry[$this->_oDb->_sFieldStatus] != 'approved') {
            return array('skip' => true);
        }

        if (isset($aAction2Name[$sAction]))
            $sActionName = $aAction2Name[$sAction];
        else
            $sActionName = '';

        return array (
            'skip' => false,
            'template' => array (
                'Subscription' => $aDataEntry[$this->_oDb->_sFieldTitle] . ($sActionName ? ' (' . $sActionName . ')' : ''),
                'EntryTitle' => $aDataEntry[$this->_oDb->_sFieldTitle],
                'ActionName' => $sActionName,
                'ViewLink' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri],
            ),
        );
    }

    // ================================== admin actions

    function _actionAdministrationSettings ($sSettingsCatName = '')
    {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $sSettingsCatName))
            return MsgBox(_t('_sys_request_page_not_found_cpt'));

        $iId = $this->_oDb->getSettingsCategory($sSettingsCatName);
        if(empty($iId))
           return MsgBox(_t('_sys_request_page_not_found_cpt'));

        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if(isset($_POST['save']) && isset($_POST['cat'])) {
            $oSettings = new BxDolAdminSettings($iId);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        $oSettings = new BxDolAdminSettings($iId);
        $sResult = $oSettings->getForm();

        if($mixedResult !== true && !empty($mixedResult))
            $sResult = $mixedResult . $sResult;

        $aVars = array (
            'content' => $sResult,
        );
        return $this->_oTemplate->parseHtmlByName('default_padding', $aVars);
    }

    function _actionAdministrationManage ($isAdminEntries, $sKeyBtnDelete = '', $sKeyBtnActivate = '', $sUrl = false)
    {
        if (getPostFieldIfSet('action_activate') && (isset($_POST['entry']) && is_array($_POST['entry']))) {

            foreach ($_POST['entry'] as $iId) {
                if ($this->_oDb->activateEntry($iId)) {
                    $this->onEventChanged ($iId, 'approved');
                }
            }

        } elseif (getPostFieldIfSet('action_delete') && (isset($_POST['entry']) && is_array($_POST['entry']))) {

            foreach ($_POST['entry'] as $iId) {

                $aDataEntry = $this->_oDb->getEntryById($iId);
                if (!$this->isAllowedDelete($aDataEntry))
                    continue;

                if ($this->_oDb->deleteEntryByIdAndOwner($iId, 0, $this->isAdmin())) {
                    $this->onEventDeleted ($iId);
                }
            }
        }

        if ($isAdminEntries) {
            $sContent = $this->_manageEntries ('admin', '', true, 'bx_twig_admin_form', array(
                'action_delete' => $sKeyBtnDelete,
            ), '', true, 0, $sUrl);
        } else {
            $sContent = $this->_manageEntries ('pending', '', true, 'bx_twig_admin_form', array(
                'action_activate' => $sKeyBtnActivate,
                'action_delete' => $sKeyBtnDelete,
            ), '', true, 0, $sUrl);
        }

        return $sContent;
    }

    function actionAdministrationCreateEntry ()
    {
        if (!$this->isAllowedAdd()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        ob_start();
        $this->_addForm(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/admin_entries');
        $aVars = array (
            'content' => ob_get_clean(),
        );
        return $this->_oTemplate->parseHtmlByName('default_padding', $aVars);
    }

    // ================================== tags/cats reparse functions

    function reparseTags ($iEntryId)
    {
        $iEntryId = (int)$iEntryId;
        bx_import('BxDolTags');
        $o = new BxDolTags ();
        $o->reparseObjTags($this->_sPrefix, $iEntryId);
    }

    function reparseCategories ($iEntryId)
    {
        $iEntryId = (int)$iEntryId;
        bx_import('BxDolCategories');
        $o = new BxDolCategories ();
        $o->reparseObjTags($this->_sPrefix, $iEntryId);
    }

    // ================================== events

    function onEventCreate ($iEntryId, $sStatus, $aDataEntry = array())
    {
        if ('approved' == $sStatus) {
            $this->reparseTags ($iEntryId);
            $this->reparseCategories ($iEntryId);
        }

        if (BxDolModule::getInstance('BxWmapModule'))
            BxDolService::call('wmap', 'response_entry_add', array($this->_oConfig->getUri(), $iEntryId));

        $this->_oDb->createForum ($aDataEntry, $this->_oDb->getProfileNickNameById($this->_iProfileId));
        $oAlert = new BxDolAlerts($this->_sPrefix, 'add', $iEntryId, $this->_iProfileId, array('Status' => $sStatus));
        $oAlert->alert();
    }

    function onEventChanged ($iEntryId, $sStatus)
    {
        $this->reparseTags ($iEntryId);
        $this->reparseCategories ($iEntryId);

        if (BxDolModule::getInstance('BxWmapModule'))
            BxDolService::call('wmap', 'response_entry_change', array($this->_oConfig->getUri(), $iEntryId));

        $oAlert = new BxDolAlerts($this->_sPrefix, 'change', $iEntryId, $this->_iProfileId, array('Status' => $sStatus));
        $oAlert->alert();
    }

    function onEventDeleted ($iEntryId, $aDataEntry = array())
    {
        // delete associated tags and categories
        $this->reparseTags ($iEntryId);
        $this->reparseCategories ($iEntryId);

        // delete votings
        bx_import('Voting', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Voting';
        $oVoting = new $sClass ($this->_sPrefix, 0, 0);
        $oVoting->deleteVotings ($iEntryId);

        // delete comments
        bx_import('Cmts', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'Cmts';
        $oCmts = new $sClass ($this->_sPrefix, $iEntryId);
        $oCmts->onObjectDelete ();

        // delete views
        bx_import ('BxDolViews');
        $oViews = new BxDolViews($this->_sPrefix, $iEntryId, false);
        $oViews->onObjectDelete();

        // delete forum
        $this->_oDb->deleteForum ($iEntryId);

        // delete associated locations
        if (BxDolModule::getInstance('BxWmapModule'))
            BxDolService::call('wmap', 'response_entry_delete', array($this->_oConfig->getUri(), $iEntryId));

		// delete all subscriptions
		$oSubscription = BxDolSubscription::getInstance();
        $oSubscription->unsubscribe(array('type' => 'object_id', 'unit' => $this->_sPrefix, 'object_id' => $iEntryId));

        // arise alert
        $oAlert = new BxDolAlerts($this->_sPrefix, 'delete', $iEntryId, $this->_iProfileId);
        $oAlert->alert();
    }

    function onEventMarkAsFeatured ($iEntryId, $aDataEntry)
    {
        // arise alert
        $oAlert = new BxDolAlerts($this->_sPrefix, 'mark_as_featured', $iEntryId, $this->_iProfileId, array('Featured' => $aDataEntry[$this->_oDb->_sFieldFeatured]));
        $oAlert->alert();
    }

    function onEventJoin ($iEntryId, $iProfileId, $aDataEntry)
    {
        // we do not need to send any notofication mail here because it will be part of standard subscription process
        $oAlert = new BxDolAlerts($this->_sPrefix, 'join', $iEntryId, $iProfileId);
        $oAlert->alert();

        bx_import('BxDolSubscription');
        $oSubscription = BxDolSubscription::getInstance();
        $oSubscription->subscribeMember($iProfileId, $this->_sPrefix, '', $iEntryId);
    }

    function _onEventJoinRequest ($iEntryId, $iProfileId, $aDataEntry, $sEmailTemplate, $iMaxFans = 1000)
    {
        $iNum = $this->_oDb->getAdmins($aGroupAdmins, $iEntryId, 0, $iMaxFans);
        $aGroupAdmins[] = getProfileInfo($aDataEntry[$this->_oDb->_sFieldAuthorId]);
        foreach ($aGroupAdmins as $aProfile)
            $this->_notifyEmail ($sEmailTemplate, $aProfile['ID'], $aDataEntry);

        $oAlert = new BxDolAlerts($this->_sPrefix, 'join_request', $iEntryId, $iProfileId);
        $oAlert->alert();
    }

    function _onEventJoinReject ($iEntryId, $iProfileId, $aDataEntry, $sEmailTemplate)
    {
        $this->_notifyEmail ($sEmailTemplate, $iProfileId, $aDataEntry);
        $oAlert = new BxDolAlerts($this->_sPrefix, 'join_reject', $iEntryId, $iProfileId);
        $oAlert->alert();
    }

    function _onEventFanRemove ($iEntryId, $iProfileId, $aDataEntry, $sEmailTemplate)
    {
        if ($sEmailTemplate)
            $this->_notifyEmail ($sEmailTemplate, $iProfileId, $aDataEntry);
        
        $oAlert = new BxDolAlerts($this->_sPrefix, 'fan_remove', $iEntryId, $iProfileId);
        $oAlert->alert();

        bx_import('BxDolSubscription');
        $oSubscription = BxDolSubscription::getInstance();
        $oSubscription->unsubscribeMember($iProfileId, $this->_sPrefix, '', $iEntryId);        
    }

    function _onEventFanBecomeAdmin ($iEntryId, $iProfileId, $aDataEntry, $sEmailTemplate)
    {
        $this->_notifyEmail ($sEmailTemplate, $iProfileId, $aDataEntry);
        $oAlert = new BxDolAlerts($this->_sPrefix, 'fan_become_admin', $iEntryId, $iProfileId);
        $oAlert->alert();
    }

    function _onEventAdminBecomeFan ($iEntryId, $iProfileId, $aDataEntry, $sEmailTemplate)
    {
        $this->_notifyEmail ($sEmailTemplate, $iProfileId, $aDataEntry);
        $oAlert = new BxDolAlerts($this->_sPrefix, 'admin_become_fan', $iEntryId, $iProfileId);
        $oAlert->alert();
    }

    function _onEventJoinConfirm ($iEntryId, $iProfileId, $aDataEntry, $sEmailTemplate)
    {
        $this->_notifyEmail ($sEmailTemplate, $iProfileId, $aDataEntry);
        $oAlert = new BxDolAlerts($this->_sPrefix, 'join_confirm', $iEntryId, $iProfileId);
        $oAlert->alert();
    }

    // ================================== other function

    function isAdmin ()
    {
        return $GLOBALS['logged']['admin'] && isProfileActive($this->_iProfileId);
    }

    function _addForm ($sRedirectUrl)
    {
        bx_import ('FormAdd', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'FormAdd';
        $oForm = new $sClass ($this, $this->_iProfileId);
        $oForm->initChecker();

        if ($oForm->isSubmittedAndValid ()) {

            $sStatus = $this->_oDb->getParam($this->_sPrefix.'_autoapproval') == 'on' || $this->isAdmin() ? 'approved' : 'pending';
            $aValsAdd = array (
                $this->_oDb->_sFieldCreated => time(),
                $this->_oDb->_sFieldUri => $oForm->generateUri(),
                $this->_oDb->_sFieldStatus => $sStatus,
            );
            $aValsAdd[$this->_oDb->_sFieldAuthorId] = $this->_iProfileId;

            $iEntryId = $oForm->insert ($aValsAdd);

            if ($iEntryId) {

                $this->isAllowedAdd(true); // perform action

                $oForm->processMedia($iEntryId, $this->_iProfileId);

                $aDataEntry = $this->_oDb->getEntryByIdAndOwner($iEntryId, $this->_iProfileId, $this->isAdmin());
                $this->onEventCreate($iEntryId, $sStatus, $aDataEntry);
                if (!$sRedirectUrl)
                    $sRedirectUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri];
                header ('Location:' . $sRedirectUrl);
                exit;

            } else {

                MsgBox(_t('_Error Occured'));
            }

        } else {

            echo $oForm->getCode ();

        }
    }

    function _manageEntries ($sMode, $sValue, $isFilter, $sFormName, $aButtons, $sAjaxPaginationBlockId = '', $isMsgBoxIfEmpty = true, $iPerPage = 0, $sUrlAdmin = false)
    {
        bx_import ('SearchResult', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'SearchResult';
        $o = new $sClass($sMode, $sValue);
        $o->sUnitTemplate = 'unit_admin';

        if ($iPerPage)
            $o->aCurrent['paginate']['perPage'] = $iPerPage;

        $sPagination = $sActionsPanel = '';
        if ($o->isError) {
            $sContent = MsgBox(_t('_Error Occured'));
        } elseif (!($sContent = $o->displayResultBlock())) {
            if ($isMsgBoxIfEmpty)
                $sContent = MsgBox(_t('_Empty'));
            else
                return '';
        } else {
            $sPagination = $sAjaxPaginationBlockId ? $o->showPaginationAjax($sAjaxPaginationBlockId) : $o->showPagination(array('url_admin' => $sUrlAdmin));
            $sActionsPanel = $o->showAdminActionsPanel ($sFormName, $aButtons);
        }

        $aVars = array (
            'form_name' => $sFormName,
            'content' => $sContent,
            'pagination' => $sPagination,
            'filter_panel' => $isFilter ? $o->showAdminFilterPanel(false !== bx_get($this->_sFilterName) ? bx_get($this->_sFilterName) : '', 'filter_input_id', 'filter_checkbox_id', $this->_sFilterName) : '',
            'actions_panel' => $sActionsPanel,
        );
        return  $this->_oTemplate->parseHtmlByName ('manage', $aVars);
    }

    function _preProductTabs ($sUri, $sSubTab = '')
    {
        if ($GLOBALS['oTemplConfig']->bAllowUnicodeInPreg)
            $sReg = '/^[\pL\pN\-_]+$/u'; // unicode characters
        else
            $sReg = '/^[\d\w\-_]+$/u'; // latin characters only

        if (!preg_match($sReg, $sUri)) {
            $this->_oTemplate->displayPageNotFound ();
            return false;
        }

        if (!($aDataEntry = $this->_oDb->getEntryByUri($sUri))) {
            $this->_oTemplate->displayPageNotFound ();
            return false;
        }

        if ($aDataEntry[$this->_oDb->_sFieldStatus] == 'pending' && !$this->isAdmin() && !($aDataEntry[$this->_oDb->_sFieldAuthorId] == $this->_iProfileId && $aDataEntry[$this->_oDb->_sFieldAuthorId]))  {
            $this->_oTemplate->displayPageNotFound ();
            return false;
        }

        $GLOBALS['oTopMenu']->setCustomSubHeader($aDataEntry[$this->_oDb->_sFieldTitle]);
        $GLOBALS['oTopMenu']->setCustomVar($this->_sPrefix.'_view_uri', $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomSubHeaderUrl(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri]);
        $GLOBALS['oTopMenu']->setCustomBreadcrumbs(array(
            _t('_'.$this->_sPrefix) => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'home/',
            $aDataEntry[$this->_oDb->_sFieldTitle] => $sSubTab ? BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri] : '',
            $sSubTab => '',
        ));

        if ((!$this->_iProfileId || $aDataEntry[$this->_oDb->_sFieldAuthorId] != $this->_iProfileId) && !$this->isAllowedView($aDataEntry, true)) {
            $this->_oTemplate->displayAccessDenied ();
            return false;
        }

        return $aDataEntry;
    }

    function _processFansActions ($aDataEntry, $iMaxFans = 1000)
    {
        header('Content-type:text/html;charset=utf-8');

        if (false !== bx_get('ajax_action') && $this->isAllowedManageFans($aDataEntry) && 0 === strcasecmp($_SERVER['REQUEST_METHOD'], 'POST')) {

            $iEntryId = $aDataEntry[$this->_oDb->_sFieldId];
            $aIds = array ();
            if (false !== bx_get('ids'))
                $aIds = $this->_getCleanIdsArray (bx_get('ids'));

            $isShowConfirmedFansOnly = false;
            switch (bx_get('ajax_action')) {
                case 'remove':
                    $isShowConfirmedFansOnly = true;
                    if ($this->_oDb->removeFans($iEntryId, $aIds)) {
                        foreach ($aIds as $iProfileId)
                            $this->onEventFanRemove ($iEntryId, $iProfileId, $aDataEntry);
                    }
                    break;
                case 'add_to_admins':
                    $isShowConfirmedFansOnly = true;
                    if ($this->isAllowedManageAdmins($aDataEntry) && $this->_oDb->addGroupAdmin($iEntryId, $aIds)) {
                        $aProfiles = array ();
                        $iNum = $this->_oDb->getAdmins($aProfiles, $iEntryId, 0, $iMaxFans, $aIds);
                        foreach ($aProfiles as $aProfile)
                            $this->onEventFanBecomeAdmin ($iEntryId, $aProfile['ID'], $aDataEntry);
                    }
                    break;
                case 'admins_to_fans':
                    $isShowConfirmedFansOnly = true;
                    $iNum = $this->_oDb->getAdmins($aGroupAdmins, $iEntryId, 0, $iMaxFans);
                    if ($this->isAllowedManageAdmins($aDataEntry) && $this->_oDb->removeGroupAdmin($iEntryId, $aIds)) {
                        foreach ($aGroupAdmins as $aProfile) {
                            if (in_array($aProfile['ID'], $aIds))
                                $this->onEventAdminBecomeFan ($iEntryId, $aProfile['ID'], $aDataEntry);
                        }
                    }
                    break;
                case 'confirm':
                    if ($this->_oDb->confirmFans($iEntryId, $aIds)) {
                        echo '<script type="text/javascript" language="javascript">
                            document.location = "' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "view/" . $aDataEntry[$this->_oDb->_sFieldUri] . '";
                        </script>';
                        $aProfiles = array ();
                        $iNum = $this->_oDb->getFans($aProfiles, $iEntryId, true, 0, $iMaxFans, $aIds);
                        foreach ($aProfiles as $aProfile) {
                            $this->onEventJoin ($iEntryId, $aProfile['ID'], $aDataEntry);
                            $this->onEventJoinConfirm ($iEntryId, $aProfile['ID'], $aDataEntry);
                        }
                    }
                    break;
                case 'reject':
                    if ($this->_oDb->rejectFans($iEntryId, $aIds)) {
                        foreach ($aIds as $iProfileId)
                            $this->onEventJoinReject ($iEntryId, $iProfileId, $aDataEntry);
                    }
                    break;
                case 'list':
                    break;
            }

            $aProfiles = array ();
            $iNum = $this->_oDb->getFans($aProfiles, $iEntryId, $isShowConfirmedFansOnly, 0, $iMaxFans);
            if (!$iNum) {
                echo MsgBox(_t('_Empty'));
            } else {
                echo $this->_profilesEdit ($aProfiles, true, $aDataEntry);
            }
            exit;
        }
    }

    function _getCleanIdsArray ($sIds, $sDivider = ',')
    {
        $a = explode($sDivider, $sIds);
        $aRet = array();
        foreach ($a as $iId) {
            if (!(int)$iId)
                continue;
            $aRet[] = (int)$iId;
        }
        return $aRet;
    }

    function _profilesEdit(&$aProfiles, $isCenterContent = false, $aDataEntry = array())
    {
        $sResult = "";
        foreach($aProfiles as $aProfile) {
            $aVars = array(
                'id' => $aProfile['ID'],
                'thumb' => get_member_thumbnail($aProfile['ID'], 'none', true),
                'bx_if:admin' => array (
                    'condition' => $aDataEntry && $this->isEntryAdmin ($aDataEntry, $aProfile['ID']) ? true : false,
                    'content' => array (),
                ),
            );
            $sResult .= $this->_oTemplate->parseHtmlByName('unit_fan', $aVars);
        }

        return $isCenterContent ? $GLOBALS['oFunctions']->centerContent ($sResult, '.sys_fan_unit') : $sResult;
    }

    function _notifyEmail ($sEmailTemplateName, $iRecipient, $aDataEntry)
    {
        if (!($aProfile = getProfileInfo ($iRecipient)))
            return false;

        bx_import ('BxDolEmailTemplates');
        $oEmailTemplate = new BxDolEmailTemplates();
        if (!$oEmailTemplate)
            return false;

        $aTemplate = $oEmailTemplate->getTemplate($sEmailTemplateName);
        $aTemplateVars = array (
            'EntryTitle' => $aDataEntry[$this->_oDb->_sFieldTitle],
            'EntryUrl' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->_oDb->_sFieldUri],
        );

        return sendMail($aProfile['Email'], $aTemplate['Subject'], $aTemplate['Body'], $aProfile['ID'], $aTemplateVars);
    }

    function _browseMy (&$aProfile, $sTitle = null)
    {
        // check access
        if (!$this->_iProfileId) {
            $this->_oTemplate->displayAccessDenied();
            return;
        }

        $bAjaxMode = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;

        // process delete action
        if (bx_get('action_delete') && is_array(bx_get('entry'))) {
            $aEntries = bx_get('entry');
            foreach ($aEntries as $iEntryId) {
                $iEntryId = (int)$iEntryId;
                $aDataEntry = $this->_oDb->getEntryById($iEntryId);
                if (!$this->isAllowedDelete($aDataEntry))
                    continue;

                if ($this->_oDb->deleteEntryByIdAndOwner($iEntryId, $this->_iProfileId, 0)) {
                    $this->onEventDeleted ($iEntryId);
                }
            }
        }

        bx_import ('PageMy', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'PageMy';
        $oPage = new $sClass ($this, $aProfile);

        // manage my data entries
        if ($bAjaxMode && ($this->_sPrefix . '_my_active') == bx_get('block')) {
            header('Content-type:text/html;charset=utf-8');
            echo $oPage->getBlockCode_My();
            exit;
        }

        // manage my pending data entries
        if ($bAjaxMode && ($this->_sPrefix . '_my_pending') == bx_get('block')) {
            header('Content-type:text/html;charset=utf-8');
            echo $oPage->getBlockCode_Pending();
            exit;
        }

        $this->_oTemplate->pageStart();

        // display whole page
        if (!$bAjaxMode)
            echo $oPage->getCode();

        $this->_oTemplate->addJs ('main.js');
        $this->_oTemplate->addCss ('main.css');
        $this->_oTemplate->addCss ('form.css');
        $this->_oTemplate->addCss ('admin.css');
        $this->_oTemplate->addCss ('forms_extra.css');
        $this->_oTemplate->pageCode($sTitle, false, false);
    }

    function isMembershipEnabledForImages ()
    {
        return $this->_isMembershipEnabledFor ('BX_PHOTOS_ADD');
    }

    function isMembershipEnabledForVideos ()
    {
        return $this->_isMembershipEnabledFor ('BX_VIDEOS_ADD');
    }

    function isMembershipEnabledForSounds ()
    {
        return $this->_isMembershipEnabledFor ('BX_SOUNDS_ADD');
    }

    function isMembershipEnabledForFiles ()
    {
        return $this->_isMembershipEnabledFor ('BX_FILES_ADD');
    }

    function _isMembershipEnabledFor ($sMembershipActionConstant)
    {
        defineMembershipActions (array('photos add', 'sounds add', 'videos add', 'files add'));
        if (!defined($sMembershipActionConstant))
            return false;
        $aCheck = checkAction(getLoggedId(), constant($sMembershipActionConstant));
        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function _formatSnippetText ($aEntryData, $iMaxLen = 300)
    {
        return strmaxtextlen($aEntryData[$this->_oDb->_sFieldDescription], $iMaxLen);
    }

    function _formatSnippetTextForOutline($aEntryData)
    {
        $s = $this->_formatSnippetText($aEntryData, 200);
        return $s;
    }

}
