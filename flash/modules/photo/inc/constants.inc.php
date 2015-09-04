<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
if(!defined("IMAGE_EXTENSION")) define("IMAGE_EXTENSION", ".jpg");
if(!defined("THUMB_FILE_NAME")) define("THUMB_FILE_NAME", "_small");

$aInfo = array(
    'mode' => "as3",
    'title' => "Photo Shooter",
    'version' => "7.2.0000",
    'code' => "photo_7.2.0000",
    'author' => "Boonex",
    'authorUrl' => "http://www.boonex.com"
);
$aModules = array(
    'shooter' => array(
        'caption' => 'Photo Shooter',
        'parameters' => array('id', 'extra'),
        'js' => array(),
        'inline' => true,
        'vResizable' => false,
        'hResizable' => false,
        'reloadable' => true,
        'layout' => array('top' => 0, 'left' => 0, 'width' => 400, 'height' => 300),
                                'minSize' => array('width' => 250, 'height' => 230),
        'div' => array(
            'style' => array('text-align' => 'center')
        )
    )
);
