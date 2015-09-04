<?php
/**
 * FileInfoCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command that returns meta data for the specified file.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_FileInfoCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		$create = isset($params->create) && $params->create === true;

		if (isset($params->paths)) {
			$result = array();

			foreach ($params->paths as $path) {
				$file = MOXMAN::getFile($path);

				if ($create) {
					$args = $this->fireBeforeFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $file);
					$file = $args->getFile();
				}

				$fileInfo = $this->fileToJson($file, true);

				$args = $this->fireCustomInfo(MOXMAN_Vfs_CustomInfoEventArgs::INSERT_TYPE, $file);
				$fileInfo->info = (object) $args->getInfo();

				if (isset($params->insert) && $params->insert) {
					$this->addVideoMeta($file, $fileInfo->meta);
					$this->fireFileAction(MOXMAN_Vfs_FileActionEventArgs::INSERT, $file);
				}

				$result[] = $fileInfo;
			}
		} else {
			$file = MOXMAN::getFile($params->path);

			if ($create) {
				$args = $this->fireBeforeFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $file);
				$file = $args->getFile();
			}

			$fileInfo = $this->fileToJson($file, true);

			$args = $this->fireCustomInfo(MOXMAN_Vfs_CustomInfoEventArgs::INSERT_TYPE, $file);
			$fileInfo->info = (object) $args->getInfo();

			if (isset($params->insert) && $params->insert) {
				$this->addVideoMeta($file, $fileInfo->meta);
				$this->fireFileAction(MOXMAN_Vfs_FileActionEventArgs::INSERT, $file);
			}

			$result = $fileInfo;
		}

		return $result;
	}

	private function addVideoMeta(MOXMAN_Vfs_IFile $file, $metaData) {
		$fileName = $file->getName();
		$ext = strtolower(MOXMAN_Util_PathUtils::getExtension($fileName));

		if (preg_match('/^(mp4|ogv|webm)$/', $ext)) {
			$metaData->url_type = MOXMAN_Util_Mime::get($fileName);
			$name = substr($fileName, 0, strlen($fileName) - strlen($ext));

			// Alternative video formats
			$altExt = array("mp4", "ogv", "webm");
			foreach ($altExt as $altExt) {
				if ($ext != $altExt) {
					$altFile = MOXMAN::getFile($file->getParent(), $name . $altExt);
					if ($altFile->exists()) {
						$metaData->alt_url = $altFile->getUrl();
						break;
					}
				}
			}

			// Alternative image format
			$altFile = MOXMAN::getFile($file->getParent(), $name . "jpg");
			if ($altFile->exists()) {
				$metaData->alt_img = $altFile->getUrl();
			}
		}
	}
}

?>