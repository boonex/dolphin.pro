<?php

$mixCheckResult = 'Update can not be applied';

if ('7.1.6' == $this->oDb->getOne("SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'sys_tmp_version'"))
    $mixCheckResult = true;

$sTempl = $this->oDb->getOne("SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'template'");
if ($mixCheckResult === true && ($sTempl != 'uni' || (isset($_COOKIE['skin']) && $_COOKIE['skin'] != 'uni')))
    $mixCheckResult = "Update can be applied after fixing the following problem: <br />Set default template to 'UNI' in Admin Settings and/or switch to 'UNI' skin in user interface (or clear browser cookies) and try again.";

return $mixCheckResult;
