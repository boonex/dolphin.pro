<?php


if (version_compare(PHP_VERSION, '5.4.0', '<')) 
    return "This version requires PHP 5.4.0 or newer";


if ($this->oDb->getOne("SELECT COUNT(*) AS `count` FROM `RayMp3Files` GROUP BY `Uri` ORDER BY `count` DESC LIMIT 1") > 1)
    return "Can't add uniq index for 'Uri' field in 'RayMp3Files' table, because there are duplicate rows";

if ($this->oDb->getOne("SELECT COUNT(*) AS `count` FROM `RayVideoFiles` GROUP BY `Uri` ORDER BY `count` DESC LIMIT 1") > 1)
    return "Can't add uniq index for 'Uri' field in 'RayVideoFiles' table, because there are duplicate rows";

if ($this->oDb->getOne("SELECT COUNT(*) AS `count` FROM `RayVideo_commentsFiles` GROUP BY `Uri` ORDER BY `count` DESC LIMIT 1") > 1)
    return "Can't add uniq index for 'Uri' field in 'RayVideo_commentsFiles' table, because there are duplicate rows";



$mixCheckResult = 'Update can not be applied';

if ('7.3.5' == $this->oDb->getOne("SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'sys_tmp_version'"))
    $mixCheckResult = true;

return $mixCheckResult;
