<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

/**
 * Current version information.
 */
if(!defined("VERSION")) define("VERSION", "7.4.0");

/**
 * Data Base Settings
 */
if(!defined("DB_HOST")) define("DB_HOST", $db['host']);
if(!defined("DB_PORT")) define("DB_PORT", $db['port']);
if(!defined("DB_SOCKET")) define("DB_SOCKET", $db['sock']);
if(!defined("DB_NAME")) define("DB_NAME", $db['db']);
if(!defined("DB_USER")) define("DB_USER", $db['user']);
if(!defined("DB_PASSWORD")) define("DB_PASSWORD", $db['passwd']);
if(!defined("DB_PREFIX")) define("DB_PREFIX", "Ray");
if(!defined("GLOBAL_MODULE")) define("GLOBAL_MODULE", "global");
if(!defined("MODULE_DB_PREFIX")) define("MODULE_DB_PREFIX", DB_PREFIX . ucfirst(empty($sModule) ? '' : $sModule));

/**
 * Flash plugin version
 */
$sFlashPlayerVersion = "9.0.0";

/**
 * General Settings
 * URL and absolute path for the Ray location directory.
 */
$sRootPath = $dir['root'];
$sRootURL = $site['url'];
$sRayHomeDir = "flash/";

$sHomeUrl = $sRootURL . $sRayHomeDir;
$sHomePath = $sRootPath . $sRayHomeDir;

$sRayXmlUrl = $sHomeUrl . "XML.php";

$sFileErrorPath = $sHomePath . "file_error.html";

/**
 * Pathes to the system directories and necessary files.
 */
$sModulesDir = "modules/";
$sModulesUrl = $sHomeUrl . $sModulesDir;
$sModulesPath = $sHomePath . $sModulesDir;

$sGlobalDir = "global/";
$sGlobalUrl = $sModulesUrl . $sGlobalDir;
$sGlobalPath = $sModulesPath . $sGlobalDir;

$sFfmpegPath = $sGlobalPath . "app/ffmpeg.exe";
if(is_integer(strpos($sFfmpegPath, " ")))
    $sFfmpegPath = '"' . $sFfmpegPath . '"';

$sIncPath = $sGlobalPath . "inc/";

$sDataDir = "data/";
$sDataUrl = $sGlobalUrl . $sDataDir;
$sDataPath = $sGlobalPath . $sDataDir;

$sSmilesetsDir = "smilesets/";
$sSmilesetsUrl = $sDataUrl . $sSmilesetsDir;
$sSmilesetsPath = $sDataPath . $sSmilesetsDir;

/**
 * Default smileset name. It has to be equel to the name of some directory in the "smilesets" directory.
 * The default path to smilesets directory is [path_to_ray]/data/smilesets
 */
$sDefSmileset = "default";

$sNoImageUrl = $sDataUrl . "no_photo.jpg";
$sWomanImageUrl = $sDataUrl . "woman.gif";
$sManImageUrl = $sDataUrl . "man.gif";

/**
 * Integration parameters.
 * URL of the site in which Ray is integrated.
 */
$sScriptHomeDir = "";
$sScriptHomeUrl = $sRootURL . $sScriptHomeDir;

/**
 * Path to images direcrory
 */
$sImagesPath = $sScriptHomeUrl . "media/images/sharingImages/";

/**
 * URL of the profile view page
 */
$sProfileUrl = $sScriptHomeUrl . "profile.php";
