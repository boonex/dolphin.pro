

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'bx_files' OR `ObjectName` = 'bx_files_albums';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES 
('bx_files', 'bx_files_cmts', 'sys_cmts_track', 0, 1, 90, 5, 1, -3, 'none', 0, 1, 0, 'cmt', 'bx_files_main', 'ID', 'CommentsCount', 'BxFilesCmts', 'modules/boonex/files/classes/BxFilesCmts.php'),
('bx_files_albums', 'bx_files_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '', 'BxFilesCmtsAlbums', 'modules/boonex/files/classes/BxFilesCmtsAlbums.php');


