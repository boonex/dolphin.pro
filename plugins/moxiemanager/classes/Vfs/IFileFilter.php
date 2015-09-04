<?php
/**
 * IFileFilter.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface is to be implemented by any file filter used to
 * filter away files from file listings for example.
 *
 * @package MOXMAN_Vfs
 */
interface MOXMAN_Vfs_IFileFilter {
	/**
	 * Returns true or false if the file is accepted or not.
	 * 
	 * @param MOXMAN_Vfs_IFile $file File to grant or deny.
	 * @param Boolean $isFile Default state if the filter is on an non existing file.
	 * @return Boolean True/false if the file is accepted or not.
	 */
	public function accept(MOXMAN_Vfs_IFile $file, $isFile = true);

	/**
	 * Returns true/false if the filter is empty or not.
	 *
	 * @return boolean True/false if the filter is empty or not.
	 */
	public function isEmpty();
}

?>