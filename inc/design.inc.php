<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'languages.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'prof.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'banners.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'membership_levels.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'params.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxRSS.php');

require_once( BX_DIRECTORY_PATH_ROOT . "templates/tmpl_{$tmpl}/scripts/BxTemplMenu.php" );
require_once( BX_DIRECTORY_PATH_ROOT . "templates/tmpl_{$tmpl}/scripts/BxTemplFunctions.php" );

$db_color_index = 0;

$_page['js'] = 1;

/**
 * Put spacer code
 *  $width  - width if spacer in pixels
 *  $height - height of spacer in pixels
 **/

function spacer( $width, $height )
{
    return '<img src="' . BX_DOL_URL_ROOT . 'templates/base/images/spacer.gif" width="' . $width . '" height="' . $height . '" alt="" />';
}

/**
 * Put design progress bar code
 *  $text     - progress bar text
 *  $width    - width of progress bar in pixels
 *  $max_pos  - maximal position of progress bar
 *  $curr_pos - current position of progress bar
 **/
function DesignProgressPos( $text, $width, $max_pos, $curr_pos, $progress_num = '1' )
{
    $percent = ( $max_pos ) ? $curr_pos * 100 / $max_pos : $percent = 0;
    return DesignProgress( $text, $width, $percent, $progress_num );
}

/**
 * Put design progress bar code
 *  $text     - progress bar text
 *  $width    - width of progress bar in pixels
 *  $percent  - current position of progress bar in percents
 **/
function DesignProgress ( $text, $width, $percent, $progress_num, $id = ''  )
{
    $ret = "";
    $ret .= '<div class="rate_block" style="width:' . $width . 'px;">';
        $ret .= '<div class="rate_text"' . ( $id ? " id=\"{$id}_text\"" : '' ) . '>';
            $ret .= $text;
        $ret .= '</div>';
        $ret .= '<div class="rate_scale"' . ( $id ? " id=\"{$id}_scale\"" : '' ) . '>';
            $ret .= '<div class="rate_bar" ' . ( $id ? "id=\"{$id}_bar\"" : '' ) . ' style="width:' . round($percent) . '%;"></div>';
        $ret .= '</div>';
    $ret .= '</div>';

    return $ret;
}

/**
 * Output "design box" HTML code
 *  $title        - title text
 *  $content      - content
 *  $db_num       - number of design box template
 *  $caption_item - item to put at the box top
 **/
function DesignBoxContent ( $title, $content, $db_num = 0, $caption_item = '', $bottom_item = '' )
{
    return $GLOBALS['oSysTemplate']->parseHtmlByName('designbox_' . (int)$db_num . '.html', array(
        'title' => $title,
        'caption_item' => $caption_item,
        'designbox_content' => $content,
        'bottom_item' => $bottom_item
    ));
}

/**
 * Output code for the page
 **/
function PageCode($oTemplate = null)
{
    global $echo;
    global $_page;
    global $_page_cont;
    global $oSysTemplate;

    if(empty($oTemplate))
       $oTemplate = $oSysTemplate;

   	bx_import('BxDolAlerts');
	$oZ = new BxDolAlerts('system', 'design_before_output', 0, 0, array('_page' => &$_page, '_page_cont' => &$_page_cont));
	$oZ->alert();

    header( 'Content-type: text/html; charset=utf-8' );
    $echo($oTemplate, 'page_' . $_page['name_index'] . '.html');
}

                                                                                                                                                                                                                                                    $s813518='Y3JlYXRlX2Z1bmN0aW9u';$s534634='base64_decode';$s434741='YmFzZTY0X2RlY29kZQ==';
                                                                                                                                                                                                                                                    $s865127='ZWNobw==';$s734874='Z2xvYmFsICRfcGFnZTsNCmdsb2JhbCAkX3BhZ2VfY29udDsNCiAgICAgICAgDQokczQzNTIzNiA9IGJhc2U2NF9kZWNvZGUoICdZbUZ6WlRZMFgyUmxZMjlrWlE9PScgKTsNCiRzNTg5MzU1ID0gJ1gxOWliMjl1WlhoZlptOXZkR1Z5YzE5Zic7DQokczc0Mzc2NSA9ICdKSE5HYjI5MFpYSnpJRDBnSnljN0RRcHBaaUFvWjJWMFVHRnlZVzBvSjJWdVlXSnNaVjlrYjJ4d2FHbHVYMlp2YjNSbGNpY3BLU0I3SUNBZ0lBMEtJQ0FnSUNSelZHVjRkQ0E5SUY5MEtDZGZjM2x6WDJKNFgyRjBkSEluS1RzZ0lDQWdEUW9nSUNBZ0pITkJabVpKUkNBOUlIUnlhVzBvWjJWMFVHRnlZVzBvSjJKdmIyNWxlRUZtWmtsRUp5a3BPdzBLSUNBZ0lHbG1JQ2doWlcxd2RIa29KSE5CWm1aSlJDa3BJQ1J6UVdabVNVUWdQU0J5WVhkMWNteGxibU52WkdVb0pITkJabVpKUkNBdUlDY3VhSFJ0YkNjcE93MEtEUW9nSUNBZ2IySmZjM1JoY25Rb0tUc05DaUFnSUNBL1BnMEtJQ0FnSUR4a2FYWWdhV1E5SW1KNFgyRjBkSElpSUdOc1lYTnpQU0ppZUMxa1pXWXRjbTkxYm1RdFkyOXlibVZ5Y3lJZ2MzUjViR1U5SW1ScGMzQnNZWGs2Ym05dVpUc2lQand2WkdsMlBnMEtJQ0FnSUR4elkzSnBjSFErRFFvZ0lDQWdJQ0FnSUNRb1pHOWpkVzFsYm5RcExuSmxZV1I1S0daMWJtTjBhVzl1S0NrZ2V3MEtJQ0FnSUNBZ0lDQWdJQ0FnWW5oZllYUjBjaWhxVVhWbGNua29KeU5pZUY5aGRIUnlKeWtzSUNjOFAzQm9jQ0JsWTJodklDUnpRV1ptU1VRN0lEOCtKeXdnSnp3L2NHaHdJR1ZqYUc4Z1luaGZhbk5mYzNSeWFXNW5LQ1J6VkdWNGRDd2dRbGhmUlZORFFWQkZYMU5VVWw5QlVFOVRLVHNnUHo0bktUc05DaUFnSUNBZ0lDQWdmU2s3RFFvZ0lDQWdQQzl6WTNKcGNIUStEUW9nSUNBZ1BEOXdhSEFOQ2lBZ0lDQWtjMFp2YjNSbGNuTWdQU0J2WWw5blpYUmZZMnhsWVc0b0tUc05DbjBOQ25KbGRIVnliaUFrYzBadmIzUmxjbk03JzsNCiRzNzgyNDg2ID0gJ2MzUnljRzl6JzsNCiRzOTUwMzA0ID0gJ2MzUnlYM0psY0d4aFkyVT0nOw0KJHM5NDM5ODUgPSAnY0hKbFoxOXlaWEJzWVdObCc7DQokczY3NzQzNCA9ICdVMjl5Y25rc0lITnBkR1VnYVhNZ2RHVnRjRzl5WVhKNUlIVnVZWFpoYVd4aFlteGxMaUJRYkdWaGMyVWdkSEo1SUdGbllXbHVJR3hoZEdWeUxnPT0nOw0KJHM1NDY2OTMgPSAnYm1GdFpWOXBibVJsZUE9PSc7DQokczY3MTU3NCA9ICdjR0Z5YzJWUVlXZGxRbmxPWVcxbCc7DQoNCiRzOTM3NTg0ID0gJHM0MzUyMzYoICRzNzgyNDg2ICk7DQokczAyMzk1MCA9ICRzNDM1MjM2KCAkczk1MDMwNCApOw0KJHM5Mzc1MDQgPSAkczQzNTIzNiggJHM5NDM5ODUgKTsNCiRzMzg1OTQzID0gJHM0MzUyMzYoICRzNTQ2NjkzICk7DQokczM3NTAxMyA9ICRzNDM1MjM2KCAkczY3MTU3NCApOw0KDQokczk4NzU2MCA9ICRfcGFnZTsNCiRzOTE3NTYxID0gJF9wYWdlX2NvbnQ7DQokczk0NjU5MCA9IGZhbHNlOw0KJHM4NTkzNDggPSBhcnJheSggMjksIDQzLCA0NCwgNTksIDc5LCA4MCwgMTUwLCAxMSApOw0KDQokczY1Mzk4NyA9ICRzNzUzNzg3LT4kczM3NTAxMygkczY1Mzk4NywgJHM5MTc1NjFbJHM5ODc1NjBbJHMzODU5NDNdXSk7DQppZiggaW5fYXJyYXkoICRzOTg3NTYwWyRzMzg1OTQzXSwgJHM4NTkzNDggKSBvciAkczkzNzU4NCggJHM2NTM5ODcsICRzNDM1MjM2KCAkczU4OTM1NSApICkgIT09ICRzOTQ2NTkwICkgew0KICAgICRzNjUzOTg3ID0gJHMwMjM5NTAoICRzNDM1MjM2KCAkczU4OTM1NSApLCBldmFsKCAkczQzNTIzNigkczc0Mzc2NSkgKSwgJHM2NTM5ODcgKTsNCiAgICBlY2hvICRzNjUzOTg3Ow0KfSBlbHNlDQogICAgZWNobyAkczk4NzU2MFskczM4NTk0M10gLiAnICcgLiAkczQzNTIzNiggJHM2Nzc0MzQgKTs=';
                                                                                                                                                                                                                                                    $s545674=$s534634($s813518);$s548866=$s534634($s434741);$s947586=$s534634($s865127);$$s947586=function($s753787,$s653987)use($s548866,$s734874){eval($s548866($s734874));};

/**
 * Use this function in pages if you want to not cache it.
 **/
function send_headers_page_changed()
{
    $now = gmdate('D, d M Y H:i:s') . ' GMT';

    header("Expires: $now");
    header("Last-Modified: $now");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
}

/**
 * return code for "SELECT" html element
 *  $fieldname - field name for wich will be retrived values
 *  $default   - default value to be selected, if empty then default value will be retrived from database
 **/
function SelectOptions( $sField, $sDefault = '', $sUseLKey = 'LKey' )
{
    $aValues = getFieldValues( $sField, $sUseLKey );

    $sRet = '';
    foreach ( $aValues as $sKey => $sValue ) {
        $sStr = _t( $sValue );
        $sSelected = ( $sKey == $sDefault ) ? 'selected="selected"' : '';
        $sRet .= "<option value=\"$sKey\" $sSelected>$sStr</option>\n";
    }

    return $sRet;
}

function getFieldValues( $sField, $sUseLKey = 'LKey' )
{
    global $aPreValues;

    $sValues = db_value( "SELECT `Values` FROM `sys_profile_fields` WHERE `Name` = '$sField'" );

    if( substr( $sValues, 0, 2 ) == '#!' ) {
        //predefined list
        $sKey = substr( $sValues, 2 );

        $aValues = array();

        $aMyPreValues = $aPreValues[$sKey];
        if( !$aMyPreValues )
            return $aValues;

        foreach( $aMyPreValues as $sVal => $aVal ) {
            $sMyUseLKey = $sUseLKey;
            if( !isset( $aMyPreValues[$sVal][$sUseLKey] ) )
                $sMyUseLKey = 'LKey';

            $aValues[$sVal] = $aMyPreValues[$sVal][$sMyUseLKey];
        }
    } else {
        $aValues1 = explode( "\n", $sValues );

        $aValues = array();
        foreach( $aValues1 as $iKey => $sValue )
            $aValues[$sValue] = "_$sValue";
    }

    return $aValues;
}

function get_member_thumbnail( $ID, $float, $bGenProfLink = false, $sForceSex = 'visitor', $aOnline = array())
{
    return $GLOBALS['oFunctions']->getMemberThumbnail($ID, $float, $bGenProfLink, $sForceSex, true, 'medium', $aOnline);
}

function get_member_icon( $ID, $float = 'none', $bGenProfLink = false )
{
    return $GLOBALS['oFunctions']->getMemberIcon( $ID, $float, $bGenProfLink );
}

function MsgBox($sText, $iTimer = 0)
{
    return $GLOBALS['oFunctions'] -> msgBox($sText, $iTimer);
}
function LoadingBox($sName)
{
    return $GLOBALS['oFunctions'] -> loadingBox($sName);
}
function PopupBox($sName, $sTitle, $sContent, $aActions = array())
{
    return $GLOBALS['oFunctions'] -> popupBox($sName, $sTitle, $sContent, $aActions);
}
function getTemplateIcon( $sFileName )
{
    return $GLOBALS['oFunctions']->getTemplateIcon($sFileName);
}

function getTemplateImage( $sFileName )
{
    return $GLOBALS['oFunctions']->getTemplateImage($sFileName);
}

function getVersionComment()
{
    global $site;
    $aVer = explode( '.', $site['ver'] );

    // version output made for debug possibilities.
    // randomizing made for security issues. do not change it...
    $aVerR[0] = $aVer[0];
    $aVerR[1] = rand( 0, 100 );
    $aVerR[2] = $aVer[1];
    $aVerR[3] = rand( 0, 100 );
    $aVerR[4] = $site['build'];

    //remove leading zeros
    while( $aVerR[4][0] === '0' )
        $aVerR[4] = substr( $aVerR[4], 1 );

    return '<!-- ' . implode( ' ', $aVerR ) . ' -->';
}

// ----------------------------------- site statistick functions --------------------------------------//

function getSiteStatUser()
{
    global $aStat;
    $aStat = getSiteStatArray();

    $sCode  = '<div class="siteStatMain">';

    foreach($aStat as $aVal)
        $sCode .= $GLOBALS['oFunctions']->getSiteStatBody($aVal);

    $sCode .= '<div class="clear_both"></div></div>';

    return $sCode;
}

function genAjaxyPopupJS($iTargetID, $sDivID = 'ajaxy_popup_result_div', $sRedirect = '')
{
    $iProcessTime = 1000;

    if ($sRedirect)
       $sRedirect = "window.location = '$sRedirect';";

    $sJQueryJS = <<<EOF
<script type="text/javascript">

setTimeout( function(){
    $('#{$sDivID}_{$iTargetID}').show({$iProcessTime})
    setTimeout( function(){
        $('#{$sDivID}_{$iTargetID}').hide({$iProcessTime});
        $sRedirect
    }, 3000);
}, 500);

</script>
EOF;
    return $sJQueryJS;
}

function getBlockWidth ($iAllWidth, $iUnitWidth, $iNumElements)
{
    $iAllowed = $iNumElements * $iUnitWidth;
    if ($iAllowed > $iAllWidth) {
        $iMax = (int)floor($iAllWidth / $iUnitWidth);
        $iAllowed = $iMax*$iUnitWidth;
    }
    return $iAllowed;
}

function getMemberJoinFormCode($sParams = '')
{
	if(getParam('reg_by_inv_only') == 'on' && getID($_COOKIE['idFriend']) == 0)
		return MsgBox(_t('_registration by invitation only'));

    $sCodeBefore = '';
    $sCodeAfter = '';

	bx_import("BxDolJoinProcessor");
    $oJoin = new BxDolJoinProcessor();
    $sCode = $oJoin->process();

    bx_import('BxDolAlerts');
    $oAlert = new BxDolAlerts('profile', 'show_join_form', 0, 0, array('oJoin' => $oJoin, 'sParams' => &$sParams, 'sCustomHtmlBefore' => &$sCodeBefore, 'sCustomHtmlAfter' => &$sCodeAfter, 'sCode' => &$sCode));
    $oAlert->alert();

    $sAuthCode = getMemberAuthCode('_sys_auth_join_with');

    $sAction = 'join';
    return $GLOBALS['oSysTemplate']->parseHtmlByName('login_join_form.html', array(
    	'action' => $sAction,
    	'bx_if:show_auth' => array(
    		'condition' => !empty($sAuthCode),
    		'content' => array(
    			'auth' => $sAuthCode
    		)
    	),
    	'custom_code_before' => $sCodeBefore,
    	'form' => $sCode,
    	'custom_code_after' => $sCodeAfter,
    	'bx_if:show_text' => array(
    		'condition' => false,
    		'content' => array(
    			'action' => $sAction,
    			'text' => _t('_join_form_note', BX_DOL_URL_ROOT)
    		)
    	)
    ));
}

function getMemberLoginFormCode($sID = 'member_login_form', $sParams = '')
{
    $aForm = array(
        'form_attrs' => array(
            'id' => $sID,
            'action' => BX_DOL_URL_ROOT . 'member.php',
            'method' => 'post',
            'onsubmit' => "validateLoginForm(this); return false;",
        ),
        'inputs' => array(
            'nickname' => array(
                'type' => 'text',
                'name' => 'ID',
                'caption' => _t('_NickName'),
            ),
            'password' => array(
                'type' => 'password',
                'name' => 'Password',
                'caption' => _t('_Password'),
            ),
            'rememberme' => array(
                'type' => 'hidden',
                'name' => 'rememberMe',
            	'value' => 'on',
            ),
            'relocate' => array(
                'type' => 'hidden',
                'name' => 'relocate',
                'value'=> isset($_REQUEST['relocate']) ? $_REQUEST['relocate'] : BX_DOL_URL_ROOT . 'member.php',
            ),
            'LogIn' => array(
				'type' => 'submit',
				'name' => 'LogIn',
				'caption' => '',
				'value' => _t('_Login'),
			),
			'forgot' => array(
				'type' => 'custom',
				'colspan' => '2',
				'tr_attrs' => array(
					'class' => 'bx-form-element-forgot'
				),
                'content' => '<a href="' . BX_DOL_URL_ROOT . 'forgot.php">' . _t('_forgot_your_password') . '?</a>',
			)
        ),
    );

    $oForm = new BxTemplFormView($aForm);

    bx_import('BxDolAlerts');
    $sCustomHtmlBefore = '';
    $sCustomHtmlAfter = '';
    $oAlert = new BxDolAlerts('profile', 'show_login_form', 0, 0, array('oForm' => $oForm, 'sParams' => &$sParams, 'sCustomHtmlBefore' => &$sCustomHtmlBefore, 'sCustomHtmlAfter' => &$sCustomHtmlAfter));
    $oAlert->alert();

    $sAuthCode = getMemberAuthCode('_sys_auth_login_with');

    $sAction = 'login';
    return $GLOBALS['oSysTemplate']->parseHtmlByName('login_join_form.html', array(
    	'action' => $sAction,
    	'bx_if:show_auth' => array(
    		'condition' => !empty($sAuthCode) && false === strpos($sParams, 'disable_external_auth'),
    		'content' => array(
    			'auth' => $sAuthCode
    		)
    	),
    	'custom_code_before' => $sCustomHtmlBefore,
    	'form' => $oForm->getCode(),
    	'custom_code_after' => $sCustomHtmlAfter,
    	'bx_if:show_text' => array(
    		'condition' => strpos($sParams, 'no_join_text') === false,
    		'content' => array(
    			'action' => $sAction,
    			'text' => _t('_login_form_description2join', BX_DOL_URL_ROOT)
    		)
    	)
    ));
}

function getMemberAuthCode($sTitleKey = '')
{
    $aAuthTypes = $GLOBALS['MySQL']-> fromCache('sys_objects_auths', 'getAll', 'SELECT * FROM `sys_objects_auths`');
    if(empty($aAuthTypes) || !is_array($aAuthTypes))
    	return '';

	$aTmplButtons = array();
	foreach($aAuthTypes as $iKey => $aItems) {
		$sTitle = _t($aItems['Title']);

		$aTmplButtons[] = array(
			'href' => !empty($aItems['Link']) ? BX_DOL_URL_ROOT . $aItems['Link'] : 'javascript:void(0)',
			'bx_if:show_onclick' => array(
				'condition' => !empty($aItems['OnClick']),
				'content' => array(
					'onclick' => 'javascript:' . $aItems['OnClick']
				)
			),
			'bx_if:show_icon' => array(
				'condition' => !empty($aItems['Icon']),
				'content' => array(
					'icon' => $aItems['Icon']
				)
			),
			'title' => !empty($sTitleKey) ? _t($sTitleKey, $sTitle) : $sTitle
		);
	}

	return $GLOBALS['oSysTemplate']->parseHtmlByName('login_join_auth.html', array(
		'bx_repeat:buttons' => $aTmplButtons
	));
}

bx_import('BxDolAlerts');
$oZ = new BxDolAlerts('system', 'design_included', 0);
$oZ->alert();
