

UPDATE `sys_options` SET `desc` = 'Cache days<br>(how many days the images are valid in your cache, Enter 0 (zero) to never update screenshots once cached or -1 to disable caching and always use embedded method instead)' WHERE `Name` = 'bx_sites_cache_days';
UPDATE `sys_options` SET `desc` = 'Inside Page Captures<br>(i.e. not just homepages and sub-domains, select if you have purchased this pro package)' WHERE `Name` = 'bx_sites_inside_pages';
UPDATE `sys_options` SET `desc` = 'Custom Messages URL<br>(specify the URL where your custom message images are stored)' WHERE `Name` = 'bx_sites_custom_msg_url';
UPDATE `sys_options` SET `desc` = 'Default Thumbnail size<br>(width: mcr 75px, tny 90px, vsm 100px, sm 120px, lg 200px, xlg 320px)' WHERE `Name` = 'bx_sites_thumb_size';
UPDATE `sys_options` SET `desc` = 'Custom Width<br>(enter your custom image width, this will override default size)' WHERE `Name` = 'bx_sites_thumb_size_custom';
UPDATE `sys_options` SET `desc` = 'Widescreen resolution Y<br>(i.e. 900 for 1440x900 if 1440 is set for Native resolution)' WHERE `Name` = 'bx_sites_widescreen_y';
UPDATE `sys_options` SET `desc` = 'Refresh On-Demand<br>(select if you have purchased this pro package and want to allow your members to use it)' WHERE `Name` = 'bx_sites_redo';


DELETE FROM `sys_objects_actions` WHERE `Caption` = '{repostCpt}' AND `Type` = 'bx_sites';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
('{repostCpt}', 'repeat', '', '{repostScript}', '', 7, 'bx_sites');


-- update module version

UPDATE `sys_modules` SET `version` = '1.3.0' WHERE `uri` = 'sites' AND `version` = '1.2.1';

