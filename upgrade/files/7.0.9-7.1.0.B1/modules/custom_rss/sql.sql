
-- menu admin

UPDATE `sys_menu_admin` SET `name` = 'Custom RSS', `title` = '_crss_ami_custom_rss', `icon` = 'rss' WHERE `name` = 'Custom RSS Moderation';


-- page biulder

DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Custom RSS block';
SET @PageKey1 = (SELECT MAX(`order`) FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Column`=2);
INSERT INTO `sys_page_compose` (`ID`, `Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
(NULL, 'profile', '1140px', 'Custom RSS block', '_crss_Custom_Feeds', 2, @PageKey1+1, 'PHP', 'return BxDolService::call(''custom_rss'', ''gen_custom_rss_block'', array($this->oProfileGen->_iProfileID));', 0, 71.9, 'non,memb', 0);


-- site stats

DELETE FROM `sys_stat_site` WHERE `Name` = 'crss';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'crss', 'crss_ss', '', 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''active''', 'modules/boonex/custom_rss/post_mod_crss.php', 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''passive''', 'rss', @iStatSiteOrder);



-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'custom_rss' AND `version` = '1.0.9';

