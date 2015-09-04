<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

if(!defined("TRUE_VAL")) define("TRUE_VAL", "true");
if(!defined("FALSE_VAL")) define("FALSE_VAL", "false");
if(!defined("SUCCESS_VAL")) define("SUCCESS_VAL", "success");
if(!defined("FAILED_VAL")) define("FAILED_VAL", "failed");

if(!defined("CONTENTS_TYPE_XML")) define("CONTENTS_TYPE_XML", "xml");
if(!defined("CONTENTS_TYPE_SWF")) define("CONTENTS_TYPE_SWF", "swf");
if(!defined("CONTENTS_TYPE_OTHER")) define("CONTENTS_TYPE_OTHER", "other");

if(!defined("WIDGET_STATUS_ENABLED")) define("WIDGET_STATUS_ENABLED", "enabled");
if(!defined("WIDGET_STATUS_DISABLED")) define("WIDGET_STATUS_DISABLED", "disabled");
if(!defined("WIDGET_STATUS_NOT_INSTALLED")) define("WIDGET_STATUS_NOT_INSTALLED", "not installed");
if(!defined("WIDGET_STATUS_NOT_REGISTERED")) define("WIDGET_STATUS_NOT_REGISTERED", "not registered");

if(!defined("USER_STATUS_OFFLINE")) define("USER_STATUS_OFFLINE", "offline");

if(!defined("FILE_DEFAULT_KEY")) define("FILE_DEFAULT_KEY", "_default_");

$aErrorCodes = array(
    1 => "Cannot open file '#1#'",
    2 => "Cannot write to file '#1#'",
    3 => "Cannot connect to database server",
    4 => "Cannot open database",
    5 => "Cannot execute MySQL query",
    6 => "Cannot find directory '#1#'",
    7 => "Directory '#1#' is empty",
    8 => "Wrong widget name"
);

$aInfo = array(
    'mode' => "paid",
    'title' => "Ray base",
    'version' => "7.2.0000",
    'author' => "Boonex",
    'authorUrl' => "http://www.boonex.com/"
);
$aModules = array(
    'admin' => array(
        'caption' => 'Base',
        'parameters' => array('nick', 'password'),
        'js' => array(),
        'inline' => false,
        'vResizable' => false,
        'hResizable' => false,
        'layout' => array('top' => 0, 'left' => 0, 'width' => 700, 'height' => 600),
        'minSize' => array('width' => 700, 'height' => 600),
        'reloadable' => false,
        'div' => array()
    )
);
