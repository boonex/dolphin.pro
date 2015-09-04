
-- update default options
UPDATE `sys_options` SET `VALUE` = '9' WHERE `Name` = 'bx_photos_number_index';
UPDATE `sys_options` SET `VALUE` = '6' WHERE `Name` = 'bx_photos_number_top';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `Name` = 'bx_photos_number_user';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `Name` = 'bx_photos_number_related';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `Name` = 'bx_photos_number_previous_rated';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `Name` = 'bx_photos_number_browse';
UPDATE `sys_options` SET `VALUE` = '9' WHERE `Name` = 'bx_photos_number_albums_browse';
UPDATE `sys_options` SET `VALUE` = '3' WHERE `Name` = 'bx_photos_number_albums_home';

-- update page builder blocks ordering only if they have default values
UPDATE `sys_page_compose` SET `Column` = '0', `Order` = '0' WHERE `Column` = '2' AND `Order` = '1' AND `Page` = 'profile' AND `Desc` = 'Profile Photo Block';
UPDATE `sys_page_compose` SET `Column` = '2', `Order` = '1' WHERE `Column` = '2' AND `Order` = '2' AND `Page` = 'profile' AND `Desc` = 'Profile Photo Album Block';

