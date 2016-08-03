
SET @iLangId = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = 'ru');

UPDATE `sys_email_templates` SET `Body` = '<bx_include_auto:_email_header.html />\n\n\n<p>Думаю, тебе будет интересно: <a href="<Link>"><Link></a><br />\n---<br />\n<a href="<SenderLink>"><SenderName></a>\n</p>\n\n<bx_include_auto:_email_footer.html />' WHERE `Name` = 't_TellFriend' AND `LangID` = @iLangId;
UPDATE `sys_email_templates` SET `Body` = '<bx_include_auto:_email_header.html />\n\n\n\n<p>Взгляни на этот профиль: <a href="<Link>"><Link></a><br />\n---<br />\n<a href="<SenderLink>"><SenderName></a>\n</p>\n\n<bx_include_auto:_email_footer.html />' WHERE `Name` = 't_TellFriendProfile' AND `LangID` = @iLangId;

-- update module version

UPDATE `sys_modules` SET `version` = '1.3.2' WHERE `uri` = 'russian' AND `version` = '1.3.1';

