<?php
/**
 * CombinedFileFilter.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class combines multiple filters into one filer.
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_CombinedFileFilter implements MOXMAN_Vfs_IFileFilter {
	/** @ignore */
	private $filters;

	/**
	 * Constructs a new combined filer.
	 */
	public function __construct() {
		$this->filters = array();
	}

	/**
	 * Adds a new filter to check.
	 *
	 * @param MOXMAN_Vfs_IFileFilter $fileFilter Filter to add.
	 */
	public function addFilter(MOXMAN_Vfs_IFileFilter $fileFilter) {
		if (!$fileFilter->isEmpty()) {
			$this->filters[] = $fileFilter;
		}
	}

	/**
	 * Returns true/false if the filter is empty or not.
	 *
	 * @return boolean True/false if the filter is empty or not.
	 */
	public function isEmpty() {
		return count($this->filters) === 0;
	}

	/**
	 * Returns true or false if the file is accepted or not by checking accept on all added filters.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to grant or deny.
	 * @param Boolean $isFile Default state if the filter is on an non existing file.
	 * @return Boolean True/false if the file is accepted or not.
	 */
	public function accept(MOXMAN_Vfs_IFile $file, $isFile = true) {
		for ($i = 0, $l = count($this->filters); $i < $l; $i++) {
			if (!$this->filters[$i]->accept($file, $isFile)) {
				return false;
			}
		}

		return true;
	}

	public function getFilters() {
		return $this->filters;
	}

	/**
	 * Creates a config instance from the specified config. It will use various config options
	 * for setting up a filter instance. This is a helper function.
	 *
	 * @param MOXMAN_Util_Config $config Config instance to get settings from.
	 * @param String $prefix Prefix of subfilter for example "edit"
	 * @return MOXMAN_Vfs_CombinedFileFilter Basic file filter instance based on config.
	 */
	public static function createFromConfig(MOXMAN_Util_Config $config, $prefix) {
		$filter1 = new MOXMAN_Vfs_BasicFileFilter();
		$filter1->setIncludeDirectoryPattern($config->get('filesystem.include_directory_pattern'));
		$filter1->setExcludeDirectoryPattern($config->get('filesystem.exclude_directory_pattern'));
		$filter1->setIncludeFilePattern($config->get('filesystem.include_file_pattern'));
		$filter1->setExcludeFilePattern($config->get('filesystem.exclude_file_pattern'));
		$filter1->setIncludeExtensions($config->get('filesystem.extensions'));
		$filter1->setExcludeFiles($config->get('filesystem.local.access_file_name'));

		$filter2 = new MOXMAN_Vfs_BasicFileFilter();
		$filter2->setIncludeDirectoryPattern($config->get($prefix . '.include_directory_pattern'));
		$filter2->setExcludeDirectoryPattern($config->get($prefix . '.exclude_directory_pattern'));
		$filter2->setIncludeFilePattern($config->get($prefix . '.include_file_pattern'));
		$filter2->setExcludeFilePattern($config->get($prefix . '.exclude_file_pattern'));
		$filter2->setIncludeExtensions($config->get($prefix . '.extensions'));

		$filter = new MOXMAN_Vfs_CombinedFileFilter();
		$filter->addFilter($filter1);
		$filter->addFilter($filter2);

		return $filter;
	}
}

?>