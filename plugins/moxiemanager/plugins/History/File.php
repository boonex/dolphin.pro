<?php
/**
 * File.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This is the local file system implementation of MOXMAN_Vfs_IFile.
 */
class MOXMAN_History_File extends MOXMAN_Vfs_BaseFile {
	private $entry;

	public function __construct($fileSystem, $path, $entry = null) {
		parent::__construct($fileSystem, $path);
		$this->entry = $entry;
	}

	public function getLastModified() {
		return $this->entry ? $this->entry->mdate : 0;
	}

	public function getPublicLinkPath() {
		return $this->entry ? $this->entry->path : "";
	}

	public function getName() {
		return $this->entry && isset($this->entry->name) ? $this->entry->name : parent::getName();
	}

	public function isFile() {
		return $this->entry ? !$this->entry->isdir : false;
	}

	public function getSize() {
		return $this->entry ? $this->entry->size : 0;
	}

	public function exists() {
		return true;
	}

	/**
	 * Deletes the file.
	 *
	 * @param boolean $deep If this option is enabled files will be deleted recurive.
	 */
	public function delete($deep = false) {
		$files = MOXMAN_Util_Json::decode(MOXMAN::getUserStorage()->get("history.files", "[]"));

		if (preg_match('/\/([^\/]+)_\$\$\[([0-9]+)\]$/', $this->getPath(), $matches)) {
			$name = $matches[1];
			$index = intval($matches[2]);

			if (isset($files[$index]) && basename($files[$index]->path) == $name) {
				array_splice($files, $index, 1);

				MOXMAN::getUserStorage()->put("history.files", MOXMAN_Util_Json::encode($files));
			}
		}

		return true;
	}

	public function getMetaData() {
		return parent::getMetaData()->extend(array(
			"ui.icon_16x16" => "history",
			"linked" => true
		));
	}

	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter) {
		$files = array();

		if ($this->isDirectory()) {
			$fileSystem = $this->getFileSystem();
			$entries = MOXMAN_Util_Json::decode(MOXMAN::getUserStorage()->get("history.files", "[]"));
			$index = 0;
			foreach ($entries as $entry) {
				$file = new MOXMAN_History_File($fileSystem, $entry->path, $entry);

				if ($filter->accept($file)) {
					$entry->name = basename($entry->path) . "_$$[" . ($index++) . "]";
					$files[] = $file;
				}
			}
		}

		return new MOXMAN_Vfs_FileList($files);
	}

	public function getParent() {
		return "/History";
	}
}

?>