<?php
/**
 * DeleteCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command that deletes multiple files.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_DeleteCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		$paths = $params->paths;
		$result = array();

		foreach ($paths as $path) {
			$file = MOXMAN::getFile($path);
			$config = $file->getConfig();

			if ($config->get('general.demo')) {
				throw new MOXMAN_Exception(
					"This action is restricted in demo mode.",
					MOXMAN_Exception::DEMO_MODE
				);
			}

			if (!$file->exists()) {
				throw new MOXMAN_Exception(
					"Path doesn't exist: " . $file->getPublicPath(),
					MOXMAN_Exception::FILE_DOESNT_EXIST
				);
			}

			$parentFile = $file->getParentFile();
			if (!$parentFile || !$parentFile->canWrite()) {
				throw new MOXMAN_Exception(
					"No write access to file: " . $file->getPublicPath(),
					MOXMAN_Exception::NO_WRITE_ACCESS
				);
			}

			$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "delete");
			if (!$filter->accept($file)) {
				throw new MOXMAN_Exception(
					"Invalid file name for: " . $file->getPublicPath(),
					MOXMAN_Exception::INVALID_FILE_NAME
				);
			}

			$result[] = $this->fileToJson($file);

			if ($file->exists()) {
				$this->fireBeforeFileAction(MOXMAN_Vfs_FileActionEventArgs::DELETE, $file);
				$file->delete(true);
				$this->fireFileAction(MOXMAN_Vfs_FileActionEventArgs::DELETE, $file);
			}
		}

		return $result;
	}
}

?>