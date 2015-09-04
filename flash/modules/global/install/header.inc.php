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
if(!defined("VERSION")) define("VERSION", "#version#");

/**
 * Data Base Settings
 */
if(!defined("DB_HOST")) define("DB_HOST", "#globals_db_server#");
if(!defined("DB_PORT")) define("DB_PORT", "#globals_db_port#");
if(!defined("DB_SOCKET")) define("DB_SOCKET", "#globals_db_socket#");
if(!defined("DB_NAME")) define("DB_NAME", "#globals_db_name#");
if(!defined("DB_USER")) define("DB_USER", "#globals_db_login#");
if(!defined("DB_PASSWORD")) define("DB_PASSWORD", "#globals_db_password#");
if(!defined("DB_PREFIX")) define("DB_PREFIX", "#globals_db_prefix#");
if(!defined("GLOBAL_MODULE")) define("GLOBAL_MODULE", "global");
$sDBModule = strtoupper(substr($sModule, 0, 1)) . substr($sModule, 1);
if(!defined("MODULE_DB_PREFIX")) define("MODULE_DB_PREFIX", DB_PREFIX . $sDBModule);

/**
 * Login and password for admin.
 */
$sAdminLogin = "#globals_admin_login#";
$sAdminPassword = "#globals_admin_password#";

/**
 * General Settings
 * URL and absolute path for the Ray location directory.
 */
$sRootPath = "#globals_root_path#";
$sRootURL = "#globals_root_url#";
$sRayHomeDir = "#globals_ray_home_dir#";

$sHomeUrl = $sRootURL . $sRayHomeDir;
$sHomePath = $sRootPath . $sRayHomeDir;

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
$sDefSmileset = "DefaultSmiles";

$sNoImageUrl = $sDataUrl . "no_photo.jpg";

/**
 * Integration parameters.
 * URL of the site in which Ray is integrated.
 */
$sScriptHomeDir = "#globals_script_home_dir#";
$sScriptHomeUrl = $sRootURL . $sScriptHomeDir;

/**
 * Path to images direcrory
 */
$sImagesPath = $sScriptHomeUrl . "#globals_script_image_dir#";

/**
 * URL of the profile view page
 */
$sProfileUrl = $sScriptHomeUrl . "#globals_script_profile_page#";
