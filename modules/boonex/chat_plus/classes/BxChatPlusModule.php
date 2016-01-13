<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectModule');

class BxChatPlusModule extends BxDolConnectModule
{
    function BxChatPlusModule(&$aModule)
    {
        parent::BxDolConnectModule($aModule);
    }

    function actionAdministration()
    {
        parent::_actionAdministration('bx_chat_plus_url', '_bx_chat_plus_setting', '_bx_chat_plus_information', '_bx_chat_plus_information_block');
    }

    function actionRedirect ()
    {
        // check CSRF token
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
<!-- Start of Helpdesk Livechat Script -->
<script type="text/javascript">
(function(w, d, s, f, u) {
	w[f] = w[f] || [];
	w[f].push(u);
	var h = d.getElementsByTagName(s)[0],
		j = d.createElement(s);
	j.async = true;
	j.src = '{$sChatUrl}/packages/rocketchat_livechat/assets/rocket-livechat.js';
	h.parentNode.insertBefore(j, h);
})(window, document, 'script', 'initRocket', '{$sChatUrl}/livechat');
</script>
<!-- End of Helpdesk Livechat Script -->
EOS;

    }
}
