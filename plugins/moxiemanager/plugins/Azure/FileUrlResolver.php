<?php
/**
 * FileUrlResolver.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Resolves the specified url to a file instance.
 *
 * @package MOXMAN_Vfs_Local
 */
class MOXMAN_Azure_FileUrlResolver implements MOXMAN_Vfs_IFileUrlResolver {
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
		$file = null;
		$prefix = $this->fileSystem->getContainerOption("urlprefix");
		$containerName = $this->fileSystem->getContainerOption("name");
		$containerKey = $this->fileSystem->getContainerOption("key");
		$match = MOXMAN_Util_PathUtils::combine($prefix, $containerName);

		if (strpos($url, $match) === 0) {
			$bucketpath = MOXMAN_Util_PathUtils::combine($prefix, $containerName);
			$filePath = MOXMAN_Util_PathUtils::combine("azure://" . $containerKey, substr($url, strlen($bucketpath)));

			if (MOXMAN_Util_PathUtils::isChildOf($filePath, $this->fileSystem->getRootPath())) {
				return $this->fileSystem->getFile($filePath);
			}
		}

		return $file;
	}
}

?>