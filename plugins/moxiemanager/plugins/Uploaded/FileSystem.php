<?php
/**
 * LocalFileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

class MOXMAN_Uploaded_FileSystem extends MOXMAN_Vfs_FileSystem {
	public function isCacheable() {
		return false;
	}

	public function getFile($path) {
		return new MOXMAN_Uploaded_File($this, $path);
	}
}

?>