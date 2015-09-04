

SET @iHandlerId = (SELECT `id` FROM `sys_alerts_handlers` WHERE `name`='bx_payment' LIMIT 1);
DELETE FROM `sys_alerts_handlers` WHERE `id`=@iHandlerId LIMIT 1;
DELETE FROM `sys_alerts` WHERE `handler_id`=@iHandlerId;


INSERT INTO `sys_alerts_handlers`(`name`, `class`, `file`, `eval`) VALUES 
('bx_payment', '', '', 'BxDolService::call(\'payment\', \'response\', array($this));');
SET @iHandlerId = LAST_INSERT_ID();

INSERT INTO `sys_alerts` (`unit`, `action`, `handler_id`) VALUES
('profile', 'delete', @iHandlerId);


-- update module version

UPDATE `sys_modules` SET `version` = '1.1.2' WHERE `uri` = 'payment' AND `version` = '1.1.1';

