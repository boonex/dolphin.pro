<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define ('BX_SECURITY_EXCEPTIONS', true);
$aBxSecurityExceptions = array ();
for ($i=1; $i<255 ; ++$i) {
    $aBxSecurityExceptions[] = 'POST.string_for_'.$i;
    $aBxSecurityExceptions[] = 'REQUEST.string_for_'.$i;
}

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
bx_import('BxTemplSearchResult');

$logged['admin'] = member_auth( 1, true, true );

bx_import('BxDolAdminSettings');
$oSettingsLanguage = new BxDolAdminSettings(21);

//--- Process submit ---//
$aResults = array();

//--- Change settings ---//
if (isset($_POST['save']) && isset($_POST['cat'])) {
    $aResults['settings'] = $oSettingsLanguage->saveChanges($_POST);
}

//--- Create/Edit/Delete/Recompile/Export/Import Languages ---//
if(isset($_POST['create_language'])) {
    $aResults[(isset($_POST['id']) && (int)$_POST['id'] != 0 ? 'langs' : 'langs-add')] = createLanguage($_POST);
} else if(isset($_POST['import_language'])) {
    $aResults['langs-import'] = importLanguage($_POST, $_FILES);
} else if(isset($_POST['adm-lang-compile']) && !empty($_POST['langs'])) {
    foreach($_POST['langs'] as $iLangId)
        if(!compileLanguage((int)$iLangId)) {
            $aResults['langs'] = '_adm_txt_langs_cannot_compile';
            break;
        }
    if(empty($aResults['langs']))
        $aResults['langs'] = '_adm_txt_langs_success_compile';
} else if(isset($_POST['adm-lang-delete']) && !empty($_POST['langs'])) {
    $sNameDefault = getParam('lang_default');
    foreach($_POST['langs'] as $iLangId) {
        $sName = getLanguageName($iLangId);
        if($sName == $sNameDefault) {
            $aResults['langs'] = '_adm_txt_langs_cannot_delete_default';
            break;
        }

        if(!deleteLanguage((int)$iLangId)){
            $aResults['langs'] = '_adm_txt_langs_cannot_delete';
            break;
        }
    }

    if(empty($aResults['langs']))
        $aResults['langs'] = '_adm_txt_langs_success_delete';
} else if(isset($_GET['action']) && $_GET['action'] == 'export' && isset($_GET['id'])) {
    $aLanguage = $GLOBALS['MySQL']->getRow("SELECT `Name`, `Flag`, `Title`, `Direction`, `LanguageCountry` FROM `sys_localization_languages` WHERE `ID`= ? LIMIT 1", [$_GET['id']]);

    $aContent = array();
    $aItems = $GLOBALS['MySQL']->getAll("SELECT `tlk`.`Key` AS `key`, `tls`.`String` AS `string` FROM `sys_localization_keys` AS `tlk` 
              LEFT JOIN `sys_localization_strings` AS `tls` ON `tlk`.`ID`=`tls`.`IDKey` WHERE `tls`.`IDLanguage`= ? ", [$_GET['id']]);
    foreach($aItems as $aItem)
        $aContent[$aItem['key']] = $aItem['string'];

    ksort($aContent);

    $sName = 'lang_' . $aLanguage['Name'] . '.php';
    $sContent = "<?php\n\$aLangInfo=" . var_export($aLanguage, true) . ";\n\$aLangContent=" . var_export($aContent, true) . ";\n?>";

    header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header ("Content-type: application/octet-stream");
    header ("Content-Length: " . strlen($sContent));
    header ("Content-Disposition: attachment; filename=" . $sName);
    echo $sContent;
    exit;
} else if(isset($_POST['action']) && $_POST['action'] == 'get_edit_form_language') {
    echo json_encode(array('code' => PopupBox('adm-langs-wnd-edit', _t('_adm_box_cpt_lang_edit_language'), _getLanguageCreateForm(true))));
    exit;
}

//--- Create/Delete/Edit Language Key ---//
if(isset($_POST['action']) && $_POST['action'] == 'get_edit_form_key') {
    echo json_encode(array('code' => PageCodeKeyEdit((int)$_POST['id'])));
    exit;
}
if(isset($_POST['create_key'])) {
    $sName = process_db_input($_POST['name']);
    $iCategoryId = (int)$_POST['category'];

    $mixedResult = $GLOBALS['MySQL']->query("INSERT INTO `sys_localization_keys`(`IDCategory`, `Key`) VALUES('" . $iCategoryId . "', '" . $sName . "')", false);
    if($mixedResult !== false) {
        $bCompiled = true;
        $iKeyId = (int)$GLOBALS['MySQL']->lastId();
        $aLanguages = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Title` AS `title` FROM `sys_localization_languages`");
        foreach($aLanguages as $aLanguage)
            if(isset($_POST['string_for_' . $aLanguage['id']])) {
                $GLOBALS['MySQL']->query("INSERT INTO `sys_localization_strings`(`IDKey`, `IDLanguage`, `String`) VALUES('" . $iKeyId . "', '" . $aLanguage['id'] . "', '" . process_db_input($_POST['string_for_' . $aLanguage['id']]) . "')");

                $bCompiled = $bCompiled && compileLanguage((int)$aLanguage['id']);
            }

        $aResult = $bCompiled ? array('code' => 0, 'message' => '_adm_txt_langs_success_key_save') : array('code' => 1, 'message' => '_adm_txt_langs_cannot_compile');
    } else
        $aResult = array('code' => 2, 'message' => '_adm_txt_langs_already_exists');

    $aResult['message'] = MsgBox(_t($aResult['message']));

    echo "<script>parent.onResult('add', " . json_encode($aResult) . ");</script>";
    exit;
} else if(isset($_POST['edit_key'])) {
    $iId = (int)$_POST['id'];

    $bCompiled = true;
    $aLanguages = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Title` AS `title` FROM `sys_localization_languages`");
    foreach($aLanguages as $aLanguage)
        if(isset($_POST['string_for_' . $aLanguage['id']])) {
            $GLOBALS['MySQL']->query("REPLACE INTO `sys_localization_strings`(`IDKey`, `IDLanguage`, `String`) VALUES('" . $iId . "', '" . $aLanguage['id'] . "', '" . process_db_input($_POST['string_for_' . $aLanguage['id']]) . "')");

            $bCompiled = $bCompiled && compileLanguage((int)$aLanguage['id']);
        }
    $aResult = $bCompiled ? array('code' => 0, 'message' => '_adm_txt_langs_success_key_save') : array('code' => 1, 'message' => '_adm_txt_langs_cannot_compile');
    $aResult['message'] = MsgBox(_t($aResult['message']));

    echo "<script>parent.onResult('edit', " . json_encode($aResult) . ");</script>";
    exit;
}

if(isset($_POST['adm-lang-key-delete']) && is_array($_POST['keys'])) {
    foreach($_POST['keys'] as $iKeyId)
        $GLOBALS['MySQL']->query("DELETE FROM `sys_localization_keys`, `sys_localization_strings` USING `sys_localization_keys`, `sys_localization_strings` WHERE `sys_localization_keys`.`ID`=`sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`ID`='" . $iKeyId . "'");
}
$iNameIndex = 5;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('forms_adv.css', 'lang_file.css'),
    'js_name' => array('lang_file.js'),
    'header' => _t('_adm_page_cpt_lang_file'),
);

$sLangRssFeed = 'on' == getParam('feeds_enable') ?  DesignBoxAdmin (_t('_adm_box_cpt_lang_files'), '<div class="RSSAggrCont" rssid="boonex_unity_lang_files" rssnum="5" member="0">' . $GLOBALS['oFunctions']->loadingBoxInline() . '</div>') : '';

$_page_cont[$iNameIndex] = array(
    'page_result_code' => '',
    'page_code_main' => PageCodeMain($aResults),
    'page_code_key' => PageCodeKeyCreate() . $sLangRssFeed,
);

PageCodeAdmin();

function PageCodeMain($aResults)
{
    $aTopItems = array(
        'adm-langs-btn-keys' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onChangeType(this)', 'title' => _t('_adm_txt_langs_keys'), 'active' => empty($aResults) ? 1 : 0),
        'adm-langs-btn-keys-add' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onCreate()', 'title' => _t('_adm_txt_langs_add_key'), 'active' => 0),
        'adm-langs-btn-langs' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onChangeType(this)', 'title' => _t('_adm_txt_langs_languages'), 'active' => isset($aResults['langs']) ? 1 : 0),
        'adm-langs-btn-langs-add' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onChangeType(this)', 'title' => _t('_adm_txt_langs_languages_add'), 'active' => isset($aResults['langs-add']) ? 1 : 0),
        'adm-langs-btn-langs-import' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onChangeType(this)', 'title' => _t('_adm_txt_langs_languages_import'), 'active' => isset($aResults['langs-import']) ? 1 : 0),
        'adm-langs-btn-settings' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onChangeType(this)', 'title' => _t('_adm_txt_langs_settings'), 'active' => isset($aResults['settings']) ? 1 : 0),
        'adm-langs-btn-help' => array('href' => 'https://github.com/boonex/dolphin.pro/wiki/Using-Language-Keys-to-Change-Site-Text', 'target' => '_blank', 'title' => _t('_help'))
    );

    $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('langs.html', array(
        'content_keys' => _getKeysList(isset($aResults['keys']) ? $aResults['keys'] : true, empty($aResults)),
        'content_files' => _getLanguagesList(isset($aResults['langs']) ? $aResults['langs'] : true),
        'content_create' => _getLanguageCreateForm(isset($aResults['langs-add']) ? $aResults['langs-add'] : true),
        'content_import' => _getLanguageImportForm(isset($aResults['langs-import']) ? $aResults['langs-import'] : true),
        'content_settings' => _getLanguageSettingsForm(isset($aResults['settings']) ? $aResults['settings'] : true),
    ));

    return DesignBoxAdmin(_t('_adm_box_cpt_lang_available'), $sResult, $aTopItems);
}

function _getLanguagesArray()
{
    return $GLOBALS['MySQL']->fromCache('sys_localization_languages', 'getAllWithKey',
            "SELECT `ID` AS `id`, `Name` AS `name`, `Title` AS `title`, `Flag` AS `flag` FROM `sys_localization_languages` ORDER BY `Name`", 'name');
}

function _checkLangUnique($sLangName)
{
    $aLangs = _getLanguagesArray();
    return array_key_exists($sLangName, $aLangs);
}

function _getKeysList($mixedResult, $bActive = false)
{
    $sFilterName = 'filter';
    $sFilter = '';
    $aItems = array();
    if(isset($_GET[$sFilterName])) {
        $sFilter = process_db_input($_GET[$sFilterName], BX_TAGS_STRIP);

        $aKeys = $GLOBALS['MySQL']->getAll("SELECT `tk`.`ID` AS `id`, `tk`.`Key` AS `key`, `tc`.`Name` AS `category` FROM `sys_localization_keys` AS `tk` 
                 LEFT JOIN `sys_localization_strings` AS `ts` ON `tk`.`ID`=`ts`.`IDKey` LEFT JOIN `sys_localization_categories` AS `tc` ON `tk`.`IDCategory`=`tc`.`ID` 
                 WHERE `tk`.`Key` LIKE ? OR `ts`.`String` LIKE ? GROUP BY `tk`.`ID`", ["%{$sFilter}%", "%{$sFilter}%"]);
        foreach($aKeys as $aKey)
            $aItems[] = array(
                'id' => $aKey['id'],
                'key' => $aKey['key'],
                'category' => $aKey['category'],
                'admin_url' => $GLOBALS['site']['url_admin']
            );
    }

    //--- Get Controls ---//
    $aButtons = array(
        'adm-lang-key-delete' => _t('_adm_txt_langs_delete')
    );
    $sControls = BxTemplSearchResult::showAdminActionsPanel('adm-keys-form', $aButtons, 'keys');

    $sFilter = BxTemplSearchResult::showAdminFilterPanel(false !== bx_get($sFilterName) ? bx_get($sFilterName) : '', 'adm-langs-look-for', 'adm-langs-apply', $sFilterName);

    if($mixedResult !== true && !empty($mixedResult))
        $sFilter .= MsgBox(_t($mixedResult), 3);

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('langs_keys.html', array(
        'display' => $bActive ? 'block' : 'none',
        'filter_panel' => $sFilter,
        'bx_repeat:items' => !empty($aItems) ? $aItems : MsgBox(_t('_Empty')),
        'control' => $sControls,
        'url_admin' => $GLOBALS['site']['url_admin']
    ));
}
function _getLanguagesList($mixedResult, $bActive = false)
{
    $sResult = '';
    if($mixedResult !== true && !empty($mixedResult)) {
        $bActive = true;
        $sResult = MsgBox(_t($mixedResult), 3);
    }

    //--- Get Items ---//
    $aItems = array();
    $sNameDefault = getParam('lang_default');

    $aLangs = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Name` AS `name`, `Title` AS `title`, `Flag` AS `flag` FROM `sys_localization_languages` ORDER BY `Name`");
    foreach($aLangs as $aLang)
        $aItems[] = array(
            'name' => $aLang['name'],
            'value' => $aLang['id'],
            'title' => $aLang['title'],
            'icon' => $GLOBALS['site']['flags'] . $aLang['flag'] . '.gif',
            'default' => $aLang['name'] == $sNameDefault ? '(' . _t('_adm_txt_langs_default') . ')' : '',
            'edit_link' => $GLOBALS['site']['url_admin'] . 'lang_file.php?action=edit&id=' . $aLang['id'],
            'export_link' => $GLOBALS['site']['url_admin'] . 'lang_file.php?action=export&id=' . $aLang['id']
        );

    //--- Get Controls ---//
    $aButtons = array(
        'adm-lang-compile' => _t('_adm_txt_langs_compile'),
        'adm-lang-delete' => _t('_adm_txt_langs_delete')
    );
    $sControls = BxTemplSearchResult::showAdminActionsPanel('adm-langs-form', $aButtons, 'langs');

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('langs_files.html', array(
        'display' => $bActive ? 'block' : 'none',
        'results' => $sResult,
        'bx_repeat:items' => $aItems,
        'controls' => $sControls
    ));
}
function _getLanguageCreateForm($mixedResult, $bActive = false)
{
    if (isset($_POST['action']) && $_POST['action'] == 'get_edit_form_language' && isset($_POST['id'])) {
        $aLanguage = $GLOBALS['MySQL']->getRow("SELECT `ID` AS `id`, `Name` AS `name`, `Flag` AS `flag`, `Title` AS `title`, `Direction` AS `direction`, `LanguageCountry` AS `lang_country` 
        FROM `sys_localization_languages` WHERE `ID`= ? LIMIT 1", [$_POST['id']]);
    }

    //--- Create language form ---//
    $aFormCreate = array(
        'form_attrs' => array(
            'id' => 'adm-settings-form-files',
            'name' => 'adm-settings-form-files',
            'action' => $GLOBALS['site']['url_admin'] . 'lang_file.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ),
        'inputs' => array(
            'CopyLanguage_Title' => array(
                'type' => 'text',
                'name' => 'CopyLanguage_Title',
                'caption' => _t('_adm_txt_langs_title'),
                'value' => isset($aLanguage['title']) ? $aLanguage['title'] : '',
            ),
            'CopyLanguage_Name' => array(
                'type' => 'text',
                'name' => 'CopyLanguage_Name',
                'caption' => _t('_adm_txt_langs_code'),
                'value' => isset($aLanguage['name']) ? $aLanguage['name'] : '',
            ),
            'LanguageCountry' => array(
                'type' => 'text',
                'name' => 'LanguageCountry',
                'caption' => _t('_adm_txt_langs_country_code'),
                'value' => isset($aLanguage['lang_country']) ? $aLanguage['lang_country'] : '',
            ),
            'Direction' => array(
                'type' => 'select',
                'name' => 'Direction',
                'caption' => _t('_adm_txt_langs_direction'),
                'values' => array('LTR' => 'LTR', 'RTL' => 'RTL'),
                'value' => isset($aLanguage['direction']) ? $aLanguage['direction'] : 'LTR',
            ),
            'Flag' => array(
                'type' => 'select',
                'name' => 'Flag',
                'caption' => _t('_adm_txt_langs_flag'),
                'values' => array(),
                'value' => isset($aLanguage['flag']) ? $aLanguage['flag'] : strtolower(getParam('default_country')),
            ),
            'CopyLanguage_SourceLangID' => array(
                'type' => 'select',
                'name' => 'CopyLanguage_SourceLangID',
                'caption' => _t('_adm_txt_langs_copy_from'),
                'values' => array()
            ),
            'create_language' => array(
                'type' => 'submit',
                'name' => 'create_language',
                'value' => _t("_adm_btn_lang_save"),
            )
        )
    );
    //--- Copy from ---//
    $aLangs = getLangsArr(false, true);
    foreach($aLangs as $iId => $sName)
        $aFormCreate['inputs']['CopyLanguage_SourceLangID']['values'][] = array('key' => $iId, 'value' => htmlspecialchars_adv( $sName ));

    //--- Flags ---//
    $aCountries = $GLOBALS['MySQL']->getAll("SELECT `ISO2` AS `code`, `Country` AS `title` FROM `sys_countries` ORDER BY `Country`");
    foreach($aCountries AS $aCountry) {
        $sCode = strtolower($aCountry['code']);
        $aFormCreate['inputs']['Flag']['values'][] = array('key' => $sCode, 'value' => $aCountry['title']);
    }

    $bLanguage = !empty($aLanguage);
    if($bLanguage) {
        unset($aFormCreate['inputs']['CopyLanguage_SourceLangID']);
        $aFormCreate['inputs']['id'] = array(
            'type' => 'hidden',
            'name' => 'id',
            'value' => $aLanguage['id']
        );
    }
    $oForm = new BxTemplFormView($aFormCreate);

    $sResult = $oForm->getCode();
    if($mixedResult !== true && !empty($mixedResult)) {
        $bActive = true;
        $sResult = MsgBox(_t($mixedResult), 3) . $sResult;
    }

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('langs_form_create.html', array(
        'display' => $bActive || $bLanguage ? 'block' : 'none',
        'form' => $sResult
    ));
}
function _getLanguageImportForm($mixedResult, $bActive = false)
{
    $aFormImport = array(
        'form_attrs' => array(
            'id' => 'adm-settings-form-import',
            'name' => 'adm-settings-form-import',
            'action' => $GLOBALS['site']['url_admin'] . 'lang_file.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ),
        'inputs' => array(
            'ImportLanguage_File' => array(
                'type' => 'file',
                'name' => 'ImportLanguage_File',
                'caption' => _t('_adm_txt_langs_file'),
            ),
            'import_language' => array(
                'type' => 'submit',
                'name' => 'import_language',
                'value' => _t('_adm_btn_lang_import'),
            )
        )
    );
    $oForm = new BxTemplFormView($aFormImport);

    $sResult = $oForm->getCode();
    if($mixedResult !== true && !empty($mixedResult)) {
        $bActive = true;
        $sResult = MsgBox(_t($mixedResult), 3) . $sResult;
    }

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('langs_form_import.html', array(
        'display' => $bActive ? 'block' : 'none',
        'form' => $sResult
    ));
}
function _getLanguageSettingsForm($mixedResult, $bActive = false)
{
    global $oSettingsLanguage;

    $oForm = $oSettingsLanguage->getFormObject();

    // re-format 'default language' form field
    foreach ($oForm->aInputs as $k => $r) {
        if ('lang_default' != $r['name'])
            continue;
        $oForm->aInputs[$k] = array(
            'type' => 'select',
            'name' => 'lang_default',
            'caption' => _t('_adm_txt_langs_def_lang'),
            'values' => array(),
            'value' => getParam('lang_default'),
        );
        $aLangs = getLangsArr();
        foreach ($aLangs as $sName => $sTitle)
            $oForm->aInputs[$k]['values'][] = array('key' => $sName, 'value' => htmlspecialchars_adv($sTitle));
    }

    // get form code
    $oForm->initChecker();
    $sResult = $oForm->getCode();

    // add operation result
    if ($mixedResult !== true && !empty($mixedResult)) {
        $bActive = true;
        $sResult = $mixedResult . $sResult;
    }

    // display
    return $GLOBALS['oAdmTemplate']->parseHtmlByName('langs_form_settings.html', array(
        'display' => $bActive ? 'block' : 'none',
        'form' => $sResult
    ));
}

function PageCodeKeyCreate()
{
    $aForm = array(
        'form_attrs' => array(
            'id' => 'adm-langs-add-key-form',
            'name' => 'adm-langs-add-key-form',
            'action' => $GLOBALS['site']['url_admin'] . 'lang_file.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data',
            'target' => 'adm-langs-add-key-iframe'
        ),
        'params' => array(),
        'inputs' => array(
            'name' => array(
                'type' => 'text',
                'name' => 'name',
                'caption' => _t('_adm_txt_keys_name'),
                'value' => '',
            ),
            'category' => array(
                'type' => 'select',
                'name' => 'category',
                'caption' => _t('_adm_txt_keys_category'),
                'value' => '',
                'values' => array()
            ),
        )
    );

    $aCategories = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Name` AS `title` FROM `sys_localization_categories`");
    foreach($aCategories as $aCategory)
        $aForm['inputs']['category']['values'][] = array('key' => $aCategory['id'], 'value' => $aCategory['title']);

    $aLanguages = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Title` AS `title` FROM `sys_localization_languages`");
    foreach($aLanguages as $aLanguage)
        $aForm['inputs']['string_for_' . $aLanguage['id']] = array(
            'type' => 'textarea',
            'name' => 'string_for_' . $aLanguage['id'],
            'caption' => _t('_adm_txt_keys_string_for', $aLanguage['title']),
            'value' => '',
        );

    $aForm['inputs']['create_key'] = array(
        'type' => 'submit',
        'name' => 'create_key',
        'value' => _t("_adm_btn_lang_save"),
    );

    $oForm = new BxTemplFormView($aForm);
    $sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('langs_key.html', array('type' => 'add', 'content' => $oForm->getCode()));
    return $GLOBALS['oFunctions']->popupBox('adm-langs-add-key', _t('_adm_box_cpt_lang_key'), $sContent);
}
function PageCodeKeyEdit($iId)
{
    $aForm = array(
        'form_attrs' => array(
            'id' => 'adm-langs-edit-key-form',
            'name' => 'adm-langs-edit-key-form',
            'action' => $GLOBALS['site']['url_admin'] . 'lang_file.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data',
            'target' => 'adm-langs-edit-key-iframe'
        ),
        'params' => array(),
        'inputs' => array(
            'id' => array(
                'type' => 'hidden',
                'name' => 'id',
                'value' => $iId
            ),
            'name' => array(
                'type' => 'text',
                'name' => 'name',
                'caption' => _t('_adm_txt_keys_name'),
                'value' => $GLOBALS['MySQL']->getOne("SELECT `Key` FROM `sys_localization_keys` WHERE `ID`='" . $iId . "' LIMIT 1"),
                'attrs' => array(
                    'disabled' => 'disabled'
                )
            ),
        )
    );

    $aStrings = $GLOBALS['MySQL']->getAllWithKey("SELECT CONCAT('string_for_', `IDLanguage`) AS `key`, `String` AS `value` FROM `sys_localization_strings` WHERE `IDKey`= ?", "key", [$iId]);
    $aLanguages = $GLOBALS['MySQL']->getAll("SELECT `ID` AS `id`, `Title` AS `title` FROM `sys_localization_languages`");
    foreach($aLanguages as $aLanguage) {
        $sKey = 'string_for_' . $aLanguage['id'];

        $aForm['inputs'][$sKey] = array(
            'type' => 'textarea',
            'name' => 'string_for_' . $aLanguage['id'],
            'caption' => _t('_adm_txt_keys_string_for', $aLanguage['title']),
            'value' => $aStrings[$sKey]['value'],
        );
    }
    $aForm['inputs']['edit_key'] = array(
        'type' => 'submit',
        'name' => 'edit_key',
        'value' => _t("_adm_btn_lang_save"),
    );

    $oForm = new BxTemplFormView($aForm);
    $sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('langs_key.html', array('type' => 'edit', 'content' => $oForm->getCode()));
    return $GLOBALS['oFunctions']->popupBox('adm-langs-edit-key', _t('_adm_box_cpt_lang_key'), $sContent);
}
function createLanguage(&$aData)
{
    global $MySQL;

    $sTitle = process_db_input($aData['CopyLanguage_Title']);
    $sName  = mb_strtolower( process_db_input($aData['CopyLanguage_Name']) );
    $sFlag = process_db_input($aData['Flag']);
    $sDir = process_db_input($aData['Direction']);
    $sLangCountry = process_db_input($aData['LanguageCountry']);
    $iSourceId = isset($aData['CopyLanguage_SourceLangID']) ? (int)$aData['CopyLanguage_SourceLangID'] : 0;

    if(strlen($sTitle) <= 0)
        return '_adm_txt_langs_empty_title';
    if(strlen($sName) <= 0)
        return '_adm_txt_langs_empty_name';

    if(isset($aData['id']) && (int)$aData['id'] != 0) {
        $MySQL->query("UPDATE `sys_localization_languages` SET `Name`='" . $sName . "', `Flag`='" . $sFlag . "', `Title`='" . $sTitle . "', `Direction`='" . $sDir . "', `LanguageCountry`='" . $sLangCountry . "' WHERE `ID`='" . (int)$aData['id'] . "'");

        return '_adm_txt_langs_success_updated';
    }

    if (_checkLangUnique($sName) === true)
        return '_adm_txt_langs_cannot_create';

    $mixedResult = $MySQL->query("INSERT INTO `sys_localization_languages` (`Name`, `Flag`, `Title`, `Direction`, `LanguageCountry`) VALUES ('{$sName}', '{$sFlag}', '{$sTitle}', '{$sDir}', '{$sLangCountry}')");
    if($mixedResult === false)
        return '_adm_txt_langs_cannot_create';
    $iId = (int)$MySQL->lastId();

    $MySQL->cleanCache('sys_localization_languages');

    $aStrings = $MySQL->getAll("SELECT `IDKey`, `String` FROM `sys_localization_strings` WHERE `IDLanguage` = ?", [$iSourceId]);

    foreach($aStrings as $aString){
        $aString['String'] = addslashes($aString['String']);
        $count = $MySQL->query("INSERT INTO `sys_localization_strings`(`IDKey`, `IDLanguage`, `String`) VALUES ('{$aString['IDKey']}', $iId, '{$aString['String']}')");

        if( !$count )
            return '_adm_txt_langs_cannot_add_string';
    }

    return '_adm_txt_langs_success_create';
}
function importLanguage(&$aData, &$aFiles)
{
    global $MySQL;

    $sTmpPath = $GLOBALS['dir']['tmp'] . time() . ".php";
    if(!file_exists($aFiles['ImportLanguage_File']['tmp_name']) || !move_uploaded_file($aFiles['ImportLanguage_File']['tmp_name'], $sTmpPath))
        return '_adm_txt_langs_cannot_upload_file';

    require_once($sTmpPath);

    $aLangInfo = isset($aLangInfo) ? $aLangInfo : $LANG_INFO;
    $aLangContent = isset($aLangContent) ? $aLangContent : $LANG;
    if (empty($aLangInfo) || empty($aLangContent)) {
        return '_adm_txt_langs_cannot_create';
    }
    if (_checkLangUnique($aLangInfo['Name']) === true)
        return '_adm_txt_langs_cannot_create';

    $mixedResult = $MySQL->query("INSERT INTO `sys_localization_languages` (`Name`, `Flag`, `Title`, `Direction`, `LanguageCountry`) 
                                  VALUES (?, ?, ?, ?, ?)", [
            $aLangInfo['Name'],
            $aLangInfo['Flag'],
            $aLangInfo['Title'],
            $aLangInfo['Direction'],
            $aLangInfo['LanguageCountry']
        ]
    );
    if($mixedResult === false) {
        @unlink($sTmpPath);
        return '_adm_txt_langs_cannot_create';
    }
    $iId = (int)$MySQL->lastId();

    $MySQL->cleanCache('sys_localization_languages');

    $aKeys = $MySQL->getAllWithKey("SELECT `ID` AS `id`, `Key` AS `key` FROM `sys_localization_keys`", "key");
    foreach($aLangContent as $sKey => $sString) {
        if(!isset($aKeys[$sKey]))
            continue;

        $MySQL->query("INSERT INTO `sys_localization_strings`(`IDKey`, `IDLanguage`, `String`) VALUES ('" . $aKeys[$sKey]['id'] . "', " . $iId . ", '" . addslashes($sString) . "')");
    }

    compileLanguage($iId);

    @unlink($sTmpPath);
    return '_adm_txt_langs_success_import';
}
function getLanguageName($iId)
{
    return $GLOBALS['MySQL']->getOne("SELECT `Name` FROM `sys_localization_languages` WHERE `ID`='" . (int)$iId . "' LIMIT 1");
}
