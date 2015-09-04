<?php
/**
 * BaseFile.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Abstract BaseFile implementation this can be used by other
 * file systems to get basic functionallity for the file implementation.
 *
 * @package MOXMAN_Vfs
 */
abstract class MOXMAN_Vfs_BaseFile implements MOXMAN_Vfs_IFile {
	/**
	 * Absolute file path. Only to be used on the backend.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Public file path. Can be used on the client.
	 * @var [type]
	 */
	protected $publicPath;

	/**
	 * Url based on the file object.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Config instance for the file.
	 *
	 * @var MOXMAN_Util_Config
	 */
	protected $config;

	/**
	 * File system implementation that created the file.
	 *
	 * @var MOXMAN_Vfs_FileSystem
	 */
	protected $fileSystem;

	/**
	 * Meta data for the file.
	 *
	 * @var MOXMAN_Vfs_FileMetaData
	 */
	protected $meta;

	/**
	 * Creates a new absolute file.
	 *
	 * @param MOXMAN_Vfs_FileSystem $fileSystem MCManager reference.
	 * @param string $path Absolute path to local file.
	 */
	public function __construct($fileSystem, $path) {
		$this->fileSystem = $fileSystem;
		$this->path = $path;
	}

	/**
	 * Returns the file system that created the file.
	 *
	 * @return MOXMAN_FileSystem File system instance that created the file.
	 */
	public function getFileSystem() {
		return $this->fileSystem;
	}

	/**
	 * Returns true if the file is a directory.
	 *
	 * @return boolean true if the file is a directory.
	 */
	public function isDirectory() {
		return !$this->isFile();
	}

	/**
	 * Returns true if the file is a file.
	 *
	 * @return boolean true if the file is a file.
	 */
	public function isFile() {
		throw new Exception("Method not implemented (isFile).");
	}

	/**
	 * Returns true if the file is hidden.
	 *
	 * @return boolean true if the file is a hidden file.
	 */
	public function isHidden() {
		return false;
	}

	/**
	 * Creates a new directory.
	 */
	public function mkdir() {
		throw new Exception("Method not implemented (mkdir).");
	}

	/**
	 * Renames/Moves this file to the specified file instance.
	 *
	 * @param MOXMAN_Vfs_IFile $dest File to rename/move to.
	 */
	public function moveTo(MOXMAN_Vfs_IFile $dest) {
		throw new Exception("Method not implemented (moveTo).");
	}

	/**
	 * Copies this file to the specified file instance.
	 *
	 * @param MOXMAN_Vfs_IFile $dest File to copy to.
	 */
	public function copyTo(MOXMAN_Vfs_IFile $dest) {
		throw new Exception("Method not implemented (copyTo).");
	}

	/**
	 * Deletes the file.
	 *
	 * @param boolean $deep If this option is enabled files will be deleted recurive.
	 */
	public function delete($deep = false) {
		throw new Exception("Method not implemented (delete).");
	}

	/**
	 * Returns file size as an long.
	 *
	 * @return long file size as an long.
	 */
	public function getSize() {
		return 0;
	}

	/**
	 * Returns last modification date in ms as an long.
	 *
	 * @return long last modification date in ms as an long.
	 */
	public function getLastModified() {
		return 0;
	}

	/**
	 * Returns the parent files absolute path or an empty string if there is no parent.
	 *
	 * @return String parent files absolute path.
	 */
	public function getParent() {
		$path = MOXMAN_Util_PathUtils::getParent($this->getPath());

		// If path is out side the file systems root path
		if (!MOXMAN_Util_PathUtils::isChildOf($path, $this->fileSystem->getRootPath())) {
			return "";
		}

		return $path;
	}

	/**
	 * Returns the parent files File instance.
	 *
	 * @return File parent files File instance or false if there is no more parents.
	 */
	public function getParentFile() {
		$parentPath = $this->getParent();

		// There is no parent path then return null
		if (!$parentPath) {
			$null = null;
			return $null;
		}

		return $this->fileSystem->getFile($parentPath);
	}

	/**
	 * Returns the file name of a file.
	 *
	 * @return string File name of file.
	 */
	public function getName() {
		return basename($this->path);
	}

	/**
	 * Returns the absolute path of the file.
	 *
	 * @return String absolute path of the file.
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Returns the public path for a file. A public path is a path that is safe
	 * to pass to the client side since it doesn't show the systems full path.
	 *
	 * @return String Public path for the file to be passed out to the client.
	 */
	public function getPublicPath() {
		if (!$this->publicPath) {
			// Use absolute path if debug mode is enabled or user_friendly_paths is off
			/*if (!$this->fileSystem->getConfig()->get("general.user_friendly_paths")) {
				return $this->getPath();
			}*/

			$rootName = $this->fileSystem->getRootName();
			$this->publicPath = MOXMAN_Util_PathUtils::combine(
				$rootName !== "/" ? "/" . $rootName : $rootName,
				substr($this->path, strlen($this->fileSystem->getRootPath()))
			);
		}

		return $this->publicPath;
	}

	/**
	 * Returns the public path of a file that this file points to.
	 *
	 * @return String Public link path or empty string if it doesn't have a link.
	 */
	public function getPublicLinkPath() {
		return "";
	}

	/**
	 * Returns an array of File instances.
	 *
	 * @return MOXMAN_Vfs_FileList List of MOXMAN_Vfs_IFile instances.
	 */
	public function listFiles() {
		return $this->listFilesFiltered(new MOXMAN_Vfs_BasicFileFilter());
	}

	/**
	 * Returns an array of MOXMAN_Vfs_IFile instances based on the specified filter instance.
	 *
	 * @param MOXMAN_Vfs_IFileFilter $filter MOXMAN_Vfs_IFileFilter instance to filter files by.
	 * @return MOXMAN_Vfs_FileList List of MOXMAN_Vfs_IFile instances.
	 */
	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter) {
		throw new Exception("Method listFilesFiltered not implemented.");
	}

	/**
	 * Returns true if the files is readable.
	 *
	 * @return boolean true if the files is readable.
	 */
	public function canRead() {
		return $this->getConfig()->get("filesystem.readable", true);
	}

	/**
	 * Returns true if the files is writable.
	 *
	 * @return boolean true if the files is writable.
	 */
	public function canWrite() {
		return $this->getConfig()->get("filesystem.writable", true);
	}

	/**
	 * Returns the public URL for the file.
	 *
	 * @return String Public URL for the file.
	 */
	public function getUrl() {
		// Use cached url
		if ($this->url) {
			return $this->url;
		}

		// Get config from file system config provider
		$urlProvider = $this->fileSystem->getFileUrlProvider();
		if ($urlProvider) {
			// Get the config for the file and cache it
			$this->url = $urlProvider->getUrl($this);
			return $this->url;
		}

		return "";
	}

	/**
	 * Returns a config instance for the current file. The config is provided by the FileConfigProvider
	 * specified for the file system.
	 *
	 * @return MOXMAN_Util_Config Config instance for the file.
	 */
	public function getConfig() {
		// Use cached config
		if ($this->config) {
			return $this->config;
		}

		// Get config from file system config provider
		$configProvider = $this->fileSystem->getFileConfigProvider();
		if ($configProvider) {
			// Get the config for the file and cache it
			$this->config = $configProvider->getConfig($this);
			return $this->config;
		}

		// Config provider not found then pass out the config we have for the file system
		return $this->fileSystem->getConfig()->getFileConfig($this);
	}

	/**
	 * Returns a meta data instance for the current file.
	 *
	 * @return MOXMAN_Vfs_FileMetaData Meta data instance for the file.
	 */
	public function getMetaData() {
		if (!$this->meta) {
			$provider = $this->fileSystem->getFileMetaDataProvider();
			$this->meta = $provider->getMetaData($this);
		}

		return $this->meta;
	}

	/**
	 * Opens a file stream by the specified mode. The default mode is rb.
	 *
	 * @param string $mode Mode to open file by, r, rb, w, wb etc.
	 * @return MOXMAN_Vfs_IFileStream File stream implementation for the file system.
	 */
	public function open($mode = MOXMAN_Vfs_IFileStream::READ) {
		throw new Exception("Method open not implemented.");
	}

	/**
	 * Exports the current file to the specified local path.
	 *
	 * @param string $localPath This is a description
	 */
	public function exportTo($localPath) {
		throw new Exception("Method exportTo not implemented.");
	}

	/**
	 * Imports the specified local file to the current file instance.
	 *
	 * @param string $localPath This is a description
	 */
	public function importFrom($localPath) {
		throw new Exception("Method importFrom not implemented.");
	}

	/**
	 * Returns true if the file exists.
	 *
	 * @return boolean true if the file exists.
	 */
	public function exists() {
		throw new Exception("Method exists not implemented.");
	}
}
?>