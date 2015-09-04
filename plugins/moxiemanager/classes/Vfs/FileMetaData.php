<?php
/**
 * FileMetaData.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class contains the basic logic for a handling meta data for files.
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_FileMetaData extends MOXMAN_Util_NameValueCollection {
	/** @ignore */
	private $provider, $file;

	/**
	 * [__construct description]
	 * @param MOXMAN_Vfs_IFileMetaDataProvider $provider Meta data provider instance.
	 * @param MOXMAN_Vfs_IFile $file File that has the meta data.
	 */
	public function __construct(MOXMAN_Vfs_IFileMetaDataProvider $provider, MOXMAN_Vfs_IFile $file) {
		parent::__construct();

		$this->provider = $provider;
		$this->file = $file;
	}

	/**
	 * Loads the meta data.
	 *
	 * @return MOXMAN_Vfs_FileMetaData Meta data instance.
	 */
	public function load() {
		$this->items = $this->provider->loadMetaData($this->file);

		return $this;
	}

	/**
	 * Saves the meta data.
	 *
	 * @return MOXMAN_Vfs_FileMetaData Meta data instance.
	 */
	public function save() {
		$this->provider->saveMetaData($this->file, $this->items);

		return $this;
	}
}

?>