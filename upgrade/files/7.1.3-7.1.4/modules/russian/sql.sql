

SET @iLangId = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = 'ru');
DELETE FROM `sys_email_templates` WHERE `Name` = 't_ModulesUpdates' AND `LangID` = @iLangId;
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_ModulesUpdates', '<SiteName> обновления для модулей', '<bx_include_auto:_email_header.html />\r\n\r\n<p>Доступные обновления для модулей:</p>\r\n\r\n<p><MessageText></p>\r\n\r\n<p>Чтобы установить обновления, нужно зайти в панель администратора -> Модули -> Добавить & Настроить -> Проверить на обновления. Все доступные обновления будут загружены автоматически.</p>\r\n\r\n<bx_include_auto:_email_footer.html />', 'Сообщение администратору об обновлениях для модулей', @iLangId);


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.4' WHERE `uri` = 'russian' AND `version` = '1.1.3';

