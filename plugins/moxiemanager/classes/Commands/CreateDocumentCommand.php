<?php
/**
 * CreateDocumentCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command that creates directories.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_CreateDocumentCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		if (!isset($params->template)) {
			throw new MOXMAN_Exception(
				"You must specify a path to a template file to be used when creating documents"
			);
		}

		$templateFile = MOXMAN::getFile($params->template);
		$file = MOXMAN::getFile($params->path, $params->name . '.' . MOXMAN_Util_PathUtils::getExtension($templateFile->getName()));

		$config = $file->getConfig();

		if ($config->get('general.demo')) {
			throw new MOXMAN_Exception(
				"This action is restricted in demo mode.",
				MOXMAN_Exception::DEMO_MODE
			);
		}

		if (!$file->canWrite()) {
			throw new MOXMAN_Exception(
				"No write access to file: " . $file->getPublicPath(),
				MOXMAN_Exception::NO_WRITE_ACCESS
			);
		}

		if ($file->exists()) {
			throw new MOXMAN_Exception(
				"File already exist: " . $file->getPublicPath(),
				MOXMAN_Exception::FILE_EXISTS
			);
		}

		$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "createdoc");
		if (!$filter->accept($file, true)) {
			throw new MOXMAN_Exception(
				"Invalid file name for: " . $file->getPublicPath(),
				MOXMAN_Exception::INVALID_FILE_NAME
			);
		}

		// TODO: Security audit this
		$stream = $templateFile->open(MOXMAN_Vfs_IFileStream::READ);
		if ($stream) {
			$content = $stream->readToEnd();
			$stream->close();

		}

		// Replace fields
		if (isset($params->fields)) {
			foreach ($params->fields as $key => $value) {
				$content = str_replace('${' . $key . '}', htmlentities($value), $content);
			}
		}

		$args = $this->fireBeforeFileAction("add", $file, strlen($content));
		$file = $args->getFile();

		// Write contents to file
		$stream = $file->open(MOXMAN_Vfs_IFileStream::WRITE);
		if ($stream) {
			$stream->write($content);
			$stream->close();
		}

		$this->fireFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $file);

		return $this->fileToJson($file, true);
	}
}

?>