<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxPmtDetailsFormCheckerHelper extends BxDolFormCheckerHelper
{
	function checkHttps ($s)
    {
        return empty($s) || substr(BX_DOL_URL_ROOT, 0, 5) == 'https';
    }
}

class BxPmtDetails
{
    var $_oDb;
    var $_oConfig;
    var $_aForm;
    var $_bCollapseFirst;

    /*
     * Constructor.
     */
    function __construct(&$oDb, &$oConfig)
    {
        $this->_oDb = &$oDb;
        $this->_oConfig = &$oConfig;

        $this->_aForm = array(
            'form_attrs' => array(
                'id' => 'pmt_details',
                'name' => 'pmt_details',
                'action' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'details/',
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'params' => array(
                'db' => array(
                    'table' => '',
                    'key' => 'id',
                    'uri' => '',
                    'uri_title' => '',
                    'submit_name' => 'submit'
                ),
                'checker_helper' => 'BxPmtDetailsFormCheckerHelper'
            ),
            'inputs' => array (
            )
        );

        $this->_bCollapseFirst = true;
    }

    function getForm($iUserId)
    {
        $aInputs = $this->_oDb->getForm();
        if(empty($aInputs))
            return '';

		$sLangsPrefix = $this->_oConfig->getLangsPrefix();

        if($iUserId == BX_PMT_ADMINISTRATOR_ID)
            $this->_aForm['form_attrs']['action'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'admin/';

        $bCollapsed = $this->_bCollapseFirst;
        $iProviderId = 0;
        $sProviderName = "";
        $aUserValues = $this->_oDb->getFormData($iUserId);
        foreach($aInputs as $aInput) {
            $sReturnDataUrl = $this->_oConfig->getDataReturnUrl() . $sProviderName . '/' . $iUserId;

            if($iProviderId != $aInput['provider_id']) {
                if(!empty($iProviderId))
                    $this->_aForm['inputs']['provider_' . $iProviderId . '_end'] = array(
                        'type' => 'block_end'
                    );

                $this->_aForm['inputs']['provider_' . $aInput['provider_id'] . '_begin'] = array(
                    'type' => 'block_header',
                    'caption' => _t($aInput['provider_caption']),
                    'collapsable' => true,
                    'collapsed' => $bCollapsed
                );

                $iProviderId = $aInput['provider_id'];
                $sProviderName = $aInput['provider_name'];
                $bCollapsed = true;
            }

            $this->_aForm['inputs'][$aInput['name']] = array(
				'type' => $aInput['type'],
                'name' => $aInput['name'],
                'caption' => _t($aInput['caption']),
                'value' => $aUserValues[$aInput['id']]['value'],
                'info' => _t($aInput['description']),
            	'attrs' => array(
            		'bx-data-provider' => $iProviderId
            	),
                'checker' => array (
                    'func' => $aInput['check_type'],
                    'params' => $aInput['check_params'],
                    'error' => _t($aInput['check_error']),
                )
            );

            //--- Make some field dependent actions ---//
            switch($aInput['type']) {
                case 'select':
                    if(empty($aInput['extra']))
                       break;

                    $aAddon = array('values' => array());

                    $aPairs = explode(',', $aInput['extra']);
                    foreach($aPairs as $sPair) {
                        $aPair = explode('|', $sPair);
                        $aAddon['values'][] = array('key' => $aPair[0], 'value' => _t($aPair[1]));
                    }
                    break;

                case 'checkbox':
                    $this->_aForm['inputs'][$aInput['name']]['value'] = 'on';
                    $aAddon = array('checked' => $aUserValues[$aInput['id']]['value'] == 'on' ? true : false);
                    break;

				 case 'value':
				 	if(str_replace($aInput['provider_option_prefix'], '', $aInput['name']) == 'return_url')
				 		$this->_aForm['inputs'][$aInput['name']]['value'] = $sReturnDataUrl;
				 	break;
            }

            if(!empty($aAddon) && is_array($aAddon))
                $this->_aForm['inputs'][$aInput['name']] = array_merge($this->_aForm['inputs'][$aInput['name']], $aAddon);
        }

        $this->_aForm['inputs']['provider_' . $iProviderId . '_end'] = array(
            'type' => 'block_end'
        );
        $this->_aForm['inputs']['submit'] = array(
            'type' => 'submit',
            'name' => 'submit',
            'value' => _t($sLangsPrefix . 'details_submit'),
        );

        bx_import('BxTemplFormView');
        $oForm = new BxTemplFormView($this->_aForm);
        $oForm->initChecker();

        if($oForm->isSubmittedAndValid()) {
            $aOptions = $this->_oDb->getOptions();
            foreach($aOptions as $aOption)
                $this->_oDb->updateOption($iUserId, $aOption['id'], process_db_input(isset($_POST[$aOption['name']]) ? $_POST[$aOption['name']] : "", BX_TAGS_STRIP));

            header('Location: ' . $oForm->aFormAttrs['action']);
        }
        else {
        	foreach($oForm->aInputs as $aInput)
        		if(!empty($aInput['error'])) {
        			$iProviderId = (int)$aInput['attrs']['bx-data-provider'];
        			$oForm->aInputs['provider_' . $iProviderId . '_begin']['collapsed'] = false;
        		}

			return $oForm->getCode();
        }
    }

	function getFormBlock($iUserId = -1)
    {
    	$sLangsPrefix = $this->_oConfig->getLangsPrefix();
    	$sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();

        if(!isLogged())
			return MsgBox(_t($sLangsPrefix . 'err_required_login'));

        $aTopMenu = array(
            'pmt-orders-processed-lnk' => array('href' => $sBaseUrl . 'orders/processed/', 'title' => _t($sLangsPrefix . 'btn_orders_processed')),
            'pmt-orders-pending-lnk' => array('href' => $sBaseUrl . 'orders/pending/', 'title' => _t($sLangsPrefix . 'btn_orders_pending')),
            'pmt-payment-settings-lnk' => array('href' =>  $sBaseUrl . 'details/', 'title' => _t($sLangsPrefix . 'btn_settings'), 'active' => 1)
        );

		$sResult = $this->getForm(is_numeric($iUserId) && $iUserId != -1 ? $iUserId : getLoggedId());
        return array((!empty($sResult) ? $sResult : MsgBox(_t($sLangsPrefix . 'msg_no_results'))), $aTopMenu, array(), false, 'getBlockCaptionMenu');
    }
}
