
DELETE FROM `sys_objects_tag` WHERE `ObjectName` = 'bx_feedback';
INSERT INTO `sys_objects_tag` (`ObjectName`, `Query`, `PermalinkParam`, `EnabledPermalink`, `DisabledPermalink`, `LangKey`) VALUES
('bx_feedback', 'SELECT `tags` FROM `bx_fdb_entries` WHERE `id`={iID} AND `status`=0', 'permalinks_module_feedback', 'm/feedback/tag/{tag}', 'modules/?r=feedback/tag/{tag}', '_feedback_lcaption_tags_object');

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.4' WHERE `uri` = 'feedback' AND `version` = '1.3.3';

