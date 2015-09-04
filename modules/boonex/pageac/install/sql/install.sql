SET @iOrder = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id`='2');
INSERT INTO `sys_menu_admin`(`parent_id`, `name`, `title`, `url`, `description`, `icon`, `icon_large`, `check`, `order`) VALUES
(2, 'bx_pageac', '_bx_pageac', '{siteUrl}modules/?r=pageac/admin/', 'Page Access Control', 'unlock', '', '', @iOrder+1);

INSERT INTO `sys_permalinks`(`standard`, `permalink`, `check`) VALUES
('modules/?r=pageac/', 'm/pageac/', 'permalinks_module_pageac');

INSERT INTO `sys_options`(`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`) VALUES
('permalinks_module_pageac', 'on', 26, 'Enable user friendly permalinks for Page Access Control module', 'checkbox', '', '', 0);

INSERT INTO `sys_alerts_handlers`(`name`, `eval`) VALUES
('bx_pageac', 'BxDolService::call(''pageac'', ''responce_protect_URL'', array($_SERVER[''REQUEST_URI'']));');
SET @iHandlerID = LAST_INSERT_ID();

INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('system', 'design_included', @iHandlerID);


CREATE TABLE `[db_prefix]rules` (
`ID` INT NOT NULL AUTO_INCREMENT ,
`Rule` TEXT NOT NULL ,
`MemLevels` TEXT,
PRIMARY KEY ( `ID` )
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]top_menu_visibility` (
`MenuItemID` INT NOT NULL,
`MemLevels` TEXT,
PRIMARY KEY ( `MenuItemID` )
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]member_menu_visibility` (
`MenuItemID` INT NOT NULL,
`MemLevels` TEXT,
PRIMARY KEY ( `MenuItemID` )
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `[db_prefix]page_blocks_visibility` (
`PageBlockID` INT NOT NULL,
`MemLevels` TEXT,
PRIMARY KEY ( `PageBlockID` )
)ENGINE=MyISAM  DEFAULT CHARSET=utf8;
