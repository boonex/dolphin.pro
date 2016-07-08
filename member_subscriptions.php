<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

bx_import('BxDolSubscription');

check_logged();
$oSubscription = BxDolSubscription::getInstance();

// --------------- page components
$iIndex = 0;

$_page = array(
    'css_name' => '',
    'header' => _t('_sys_pcpt_my_subscriptions'),
    'header_text' => _t('_sys_bcpt_my_subscriptions'),
    'name_index' => $iIndex
);
$_page_cont[$iIndex]['page_main_code'] = $oSubscription->getMySubscriptions();

// --------------- [END] page components

PageCode();
// --------------- page components functions
