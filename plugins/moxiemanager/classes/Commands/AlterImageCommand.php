<?php
/**
 * AlterImageCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command for altering images. This command lets you modify images using specific ations like resize, fip, rotate and crop.
 * These are used as a fallback when the client browser doesn't have image editing support.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_AlterImageCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		if (isset($params->action) && $params->action == "save") {
			return $this->save($params);
		}

		$file = MOXMAN::getFile($params->path);
		$config = $file->getConfig();

		if (!$file->exists()) {
			throw new MOXMAN_Exception(
				"File doesn't exist: " . $file->getPublicPath(),
				MOXMAN_Exception::FILE_DOESNT_EXIST
			);
		}

		$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "edit");
		if (!$filter->accept($file, true)) {
			throw new MOXMAN_Exception(
				"Invalid file name for: " . $file->getPublicPath(),
				MOXMAN_Exception::INVALID_FILE_NAME
			);
		}

		// Create temp name if not specified
		$tempname = isset($params->tempname) ? $params->tempname : "";
		if (!$tempname) {
			$ext = MOXMAN_Util_PathUtils::getExtension($file->getName());
			$tempname = "mcic_" . md5(session_id() . $file->getName()) . "." . $ext;
			$tempFilePath = MOXMAN_Util_PathUtils::combine(MOXMAN_Util_PathUtils::getTempDir(), $tempname);

			if (file_exists($tempFilePath)) {
				unlink($tempFilePath);
			}

			$file->exportTo($tempFilePath);
		} else {
			$tempFilePath = MOXMAN_Util_PathUtils::combine(MOXMAN_Util_PathUtils::getTempDir(), $tempname);
		}

		$imageAlter = new MOXMAN_Media_ImageAlter();
		$imageAlter->load($tempFilePath);

		// Rotate
		if (isset($params->rotate)) {
			$imageAlter->rotate($params->rotate);
		}

		// Flip
		if (isset($params->flip)) {
			$imageAlter->flip($params->flip == "h");
		}

		// Crop
		if (isset($params->crop)) {
			$imageAlter->crop($params->crop->x, $params->crop->y, $params->crop->w, $params->crop->h);
		}

		// Resize
		if (isset($params->resize)) {
			$imageAlter->resize($params->resize->w, $params->resize->h);
		}

		$imageAlter->save($tempFilePath, $config->get("edit.jpeg_quality"));

		return (object) array(
			"path" => $file->getPublicPath(),
			"tempname" => $tempname
		);
	}

	/**
	 * Executes the save command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	private function save($params) {
		$file = MOXMAN::getFile($params->path);
		$config = $file->getConfig();
		$size = 0;

		if ($config->get("general.demo")) {
			throw new MOXMAN_Exception("This action is restricted in demo mode.", MOXMAN_Exception::DEMO_MODE);
		}

		if (!$file->canWrite()) {
			throw new MOXMAN_Exception(
				"No write access to file: " . $file->getPublicPath(),
				MOXMAN_Exception::NO_WRITE_ACCESS
			);
		}

		$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "edit");
		if (!$filter->accept($file)) {
			throw new MOXMAN_Exception(
				"Invalid file name for: " . $file->getPublicPath(),
				MOXMAN_Exception::INVALID_FILE_NAME
			);
		}

		// Import temp file as target file
		if (isset($params->tempname)) {
			$tempFilePath = MOXMAN_Util_PathUtils::combine(MOXMAN_Util_PathUtils::getTempDir(), $params->tempname);
			$size = filesize($tempFilePath);
			$file->importFrom($tempFilePath);
		}

		$args = $this->fireBeforeFileAction("add", $file, $size);
		$file = $args->getFile();

		MOXMAN::getFileSystemManager()->removeLocalTempFile($file);
		$this->fireFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $file);

		return parent::fileToJson($file, true);
	}
}

?>