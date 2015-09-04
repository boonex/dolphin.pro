<?php
/**
 * IConfigProvider.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface is to be implemented by any config provider. A config provider gives out config
 * instances for files.
 *
 * @package MOXMAN_Vfs
 */
interface MOXMAN_Vfs_IFileConfigProvider {
	/**
	 * Returns a config based on the specified file.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to get the config for.
	 * @return MOXMAN_Util_Config Config for the specified file.
	 */
	public function getConfig(MOXMAN_Vfs_IFile $file);
}

?>