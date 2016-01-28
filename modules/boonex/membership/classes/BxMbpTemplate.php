<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleTemplate');

class BxMbpTemplate extends BxDolModuleTemplate
{
    /**
     * Constructor
     */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    function displayCurrentLevel($aUserLevel)
    {
        $aLevelInfo = $this->_oDb->getMembershipsBy(array('type' => 'level_id', 'id' => $aUserLevel['ID']));
        if(isset($aUserLevel['DateExpires']))
            $sTxtExpiresIn = _t('_membership_txt_expires_in', floor(($aUserLevel['DateExpires'] - time())/86400));
        else
            $sTxtExpiresIn = _t('_membership_txt_expires_never');

        $this->addCss('levels.css');
        $sContent = $this->parseHtmlByName('current.html', array(
            'id' => $aLevelInfo['mem_id'],
            'title' => $aLevelInfo['mem_name'],
            'icon' =>  $this->_oConfig->getIconsUrl() . $aLevelInfo['mem_icon'],
            'description' => str_replace("\$", "&#36;", $aLevelInfo['mem_description']),
            'expires' => $sTxtExpiresIn
            )
        );

        return array($sContent, array(), array(), false);
    }

    function displayAvailableLevels($aValues)
    {
        $sCurrencyCode = strtoupper($this->_oConfig->getCurrencyCode());
        $sCurrencySign = $this->_oConfig->getCurrencySign();

        bx_import('BxDolPayments');
        $oPayment = BxDolPayments::getInstance();

        $aMemberships = array();
        foreach($aValues as $aValue) {
        	list($sJsCode, $sJsMethod) = $oPayment->getAddToCartJs(0, $this->_oConfig->getId(), $aValue['price_id'], 1, true);

        	$aMemberships[] = array(
        		'level' => $this->parseHtmlByName('available_level.html', array(
	                'url_root' => BX_DOL_URL_ROOT,
	                'id' => $aValue['mem_id'],
	                'title' => $aValue['mem_name'],
	                'icon' =>  $this->_oConfig->getIconsUrl() . $aValue['mem_icon'],
	        		'bx_if:show_description' => array(
	        			'condition' => strlen($aValue['mem_description']) > 0,
	        			'content' => array(
	        				'description' => str_replace("\$", "&#36;", $aValue['mem_description']),
	        			)
	        		),
	                'days' => $aValue['price_days'] > 0 ?  $aValue['price_days'] . ' ' . _t('_membership_txt_days') : _t('_membership_txt_expires_never') ,
	                'price' => $aValue['price_amount'],
	                'currency_code' => $sCurrencyCode,
	        		'add_to_cart_js' => $sJsMethod
	            ))
        	);
        }

        $this->addCss(array('levels.css', 'levels_tablet.css', 'levels_phone.css'));
        $sContent = $this->parseHtmlByName('available_levels.html', array(
        	'js_code' => $oPayment->getCartJs(),
        	'bx_repeat:levels' => $aMemberships
        ));

        return array($sContent, array(), array(), false);
    }

    function displaySelectLevelBlock($aLevels, $bDynamic = false)
    {
    	$iModuleId = $this->_oConfig->getId();
    	$iSelectedLevel = $iSelectedProvider = 0;  	

        $sCurrencyCode = strtoupper($this->_oConfig->getCurrencyCode());
        $sCurrencySign = $this->_oConfig->getCurrencySign();

        bx_import('BxDolPayments');
        $oPayment = BxDolPayments::getInstance();

        $aProviders = $oPayment->getProviders(0);
        if(empty($aProviders))
        	return array(MsgBox(_t('_membership_err_no_payment_options')));

        //--- Prepare levels
        $aTmplVarsLevels = array();
        foreach($aLevels as $iIndex => $aLevel) {
        	$bPaid = (int)$aLevel['price_amount'] > 0;

        	$iPriceDays = (int)$aLevel['price_days'];
        	$fPriceAmount = (float)$aLevel['price_amount'];

        	$aTmplVarsLevels[] = array(
        		'level' => $this->parseHtmlByName('select_level.html', array(
        			'js_object' => $this->_oConfig->getJsObject('join'),
	                'id' => $aLevel['mem_id'],
	        		'descriptor' => (int)$aLevel['mem_id'] == MEMBERSHIP_ID_STANDARD ? $this->_oConfig->getStandardDescriptor() : $oPayment->getCartItemDescriptor(0, $iModuleId, $aLevel['price_id'], 1),
	        		'checked' => $iIndex == $iSelectedLevel ? 'checked="checked"' : '',
	                'title' => $aLevel['mem_name'],
	                'icon' =>  $this->_oConfig->getIconsUrl() . $aLevel['mem_icon'],
	        		'bx_if:show_description' => array(
	        			'condition' => strlen($aLevel['mem_description']) > 0,
	        			'content' => array(
	        				'description' => str_replace("\$", "&#36;", $aLevel['mem_description']),
	        			)
	        		),
	                'days' => $iPriceDays > 0 ?  $iPriceDays . ' ' . _t('_membership_txt_days') : _t('_membership_txt_expires_never'),
	        		'price' => $fPriceAmount,
	        		'bx_if:show_price_paid' => array(
	        			'condition' => $bPaid,
	        			'content' => array(
	        				'price' => $fPriceAmount,
	                		'currency_code' => $sCurrencyCode,
	        			)
	        		),
	                'bx_if:show_price_free' => array(
	        			'condition' => !$bPaid,
	        			'content' => array()
	        		)
	            ))
        	);
        }

        //--- Prepare providers
		$aTmplVarsProviders = array();
		if(!empty($aProviders))
        	foreach($aProviders as $iIndex => $aProvider) {
        		if((int)$aProvider['for_visitor'] != 1)
        			continue;

        		$aTmplVarsProviders[] = array(
        			'name' => $aProvider['name'],
        			'caption' => $aProvider['caption_cart'],
        			'checked' => $iIndex == $iSelectedProvider ? 'checked="checked"' : ''
        		);
        	}
		if(empty($aTmplVarsProviders))
        	return array(MsgBox(_t('_membership_err_no_payment_options')));

		$bSelectedLevelPaid = (int)$aLevels[$iSelectedLevel]['price_amount'] > 0;

		$bSelectedProvider = count($aTmplVarsProviders) == 1;
		$sSelectedProvider = $bSelectedProvider ? $aTmplVarsProviders[0]['name'] : '';

		//--- Prepare captcha
		$sCaptcha = '';
		$bCaptcha = $this->_oConfig->isCaptchaOnPaidJoin();
		if($bCaptcha) {
			bx_import('BxDolCaptcha');
	        $oCaptcha = BxDolCaptcha::getObjectInstance();
	        $sCaptcha = $oCaptcha ? $oCaptcha->display($bDynamic) : _t('_sys_txt_captcha_not_available');
		}

		$aTmplParams = array(
			'js_object' => $this->_oConfig->getJsObject('join'),
			'js_code' => $this->getJsCode('join', true),
			'submit_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'join_submit',
			'bx_repeat:levels' => $aTmplVarsLevels,
			'bx_if:show_providers_selector' => array(
				'condition' => !$bSelectedProvider,
				'content' => array(
					'bx_if:show_providers_selector_hidden' => array(
						'condition' => !$bSelectedLevelPaid,
						'content' => array()
					),
					'bx_repeat:providers' => $aTmplVarsProviders
				)
			),
			'bx_if:show_captcha' => array(
				'condition' => $bCaptcha,
				'content' => array(
					'captcha' => $sCaptcha
				)
			),
			'bx_if:show_selected_provider' => array(
				'condition' => $bSelectedProvider,
				'content' => array(
					'name' => $sSelectedProvider
				)
			),
			'txt_checkout' => _t($bSelectedLevelPaid ? '_membership_btn_pay' : '_membership_btn_select')
		);

		$sCssJs = '';
        $sCssJs .= $this->addCss(array('levels.css', 'levels_tablet.css', 'levels_phone.css'), $bDynamic);
        $sCssJs .= $this->addJs('join.js', $bDynamic);

        $this->addJsTranslation(array('_membership_err_need_select_level', '_membership_err_need_select_provider'));
        return array(($bDynamic ? $sCssJs : '') . $this->parseHtmlByName('select_level_block.html', $aTmplParams), array(), array(), false);
    }

	function getPageCodeAdmin(&$aParams)
    {
        global $_page;
        global $_page_cont;

        $iIndex = isset($aParams['index']) ? (int)$aParams['index'] : 9;
        $_page['name_index'] = $iIndex;
        $_page['js_name'] = isset($aParams['js']) ? $aParams['js'] : '';
        $_page['css_name'] = isset($aParams['css']) ? $aParams['css'] : '';
        $_page['header'] = isset($aParams['title']['page']) ? $aParams['title']['page'] : '';

        if(isset($aParams['content']))
            foreach($aParams['content'] as $sKey => $sValue)
                $_page_cont[$iIndex][$sKey] = $sValue;

        PageCodeAdmin();
    }

    function getPageCode(&$aParams)
    {
        global $_page;
        global $_page_cont;

        $iIndex = isset($aParams['index']) ? (int)$aParams['index'] : 0;
        $_page['name_index'] = $iIndex;
        $_page['js_name'] = isset($aParams['js']) ? $aParams['js'] : '';
        $_page['css_name'] = isset($aParams['css']) ? $aParams['css'] : '';
        $_page['extra_js'] = isset($aParams['extra_js']) ? $aParams['extra_js'] : '';

        check_logged();

        if(isset($aParams['content']))
            foreach($aParams['content'] as $sKey => $sValue)
                $_page_cont[$iIndex][$sKey] = $sValue;

        if(isset($aParams['title']['page']))
            $this->setPageTitle($aParams['title']['page']);
        if(isset($aParams['title']['block']))
            $this->setPageMainBoxTitle($aParams['title']['block']);

        if(isset($aParams['breadcrumb']))
            $GLOBALS['oTopMenu']->setCustomBreadcrumbs($aParams['breadcrumb']);

        PageCode($this);
    }

	function getPageCodeError($sMessage)
    {
		$aParams = array(
            'title' => array(
                'page' => _t('_membership_pcaption_error'),
				'block' => _t('_membership_bcaption_error')
            ),
            'content' => array(
                'page_main_code' => MsgBox(_t($sMessage))
            )
        );
        $this->getPageCode($aParams);
    }

	function getJsCode($sType, $bWrap = false)
    {
    	$sJsObject = $this->_oConfig->getJsObject($sType);
        $sJsClass = $this->_oConfig->getJsClass($sType);

        $aOptions = array(
        	'sActionUrl' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri(),
        	'sObjName' => $sJsObject,
        	'sAnimationEffect' => $this->_oConfig->getAnimationEffect(),
        	'iAnimationSpeed' => $this->_oConfig->getAnimationSpeed(),
        	'sTxtPay' => bx_js_string(_t('_membership_btn_pay')),
        	'sTxtSelect' => bx_js_string(_t('_membership_btn_select')),
        	'sErrSelectLevel' => bx_js_string(_t('_membership_err_need_select_level')),
        	'sErrSelectProvider' => bx_js_string(_t('_membership_err_need_select_provider'))
        );

        $sContent .= 'var ' . $sJsObject . ' = new ' . $sJsClass . '(' . json_encode($aOptions) . ');';

        return $bWrap ? $this->_wrapInTagJsCode($sContent) : $sContent;
    }
}
