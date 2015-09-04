<?php
/**
 * FileUtils.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class is an utility class for handling file stuff.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_FileUtils {
	/**
	 * Get an unique file
	 *
	 * @param MOXMAN_Vfs_IFile $file File object to check against
	 * @return MOXMAN_Vfs_IFile Unique file object.
	 */
	public static function uniqueFile(MOXMAN_Vfs_IFile $file) {
		$fileName = $file->getName();
		$ext = MOXMAN_Util_PathUtils::getExtension($fileName);
		for ($i = 2; $file->exists(); $i++) {
			if ($file->isFile() && $ext) {
				$file = MOXMAN::getFile($file->getParent(), basename($fileName, '.' . $ext) . '_' . $i . '.' . $ext);
			} else {
				$file = MOXMAN::getFile($file->getParent(), $fileName . '_' . $i);
			}
		}

		return $file;
	}
}