<?php
/**
 * Example.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Here is an simple example plugin that displays how to listen for FileAction events. This event
 * will fire each time a file is added/deleted/moved/copied etc.
 */
class MOXMAN_Example_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getPluginManager()->get("core")->bind("FileAction", "onFileAction", $this);
	}

	public function onFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		$logger = MOXMAN::getLogger();

		if ($logger) {
			if ($args->getTargetFile()) {
				// Log copy/move operations these have a target file
				$logger->debug(
					"Action: " . $args->getAction(),
					"Path: " . $args->getFile()->getPath(),
					"TargetPath: " . $args->getTargetFile()->getPath()
				);
			} else {
				// Log single file operations
				$logger->debug(
					"Action: " . $args->getAction(),
					"Path: " . $args->getFile()->getPath()
				);
			}
		}
	}
}

MOXMAN::getPluginManager()->add("example", new MOXMAN_Example_Plugin());

?>