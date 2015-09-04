

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'bx_poll';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES
('bx_poll', 'bx_poll_cmts', 'bx_poll_cmts_track', 0, 1, 90, 5, 1, -3, 'none', 0, 1, 0, 'cmt', 'bx_poll_data', 'id_poll', 'poll_comments_count', 'BxPollCmts', 'modules/boonex/poll/classes/BxPollCmts.php');


