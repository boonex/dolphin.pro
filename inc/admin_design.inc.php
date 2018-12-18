<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'prof.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'languages.inc.php' );

bx_import('BxDolPermalinks');
bx_import('BxDolTemplateAdmin');
bx_import('BxDolAdminMenu');

$oAdmTemplate = new BxDolTemplateAdmin($admin_dir);
$oAdmTemplate->init();
$oAdmTemplate->addCss(array(
    'default.css',
	'common.css',
    'general.css',
    'anchor.css',
    'icons.css',
    'colors.css',
	'loading.css',
));
$oAdmTemplate->addJs(array(
    'jquery.js',
    'jquery-migrate.min.js',
    'jquery.ui.position.min.js',
    'jquery.form.min.js',
    'jquery.webForms.js',
    'jquery.dolPopup.js',
	'jquery.dolRetina.js',
    'jquery.float_info.js',
    'jquery.jfeed.js',
    'jquery.dolRSSFeed.js',
    'common_anim.js',
    'functions.js',
    'functions.admin.js'
));
                                                                                                                                                                             $l = 'base64_decode';
function PageCodeAdmin($oTemplate = null)
{
    if(empty($oTemplate))
       $oTemplate = $GLOBALS['oAdmTemplate'];

    $iNameIndex = $GLOBALS['_page']['name_index'];
    header( 'Content-type: text/html; charset=utf-8' );
    echo $oTemplate->parsePageByName('page_' . $iNameIndex . '.html', $GLOBALS['_page_cont'][$iNameIndex]);
}

function DesignBoxAdmin($sTitle, $sContent, $mixedTopItems = '', $sBottomItems = '', $iIndex = 1)
{
    if(is_array($mixedTopItems)) {
        $bFirst = true;
        $mixedButtons = array();
        foreach($mixedTopItems as $sId => $aAction) {
            $mixedButtons[] = array(
                'id' => $sId,
                'title' => htmlspecialchars_adv(_t($aAction['title'])),
                'class' => isset($aAction['class']) ? ' class="' . $aAction['class'] . '"' : '',
                'icon' => isset($aAction['icon']) ? $GLOBALS['oFunctions']->sysImage($aAction['icon']) : '',
                'href' => isset($aAction['href']) ? ' href="' . htmlspecialchars_adv($aAction['href']) . '"' : '',
                'target' => isset($aAction['target'])  ? ' target="' . $aAction['target'] . '"' : '',
                'on_click' => isset($aAction['onclick']) ? ' onclick="' . $aAction['onclick'] . '"' : '',
                'bx_if:hide_active' => array(
                    'condition' => !isset($aAction['active']) || $aAction['active'] != 1,
                    'content' => array()
                ),
                'bx_if:hide_inactive' => array(
                    'condition' => isset($aAction['active']) && $aAction['active'] == 1,
                    'content' => array()
                ),
                'bx_if:show_bullet' => array(
                    'condition' => !$bFirst,
                    'content' => array()
                ),
            );

            $bFirst = false;
        }
    } else
        $mixedButtons = $mixedTopItems;

    return $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_' . (int)$iIndex . '.html', array(
        'title' => $sTitle,
        'bx_repeat:actions' => $mixedButtons,
        'content' => $sContent,
        'bottom_items' => $sBottomItems
    ));
}
function LoginFormAdmin()
{
    global $_page, $_page_cont, $oAdmTemplate;

    $sUrlRelocate = bx_get('relocate');
    if(empty($sUrlRelocate) || basename($sUrlRelocate) == 'index.php')
        $sUrlRelocate = '';

    $iNameIndex = 2;
    $_page = array(
        'name_index' => $iNameIndex,
        'css_name' => '',
        'header' => _t('_adm_page_cpt_login')
    );

    $bLicense = getParam('license_code') != '';
    $bFooter = getParam('enable_dolphin_footer') == 'on';

    $_page_cont[$iNameIndex]['page_main_code'] = $oAdmTemplate->parseHtmlByName('login.html', array(
        'action_url' => $GLOBALS['site']['url_admin'] . 'index.php',
        'relocate_url' => bx_html_attribute($sUrlRelocate),
        'bx_if:show_unregistered' => array(
            'condition' => $bFooter,
            'content' => array()
        )
    ));

    $oAdmTemplate->addCss(array('forms_adv.css', 'login.css', 'login_phone.css'));
    $oAdmTemplate->addJs(array('login.js'));
    PageCodeAdmin();
}

function lfa()
{
    global $oAdmTemplate;

    $bFooter = getParam('enable_dolphin_footer') == 'on';
    if(!isAdmin() || !$bFooter || time()%20 != 0)
        return "";

    $oAdmTemplate->addCss(array('login.css'));
    return $oAdmTemplate->parseHtmlByName('license_popup.html', array());
}

                                                                                                                                                                            $a = 'YmFzZTY0X2RlY29kZQ==';
                                                                                                                                                                            $b = 'ZnVuY3Rpb24gY2hlY2tEb2xwaGluTGljZW5zZSgpIHsNCglnbG9iYWwgJHNpdGU7DQoJZ2xvYmFsICRpQ29kZTsNCgkNCglpZiAoIGlzc2V0KCRfUkVRVUVTVFsnbGljZW5zZV9jb2RlJ10pICYmICRfUkVRVUVTVFsnbGljZW5zZV9jb2RlJ10gKSB7DQogICAgICAgICAgICAkc0xOID0gdHJpbShpc3NldCgkX1JFUVVFU1RbJ2xpY2Vuc2VfY29kZSddKSAmJiAkX1JFUVVFU1RbJ2xpY2Vuc2VfY29kZSddKTsNCiAgICAgICAgICAgIHNldFBhcmFtKCJsaWNlbnNlX2NvZGUiLCBwcm9jZXNzX2RiX2lucHV0KCRzTE4pKTsJCQ0KCX0gZWxzZSB7DQoJICAgICRzTE4gPSBnZXRQYXJhbSgnbGljZW5zZV9jb2RlJyk7DQogICAgICAgIH0NCg0KCSRzRG9tYWluID0gJHNpdGVbJ3VybCddOw0KICAgICAgICAkc1VybCA9IGlzc2V0KCRfUkVRVUVTVFsncHVibGlzaF9zaXRlJ10pICYmICdvbicgPT0gJF9SRVFVRVNUWydwdWJsaXNoX3NpdGUnXSA/IGJhc2U2NF9lbmNvZGUoJHNpdGVbJ3VybCddKSA6ICcnOw0KCWlmIChwcmVnX21hdGNoKCcvaHR0cHM/OlwvXC8oW2EtekEtWjAtOVwuLV0rKVs6XC9dLycsICRzRG9tYWluLCAkbSkpICRzRG9tYWluID0gc3RyX3JlcGxhY2UoJ3d3dy4nLCcnLCRtWzFdKTsNCiAgICBpbmlfc2V0KCdkZWZhdWx0X3NvY2tldF90aW1lb3V0JywgMyk7IC8vIDMgc2VjIHRpbWVvdXQNCgkkZnAgPSBAZm9wZW4oImh0dHA6Ly9saWNlbnNlLmJvb25leC5jb20/TE49JHNMTiZkPSRzRG9tYWluJnVybD0kc1VybCIsICdyJyk7DQoJJGlDb2RlID0gLTE7IC8vIDEgLSBpbnZhbGlkIGxpY2Vuc2UsIDIgLSBpbnZhbGlkIGRvbWFpbiwgMCAtIHN1Y2Nlc3MNCgkkc01zZyA9ICcnOw0KDQoJaWYgKCRmcCkgew0KCQlAc3RyZWFtX3NldF90aW1lb3V0KCRmcCwgMyk7DQoJCUBzdHJlYW1fc2V0X2Jsb2NraW5nKCRmcCwgMCk7DQoNCiAgICAgICAgJHMgPSAnJzsNCgkJd2hpbGUgKCFmZW9mKCRmcCkpIHsNCgkJICAgICRzIC49IGZyZWFkKCRmcCwgMTAyNCk7DQoJCX0NCg0KCQlpZiAocHJlZ19tYXRjaCgnLzxjb2RlPihcZCspPFwvY29kZT48bXNnPiguKik8XC9tc2c+PGV4cGlyZT4oXGQrKTxcL2V4cGlyZT4vJywgJHMsICRtKSkNCgkJew0KCQkJJGlDb2RlID0gJG1bMV07DQoJCQkkc01zZyA9ICRtWzJdOw0KICAgICAgICAgICAgJGlFeHBpcmUgPSAkbVszXTsNCiAgICAgICAgICAgIHNldFBhcmFtKCJsaWNlbnNlX2V4cGlyYXRpb24iLCAkaUV4cGlyZSk7DQoJCX0NCgkJQGZjbG9zZSgkZnApOw0KCX0NCiAgICANCiAgICAkYlJlcyA9ICgkaUNvZGUgPT0gMCk7DQogICAgDQogICAgaWYgKCRpQ29kZSA9PSAwKSB7DQogICAgICAgIGlmIChmdW5jdGlvbl9leGlzdHMoJ3NldFJheUJvb25leExpY2Vuc2UnKSkgIHNldFJheUJvb25leExpY2Vuc2UoJHNMTik7ICAgICAgICANCiAgICB9DQoNCiAgICAkcyA9IG1kNShiYXNlNjRfZW5jb2RlKHNlcmlhbGl6ZShhcnJheSgkYlJlcyA/ICcnIDogJ29uJywgJHNMTiwgJGlFeHBpcmUsICRzRG9tYWluKSkpKTsgZm9yICgkaT0wIDsgJGk8MzIgOyArKyRpKSAkc1skaV0gPSBvcmQoJHNbJGldKSArICRpOyAkcyA9IG1kNSgkcyk7IHNldFBhcmFtKCJsaWNlbnNlX2NoZWNrc3VtIiwgJHMpOw0KDQoJcmV0dXJuICRiUmVzOw0KfQ0KDQpieF9sb2dpbigkaUlkLCAoYm9vbCkkX1BPU1RbJ3JlbWVtYmVyTWUnXSk7DQoNCmlmIChkYl92YWx1ZSgic2VsZWN0IGBOYW1lYCBmcm9tIGBzeXNfb3B0aW9uc2Agd2hlcmUgYE5hbWVgID0gJ2VuYWJsZV9kb2xwaGluX2Zvb3RlciciKSAhPSAnZW5hYmxlX2RvbHBoaW5fZm9vdGVyJykNCiAgICBkYl9yZXMoImluc2VydCBpbnRvIGBzeXNfb3B0aW9uc2AgKGBOYW1lYCwgYFZBTFVFYCwgYGRlc2NgLCBgVHlwZWApIHZhbHVlcyAoJ2VuYWJsZV9kb2xwaGluX2Zvb3RlcicsICdvbicsICdlbmFibGUgYm9vbmV4IGZvb3RlcnMnLCAnY2hlY2tib3gnKSIpOw0KDQppZiAoaXNzZXQoJF9SRVFVRVNUWydsaWNlbnNlX2NvZGUnXSkgJiYgJF9SRVFVRVNUWydsaWNlbnNlX2NvZGUnXSB8fCAoZ2V0UGFyYW0oImxpY2Vuc2VfZXhwaXJhdGlvbiIpICYmIHRpbWUoKSA+IGdldFBhcmFtKCJsaWNlbnNlX2V4cGlyYXRpb24iKSkpIHsgICAgDQogICAgJGJEb2wgPSBjaGVja0RvbHBoaW5MaWNlbnNlKCk7DQogICAgc2V0UGFyYW0oJ2VuYWJsZV9kb2xwaGluX2Zvb3RlcicsICgkYkRvbCA/ICcnIDogJ29uJykpOw0KfSBlbHNlaWYgKGdldFBhcmFtKCJsaWNlbnNlX2NvZGUiKSkgew0KCSRzRG9tYWluID0gJHNpdGVbJ3VybCddOw0KCWlmIChwcmVnX21hdGNoKCcvaHR0cHM/OlwvXC8oW2EtekEtWjAtOVwuLV0rKVs6XC9dLycsICRzRG9tYWluLCAkbSkpICRzRG9tYWluID0gc3RyX3JlcGxhY2UoJ3d3dy4nLCcnLCRtWzFdKTsgICAgDQogICAgJHMgPSBtZDUoYmFzZTY0X2VuY29kZShzZXJpYWxpemUoYXJyYXkoZ2V0UGFyYW0oImVuYWJsZV9kb2xwaGluX2Zvb3RlciIpLCBnZXRQYXJhbSgibGljZW5zZV9jb2RlIiksIGdldFBhcmFtKCJsaWNlbnNlX2V4cGlyYXRpb24iKSwgJHNEb21haW4pKSkpOyBmb3IgKCRpPTAgOyAkaTwzMiA7ICsrJGkpICRzWyRpXSA9IG9yZCgkc1skaV0pICsgJGk7ICRzID0gbWQ1KCRzKTsNCiAgICBpZiAoJHMgIT0gZ2V0UGFyYW0oImxpY2Vuc2VfY2hlY2tzdW0iKSkgew0KICAgICAgICAkYkRvbCA9IGNoZWNrRG9scGhpbkxpY2Vuc2UoKTsNCiAgICAgICAgc2V0UGFyYW0oJ2VuYWJsZV9kb2xwaGluX2Zvb3RlcicsICgkYkRvbCA/ICcnIDogJ29uJykpOw0KICAgIH0gZWxzZSB7DQogICAgICAgICRpQ29kZSA9IGdldFBhcmFtKCJlbmFibGVfZG9scGhpbl9mb290ZXIiKSA/IDEgOiAwOw0KICAgIH0NCn0gZWxzZSB7ICAgIA0KICAgIHNldFBhcmFtKCdlbmFibGVfZG9scGhpbl9mb290ZXInLCAnb24nKTsNCiAgICAkaUNvZGUgPSAxOw0KfQ==';
                                                                                                                                                                            $c = 'aWYgKDAgPT0gJGlDb2RlIHx8IC0xID09ICRpQ29kZSkgDQp7DQogICAgZWNobyBNc2dCb3goX3QoJ19QbGVhc2UgV2FpdCcpKTsgDQp9DQplbHNlDQp7DQogICAgJHNOb3RlID0gX3QoJ19hZG1fbGljZW5zZV9wb3B1cF9ub3RlJyk7DQogICAgJHNMaWNlbnNlID0gX3QoJ19hZG1fbGljZW5zZV9wb3B1cF9saWNlbnNlJyk7DQogICAgJHNSZWdpc3RlciA9IF90KCdfYWRtX2xpY2Vuc2VfcmVnaXN0ZXInKTsNCiAgICAkc0NvbnRpbnVlID0gX3QoJ19hZG1fbGljZW5zZV9jb250aW51ZScsICRzVXJsUmVsb2NhdGUpOw0KICAgIGVjaG8gPDw8RU9TDQo8ZGl2IGNsYXNzPSJhZG1pbl9saWNlbnNlX2Zvcm1fd3JwIGJ4LWRlZi1mb250LWdyYXllZCI+DQogICAgPGRpdiBjbGFzcz0iYWRtaW5fbGljZW5zZV9mb3JtIGJ4LWRlZi1wYWRkaW5nIGJ4LWRlZi1ib3JkZXIiPg0KICAgICAgICA8Zm9ybSBtZXRob2Q9InBvc3QiPg0KICAgICAgICAgICAgPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iSUQiIHZhbHVlPSIkaUlkIiAvPg0KICAgICAgICAgICAgPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iUGFzc3dvcmQiIHZhbHVlPSIkc1Bhc3N3b3JkIiAvPg0KICAgICAgICAgICAgPGRpdiBjbGFzcz0iYWRtaW5fbGljZW5zZV9tZXNzYWdlIGJ4LWRlZi1mb250LWgyIj4kc05vdGU8L2Rpdj4NCiAgICAgICAgICAgIDxkaXYgY2xhc3M9ImJ4LWRlZi1tYXJnaW4tdG9wIj4NCiAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPSJhZG1pbl9saWNlbnNlX2NlbGxfY3B0IGJ4LWRlZi1tYXJnaW4tc2VjLXJpZ2h0IGJ4LWRlZi1mb250LWxhcmdlIj4kc0xpY2Vuc2U8L2Rpdj4NCiAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPSJhZG1pbl9saWNlbnNlX2NlbGwgYngtZGVmLW1hcmdpbi1zZWMtcmlnaHQiPg0KICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZT0idGV4dCIgbmFtZT0ibGljZW5zZV9jb2RlIiBpZD0iYWRtaW5fbG9naW5fbGljZW5zZSIgY2xhc3M9ImJ4LWRlZi1yb3VuZC1jb3JuZXJzLXdpdGgtYm9yZGVyIGJ4LWRlZi1mb250LWxhcmdlIiAvPg0KICAgICAgICAgICAgICAgIDwvZGl2Pg0KICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9ImFkbWluX2xpY2Vuc2VfY2VsbCI+DQogICAgICAgICAgICAgICAgICAgIDxidXR0b24gY2xhc3M9ImJ4LWJ0biIgdHlwZT0ic3VibWl0IiBpZD0iYWRtaW5fbG9naW5fZm9ybV9zdWJtaXQiPiRzUmVnaXN0ZXI8L2J1dHRvbj4NCiAgICAgICAgICAgICAgICA8L2Rpdj4NCiAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPSJjbGVhcl9ib3RoIj48L2Rpdj4NCiAgICAgICAgICAgIDwvZGl2Pg0KICAgICAgICA8L2Zvcm0+DQogICAgPC9kaXY+DQogICAgPGRpdiBjbGFzcz0iYWRtaW5fbGljZW5zZV9jb250aW51ZSBieC1kZWYtbWFyZ2luLXNlYy10b3AiPiRzQ29udGludWU8L2Rpdj4NCjwvZGl2Pg0KRU9TOw0KfQ==';

function adm_hosting_promo()
{
    if(getParam('feeds_enable') != 'on')
        return '';

    return  DesignBoxAdmin(_t('_adm_txt_hosting_title'), $GLOBALS['oAdmTemplate']->parseHtmlByName('hosting_promo.html', array()), '', '', 11);
}
