<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolTwigTemplate');

class BxOAuthTemplate extends BxDolTwigTemplate
{
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    function pageError($sErrorMsg) 
    {
        $this->_page(_t('_bx_oauth_authorization'), MsgBox($sErrorMsg));
    }

    function pageAuth($sTitle) 
    {
        $this->_page(_t('_bx_oauth_authorization'), $this->parseHtmlByName('page_auth.html', array(
            'text' => _t('_bx_oauth_authorize_app', htmlspecialchars_adv($sTitle)),
            'url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'auth',
            'client_id' => bx_get('client_id'),
            'response_type' => bx_get('response_type'),
            'redirect_uri' => bx_get('redirect_uri'),
            'state' => bx_get('state'),
        )));
    }

    function _page($sTitle, $sContent) 
    {
        global $_page, $_page_cont;

        $this->addCss('main.css');

    	$_page['name_index'] = 0;
        $_page['header'] = $_page['header_text'] = $sTitle;
        $_page_cont[0]['page_main_code'] = $sContent;

        PageCode();
        exit;
    }

}
