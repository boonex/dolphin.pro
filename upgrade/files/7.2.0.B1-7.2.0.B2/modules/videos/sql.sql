

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'bx_videos' OR `ObjectName` = 'bx_videos_albums';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES 
('bx_videos', 'bx_videos_cmts', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', 'RayVideoFiles', 'ID', 'CommentsCount', 'BxVideosCmts', 'modules/boonex/videos/classes/BxVideosCmts.php'),
('bx_videos_albums', 'bx_videos_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '', 'BxVideosCmtsAlbums', 'modules/boonex/videos/classes/BxVideosCmtsAlbums.php');


