<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolProfileFields.php');

class BxEventsFormSearch extends BxTemplFormView
{
    function __construct ()
    {
        $oProfileFields = new BxDolProfileFields(0);
        $aCountries = $oProfileFields->convertValues4Input('#!Country');
        $aCountries = array_merge (array('' => _t('_bx_events_all_countries')), $aCountries);

        $aCustomForm = array(

            'form_attrs' => array(
                'name'     => 'form_search_events',
                'action'   => '',
                'method'   => 'get',
            ),

            'params' => array (
                'db' => array(
                    'submit_name' => 'submit_form',
                ),
                'csrf' => array(
                    'disable' => true,
                ),
            ),

            'inputs' => array(
                'Keyword' => array(
                    'type' => 'text',
                    'name' => 'Keyword',
                    'caption' => _t('_bx_events_caption_keyword'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,100),
                        'error' => _t ('_bx_events_err_keyword'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'Country' => array(
                    'type' => 'select_box',
                    'name' => 'Country',
                    'caption' => _t('_bx_events_caption_country'),
                    'values' => $aCountries,
                    'required' => true,
                    'checker' => array (
                        'func' => 'preg',
                        'params' => array('/^[a-zA-Z]{0,2}$/'),
                        'error' => _t ('_bx_events_err_country'),
                    ),
                    'db' => array (
                        'pass' => 'Preg',
                        'params' => array('/([a-zA-Z]{0,2})/'),
                    ),
                ),
                'Submit' => array (
                    'type' => 'submit',
                    'name' => 'submit_form',
                    'value' => _t('_Submit'),
                    'colspan' => true,
                ),
            ),
        );

        parent::__construct ($aCustomForm);
    }
}
