<?php
/**
 * IUrlProvider.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface is to be implemented by file url provider classes.
 * A file url provider class returns urls for specified file objects.
 *
 * @package MOXMAN_Vfs
 */
interface MOXMAN_Vfs_IFileUrlProvider {
	/**
	 * Returns an URL for the specified file object.
	 * 
	 * @param MOXMAN_Vfs_IFile $file File to get the absolute URL for.
	 * @return String Absolute URL for the specified file.
	 */
	public function getUrl(MOXMAN_Vfs_IFile $file);
}

?>