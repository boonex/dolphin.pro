<?php

$mixCheckResult = 'Update can not be applied';

if ('7.3.0.B1' == $this->oDb->getOne("SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'sys_tmp_version'"))
    $mixCheckResult = true;

return $mixCheckResult;
