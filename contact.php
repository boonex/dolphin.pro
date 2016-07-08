<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'utils.inc.php' );

// --------------- page variables and login

$_page['name_index'] 	= 16;

check_logged();

$_page['header'] = _t( "_CONTACT_H" );
$_page['header_text'] = _t( "_CONTACT_H1" );

// --------------- page components

$showForm = getParam('enable_contact_form') == 'on' ? true : false ;

$_ni = $_page['name_index'];

if( $showForm ) {
    $_page_cont[$_ni]['page_main_code'] = PageCompPageMainCodeWithForm();
} else {
    $_page_cont[$_ni]['page_main_code'] = PageCompPageMainCode();
}

// --------------- [END] page components

PageCode();

// --------------- page components functions

/**
 * page code function
 */
function PageCompPageMainCode()
{
    global $oTemplConfig;
    return DesignBoxContent( _t('_CONTACT_H1'), MsgBox(_t('_CONTACT')), $oTemplConfig->PageCompThird_db_num);
}

function PageCompPageMainCodeWithForm()
{
    global $oTemplConfig, $site;

    $aForm = array(
        'form_attrs' => array(
            'id' => 'post_us_form',
            'action' => BX_DOL_URL_ROOT . 'contact.php',
            'method' => 'post',
        ),
        'params' => array (
            'db' => array(
                'submit_name' => 'do_submit',
            ),
        ),
        'inputs' => array(
            'name' => array(
                'type' => 'text',
                'name' => 'name',
                'caption' => _t('_Your name'),
                'required' => true,
                'checker' => array(
                    'func' => 'length',
                    'params' => array(1, 150),
                    'error' => _t( '_Name is required' )
                ),
            ),
            'email' => array(
                'type' => 'text',
                'name' => 'email',
                'caption' => _t('_Your email'),
                'required' => true,
                'checker' => array(
                    'func' => 'email',
                    'error' => _t( '_Incorrect Email' )
                ),
            ),
            'message_subject' => array(
                'type' => 'text',
                'name' => 'subject',
                'caption' => _t('_message_subject'),
                'required' => true,
                'checker' => array(
                    'func' => 'length',
                    'params' => array(5, 300),
                    'error' => _t( '_ps_ferr_incorrect_length' )
                ),
            ),
            'message_text' => array(
                'type' => 'textarea',
                'name' => 'body',
                'caption' => _t('_Message text'),
                'required' => true,
                'checker' => array(
                    'func' => 'length',
                    'params' => array(10, 5000),
                    'error' => _t( '_ps_ferr_incorrect_length' )
                ),
            ),
            'captcha' => array(
                'type' => 'captcha',
                'caption' => _t('_Enter what you see'),
                'name' => 'securityImageValue',
                'required' => true,
                'checker' => array(
                    'func' => 'captcha',
                    'error' => _t( '_Incorrect Captcha' ),
                ),
            ),
            'submit' => array(
                'type' => 'submit',
                'name' => 'do_submit',
                'value' => _t('_Submit'),
            ),
        ),
    );

    $oForm = new BxTemplFormView($aForm);
    $sForm = $oForm->getCode();
    $oForm->initChecker();
    if ( $oForm->isSubmittedAndValid() ) {
        $sSenderName	= process_pass_data($_POST['name'], BX_TAGS_STRIP);
        $sSenderEmail	= process_pass_data($_POST['email'], BX_TAGS_STRIP);
        $sLetterSubject = process_pass_data($_POST['subject'], BX_TAGS_STRIP);
        $sLetterBody	= process_pass_data($_POST['body'], BX_TAGS_STRIP);

        $sLetterBody = $sLetterBody . "\r\n" . '============' . "\r\n" . _t('_from') . ' ' . $sSenderName . "\r\n" . 'with email ' .  $sSenderEmail;

        if (sendMail($site['email'], $sLetterSubject, $sLetterBody)) {
            $sActionKey = '_ADM_PROFILE_SEND_MSG';
        } else {
            $sActionKey = '_Email sent failed';
        }
        $sActionText = MsgBox(_t($sActionKey));
        $sForm = $sActionText . $sForm;
    }
    else
        $sForm = $oForm->getCode();
    return DesignBoxContent(_t('_CONTACT_H1'), $sForm, $oTemplConfig->PageCompThird_db_num);
}
