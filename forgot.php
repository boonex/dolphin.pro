<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );
bx_import( 'BxDolEmailTemplates' );
bx_import( 'BxTemplFormView' );

class BxDolForgotCheckerHelper extends BxDolFormCheckerHelper
{
    function checkEmail($s)
    {
        if (!preg_match("/(([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?/", $s))
            return false;

        $iID = (int)db_value( "SELECT `ID` FROM `Profiles` WHERE `Email` = '$s'" );
        if (!$iID)
            return _t( '_MEMBER_NOT_RECOGNIZED', $site['title'] );

        return true;
    }
}

// --------------- page variables and login

$_page['name_index'] 	= 1;

$logged['member'] = member_auth( 0, false );

$_page['header'] = _t( "_Forgot password?" );
$_page['header_text'] = _t( "_Password retrieval", $site['title'] );

// --------------- page components

$_ni = $_page['name_index'];

$aForm = array(
    'form_attrs' => array(
        'name'     => 'forgot_form',
        'action'   => BX_DOL_URL_ROOT . 'forgot.php',
        'method'   => 'post',
    ),
    'params' => array (
        'db' => array(
            'submit_name' => 'do_submit',
        ),
        'checker_helper' => 'BxDolForgotCheckerHelper',
    ),
    'inputs' => array(
        array(
            'type' => 'email',
            'name' => 'Email',
            'caption' => _t('_My Email'),
            'value' => isset($_POST['Email']) ? $_POST['Email'] : '',
            'required' => true,
            'checker' => array(
                'func' => 'email',
                'error' => _t( '_Incorrect Email' )
            ),
        ),
        array(
            'type' => 'captcha',
            'name' => 'captcha',
            'caption' => _t('_Enter Captcha'),
            'required' => true,
            'checker' => array(
                'func' => 'captcha',
                'error' => _t( '_Incorrect Captcha' ),
            ),
        ),
        array(
            'type' => 'submit',
            'name' => 'do_submit',
            'value' => _t( "_Retrieve my information" ),
        ),
    )
);

$oForm = new BxTemplFormView($aForm);
$oForm->initChecker();

if ( $oForm->isSubmittedAndValid() ) {
    // Check if entered email is in the base
    $sEmail = process_db_input($_POST['Email'], BX_TAGS_STRIP);
    $memb_arr = db_arr( "SELECT `ID` FROM `Profiles` WHERE `Email` = '$sEmail'" );

    $recipient = $sEmail;

    $rEmailTemplate = new BxDolEmailTemplates();
    $aTemplate = $rEmailTemplate -> getTemplate( 't_Forgot', $memb_arr['ID'] ) ;

    $aPlus['Password'] = generateUserNewPwd($memb_arr['ID']);
    $aProfile = getProfileInfo($memb_arr['ID']);
    $mail_ret = sendMail( $recipient, $aTemplate['Subject'], $aTemplate['Body'], $memb_arr['ID'], $aPlus, 'html', false, true );

    // create system event
    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');
    $oZ = new BxDolAlerts('profile', 'password_restore',  $memb_arr['ID']);
      $oZ->alert();

    $_page['header'] = _t( "_Recognized" );
    $_page['header_text'] = _t( "_RECOGNIZED", $site['title'] );

    if ($mail_ret)
        $action_result = _t( "_MEMBER_RECOGNIZED_MAIL_SENT", $site['url'], $site['title'] );
    else
        $action_result = _t( "_MEMBER_RECOGNIZED_MAIL_NOT_SENT", $site['title'] );

    $sForm = '';
} else {
    $action_result = _t( "_FORGOT", $site['title'] );
    $sForm = $oForm->getCode();
}

$sPageCode = <<<BLAH
            <div class="bx-def-margin-sec-bottom bx-def-font-large">
                $action_result
            </div>
            $sForm
BLAH;

$_page_cont[$_ni]['page_main_code'] = DesignBoxContent($_page['header_text'], $sPageCode, 11);

// --------------- [END] page components

PageCode();

// --------------- page components functions

function generateUserNewPwd($ID)
{
    $sPwd = genRndPwd();
    $sSalt = genRndSalt();

    $sQuery = "
        UPDATE `Profiles`
        SET
            `Password` = '" . encryptUserPwd($sPwd, $sSalt) . "',
            `Salt` = '$sSalt'
        WHERE
            `ID`= ?
    ";

    db_res($sQuery, [$ID]);
    createUserDataFile($ID);

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');
    $oZ = new BxDolAlerts('profile', 'edit', $ID);
    $oZ->alert();
    return $sPwd;
}
