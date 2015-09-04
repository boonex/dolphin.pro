<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ...
 */
class MOXMAN_History_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getFileSystemManager()->registerFileSystem("history", "MOXMAN_History_FileSystem");
		MOXMAN::getFileSystemManager()->addRoot("History=history:///");
		MOXMAN::getPluginManager()->get("core")->bind("FileAction", "onFileAction", $this);
	}

	public function add($path) {
		$files = MOXMAN_Util_Json::decode(MOXMAN::getUserStorage()->get("history.files", "[]"));

		// If files is larger then max size then crop it
		$max = intval(MOXMAN::getConfig()->get("history.max"));
		if (count($files) >= $max) {
			$files = array_slice($files, count($files) - $max);
		}

		// Remove existing paths
		for ($i = count($files) - 1; $i >= 0; $i--) {
			if ($files[$i]->path == $path) {
				array_splice($files, $i, 1);
				$i--;
			}
		}

		$file = MOXMAN::getFile($path);

		$files[] = array(
			"path" => $file->getPublicPath(),
			"size" => $file->getSize(),
			"isdir" => $file->isDirectory(),
			"mdate" => $file->getLastModified()
		);

		MOXMAN::getUserStorage()->put("history.files", MOXMAN_Util_Json::encode($files));
	}

	public function remove($params) {
		if (MOXMAN::getConfig()->get('general.demo')) {
			throw new MOXMAN_Exception(
				"This action is restricted in demo mode.",
				MOXMAN_Exception::DEMO_MODE
			);
		}

		if (isset($params->paths) && is_array($params->paths)) {
			$paths = $params->paths;
			$files = MOXMAN_Util_Json::decode(MOXMAN::getUserStorage()->get("history.files", "[]"));

			for ($i = count($files) - 1; $i >= 0; $i--) {
				foreach ($paths as $path) {
					if ($files[$i]->path == $path) {
						array_splice($files, $i, 1);
						$i--;
					}
				}
			}

			MOXMAN::getUserStorage()->put("history.files", MOXMAN_Util_Json::encode($files));
		}

		return true;
	}

	public function onFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		if ($args->isAction("insert")) {
			$this->add($args->getFile()->getPublicPath());
		}

		if ($args->isAction("delete")) {
			$this->remove((object) array(
				"paths" => array(
					$args->getFile()->getPublicPath()
				)
			));
		}
	}
}

// Add plugin
MOXMAN::getPluginManager()->add("history", new MOXMAN_History_Plugin());

?>