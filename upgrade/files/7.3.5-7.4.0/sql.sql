

UPDATE `sys_menu_member` SET `Eval` = 'return array(''evalResultCssClassWrapper'' => ''extra_item_add_content'');' WHERE `Name` = 'AddContent';


SET @iMaxId = (SELECT MAX(`ID`) FROM `sys_acl_actions`);
UPDATE `sys_acl_actions` SET `ID` = @iMaxId + 1 WHERE `ID` = 10;

DELETE FROM `sys_acl_actions` WHERE `Name` = 'send friend request';
INSERT INTO `sys_acl_actions` VALUES(10, 'send friend request', NULL);

DELETE FROM `sys_acl_matrix` WHERE `IDAction` = 10;
INSERT INTO `sys_acl_matrix` VALUES(2, 10, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sys_acl_matrix` VALUES(3, 10, NULL, NULL, NULL, NULL, NULL);


DELETE FROM `sys_objects_actions` WHERE `Type` = 'Profile' AND `Icon` = 'edit' AND `Url` = 'pedit.php?ID={ID}';
INSERT INTO `sys_objects_actions` (`Caption`, `Icon`, `Url`, `Script`, `Eval`, `Order`, `Type`, `bDisplayInSubMenuHeader`) VALUES
('{evalResult}', 'edit', 'pedit.php?ID={ID}', '', 'if ({ID} == {member_id} || isAdmin({member_id}) || isModerator({member_id})) return _t(''{cpt_edit}'');', 1, 'Profile', 0);


CREATE TABLE IF NOT EXISTS `sys_objects_exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `class_file` varchar(255) NOT NULL,
  `order` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`object`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

TRUNCATE TABLE `sys_objects_exports`;

INSERT INTO `sys_objects_exports` (`object`, `title`, `class_name`, `class_file`, `order`, `active`) VALUES
('profiles', '_Profiles', 'BxDolExportProfile', '', 1, 1),
('flash', '_adm_admtools_Flash', 'BxDolExportFlash', '', 2, 1);


-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.4.0' WHERE `Name` = 'sys_tmp_version';

