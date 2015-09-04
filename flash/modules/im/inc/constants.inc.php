<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
if(!defined("USER_STATUS_ONLINE")) define("USER_STATUS_ONLINE", "online");
if(!defined("USER_STATUS_OFFLINE")) define("USER_STATUS_OFFLINE", "offline");

$aInfo = array(
    'mode' => "as3",
    'title' => "Messenger",
    'version' => "7.2.0000",
    'code' => "im_7.2.0000",
    'author' => "Boonex",
    'authorUrl' => "http://www.boonex.com/"
);
$aModules = array(
    'user' => array(
        'caption' => 'Messenger',
        'parameters' => array('sender', 'password', 'recipient'),
        'js' => array(),
        'inline' => false,
        'vResizable' => true,
        'hResizable' => true,
        'layout' => array('top' => 0, 'left' => 0, 'width' => 550, 'height' => 450),
                                'minSize' => array('width' => 550, 'height' => 400),
        'reloadable' => true,
        'div' => array()
    )
);
