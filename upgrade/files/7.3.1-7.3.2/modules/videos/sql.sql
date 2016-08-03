
UPDATE `sys_page_compose` SET `ColWidth` = '71.9' WHERE `Page` = 'bx_videos_home' AND `Func` = 'Albums' AND `ColWidth` = '28.1';

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.2' WHERE `uri` = 'videos' AND `version` = '1.3.1';

