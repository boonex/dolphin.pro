<?php
/**
 * IFile.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface is to be implemented by any file instance class.
 *
 * @package MOXMAN_Vfs
 */
interface MOXMAN_Vfs_IFile {
	/**
	 * Returns the file system that created the file.
	 *
	 * @return MOXMAN_FileSystem File system instance that created the file.
	 */
	public function getFileSystem();

	/**
	 * Returns the parent files absolute path.
	 *
	 * @return String parent files absolute path.
	 */
	public function getParent();

	/**
	 * Returns the parent files MOXMAN_Vfs_IFile instance.
	 *
	 * @return MOXMAN_Vfs_IFile parent file instance or false if there is no more parents.
	 */
	public function getParentFile();

	/**
	 * Returns the file name of a file.
	 *
	 * @return string File name of file.
	 */
	public function getName();

	/**
	 * Returns the absolute path of the file.
	 *
	 * @return String absolute path of the file.
	 */
	public function getPath();

	/**
	 * Returns the public path for a file. A public path is a path that is safe
	 * to pass to the client side since it doesn't show the systems full path.
	 *
	 * @return String Public path for the file to be passed out to the client.
	 */
	public function getPublicPath();

	/**
	 * Returns the public path of a file that this file points to.
	 *
	 * @return String Public link path or empty string if it doesn't have a link.
	 */
	public function getPublicLinkPath();

	/**
	 * Returns the absolute public URL for the file.
	 *
	 * @return String Absolute public URL for the file.
	 */
	public function getUrl();

	/**
	 * Imports a local file to the file system, for example when users upload files.
	 * Implementations of this method should also support directory recursive importing.
	 *
	 * @param string $localPath Absolute path to local file.
	 */
	public function importFrom($localPath);

	/**
	 * Exports the file to the local system, for example a file from a zip or db file system.
	 * Implementations of this method should also support directory recursive exporting.
	 *
	 * @param string $localPath Absolute path to local file.
	 */
	public function exportTo($localPath);

	/**
	 * Returns true if the file exists.
	 *
	 * @return boolean true if the file exists.
	 */
	public function exists();

	/**
	 * Returns true if the file is a directory.
	 *
	 * @return boolean true if the file is a directory.
	 */
	public function isDirectory();

	/**
	 * Returns true if the file is a file.
	 *
	 * @return boolean true if the file is a file.
	 */
	public function isFile();

	/**
	 * Returns true if the file is hidden.
	 *
	 * @return boolean true if the file is a hidden file.
	 */
	public function isHidden();

	/**
	 * Returns true if the files is readable.
	 *
	 * @return boolean true if the files is readable.
	 */
	public function canRead();

	/**
	 * Returns true if the files is writable.
	 *
	 * @return boolean true if the files is writable.
	 */
	public function canWrite();

	/**
	 * Returns file size as an long.
	 *
	 * @return long file size as an long.
	 */
	public function getSize();

	/**
	 * Returns last modification date in ms as an long.
	 *
	 * @return long last modification date in ms as an long.
	 */
	public function getLastModified();

	/**
	 * Copies this file to the specified file instance.
	 *
	 * @param MOXMAN_Vfs_IFile $dest File to copy to.
	 */
	public function copyTo(MOXMAN_Vfs_IFile $dest);

	/**
	 * Moves this file to the specified file instance.
	 *
	 * @param MOXMAN_Vfs_IFile $dest File to rename/move to.
	 */
	public function moveTo(MOXMAN_Vfs_IFile $dest);

	/**
	 * Deletes the file.
	 *
	 * @param boolean $deep If this option is enabled files will be deleted recurive.
	 */
	public function delete($deep = false);

	/**
	 * Returns an array of MOXMAN_Vfs_IFile instances.
	 *
	 * @return MOXMAN_Vfs_FileList List of MOXMAN_Vfs_IFile instances.
	 */
	public function listFiles();

	/**
	 * Returns an array of MOXMAN_Vfs_IFile instances based on the specified filter instance.
	 *
	 * @param MOXMAN_Vfs_IFileFilter $filter MOXMAN_Vfs_IFileFilter instance to filter files by.
	 * @return MOXMAN_Vfs_FileList List of MOXMAN_Vfs_IFile instances.
	 */
	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter);

	/**
	 * Creates a new directory.
	 */
	public function mkdir();

	/**
	 * Opens a file stream by the specified mode. The default mode is rb.
	 *
	 * @param string $mode Mode to open file by, r, rb, w, wb etc.
	 * @return MOXMAN_Vfs_IFileStream File stream implementation for the file system.
	 */
	public function open($mode = MOXMAN_Vfs_IFileStream::READ);

	/**
	 * Returns a config instance for the current file or null if it's not available.
	 *
	 * @return MOXMAN_Util_Config Config instance for the file.
	 */
	public function getConfig();

	/**
	 * Returns a meta data instance for the current file.
	 *
	 * @return MOXMAN_Vfs_FileMetaData Meta data instance for the file.
	 */
	public function getMetaData();
}

?>