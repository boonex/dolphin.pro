<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolProfileFields');

/**
 * Base send broadcast message class for modules like events/groups/store
 */
class BxDolTwigFormBroadcast extends BxTemplFormView
{

    function __construct ($sCaptionMsgTitle, $sErrMsgTitle, $sCaptionMsgBody, $sErrMsgBory)
    {
        $aCustomForm = array(

            'form_attrs' => array(
                'name'     => 'form_broadcast',
                'action'   => '',
                'method'   => 'post',
            ),

            'params' => array (
                'db' => array(
                    'submit_name' => 'submit_form',
                ),
            ),

            'inputs' => array(
                'title' => array(
                    'type' => 'text',
                    'name' => 'title',
                    'caption' => $sCaptionMsgTitle,
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,100),
                        'error' => $sErrMsgTitle,
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'message' => array(
                    'type' => 'textarea',
                    'name' => 'message',
                    'caption' => $sCaptionMsgBody,
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(10,64000),
                        'error' => $sErrMsgBory,
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),

                'Submit' => array (
                    'type' => 'submit',
                    'name' => 'submit_form',
                    'value' => _t('_Submit'),
                ),
            ),
        );

        parent::__construct ($aCustomForm);
    }
}
