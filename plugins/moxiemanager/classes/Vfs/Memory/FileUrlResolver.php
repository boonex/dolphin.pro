<?php
/**
 * FileUrlResolver.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Resolves the specified url to a file instance.
 *
 * @package MOXMAN_Vfs_Memory
 */
class MOXMAN_Vfs_Memory_FileUrlResolver implements MOXMAN_Vfs_IFileUrlResolver {
	/** @ignore */
	private $fileSystem;

	/**
	 * Constructs a new FileUrlResolver.
	 *
	 * @param MOXMAN_Vfs_FileSystem $filesystem File system reference.
	 */
	public function __construct($filesystem) {
		$this->fileSystem = $filesystem;
	}

	/**
	 * Returns a file object out of the specified URL.
	 *
	 * @param string Absolute URL for the specified file.
	 * @return MOXMAN_Vfs_IFile File that got resolved or null if it wasn't found.
	 */
	public function getFile($url) {
		$prefix = "http://memory";
		$file = null;

		if (strpos($url, $prefix) === 0) {
			$filePath = substr($url, strlen($prefix));

			if (MOXMAN_Util_PathUtils::isChildOf($filePath, $this->fileSystem->getRootPath())) {
				return $this->fileSystem->getFile($filePath);
			}
		}

		return $file;
	}
}

?>