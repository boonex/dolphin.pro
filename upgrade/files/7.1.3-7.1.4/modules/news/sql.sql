

UPDATE `sys_menu_mobile` SET `action_data` = '{xmlrpc_url}r.php?url=modules%2F%3Fr%3Dnews%2Fmobile_latest_entries%2F&user={member_username}&pwd={member_password}' WHERE `type` = 'bx_news' AND `page` = 'homepage' AND `title` = '_news_bcaption_view_main';


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.4' WHERE `uri` = 'news' AND `version` = '1.1.3';

