<?php
/**
 * FileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Memory file system implementation.
 *
 * @package MOXMAN_Vfs_Memory
 */
class MOXMAN_Vfs_Memory_FileSystem extends MOXMAN_Vfs_FileSystem {
	/** @ignore */
	private $entries, $pathLookup;

	/**
	 * Constructs a new memory file system.
	 *
	 * @param string $scheme File scheme.
	 * @param MOXMAN_Util_Config $config Config instance for file system.
	 * @param string $root Root path for file system.
	 */
	public function __construct($scheme, MOXMAN_Util_Config $config, $root) {
		parent::__construct($scheme, $config, $root);
		$this->entries = array();
		$this->pathLookup = array();

		$this->addEntry($root, array(
			"isFile" => false
		));

		$this->setFileUrlResolver(new MOXMAN_Vfs_Memory_FileUrlResolver($this));
	}

	/** @ignore */
	public function getEntries($path = "/") {
		if ($path !== "/") {
			$entries = array();

			for ($i = 0, $l = count($this->entries); $i < $l; $i++) {
				$entryPath = $this->entries[$i]->path;
				if ($entryPath === $path || strpos($entryPath, $path . '/') === 0) {
					$entries[] = $this->entries[$i];
				}
			}

			return $entries;
		}

		return $this->entries;
	}

	/** @ignore */
	public function getChildEntries($path = "/") {
		$entries = array();
		$matchPath = $path == "/" ? "/" : $path . "/";

		for ($i = 0, $l = count($this->entries); $i < $l; $i++) {
			$entryPath = $this->entries[$i]->path;

			if ($entryPath === $matchPath) {
				continue;
			}

			if (strpos($entryPath, $matchPath) !== 0) {
				continue;
			}

			if (strpos($entryPath, "/", strlen($matchPath)) !== false) {
				continue;
			}

			$entries[] = $this->entries[$i];
		}

		return $entries;
	}

	/** @ignore */
	public function getEntry($path) {
		if (isset($this->pathLookup[$path])) {
			return $this->pathLookup[$path];
		}

		for ($i = count($this->entries) - 1; $i >= 0; $i--) {
			if ($this->entries[$i]->path === $path) {
				$this->pathLookup[$path] = $this->entries[$i];
				return $this->entries[$i];
			}
		}

		return null;
	}

	/** @ignore */
	public function addEntry($path, $data) {
		$this->entries[] = (object) array_merge(array(
			"path" => $path,
			"isFile" => true,
			"lastModified" => time(),
			"data" => "",
			"canRead" => true,
			"canWrite" => true
		), $data);
	}

	/** @ignore */
	public function deleteEntry($path) {
		for ($i = count($this->entries) - 1; $i >= 0; $i--) {
			$entryPath = $this->entries[$i]->path;

			if ($entryPath === $path || strpos($entryPath, $path . '/') === 0) {
				unset($this->pathLookup[$entryPath]);
				array_splice($this->entries, $i, 1);
			}
		}
	}

	/**
	 * Returns the true/false if the file system can be cached or not.
	 *
	 * @return True/false if the file system is cacheable or not.
	 */
	public function isCacheable() {
		return false;
	}

	/**
	 * Returns a MOXMAN_Vfs_IFile instance based on the specified path.
	 *
	 * @param string $path Path of the file to retrive.
	 * @return MOXMAN_Vfs_IFile File instance for the specified path.
	 */
	public function getFile($path) {
		$file = new MOXMAN_Vfs_Memory_File($this, $path);
		return $file;
	}
}

?>