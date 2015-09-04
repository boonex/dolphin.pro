

UPDATE `sys_objects_cmts` SET `ObjectName` = 'bx_blogs' WHERE `ObjectName` = 'blogposts';
UPDATE `sys_objects_vote` SET `ObjectName` = 'bx_blogs' WHERE `ObjectName` = 'blogposts';
UPDATE `sys_objects_views` SET `name` = 'bx_blogs' WHERE `name` = 'blogposts';


UPDATE `sys_modules` SET `version` = '1.0.7' WHERE `uri` = 'blogs' AND `version` = '1.0.6';

