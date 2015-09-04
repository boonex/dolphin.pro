
SET @iLangId = (SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = 'ru');

DELETE FROM `sys_email_templates` WHERE `LangID` = @iLangId;

