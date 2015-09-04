<?php
/**
 * File.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This is the local file system implementation of MOXMAN_Vfs_IFile.
 */
class MOXMAN_Ftp_File extends MOXMAN_Vfs_BaseFile {
	private $stat;

	public function __construct($fileSystem, $path, $stat = null) {
		$this->fileSystem = $fileSystem;
		$this->path = $path;
		$this->stat = $stat;
	}

	public function isFile() {
		return $this->exists() && !$this->getStatItem("isdir");
	}

	public function exists() {
		return $this->getStatItem("size") !== null;
	}

	public function getSize() {
		return $this->getStatItem("size");
	}

	public function getLastModified() {
		return $this->getStatItem("mdate");
	}

	public function delete($deep = false) {
		if ($this->isFile()) {
			ftp_delete($this->fileSystem->getConnection(), $this->getInternalPath());
		} else {
			if ($deep) {
				$this->deleteRecursive($this->getInternalPath());
			} else {
				ftp_rmdir($this->fileSystem->getConnection(), $this->getInternalPath());
			}
		}

		// TODO: Maybe just set size to null?
		$this->stat = null;
	}

	public function mkdir() {
		ftp_mkdir($this->fileSystem->getConnection(), $this->getInternalPath());
	}

	public function moveTo(MOXMAN_Vfs_IFile $dest) {
		if ($dest instanceof MOXMAN_Ftp_File) {
			ftp_rename($this->fileSystem->getConnection(), $this->getInternalPath(), $dest->getInternalPath());
		} else {
			$this->copyTo($dest);
			$this->delete(true);
		}
	}

	public function copyTo(MOXMAN_Vfs_IFile $dest) {
		if ($this->isDirectory()) {
			$dest->mkdir();
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

	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter) {
		$files = array();

		if ($this->isDirectory()) {
			$dirPath = $this->getPath();
			$ftpFiles = $this->getFtpList($dirPath);
			foreach ($ftpFiles as $ftpFile) {
				$file = new MOXMAN_Ftp_File($this->fileSystem, $dirPath . "/" . $ftpFile["name"], $ftpFile);
				if ($filter->accept($file)) {
					$files[] = $file;
				}
			}
		}

		return new MOXMAN_Vfs_FileList($files);
	}

	public function getInternalPath($path = null) {
		$url = parse_url($path ? $path : $this->path);
		$path = isset($url["path"]) ? $url["path"] : "/";

		return MOXMAN_Util_PathUtils::combine($this->getFileSystem()->getAccountItem("rootpath"), $path);
	}

	public function open($mode = MOXMAN_Vfs_IFileStream::READ) {
		$stream = new MOXMAN_Vfs_MemoryFileStream($this, $mode);

		return $stream;
	}

	private function getStat() {
		$parentPath = $this->getParent();

		if ($parentPath) {
			$ftpFiles = $this->getFtpList($parentPath);
			$targetStat = null;

			foreach ($ftpFiles as $stat) {
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
				"mdate" => time()
			);
		}

		return $targetStat;
	}

	public function exportTo($localPath) {
		if (!file_exists($localPath)) {
			ftp_get($this->getFileSystem()->getConnection(), $localPath, $this->getInternalPath(), FTP_BINARY);
		}
	}

	public function importFrom($localPath) {
		if (file_exists($localPath)) {
			ftp_put($this->getFileSystem()->getConnection(), $this->getInternalPath(), $localPath, FTP_BINARY);
		}
	}

	public function getUrl() {
		$url = "";
		$fileSystem = $this->getFileSystem();
		$wwwroot = $fileSystem->getAccountItem("wwwroot");
		$path = $this->getInternalPath();

		// Resolve ftp path to url
		if ($wwwroot && MOXMAN_Util_PathUtils::isChildOf($path, $wwwroot)) {
			// Get config items
			$prefix = $fileSystem->getAccountItem("urlprefix", "{proto}://{host}");
			$suffix = $fileSystem->getAccountItem("urlsuffix");

			// Replace protocol
			if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
				$prefix = str_replace("{proto}", "https", $prefix);
			} else {
				$prefix = str_replace("{proto}", "http", $prefix);
			}

			// Replace host/port
			$prefix = str_replace("{host}", $fileSystem->getAccountItem("host"), $prefix);
			$prefix = str_replace("{port}", $_SERVER['SERVER_PORT'], $prefix);

			// Insert path into URL
			$url = substr($path, strlen($wwwroot));

			// Add prefix to URL
			if ($prefix) {
				$url = MOXMAN_Util_PathUtils::combine($prefix, $url);
			}

			// Add suffix to URL
			if ($suffix) {
				$url .= $suffix;
			}
		}

		return $url;
	}

	private function getStatItem($name, $defaultVal = null) {
		if (!$this->stat) {
			$this->stat = $this->getStat();
		}

		return isset($this->stat[$name]) ? $this->stat[$name] : $defaultVal;
	}

	private function getFtpList($path) {
		$listPath = $this->getInternalPath($path);

		// Special treatment for directories with spaces in them
		if (strpos($listPath, ' ') !== false) {
			ftp_chdir($this->fileSystem->getConnection(), $listPath);
			$listPath = ".";
		}

		$files = array();
		$lines = ftp_rawlist($this->fileSystem->getConnection(), $listPath);

		if ($lines) {
			foreach ($lines as $line) {
				$matches = null;
				$unixRe = '/^([\-ld])((?:[\-r][\-w][\-xs]){3})\s+(\d+)\s+(\w+)\s+([\-\w]+)\s+(\d+)\s+(\w+\s+\d+\s+[\w:]+)\s+(.+)$/';
				$windowsRe = "/^([^\s]+\s+[^\s]+)\s+((?:<DIR>|[\w]+)?)\s+(.+)$/";

				if ($line) {
					if (preg_match($unixRe, $line, $matches)) {
						if ($matches[8] == "." || $matches[8] == ".." || $matches[1] == "l") {
							continue;
						}

						// Unix style
						$stat = array(
							"name" => $matches[8],
							"isdir" => $matches[1] === "d",
							"size" => intval($matches[6]),
							"mdate" => strtotime($matches[7])
						);
					} else if (preg_match($windowsRe, $line, $matches)) {
						// Windows style
						$stat = array(
							"name" => $matches[3],
							"isdir" => $matches[2] === "<DIR>",
							"size" => $matches[2] !== "<DIR>" ? intval($matches[2]) : 0,
							"mdate" => strtotime($matches[1])
						);
					} else {
						// Unknown format
						throw new MOXMAN_Exception("Unknown FTP list format: " . $line);
					}

					$path = MOXMAN_Util_PathUtils::combine($path, $stat["name"]);
					$files[] = $stat;
				}
			}
		}

		return $files;
	}

	private function deleteRecursive($path) {
		$handle = $this->fileSystem->getConnection();
		$files = array();
		$dirs = array();

		$lines = ftp_rawlist($handle, $path);
		foreach ($lines as $line) {
			$matches = null;
			$unixRe = '/^([\-ld])((?:[\-r][\-w][\-xs]){3})\s+(\d+)\s+(\w+)\s+([\-\w]+)\s+(\d+)\s+(\w+\s+\d+\s+[\w:]+)\s+(.+)$/';
			$windowsRe = "/^([^\s]+\s+[^\s]+)\s+((?:<DIR>|[\w]+)?)\s+(.+)$/";

			if (preg_match($unixRe, $line, $matches)) {
				if ($matches[8] == "." || $matches[8] == "..") {
					continue;
				}

				$filePath = MOXMAN_Util_PathUtils::combine($path, $matches[8]);

				if ($matches[1] === "d") {
					$dirs[] = $filePath;
				} else {
					$files[] = $filePath;
				}
			} else if (preg_match($windowsRe, $line, $matches)) {
				$filePath = MOXMAN_Util_PathUtils::combine($path, $matches[3]);

				if ($matches[2] === "<DIR>") {
					$dirs[] = $filePath;
				} else {
					$files[] = $filePath;
				}
			} else {
				// Unknown format
				throw new MOXMAN_Exception("Unknown FTP list format: " . $line);
			}
		}

		// Delete files in dir
		foreach ($files as $file) {
			ftp_delete($handle, $file);
		}

		// Delete directories in dir
		foreach ($dirs as $dir) {
			$this->deleteRecursive($dir);
		}

		// Delete dir
		ftp_rmdir($handle, $path);
	}
}

?>