<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModule.php');

/**
* Custom RSS module by BoonEx
*
* This module allow user to add custom RSS feeds to own profile page.
*
* Example of using this module to get any member RSS feeds:
*
* require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php');
* require_once( BX_DIRECTORY_PATH_MODULES . 'boonex/custom_rss/classes/BxCRSSModule.php');
* $oModuleDb = new BxDolModuleDb();
* $aModule = $oModuleDb->getModuleByUri('custom_rss');
* $oBxCRSSModule = new BxCRSSModule($aModule);
* echo $oBxCRSSModule->GenCustomRssBlock($iID); //member ID
*
*
*
* Profile's Wall:
* no wall events
*
*
*
* Spy:
* no spy events
*
*
*
* Memberships/ACL:
* no memberships available
*
*
*
* Service methods:
*
* Generate RSS feeds of profiles.
* @see BxAvaModule::serviceGenCustomRssBlock
* BxDolService::call('custom_rss', 'gen_custom_rss_block', array($iID));
*
*
*
* Alerts:
* No alerts available
*
*/
class BxCRSSModule extends BxDolModule
{
    // Variables
    var $_iProfileID;

    // Constructor
    function __construct($aModule)
    {
        parent::__construct($aModule);
    }

    /**
    * Generate member`s RSS feeds.
    *
    * @param $iID - member id
    *
    * @return html of RSS feeds of member
    */
    function serviceGenCustomRssBlock($iID)
    {
        return $this->GenCustomRssBlock($iID);
    }

    function GenCustomRssBlock($iID)
    {
        global $site;

        if (getParam('enable_crss_module') != 'on')
            return;

        $this->_iProfileID = $iID;
        $iVisitorID = getLoggedId();

        $bAjaxMode = (false !== bx_get('mode') && bx_get('mode') == 'ajax');
        $bAjaxMode2 = (false !== bx_get('mode') && bx_get('mode') == 'ajax2');

        $sHomeUrl = $this->_oConfig->getHomeUrl();

        $sManagingForm = $sRssContent = '';

        //Generation of managing form
        if ($iVisitorID>0 && $iVisitorID == $this->_iProfileID) {
            $sAddC = _t('_Submit');
            $sEditC = _t('_Edit');
            $sAddNewURLC = _t('_Enter new URL');
            $sURLC = _t('_URL');
            $sDescriptionC = _t('_Description');
            $sQuantityC = _t('_crss_Quantity');

            $sAction = bx_get('action');

            if (0 === strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') && isset($sAction) && $sAction != '') {
                $sNewUrl = process_db_input(bx_get('rss_url'), BX_TAGS_STRIP);
                $sNewDesc = process_db_input(bx_get('rss_desc'), BX_TAGS_STRIP);
                $iOldID = (int)bx_get('rss_id');
                $iQuantity = (int)bx_get('rss_quantity');

                switch ($sAction) {
                    case 'add_rss':
                        $this->_oDb->insertProfileRSS($this->_iProfileID, $sNewUrl, $sNewDesc, $iQuantity);
                        break;
                    case 'ren_rss':
                        $this->_oDb->updateProfileRSS($this->_iProfileID, $sNewUrl, $iOldID);
                        break;
                    case 'del_rss':
                        $this->_oDb->deleteProfileRSS($this->_iProfileID, $iOldID);
                        break;
                }
            }

            $sRSSList = '';

            $aMemberRSS = $this->_oDb->getProfileRSS($this->_iProfileID);

            if(count($aMemberRSS)==0) {
                $sRSSList = '<tr><td>' . MsgBox(_t('_Empty')) . '</td></tr>';
            } else {
                foreach($aMemberRSS as $sKey => $aRSSInfo) {
                    $iRssID = (int)$aRSSInfo['ID'];
                    $sRssUrl = process_line_output($aRSSInfo['RSSUrl']);
                    $sRssDesc = process_line_output($aRSSInfo['Description']);
                    $sRssStatus = process_line_output($aRSSInfo['Status']);
                    $iRssQuantity = (int)$aRSSInfo['Quantity'];

                    $aFormVariables = array (
                        'rss_url' => $sRssUrl,
                        'rss_description' => $sRssDesc,
                        'rss_status' => $sRssStatus,
                        'enter_new_url' => $sAddNewURLC,
                        'rss_url_js' => $sRssUrl,
                        'php_self' => bx_html_attribute($_SERVER['PHP_SELF']),
                        'rss_id' => $iRssID,
                        'owner_id' => $this->_iProfileID,
                        'rss_quantity' => $iRssQuantity,
                    );
                    $sRSSList .= $this->_oTemplate->parseHtmlByTemplateName('crss_unit', $aFormVariables);
                }
            }

            $sFormOnsubmitCode = <<<EOF
sLink1 = '{$sHomeUrl}crss.php?ID={$this->_iProfileID}&action=add_rss&rss_url=' + encodeUriComponent($('form#adding_custom_rss_form input[name=rss_url]').val()) + '&rss_desc=' + encodeUriComponent($('form#adding_custom_rss_form input[name=rss_desc]').val()) + '&rss_quantity=' + encodeUriComponent($('form#adding_custom_rss_form input[name=rss_quantity]').val()) + '&mode=ajax';
getHtmlData('custom_rss_lists_div', sLink1, '', 'post');
return false;
EOF;

            //adding form
            $aForm = array(
                'form_attrs' => array(
                    'name' => 'adding_custom_rss_form',
                    'action' => getProfileLink($iVisitorID),
                    'method' => 'post',
                    'onsubmit' => $sFormOnsubmitCode,
                ),
                'inputs' => array(
                    'header1' => array(
                        'type' => 'block_header',
                        'caption' => _t('_crss_add_new'),
                    ),
                    'rss_url' => array(
                        'type' => 'text',
                        'name' => 'rss_url',
                        'maxlength' => 255,
                        'caption' => $sURLC,
                    ),
                    'rss_quantity' => array(
                        'type' => 'text',
                        'name' => 'rss_quantity',
                        'maxlength' => 4,
                        'caption' => $sQuantityC,
                    ),
                    'rss_desc' => array(
                        'type' => 'text',
                        'name' => 'rss_desc',
                        'maxlength' => 255,
                        'caption' => $sDescriptionC,
                    ),
                    'hidden_id' => array(
                        'type' => 'hidden',
                        'name' => 'ID',
                        'value' => $this->_iProfileID,
                    ),
                    'hidden_action' => array(
                        'type' => 'hidden',
                        'name' => 'action',
                        'value' => 'add_rss',
                    ),
                    'add_button' => array(
                        'type' => 'submit',
                        'name' => 'submit',
                        'caption' => '',
                        'value' => $sAddC,
                    ),
                ),
            );

            $oForm = new BxTemplFormView($aForm);
            $sAddingForm = $oForm->getCode();
        }

        $sLoadingC = _t('_loading ...');

        //Generation of Active RSS`s
        unset($aMemberRSS);
        $sActiveRSSList = $sRssAggr = '';
        if ($this->_iProfileID>0) {

            //view RSS of current member
            $aMemberRSS = $this->_oDb->getActiveProfileRSS($this->_iProfileID);

            if(count($aMemberRSS)==0) {
                $sRssContent = MsgBox(_t('_Empty'));
            } else {
                foreach($aMemberRSS as $sKey => $aRSSInfo) {
                    $iRssID = (int)$aRSSInfo['ID'];
                    $iRssQuantity = (int)$aRSSInfo['Quantity'];
                    $sRssUrl = process_line_output($aRSSInfo['RSSUrl']);

                    $sActiveRSSList .= "<div class='RSSAggrCont_" . $iRssID . "' rssnum='" . $iRssQuantity . "'>" . $GLOBALS['oFunctions']->loadingBoxInline() . "</div>";
                    $sRssAggr .= "$('div.RSSAggrCont_" . $iRssID . "').dolRSSFeed('" . $sHomeUrl . "get_rss_feed.php?ID=" . $iRssID . "');";
                }

                $sRssContent = $sActiveRSSList;
            }
        }

        $sTableExRssContent = $sRSSList;

        if ($bAjaxMode) {
            echo $sTableExRssContent;
            exit;
        }

        $aFormVariables = array (
            'member_rss_list' => $sRssContent,
            'member_rss_js_aggr' => $sRssAggr,
        );
        $sReadyRssContent = $this->_oTemplate->parseHtmlByTemplateName('member_rss_list_loaded', $aFormVariables);

        if ($bAjaxMode2) {
            echo $sReadyRssContent;
            exit;
        }

        $aFormVariables = array (
            'view_css' => $this->_oTemplate->getCssUrl('view.css'),
            'main_js_url' => $sHomeUrl . 'js/main.js',
            'table_existed_rss_list' => $sTableExRssContent,
            'form_adding' => $sAddingForm,
            'member_rss_list_loaded' => $sReadyRssContent,
        );
        $sBlockContent = $this->_oTemplate->parseHtmlByTemplateName('view', $aFormVariables);

        //Action to showing managing form
        $sPreferSpeed = $this->_oConfig->getAnimationSpeed();
        $sActions = BxDolPageView::getBlockCaptionMenu(time(), array(
            'crss_t1' => array('href' => bx_html_attribute($_SERVER['PHP_SELF']), 'title' => $sEditC, 'onclick' => "ShowHideEditCRSSForm('{$sPreferSpeed}'); return false;")
        ));
        return DesignBoxContent(_t('_crss_Custom_Feeds'), $sBlockContent, 1, $sActions);
    }
}
