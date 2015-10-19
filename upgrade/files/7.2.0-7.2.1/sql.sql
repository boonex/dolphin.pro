
-- ================ can be safely applied multiple times ================ 

DELETE FROM `sys_options` WHERE `Name` IN('disable_join_form', 'site_timezone', 'sys_safe_iframe_regexp');

SET @iCatSite = 7;
SET @iCatSecurity = 14;

INSERT INTO `sys_options` VALUES 
('site_timezone', 'UTC', @iCatSite, 'Site Timezone', 'select', '', '', 40, 'PHP:return array_combine(timezone_identifiers_list(), timezone_identifiers_list());'),
('sys_safe_iframe_regexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%', @iCatSecurity, 'Safe iframe URI regular expression (don''t edit if unsure)', 'text', '', '', 100, '');



INSERT IGNORE INTO `sys_profile_fields` VALUES(NULL, 'FirstName', 'text', NULL, '', 2, 200, '', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);
INSERT IGNORE INTO `sys_profile_fields` VALUES(NULL, 'LastName',  'text', NULL, '', 2, 200, '', 'LKey', '', 0, '', 1, 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '', 0, NULL, 0, NULL, 0, NULL, 0, 0);



-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.2.1' WHERE `Name` = 'sys_tmp_version';

