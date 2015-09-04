<?php
/**
 * LocalFileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

class MOXMAN_Favorites_FileSystem extends MOXMAN_Vfs_FileSystem {
	public function isCacheable() {
		return false;
	}

	public function getFile($path) {
		return new MOXMAN_Favorites_File($this, $path);
	}
}

?>