<?php
/**
 * FileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Indexed file system.
 *
 * @package MOXMAN_Vfs_Cache
 */
class MOXMAN_Vfs_Cache_FileSystem extends MOXMAN_Vfs_FileSystem {
	private $wrappedFileSystem, $fileUrlResolver;

	public function __construct(MOXMAN_Vfs_FileSystem $fileSystem) {
		$this->wrappedFileSystem = $fileSystem;
	}

	public function getCache() {
		return $this->wrappedFileSystem->getCache();
	}

	public function getScheme() {
		return $this->wrappedFileSystem->getScheme();
	}

	public function getRootName() {
		return $this->wrappedFileSystem->getRootName();
	}

	public function getRootPath() {
		return $this->wrappedFileSystem->getRootPath();
	}

	public function getRootFile() {
		return new MOXMAN_Vfs_Cache_File($this, $this->wrappedFileSystem->getRootFile());
	}

	public function setFileMetaDataProvider(MOXMAN_Vfs_IFileMetaDataProvider $provider) {
		return $this->wrappedFileSystem->setFileMetaDataProvider($provider);
	}

	public function getFileMetaDataProvider() {
		return $this->wrappedFileSystem->getFileMetaDataProvider();
	}

	public function setFileConfigProvider(MOXMAN_Vfs_IFileConfigProvider $provider) {
		return $this->wrappedFileSystem->setFileConfigProvider($provider);
	}

	public function getFileConfigProvider() {
		return $this->wrappedFileSystem->getFileConfigProvider();
	}

	public function setFileUrlProvider(MOXMAN_Vfs_IFileUrlProvider $provider) {
		$this->wrappedFileSystem->setFileUrlProvider($provider);
	}

	public function getFileUrlProvider() {
		return $this->wrappedFileSystem->getFileUrlProvider();
	}

	public function setFileUrlResolver(MOXMAN_Vfs_IFileUrlResolver $resolver) {
		$this->wrappedFileSystem->setFileUrlResolver($resolver);
	}

	public function getFileUrlResolver() {
		if (!$this->fileUrlResolver) {
			$fileUrlResolver = $this->wrappedFileSystem->getFileUrlResolver();
			if ($fileUrlResolver) {
				$this->fileUrlResolver = new MOXMAN_Vfs_Cache_FileUrlResolver($this, $fileUrlResolver);
			}
		}

		return $this->fileUrlResolver;
	}

	public function getConfig() {
		return $this->wrappedFileSystem->getConfig();
	}

	public function getFile($path, $info = null) {
		$file = $this->wrappedFileSystem->getFile($path);
		if ($file) {
			$file = new MOXMAN_Vfs_Cache_File($this, $file, $info);
		}

		return $file;
	}

	public function close() {
		return $this->wrappedFileSystem->close();
	}

	public function isDatabaseSupported() {
		return MOXMAN_Vfs_Cache_FileInfoStorage::getInstance()->getPdo() !== null;
	}
}
