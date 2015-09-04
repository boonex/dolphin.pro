<?php
/**
 * File.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Indexed file class.
 *
 * @package MOXMAN_Vfs_Cache
 */
class MOXMAN_Vfs_Cache_File implements MOXMAN_Vfs_IFile {
	private $fileSystem, $wrappedFile, $info, $config;

	public function __construct(MOXMAN_Vfs_FileSystem $fileSystem, MOXMAN_Vfs_IFile $file, $info = null) {
		$this->fileSystem = $fileSystem;
		$this->wrappedFile = $file;
		$this->fileInfoStorage = MOXMAN_Vfs_Cache_FileInfoStorage::getInstance();
		$this->info = $info;
	}

	public function getFileSystem() {
		return $this->fileSystem;
	}

	public function getParent() {
		return $this->wrappedFile->getParent();
	}

	public function getParentFile() {
		$file = $this->wrappedFile->getParentFile();
		if ($file) {
			$file = new MOXMAN_Vfs_Cache_File($this->fileSystem, $file);
		}

		return $file;
	}

	public function getName() {
		return $this->wrappedFile->getName();
	}

	public function getPath() {
		return $this->wrappedFile->getPath();
	}

	public function getPublicPath() {
		return $this->wrappedFile->getPublicPath();
	}

	public function getPublicLinkPath() {
		return $this->wrappedFile->getPublicLinkPath();
	}

	public function getUrl() {
		return $this->wrappedFile->getUrl();
	}

	public function exists() {
		$size = $this->getStatItem("size", -1);

		// Can still exist since it might be an non cached empty dir
		if ($size === -1) {
			return $this->wrappedFile->exists();
		}

		return true;
	}

	public function isDirectory() {
		$isDirectory = $this->getStatItem("isDirectory", null);

		// Can still exist since it might be an non cached empty dir
		if ($isDirectory === null) {
			return $this->wrappedFile->isDirectory();
		}

		return $isDirectory;
	}

	public function isFile() {
		return !$this->isDirectory();
	}

	public function isHidden() {
		return $this->wrappedFile->isHidden();
	}

	public function getLastModified() {
		return $this->getStatItem("lastModified", 0);
	}

	public function canRead() {
		return $this->getStatItem("canRead", true);
	}

	public function canWrite() {
		return $this->getStatItem("canWrite", true);
	}

	public function getSize() {
		return $this->getStatItem("size", 0);
	}

	public function moveTo(MOXMAN_Vfs_IFile $dest) {
		if ($dest instanceof MOXMAN_Vfs_Cache_File) {
			$dest = $dest->getWrappedFile();
		}

		$this->wrappedFile->moveTo($dest);
		$this->fileInfoStorage->deleteFile($this->getWrappedFile());
		$this->fileInfoStorage->putFile($dest);
	}

	public function copyTo(MOXMAN_Vfs_IFile $dest) {
		if ($dest instanceof MOXMAN_Vfs_Cache_File) {
			$dest = $dest->getWrappedFile();
		}

		$this->wrappedFile->copyTo($dest);
		$this->fileInfoStorage->putFile($dest);
	}

	public function delete($deep = false) {
		$this->wrappedFile->delete($deep);
		$this->fileInfoStorage->deleteFile($this->getWrappedFile());
	}

	public function listFiles() {
		return $this->listFilesFiltered(new MOXMAN_Vfs_BasicFileFilter());
	}

	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter) {
		return new MOXMAN_Vfs_Cache_FileList($this, $filter);
	}

	public function mkdir() {
		$this->wrappedFile->mkdir();
		$this->fileInfoStorage->putFile($this);
	}

	public function open($mode = MOXMAN_Vfs_IFileStream::READ) {
		return new MOXMAN_Vfs_Cache_FileStream($this, $this->wrappedFile->open($mode), $mode);
	}

	public function exportTo($localPath) {
		return $this->wrappedFile->exportTo($localPath);
	}

	public function importFrom($localPath) {
		$this->wrappedFile->importFrom($localPath);
		$this->fileInfoStorage->putFile($this);
	}

	public function getConfig() {
		// Use cached config
		if ($this->config) {
			return $this->config;
		}

		// Get config from file system config provider
		$configProvider = $this->wrappedFile->getFileSystem()->getFileConfigProvider();
		if ($configProvider) {
			// Get the config for the file and cache it
			$this->config = $configProvider->getConfig($this);
			return $this->config;
		}

		// Config provider not found then pass out the config we have for the file system
		return $this->fileSystem->getConfig()->getFileConfig($this);
	}

	public function getMetaData() {
		return $this->wrappedFile->getMetaData();
	}

	public function getWrappedFile() {
		return $this->wrappedFile;
	}

	public function getFileInfoStorage() {
		return $this->fileInfoStorage;
	}

	private function getStat() {
		if ($this->info) {
			return $this->info;
		}

		$this->info = $this->fileInfoStorage->getInfo($this->wrappedFile);

		if (!$this->info && $this->wrappedFile->exists()) {
			$this->info = $this->fileInfoStorage->putFile($this->wrappedFile);
		}

		return $this->info;
	}

	/**
	 * Returns a stat item or the default value if it wasn't found.
	 *
	 * @param String $key Key of stat item to get.
	 * @param mixed $default Default value to return.
	 * @return mixed Value of stat item or default.
	 */
	private function getStatItem($key, $default = false) {
		$stat = $this->getStat();

		return $stat !== null && array_key_exists($key, $stat) ? $stat[$key] : $default;
	}
}
?>