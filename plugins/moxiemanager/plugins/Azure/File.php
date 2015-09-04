<?php
/**
 * File.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This is a Azure implementation of the MOXMAN_Vfs_IFile.
 */
class MOXMAN_Azure_File extends MOXMAN_Vfs_BaseFile {
	private $stat;

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
		return $this->getStatItem("size", false) !== false;
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
			throw new MOXMAN_Exception("Delete non empty folders not supported by Azure.");
		}

		$internalPath = $this->isDirectory() ? $this->getInternalPath() . "/" : $this->getInternalPath();

		$this->sendRequest(array(
			"method" => "DELETE",
			"path" => $internalPath
		));
	}

	/**
	 * Creates a new directory.
	 */
	public function mkdir() {
		$this->sendRequest(array(
			"method" => "PUT",
			"path" => $this->getInternalPath() . '/',
			"headers" => array(
				"Content-Length" => "0",
				"x-ms-blob-type" => "BlockBlob"
			)
		));
	}

	/**
	 * Copies this file to the specified file instance.
	 *
	 * @param MCE_File $dest File to copy to.
	 */
	public function copyTo(MOXMAN_Vfs_IFile $dest) {
		if ($this->exists() && $this->isDirectory()) {
			if (!$this->isFileOrEmptyDir()) {
				throw new MOXMAN_Exception("Copy non empty folders not supported by Azure.");
			} else {
				$dest->mkdir();
				return;
			}
		}

		if ($dest instanceof MOXMAN_Azure_File) {
			$containerUrl = MOXMAN_Util_PathUtils::combine(
				$this->fileSystem->getContainerOption("account"),
				$this->fileSystem->getContainerOption("name")
			);

			$fromUrl = "/" . MOXMAN_Util_PathUtils::combine($containerUrl, $this->getInternalPath());

			$request = $this->getFileSystem()->createRequest(array(
				"method" => "PUT",
				"path" => $dest->getInternalPath(),
				"headers" => array(
					"x-ms-copy-source" => $fromUrl,
					"Content-Length" => 0
				)
			));

			$this->getFileSystem()->sendRequest($request);
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
			throw new MOXMAN_Exception("Move/rename non empty folders not supported by Azure.");
		}

		$this->copyTo($dest);
		$this->delete();
	}

	/**
	 * Returns an array of MCE_File instances based on the specified filter instance.
	 *
	 * @param MCE_FileFilter $filter MCE_FileFilter instance to filter files by.
	 * @return Array array of MCE_File instances based on the specified filter instance.
	 */
	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter) {
		$files = array();

		$azureFiles = $this->getFileList($this->getInternalPath());
		foreach ($azureFiles as $azureFile) {
			$path = MOXMAN_Util_PathUtils::combine($this->getPath(), $azureFile["name"]);
			$file = new MOXMAN_Azure_File($this->fileSystem, $path, $azureFile);
			if ($filter->accept($file)) {
				$files[] = $file;
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
		return new MOXMAN_Vfs_MemoryFileStream($this, $mode);
	}

	/**
	 * Exports the file to the local system, for example a file from a zip or db file system.
	 * Implementations of this method should also support directory recursive exporting.
	 *
	 * @param String $localPath Absolute path to local file.
	 */
	public function exportTo($localPath) {
		if (!file_exists($localPath)) {
			$request = $this->getFileSystem()->createRequest(array(
				"method" => "GET",
				"path" => $this->getInternalPath(),
				"headers" => array(
					"x-ms-blob-type" => "BlockBlob"
				)
			));

			$response = $this->getFileSystem()->sendRequest($request);

			// Read remote file and write the contents to local file
			$fp = fopen($localPath, "wb");
			if ($fp) {
				// Stream file down to disk
				while (($chunk = $response->read()) != "") {
					fwrite($fp, $chunk);
				}

				fclose($fp);
			}
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
			$request = $this->getFileSystem()->createRequest(array(
				"method" => "PUT",
				"path" => $this->getInternalPath(),
				"headers" => array(
					"Content-Type" => MOXMAN_Util_Mime::get($this->getName()),
					"Content-Length" => filesize($localPath),
					"x-ms-blob-type" => "BlockBlob"
				)
			));

			$request->setLocalFile($localPath);
			$body = $this->getFileSystem()->sendRequest($request)->getBody();

			if ($body) {
				throw new MOXMAN_Exception("Azure import failed.");
			}
		}
	}

	/**
	 * Returns the absolute public URL for the file.
	 *
	 * @return String Absolute public URL for the file.
	 */
	public function getUrl() {
		$fileSystem = $this->getFileSystem();
		$prefix = $fileSystem->getContainerOption("urlprefix");

		return $prefix ? MOXMAN_Util_PathUtils::combine($prefix, $fileSystem->getContainerOption("name") . $this->getInternalPath()) : "";
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
	 * @param String $path Optional path to convert into internal an internal path.
	 * @return String Internal file system path.
	 */
	public function getInternalPath($path = null) {
		$url = parse_url($path ? $path : $this->path);
		$path = isset($url["path"]) ? $url["path"] : "/";

		return $path;
	}

	/**
	 * Gets the stat info for the current file object.
	 *
	 * @return Array Name/value array with info about the current file.
	 */
	private function getStat() {
		$parentPath = $this->getParent();

		if ($parentPath) {
			$parentPath = $this->getInternalPath($this->getParent());
			$files = $this->getFileList($parentPath, $this->getName());
			$targetStat = null;

			foreach ($files as $stat) {
				if ($stat["name"] === $this->getName()) {
					$targetStat = $stat;
				}
			}
		} else {
			// Stat info for root directory
			$targetStat = array(
				"name" => $this->fileSystem->getRootName(),
				"isdir" => true,
				"size" => 0,
				"mdate" => 0
			);
		}

		return $targetStat;
	}

	/**
	 * Lists files in the specified path and returns an array with stat info details.
	 *
	 * @param String $path Path to list files in.
	 * @return Array Array with stat info name/value arrays.
	 */
	private function getFileList($path) {
		$files = array();
		$prefix = $path === "/" ? "" : substr($path, 1) . "/";

		$xml = $this->sendXmlRequest(array(
			"query" => array(
				"comp" => "list",
				"restype" => "container",
				"prefix" => $prefix,
				"delimiter" => "/",
				"maxresults" => 99999
			)
		));

		// List dirs
		if (isset($xml->Blobs->BlobPrefix)) {
			foreach ($xml->Blobs->BlobPrefix as $blobPrefix) {
				if ($prefix != $blobPrefix->Name) {
					$stat = array(
						"name" => basename($blobPrefix->Name),
						"isdir" => true,
						"size" => 0,
						"mdate" => 0
					);

					$path = MOXMAN_Util_PathUtils::combine($path, $stat["name"]);
					$files[] = $stat;
				}
			}
		}

		// List files
		if (isset($xml->Blobs->Blob)) {
			foreach ($xml->Blobs->Blob as $blob) {
				if ($prefix != $blob->Name) {
					$stat = array(
						"name" => basename($blob->Name),
						"isdir" => false,
						"size" => intval($blob->Properties->{"Content-Length"}),
						"mdate" => strtotime($blob->Properties->{"Last-Modified"})
					);

					$path = MOXMAN_Util_PathUtils::combine($path, $stat["name"]);
					$files[] = $stat;
				}
			}
		}

		return $files;
	}

	/**
	 * Sends a request to the Azure rest API.
	 *
	 * @param Array $params Name/value array of request parameters.
	 * @return SimpleXMLElement Simple XML element of the response body.
	 */
	private function sendRequest($params) {
		$request = $this->getFileSystem()->createRequest($params);
		return $this->getFileSystem()->sendRequest($request)->getBody();
	}

	/**
	 * Sends a XML request to the Azure rest API.
	 *
	 * @param Array $params Name/value array of request parameters.
	 * @return SimpleXMLElement Simple XML element of the response body.
	 */
	private function sendXmlRequest($params) {
		$request = $this->getFileSystem()->createRequest($params);
		$body = $this->getFileSystem()->sendRequest($request)->getBody();

		if ($body) {
			return new SimpleXMLElement($body);
		}

		return $body;
	}

	private function isFileOrEmptyDir() {
		if (!$this->exists() || $this->isFile()) {
			return true;
		}

		return count($this->getFileList($this->getInternalPath())) == 0;
	}
}

?>