<?php
/**
 * IUrlResolver.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface is to be implemented by file url resolver classes.
 * A file url resolver returns file objects for specified urls.
 *
 * @package MOXMAN_Vfs
 */
interface MOXMAN_Vfs_IFileUrlResolver {
	/**
	 * Returns a file object out of the specified URL.
	 *
	 * @param string $url Absolute URL for the specified file.
	 * @return MOXMAN_Vfs_IFile File that got resolved or null if it wasn't found.
	 */
	public function getFile($url);
}

?>