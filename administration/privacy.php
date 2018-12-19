<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );

$GLOBALS['iAdminPage'] = 1;

require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

bx_import('BxDolPrivacy');
bx_import('BxDolAdminSettings');
bx_import('BxTemplSearchResult');

$logged['admin'] = member_auth( 1, true, true );

$oSettings = new BxDolAdminSettings(9);

//--- Process submit ---//
$aResults = array();

if(isset($_POST['save']) && isset($_POST['cat'])) {
    $aResults['settings'] = $oSettings->saveChanges($_POST);
}

$iNameIndex = 18;

$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('forms_adv.css', 'settings.css'),
	'js_name' => array('privacy.js'),
    'header' => _t('_adm_page_cpt_privacy'),
);

$_page_cont[$iNameIndex] = array(
    'page_main_code' => PageCodeMain($aResults)
);

PageCodeAdmin();

function PageCodeMain($aResults)
{
    $aTopItems = array(
        'adm-pvc-btn-defaults' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onChangeType(this)', 'title' => _t('_adm_txt_pvc_defaults'), 'active' => empty($aResults) ? 1 : 0),
        'adm-pvc-btn-settings' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:onChangeType(this)', 'title' => _t('_adm_txt_pvc_settings'), 'active' => isset($aResults['settings']) ? 1 : 0)
    );

    $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('privacy.html', array(
        'content_defaults' => _getDefaults(isset($aResults['defaults']) ? $aResults['defaults'] : true, empty($aResults)),
        'content_settings' => _getSettings(isset($aResults['settings']) ? $aResults['settings'] : true),
    ));

    return DesignBoxAdmin(_t('_adm_box_cpt_privacy'), $sResult, $aTopItems);
}

function _getSettings($mixedResult, $bActive = false)
{
    $sResult = $GLOBALS['oSettings']->getForm();
    if($mixedResult !== true && !empty($mixedResult)) {
        $bActive = true;
        $sResult = $mixedResult . $sResult;
    }

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('privacy_settings.html', array(
        'display' => $bActive ? 'block' : 'none',
        'form' => $sResult
    ));
}

function _getDefaults($mixedResult, $bActive = false)
{
	$sNamePrefix = 'adm-pvc-action-';

    $aForm = array(
        'form_attrs' => array(
            'id' => 'adm-pvc-defaults',
            'action' => $GLOBALS['site']['url_admin'] . 'privacy.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ),
        'params' => array (
			'db' => array(
				'table' => 'sys_privacy_actions',
				'key' => 'ID',
				'uri' => '',
				'uri_title' => '',
				'submit_name' => 'adm-pvc-defaults-save'
			),
		),
        'inputs' => array ()
    );

    $aValues = array();

    $aGroupIds = $GLOBALS['MySQL']->getColumn("SELECT `id` FROM `sys_privacy_groups` WHERE `owner_id`='0' AND `id` NOT IN('" . implode("','", array(BX_DOL_PG_DEFAULT, BX_DOL_PG_HIDDEN)) . "')");
    foreach($aGroupIds as $iGroupId)
    	if(getParam('sys_ps_enabled_group_' . $iGroupId) == 'on')
    		$aValues[$iGroupId] = _t('_ps_group_' . $iGroupId . '_title');

	$sModule = '';
    $aActions = $GLOBALS['MySQL']->getAll("SELECT `id` AS `id`, `module_uri` AS `module`, `title` AS `title`, `default_group` AS `default_group` FROM `sys_privacy_actions` WHERE 1 ORDER BY `module_uri` ASC");
    foreach($aActions as $aAction) {
    	$sName = $sNamePrefix . $aAction['id'];
    	$sValue = $aAction['default_group'];

    	if($aAction['module'] != $sModule) {
    		if(!empty($sModule))
    			$aForm['inputs'][$sModule . '_end'] = array(
	                'type' => 'block_end'
	            );

			$sModule = $aAction['module'];

			$aForm['inputs'][$sModule . '_beg'] = array(
                'type' => 'block_header',
                'caption' => _t('_sys_module_' . $sModule),
                'collapsable' => true,
                'collapsed' => true
            );
    	}

    	if(!in_array($sValue, $aGroupIds))
    		continue;

        $aForm['inputs'][$sName] = array(
			'type' => 'select',
			'name' => $sName,
			'caption' => _t($aAction['title']),
			'value' =>  (int)$sValue,
			'values' => $aValues,
			'db' => array (
				'pass' => 'Int',
			)
		);
    }

    $aForm['inputs'][$sModule . '_end'] = array(
		'type' => 'block_end'
	);
    $aForm['inputs']['adm-pvc-defaults-save'] = array(
        'type' => 'submit',
        'name' => 'adm-pvc-defaults-save',
        'value' => _t('_adm_btn_pvc_save'),
    );

    $oForm = new BxTemplFormView($aForm);
    $oForm->initChecker();

    $sResult = "";
    if($oForm->isSubmittedAndValid()) {
    	$iResult = 0;
    	foreach($aActions as $aAction) {
    		$sName = $sNamePrefix . $aAction['id'];
    		$sValueOld = $aAction['default_group'];

    		if(!in_array($sValueOld, $aGroupIds))
    			continue;

			$sValueNew = bx_get($sName);
			if($sValueNew === false || (int)$sValueNew == (int)$sValueOld)
				continue;

    		$iResult += (int)$GLOBALS['MySQL']->query("UPDATE `sys_privacy_actions` SET `default_group`='" . (int)$sValueNew . "' WHERE `id`='" . $aAction['id'] . "'");
    	}

    	$bActive = true;
        $sResult .= MsgBox(_t($iResult > 0 ? "_adm_txt_pvc_success_save" : "_adm_txt_pvc_nothing_changed"), 3);
    }
    $sResult .= $oForm->getCode();

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('privacy_defaults.html', array(
        'display' => $bActive ? 'block' : 'none',
        'form' => stripslashes($sResult)
    ));
}
