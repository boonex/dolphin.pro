<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_SECURITY_EXCEPTIONS', true);
$aBxSecurityExceptions = array(
    'POST.Text',
    'REQUEST.Text',
    'POST.Url',
    'REQUEST.Url',
);

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin_design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

$logged['admin'] = member_auth(1, true, true);

//Possible deletion
$iBannerID = (int)bx_get('banner_id');
if ( $iBannerID > 0 && bx_get('action') == "delete" ) {
    db_res( "DELETE FROM `sys_banners` WHERE ID = '{$iBannerID}'" );
}

$sManageBannersBlock = getManageBannersBlock();
$sExistedBannersBlock = getExistedBannersBlock();
$sPreviewBlock = getPreviewBlock($iBannerID);

bx_import('BxTemplFormView');

$iNameIndex = 14;
$_page = array(
    'name_index' => $iNameIndex,
    'header' => _t('_adm_bann_title'),
    'header_text' => _t('_adm_bann_title')
);

$_page_cont[$iNameIndex]['page_main_code'] = $sPreviewBlock . $sExistedBannersBlock . $sManageBannersBlock;

PageCodeAdmin();

// Functions
function getPreviewBlock($iBannerID)
{
    $sPreview = MsgBox(_t('_Empty'));

    if (getGetFieldIfSet('action') == 'preview' && $iBannerID > 0) {
        $aBannerInfo = db_arr("SELECT * FROM `sys_banners` WHERE `ID` = '{$iBannerID}'");
        $sBannerTitle = process_line_output($aBannerInfo['Title']);
        $sBannerPut = banner_put($aBannerInfo['ID'], 0);

        $sPreview = <<<EOF
<table cellspacing="0" cellpadding="0" width="100%" height="200" align="center" style="border: 1px solid #ccc;">
    {$sBannerTitle}
    <tr><td align=center bgcolor=white>{$sBannerPut}</td></tr>
</table>
EOF;
        $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $sPreview));
        return DesignBoxContent(_t('_Preview'), $sResult, 1);
    }
}

function getExistedBannersBlock()
{
    $sClicksC = _t('_adm_bann_clicks');
    $sImpressionsC = _t('_adm_bann_impressions');
    $sPreviewC = _t('_Preview');
    $sEditC = _t('_Edit');
    $sDeleteC = _t('_Delete');

    // Get banner info from database.
    $banners_res = db_res("SELECT * FROM `sys_banners` ORDER BY `ID` DESC");
    $sExistedBanners = MsgBox(_t('_Empty'));
    if (  $banners_res ->rowCount() ) {
        $sExistedBanners = "<table cellspacing=1 cellpadding=2 border=0 class=small1 width=100%>";
        while ( $banns_arr = $banners_res->fetch() ) {
            $imp = db_arr("SELECT COUNT(*) FROM `sys_banners_shows` WHERE `ID` = '{$banns_arr['ID']}'");
            $clicks = db_arr("SELECT COUNT(*) FROM `sys_banners_clicks` WHERE `ID` = '{$banns_arr['ID']}'");

            $class = ( !$banns_arr['Active'] ) ? 'table_err' : 'panel';
            $sBannerTitle = process_line_output($banns_arr['Title']);

               $sExistedBanners .= <<<EOF
<tr class={$class}>
    <td>
        (<a href="banners.php?action=preview&banner_id={$banns_arr['ID']}">{$sPreviewC}</a> |
            <a href="banners.php?banner_id={$banns_arr['ID']}">{$sEditC}</a> |
            <a href="banners.php?banner_id={$banns_arr['ID']}&action=delete">{$sDeleteC}</a>)&nbsp;
            {$sBannerTitle}
        </a>
    </td>
    <td><b>{$clicks[0]}</b> {$sClicksC} </td>
    <td><b>{$imp[0]}</b> {$sImpressionsC} </td>
</tr>
EOF;
        }
        $sExistedBanners .= "</table>";
    }

    $sResult = $GLOBALS['oAdmTemplate']->parseHtmlByName('design_box_content.html', array('content' => $sExistedBanners));
    return DesignBoxContent(_t('_adm_mmi_banners'), $sResult, 11);
}

function getManageBannersBlock()
{
    $sAsNew = _t('_adm_bann_Insert_as_new');
    $sErrorC = _t('_Error Occured');
    $sApplyChangesC = _t('_Submit');
    $sTopC = _t('_adm_bann_Top');
    $sLeftC = _t('_adm_bann_Left');
    $sRightC = _t('_adm_bann_Right');
    $sBottomC = _t('_adm_bann_Bottom');
    $sHShiftC = _t('_adm_bann_HShift');
    $sVShiftC = _t('_adm_bann_VShift');
    $sTitleC = _t('_Title');
    $sUrlC = _t('_URL');
    $sActiveC = _t('_Active');
    $sTextC = _t('_Text');
    $sStartDateC = _t('_Start date');
    $sEndDateC = _t('_Expiration date');
    $sPositionOnPageC = _t('_adm_bann_Position_on_the_page');

    // get start & end dates
    $start_date_default = "2012-01-01";
    $end_date_default = "2020-01-01";

    $start_date = bx_get('start_date') !== false ? bx_get('start_date') : $start_date_default;
    $end_date = bx_get('end_date') !== false ? bx_get('end_date') : $end_date_default;

    $Title = $Url = $Active = $Text = $Position = $lhshift = $lvshift = $rhshift = $rvshift = '';
    $iBannerID = (int)getGetFieldIfSet('banner_id');
    $action	= "new";

    if ($iBannerID > 0 && ! strlen(bx_get('action'))) { //banner edit
        $banns_arr = db_arr("SELECT * FROM `sys_banners` WHERE `ID`='{$iBannerID}'");

        $action	= "modify";

        $Title	= $banns_arr['Title'];
        $Url	= $banns_arr['Url'];
        $Text	= $banns_arr['Text'];
        $Active = $banns_arr['Active'];
        $Position = $banns_arr['Position'];

        $lhshift = $banns_arr['lhshift'];
        $lvshift = $banns_arr['lvshift'];
        $rhshift = $banns_arr['rhshift'];
        $rvshift = $banns_arr['rvshift'];


        $start_date = $banns_arr['campaign_start'];
        $end_date = $banns_arr['campaign_end'];
    }

    $sFormTitle = htmlspecialchars($Title);
    $sFormUrl = htmlspecialchars($Url);
    $sFormActiveState = ($Active) ? 'checked="checked"' : '';

    $sFormActiveStateVal = ($Active) ? 'yes' : '';
    $sFormActiveStateChk = ($Active) ? true : false;

    $sFormBannerText = $Text;
    $sFormStartDate = $start_date;
    $sFormEndDate = $end_date;

    $sTopPosState = (substr_count($Position,"1") > 0 ) ? 'checked="checked"' : '';
    $sLeftPosState = (substr_count($Position,"2") > 0 ) ? 'checked="checked"' : '';
    $sRightPosState = (substr_count($Position,"3") > 0 ) ? 'checked="checked"' : '';
    $sBottomPosState = (substr_count($Position,"4") > 0 ) ? 'checked="checked"' : '';

    $sTopShift = (substr_count($Position,"2") > 0 ) ? $lhshift : '';
    $sLeftShift = (substr_count($Position,"2") > 0 ) ? $lvshift : '';
    $sRightShift = (substr_count($Position,"3") > 0 ) ? $rhshift : '';
    $sBottomShift = (substr_count($Position,"3") > 0 ) ? $rvshift : '';

    $sActionAdd = ($action == "modify") ? $sAsNew . '&nbsp;<input type=checkbox name=as_new />' : '';

    $sCustomPositions = <<<EOF
<style>
.banner-positions td {
    border:none;
}
</style>
<table class="banner-positions" border=0 width=100% cellspacing=10 cellpading=20>
    <tr>
        <td colspan=5 align=center><input type=checkbox name="pos_top" {$sTopPosState} />{$sTopC}</td>
    </tr>
    <tr>
        <td colspan=2 align=center><input type=checkbox name="pos_left" {$sLeftPosState} />{$sLeftC}</td>
        <td>&nbsp;</td>
        <td colspan=2 align=center><input type=checkbox name="pos_right" {$sRightPosState} />{$sRightC}</td>
    </tr>
    <tr>
        <td>{$sHShiftC}</td>
        <td>{$sVShiftC}</td>
        <td>&nbsp;</td>
        <td>{$sHShiftC}</td>
        <td>{$sVShiftC}</td>
    </tr>
    <tr>
        <td><input name="lhshift" type=input size=5 value={$sTopShift} /></td>
        <td><input name="lvshift" type=input size=5 value={$sLeftShift} /></td>
        <td>&nbsp;</td>
        <td><input name="rhshift" type=input size=5 value={$sRightShift} /></td>
        <td><input name="rvshift" type=input size=5 value={$sBottomShift} /></td>
    </tr>
    <tr>
        <td colspan=5 align=center><input type=checkbox name="pos_bottom" {$sBottomPosState} />{$sBottomC}</td>
    </tr>
</table>
{$sActionAdd}
EOF;

    $aForm = array(
        'form_attrs' => array(
            'name' => 'apply_ip_list_form',
            'action' => $GLOBALS['site']['url_admin'] . 'banners.php',
            'method' => 'post',
        ),
        'params' => array (
            'db' => array(
                'table' => 'sys_banners',
                'key' => 'ID',
                'submit_name' => 'add_button',
            ),
        ),
        'inputs' => array(
            'BannerTitle' => array(
                'type' => 'text',
                'name' => 'Title',
                'value' => $sFormTitle,
                'caption' => $sTitleC,
                'required' => true,
                'checker' => array (
                    'func' => 'length',
                    'params' => array(2,128),
                    'error' => _t('_chars_to_chars', 2, 128),
                ),
                'db' => array (
                    'pass' => 'Xss',
                ),
            ),
            'BannerUrl' => array(
                'type' => 'text',
                'name' => 'Url',
                'value' => $sFormUrl,
                'caption' => $sUrlC,
                'required' => false,
                'db' => array (
                    'pass' => 'Xss',
                ),
            ),
            'BannerActive' => array(
                'type' => 'checkbox',
                'name' => 'Active',
                'caption' => $sActiveC,
                'value' => 1,
                'checked' => $sFormActiveStateChk,
            ),
            'BannerText' => array(
                'type' => 'textarea',
                'name' => 'Text',
                'value' => $sFormBannerText,
                'caption' => $sTextC,
                'required' => true,
                'checker' => array (
                    'func' => 'length',
                    'params' => array(10,32000),
                    'error' => _t('_chars_to_chars', 10, 32000),
                ),
                'db' => array (
                    'pass' => 'All',
                ),
            ),
            'StartDate' => array(
                'type' => 'date',
                'name' => 'start_date',
                'value' => $sFormStartDate,
                'caption' => $sStartDateC,
                'required' => true,
                'checker' => array (
                    'func' => 'Date',
                    'error' => $sErrorC,
                ),
            ),
            'EndDate' => array(
                'type' => 'date',
                'name' => 'end_date',
                'value' => $sFormEndDate,
                'caption' => $sEndDateC,
                'required' => true,
                'checker' => array (
                    'func' => 'Date',
                    'error' => $sErrorC,
                ),
            ),
            'Positions' => array(
                'type' => 'custom',
                'name' => 'Position',
                'caption' => $sPositionOnPageC,
                'content' => $sCustomPositions
            ),
            'ID' => array(
                'type' => 'hidden',
                'name' => 'banner_id',
                'value' => $iBannerID,
            ),
            'Action' => array(
                'type' => 'hidden',
                'name' => 'action',
                'value' => $action,
            ),
            'add_button' => array(
                'type' => 'submit',
                'name' => 'add_button',
                'value' => $sApplyChangesC,
            ),
        ),
    );

    $sResult = '';
    $oForm = new BxTemplFormView($aForm);
    $oForm->initChecker();
    if ($oForm->isSubmittedAndValid()) {
        list($iYearStart, $iMonthStart, $iDayStart) = explode( '-', $oForm->getCleanValue('start_date'));
        $sDateStart = "{$iYearStart}-{$iMonthStart}-{$iDayStart}";

        list($iYearEnd, $iMonthEnd, $iDayEnd) = explode( '-', $oForm->getCleanValue('end_date'));
        $sDateEnd = "{$iYearEnd}-{$iMonthEnd}-{$iDayEnd}";

        $sCurTime = date("Y-m-d");// 2010-12-31

        $iLastId = (int)$oForm->getCleanValue('banner_id');

        $banner_pos = "";
        if($oForm->getCleanValue('pos_top') == "on" ) $banner_pos .= '1';
        if($oForm->getCleanValue('pos_left') == "on" ) $banner_pos .= '2';
        if($oForm->getCleanValue('pos_right') == "on" ) $banner_pos .= '3';
        if($oForm->getCleanValue('pos_bottom') == "on" ) $banner_pos .= '4';
        $banner_pos = (int)$banner_pos;

        $banner_lhshift = (int)$oForm->getCleanValue('lhshift');
        $banner_lvshift = (int)$oForm->getCleanValue('lvshift');
        $banner_rhshift = (int)$oForm->getCleanValue('rhshift');
        $banner_rvshift = (int)$oForm->getCleanValue('rvshift');

        $aValsAdd = array (
            'Position' => $banner_pos,
            'Active' => '' != $oForm->getCleanValue('Active') ? 1 : 0,
            'Created' => $sCurTime,
            'campaign_start' => $sDateStart,
            'campaign_end' => $sDateEnd,
            'lhshift' => $banner_lhshift,
            'lvshift' => $banner_lvshift,
            'rhshift' => $banner_rhshift,
            'rvshift' => $banner_rvshift,
        );

        if ($oForm->getCleanValue('action') == 'modify' && $oForm->getCleanValue('as_new') != "on" && $iLastId > 0 ) {
            $oForm->update($iLastId, $aValsAdd);
        }
        if ($oForm->getCleanValue('action') == 'new' || $oForm->getCleanValue('as_new') == "on" && $oForm->getCleanValue('action') == 'modify' ) {
            $iLastId = $oForm->insert($aValsAdd);
        }

        $sResult = ($iLastId > 0) ? MsgBox(_t('_Success'), 3) : MsgBox($sErrorC);
    }

    return DesignBoxContent(_t('_adm_bann_title'), $sResult . $oForm->getCode(), 11);
}
