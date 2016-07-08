<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolProfileFields');

class BxStoreFormSearch extends BxTemplFormView
{
    function __construct ()
    {
        bx_import('BxDolCategories');
        $oCategories = new BxDolCategories();
        $oCategories->getTagObjectConfig ();
        $aCategories = $oCategories->getCategoriesList('bx_store', (int)$iProfileId, true);

        $aCustomForm = array(

            'form_attrs' => array(
                'name'     => 'form_search_store',
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
                    'caption' => _t('_bx_store_form_caption_keyword'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,100),
                        'error' => _t ('_bx_store_form_err_keyword'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'Category' => array(
                    'type' => 'select_box',
                    'name' => 'Category',
                    'caption' => _t('_bx_store_form_caption_category'),
                    'values' => $aCategories,
                    'required' => true,
                    'checker' => array (
                        'func' => 'avail',
                        'error' => _t ('_bx_store_form_err_category'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
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
