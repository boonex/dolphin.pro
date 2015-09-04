<?php
/**
 * FileSystemManager.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class is responsible for creating file system instances and getting files out of them.
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_FileSystemManager {
	/** @ignore */
	private $config, $fileSystemClasses, $fileSystems, $defaultScheme, $hasCleanedTemp;

	/**
	 * Constructs a new FileSystemManager instance.
	 *
	 * @param MOXMAN_Util_Config $config Config instance to use for the file system manager.
	 */
	public function __construct(MOXMAN_Util_Config $config) {
		$this->config = $config;
		$this->fileSystemClasses = array();
		$this->fileSystems = array();
	}

	/**
	 * Registers a new file system class for a specific scheme.
	 *
	 * @param string $scheme Scheme for the file system.
	 * @param string $className Class name to create for the specificed scheme.
	 */
	public function registerFileSystem($scheme, $className) {
		$scheme = strtolower($scheme);

		if (!$this->defaultScheme) {
			$this->defaultScheme = $scheme;
		}

		$this->fileSystemClasses[$scheme] = $className;
	}

	/**
	 * Sets the default scheme. This will be used when a path is specified
	 * without a scheme for example "mydir".
	 *
	 * @param string $scheme Default scheme to set for example local, zip or db
	 */
	public function setDefaultScheme($scheme) {
		$this->defaultScheme = strtolower($scheme);
	}

	/**
	 * Returns the default scheme. For example local, zip or db.
	 *
	 * @return String Default scheme name for example "local".
	 */
	public function getDefaultScheme() {
		return $this->defaultScheme;
	}

	/**
	 * Adds a new root path for the file system manager. This will later on create FileSystem instances when
	 * you request the first file.
	 *
	 * Examples of root paths strings:
	 * Simple path: /root/dir
	 * Two named paths: name=/root/dir;name2=/root/otherdir
	 * Named database path: name=db://user:password@localhost/database/root
	 * Named ftp path: name=ftp://user:password@localhost/path
	 * Named flickr path: name=flickr://3dsfsd324sdfsd3/path
	 * Named picasa path: name=picasa://user@email:password/path
	 * Named webdav path: name=webdav://user@password@domain/path
	 *
	 * @param string $roots Root paths to add.
	 */
	public function addRoot($roots) {
		$roots = explode(';', $roots);
		foreach ($roots as $root) {
			if ($root) {
				$rootParts = explode('=', $root);
				$rootPath = count($rootParts) > 1 ? $rootParts[1] : $rootParts[0];

				// Parse scheme and path like db:// or zip://
				$matches = array();
				$scheme = $this->defaultScheme;
				if (preg_match('/^([a-z0-9]+):\/\/(.+)$/', $rootPath, $matches)) {
					$scheme = $matches[1];
					if ($scheme === $this->defaultScheme) {
						$rootPath = $matches[2];
					}
				}

				// Trim trailing slashes in root path
				$root = preg_replace('/([^:\/])\/$/', '$1', $root);

				$this->fileSystems[] = array(
					"path" => $root,
					"scheme" => $scheme
				);
			}
		}
	}

	/**
	 * Gets the created file systems instances as an array.
	 *
	 * @return Array Array with file system instances.
	 */
	public function getFileSystems() {
		$this->createFileSystems();

		return $this->fileSystems;
	}

	/**
	 * Removes all filesystem instances.
	 */
	public function removeAllFileSystems() {
		$this->fileSystems = array();
	}

	/**
	 * Returns a file instance based on the specified path. The path can be in the following formats:
	 * - Scheme path: local:/mydir/myfile
	 * - Encoded path: {0}/myfile
	 * - Default scheme path: /myfile
	 *
	 * @param string $path Path to get the file system instance for.
	 * @param string $childPath Path to the child of specified parent path.
	 * @return MOXMAN_Vfs_IFile File instance from the specified path.
	 */
	public function getFile($path, $childPath = "") {
		// Verfiy that the path doesn't have any abnormalities
		if (preg_match('/\x00-\x19]/', $path) || preg_match('/[\x00-\x19]/', $childPath)) {
			throw new MOXMAN_Exception("Specified path has invalid characters.", MOXMAN_Exception::INVALID_FILE_NAME);
		}

		if ($this->fileSystems) {
			$this->createFileSystems();

			// Is a http URL then ask each file systems URL resolver for a file
			if (preg_match('/^https?:\/\//', $path)) {
				$file = null;

				foreach ($this->fileSystems as $fileSystem) {
					$urlResolver = $fileSystem->getFileUrlResolver();
					if ($urlResolver) {
						$file = $urlResolver->getFile($path);

						if ($file) {
							break;
						}
					}
				}

				return $file;
			}

			if ($childPath) {
				$path = MOXMAN_Util_PathUtils::combine($path, $childPath);
			}

			// Decode {num} with root path
			$matches = array();
			if (preg_match('/^\{([0-9]+)\}/', $path, $matches)) {
				$rootIdx = intval($matches[1]);
				if (!isset($this->fileSystems[$rootIdx])) {
					throw new MOXMAN_Exception("Could not decode {" . $rootIdx . "} with a root path. Index not found.");
				}

				$path = str_replace("{" . $rootIdx . "}", $this->fileSystems[$rootIdx]->getRootPath(), $path);
			}

			// Parse scheme like db, zip,
			$matches = array();
			$scheme = "";
			if (preg_match('/^([a-z0-9]+):\/\/(.+)$/', $path, $matches)) {
				$scheme = $matches[1];
				//$pathPart = $matches[2];
				//$pathPart = substr($pathPart, 0, 1) !== "/" ? "/" . $pathPart : $pathPart;
			}

			// Get file from one of the file systems
			foreach ($this->fileSystems as $fileSystem) {
				if (!$scheme || $fileSystem->getScheme() === $scheme) {
					// Check if it matches the absolute root
					if (strpos($path, $fileSystem->getRootPath()) === 0) {
						$file = $fileSystem->getFile($path);
						return $file;
					}

					// Check if path matches the public root
					$rootPrefix = $fileSystem->getRootName();

					if ($rootPrefix !== '/') {
						$rootPrefix = '/' . $rootPrefix;
					}

					if ($path === $rootPrefix) {
						return $fileSystem->getRootFile();
					}

					if ($rootPrefix !== '/') {
						$rootPrefix .= '/';
					}

					// Check if path matches the beginning of public root
					if (strpos($path, $rootPrefix) === 0) {
						$filePath = MOXMAN_Util_PathUtils::combine($fileSystem->getRootPath(), substr($path, strlen($rootPrefix)));
						$file = $fileSystem->getFile($filePath);
						return $file;
					}
				}
			}
		}

		// Could not resolve path no file system returned a file
		throw new MOXMAN_Exception("Could not resolve path: " . $path);
	}

	/**
	 * Returns the config for the FileSystemManager.
	 *
	 * @return MOXMAN_Util_Config Config instance that was passed as a constructor.
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * Closes all file systems.
	 */
	public function close() {
		if ($this->fileSystems !== null) {
			for ($i = 0, $l = count($this->fileSystems); $i < $l; $i++) {
				$fileSystem = $this->fileSystems[$i];
				if ($fileSystem instanceof MOXMAN_Vfs_FileSystem) {
					$fileSystem->close();
				}
			}

			$this->fileSystems = null;
		}
	}

	// TODO: Fix unit tests for this
	// @codeCoverageIgnoreStart

	/**
	 * Returns the local temp path for a file instance.
	 *
	 * @param MOXMAN_Vfs_IFile $file File instance to get local temp path for.
	 * @return string Local temp path for the specified file.
	 */
	public function getLocalTempPath(MOXMAN_Vfs_IFile $file) {
		if ($file instanceof MOXMAN_Vfs_Local_File) {
			return $file->getPath();
		}

		// Get temp dir and temp file
		$tempDir = MOXMAN_Util_PathUtils::combine(MOXMAN_Util_PathUtils::getTempDir($this->config), "moxman_blob_cache");
		$tempFilePath = MOXMAN_Util_PathUtils::combine($tempDir, md5($file->getPath() . $file->getLastModified()) . "." . MOXMAN_Util_PathUtils::getExtension($file->getName()));

		if (file_exists($tempDir)) {
			// Touch temp file
			if (file_exists($tempFilePath)) {
				touch($tempFilePath);
			}

			// Remove old temp files
			if (!$this->hasCleanedTemp) {
				$ttl = $this->getConfig()->get("filesystem.blob_cache_ttl", 60) * 60;
				$now = time();

				// Loop and remove
				if ($handle = opendir($tempDir)) {
					while (($name = readdir($handle)) !== false) {
						if ($name !== "." && $name !== "..") {
							$name = $tempDir . DIRECTORY_SEPARATOR . $name;
							if (filemtime($name) < $now - $ttl) {
								unlink($name);
							}
						}
					}

					closedir($handle);
				}

				// Only clear once per request
				$this->hasCleanedTemp = true;
			}
		} else {
			mkdir($tempDir);
		}

		return $tempFilePath;
	}

	// @codeCoverageIgnoreEnd

	/**
	 * Removes the local temp file for a specific file instance.
	 *
	 * @param MOXMAN_Vfs_IFile File instance used to create a local temp file.
	 */
	public function removeLocalTempFile(MOXMAN_Vfs_IFile $file) {
		if ($file->exists()) {
			$tempDir = MOXMAN_Util_PathUtils::combine(MOXMAN_Util_PathUtils::getTempDir($this->config), "moxman_blob_cache");
			$tempFile = MOXMAN_Util_PathUtils::combine($tempDir, md5($file->getPath() . $file->getLastModified()) . "." . MOXMAN_Util_PathUtils::getExtension($file->getName()));

			if (file_exists($tempFile)) {
				unlink($tempFile);
			}
		}
	}

	/**
	 * Creates instances of any pending root paths.
	 */
	private function createFileSystems() {
		for ($i = 0, $l = count($this->fileSystems); $i < $l; $i++) {
			$fileSystem = $this->fileSystems[$i];

			if (is_array($fileSystem)) {
				$scheme = $fileSystem["scheme"];
				$path = $fileSystem["path"];

				if (isset($this->fileSystemClasses[$scheme])) {
					$fileSystemClass = $this->fileSystemClasses[$scheme];
					$fileSystem = new $fileSystemClass($scheme, $this->config, $path);

					if ($fileSystem->isCacheable()) {
						$cacheFileSystem = new MOXMAN_Vfs_Cache_FileSystem($fileSystem);

						// Detect if PDO is supported
						if ($cacheFileSystem->isDatabaseSupported()) {
							$fileSystem = $cacheFileSystem;
						}
					}

					$this->fileSystems[$i] = $fileSystem;
				} else {
					throw new MOXMAN_Exception("Could not resolve filesystem scheme: " . $scheme);
				}
			}
		}
	}
}

?>