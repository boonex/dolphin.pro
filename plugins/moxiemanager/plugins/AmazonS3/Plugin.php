<?php
/**
 * AmazonS3.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * AmazonS3 file system plugin. This plugin will enable you to connect to different buckets and manage your files on those.
 */
class MOXMAN_AmazonS3_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getFileSystemManager()->registerFileSystem("s3", "MOXMAN_AmazonS3_FileSystem");
	}
}

// Add plugin
MOXMAN::getPluginManager()->add("amazons3", new MOXMAN_AmazonS3_Plugin());

?>