<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once("BxPmtProvider.php");

define('PP_MODE_LIVE', 1);
define('PP_MODE_TEST', 2);

define('PP_PRC_TYPE_DIRECT', 1);
define('PP_PRC_TYPE_PDT', 2);
define('PP_PRC_TYPE_IPN', 3);

class BxPmtPayPal extends BxPmtProvider
{
    var $_sDataReturnUrl;

    /**
     * Constructor
     */
    function __construct($oDb, $oConfig, $aConfig)
    {
        parent::__construct($oDb, $oConfig, $aConfig);
        $this->_bRedirectOnResult = false;

        $this->_sDataReturnUrl = $this->_oConfig->getDataReturnUrl() . $this->_sName . '/';
    }
    function initializeCheckout($iPendingId, $aCartInfo, $bRecurring = false, $iRecurringDays = 0)
    {
        $iMode = (int)$this->getOption('mode');
        $sActionURL = $iMode == PP_MODE_LIVE ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';

        if($bRecurring)
            $aFormData = array(
                'cmd' => '_xclick-subscriptions',
                'a3' => sprintf("%.2f", (float)$aCartInfo['items_price']),
                'p3' => $iRecurringDays,
                't3' => 'D',
                'src' => '1', // repeat billings unles member cancels subscription
                'sra' => '1' // reattempt on failure
            );
        else
            $aFormData = array(
                'cmd' => '_xclick',
                'amount' => sprintf( "%.2f", (float)$aCartInfo['items_price'])
            );

        $aFormData = array_merge($aFormData, array(
            'business' => $iMode == PP_MODE_LIVE ? $this->getOption('business') : $this->getOption('sandbox'),
            'bn' => 'Boonex_SP',
            'item_name' => _t('_payment_txt_payment_to', $aCartInfo['vendor_username']),
            'item_number' => $iPendingId,
            'currency_code' => $aCartInfo['vendor_currency_code'],
            'no_note' => '1',
            'no_shipping' => '1',
            'custom' => md5($aCartInfo['vendor_id'] . $iPendingId)
        ));

        $iIndex = 1;
        foreach($aCartInfo['items'] as $aItem)
            $aFormData['item_name'] .= ' ' . ($iIndex++) . '. ' . $aItem['title'];

        switch($this->getOption('prc_type')) {
            case PP_PRC_TYPE_PDT:
            case PP_PRC_TYPE_DIRECT:
                $aFormData = array_merge($aFormData, array(
                    'return' => $this->_sDataReturnUrl . $aCartInfo['vendor_id'],
                    'rm' => '2'
                ));
                break;
            case PP_PRC_TYPE_IPN:
                $aFormData = array_merge($aFormData, array(
                    'return' => $this->_oConfig->getReturnUrl(),
                    'notify_url' => $this->_sDataReturnUrl . $aCartInfo['vendor_id'],
                    'rm' => '1'
                ));
                break;
        }

        Redirect($sActionURL, $aFormData, 'post', $this->_sCaption);
        exit();
    }
    function finalizeCheckout(&$aData)
    {
        if($aData['txn_type'] == 'web_accept' || isset($aData['tx']))
            return $this->_registerCheckout($aData);

        return array('code' => 2, 'message' => _t('_payment_pp_err_no_data_given'));
    }

    /**
     *
     * @param $aData - data from payment provider.
     * @param $bSubscription - Is not needed. May be used in the future for subscriptions.
     * @param $iPendingId - Is not needed. May be used in the future for subscriptions.
     * @return array with results.
     */
    function _registerCheckout(&$aData, $bSubscription = false, $iPendingId = 0)
    {
        if(empty($this->_aOptions) && isset($aData['item_number']))
            $this->_aOptions = $this->getOptionsByPending($aData['item_number']);

        if(empty($this->_aOptions))
            return array('code' => -1, 'message' => _t('_payment_pp_err_no_vendor_given'));

        $iPrcType = (int)$this->getOption('prc_type');
        if(($iPrcType == PP_PRC_TYPE_IPN || $iPrcType == PP_PRC_TYPE_DIRECT) && (!isset($aData['item_number']) || !isset($aData['txn_id'])))
            return array('code' => 2, 'message' => _t('_payment_pp_err_no_data_given'));
        else if($iPrcType == PP_PRC_TYPE_PDT && !isset($aData['tx']))
            return array('code' => 2, 'message' => _t('_payment_pp_err_no_data_given'));

        $aResult = $this->_validateCheckout($aData);

        if(!$bSubscription || empty($iPendingId))
            $iPendingId = (int)$aData['item_number'];

        $aPending = $this->_oDb->getPending(array('type' => 'id', 'id' => $iPendingId));
        if(!empty($aPending['order']) || !empty($aPending['error_code']) || !empty($aPending['error_msg']) || (int)$aPending['processed'] != 0)
            return array('code' => -1, 'message' => _t('_payment_pp_err_already_processed'));

        //--- Update pending transaction ---//
        $this->_oDb->updatePending($iPendingId, array(
            'order' => $aData['txn_id'],
            'error_code' => $aResult['code'],
            'error_msg' => $aResult['message']
        ));

        $sBuyerFirstName = process_db_input($aData['first_name'], BX_TAGS_STRIP);
        $sBuyerLastName = process_db_input($aData['last_name'], BX_TAGS_STRIP);
        $sBuyerEmail = process_db_input($aData['payer_email'], BX_TAGS_STRIP);

        $aResult['pending_id'] = $iPendingId;
        $aResult['payer_name'] = _t('_payment_txt_buyer_name_mask', $sBuyerFirstName, $sBuyerLastName); 
		$aResult['payer_email'] = $sBuyerEmail;
        return $aResult;
    }

    function _validateCheckout(&$aData)
    {
        $iMode = (int)$this->getOption('mode');
        if($iMode == PP_MODE_LIVE) {
           $sBusiness = $this->getOption('business');
           $sConnectionUrl = 'www.paypal.com';
        } else {
            $sBusiness = $this->getOption('sandbox');
            $sConnectionUrl = 'www.sandbox.paypal.com';
        }

        $iPrcType = $this->getOption('prc_type');
        if($iPrcType == PP_PRC_TYPE_DIRECT || $iPrcType == PP_PRC_TYPE_IPN) {
            if($aData['payment_status'] != 'Completed' )
                return array('code' => 0, 'message' => _t('_payment_pp_err_not_completed'));

            if($aData['business'] != $sBusiness)
                return array('code' => -1, 'message' => _t('_payment_pp_err_wrong_business'));

            $sRequest = 'cmd=_notify-validate';
            foreach($aData as $sKey => $sValue) {
                if(in_array($sKey, array('cmd')))
                    continue;

                $sRequest .= '&' . urlencode($sKey) . '=' . urlencode( process_pass_data($sValue));
            }

            $aResponse = $this->_readValidationData($sConnectionUrl, $sRequest);
            if((int)$aResponse['code'] !== 0)
               return $aResponse;

            array_walk($aResponse['content'], function(&$arg) {
                $arg = trim($arg);  
            });
            if(strcmp($aResponse['content'][0], "INVALID") === 0)
                return array('code' => -1, 'message' => _t('_payment_pp_err_wrong_transaction'));
            else if(strcmp($aResponse['content'][0], "VERIFIED") !== 0)
                return array('code' => 2, 'message' => _t('_payment_pp_err_wrong_verification_status'));
        }
        else if($iPrcType == PP_PRC_TYPE_PDT) {
            $sRequest = "cmd=_notify-synch&tx=" . $aData['tx'] . "&at=" . $this->getOption('token');
            $aResponse = $this->_readValidationData($sConnectionUrl, $sRequest);

            if((int)$aResponse['code'] !== 0)
               return $aResponse;

            if(strcmp($aResponse['content'][0], "FAIL") === 0)
                return array('code' => -1, 'message' => _t('_payment_pp_err_wrong_transaction'));
            else if(strcmp($aResponse['content'][0], "SUCCESS") !== 0)
                return array('code' => 2, 'message' => _t('_payment_pp_err_wrong_verification_status'));

            $aKeys = array();
            foreach($aResponse['content'] as $sLine) {
                list($sKey, $sValue) = explode("=", $sLine);
                $aKeys[urldecode($sKey)] = urldecode($sValue);
            }

            $aData = array_merge($aData, $aKeys);

            if($aData['payment_status'] != 'Completed' )
                return array('code' => 0, 'message' => _t('_payment_pp_err_not_completed'));

            if($aData['business'] != $sBusiness)
                return array('code' => -1, 'message' => _t('_payment_pp_err_wrong_business'));
        }

        $aPending = $this->_oDb->getPending(array('type' => 'id', 'id' => $aData['item_number']));
        $aVendor = $this->_oDb->getVendorInfoProfile($aPending['seller_id']);
        $fAmount = (float)$this->_getReceivedAmount($aVendor['currency_code'], $aData);
        if($fAmount != (float)$aPending['amount'])
            return array('code' => -1, 'message' => _t('_payment_pp_err_wrong_amount'));

        if($aData['custom'] != md5($aPending['seller_id'] . $aPending['id']))
            return array('code' => -1, 'message' => _t('_payment_pp_err_wrong_custom_data'));

        return array('code' => 1, 'message' => _t('_payment_pp_msg_verified'));
    }

    function _readValidationData($sConnectionUrl, $sRequest)
    {
        $rConnect = curl_init('https://' . $sConnectionUrl . '/cgi-bin/webscr');
		curl_setopt($rConnect, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($rConnect, CURLOPT_POST, 1);
		curl_setopt($rConnect, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($rConnect, CURLOPT_POSTFIELDS, $sRequest);
		curl_setopt($rConnect, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($rConnect, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($rConnect, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($rConnect, CURLOPT_HTTPHEADER, array('Connection: Close'));

		$sResponse = curl_exec($rConnect);
    	if(curl_errno($rConnect) == 60) { // CURLE_SSL_CACERT
            curl_setopt($rConnect, CURLOPT_CAINFO, BX_DIRECTORY_PATH_PLUGINS . 'curl/cacert.pem');
            $sResponse = curl_exec($rConnect);
        }

        curl_close($rConnect);
		if(!$sResponse)
			return array('code' => 6, 'message' => $this->_sLangsPrefix . 'err_cannot_validate');

		return array('code' => 0, 'content' => explode("\n", $sResponse));
    }

    function _getReceivedAmount($sCurrencyCode, &$aResultData)
    {
        $fAmount = 0.00;
        $fTax = isset($aResultData['tax']) ? (float)$aResultData['tax'] : 0.00;

        if($aResultData['mc_currency'] == $sCurrencyCode && isset($aResultData['payment_gross']) && !empty($aResultData['payment_gross']))
            $fAmount = (float)$aResultData['payment_gross'] - $fTax;
        else if($aResultData['mc_currency'] == $sCurrencyCode && isset($aResultData['mc_gross']) && !empty($aResultData['mc_gross']))
            $fAmount = (float)$aResultData['mc_gross'] - $fTax;
        else if($aResultData['settle_currency'] == $sCurrencyCode && isset($aResultData['settle_amount']) && !empty($aResultData['settle_amount']))
            $fAmount = (float)$aResultData['settle_amount'] - $fTax;

        return $fAmount;
    }
}
