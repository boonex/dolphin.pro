<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('../inc/header.inc.php');

$GLOBALS['iAdminPage'] = 1;

require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'admin_design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');

bx_import('BxDolAdminSettings');
bx_import('BxTemplSearchResult');

$logged['admin'] = member_auth(1, true, true);

$oSettings = new BxDolAdminSettings(4);

//--- Process submit ---//
$aResults = array();

if (isset($_POST['save']) && isset($_POST['cat'])) {
    $aResults['settings'] = $oSettings->saveChanges($_POST);
} elseif (isset($_POST['action']) && $_POST['action'] == 'get_translations') {
    $aTranslation = $GLOBALS['MySQL']->getRow("SELECT `Subject` AS `subject`, `Body` AS `body` FROM `sys_email_templates` WHERE `Name`= ? AND `LangID`= ? LIMIT ?",
        [$_POST['templ_name'], $_POST['lang_id'], 1]);
    if (empty($aTranslation)) {
        $aTranslation = $GLOBALS['MySQL']->getRow("SELECT `Subject` AS `subject`, `Body` AS `body` FROM `sys_email_templates` WHERE `Name`= ? AND `LangID`= ? LIMIT ?",
            [$_POST['templ_name'], 0, 1]);
    }

    echo json_encode(array('subject' => $aTranslation['subject'], 'body' => $aTranslation['body']));
    exit;
}

$iNameIndex              = 8;
$_page                   = array(
    'name_index' => $iNameIndex,
    'css_name'   => array('forms_adv.css', 'settings.css'),
    'js_name'    => array('email_templates.js'),
    'header'     => _t('_adm_page_cpt_email_templates'),
);
$_page_cont[$iNameIndex] = array(
    'page_main_code' => PageCodeMain($aResults)
);

PageCodeAdmin();

function PageCodeMain($aResults)
{
    $aTopItems = array(
        'adm-etempl-btn-list'     => array(
            'href'    => 'javascript:void(0)',
            'onclick' => 'javascript:onChangeType(this)',
            'title'   => _t('_adm_txt_email_list'),
            'active'  => empty($aResults) ? 1 : 0
        ),
        'adm-etempl-btn-settings' => array(
            'href'    => 'javascript:void(0)',
            'onclick' => 'javascript:onChangeType(this)',
            'title'   => _t('_adm_txt_email_settings'),
            'active'  => isset($aResults['settings']) ? 1 : 0
        )
    );

    $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('email_templates.html', array(
        'content_list'     => _getList(isset($aResults['list']) ? $aResults['list'] : true, empty($aResults)),
        'content_settings' => _getSettings(isset($aResults['settings']) ? $aResults['settings'] : true),
    ));

    return DesignBoxAdmin(_t('_adm_box_cpt_email_templates'), $sResult, $aTopItems);
}

function _getSettings($mixedResult, $bActive = false)
{
    $sResult = $GLOBALS['oSettings']->getForm();
    if ($mixedResult !== true && !empty($mixedResult)) {
        $bActive = true;
        $sResult = $mixedResult . $sResult;
    }

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('email_templates_settings.html', array(
        'display' => $bActive ? 'block' : 'none',
        'form'    => $sResult
    ));
}

function _getList($mixedResult, $bActive = false)
{
    $aForm = array(
        'form_attrs' => array(
            'id'      => 'adm-email-templates',
            'action'  => $GLOBALS['site']['url_admin'] . 'email_templates.php',
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ),
        'params'     => array(
            'db' => array(
                'table'       => 'sys_email_templates',
                'key'         => 'ID',
                'uri'         => '',
                'uri_title'   => '',
                'submit_name' => 'adm-emial-templates-save'
            ),
        ),
        'inputs'     => array()
    );

    $aLanguages = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Title` AS `title` FROM `sys_localization_languages`");

    $aLanguageChooser = array(array('key' => 0, 'value' => 'default'));
    foreach ($aLanguages as $aLanguage) {
        $aLanguageChooser[] = array('key' => $aLanguage['id'], 'value' => $aLanguage['title']);
    }

    $sLanguageCpt = _t('_adm_txt_email_language');
    $sSubjectCpt  = _t('_adm_txt_email_subject');
    $sBodyCpt     = _t('_adm_txt_email_body');

    $aEmails = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Name` AS `name`, `Subject` AS `subject`, `Body` AS `body`, `Desc` AS `description` FROM `sys_email_templates` WHERE `LangID`='0' ORDER BY `ID`");
    foreach ($aEmails as $aEmail) {
        $aForm['inputs'] = array_merge($aForm['inputs'], array(
            $aEmail['name'] . '_Beg'      => array(
                'type'        => 'block_header',
                'caption'     => $aEmail['description'],
                'collapsable' => true,
                'collapsed'   => true
            ),
            $aEmail['name'] . '_Language' => array(
                'type'    => 'select',
                'name'    => $aEmail['name'] . '_Language',
                'caption' => $sLanguageCpt,
                'value'   => 0,
                'values'  => $aLanguageChooser,
                'db'      => array(
                    'pass' => 'Int',
                ),
                'attrs'   => array(
                    'onchange' => "javascript:getTranslations(this)"
                )
            ),
            $aEmail['name'] . '_Subject'  => array(
                'type'    => 'text',
                'name'    => $aEmail['name'] . '_Subject',
                'caption' => $sSubjectCpt,
                'value'   => $aEmail['subject'],
                'db'      => array(
                    'pass' => 'Xss',
                ),
            ),
            $aEmail['name'] . '_Body'     => array(
                'type'    => 'textarea',
                'name'    => $aEmail['name'] . '_Body',
                'caption' => $sBodyCpt,
                'value'   => $aEmail['body'],
                'db'      => array(
                    'pass' => 'XssHtml',
                ),
            ),
            $aEmail['name'] . '_End'      => array(
                'type' => 'block_end'
            )
        ));
    }

    $aForm['inputs']['adm-emial-templates-save'] = array(
        'type'  => 'submit',
        'name'  => 'adm-emial-templates-save',
        'value' => _t('_adm_btn_email_save'),
    );

    $oForm = new BxTemplFormView($aForm);
    $oForm->initChecker();

    $sResult = "";
    if ($oForm->isSubmittedAndValid()) {
        $iResult = 0;
        foreach ($aEmails as $aEmail) {
            $iEmailId = (int)$GLOBALS['MySQL']->getOne("SELECT `ID` FROM `sys_email_templates` WHERE `Name`='" . process_db_input($aEmail['name']) . "' AND `LangID`='" . (int)$_POST[$aEmail['name'] . '_Language'] . "' LIMIT 1");
            if ($iEmailId != 0) {
                $iResult += (int)$GLOBALS['MySQL']->query("UPDATE `sys_email_templates` SET `Subject`='" . process_db_input($_POST[$aEmail['name'] . '_Subject']) . "', `Body`='" . process_db_input($_POST[$aEmail['name'] . '_Body']) . "' WHERE `ID`='" . $iEmailId . "'");
            } else {
                $iResult += (int)$GLOBALS['MySQL']->query("INSERT INTO `sys_email_templates` SET `Name`='" . process_db_input($aEmail['name']) . "', `Subject`='" . process_db_input($_POST[$aEmail['name'] . '_Subject']) . "', `Body`='" . process_db_input($_POST[$aEmail['name'] . '_Body']) . "', `LangID`='" . (int)$_POST[$aEmail['name'] . '_Language'] . "'");
            }
        }

        $bActive = true;
        $sResult .= MsgBox(_t($iResult > 0 ? "_adm_txt_email_success_save" : "_adm_txt_email_nothing_changed"), 3);
    }
    $sResult .= $oForm->getCode();

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('email_templates_list.html', array(
        'display' => $bActive ? 'block' : 'none',
        'content' => stripslashes($sResult),
        'loading' => LoadingBox('adm-email-loading')
    ));
}
