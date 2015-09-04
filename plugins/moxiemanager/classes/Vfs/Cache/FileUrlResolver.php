<?php
/**
 * FileUrlResolver.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Resolves the specified url to a file instance.
 *
 * @package MOXMAN_Vfs_Cache
 */
class MOXMAN_Vfs_Cache_FileUrlResolver implements MOXMAN_Vfs_IFileUrlResolver {
	private $fileSystem, $wrappedFileUrlResolver;

	public function __construct(MOXMAN_Vfs_FileSystem $fileSystem, MOXMAN_Vfs_IFileUrlResolver $fileUrlResolver) {
		$this->fileSystem = $fileSystem;
		$this->wrappedFileUrlResolver = $fileUrlResolver;
	}

	public function getFile($url) {
		$file = $this->wrappedFileUrlResolver->getFile($url);
		if ($file) {
			$file = new MOXMAN_Vfs_Cache_File($this->fileSystem, $file);
		}

		return $file;
	}
}

?>