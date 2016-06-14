<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );

bx_import('BxTemplSearchResult');
$oBxDolDNSBlacklists = bx_instance('BxDolDNSBlacklists');

class BxDolAdmFormDnsblAdd extends BxTemplFormView
{
    function __construct ($aChains, $sDefaultMode)
    {
        $aCustomForm = array(

            'form_attrs' => array(
            'id' => 'sys-adm-dnsbl-add',
            'name' => 'sys-adm-dnsbl-add',
            'action' => BX_DOL_URL_ADMIN . 'antispam.php?action=dnsbl_add&mode='.$sDefaultMode,
            'method' => 'post',
            ),

            'params' => array (
                'db' => array(
                    'table' => 'sys_dnsbl_rules',
                    'key' => 'id',
                    'submit_name' => 'dnsbl_add',
                ),
            ),

            'inputs' => array(

                'chain' => array(
                    'type' => 'select',
                    'name' => 'chain',
                    'caption' => _t('_sys_adm_fld_dnsbl_chain'),
                    'values' => $aChains,
                    'value' => '',
                    'required' => true,
                    'checker' => array (
                        'func' => 'avail',
                        'error' => _t ('_sys_adm_form_err_required_field'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'zonedomain' => array(
                    'type' => 'text',
                    'name' => 'zonedomain',
                    'caption' => _t('_sys_adm_fld_dnsbl_zonedomain'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'avail',
                        'error' => _t ('_sys_adm_form_err_required_field'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'postvresp' => array(
                    'type' => 'text',
                    'name' => 'postvresp',
                    'caption' => _t('_sys_adm_fld_dnsbl_postvresp'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'avail',
                        'error' => _t ('_sys_adm_form_err_required_field'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'url' => array(
                    'type' => 'text',
                    'name' => 'url',
                    'caption' => _t('_sys_adm_fld_dnsbl_url'),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'recheck' => array(
                    'type' => 'text',
                    'name' => 'recheck',
                    'caption' => _t('_sys_adm_fld_dnsbl_recheck_url'),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'comment' => array(
                    'type' => 'text',
                    'name' => 'comment',
                    'caption' => _t('_sys_adm_fld_dnsbl_comment'),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'active' => array(
                    'type' => 'select',
                    'name' => 'active',
                    'caption' => _t('_sys_adm_fld_dnsbl_active'),
                    'values' => array (1 => _t('_Yes'), 0 => _t('_No')),
                    'value' => '1',
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),

                'Submit' => array (
                    'type' => 'submit',
                    'name' => 'dnsbl_add',
                    'value' => _t('_Submit'),
                    'colspan' => true,
                ),
            ),
        );

        parent::__construct ($aCustomForm);
    }
}

class BxDolAdmFormDnsblRecheck extends BxTemplFormView
{
    function __construct ($sTitle, $sId)
    {
        $aCustomForm = array(

            'form_attrs' => array(
                'id' => 'sys-adm-dnsbl-recheck',
                'name' => 'sys-adm-dnsbl-recheck',
                'onsubmit' => "return bs_sys_adm_dbsbl_recheck($('#$sId').val());",
                'method' => 'post',
            ),

            'inputs' => array(

                'test' => array(
                    'type' => 'text',
                    'attrs' => array('id' => $sId),
                    'name' => $sId,
                    'caption' => $sTitle,
                    'required' => true,
                ),

                'Submit' => array (
                    'type' => 'submit',
                    'name' => 'dnsbl_recheck',
                    'value' => _t('_Submit'),
                    'colspan' => true,
                ),
            ),
        );

        parent::__construct ($aCustomForm);
    }
}

$logged['admin'] = member_auth( 1, true, true );

$sGlMsg = '';

// Process popups
if (isset($_GET['popup'])) {

    switch ($_GET['popup']) {

        case 'dnsbl_log':
            $sPopupTitle = _t('_sys_adm_title_dnsbl_log');
            $sPopupContent = PageCodeLog ('dnsbl');
            break;

        case 'dnsbluri_log':
            $sPopupTitle = _t('_sys_adm_title_dnsbluri_log');
            $sPopupContent = PageCodeLog ('dnsbluri');
            break;

        case 'akismet_log':
            $sPopupTitle = _t('_sys_adm_title_akismet_log');
            $sPopupContent = PageCodeLog ('akismet');
            break;

        case 'stopforumspam_log':
            $sPopupTitle = _t('_sys_adm_title_stopforumspam_log');
            $sPopupContent = PageCodeLog ('stopforumspam');
            break;

        case 'dnsbl_recheck':
            $sPopupTitle = _t('_sys_adm_title_dnsbl_recheck');
            $aChains = array(BX_DOL_DNSBL_CHAIN_SPAMMERS, BX_DOL_DNSBL_CHAIN_WHITELIST);
            $sPopupContent = PageCodeRecheckPopup ($aChains, _t('_sys_adm_fld_dnsbl_recheck'), 'sys-adm-dnsbl-test', 'dnsbl-recheck-ip');
            break;

        case 'dnsbluri_recheck':
            $sPopupTitle = _t('_sys_adm_title_dnsbluri_recheck');
            $aChains = array(BX_DOL_DNSBL_CHAIN_URIDNS);
            $sPopupContent = PageCodeRecheckPopup ($aChains, _t('_sys_adm_fld_dnsbluri_recheck'), 'sys-adm-dnsbl-test', 'dnsbl-recheck-uri');
            break;

        case 'dnsbl_help':
            $sPopupTitle = _t('_sys_adm_btn_dnsbl_help');
            $sPopupContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_dnsbl_help.html', array('text' => _t('_sys_adm_btn_dnsbl_help_text')));
            break;
        case 'dnsbluri_help':
            $sPopupTitle = _t('_sys_adm_btn_dnsbl_help');
            $sPopupContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_dnsbl_help.html', array('text' => _t('_sys_adm_btn_dnsbluri_help_text')));
            break;
        case 'dnsbl_add':
            $sPopupTitle = _t('_sys_adm_btn_dnsbl_add');
            $oForm = new BxDolAdmFormDnsblAdd(array ('spammers' => 'spammers', 'whitelist' => 'whitelist'), 'dnsbl');
            $sPopupContent = $oForm->getCode();
            break;
        case 'dnsbluri_add':
            $sPopupTitle = _t('_sys_adm_btn_dnsbl_add');
            $oForm = new BxDolAdmFormDnsblAdd(array ('uridns' => 'uridns'), 'dnsbluri');
            $sPopupContent = $oForm->getCode();
            break;
    }

    $sPopupContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array(
        'content' => $sPopupContent
    ));

    header("Content-type: text/html; charset=utf-8");
    echo $GLOBALS['oFunctions']->popupBox('adm_antispam_popup', $sPopupTitle, $sPopupContent);
    exit;
}

// Process actions
switch (true) {

    case (isset($_GET['action']) && $_GET['action'] == 'log' && isset($_GET['type'])):
        header("Content-type: text/html; charset=utf-8");
        echo PageCodeLog ($_GET['type']);
        exit;

    case (isset($_POST['action']) && isset($_POST['id']) && isset($_POST['test'])):

        $o = bx_instance('BxDolDNSBlacklists');
        $aChain = $GLOBALS['MySQL']->getAll("SELECT `zonedomain`, `postvresp` FROM `sys_dnsbl_rules` WHERE `id` = ? AND `active` = 1", [$_POST['id']]);

        $iRet = BX_DOL_DNSBL_FAILURE;
        if ($aChain) {
            if ($_POST['action'] == 'dnsbl-recheck-ip') {
                $iRet = $o->dnsbl_lookup_ip($aChain, $_POST['test']);
            } elseif ($_POST['action'] == 'dnsbl-recheck-uri') {
                $sUrl = preg_replace('/^\w+:\/\//', '', $_POST['test']);
                $sUrl = preg_replace('/^www\./', '', $sUrl);
                $oBxDolDNSURIBlacklists = bx_instance('BxDolDNSURIBlacklists');
                $aUrls = $oBxDolDNSURIBlacklists->validateUrls(array($sUrl));
                if ($aUrls)
                    $iRet = $o->dnsbl_lookup_uri($aUrls[0], $aChain);
            }
        }

        switch ($iRet) {
            case BX_DOL_DNSBL_POSITIVE:
                echo 'LISTED';
                exit;
            case BX_DOL_DNSBL_NEGATIVE:
                echo 'NOT LISTED';
                exit;
            default:
            case BX_DOL_DNSBL_FAILURE:
                echo 'FAIL';
                exit;
        }

    case (isset($_POST['adm-dnsbl-activate'])):
        foreach($_POST['rules'] as $iRuleId)
            db_res("UPDATE `sys_dnsbl_rules` SET `active` = 1 WHERE `id` = " . (int)$iRuleId);
        $oBxDolDNSBlacklists->clearCache();
        break;

    case (isset($_POST['adm-dnsbl-deactivate'])):
        foreach($_POST['rules'] as $iRuleId)
            db_res("UPDATE `sys_dnsbl_rules` SET `active` = 0 WHERE `id` = " . (int)$iRuleId);
        $oBxDolDNSBlacklists->clearCache();
        break;

    case (isset($_POST['adm-dnsbl-delete'])):
        foreach($_POST['rules'] as $iRuleId)
            db_res("DELETE FROM `sys_dnsbl_rules` WHERE `id` = " . (int)$iRuleId);
        $oBxDolDNSBlacklists->clearCache();
        break;

    case (isset($_GET['action']) && 'dnsbl_add' == $_GET['action'] && $_POST['dnsbl_add']):
        $oForm = new BxDolAdmFormDnsblAdd (array(), bx_get('mode'));
        $oForm->initChecker();
        if ($oForm->isSubmittedAndValid () && $oForm->insert (array('added' => time())))
            $sGlMsg = MsgBox(_t('_sys_sucess_result'));
        else
            $sGlMsg = MsgBox(_t('_Error Occured'));
        $oBxDolDNSBlacklists->clearCache();
        break;
}

$aPages = array (
    'dnsbl' => array (
        'option' => 'sys_dnsbl_enable',
        'title' => _t('_sys_adm_page_cpt_dnsbl'),
        'url' => BX_DOL_URL_ADMIN . 'antispam.php?mode=dnsbl',
        'func' => 'PageCodeDNSBL',
        'func_params' => array(array(BX_DOL_DNSBL_CHAIN_SPAMMERS, BX_DOL_DNSBL_CHAIN_WHITELIST), 'dnsbl'),
    ),
    'dnsbluri' => array (
        'option' => 'sys_uridnsbl_enable',
        'title' => _t('_sys_adm_page_cpt_uridnsbl'),
        'url' => BX_DOL_URL_ADMIN . 'antispam.php?mode=dnsbluri',
        'func' => 'PageCodeDNSBL',
        'func_params' => array(array(BX_DOL_DNSBL_CHAIN_URIDNS), 'dnsbluri'),
    ),
    'akismet' => array (
        'option' => 'sys_akismet_enable',
        'title' => _t('_sys_adm_page_cpt_akismet'),
        'url' => BX_DOL_URL_ADMIN . 'antispam.php?mode=akismet',
        'func' => 'PageCodeAkismet',
        'func_params' => array('akismet'),
    ),
    'stopforumspam' => array (
        'option' => 'sys_stopforumspam_enable',
        'title' => _t('_sys_adm_page_cpt_stopforumspam'),
        'url' => BX_DOL_URL_ADMIN . 'antispam.php?mode=stopforumspam',
        'func' => 'PageCodeStopForumSpam',
        'func_params' => array('stopforumspam'),
    ),    
    'settings' => array (
        'option' => '',
        'title' => _t('_Settings'),
        'url' => BX_DOL_URL_ADMIN . 'antispam.php?mode=settings',
        'func' => 'PageCodeSettings',
        'func_params' => array(),
    ),
);

if (!isset($_GET['mode']) || !isset($aPages[$_GET['mode']]))
    $sMode = 'dnsbl';
else
    $sMode = $_GET['mode'];

$iNameIndex = 9;

$aTopItems = array();
foreach ($aPages as $k => $r)
    $aTopItems['dbmenu_' . $k] = array(
        'href' => $r['url'],
        'title' => $r['title'],
        'active' => $k == $sMode ? 1 : 0
    );

$sPageTitle = $aPages[$sMode]['title'];
$_page_cont[$iNameIndex]['page_main_code'] = call_user_func($aPages[$sMode]['func'], $aPages[$sMode]['func_params'][0], $aPages[$sMode]['func_params'][1]);

$_page = array(
    'name_index' => $iNameIndex,
    'header' => $sPageTitle,
    'header_text' => $sPageTitle,
    'css_name' => array('forms_adv.css', 'antispam.css'),
);

PageCodeAdmin();

function PageCodeDNSBL($aChains, $sMode)
{
    global $aPages;

    $sControls = BxTemplSearchResult::showAdminActionsPanel('adm-dnsbl-form', array(
        'adm-dnsbl-delete' => _t('_sys_adm_btn_dnsbl_delete'),
        'adm-dnsbl-activate' => _t('_sys_adm_btn_dnsbl_activate'),
        'adm-dnsbl-deactivate' => _t('_sys_adm_btn_dnsbl_deactivate'),
    ), 'rules');

    $sPlaceholders = implode(',', array_fill(0, count($aChains), '?'));
    $aRules = $GLOBALS['MySQL']->getAll("SELECT * FROM `sys_dnsbl_rules` WHERE `chain` IN($sPlaceholders) ORDER BY `chain`, `added` ", $aChains);
    foreach ($aRules as $k => $r) {
        $aRules[$k]['comment'] = bx_html_attribute ($r['comment']);
    }

    $bMode = getParam($aPages[$sMode]['option']) == 'on';
    $sTopControls = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_manage_dnsbl_top_controls.html', array(
        'status' => $bMode ? _t('_sys_adm_enabled') : _t('_sys_adm_disabled'),
        'status_class' => 'sys-adm-' . ($bMode ? 'enabled' : 'disabled'),
        'mode' => $sMode,
    ));

    if (is_array($aRules) && !empty($aRules)) {
        $s = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_manage_dnsbl.html', array(
            'top_controls' => $sTopControls,
            'bx_repeat:items' => $aRules,
            'controls' => $sControls,
            'global_message' => $GLOBALS['sGlMsg'],
            'mode' => $sMode
        ));
    } else {
        $s = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_manage_dnsbl.html', array(
            'top_controls' => $sTopControls,
            'bx_repeat:items' => array(),
            'controls' => '',
            'global_message' => MsgBox(_t('_Empty')),
            'mode' => $sMode
        ));
    }

    return DesignBoxAdmin ($GLOBALS['sPageTitle'], $s, $GLOBALS['aTopItems']);
}

function PageCodeAkismet($sMode)
{
    global $aPages;

    $sKeyStatusClass = '';
    $sKeyStatus = _t('_sys_adm_akismet_key_empty');
    if (getParam('sys_akismet_api_key')) {

        $oBxDolAkismet = bx_instance('BxDolAkismet');
        if ($oBxDolAkismet->oAkismet->isKeyValid()) {
            $sKeyStatusClass = 'sys-adm-enabled';
            $sKeyStatus = _t('_sys_adm_akismet_key_valid');
        } else {
            $sKeyStatusClass = 'sys-adm-disabled';
            $sKeyStatus = _t('_sys_adm_akismet_key_invalid');
        }
    }

    $bMode = getParam($aPages[$sMode]['option']) == 'on';
    $sTopControls = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_akismet_top_controls.html', array(
        'status' => $bMode ? _t('_sys_adm_enabled') : _t('_sys_adm_disabled'),
        'status_class' => 'sys-adm-' . ($bMode ? 'enabled' : 'disabled')
    ));

    $s = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_akismet.html', array(
        'top_controls' => $sTopControls,
        'key_status' => $sKeyStatus,
        'key_status_class' => $sKeyStatusClass,
    ));

    return DesignBoxAdmin($GLOBALS['sPageTitle'], $s, $GLOBALS['aTopItems']);
}

function PageCodeStopForumSpam($sMode)
{
    global $aPages;

    $sKeyStatusClass = '';
    $sKeyStatus = _t('_sys_adm_stopforumspam_key_empty');
    if (getParam('sys_stopforumspam_api_key')) {
        $sKeyStatusClass = 'sys-adm-enabled';
        $sKeyStatus = _t('_sys_adm_stopforumspam_key_specified');
    }

    $bMode = getParam($aPages[$sMode]['option']) == 'on';
    $sTopControls = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_stopforumspam_top_controls.html', array(
        'status' => $bMode ? _t('_sys_adm_enabled') : _t('_sys_adm_disabled'),
        'status_class' => 'sys-adm-' . ($bMode ? 'enabled' : 'disabled')
    ));

    $s = $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_akismet.html', array(
        'top_controls' => $sTopControls,
        'key_status' => $sKeyStatus,
        'key_status_class' => $sKeyStatusClass,
    ));

    return DesignBoxAdmin($GLOBALS['sPageTitle'], $s, $GLOBALS['aTopItems']);
}

function PageCodeRecheckPopup ($aChains, $sFieldTitle, $sId, $sAction)
{
    $sPlaceholders = implode(',', array_fill(0, count($aChains), '?'));
    $aRules = $GLOBALS['MySQL']->getAll("SELECT * FROM `sys_dnsbl_rules` WHERE `chain` IN($sPlaceholders) AND `active` = 1 ORDER BY `chain`, `added` ", $aChains);
    $oForm = new BxDolAdmFormDnsblRecheck($sFieldTitle, $sId);
    return $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_dnsbl_recheck.html', array(
        'txt_listed' => bx_js_string(_t('_sys_adm_dnsbl_listed')),
        'txt_not_listed' => bx_js_string(_t('_sys_adm_dnsbl_not_listed')),
        'txt_failed' => bx_js_string(_t('_sys_adm_dnsbl_failed')),
        'form' => $oForm->getCode(),
        'action' => $sAction,
        'admin_url' => BX_DOL_URL_ADMIN,
        'bx_repeat:items' => $aRules,
    ));
}

function PageCodeLog ($sMode)
{
    switch ($sMode) {
        case 'dnsbl':
        case 'dnsbluri':
        case 'akismet':
        case 'stopforumspam':
            break;
        default:
            $sMode = 'dnsbl';
    }

    $iPage = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $iPerPage = 12;
    $iStart = ($iPage-1) * $iPerPage;

    $aLog = $GLOBALS['MySQL']->getAll("SELECT SQL_CALC_FOUND_ROWS * FROM `sys_antispam_block_log` WHERE `type` = ? ORDER BY `added` DESC LIMIT $iStart, $iPerPage", [$sMode]);
    $iCount = $GLOBALS['MySQL']->getOne("SELECT FOUND_ROWS()");
    foreach ($aLog as $k => $r) {
        $aLog[$k]['ip'] = long2ip ($r['ip']);
        $aLog[$k]['member_url'] = $r['member_id'] ? getProfileLink($r['member_id']) : 'javascript:void(0);';
        $aLog[$k]['member_nickname'] = $r['member_id'] ? getNickName($r['member_id']) : _t('_Guest');
        $aLog[$k]['extra'] = bx_html_attribute ($r['extra']);
        $aLog[$k]['ago'] = defineTimeInterval ($r['added']);
    }

    $sPaginate = '';
    if ($iCount > $iPerPage) {
        $sUrlStart = BX_DOL_URL_ADMIN . 'antispam.php?action=log&type='.$sMode;
        $oPaginate = new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'count' => $iCount,
            'per_page' => $iPerPage,
            'page' => $iPage,
            'on_change_page' => "getHtmlData('sys-adm-antispam-log', '{$sUrlStart}&page={page}');",
        ));

        $sPaginate = $oPaginate->getSimplePaginate(false, -1, -1, false);
    }

    if (is_array($aLog) && !empty($aLog)) {
        return $GLOBALS['oAdmTemplate']->parseHtmlByName('antispam_log.html', array(
            'bx_repeat:items' => $aLog,
            'paginate' => $sPaginate,
        ));
    } else {
        return MsgBox(_t('_Empty'));
    }
}

function PageCodeSettings()
{
    global $aPages;

    bx_import('BxDolAdminSettings');
    $oSettings = new BxDolAdminSettings(23);

    $sResults = false;
    if (isset($_POST['save']) && isset($_POST['cat']))
        $sResult = $oSettings->saveChanges($_POST);

    $s = $oSettings->getForm();
    if ($sResult)
        $s = $sResult . $s;

    return DesignBoxAdmin($GLOBALS['sPageTitle'], $s, $GLOBALS['aTopItems'], '', 11);
}
