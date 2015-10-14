<?php
/**
 * FileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Local file system implementation.
 *
 * @package MOXMAN_Vfs_Local
 */
class MOXMAN_Vfs_Local_FileSystem extends MOXMAN_Vfs_FileSystem {
	/**
	 * Constructs a new LocalFileSystem.
	 *
	 * @param string $scheme File scheme.
	 * @param MOXMAN_Util_Config $config Config instance for file system.
	 * @param string $root Root path for file system.
	 */
	public function __construct($scheme, MOXMAN_Util_Config $config, $root) {
		parent::__construct($scheme, $config, $root);

		// Force the root path to an absolute path
		$this->rootPath = MOXMAN_Util_PathUtils::toAbsolute(MOXMAN_ROOT, $this->rootPath);

		// Get wwwroot from config or resolve it, remove trailing slash.
		$wwwroot = $config->get("filesystem.local.wwwroot");
		if (is_array($wwwroot)) {
			$wwwval = null;
			foreach($wwwroot as $www => $wwwval) {
				if (MOXMAN_Util_PathUtils::isChildOf($this->rootPath, $www)) {
					$wwwroot = $www;
					break;
				}
			}
		}

		if (!$wwwroot || is_array($wwwroot)) {
			$sitePaths = MOXMAN_Util_PathUtils::getSitePaths();
			$wwwroot = $sitePaths["wwwroot"];
		}

		$wwwroot = preg_replace('/\\/$/', '', $wwwroot);

		// If rootpath isn't within the resolved wwwroot then resolve the rootpath
		if (!MOXMAN_Util_PathUtils::isChildOf($this->rootPath, $wwwroot)) {
			$this->rootPath = realpath($this->rootPath);

			if (!$this->rootPath) {
				throw new MOXMAN_Exception("Configured filesystem.rootpath doesn't exist or couldn't be resolved.");
			} else if (!MOXMAN_Util_PathUtils::isChildOf($this->rootPath, $wwwroot)) {
				throw new MOXMAN_Exception(
					"The filesystem.rootpath isn't within the auto detected wwwroot." .
					"You need to configure filesystem.local.wwwroot."
				);
			}
		}

		$this->setFileConfigProvider(new MOXMAN_Vfs_Local_FileConfigProvider($this, $config));
		$this->setFileUrlProvider(new MOXMAN_Vfs_Local_FileUrlProvider());
		$this->setFileUrlResolver(new MOXMAN_Vfs_Local_FileUrlResolver($this));
	}

	/**
	 * Returns the true/false if the file system can be cached or not.
	 *
	 * @return True/false if the file system is cacheable or not.
	 */
	public function isCacheable() {
		return $this->config->get("filesystem.local.cache", false) === true;
	}

	/**
	 * Returns a MOXMAN_Vfs_IFile instance based on the specified path.
	 *
	 * @param string $path Path of the file to retrive.
	 * @return MOXMAN_Vfs_IFile File instance for the specified path.
	 */
	public function getFile($path) {
		// Never give access to the mc_access file
		if ($this->getConfig()->get("filesystem.local.access_file_name") === basename($path)) {
			throw new MOXMAN_Exception("Can't access the access_file_name.");
		}

		$this->verifyPath($path);

		// Force the path to an absolute path
		$path = MOXMAN_Util_PathUtils::toAbsolute(MOXMAN_ROOT, $path);

		// If the path is out side the root then return null
		if (!MOXMAN_Util_PathUtils::isChildOf($path, $this->rootPath)) {
			$null = null;
			return $null;
		}

		return new MOXMAN_Vfs_Local_File($this, $path);
	}

	/**
	 * Verifies the input path against various exploits and throws exceptions if one is found.
	 *
	 * @param String $path Path to verify.
	 * @param String $fileType File type to verify path against. Values: dir or file.
	 */
	public function verifyPath($path, $fileType = null) {
		// Verfiy that the path doesn't have any abnormalities
		if (preg_match('/\\\\|\\.\\.\/|[\x00-\x19]/', $path)) {
			throw new MOXMAN_Exception("Specified path has invalid characters.");
		}

		$path = MOXMAN_Util_PathUtils::toUnixPath($path);

		if (preg_match('~IIS/(\d+\.\d+)~', $_SERVER['SERVER_SOFTWARE'], $matches)) {
			$version = floatval($matches[1]);
			if ($version < 7) {
				if (strpos($path, ';') !== false) {
					if ($this->getConfig()->get("filesystem.local.warn_semicolon", true)) {
						throw new MOXMAN_Exception("IIS 6 doesn't support semicolon in paths for security reasons.", MOXMAN_Exception::INVALID_FILE_NAME);
					}
				}

				if (preg_match('/\.[^\/]+\//', $path) || ($fileType == "dir" && strpos($path, '.') !== false)) {
					if ($this->getConfig()->get("filesystem.local.warn_dot_dirs", true)) {
						throw new MOXMAN_Exception("IIS 6 don't support dots in directory names for security reasons.", MOXMAN_Exception::INVALID_FILE_NAME);
					}
				}
			}
		} else if (preg_match('/\.(php|inc|php\d+|phtml|php[st])\.[^\/]+/', $path)) {
			if ($this->getConfig()->get("filesystem.local.warn_double_exts", true)) {
				throw new MOXMAN_Exception("Double extensions is not allowed for security reasons.", MOXMAN_Exception::INVALID_FILE_NAME);
			}
		}
	}
}

?>