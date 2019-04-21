
DELETE FROM `sys_objects_social_sharing` WHERE `object` = 'googleplus';

-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.4.2' WHERE `Name` = 'sys_tmp_version';

