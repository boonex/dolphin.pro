<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ....
 *
 */
class MOXMAN_AutoRename_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getPluginManager()->get("core")->bind("BeforeFileAction", "onBeforeFileAction", $this);
	}

	public function onBeforeFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		switch ($args->getAction()) {
			case MOXMAN_Vfs_FileActionEventArgs::ADD:
				$args->setFile($this->renameFile($args->getFile()));
				break;
			case MOXMAN_Vfs_FileActionEventArgs::MOVE:
				$args->setTargetFile($this->renameFile($args->getTargetFile()));
				break;
		}
	}

	/**
	 * Fixes filenames
	 *
	 * @param MOXMAN_Vfs_IFile $file File to fix name on.
	 */
	public function renameFile(MOXMAN_Vfs_IFile $file) {
		$config = $file->getConfig();
		$autorename = $config->get("autorename.enabled", "");
		$spacechar = $config->get("autorename.space", "_");
		$custom = $config->get("autorename.pattern", "/[^0-9a-z\-_]/i");
		//$overwrite = $config->get("upload.overwrite", false);
		$lowercase = $config->get("autorename.lowercase", false);

		// @codeCoverageIgnoreStart
		if (!$autorename) {
			return $file;
		}

		// @codeCoverageIgnoreEnd
		$path = $file->getPath();
		$name = $file->getName();
		$orgname = $name;
		$ext = MOXMAN_Util_PathUtils::getExtension($path);
		$name = preg_replace("/\.". $ext ."$/i", "", $name);

		$name = str_replace(array('\'', '"'), '', $name);
		$name = htmlentities($name, ENT_QUOTES, 'UTF-8');
		$name = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $name);
		$name = preg_replace($custom, $spacechar, $name);
		$name = str_replace(" ", $spacechar, $name);
		$name = trim($name);

		if ($lowercase) {
			$ext = strtolower($ext);
			$name = strtolower($name);
		}

		if ($ext) {
			$name = $name .".". $ext;
		}

		// If no change to name after all this, return original file.
		if ($name === $orgname) {
			return $file;
		}

		// Return new file
		$toFile = MOXMAN::getFile($file->getParent() . "/" . $name);

		return $toFile;
	}
}

// Add plugin
MOXMAN::getPluginManager()->add("autorename", new MOXMAN_AutoRename_Plugin());

?>