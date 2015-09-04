

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'bx_photos' OR `ObjectName` = 'bx_photos_albums';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES
('bx_photos', 'bx_photos_cmts', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', 'bx_photos_main', 'ID', 'CommentsCount', 'BxPhotosCmts', 'modules/boonex/photos/classes/BxPhotosCmts.php'),
('bx_photos_albums', 'bx_photos_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '', 'BxPhotosCmtsAlbums', 'modules/boonex/photos/classes/BxPhotosCmtsAlbums.php');


