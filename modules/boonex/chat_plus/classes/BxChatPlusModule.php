<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectModule');

class BxChatPlusModule extends BxDolConnectModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    function actionAdministration()
    {
        parent::_actionAdministration('bx_chat_plus_url', '_bx_chat_plus_setting', '_bx_chat_plus_information', '_bx_chat_plus_information_block');
    }

    function actionView ()
    {
        $this->_oTemplate->pageStart();

        bx_import ('Page', $this->_aModule);
        $oPage = new BxChatPlusPage ('bx_chat_plus');
        echo $oPage->getCode();

        $this->_oTemplate->pageCode(_t('_bx_chat_plus_chat'), false, false);
    }

    function actionRedirect ()
    {
        if (!getParam('bx_chat_plus_url')) {
            $this->_oTemplate->displayMsg(_t('_bx_chat_plus_not_configured'));
            return;
        }

        header("Location:" . getParam('bx_chat_plus_url'), true, 302);
    }

    function actionLogo ()
    {
        if (!($sLogoUrl = $GLOBALS['oFunctions']->getLogoUrl())) {
            header("HTTP/1.0 404 Not Found");
            echo '404 Not Found';
            return;
        }

        header("Location:" . $sLogoUrl);
    }

    function serviceChatBlock ()
    {
        if (!getParam('bx_chat_plus_url'))
           return array(MsgBox(_t('_bx_chat_plus_not_configured')));
       
        $this->_oTemplate->addCss('main.css');
        $s = $this->_oTemplate->parseHtmlByName('chat_block.html', array('chat_url' => getParam('bx_chat_plus_url')));
       
        return array($s, array (
            _t('_bx_chat_plus_open_in_separate_window') => array (
                'href' => getParam('bx_chat_plus_url'),
                'target' => '_blank',
                'active' => true,
            ),
        ), false, false, 'getChatBlockMenu');
    }

    function serviceHelpdeskCode ()
    {
        $sChatUrl = getParam('bx_chat_plus_url');
        if (!getParam('bx_chat_plus_helpdesk') || !$sChatUrl)
            return '';

        if (getParam('bx_chat_plus_helpdesk_guest_only') && isLogged())
            return '';

        $aUrl = parse_url($sChatUrl);
        $sChatUrl = $aUrl['scheme'] . '://' . $aUrl['host'] . ($aUrl['port'] ? ':' . $aUrl['port'] : '');

        return <<<EOS

<!-- Start of Rocket.Chat Livechat Script -->
<script type="text/javascript">
(function(w, d, s, u) {
	w.RocketChat = function(c) { w.RocketChat._.push(c) }; w.RocketChat._ = []; w.RocketChat.url = u;
	var h = d.getElementsByTagName(s)[0], j = d.createElement(s);
	j.async = true; j.src = '{$sChatUrl}/packages/rocketchat_livechat/assets/rocketchat-livechat.min.js?_=201702160944';
	h.parentNode.insertBefore(j, h);
})(window, document, 'script', '{$sChatUrl}/livechat');
</script>
<!-- End of Rocket.Chat Livechat Script -->

EOS;

    }
}
