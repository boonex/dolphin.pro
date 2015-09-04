

DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'bx_sounds' OR `ObjectName` = 'bx_sounds_albums';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) VALUES
('bx_sounds', 'bx_sounds_cmts', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', 'RayMp3Files', 'ID', 'CommentsCount', 'BxSoundsCmts', 'modules/boonex/sounds/classes/BxSoundsCmts.php'),
('bx_sounds_albums', 'bx_sounds_cmts_albums', 'sys_cmts_track', 0, 1, 90, 20, 1, -3, 'none', 0, 1, 0, 'cmt', '', '', '', 'BxSoundsCmtsAlbums', 'modules/boonex/sounds/classes/BxSoundsCmtsAlbums.php');


