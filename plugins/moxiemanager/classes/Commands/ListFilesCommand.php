<?php
/**
 * ListFilesCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command for listing files for a specific path in a file system.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_ListFilesCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		$url = isset($params->url) ? $params->url : "";
		$path = isset($params->path) ? $params->path : "{default}";
		$rootPath = isset($params->rootpath) ? $params->rootpath : "";
		$lastPath = isset($params->lastPath) ? $params->lastPath : "";
		$offset = isset($params->offset) ? $params->offset : 0;
		$length = isset($params->length) ? $params->length : null;
		$orderBy = isset($params->orderBy) ? $params->orderBy : "name";
		$desc = isset($params->desc) ? $params->desc : false;

		// Result URL to closest file
		$file = null;
		if ($url) {
			try {
				$file = MOXMAN::getFile($url);

				if ($file && !MOXMAN_Util_PathUtils::isChildOf($file->getPublicPath(), $rootPath)) {
					$file = null;
				}
			} catch (MOXMAN_Exception $e) {
				// Might throw exception ignore it
				$file = null;
			}

			if ($file) {
				if ($file->exists()) {
					$urlFile = $file;
				}

				while (!$file->exists() || !$file->isDirectory()) {
					$file = $file->getParentFile();
				}
			}
		}

		if ($rootPath) {
			if (!MOXMAN_Util_PathUtils::isChildOf($path, $rootPath)) {
				$path = $rootPath;
			}

			if (!MOXMAN_Util_PathUtils::isChildOf($lastPath, $rootPath)) {
				$lastPath = null;
			}
		}

		// Resolve lastPath input
		if ($lastPath && !$file) {
			try {
				$file = MOXMAN::getFile($lastPath);
			} catch (MOXMAN_Exception $e) {
				// Might throw exception ignore it
				$file = null;
			}

			if ($file) {
				while (!$file->exists() || !$file->isDirectory()) {
					$file = $file->getParentFile();
				}
			}
		}

		$file = $file ? $file : MOXMAN::getFile($path);

		// Force update on cached file info
		if (isset($params->force) && $params->force) {
			MOXMAN_Vfs_Cache_FileInfoStorage::getInstance()->updateFileList($file);
		}

		if (!$file->isDirectory()) {
			throw new MOXMAN_Exception(
				"Path isn't a directory: " . $file->getPublicPath(),
				MOXMAN_Exception::INVALID_FILE_TYPE
			);
		}

		$config = $file->getConfig();

		// Setup input file filter
		$paramsFileFilter = new MOXMAN_Vfs_BasicFileFilter();

		if (isset($params->include_directory_pattern) && $params->include_directory_pattern) {
			$paramsFileFilter->setIncludeDirectoryPattern($params->include_directory_pattern);
		}

		if (isset($params->exclude_directory_pattern) && $params->exclude_directory_pattern) {
			$paramsFileFilter->setExcludeDirectoryPattern($params->exclude_directory_pattern);
		}

		if (isset($params->include_file_pattern) && $params->include_file_pattern) {
			$paramsFileFilter->setIncludeFilePattern($params->include_file_pattern);
		}

		if (isset($params->exclude_file_pattern) && $params->exclude_file_pattern) {
			$paramsFileFilter->setExcludeFilePattern($params->exclude_file_pattern);
		}

		if (isset($params->extensions) && $params->extensions) {
			$paramsFileFilter->setIncludeExtensions($params->extensions);
		}

		if (isset($params->filter) && $params->filter != null) {
			$paramsFileFilter->setIncludeWildcardPattern($params->filter);
		}

		if (isset($params->only_dirs) && $params->only_dirs === true) {
			$paramsFileFilter->setOnlyDirs(true);
		}

		if (isset($params->only_files) && $params->only_files === true) {
			$paramsFileFilter->setOnlyFiles(true);
		}

		// Setup file filter
		$configuredFilter = new MOXMAN_Vfs_BasicFileFilter();
		$configuredFilter->setIncludeDirectoryPattern($config->get('filesystem.include_directory_pattern'));
		$configuredFilter->setExcludeDirectoryPattern($config->get('filesystem.exclude_directory_pattern'));
		$configuredFilter->setIncludeFilePattern($config->get('filesystem.include_file_pattern'));
		$configuredFilter->setExcludeFilePattern($config->get('filesystem.exclude_file_pattern'));
		$configuredFilter->setIncludeExtensions($config->get('filesystem.extensions'));

		// Setup combined filter
		$combinedFilter = new MOXMAN_Vfs_CombinedFileFilter();
		$combinedFilter->addFilter($paramsFileFilter);
		$combinedFilter->addFilter($configuredFilter);

		$files = $file->listFilesFiltered($combinedFilter)->limit($offset, $length)->orderBy($orderBy, $desc);
		$args = $this->fireFilesAction(MOXMAN_Vfs_FileActionEventArgs::LIST_FILES, $file, $files);
		$files = $args->getFileList();

		$renameFilter = MOXMAN_Vfs_BasicFileFilter::createFromConfig($file->getConfig(), "rename");
		$editFilter = MOXMAN_Vfs_BasicFileFilter::createFromConfig($file->getConfig(), "edit");
		$viewFilter = MOXMAN_Vfs_BasicFileFilter::createFromConfig($file->getConfig(), "view");

		// List thumbnails and make lookup table
		$thumbnails = array();
		$thumbnailFolder = $config->get("thumbnail.folder");
		$thumbnailPrefix = $config->get("thumbnail.prefix");
		if ($config->get('thumbnail.enabled')) {
			$thumbFolderFile = MOXMAN::getFile($file->getPath(), $thumbnailFolder);

			// Force update on cached file info
			if (isset($params->force) && $params->force) {
				MOXMAN_Vfs_Cache_FileInfoStorage::getInstance()->updateFileList($thumbFolderFile);
			}

			if ($file instanceof MOXMAN_Vfs_Local_File === false) {
				$hasThumbnails = false;
				foreach ($files as $subFile) {
					if (MOXMAN_Media_ImageAlter::canEdit($subFile)) {
						$hasThumbnails = true;
						break;
					}
				}

				if ($hasThumbnails) {
					$thumbFiles = $thumbFolderFile->listFilesFiltered($combinedFilter)->limit($offset, $length)->orderBy($orderBy, $desc);

					foreach ($thumbFiles as $thumbFile) {
						$thumbnails[$thumbFile->getName()] = true;
					}
				}
			} else {
				// Stat individual files on local fs faster than listing 1000 files
				$fileSystem = $thumbFolderFile->getFileSystem();
				foreach ($files as $subFile) {
					if (MOXMAN_Media_ImageAlter::canEdit($subFile)) {
						$thumbFile = $fileSystem->getFile(MOXMAN_Util_PathUtils::combine(
							$thumbFolderFile->getPath(),
							$thumbnailPrefix . $subFile->getName()
						));

						if ($thumbFile->exists()) {
							$thumbnails[$thumbFile->getName()] = true;
						}
					}
				}
			}
		}

		$result = (object) array(
			"columns" => array("name", "size", "modified", "attrs", "info"),
			"config" => $this->getPublicConfig($file),
			"file" => $this->fileToJson($file, true),
			"urlFile" => isset($urlFile) ? $this->fileToJson($urlFile, true) : null,
			"data" => array(),
			"url" => $file->getUrl(),
			"thumbnailFolder" => $thumbnailFolder,
			"thumbnailPrefix" => $thumbnailPrefix,
			"offset" => $files->getOffset(),
			"last" => $files->isLast()
		);

		foreach ($files as $subFile) {
			$attrs = $subFile->isDirectory() ? "d" : "-";
			$attrs .= $subFile->canRead() ? "r" : "-";
			$attrs .= $subFile->canWrite() ? "w" : "-";
			$attrs .= $renameFilter->accept($subFile) ? "r" : "-";
			$attrs .= $subFile->isFile() && $editFilter->accept($subFile) ? "e" : "-";
			$attrs .= $subFile->isFile() && $viewFilter->accept($subFile) ? "v" : "-";
			$attrs .= $subFile->isFile() && MOXMAN_Media_ImageAlter::canEdit($subFile) ? "p" : "-";
			$attrs .= isset($thumbnails[$thumbnailPrefix . $subFile->getName()]) ? "t" : "-";

			$args = $this->fireCustomInfo(MOXMAN_Vfs_CustomInfoEventArgs::LIST_TYPE, $subFile);
			$custom = (object) $args->getInfo();

			if ($subFile->getPublicLinkPath()) {
				$custom->link = $subFile->getPublicLinkPath();
			}

			$result->data[] = array(
				$subFile->getName(),
				$subFile->isDirectory() ? 0 : $subFile->getSize(),
				$subFile->getLastModified(),
				$attrs,
				$custom
			);
		}

		return $result;
	}
}

?>