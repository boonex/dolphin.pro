<?php
/**
 * LocalFileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

class MOXMAN_History_FileSystem extends MOXMAN_Vfs_FileSystem {
	public function isCacheable() {
		return false;
	}

	public function getFile($path) {
		return new MOXMAN_History_File($this, $path);
	}
}

?>