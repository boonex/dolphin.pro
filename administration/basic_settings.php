<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_SECURITY_EXCEPTIONS', true);
$aBxSecurityExceptions = array(
    'POST.content_text',
    'REQUEST.content_text',
);

require_once( '../inc/header.inc.php' );

$GLOBALS['iAdminPage'] = 1;

require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
bx_import('BxDolAdminSettings');

$logged['admin'] = member_auth( 1, true, true );

//--- Process submit ---//
$mixedResultLogo = '';
$mixedResultPromo = '';

$oSettings = new BxDolAdminSettings(7);

//--- Logo uploading ---//
if(isset($_POST['upload']) && isset($_FILES['new_file']))
    $mixedResultLogo = setLogo($_POST, $_FILES);
else if(isset($_POST['delete']))
    deleteLogo();

//--- Site's settings saving ---//
if(isset($_POST['save']) && isset($_POST['cat'])) {
    $sResult = $oSettings->saveChanges($_POST);
}
//--- Promo text saving ---//
if(isset($_POST['save_splash'])) {
    setParam('splash_editor', (process_db_input($_POST['editor']) == 'on' ? 'on' : ''));
    setParam('splash_code',  process_db_input($_POST['code'], BX_TAGS_VALIDATE));
    setParam('splash_visibility', process_db_input($_POST['visibility']));
    setParam('splash_logged', (process_db_input($_POST['logged']) == 'on' ? 'on' : ''));
}

$iNameIndex = 4;
$_page = array(
    'name_index' => $iNameIndex,
    'css_name' => array('forms_adv.css', 'settings.css'),
    'header' => _t('_adm_page_cpt_settings_basic')
);
$_page_cont[$iNameIndex] = array(
    'page_code_settings' => DesignBoxAdmin(_t('_adm_box_cpt_settings_main'), $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oSettings->getForm()))),
    'page_code_logo' => PageCodeLogo($mixedResultLogo),
    'page_code_promo' => PageCodePromo($mixedResultPromo)
);

PageCodeAdmin();

function PageCodePromo($mixedResultPromo)
{
    $bEditor = getParam('splash_editor') == 'on';

    $aForm = array(
        'form_attrs' => array(
            'id' => 'adm-settings-form-splash',
            'name' => 'adm-settings-form-splash',
            'action' => $GLOBALS['site']['url_admin'] . 'basic_settings.php',
            'method' => 'post',
        ),
        'params' => array(),
        'inputs' => array(
            'editor' => array(
                'type' => 'checkbox',
                'name' => 'editor',
                'caption' => _t('_adm_txt_settings_splash_editor'),
                'info' => _t('_adm_dsc_settings_splash_editor'),
                'value' =>  'on',
                'checked' => $bEditor,
                'attrs' => array(
                    'onchange' => 'javascript:splashEnableEditor(this)'
                )
            ),
            'code' => array(
                'type' => 'textarea',
                'name' => 'code',
                'caption' => '',
                'value' => getParam('splash_code'),
                'html' => $bEditor ? 2 : 0,
                'colspan' => 2,
                'tr_attrs' => array(
                    'id' => 'adm-bs-splash-editor-wrp'
                ),
                'attrs_wrapper' => array(
                    'style' => 'height:300px; width:100%;',
                ),
                'attrs' => array(
                    'id' => 'adm-bs-splash-editor',
                    'style' => 'height:300px; width:100%;',
                )
            ),
            'visibility' => array(
                'type' => 'radio_set',
                'name' => 'visibility',
                'caption' => _t('_adm_txt_settings_splash_visibility'),
                'value' =>  getParam('splash_visibility'),
                'values' => array(
                    BX_DOL_SPLASH_VIS_DISABLE => _t('_adm_txt_settings_splash_visibility_disable'),
                    BX_DOL_SPLASH_VIS_INDEX => _t('_adm_txt_settings_splash_visibility_index'),
                    BX_DOL_SPLASH_VIS_ALL => _t('_adm_txt_settings_splash_visibility_all')
                ),
                'dv' => '<br />'
            ),
            'logged' => array(
                'type' => 'checkbox',
                'name' => 'logged',
                'caption' => _t('_adm_txt_settings_splash_logged'),
                'value' => 'on',
                'checked' => getParam('splash_logged') == 'on',
            ),
            'save_splash' => array(
                'type' => 'submit',
                'name' => 'save_splash',
                'value' => _t("_adm_btn_settings_save"),
            )
        )
    );
    $oForm = new BxTemplFormView($aForm);

    $sContent = '';
    $sContent .= MsgBox(_t('_adm_txt_settings_splash_warning'));
    $sContent .= $oForm->getCode();

    return DesignBoxAdmin(_t('_adm_box_cpt_splash'), $GLOBALS['oAdmTemplate']->parseHtmlByName('splash.html', array(
        'content' => $sContent
    )));
}

function PageCodeLogo($mixedResultLogo)
{
    $aForm = array(
        'form_attrs' => array(
            'id' => 'adm-settings-form-logo',
            'name' => 'adm-settings-form-logo',
            'action' => $GLOBALS['site']['url_admin'] . 'basic_settings.php',
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ),
        'params' => array(),
        'inputs' => array(
            'upload_header_beg' => array(
                'type' => 'block_header',
                'caption' => _t('_adm_txt_settings_logo_header'),
                'collapsable' => false,
                'collapsed' => false
            ),
            'old_file' => array(
                'type' => 'custom',
                'content' => $GLOBALS['oFunctions']->genSiteLogo(),
                'colspan' => true
            ),
            'new_file' => array(
                'type' => 'file',
                'name' => 'new_file',
                'caption' => _t('_adm_txt_settings_logo_upload'),
                'value' => '',
            ),
            'resize_header_beg' => array(
                'type' => 'block_header',
                'caption' => _t('_adm_txt_settings_resize_header'),
                'collapsable' => false,
                'collapsed' => false
            ),
            'resize' => array(
                'type' => 'checkbox',
                'name' => 'resize',
                'caption' => _t('_adm_txt_settings_resize_enable'),
                'value' => 'yes',
                'checked' => true
            ),
            'new_width' => array(
                'type' => 'text',
                'name' => 'new_width',
                'caption' => _t('_adm_txt_settings_resize_width'),
                'value' => '64'
            ),
            'new_height' => array(
                'type' => 'text',
                'name' => 'new_height',
                'caption' => _t('_adm_txt_settings_resize_height'),
                'value' => '64'
            ),
            'resize_header_end' => array(
                'type' => 'block_end'
            ),
            'upload' => array(
                'type' => 'submit',
                'name' => 'upload',
                'value' => _t("_adm_btn_settings_upload"),
            )
        )
    );

    if(isLogoUploaded()) {
        $aControls = array(
            'type' => 'input_set',
            'name' => 'controls',
        );
        $aControls[] = $aForm['inputs']['upload'];
        $aControls[] = array(
            'type' => 'submit',
            'name' => 'delete',
            'value' => _t("_adm_btn_settings_delete"),
        );

        $aForm['inputs']['upload'] = $aControls;
    }

    $oForm = new BxTemplFormView($aForm);
    $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $oForm->getCode()));

    if($mixedResultLogo !== true && !empty($mixedResultLogo))
        $sResult = MsgBox(_t($mixedResultLogo), 3) . $sResult;

    return DesignBoxAdmin(_t('_adm_box_cpt_logo'), $sResult);
}

function isLogoUploaded()
{
    global $dir;

    $sFileName = getParam('sys_main_logo');
    return $sFileName && file_exists($dir['mediaImages'] . $sFileName);
}

function setLogo(&$aData, &$aFile)
{
    global $dir;

    $aFileInfo = getimagesize($aFile['new_file']['tmp_name']);
    if(empty($aFileInfo))
        return '_adm_txt_settings_file_not_image';

    $sExt = '';
    switch( $aFileInfo['mime'] ) {
        case 'image/jpeg': $sExt = 'jpg'; break;
        case 'image/gif':  $sExt = 'gif'; break;
        case 'image/png':  $sExt = 'png'; break;
    }
    if(empty($sExt))
        return '_adm_txt_settings_file_wrong_format';

    $sFileName = mktime() . '.' . $sExt;
    $sFilePath = $dir['mediaImages'] . $sFileName;
    if(!move_uploaded_file($aFile['new_file']['tmp_name'], $sFilePath))
        return '_adm_txt_settings_file_cannot_move';

    if(!empty($aData['resize'])) {
        $iWidth = (int)$aData['new_width'];
        $iHeight = (int)$aData['new_height'];
        if($iWidth <= 0 || $iHeight <= 0)
            return '_adm_txt_settings_logo_wrong_size';

        if(imageResize($sFilePath, $sFilePath, $iWidth, $iHeight) != IMAGE_ERROR_SUCCESS)
            return '_adm_txt_settings_image_cannot_resize';
    }

    @unlink($dir['mediaImages'] . getParam('sys_main_logo'));
    setParam('sys_main_logo', $sFileName);

    return true;
}

function deleteLogo()
{
    global $dir;

    @unlink($dir['mediaImages'] . getParam('sys_main_logo'));
    setParam('sys_main_logo', '');
}
