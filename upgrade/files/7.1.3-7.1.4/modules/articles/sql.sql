

UPDATE `sys_menu_mobile` SET `action_data` = '{xmlrpc_url}r.php?url=modules%2F%3Fr%3Darticles%2Fmobile_latest_entries%2F&user={member_username}&pwd={member_password}' WHERE `type` = 'bx_articles' AND `page` = 'homepage' AND `title` = '_articles_bcaption_all';


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.4' WHERE `uri` = 'articles' AND `version` = '1.1.3';

