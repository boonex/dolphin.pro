<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $aModule['class_prefix'] . 'Module.php');

    $oSimpleMessenger = new BxSimpleMessengerModule($aModule);

    // ** init some needed variables ;

    global $_page;
    global $_page_cont;

    $iIndex = 2;

    $_page['name_index']	= $iIndex;

    $sPageCaption = _t('_simple_messenger_privacy_settings_caption');

    $_page['header']        = $sPageCaption ;
    $_page['header_text']   = $sPageCaption ;
    $_page['css_name']      = 'main.css';

    $_page_cont[$iIndex]['page_main_code'] = $oSimpleMessenger -> getPrivacyPage();

    PageCode($oSimpleMessenger -> _oTemplate);
