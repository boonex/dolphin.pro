

ALTER TABLE  `Profiles` DROP INDEX  `NickName_2`;
ALTER TABLE  `Profiles` ADD FULLTEXT KEY `NickName_2` (`NickName`,`FullName`,`FirstName`,`LastName`,`City`,`DescriptionMe`,`Tags`);


ALTER TABLE `RayMp3Files` ADD KEY `Uri` (`Uri`);

ALTER TABLE `RayVideoFiles` ADD KEY `Uri` (`Uri`);

ALTER TABLE `RayVideo_commentsFiles` ADD KEY `Uri` (`Uri`);


-- last step is to update current version

UPDATE `sys_options` SET `VALUE` = '7.3.4' WHERE `Name` = 'sys_tmp_version';

