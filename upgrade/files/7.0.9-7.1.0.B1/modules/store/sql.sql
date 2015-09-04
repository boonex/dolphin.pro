
-- page builder 

DELETE FROM `sys_page_compose` WHERE `Page` IN ('index', 'profile') AND `Desc` IN ('Store', 'User Store');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_store_view' AND `Func` IN ('Actions', 'Rate', 'Info', 'Files', 'Desc', 'Photo', 'Video', 'Comments', 'SocialSharing');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_store_main' AND `Func` IN ('LatestFeaturedProduct', 'Recent', 'Categories', 'Tags');
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_store_my' AND `Func` IN ('Owner', 'Browse');
UPDATE `sys_page_compose` SET `Column` = 0, `Order` = 0 WHERE `Page` IN ('bx_store_view', 'bx_store_main', 'bx_store_my');

INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`) VALUES 
    ('bx_store_view', '1140px', 'Product''s info block', '_bx_store_block_info', 2, 0, 'Info', '', '1', 28.1, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s actions block', '_bx_store_block_actions', 2, 1, 'Actions', '', '1', 28.1, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s items block', '_bx_store_block_items', 2, 2, 'Files', '', '1', 28.1, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s rate block', '_bx_store_block_rate', 2, 3, 'Rate', '', '1', 28.1, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s social sharing block', '_sys_block_title_social_sharing', 2, 4, 'SocialSharing', '', '1', 28.1, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s description block', '_bx_store_block_desc', 1, 0, 'Desc', '', '1', 71.9, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s photo block', '_bx_store_block_photo', 1, 1, 'Photo', '', '1', 71.9, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s videos block', '_bx_store_block_video', 1, 2, 'Video', '', '1', 71.9, 'non,memb', '0'),
    ('bx_store_view', '1140px', 'Product''s comments block', '_bx_store_block_comments', 1, 3, 'Comments', '', '1', 71.9, 'non,memb', '0'),

    ('bx_store_main', '1140px', 'Latest Featured Product', '_bx_store_block_latest_featured_product', 1, 0, 'LatestFeaturedProduct', '', '1', 71.9, 'non,memb', '0'),
    ('bx_store_main', '1140px', 'Recent products', '_bx_store_block_recent', 1, 1, 'Recent', '', '1', 71.9, 'non,memb', '0'),
    ('bx_store_main', '1140px', 'Product Categories', '_bx_store_block_categories', 2, 0, 'Categories', '', '1', 28.1, 'non,memb', '0'),
    ('bx_store_main', '1140px', 'Product Tags', '_bx_store_block_tags', 2, 1, 'Tags', '', '1', 28.1, 'non,memb', '0'),

    ('bx_store_my', '1140px', 'Administration Owner', '_bx_store_block_administration_owner', '1', '0', 'Owner', '', '1', '100', 'non,memb', '0'),
    ('bx_store_my', '1140px', 'User''s products', '_bx_store_block_users_products', '1', '1', 'Browse', '', '0', '100', 'non,memb', '0'),

    ('index', '1140px', 'Store', '_bx_store_block_homepage', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''store'', ''homepage_block'');', 1, 66, 'non,memb', 0),
    ('profile', '1140px', 'User Store', '_bx_store_block_my_products', 0, 0, 'PHP', 'bx_import(''BxDolService''); return BxDolService::call(''store'', ''profile_block'', array($this->oProfileGen->_iProfileID));', 1, 34, 'non,memb', 0);


-- objects: actions

DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_store' AND `Caption` IN ('{TitleEdit}', '{TitleDelete}', '{TitleShare}', '{TitleBroadcast}', '{AddToFeatured}', '{TitleSubscribe}');
DELETE FROM `sys_objects_actions` WHERE `Type` = 'bx_store_title' AND (`Eval` LIKE '%_bx_store_action_add_product%' OR `Eval` LIKE '%_bx_store_action_my_products%' OR `Eval` LIKE '%_bx_store_action_store_home%');

INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`) VALUES 
    ('{TitleEdit}', 'edit', '{evalResult}', '', '$oConfig = $GLOBALS[''oBxStoreModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''edit/{ID}'';', '0', 'bx_store'),
    ('{TitleDelete}', 'remove', '', 'getHtmlData(''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'', true); return false;', '$oConfig = $GLOBALS[''oBxStoreModule'']->_oConfig; return  BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''delete/{ID}'';', '1', 'bx_store'),
    ('{TitleShare}', 'share', '', 'showPopupAnyHtml (''{BaseUri}share_popup/{ID}'');', '', '4', 'bx_store'),
    ('{TitleBroadcast}', 'envelope', '{BaseUri}broadcast/{ID}', '', '', '5', 'bx_store'),
    ('{AddToFeatured}', 'star-empty', '', 'getHtmlData( ''ajaxy_popup_result_div_{ID}'', ''{evalResult}'', false, ''post'');return false;', '$oConfig = $GLOBALS[''oBxStoreModule'']->_oConfig; return BX_DOL_URL_ROOT . $oConfig->getBaseUri() . ''mark_featured/{ID}'';', 6, 'bx_store'),
    ('{TitleSubscribe}', 'paper-clip', '', '{ScriptSubscribe}', '', 7, 'bx_store'),
    ('{evalResult}', 'plus', '{BaseUri}browse/my&bx_store_filter=add_product', '', 'return ($GLOBALS[''logged''][''member''] && BxDolModule::getInstance(''BxStoreModule'')->isAllowedAdd()) || $GLOBALS[''logged''][''admin''] ? _t(''_bx_store_action_add_product'') : '''';', 1, 'bx_store_title'),
    ('{evalResult}', 'shopping-cart', '{BaseUri}browse/my', '', 'return $GLOBALS[''logged''][''member''] || $GLOBALS[''logged''][''admin''] ? _t(''_bx_store_action_my_products'') : '''';', '2', 'bx_store_title');


-- menu top

SET @iMenuStoreSystem = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'system' AND `Name` = 'Store');
UPDATE `sys_menu_top` SET `Picture` = 'shopping-cart' WHERE `ID` = @iMenuStoreSystem;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuStoreSystem;
UPDATE `sys_menu_top` SET `Check` = '$oModuleDb = new BxDolModuleDb(); return $oModuleDb->getModuleByUri(''forum'') ? true : false;' WHERE `Parent` = @iMenuStoreSystem AND `Name` = 'Store View Forum';

SET @iMenuStoreTop = (SELECT `ID` FROM `sys_menu_top` WHERE `Parent` = 0 AND `Type` = 'top' AND `Name` = 'Store');
UPDATE `sys_menu_top` SET `Picture` = 'shopping-cart', `Icon` = 'shopping-cart' WHERE `ID` = @iMenuStoreTop;
UPDATE `sys_menu_top` SET `Picture` = '' WHERE `Parent` = @iMenuStoreTop;
UPDATE `sys_menu_top` SET `Link` = 'modules/?r=store/browse/user/{profileUsername}' WHERE `Parent` = 9 AND `Name` = 'Store';


-- menu member 

DELETE FROM `sys_menu_member` WHERE `Name` = 'bx_store';
SET @iMemberMenuParent = (SELECT `ID` FROM `sys_menu_member` WHERE `Name` = 'AddContent');
SET @iMemberMenuOrder = (SELECT MAX(`Order`) + 1 FROM `sys_menu_member` WHERE `Parent` = IFNULL(@iMemberMenuParent, -1));
INSERT INTO `sys_menu_member` SET `Name` = 'bx_store', `Eval` = 'return BxDolService::call(''store'', ''get_member_menu_item_add_content'');', `Type` = 'linked_item', `Parent` = IFNULL(@iMemberMenuParent, 0), `Order` = IFNULL(@iMemberMenuOrder, 1);


-- menu admin 

UPDATE `sys_menu_admin` SET `icon` = 'shopping-cart' WHERE `name` = 'bx_store';


-- stats site 

DELETE FROM `sys_stat_site` WHERE `Name` = 'bx_store';
SET @iStatSiteOrder := (SELECT `StatOrder` + 1 FROM `sys_stat_site` WHERE 1 ORDER BY `StatOrder` DESC LIMIT 1);
INSERT INTO `sys_stat_site` VALUES(NULL, 'bx_store', 'bx_store_ss', 'modules/?r=store/browse/recent', 'SELECT COUNT(`id`) FROM `[db_prefix]products` WHERE `status`=''approved''', 'modules/?r=store/administration', 'SELECT COUNT(`id`) FROM `[db_prefix]products` WHERE `status`=''pending''', 'shopping-cart', @iStatSiteOrder);


-- email templates

DELETE FROM `sys_email_templates` WHERE `Name` IN ('bx_store_broadcast', 'bx_store_sbs');

INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES 
('bx_store_broadcast', '<BroadcastTitle>', '<bx_include_auto:_email_header.html />\r\n\r\n <p>Hello <NickName>,</p> \r\n\r\n<p><a href="<EntryUrl>"><EntryTitle></a> product admin has sent the following broadcast message:</p> \r\n<hr>\r\n<BroadcastMessage>\r\n<hr>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Store broadcast message', 0),
('bx_store_sbs', 'Subscription: Product Details Changed', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Hello <NickName>,</p> \r\n\r\n<p><a href="<ViewLink>"><EntryTitle></a> product details changed: <br /> <ActionName> </p> \r\n<hr>\r\n<p>Cancel this subscription: <a href="<UnsubscribeLink>"><UnsubscribeLink></a></p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Subscription: product changes', 0);


-- subscriptions

SET @iSbsTypeRate = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_store' AND `action` = 'rate');
SET @iSbsTypeMain = (SELECT `id` FROM `sys_sbs_types` WHERE `unit` = 'bx_store' AND `action` = '' AND `template` = '');
UPDATE `sys_sbs_entries` SET `subscription_id` = @iSbsTypeMain WHERE `subscription_id` = @iSbsTypeRate;
DELETE FROM `sys_sbs_types` WHERE `id` = @iSbsTypeRate;
OPTIMIZE TABLE `sys_sbs_types`;


-- objects: sitemap

DELETE FROM `sys_objects_site_maps` WHERE `object` = 'bx_store';
SET @iMaxOrderSiteMaps = (SELECT MAX(`order`)+1 FROM `sys_objects_site_maps`);
INSERT INTO `sys_objects_site_maps` (`object`, `title`, `priority`, `changefreq`, `class_name`, `class_file`, `order`, `active`) VALUES
('bx_store', '_bx_store_sitemap', '0.8', 'auto', 'BxStoreSiteMaps', 'modules/boonex/store/classes/BxStoreSiteMaps.php', @iMaxOrderSiteMaps, 1);


-- objects: chart

DELETE FROM `sys_objects_charts` WHERE `object` = 'bx_store';
SET @iMaxOrderCharts = (SELECT MAX(`order`)+1 FROM `sys_objects_charts`);
INSERT INTO `sys_objects_charts` (`object`, `title`, `table`, `field_date_ts`, `field_date_dt`, `query`, `active`, `order`) VALUES
('bx_store', '_bx_store_chart', 'bx_store_products', 'created', '', '', 1, @iMaxOrderCharts);



-- delete unused language keys 

DELETE `sys_localization_strings` FROM `sys_localization_strings`, `sys_localization_keys` WHERE `sys_localization_keys`.`ID` = `sys_localization_strings`.`IDKey` AND `sys_localization_keys`.`Key` IN('_bx_store_action_store_home','_bx_store_add','_bx_store_buy','_bx_store_caption_add','_bx_store_caption_admin_actions','_bx_store_caption_pending_approval','_bx_store_form_caption_author_id','_bx_store_form_info_author','_bx_store_menu_view_photos','_bx_store_menu_view_videos','_bx_store_msg_photo_is_pending_approval','_bx_store_privacy_edit_product');
DELETE FROM `sys_localization_keys` WHERE `Key` IN('_bx_store_action_store_home','_bx_store_add','_bx_store_buy','_bx_store_caption_add','_bx_store_caption_admin_actions','_bx_store_caption_pending_approval','_bx_store_form_caption_author_id','_bx_store_form_info_author','_bx_store_menu_view_photos','_bx_store_menu_view_videos','_bx_store_msg_photo_is_pending_approval','_bx_store_privacy_edit_product');



-- update module version

UPDATE `sys_modules` SET `dependencies` = 'payment,files' WHERE `uri` = 'store';
UPDATE `sys_modules` SET `version` = '1.1.0' WHERE `uri` = 'store' AND `version` = '1.0.9';

