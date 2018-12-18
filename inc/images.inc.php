<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'params.inc.php' );

bx_import ('BxDolImageResize');

$gdInstalled = extension_loaded( 'gd' );
$use_gd = getParam( 'enable_gd' ) == 'on' ? 1 : 0;

/**
 * Resizes image given in $srcFilename to dimensions specified with $sizeX x $sizeY and
 * saves it to $dstFilename
 *
 * @param string $srcFilename			- source image filename
 * @param string $dstFilename			- destination image filename
 * @param int $sizeX					- width of destination image
 * @param int $sizeY					- height of destination image
 * @param bool $forceJPGOutput			- always make result in JPG format
 *
 * @return int 							- zero on success, non-zero on fail
 */
function imageResize( $srcFilename, $dstFilename, $sizeX, $sizeY, $forceJPGOutput = false, $isSquare = false )
{
    $o = BxDolImageResize::instance();
    $o->removeCropOptions ();
    $o->setJpegOutput ($forceJPGOutput);
    $o->setSize ($sizeX, $sizeY);
    if ($isSquare || (($sizeX == 32) && (32 == $sizeY)) || (($sizeX == 64) && (64 == $sizeY)))
        $o->setSquareResize (true);
    else
        $o->setSquareResize (false);
    return $o->resize($srcFilename, $dstFilename);
}

/**
 * Applies watermark to image given in $srcFilename with specified opacity and saves result
 * to $dstFilename
 *
 * @param string $srcFilename			- source image filename
 * @param string $dstFilename			- destination image filename
 * @param string $wtrFilename			- watermark filename
 * @param int $wtrTransparency			- watermark transparency (from 0 to 100)
 *
 * @return int 							- zero on success, non-zero on fail
 *
 *
 * NOTE: Source image should be in GIF, JPEG or PNG format
 * NOTE: if $wtrTransparency = 0 then no action will be done with source image
 *       but if $wtrTransparency = 100 then watermark will fully override source image
*/
function applyWatermark( $srcFilename, $dstFilename, $wtrFilename, $wtrTransparency )
{
    $o = BxDolImageResize::instance();
    return $o->applyWatermark ($srcFilename, $dstFilename, $wtrFilename, $wtrTransparency);
}
