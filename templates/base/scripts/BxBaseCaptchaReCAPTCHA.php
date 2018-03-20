<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolCaptcha');

/**
 * reCAPTCHA representation.
 * @see BxDolCaptcha
 */
class BxBaseCaptchaReCAPTCHA extends BxDolCaptcha
{
    protected $_bJsCssAdded = false;
    protected $_oTemplate;

    protected $_sSkin;
	protected $_sApiUrl;
	protected $_sVerifyUrl;
    protected $_sKeyPublic;
    protected $_sKeyPrivate;

    protected $_error = null;

    public function __construct ($aObject, $oTemplate)
    {
        parent::__construct ($aObject, $oTemplate);

        if ($oTemplate)
            $this->_oTemplate = $oTemplate;
        else
            $this->_oTemplate = $GLOBALS['oSysTemplate'];

        $this->_sSkin = 'light';
        $this->_sApiUrl = 'https://www.google.com/recaptcha/api.js';
        $this->_sVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $this->_sKeyPublic = getParam('sys_recaptcha_key_public');
        $this->_sKeyPrivate = getParam('sys_recaptcha_key_private');
    }

    /**
     * Display captcha.
     */
    public function display ($bDynamicMode = false)
    {
        $sCode = '';
        $aApiParams = array();
        if($bDynamicMode)  {
        	$sPostfix = $this->_sObject;
        	$sId = 'sys-captcha-' . $sPostfix;

        	$sOnLoadFunction = 'onLoadCallback' . $sPostfix;
        	$sOnLoadCode = "
	        	var " . $sOnLoadFunction . " = function() {
					grecaptcha.render('" . $sId . "', {
						'sitekey': '" . $this->_sKeyPublic . "',
						'theme': '" . $this->_sSkin . "'
					});
				};
		        ";

        	$aApiParams = array(
        		'onload' => $sOnLoadFunction,
        		'render' => 'explicit'
        	);

        	$sCode .= $this->_oTemplate->_wrapInTagJsCode($sOnLoadCode);
        	$sCode .= '<div id="' . $sId . '"></div>';
        }
        else {
        	$aApiParams = array(
        		'render' => 'onload'
        	);

        	$sCode .= '<div class="g-recaptcha" data-sitekey="' . $this->_sKeyPublic . '" data-theme="' . $this->_sSkin . '" style="max-width:200px;"></div>';
        }

        $aApiParams['hl'] = getCurrentLangName(false);
        $sCodeJs = $this->_oTemplate->addJs(bx_append_url_params($this->_sApiUrl, $aApiParams), $bDynamicMode);

        return ($bDynamicMode ? $sCodeJs : '') . $sCode;
    }

    /**
     * Check captcha.
     */
    public function check ()
    {
    	$mixedResponce = bx_file_get_contents($this->_sVerifyUrl, array(
    		'secret' => $this->_sKeyPrivate, 
    		'response' => process_pass_data(bx_get('g-recaptcha-response')),
    		'remoteip' => getVisitorIP()
    	));
    	if($mixedResponce === false)
    		return false;

    	$aResponce = json_decode($mixedResponce, true); 	
    	if(isset($aResponce['success']) && $aResponce['success'] === true)
    		return true;

		if(!empty($aResponce['error-codes']))
			$this->_error = $aResponce['error-codes'];

		return false;
    }

	/**
     * Return text entered by user
     */
    public function getUserResponse ()
    {
        return process_pass_data(bx_get('g-recaptcha-response'));
    }

    /**
     * Check if captcha is available, like all API keys are specified.
     */
    public function isAvailable ()
    {
        return !empty($this->_sKeyPublic) && !empty($this->_sKeyPrivate);
    }
}

/** @} */
