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
class MOXMAN_AmazonS3_FileUrlResolver implements MOXMAN_Vfs_IFileUrlResolver {
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
		$prefix = $this->fileSystem->getBucketOption("urlprefix");
		$prefix = preg_replace('/^https?:\/\//', '//', $prefix);
		$url = preg_replace('/^https?:\/\//', '//', $url);

		if (strpos($url, $prefix) === 0) {
			$bucketKey = $this->fileSystem->getBucketOption("key");
			$path = urldecode(substr($url, strlen($prefix)));
			$filePath = MOXMAN_Util_PathUtils::combine("s3://" . $bucketKey, $path);

			if (MOXMAN_Util_PathUtils::isChildOf($filePath, $this->fileSystem->getRootPath())) {
				return $this->fileSystem->getFile($filePath);
			}
		}

		return $file;
	}
}

?>