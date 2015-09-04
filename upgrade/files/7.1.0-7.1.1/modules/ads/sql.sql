
DELETE FROM `sys_account_custom_stat_elements` WHERE `Label` = '_bx_ads_Ads';
INSERT INTO `sys_account_custom_stat_elements` (`ID`, `Label`, `Value`) VALUES(NULL, '_bx_ads_Ads', '__mad__ (<a href="__site_url__ads/my_page/add/">__l_add__</a>)');

UPDATE `sys_menu_admin` SET `title` = '_sys_module_ads' WHERE `parent_id` = 2 AND `name` = 'Ads';

DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/boonex/ads/classifieds.php?UsersOtherListing=1&IDProfile=';
INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES
('modules/boonex/ads/classifieds.php?UsersOtherListing=1&IDProfile=', 'ads/member_ads/', 'permalinks_module_ads');

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_ads' AND `Caption` = '{TitleShare}';
INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
(NULL, '{TitleShare}', 'share', '', 'showPopupAnyHtml (''{BaseUri}share_popup/{ads_id}'');', '', '12', 'bx_ads', 0);


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.1' WHERE `uri` = 'ads' AND `version` = '1.1.0';

