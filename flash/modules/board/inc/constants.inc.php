<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

//--- Board statuses ---//
if(!defined("BOARD_STATUS_NEW")) define("BOARD_STATUS_NEW", "new");
if(!defined("BOARD_STATUS_NORMAL")) define("BOARD_STATUS_NORMAL", "normal");
if(!defined("BOARD_STATUS_DELETE")) define("BOARD_STATUS_DELETE", "delete");
if(!defined("BOARD_STATUS_UPDATED")) define("BOARD_STATUS_UPDATED", "updated");

if(!defined("BOARD_TYPE_EDIT")) define("BOARD_TYPE_EDIT", "edit");
if(!defined("BOARD_TYPE_VIEW")) define("BOARD_TYPE_VIEW", "view");

if(!defined("USER_STATUS_NEW")) define("USER_STATUS_NEW", "new");
if(!defined("USER_STATUS_OLD")) define("USER_STATUS_OLD", "old");
if(!defined("USER_STATUS_IDLE")) define("USER_STATUS_IDLE", "idle");

$aInfo = array(
    'mode' => "as3",
    'title' => "Whiteboard",
    'version' => "7.2.0000",
    'code' => "board_7.2.0000",
    'author' => "Boonex",
    'authorUrl' => "http://www.boonex.com"
);
$aModules = array(
    'user' => array(
        'caption' => 'Whiteboard',
        'parameters' => array('id', 'password', 'saved'),
        'js' => array(),
        'inline' => true,
        'vResizable' => false,
        'hResizable' => false,
        'reloadable' => true,
        'layout' => array('top' => 0, 'left' => 0, 'width' => "100%", 'height' => 600),
                                'minSize' => array('width' => 700, 'height' => 600),
        'div' => array(),
    )
);
