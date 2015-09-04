
-- ads categories

UPDATE `[db_prefix]_category` SET `Description` = 'Jobs', `CustomFieldName2` = '', `Unit2` = '', `Picture` = 'user-md' WHERE `Name` = 'Jobs';
UPDATE `[db_prefix]_category` SET `Description` = 'Music Exchange', `Picture` = 'music' WHERE `Name` = 'Music Exchange';
UPDATE `[db_prefix]_category` SET `Description` = 'Housing & Rentals', `Picture` = 'home' WHERE `Name` = 'Housing & Rentals';
UPDATE `[db_prefix]_category` SET `Description` = 'Services', `Picture` = 'wrench' WHERE `Name` = 'Services';
UPDATE `[db_prefix]_category` SET `Description` = 'Casting Calls', `Picture` = 'eye-open' WHERE `Name` = 'Casting Calls';
UPDATE `[db_prefix]_category` SET `Description` = 'Personals', `Picture` = 'user' WHERE `Name` = 'Personals';
UPDATE `[db_prefix]_category` SET `Description` = 'For Sale', `Picture` = 'shopping-cart' WHERE `Name` = 'For Sale';
UPDATE `[db_prefix]_category` SET `Description` = 'Cars For Sale', `Picture` = 'truck' WHERE `Name` = 'Cars For Sale';


-- menu admin

UPDATE `sys_menu_admin` SET `icon` = 'money' WHERE `name` = 'Ads';


-- page compose

UPDATE `sys_page_compose_pages` SET `Title` = 'Ads View' WHERE `Name` = 'ads';
DELETE FROM `sys_page_compose_pages` WHERE `Name` = 'ads_home';
SET @iPCPOrder = (SELECT `Order` FROM `sys_page_compose_pages` WHERE `Name` = 'ads' LIMIT 1);
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES
('ads_home', 'Ads Home', @iPCPOrder);

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'member', 'profile') AND `Desc` = 'Classifieds';
DELETE FROM `sys_page_compose` WHERE `Page` = 'ads_home';
DELETE FROM `sys_page_compose` WHERE `Func` IN ('AdDescription', 'AdCustomInfo', 'AdPhotos', 'ViewComments', 'ActionList', 'AdInfo', 'Rate', 'UserOtherAds', 'SocialSharing') AND `Page` = 'ads';
DELETE FROM `sys_page_compose` WHERE `Desc` = 'Ad''s Location' AND `Page` = 'ads';
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` = 'ads' OR `Page` = 'ads_home';

INSERT INTO `sys_page_compose` (`ID`, `Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
(NULL, 'index', '1140px', 'Classifieds', '_bx_ads_Ads', 0, 0, 'PHP', 'return BxDolService::call(''ads'', ''ads_index_page'');', 1, 28.1, 'non,memb', 0),
(NULL, 'member', '1140px', 'Classifieds', '_bx_ads_Ads', 0, 0, 'PHP', 'return BxDolService::call(''ads'', ''ads_profile_page'', array($this->iMember));', 1, 28.1, 'non,memb', 0),
(NULL, 'profile', '1140px', 'Classifieds', '_bx_ads_Ads', 0, 0, 'PHP', 'return BxDolService::call(''ads'', ''ads_profile_page'', array($this->oProfileGen->_iProfileID));', 1, 28.1, 'non,memb', 0),

(NULL, 'ads', '1140px', '', '_Description', 1, 0, 'AdDescription', '', 1, 71.9, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_bx_ads_Ad_photos', 1, 1, 'AdPhotos', '', 1, 71.9, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_Comments', 1, 2, 'ViewComments', '', 1, 71.9, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_Info', 2, 0, 'AdInfo', '', 1, 28.1, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_bx_ads_Custom_Values', 2, 1, 'AdCustomInfo', '', 1, 28.1, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_Actions', 2, 2, 'ActionList', '', 1, 28.1, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_sys_block_title_social_sharing', 2, 3, 'SocialSharing', '', 1, 28.1, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_Rate', 2, 4, 'Rate', '', 1, 28.1, 'non,memb', 0),
(NULL, 'ads', '1140px', '', '_bx_ads_Users_other_listing', 2, 5, 'UserOtherAds', '', 1, 28.1, 'non,memb', 0);

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Content`, `Column`, `Order`, `DesignBox`, `ColWidth`, `MinWidth`, `Visible`, `Caption`, `Func`) VALUES
('ads_home', '1140px', '', '', 2, 0, 0, 28.1, 0, 'non,memb', '_bx_ads_last_ads', 'last'),
('ads_home', '1140px', '', '', 1, 0, 1, 71.9, 0, 'non,memb', '_bx_ads_last_featured', 'featured'),
('ads_home', '1140px', '', '', 1, 1, 0, 71.9, 0, 'non,memb', '_bx_ads_Categories', 'categories');

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'ads' AND `Column` = 2 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES
('ads', '1140px', 'Ad''s Location', '_Location', 2, @iMaxOrder, 'PHP', 'return BxDolService::call(''wmap'', ''location_block'', array(''ads'', $this->oAds->oCmtsView->getId()));', 1, 28.1, 'non,memb', 0);

SET @iMaxOrder = (SELECT `Order` + 1 FROM `sys_page_compose` WHERE `Page` = 'ads_home' AND `Column` = 1 ORDER BY `Order` DESC LIMIT 1);
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
('ads_home', '1140px', 'Map', '_Map', 1, @iMaxOrder, 'PHP', 'return BxDolService::call(''wmap'', ''homepage_part_block'', array (''ads''));', 1, 71.9, 'non,memb', 0);


-- stat site

UPDATE `sys_stat_site` SET `UserLink` = 'modules/boonex/ads/classifieds.php?action=show_all_ads', `AdminLink` = 'modules/boonex/ads/post_mod_ads.php', `AdminQuery` = 'SELECT COUNT(`ID`) FROM `[db_prefix]_main` WHERE `Status`=''new'' AND UNIX_TIMESTAMP() - `[db_prefix]_main`.`LifeTime`*24*60*60 < `[db_prefix]_main`.`DateTime`', `IconName` = 'money' WHERE `Name` = 'cls';


-- menu top

SET @iMenuAds = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Ads');
UPDATE `sys_menu_top` SET `Picture` = 'money', `Icon` = 'money' WHERE `ID` = @iMenuAds;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuAds OR (`Name` = 'Profile Ads' AND (`Parent` = 4 OR `Parent` = 9));
UPDATE `sys_menu_top` SET `Picture` = 'money', `Name` = 'Ad View' WHERE `Name` = 'bx_ads_view' AND `Parent` = 0;


-- menu member 

SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_ads';
INSERT INTO `sys_menu_member` SET `Name` = 'bx_ads', `Eval` = 'return BxDolService::call(''ads'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- alert handlers
SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_ads_map_install' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;
INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_ads_map_install', '', '', 'if (''wmap'' == $this->aExtras[''uri''] && $this->aExtras[''res''][''result'']) BxDolService::call(''ads'', ''map_install'');');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'module', 'install', @iHandler);


-- objects: actions 

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_ads' AND `Caption` IN ('_bx_ads_Add', '_bx_ads_My_Ads', '_bx_ads_Ads_Home', '_Edit', '_bx_ads_RSS', '{sbs_ads_title}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_ads' AND (`Eval` LIKE '%_bx_ads_Buy_Now%' OR `Eval` LIKE '%_Send Message%' OR `Eval` LIKE '%_Delete%' OR `Eval` LIKE '%_bx_ads_Activate%' OR `Eval` LIKE '%_bx_ads_Feature_it%');

INSERT INTO `sys_objects_actions` (`ID`, `Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES

(NULL, '_bx_ads_Add', 'plus', '{evalResult}', '', 'if ({only_menu} == 1)\r\n    if (getParam(''permalinks_module_ads'') == ''on'') return ''ads/my_page/add/'';\r\n    else return ''modules/boonex/ads/classifieds.php?action=my_page&mode=add'';\r\nelse\r\n    return null;\r\n', 1, 'bx_ads', 1),
(NULL, '_bx_ads_My_Ads', 'money', '{evalResult}', '', 'if ({only_menu} == 1)\r\n    if (getParam(''permalinks_module_ads'') == ''on'') return ''ads/my_page/'';\r\n    else return ''modules/boonex/ads/classifieds.php?action=my_page'';\r\nelse\r\n    return null;\r\n', 2, 'bx_ads', 1),

(NULL, '{evalResult}', 'shopping-cart', '', 'document.forms[''BuyNowForm''].submit();', '$bBnp = getParam(''bx_ads_enable_paid'');\r\nif ({visitor_id} > 0 && {visitor_id} != {owner_id} && $bBnp==''on'') {\r\nreturn _t(''_bx_ads_Buy_Now'');\r\n}\r\nelse\r\nreturn null;', 4, 'bx_ads', 0),
(NULL, '{evalResult}', 'envelope', '', 'document.forms[''post_pm''].submit();', 'if ({visitor_id} > 0 && {visitor_id} != {owner_id}) {\r\nreturn _t(''_Send Message'');\r\n}\r\nelse\r\nreturn null;', 5, 'bx_ads', 0),
(NULL, '_Edit', 'edit', '{evalResult}', '', 'if (({visitor_id} > 0 && {visitor_id} == {owner_id}) || ({admin_mode}==true)) {\r\n    return (getParam(''permalinks_module_ads'') == ''on'') ? ''ads/my_page/edit/{ads_id}'' : ''modules/boonex/ads/classifieds.php?action=my_page&mode=add&EditPostID={ads_id}'';\r\n} else return null;', 6, 'bx_ads', 0),
(NULL, '{evalResult}', 'remove', '', 'iDelAdID = {ads_id}; if (confirm(''{sure_label}'')) { $(''#DeleteAdvertisementID'').val(iDelAdID);document.forms.command_delete_advertisement.submit(); }', '$oModule = BxDolModule::getInstance(''BxAdsModule'');\r\n if (({visitor_id} > 0 && {visitor_id} == {owner_id}) || ({admin_mode}==true) || $oModule->isAllowedDelete({owner_id})) {\r\nreturn _t(''_Delete'');\r\n}\r\nelse\r\nreturn null;', 7, 'bx_ads', 0),
(NULL, '_bx_ads_RSS', 'rss', 'rss_factory.php?action=ads&pid={owner_id}', '', '', 8, 'bx_ads', 0),
(NULL, '{evalResult}', 'ok-circle', '', '$(''#ActivateAdvertisementID'').val(''{ads_id}'');document.forms.command_activate_advertisement.submit(); return false;', 'if ({admin_mode}==true && ''{ads_status}''!=''active'') {\r\nreturn _t(''_bx_ads_Activate'');\r\n}\r\nelse\r\nreturn null;', 9, 'bx_ads', 0),
(NULL, '{evalResult}', 'star-empty', '{ads_entry_url}&do=cfs', '', '$iAdsFeature = (int)''{ads_featured}'';\r\nif ({admin_mode}==true) {\r\nreturn ($iAdsFeature==1) ? _t(''_bx_ads_De-Feature_it'') : _t(''_bx_ads_Feature_it'');\r\n}\r\nelse\r\nreturn null;', 10, 'bx_ads', 0),
(NULL, '{sbs_ads_title}', 'paper-clip', '', '{sbs_ads_script}', '', 11, 'bx_ads', 0);


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_ads' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_ads' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
UPDATE `sys_sbs_types` SET `unit` = 'ads' WHERE `unit` = 'bx_ads';
OPTIMIZE TABLE `sys_sbs_types`;

-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('t_sbsAdsRates', 't_sbsAdsComments', 't_BuyNow', 't_BuyNowS');
INSERT INTO `sys_email_templates`(`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_sbsAdsComments', 'New Comments To An Ad', '<bx_include_auto:_email_header.html />\r\n\r\n<p><b>Dear <RealName></b>,</p>\r\n\r\n<p>The <a href="<ViewLink>">ad you subscribed to got new comments</a>!</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: new comments to ad', 0),
('t_BuyNow', 'Your Purchase', '<bx_include_auto:_email_header.html />\r\n\r\nItem: <a href="<ShowAdvLnk>"><Subject></a><br/><br/>\r\n\r\nSeller name: <NickName><br/>\r\nSeller email: <EmailS><br/><br/>\r\n\r\nBuyer name: <NickNameB><br/>\r\nBuyer email: <EmailB><br/><br/>\r\n\r\nPrice details: <sCustDetails><br/><br/>\r\n\r\nContact the <Who> directly to arrange payment and delivery. \r\n\r\n<bx_include_auto:_email_footer.html />', 'Purchase notification', 0),
('t_BuyNowS', 'Your Item Was Purchased', '<bx_include_auto:_email_header.html />\r\n\r\nItem: <a href="<ShowAdvLnk>"><Subject></a><br/><br/>\r\n\r\nSeller name: <NickName><br/>\r\nSeller email: <EmailS><br/><br/>\r\n\r\nBuyer name: <NickNameB><br/>\r\nBuyer email: <EmailB><br/><br/><br/>\r\n\r\nPrice details: <sCustDetails><br/><br/>\r\n\r\nContact the <Who> directly to arrange payment and delivery. \r\n\r\n<bx_include_auto:_email_footer.html />', 'Seller notification about a purchase', 0);


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_ads';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_ads', '_bx_ads_Ads', '0.8', 'auto', 'BxAdsSiteMaps', 'modules/boonex/ads/classes/BxAdsSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_ads';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_ads', '_bx_ads_Ads', 'bx_ads_main', 'DateTime', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_ads_CustomField1','_bx_ads_CustomField2','_bx_ads_Description','_bx_ads_Offer_Details','_bx_ads_Sub_Categories','_bx_ads_Under_Development','_bx_ads_bigger','_bx_ads_choose_custom_action','_bx_ads_custom_error_desc','_bx_ads_equal','_bx_ads_sbs_main','_bx_ads_sbs_rates','_bx_ads_smaller','_bx_ads_user_bought_ad','_bx_ads_user_posted_ad_comment','_bx_ads_wall_photo','_sbs_txt_title_bx_ads');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_ads_CustomField1','_bx_ads_CustomField2','_bx_ads_Description','_bx_ads_Offer_Details','_bx_ads_Sub_Categories','_bx_ads_Under_Development','_bx_ads_bigger','_bx_ads_choose_custom_action','_bx_ads_custom_error_desc','_bx_ads_equal','_bx_ads_sbs_main','_bx_ads_sbs_rates','_bx_ads_smaller','_bx_ads_user_bought_ad','_bx_ads_user_posted_ad_comment','_bx_ads_wall_photo','_sbs_txt_title_bx_ads');
        


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'ads' AND `version` = '1.0.9';

