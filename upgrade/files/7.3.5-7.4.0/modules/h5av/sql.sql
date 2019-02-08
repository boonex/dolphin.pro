

SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_h5av_video_embed' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_h5av_video_embed', '', '', 'BxDolService::call(''h5av'', ''response_video_embed'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'bx_videos', 'embed_code', @iHandler);


SET @iHandler := (SELECT `id` FROM `sys_alerts_handlers` WHERE `name` = 'bx_h5av_audio_embed' LIMIT 1);
DELETE FROM `sys_alerts` WHERE `handler_id` = @iHandler;
DELETE FROM `sys_alerts_handlers` WHERE `id` = @iHandler;

INSERT INTO `sys_alerts_handlers` VALUES (NULL, 'bx_h5av_audio_embed', '', '', 'BxDolService::call(''h5av'', ''response_audio_embed'', array($this));');
SET @iHandler := LAST_INSERT_ID();
INSERT INTO `sys_alerts` VALUES (NULL , 'bx_sounds', 'embed_code', @iHandler);


-- update module version

UPDATE `sys_modules` SET `version` = '1.4.0' WHERE `uri` = 'h5av' AND `version` = '1.3.5';

