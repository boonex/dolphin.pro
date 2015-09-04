
UPDATE `sys_objects_actions` SET `Url` = '{evalResult}&make_avatar_from_shared_photo={ID}' WHERE `Type` = '[db_prefix]' AND `Caption` = '{TitleSetAsAvatar}';

UPDATE `sys_modules` SET `version` = '1.0.9' WHERE `uri` = 'photos' AND `version` = '1.0.8';

