
DELETE FROM `sys_objects_cmts` WHERE `ObjectName` = 'bx_blogs';
INSERT INTO `sys_objects_cmts` (`ObjectName`, `TableCmts`, `TableTrack`, `AllowTags`, `Nl2br`, `SecToEdit`, `PerView`, `IsRatable`, `ViewingThreshold`, `AnimationEffect`, `AnimationSpeed`, `IsOn`, `IsMood`, `RootStylePrefix`, `TriggerTable`, `TriggerFieldId`, `TriggerFieldComments`, `ClassName`, `ClassFile`) 
VALUES('bx_blogs', 'bx_blogs_cmts', 'sys_cmts_track', 0, 1, 90, 5, 1, -3, 'none', 0, 1, 0, 'cmt', 'bx_blogs_posts', 'PostID', 'CommentsCount', 'BxBlogsCmts', 'modules/boonex/blogs/classes/BxBlogsCmts.php');

