<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
if(!defined("VC_TEMP_FILE_NAME")) define("VC_TEMP_FILE_NAME", "_temp");
if(!defined("VC_THUMB_FILE_NAME")) define("VC_THUMB_FILE_NAME", "_small");
if(!defined("VC_MOBILE_EXTENSION")) define("VC_MOBILE_EXTENSION", ".mp4");
if(!defined("VC_FLV_EXTENSION")) define("VC_FLV_EXTENSION", ".flv");
if(!defined("VC_M4V_EXTENSION")) define("VC_M4V_EXTENSION", ".m4v");
if(!defined("VC_IMAGE_EXTENSION")) define("VC_IMAGE_EXTENSION", ".jpg");
if(!defined("VC_VIDEO_SIZE_4_3")) define("VC_VIDEO_SIZE_4_3", "288x216");
if(!defined("VC_VIDEO_SIZE_16_9")) define("VC_VIDEO_SIZE_16_9", "384x216");
if(!defined("VC_THUMB_SIZE")) define("VC_THUMB_SIZE", "110x80");

if(!defined("VC_STATUS_APPROVED")) define("VC_STATUS_APPROVED", "approved");
if(!defined("VC_STATUS_DISAPPROVED")) define("VC_STATUS_DISAPPROVED", "disapproved");
if(!defined("VC_STATUS_PENDING")) define("VC_STATUS_PENDING", "pending");
if(!defined("VC_STATUS_PROCESSING")) define("VC_STATUS_PROCESSING", "processing");
if(!defined("VC_STATUS_FAILED")) define("VC_STATUS_FAILED", "failed");

$aInfo = array(
    'mode' => "as3",
    'title' => "Comments Video Player",
    'version' => "7.2.0000",
    'code' => "video_7.2.0000",
    'author' => "Boonex",
    'authorUrl' => "http://www.boonex.com"
);
$aModules = array(
    'player' => array(
        'caption' => 'Video Player',
        'parameters' => array('id', 'user', 'password'),
        'js' => array(),
        'inline' => true,
        'vResizable' => false,
        'hResizable' => false,
        'reloadable' => true,
        'layout' => array('top' => 0, 'left' => 0, 'width' => "100%", 'height' => 400),
                                'minSize' => array('width' => 350, 'height' => 400),
        'div' => array()
    ),
    'recorder' => array(
        'caption' => 'Video Recorder',
        'parameters' => array('user', 'password', 'extra'),
        'js' => empty($sModulesUrl) ? array() : array($sModulesUrl . $sModule . "/js/record.js"),
        'inline' => true,
        'vResizable' => false,
        'hResizable' => false,
        'reloadable' => true,
        'layout' => array('top' => 0, 'left' => 0, 'width' => "100%", 'height' => 300),
                                'minSize' => array('width' => 250, 'height' => 230),
        'div' => array()
    )
);
