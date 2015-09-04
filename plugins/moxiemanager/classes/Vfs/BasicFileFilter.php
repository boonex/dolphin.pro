<?php
/**
 * BasicFileFilter.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class provides basic file filtering logic.
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_BasicFileFilter implements MOXMAN_Vfs_IFileFilter {
	/** @ignore */
	private $excludeFiles;

	/** @ignore */
	private $includeFilePattern, $excludeFilePattern, $includeDirectoryPattern;

	/** @ignore */
	private $excludeDirectoryPattern, $filesOnly, $dirsOnly;

	/** @ignore */
	private $includeWildcardPatternRegExp, $excludeWildcardPatternRegExp, $extensions, $includeWildcardPattern;

	/**
	 * Sets if only files are to be accepted in result.
	 *
	 * @param boolean $filesOnly True if only files are to be accepted.
	 */
	public function setOnlyFiles($filesOnly) {
		$this->filesOnly = $filesOnly;
	}

	/**
	 * Sets if only dirs are to be accepted in result.
	 *
	 * @param boolean $dirsOnly True if only dirs are to be accepted.
	 */
	public function setOnlyDirs($dirsOnly) {
		$this->dirsOnly = $dirsOnly;
	}

	/**
	 * Sets a comma separated list of valid file extensions.
	 *
	 * @param string $extensions Comma separated list of valid file extensions.
	 */
	public function setIncludeExtensions($extensions) {
		$extensions = preg_replace('/\s+/', '', $extensions);

		if ($extensions === "*" || !$extensions) {
			$this->extensions = "";
			return;
		}

		$this->extensions = explode(',', strtolower($extensions));
	}

	/**
	 * Gets a comma separated list of valid file extensions or empty string if all are accepted.
	 *
	 * @return string Comma separated list of valid file extensions.
	 */
	public function getIncludeExtensions() {
		return $this->extensions ? implode(',', $this->extensions) : "";
	}

	/**
	 * Sets comma separated string list of filenames to exclude.
	 *
	 * @param string $files separated string list of filenames to exclude.
	 */
	public function setExcludeFiles($files) {
		if ($files) {
			$this->excludeFiles = explode(',', $files);
		}
	}

	/**
	 * Sets a regexp pattern that is used to accept files path parts.
	 *
	 * @param string $pattern regexp pattern that is used to accept files path parts.
	 */
	public function setIncludeFilePattern($pattern) {
		$this->includeFilePattern = $pattern;
	}

	/**
	 * Sets a regexp pattern that is used to deny files path parts.
	 *
	 * @param string $pattern regexp pattern that is used to deny files path parts.
	 */
	public function setExcludeFilePattern($pattern) {
		$this->excludeFilePattern = $pattern;
	}

	/**
	 * Sets a regexp pattern that is used to accept directory path parts.
	 *
	 * @param string $pattern regexp pattern that is used to accept directory path parts.
	 */
	public function setIncludeDirectoryPattern($pattern) {
		$this->includeDirectoryPattern = $pattern;
	}

	/**
	 * Sets a regexp pattern that is used to deny directory path parts.
	 *
	 * @param string $pattern regexp pattern that is used to deny directory path parts.
	 */
	public function setExcludeDirectoryPattern($pattern) {
		$this->excludeDirectoryPattern = $pattern;
	}

	/**
	 * Sets a wildcard pattern that is used to accept files path parts.
	 *
	 * @param string $pattern wildcard pattern that is used to accept files path parts.
	 */
	public function setIncludeWildcardPattern($pattern) {
		$this->includeWildcardPattern = $pattern;
		$this->includeWildcardPatternRegExp = $this->convertToRegExp($pattern);
	}

	public function getIncludeWildcardPattern() {
		return $this->includeWildcardPattern;
	}

	/**
	 * Sets a wildcard pattern that is used to deny files path parts.
	 *
	 * @param string $pattern wildcard pattern that is used to deny files path parts.
	 */
	public function setExcludeWildcardPattern($pattern) {
		$this->excludeWildcardPatternRegExp = $this->convertToRegExp($pattern);
	}

	/**
	 * Returns true/false if the filter is empty or not.
	 *
	 * @return boolean True/false if the filter is empty or not.
	 */
	public function isEmpty() {
		if ($this->extensions || $this->includeFilePattern || $this->excludeFilePattern) {
			return false;
		}

		if ($this->includeDirectoryPattern || $this->excludeDirectoryPattern) {
			return false;
		}

		if ($this->filesOnly || $this->dirsOnly || $this->excludeFiles) {
			return false;
		}

		if ($this->includeWildcardPatternRegExp || $this->excludeWildcardPatternRegExp) {
			return false;
		}

		return true;
	}

	/**
	 * Returns true or false if the file is accepted or not.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to grant or deny.
	 * @param Boolean $isFile Default state if the filter is on an non existing file.
	 * @return Boolean True/false if the file is accepted or not.
	 */
	public function accept(MOXMAN_Vfs_IFile $file, $isFile = true) {
		if ($this->isEmpty()) {
			return true;
		}

		$name = $file->getName();
		$isFile = $file->exists() ? $file->isFile() : $isFile;

		// Handle file patterns
		if ($isFile) {
			if ($this->dirsOnly) {
				return false;
			}

			// Handle exclude files
			if ($this->excludeFiles) {
				foreach ($this->excludeFiles as $fileName) {
					if ($name == $fileName) {
						return false;
					}
				}
			}

			// Handle exclude pattern
			if ($this->excludeFilePattern && preg_match($this->excludeFilePattern, $name)) {
				return false;
			}

			// Handle include pattern
			if ($this->includeFilePattern && !preg_match($this->includeFilePattern, $name)) {
				return false;
			}

			// Handle file extension pattern
			if ($this->extensions) {
				$ext = MOXMAN_Util_PathUtils::getExtension($name);
				$valid = false;

				foreach ($this->extensions as $extension) {
					if ($extension == $ext) {
						$valid = true;
						break;
					}
				}

				if (!$valid) {
					return false;
				}
			}
		} else {
			if ($this->filesOnly) {
				return false;
			}

			// Handle exclude pattern
			if ($this->excludeDirectoryPattern && preg_match($this->excludeDirectoryPattern, $name)) {
				return false;
			}

			// Handle include pattern
			if ($this->includeDirectoryPattern && !preg_match($this->includeDirectoryPattern, $name)) {
				return false;
			}
		}

		// Handle include wildcard pattern
		if ($this->includeWildcardPatternRegExp && !preg_match($this->includeWildcardPatternRegExp, $name)) {
			return false;
		}

		// Handle exclude wildcard pattern
		if ($this->excludeWildcardPatternRegExp && preg_match($this->excludeWildcardPatternRegExp, $name)) {
			return false;
		}

		return true;
	}

	/**
	 * Creates a config instance from the specified config. It will use various config options
	 * for setting up a filter instance. This is a helper function.
	 *
	 * @param MOXMAN_Util_Config $config Config instance to get settings from.
	 * @param String $prefix Optional config prefix defaults to filesystem.
	 * @return MOXMAN_Vfs_BasicFileFilter Basic file filter instance based on config.
	 */
	public static function createFromConfig(MOXMAN_Util_Config $config, $prefix = "filesystem") {
		$filter = new MOXMAN_Vfs_BasicFileFilter();

		$filter->setIncludeDirectoryPattern($config->get($prefix . '.include_directory_pattern'));
		$filter->setExcludeDirectoryPattern($config->get($prefix . '.exclude_directory_pattern'));
		$filter->setIncludeFilePattern($config->get($prefix . '.include_file_pattern'));
		$filter->setExcludeFilePattern($config->get($prefix . '.exclude_file_pattern'));
		$filter->setIncludeExtensions($config->get($prefix . '.extensions'));
		$filter->setExcludeFiles($config->get($prefix . '.local.access_file_name'));

		return $filter;
	}

	/** @ignore */
	private function convertToRegExp($wildcardPattern) {
		if ($wildcardPattern && $wildcardPattern != "*" && $wildcardPattern != "*.*") {
			// Convert whildcard pattern to regexp
			$wildcardPattern = preg_quote($wildcardPattern);
			$wildcardPattern = str_replace("\\*", ".*", $wildcardPattern);
			$wildcardPattern = str_replace("\\?", ".", $wildcardPattern);

			return "/" . $wildcardPattern . "/i";
		}
	}
}
?>