<?php
/**
 * File.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This is a AmazonS3 implementation of the MOXMAN_Vfs_IFile.
 */
class MOXMAN_AmazonS3_File extends MOXMAN_Vfs_BaseFile {
	private $stat, $internalPath;

	/**
	 * Constructs a new file instance.
	 *
	 * @param MOXMAN_Vfs_FileSystem $fileSystem File system instance for the file.
	 * @param String $path Path for the file.
	 * @param Array $stat File stat info or null.
	 */
	public function __construct(MOXMAN_Vfs_FileSystem $fileSystem, $path, $stat = null) {
		$this->fileSystem = $fileSystem;
		$this->path = $path;
		$this->stat = $stat;
	}

	/**
	 * Returns true if the file is a file.
	 *
	 * @return boolean true if the file is a file.
	 */
	public function isFile() {
		return $this->exists() && !$this->getStatItem("isdir");
	}

	/**
	 * Returns true if the file exists.
	 *
	 * @return boolean true if the file exists.
	 */
	public function exists() {
		return $this->getStatItem("size", -1) !== -1;
	}

	/**
	 * Returns file size as an long.
	 *
	 * @return long file size as an long.
	 */
	public function getSize() {
		return $this->getStatItem("size");
	}

	/**
	 * Returns last modification date in ms as an long.
	 *
	 * @return long last modification date in ms as an long.
	 */
	public function getLastModified() {
		return $this->getStatItem("mdate");
	}

	/**
	 * Deletes the file.
	 *
	 * @param boolean $deep If this option is enabled files will be deleted recurive.
	 */
	public function delete($deep = false) {
		if (!$this->isFileOrEmptyDir()) {
			throw new MOXMAN_Exception("Delete non empty folders not supported by S3.");
		}

		$internalPath = $this->getInternalPath();
		if ($this->isDirectory()) {
			$internalPath .= "/";
		}

		$this->getFileSystem()->getClient()->delete($internalPath);
		$this->getFileSystem()->getStatCache()->put($this->getPath(), $this->stat);
	}

	/**
	 * Creates a new directory.
	 */
	public function mkdir() {
		$stat = $this->getFileSystem()->getClient()->mkdir($this->getInternalPath());
		$this->getFileSystem()->getStatCache()->put($this->getPath(), $stat);
	}

	/**
	 * Copies this file to the specified file instance.
	 *
	 * @param MCE_File $dest File to copy to.
	 */
	public function copyTo(MOXMAN_Vfs_IFile $dest) {
		if ($this->exists() && $this->isDirectory()) {
			if (!$this->isFileOrEmptyDir()) {
				throw new MOXMAN_Exception("Copy non empty folders not supported by S3.");
			} else {
				$dest->mkdir();
				return;
			}
		}

		if ($dest instanceof MOXMAN_AmazonS3_File) {
			$fromPath = $this->getInternalPath();
			$toPath = $dest->getInternalPath();

			if ($this->isDirectory()) {
				$fromPath .= "/";
				$toPath .=  "/";
			}

			$this->getFileSystem()->getClient()->copy($fromPath, $toPath);
			$dest->removeStatCache();
		} else {
			$fromStream = $this->open("rb");
			$toStream = $dest->open("wb");

			while (($buff = $fromStream->read(8192)) !== "") {
				$toStream->write($buff);
			}

			$fromStream->close();
			$toStream->close();
		}
	}

	/**
	 * Moves this file to the specified file instance.
	 *
	 * @param MCE_File $dest File to rename/move to.
	 */
	public function moveTo(MOXMAN_Vfs_IFile $dest) {
		if (!$this->isFileOrEmptyDir()) {
			throw new MOXMAN_Exception("Move/rename non empty folders not supported by S3.");
		}

		$this->copyTo($dest);
		$this->delete();
		$this->removeStatCache();

		if ($dest instanceof MOXMAN_AmazonS3_File) {
			$dest->removeStatCache();
		}
	}

	/**
	 * Returns an array of MCE_File instances based on the specified filter instance.
	 *
	 * @param MCE_FileFilter $filter MCE_FileFilter instance to filter files by.
	 * @return MOXMAN_Vfs_FileList List of MOXMAN_Vfs_IFile instances.
	 */
	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter) {
	 	$files = array();

	 	if ($this->isDirectory()) {
			$fileSystem = $this->getFileSystem();
			$dirPath = $this->getPath();
			$entries = $this->getFileList($this->getInternalPath());
			foreach ($entries as $entry) {
				$file = new MOXMAN_AmazonS3_File($fileSystem, $dirPath . "/" . $entry["name"], $entry);
				if ($filter->accept($file)) {
					$files[] = $file;
				}
			}
		}

		return new MOXMAN_Vfs_FileList($files);
	}

	/**
	 * Opens a file stream by the specified mode. The default mode is rb.
	 *
	 * @param String $mode Mode to open file by, r, rb, w, wb etc.
	 * @return MOXMAN_Vfs_IFileStream File stream implementation for the file system.
	 */
	public function open($mode = MOXMAN_Vfs_IFileStream::READ) {
		return new MOXMAN_AmazonS3_FileStream($this, $mode);
	}

	/**
	 * Exports the file to the local system, for example a file from a zip or db file system.
	 * Implementations of this method should also support directory recursive exporting.
	 *
	 * @param String $localPath Absolute path to local file.
	 */
	public function exportTo($localPath) {
		if (!file_exists($localPath)) {
			$this->getFileSystem()->getClient()->exportTo($this->getInternalPath(), $localPath);
		}
	}

	/**
	 * Imports a local file to the file system, for example when users upload files.
	 * Implementations of this method should also support directory recursive importing.
	 *
	 * @param String $localPath Absolute path to local file.
	 */
	public function importFrom($localPath) {
		if (file_exists($localPath)) {
			$this->getFileSystem()->getClient()->importFrom($localPath, $this->getInternalPath());

			$this->stat = array(
				"name" => $this->getName(),
				"isdir" => false,
				"size" => filesize($localPath),
				"mdate" => filemtime($localPath)
			);

			$this->getFileSystem()->getStatCache()->put($this->getPath(), $this->stat);
		}
	}

	/**
	 * Returns the absolute public URL for the file.
	 *
	 * @return String Absolute public URL for the file.
	 */
	public function getUrl() {
		return MOXMAN_Util_PathUtils::combine($this->getFileSystem()->getBucketOption("urlprefix"), $this->getInternalPath());
	}

	/**
	 * Returns a stat item or the default value if it wasn't found.
	 *
	 * @param String $key Key of stat item to get.
	 * @param mixed $default Default value to return.
	 * @return mixed Value of stat item or default.
	 */
	public function getStatItem($key, $default = false) {
		// File stat data not specified then we need to get it from server
		if (!$this->stat) {
			$this->stat = $this->getStat();
		}

		return $this->stat !== null && isset($this->stat[$key]) ? $this->stat[$key] : $default;
	}

	/**
	 * Returns the file system internal path. This is used when oding requests on the remote server.
	 *
	 * @return String Internal file system path.
	 */
	public function getInternalPath() {
		if (!$this->internalPath) {
			$url = parse_url($this->path);
			$path = isset($url["path"]) ? $url["path"] : "/";
			$path = MOXMAN_Util_PathUtils::combine($this->getFileSystem()->getBucketOption("path"), $path);
			$this->internalPath = $path;
		}

		return $this->internalPath;
	}

	/**
	 * Gets the stat info for the current file object.
	 *
	 * @return Array Name/value array with info about the current file.
	 */
	private function getStat() {
		$statCache = $this->getFileSystem()->getStatCache();

		// Get cached stat
		$stat = $statCache->get($this->getPath());
		if ($stat !== null) {
			return $stat;
		}

		// Root directory stat
		if (!$this->getParent()) {
			$stat = array(
				"name" => $this->fileSystem->getRootName(),
				"isdir" => true,
				"size" => 0,
				"mdate" => 0
			);

			return $stat;
		}

		$stat = $this->fileSystem->getClient()->stat($this->getInternalPath());
		if ($stat === null) {
			$stat = array(
				"name" => $this->getName(),
				"isdir" => false,
				"size" => -1,
				"mdate" => 0
			);
		}

		$this->getFileSystem()->getStatCache()->put($this->getPath(), $stat);

		return $stat;
	}

	/**
	 * Lists files in the specified path and returns an array with stat info details.
	 *
	 * @param String $path Path to list files in.
	 * @return Array Array with stat info name/value arrays.
	 */
	public function getFileList($path) {
		return $this->fileSystem->getClient()->listFiles($path);
	}

	private function isFileOrEmptyDir() {
		if (!$this->exists() || $this->isFile()) {
			return true;
		}

		return $this->getStatItem("empty", true);
	}

	public function removeStatCache() {
		$this->stat = null;
		$this->getFileSystem()->getStatCache()->remove($this->getPath());
	}
}

?>