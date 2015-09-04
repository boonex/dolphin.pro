<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );

bx_import('BxDolInstallerUi');
bx_import('BxDolFtp');

$logged['admin'] = member_auth(1, true, true);

//--- Check actions ---//
$aEnabledTemplateAction = array(
	'upload' => 1,
	'delete' => 1, 
	'change_default' => 1,
);
$oZ = new BxDolAlerts('system', 'admin_templates_actions', 0, 0, array(
	'actions' => &$aEnabledTemplateAction
));
$oZ->alert();

$sResult = '';
if ($_POST['set_default'] && file_exists(BX_DIRECTORY_PATH_ROOT . 'templates/tmpl_' . $_POST['set_default']) && isset($aEnabledTemplateAction['change_default'])) {
    setParam('template', $_POST['set_default']);
} elseif ($_POST['del_template'] && $_POST['del_template'] != 'uni' && file_exists(BX_DIRECTORY_PATH_ROOT . 'templates/tmpl_' . $_POST['del_template']) && isset($aEnabledTemplateAction['delete'])) {
    $oInstallerUi = new BxDolInstallerUi();
    $sResult = $oInstallerUi->actionDelete(array('tmpl_' . $_POST['del_template']), 'template');
    $sResult = _t($sResult);
}

$aPages = array (
    'templates' => array (
        'title' => _t('_adm_txt_list'),
        'url' => BX_DOL_URL_ADMIN . 'templates.php?mode=templates',
        'func' => 'PageCodeTemplates',
        'func_params' => array($sResult),
    ),
    'add' => array (
        'title' => _t('_add'),
        'url' => BX_DOL_URL_ADMIN . 'templates.php?mode=add',
        'func' => 'PageCodeAdd',
        'func_params' => array(),
    ),
    'settings' => array (
        'title' => _t('_Settings'),
        'url' => BX_DOL_URL_ADMIN . 'templates.php?mode=settings',
        'func' => 'PageCodeSettings',
        'func_params' => array(),
    ),
);

if (!isset($_GET['mode']) || !isset($aPages[$_GET['mode']]))
    $sMode = 'templates';
else
    $sMode = $_GET['mode'];

$aTopItems = array();
foreach ($aPages as $k => $r)
    $aTopItems['dbmenu_' . $k] = array(
        'href' => $r['url'],
        'title' => $r['title'],
        'active' => $k == $sMode ? 1 : 0
    );

$oZ = new BxDolAlerts('system', 'admin_templates_tabs', 0, 0, array(
	'items' => &$aTopItems,
));
$oZ->alert();

$iNameIndex = 9;
$sPageTitle = _t('_adm_txt_templates');
$_page_cont[$iNameIndex]['page_main_code'] = call_user_func($aPages[$sMode]['func'], $aPages[$sMode]['func_params'][0], $aPages[$sMode]['func_params'][1]);

$_page = array(
    'name_index' => $iNameIndex,
    'header' => $sPageTitle,
    'header_text' => $sPageTitle,
    'css_name' => array('templates.css'),
);

PageCodeAdmin();

function PageCodeTemplates($sResult)
{
    $a = get_templates_array(true);

    $aTemplates = array ();
    foreach ($a as $k => $r) {
        $aTemplates[] = array(
            'key' => $k,
            'name' => htmlspecialchars_adv($r['name']),
            'ver' => htmlspecialchars_adv($r['ver']),
            'vendor' => htmlspecialchars_adv($r['vendor']),
            'desc' => $r['desc'],
            'bx_if:preview' => array (
                'condition' => (bool)$r['preview'],
                'content' => array ('img' => $r['preview']),
            ),
            'bx_if:no_preview' => array (
                'condition' => !$r['preview'],
                'content' => array (),
            ),
            'bx_if:default' => array (
                'condition' => $k == getParam('template'),
                'content' => array (),
            ),
            'bx_if:make_default' => array (
                'condition' => $k != getParam('template'),
                'content' => array ('key' => $k),
            ),
            'bx_if:delete' => array (
                'condition' => $k != getParam('template') && $k != 'uni' && $k != 'alt',
                'content' => array ('key' => $k),
            ),
        );
    }

    $s  = $sResult ? MsgBox($sResult, 10) : '';
    $s .= $GLOBALS['oAdmTemplate']->parseHtmlByName('templates.html', array(
        'bx_repeat:templates' => $aTemplates,
    ));

    $sCode =  DesignBoxAdmin ($GLOBALS['sPageTitle'], $s, $GLOBALS['aTopItems'], '', 11);

    if ('on' == getParam('feeds_enable'))
        $sCode = $sCode . DesignBoxAdmin (_t('_adm_box_cpt_design_templates'), '<div class="RSSAggrCont" rssid="boonex_unity_market_templates" rssnum="5" member="0">' . $GLOBALS['oFunctions']->loadingBoxInline() . '</div>');

    $GLOBALS['oAdmTemplate']->addJsTranslation(array('_Are_you_sure'));

    return $sCode;
}

function PageCodeAdd()
{
    $oInstallerUi = new BxDolInstallerUi();

    $sResult = '';
    if (isset($_POST['submit_upload']) && isset($_FILES['module']) && !empty($_FILES['module']['tmp_name']) && isset($GLOBALS['aEnabledTemplateAction']['upload']))
		$sResult = $oInstallerUi->actionUpload('template', $_FILES['module'], $_POST);

    $sContent = $oInstallerUi->getUploader($sResult, '_Template', true, $GLOBALS['aPages']['add']['url']);
    $sContent = DesignBoxAdmin($GLOBALS['sPageTitle'], $sContent, $GLOBALS['aTopItems'], '', 11);

	$oZ = new BxDolAlerts('system', 'admin_templates_blocks_add', 0, 0, array(
		'title' => &$GLOBALS['sPageTitle'],
		'code' => &$sContent,
	));
    $oZ->alert();

    return $sContent;
}

function PageCodeSettings()
{
    bx_import('BxDolAdminSettings');
    $oSettings = new BxDolAdminSettings(13);

    $sResults = false;
    if (isset($_POST['save']) && isset($_POST['cat']))
        $sResult = $oSettings->saveChanges($_POST);

    $s = $sResult . $oSettings->getForm();

    return DesignBoxAdmin($GLOBALS['sPageTitle'], $s, $GLOBALS['aTopItems'], '', 11);
}
