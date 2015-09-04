
DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'ads';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES
('ads', 'bx_ads_cmts', 'sys_cmts_track', 0, 1, 90, 5, 1, -3, 'none', 0, 1, 0, 'cmt', 'bx_ads_main', 'ID', 'CommentsCount', 'BxAdsCmts', 'modules/boonex/ads/classes/BxAdsCmts.php');

