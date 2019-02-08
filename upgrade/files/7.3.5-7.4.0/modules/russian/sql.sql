

SET @iLangId = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = 'ru');
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_ExportReady', '<SiteName> экспорт данных готов', '<bx_include_auto:_email_header.html />\r\n\r\n\r\n<p><b>Дорогой <RealName></b>,</p>\r\n\r\n<p>Данные могут быть скачаны по следующей ссылке:</p>\r\n\r\n<p><FileUrl></p>\r\n\r\n<p>Ссылка будет доступна в течение 24 часов.</p>\r\n\r\n\r\n<bx_include_auto:_email_footer.html />', 'Уведомление об экспорте данных', @iLangId);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'russian' AND `version` = '1.3.5';

