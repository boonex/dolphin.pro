<?php

$sConditionalSuccess = "Update can be applied after fixing the following problem: <br />";

$mixCheckResult = 'Update can not be applied';

if ('7.0.9' == $this->oDb->getOne("SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'sys_tmp_version'"))
    $mixCheckResult = true;

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'wall'");
if ($iModuleId && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'Wall'(Timeline) module can't be upgraded, it must be uninstalled before the upgrade process, after upgrade is completed you can install it again.";

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'spy'");
if ($iModuleId && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'Spy' module can't be upgraded, it must be uninstalled before the upgrade process, after upgrade is completed you can install it again.";

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'map_profiles'");
if ($iModuleId && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'Profiles Map' module is replaced with new 'World Maps' module, you need to uninstall 'Profiles Map' module.";

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'open_social'");
if ($iModuleId && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'Open Social' module is removed, you need to uninstall this module.";

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'data_migration'");
if ($iModuleId && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'Data Migration from Dolphin 6.1.6' module is removed, you need to uninstall this module.";

bx_import('BxDolIO');
$oBxDolIO = new BxDolIO();

$isWritable = $oBxDolIO->isWritable('sitemap.xml');
if (!$isWritable && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'sitemap.xml' file is not writable, make it writable and try again";

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'profiler'");
$isWritable = $oBxDolIO->isWritable('modules/boonex/profiler/log');
if ($iModuleId && !$isWritable && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'modules/boonex/profiler/log' directory is not writable, make it writable and try again";

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'sites'");
$isWritable = $oBxDolIO->isWritable('modules/boonex/sites/data/images/thumbs');
if ($iModuleId && !$isWritable && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "'modules/boonex/sites/data/images/thumbs' directory is not writable, make it writable and try again";

$iModuleId = $this->oDb->getOne("SELECT `ID` FROM `sys_modules` WHERE `uri` = 'forum'");
$isFileExists = file_exists(BX_DIRECTORY_PATH_MODULES . 'boonex/forum/classes/en') || file_exists(BX_DIRECTORY_PATH_MODULES . 'boonex/forum/js/en') || file_exists(BX_DIRECTORY_PATH_MODULES . 'boonex/forum/layout/base_en') || file_exists(BX_DIRECTORY_PATH_MODULES . 'boonex/forum/layout/uni_en');
if ($iModuleId && $isFileExists && true === $mixCheckResult)
    $mixCheckResult = $sConditionalSuccess . "Remove the following folders before installation (remove folders for other than 'en' language as well): <br />
        &nbsp;&nbsp; modules/boonex/forum/classes/en/ <br />
        &nbsp;&nbsp; modules/boonex/forum/js/en/ <br />
        &nbsp;&nbsp; modules/boonex/forum/layout/base_en/ <br />
        &nbsp;&nbsp; modules/boonex/forum/layout/uni_en/";

$sTempl = $this->oDb->getOne("SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'template'");
if ($sTempl != 'uni' || (isset($_COOKIE['skin']) && $_COOKIE['skin'] != 'uni'))
    $mixCheckResult = $sConditionalSuccess . "Set default template to 'UNI' in Admin Settings and/or switch to 'UNI' skin in user interface (or clear browser cookies) and try again.";

return $mixCheckResult;
