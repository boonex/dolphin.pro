<?php
/**
 * PutFileContentsCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command that creates files.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_PutFileContentsCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		$file = MOXMAN::getFile($params->path);
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

		$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "edit");
		if (!$filter->accept($file, true)) {
			throw new MOXMAN_Exception(
				"Invalid file name for: " . $file->getPublicPath(),
				MOXMAN_Exception::INVALID_FILE_NAME
			);
		}

		if ($file->exists()) {
			$args = $this->fireBeforeFileAction(MOXMAN_Vfs_FileActionEventArgs::DELETE, $file);
			$file = $args->getFile();
			$file->delete(true);
			$this->fireFileAction(MOXMAN_Vfs_FileActionEventArgs::DELETE, $file);
		}

		$encoding = $config->get("edit.encoding", "utf-8");
		$lineEndings = $config->get("edit.line_endings", "lf");

		// Normalize line endings to unix style
		$content = str_replace("\r\n", "\n", $params->content);

		// Force line endings
		if ($lineEndings == "crlf") {
			$content = str_replace("\n", "\r\n", $content);
		}

		// Encode
		if ($encoding != "utf-8") {
			$content = iconv("utf-8", $encoding, $content);
		}

		// Fire before file action add event
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