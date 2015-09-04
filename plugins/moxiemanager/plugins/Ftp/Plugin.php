<?php
/**
 * Ftp.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ...
 */
class MOXMAN_Ftp_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getFileSystemManager()->registerFileSystem("ftp", "MOXMAN_Ftp_FileSystem");
	}
}

// Add plugin
MOXMAN::getPluginManager()->add("ftp", new MOXMAN_Ftp_Plugin());

?>