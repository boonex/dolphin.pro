<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define ('BX_SECURITY_EXCEPTIONS', true);

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
bx_import('BxDolDb');
bx_import('BxTemplSearchResult');
bx_import('BxDolCategories');
bx_import('BxDolAdminSettings');

$aBxSecurityExceptions = array ();
if (bx_get('pathes') !== false) {
    $aPathes = bx_get('pathes');

    if(is_array($aPathes))
        for ($i=0; $i<count($aPathes); ++$i) {
            $aBxSecurityExceptions[] = 'POST.pathes.'.$i;
            $aBxSecurityExceptions[] = 'REQUEST.pathes.'.$i;
        }
}

$logged['admin'] = member_auth( 1, true, true );

function actionAllCategories()
{
    $oDb = BxDolDb::getInstance();

    // check actions
    if(bx_get('pathes') !== false) {
        $aPathes = bx_get('pathes');

        if(is_array($aPathes) && !empty($aPathes))
            foreach($_POST['pathes'] as $sValue) {
                list($sCategory, $sId, $sType) = explode('%%', process_db_input($sValue, BX_TAGS_STRIP));
                if (bx_get('action_disable') !== false)
                    $oDb->query("UPDATE `sys_categories` SET `Status` = 'passive' WHERE
                        `Category` = '$sCategory' AND `ID` = " . (int)$sId . " AND `Type` = '$sType'");
                else if(bx_get('action_delete') !== false)
                    $oDb->query("DELETE FROM `sys_categories` WHERE
                        `Category` = '$sCategory' AND `ID` = " . (int)$sId . " AND `Type` = '$sType'");
            }
    }

    $aModules = array();
    $oCategories = new BxDolCategories();
    $oCategories->getTagObjectConfig();

    if(empty($oCategories->aTagObjects))
        return MsgBox(_t('_Empty'));

    $sModule = bx_get('module') !== false ? bx_get('module') : '';
    foreach($oCategories->aTagObjects as $sKey => $aValue) {
        if(!$sModule)
            $sModule = $sKey;

        $aModules[] = array(
            'value' => $sKey,
            'caption' => _t($aValue['LangKey']),
            'selected' => $sKey == $sModule ? 'selected="selected"' : ''
        );
    }

    $sTopControls = $GLOBALS['oAdmTemplate']->parseHtmlByName('categories_list_top_controls.html', array(
        'name' => _t('_categ_modules'),
        'bx_repeat:items' => $aModules,
        'location_href' => BX_DOL_URL_ADMIN . 'categories.php?action=all&module='
    ));

    $aCategories = $oDb->getAll("SELECT * FROM `sys_categories` WHERE `Status` = 'active' AND `Owner` = 0 AND `Type` = ?", [$sModule]);
    if(!empty($aCategories)) {
        $mixedTmplItems = array();
        foreach($aCategories as $aCategory)
            $mixedTmplItems[] = array(
                'name' => bx_html_attribute($aCategory['Category']),
                'value' => bx_html_attribute($aCategory['Category']) . '%%' . $aCategory['ID'] . '%%' . $aCategory['Type'],
                'title'=> $aCategory['Category'],
            );
    } else
        $mixedTmplItems = MsgBox(_t('_Empty'));

    $sFormName = 'categories_form';
    $sControls = $sControls = BxTemplSearchResult::showAdminActionsPanel($sFormName, array(
        'action_disable' => _t('_categ_btn_disable'),
        'action_delete' => _t('_categ_btn_delete')
    ), 'pathes');

    $sContent = $GLOBALS['oAdmTemplate']->parseHtmlByName('categories_list.html', array(
        'top_controls' => $sTopControls,
        'form_name' => $sFormName,
        'bx_repeat:items' => $mixedTmplItems,
        'controls' => $sControls
    ));

    return $sContent;
}

function actionPending()
{
    $oDb = BxDolDb::getInstance();
    $sFormName = 'categories_aprove_form';
    $aItems = array();

    if(is_array($_POST['pathes']) && !empty($_POST['pathes'])) {
        foreach($_POST['pathes'] as $sValue) {
            list($sCategory, $sId, $sType) = explode('%%', process_db_input($sValue, BX_TAGS_STRIP));
            $oDb->query("UPDATE `sys_categories` SET `Status` = 'active' WHERE
                `Category` = '$sCategory' AND `ID` = '$sId' AND `Type` = '$sType'");
        }
    }

    $aCategories = $oDb->getAll("SELECT * FROM `sys_categories` WHERE `Status` = 'passive'");

    if (!empty($aCategories)) {
        foreach($aCategories as $aCategory) {
            $aItems[] = array(
                'name' => bx_html_attribute($aCategory['Category']),
                'value' => bx_html_attribute($aCategory['Category']) . '%%' . $aCategory['ID'] . '%%' . $aCategory['Type'],
                'title'=> $aCategory['Category'] . '(' . $aCategory['Type'] . ')',
            );
        }

        $aButtons = array(
            'action_activate' => _t('_categ_btn_activate'),
        );
        $sControls = BxTemplSearchResult::showAdminActionsPanel($sFormName, $aButtons, 'pathes');

        return $GLOBALS['oAdmTemplate']->parseHtmlByName('categories_list.html', array(
            'form_name' => $sFormName,
            'bx_repeat:items' => $aItems,
            'controls' => $sControls
        ));
    } else
        return MsgBox(_t('_Empty'));
}

function actionSettings()
{
    $oSettings = new BxDolAdminSettings(27);

    $mixedResult = '';
    if(isset($_POST['save']) && isset($_POST['cat']))
        $mixedResult = $oSettings->saveChanges($_POST);

    $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oSettings->getForm()));

    if($mixedResult !== true && !empty($mixedResult))
        $sResult = $mixedResult . $sResult;

    return $sResult;
}

function getCategoryForm()
{
    $oCateg = new BxDolCategories();
    $aTypes = array();
    $oCateg->getTagObjectConfig();

    foreach ($oCateg->aTagObjects as $sKey => $aValue)
        $aTypes[$sKey] = _t($aValue[$oCateg->aObjFields['lang_key']]);

    $aForm = array(

        'form_attrs' => array(
            'name'     => 'category_form',
            'action'   => $_SERVER['REQUEST_URI'],
            'method'   => 'post',
            'enctype' => 'multipart/form-data',
        ),

        'params' => array (
            'db' => array(
                'table' => 'sys_categories',
                'submit_name' => 'submit_form'
            ),
        ),

        'inputs' => array(

            'name' => array(
                'type' => 'text',
                'name' => 'Category',
                'value' => isset($aUnit['name']) ? $aUnit['name'] : '',
                'caption' => _t('_categ_form_name'),
                'required' => true,
                'checker' => array (
                    'func' => 'length',
                    'params' => array(3, 100),
                    'error' => _t('_categ_form_field_name_err'),
                ),
                'db' => array(
                    'pass' => 'Xss'
                ),
                'display' => true,
            ),
            'type' => array(
                'type' => 'select',
                'name' => 'Type',
                'required' => true,
                'values' => $aTypes,
                'value' => bx_get('module') !== false ? bx_get('module') : '',
                'caption' => _t('_categ_form_type'),
                'attrs' => array(
                        'multiplyable' => false
                    ),
                'display' => true,
                'db' => array(
                    'pass' => 'Xss'
                ),
            ),
            'submit' => array (
                'type' => 'submit',
                'name' => 'submit_form',
                'value' => _t('_Submit'),
                'colspan' => false,
            ),
        )
    );

    return new BxTemplFormView($aForm);
}

function getAddCategoryForm()
{
    $oForm = getCategoryForm();
    $oForm->initChecker();
    $sResult = '';

    if ($oForm->isSubmittedAndValid()) {
        $oDb = BxDolDb::getInstance();
        if ($oDb->getOne("SELECT COUNT(*) FROM `sys_categories` WHERE `Category` = '" . $oForm->getCleanValue('Category') . "' AND `ID` = 0 AND `Type` = '" . $oForm->getCleanValue('Type') . "'") == 0) {
            $aValsAdd = array (
                'ID' => 0,
                'Owner' => 0,
                'Status' => 'active',
            );

            $oForm->insert($aValsAdd);
            header('Location:' . BX_DOL_URL_ADMIN . 'categories.php?action=all&module=' . $oForm->getCleanValue('Type'));
        } else
            $sResult = sprintf(_t('_categ_exist_err'), $oForm->getCleanValue('Category'));
    }

    return (strlen($sResult) > 0 ? MsgBox($sResult) : '') .
        $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode()));
}

$iNameIndex = 9;
$aMenu = array(
    'all' => array(
        'title' => _t('_categ_all'),
        'href' => $GLOBALS['site']['url_admin'] . 'categories.php?action=all',
        '_func' => array ('name' => 'actionAllCategories', 'params' => array()),
    ),
    'pending' => array(
        'title' => _t('_categ_admin_pending'),
        'href' => $GLOBALS['site']['url_admin'] . 'categories.php?action=pending',
        '_func' => array ('name' => 'actionPending', 'params' => array()),
    ),
    'settings' => array(
        'title' => _t('_categ_admin_settings'),
        'href' => $GLOBALS['site']['url_admin'] . 'categories.php?action=settings',
        '_func' => array ('name' => 'actionSettings', 'params' => array()),
    ),
);
$sAction = bx_get('action') !== false ? bx_get('action') : 'all';
$aMenu[$sAction]['active'] = 1;
$sContent = call_user_func_array($aMenu[$sAction]['_func']['name'], $aMenu[$sAction]['_func']['params']);

$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('forms_adv.css', 'settings.css', 'categories.css'),
    'header' => _t('_CategoriesSettings'),
);

$_page_cont[$iNameIndex]['page_main_code'] = DesignBoxAdmin(_t('_categ_form_add'), getAddCategoryForm()) .
    DesignBoxAdmin($aMenu[$sAction]['title'], $sContent, $aMenu);

PageCodeAdmin();
