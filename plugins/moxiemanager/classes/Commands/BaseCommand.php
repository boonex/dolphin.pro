<?php
/**
 * BaseCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Class to be extended by other core plugin classes. Provides basic functionality shared by all commands.
 *
 * @package MOXMAN_Commands
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class MOXMAN_Commands_BaseCommand implements MOXMAN_ICommand {
	/**
	 * Fires a file action event with the specified file object and files array.
	 *
	 * @param string $action Action for files event for example LIST_FILES.
	 * @param MOXMAN_Vfs_IFile $file File instance to use.
	 * @param array $files Array with files to include in event.
	 * @return MOXMAN_Vfs_FileActionEventArgs Returns event argument instance.
	 */
	protected function fireFilesAction($action, $file, $files) {
		$args = new MOXMAN_Vfs_FileActionEventArgs($action, $file);
		$args->setFileList($files);

		return MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
	}

	/**
	 * Fires a file action event with the specified file object.
	 *
	 * @param string $action Action name for the file event for example DELETE.
	 * @param MOXMAN_Vfs_IFile $file File instance to use.
	 * @return MOXMAN_Vfs_FileActionEventArgs Returns event argument instance.
	 */
	protected function fireFileAction($action, $file) {
		$args = new MOXMAN_Vfs_FileActionEventArgs($action, $file);

		return MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
	}

	/**
	 * Fires a file action event with the specified from/to file objects.
	 *
	 * @param string $action Action name for the file event for example COPY.
	 * @param MOXMAN_Vfs_IFile $fromFile From file to use.
	 * @param MOXMAN_Vfs_IFile $toFile To file to use.
	 * @return MOXMAN_Vfs_FileActionEventArgs Returns event argument instance.
	 */
	protected function fireTargetFileAction($action, $fromFile, $toFile) {
		$args = new MOXMAN_Vfs_FileActionEventArgs($action, $fromFile);
		$args->setTargetFile($toFile);

		return MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
	}

	/**
	 * Fires a before file action event with the specified file object.
	 *
	 * @param string $action Action name for the file event for example DELETE.
	 * @param MOXMAN_Vfs_IFile $file File instance to use.
	 * @param Number $size Size of the file being added.
	 * @return MOXMAN_Vfs_FileActionEventArgs Returns event argument instance.
	 */
	protected function fireBeforeFileAction($action, $file, $size = 0) {
		$args = new MOXMAN_Vfs_FileActionEventArgs($action, $file);
		$args->getData()->fileSize = $size;

		return MOXMAN::getPluginManager()->get("core")->fire("BeforeFileAction", $args);
	}

	/**
	 * Fires a before file action event with the specified from/to file objects.
	 *
	 * @param string $action Action name for the file event for example COPY.
	 * @param MOXMAN_Vfs_IFile $fromFile From file to use.
	 * @param MOXMAN_Vfs_IFile $toFile To file to use.
	 * @return MOXMAN_Vfs_FileActionEventArgs Returns event argument instance.
	 */
	protected function fireBeforeTargetFileAction($action, $fromFile, $toFile) {
		$args = new MOXMAN_Vfs_FileActionEventArgs($action, $fromFile);
		$args->setTargetFile($toFile);

		return MOXMAN::getPluginManager()->get("core")->fire("BeforeFileAction", $args);
	}

	/**
	 * Fires a file custom info for the specified file.
	 *
	 * @param string $type Type of info event.
	 * @param MOXMAN_Vfs_IFile $file File instance to use.
	 * @return MOXMAN_Vfs_CustomInfoEventArgs Returns event argument instance.
	 */
	protected function fireCustomInfo($type, $file) {
		$args = new MOXMAN_Vfs_CustomInfoEventArgs($type, $file);

		return MOXMAN::getPluginManager()->get("core")->fire("CustomInfo", $args);
	}

	/**
	 * Converts a file instance to a JSON serializable object.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to convert into JSON format.
	 * @param Boolean $meta State if the meta data should be returned or not.
	 * @return stdClass JSON serializable object.
	 */
	protected function fileToJson($file, $meta = false) {
		// TODO: Maybe move to something else?
		return MOXMAN_CorePlugin::fileToJson($file, $meta);
	}

	/**
	 * Returns public config options to the client.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to get public config options for.
	 * @return stdClass JSON serializable object of config options.
	 */
	protected function getPublicConfig($file = null) {
		$exposed = array(
			"general.hidden_tools" => "",
			"general.disabled_tools" => "",
			"filesystem.extensions" => "*",
			"filesystem.force_directory_template" => false,
			"upload.maxsize" => "10mb",
			"upload.chunk_size" => "2mb",
			"upload.extensions" => "*",
			"createdoc.templates" => "",
			"createdoc.fields" => "",
			"createdir.templates" => ""
		);

		$result = array();
		$config = $file ? $file->getConfig() : MOXMAN::getConfig();
		foreach ($exposed as $name => $default) {
			$result[$name] = $config->get($name, $default);
		}

		return (object) $result;
	}
}
?>