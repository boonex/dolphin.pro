<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectModule');

class BxChatWebRTCModule extends BxDolConnectModule
{
    function BxChatWebRTCModule(&$aModule)
    {
        parent::BxDolConnectModule($aModule);
    }

    function actionAdministration()
    {
        parent::_actionAdministration('bx_chat_webrtc_url', '_bx_chat_webrtc_setting', '_bx_chat_webrtc_information', '_bx_chat_webrtc_information_block');
    }

    function actionRedirect ()
    {
        // check CSRF token
        if (!getParam('bx_chat_webrtc_url')) {
            $this->_oTemplate->displayMsg(_t('_bx_chat_webrtc_not_configured'));
            return;
        }

        header("Location:" . getParam('bx_chat_webrtc_url'), true, 302);
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
}
