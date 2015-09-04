<?php
/**
 * IFileMetaDataProvider.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This is to be implemented by file meta data provider instances. A meta data provider
 * provides meta data instances for specific files.
 *
 * @package MOXMAN_Vfs
 */
interface MOXMAN_Vfs_IFileMetaDataProvider {
	/**
	 * Returns MOXMAN_Vfs_IFileMetaData instance.
	 *
	 * @param MOXMAN_Vfs_IFile $file File instance to get meta data for.
	 * @return MOXMAN_Vfs_IFileMetaData Meta data instance.
	 */
	public function getMetaData(MOXMAN_Vfs_IFile $file);

	/**
	 * Disposes the instance this will close any open connections.
	 */
	public function dispose();
}

?>