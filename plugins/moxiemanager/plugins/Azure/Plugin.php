<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * AzureBlobStorage file system.
 */
class MOXMAN_Azure_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getFileSystemManager()->registerFileSystem("azure", "MOXMAN_Azure_FileSystem");
	}
}

MOXMAN::getPluginManager()->add("azure", new MOXMAN_Azure_Plugin());

?>