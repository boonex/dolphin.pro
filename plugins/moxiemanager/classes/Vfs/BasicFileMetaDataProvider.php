<?php
/**
 * BasicFileMetaDataProvider.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

// @codeCoverageIgnoreStart

/**
 * This is to be implemented by file meta data provider instances. A meta data provider
 * provides meta data instances for specific files.
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_BasicFileMetaDataProvider implements MOXMAN_Vfs_IFileMetaDataProvider {
	/** @ignore */
	private $fileSystem, $metaFileCache;

	/**
	 * Constructs a new basic file meta data provider.
	 *
	 * @param MOXMAN_Vfs_FileSystem $fileSystem File system instance for the meta data provider.
	 */
	public function __construct(MOXMAN_Vfs_FileSystem $fileSystem) {
		$this->fileSystem = $fileSystem;
		$this->metaFileCache = array();
	}

	/**
	 * Returns MOXMAN_Vfs_IFileMetaData instance.
	 *
	 * @param MOXMAN_Vfs_IFile $file File instance to get meta data for.
	 * @return MOXMAN_Vfs_IFileMetaData Meta data instance.
	 */
	public function getMetaData(MOXMAN_Vfs_IFile $file) {
		$meta = new MOXMAN_Vfs_FileMetaData($this, $file);

		return $meta;
	}

	/**
	 * Loads meta data for the specified file and returns the result.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to get the meta data from.
	 * @return Array Name/value array with meta data.
	 */
	public function loadMetaData(MOXMAN_Vfs_IFile $file) {
		$metaFile = $this->getMetaFile($file);
		$items = array();

		if ($metaFile !== null && $metaFile->exists()) {
			$metaFilePath = $metaFile->getPath();

			// Check if meta data is cached or not
			if (!isset($this->metaFileCache[$metaFilePath])) {
				$stream = $metaFile->open("r");
				$metaData = MOXMAN_Util_Json::decode(gzuncompress($stream->readToEnd()));
				$stream->close();

				$this->metaFileCache[$metaFilePath] = $metaData;
			} else {
				$metaData = $this->metaFileCache[$metaFilePath];
			}

			$name = $this->file->getName();
			if (isset($metaData[$name])) {
				$items = $metaData[$name];
			}
		}

		return $items;
	}

	/**
	 * Saves meta data for the specified file.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to save the meta data for.
	 * @param Array Name/value array with meta data.
	 */
	public function saveMetaData(MOXMAN_Vfs_IFile $file, $items) {
		$metaFile = $this->getMetaFile($file);

		if ($metaFile !== null) {
			$metaData = array();
			$metaFilePath = $metaFile->getPath();

			// Check if meta data is cached or not
			if (!isset($this->metaFileCache[$metaFilePath])) {
				if ($metaFile->exists()) {
					// Open meta file
					$stream = $metaFile->open("r");
					$metaData = MOXMAN_Util_Json::decode(gzuncompress($stream->readToEnd()));
					$stream->close();
				}
			} else {
				$metaData = $this->metaFileCache[$metaFilePath];
			}

			// Put in new contents
			if (!empty($items)) {
				$metaData[$file->getName()] = $items;
			} else {
				unset($metaData[$file->getName()]);
			}

			// Save meta file
			if (!empty($metaData)) {
				$stream = $metaFile->open("w");
				$stream->write(gzcompress(MOXMAN_Util_Json::encode($metaData), 9));
				$stream->close();
			} else if ($metaFile->exists()) {
				$metaFile->delete();
			}

			// Cache meta data
			$this->metaFileCache[$metaFilePath] = $metaData;
		}
	}

	/**
	 * Disposes the instance.
	 */
	public function dispose() {
		foreach ($this->metaFileCache as $filePath => $data) {
			$metaFile = $this->fileSystem->getFile($filePath);

			if (!empty($data)) {
				$stream = $metaFile->open("w");
				$stream->write(gzcompress(MOXMAN_Util_Json::encode($data), 9));
				$stream->close();
			} else if ($metaFile->exists()) {
				$metaFile->delete();
			}
		}
	}

	/**
	 * Returns the meta data file instance used to store meta information.
	 *
	 * @param MOXMAN_Vfs_IFile $file File instance to get the meta data file for.
	 * @return MOXMAN_Vfs_IFile Meta data file.
	 */
	protected function getMetaFile(MOXMAN_Vfs_IFile $file) {
		$metaFile = null;

		if ($file->exists()) {
			$parent = $file->getParentFile();

			// Check if file isn't a root file
			if ($parent !== null) {
				$metaFile = $parent->getFileSystem()->getFile(MOXMAN_Util_PathUtils::combine($parent->getPath(), "meta.dat"));
			}
		}

		return $metaFile;
	}
}

// @codeCoverageIgnoreEnd

?>