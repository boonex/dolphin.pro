<?php
/**
 * CorePlugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Core plugin contains core commands and logic.
 *
 * @package MOXMAN
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MOXMAN_CorePlugin implements MOXMAN_IPlugin, MOXMAN_ICommandHandler, MOXMAN_Http_IHandler {
	/** @ignore */
	private $dispatcher, $commands;

	// @codeCoverageIgnoreStart

	/** @ignore */
	public function __construct() {
		$this->dispatcher = new MOXMAN_Util_EventDispatcher();

		// Listen for add/remove/stream events to generate thumbnails
		$this->bind("FileAction", "onFileAction", $this);
	}

	/**
	 * Initializes the core plugin.
	 */
	public function init() {
		$this->commands = new MOXMAN_CommandCollection();

		// Map commands to classes
		$this->commands->addClasses(array(
			"Install" => "MOXMAN_Commands_InstallCommand",
			"AlterImage" => "MOXMAN_Commands_AlterImageCommand",
			"CopyTo" => "MOXMAN_Commands_CopyToCommand",
			"CreateDirectory" => "MOXMAN_Commands_CreateDirectoryCommand",
			"CreateDocument" => "MOXMAN_Commands_CreateDocumentCommand",
			"PutFileContents" => "MOXMAN_Commands_PutFileContentsCommand",
			"Delete" => "MOXMAN_Commands_DeleteCommand",
			"FileInfo" => "MOXMAN_Commands_FileInfoCommand",
			"ListFiles" => "MOXMAN_Commands_ListFilesCommand",
			"ListRoots" => "MOXMAN_Commands_ListRootsCommand",
			"Loopback" => "MOXMAN_Commands_LoopbackCommand",
			"MoveTo" => "MOXMAN_Commands_MoveToCommand",
			"Zip" => "MOXMAN_Commands_ZipCommand",
			"UnZip" => "MOXMAN_Commands_UnZipCommand",
			"GetAppKeys" => "MOXMAN_Commands_GetAppKeysCommand",
			"GetFileContents" => "MOXMAN_Commands_GetFileContentsCommand",
			"GetConfig" => "MOXMAN_Commands_GetConfigCommand",
			"ImportFromUrl" => "MOXMAN_Commands_ImportFromUrlCommand",
			"Login" => "MOXMAN_Commands_LoginCommand",
			"Logout" => "MOXMAN_Commands_LogoutCommand"
		));
	}

	// @codeCoverageIgnoreEnd

	/**
	 * Gets executed when a RPC call is made.
	 *
	 * @param string $name Name of RPC command to execute.
	 * @param Object $params Object passed in from RPC handler.
	 * @return Object Return object that gets passed back to client.
	 */
	public function execute($name, $params) {
		return $this->commands->execute($name, $params);
	}

	/**
	 * Process a request using the specified context.
	 *
	 * @param MOXMAN_Http_Context $httpContext Context instance to pass to use for the handler.
	 */
	public function processRequest(MOXMAN_Http_Context $httpContext) {
		$request = $httpContext->getRequest();

		if ($request->get("json")) {
			$instance = new MOXMAN_Handlers_JsonRpcHandler();
			$instance->processRequest($httpContext);
		}

		// TODO: Make this nicer, switch?
		$action = strtolower($request->get("action", ""));

		if ($action == "debug") {
			$instance = new MOXMAN_Handlers_DebugHandler();
			$instance->processRequest($httpContext);
		}

		if ($action == "download") {
			$instance = new MOXMAN_Handlers_DownloadHandler();
			$instance->processRequest($httpContext);
		}

		if ($action == "upload") {
			$instance = new MOXMAN_Handlers_UploadHandler();
			$instance->processRequest($httpContext);
		}

		if ($action == "streamfile") {
			$instance = new MOXMAN_Handlers_StreamFileHandler($this);
			$instance->processRequest($httpContext);
		}

		if ($action == "language") {
			$instance = new MOXMAN_Handlers_LanguageHandler($this);
			$instance->processRequest($httpContext);
		}

		if ($action == "auth") {
			$instance = new MOXMAN_Handlers_AuthHandler($this);
			$instance->processRequest($httpContext);
		}

		// @codeCoverageIgnoreStart
		if ($action == "pluginjs") {
			$instance = new MOXMAN_Handlers_PluginJsHandler($this);
			$instance->processRequest($httpContext);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * This method will fire a specific event by name with the specified event args instance.
	 *
	 * @param string $name Name of the event to fire for example custom info.
	 * @param MOXMAN_Util_EventArgs $args Event args to pass to all event listeners.
	 * @return MOXMAN_PluginManager PluginManager instance to enable chainablity.
	 */
	public function fire($name, MOXMAN_Util_EventArgs $args) {
		return $this->dispatcher->dispatch($this, $name, $args);
	}

	/**
	 * Binds a specific event by name for a specific plugin instance.
	 *
	 * @param string $name Event name to bind.
	 * @param string $func String name of the function to call.
	 * @param MOXMAN_Plugin $plugin Plugin instance to call event method on.
	 * @return MOXMAN_PluginManager PluginManager instance to enable chainablity.
	 */
	public function bind($name, $func, $plugin) {
		return $this->dispatcher->add($name, $func, $plugin);
	}

	/**
	 * Unbinds a specific event by name from a specific plugin instance.
	 *
	 * @param string $name Event name to unbind.
	 * @param string $func String name of the function not to call.
	 * @param MOXMAN_IPlugin $plugin Plugin instance to not call event method on.
	 * @return MOXMAN_PluginManager PluginManager instance to enable chainablity.
	 */
	public function unbind($name, $func, $plugin) {
		return $this->dispatcher->remove($name, $func, $plugin);
	}

	/**
	 * Event handler function. Gets executed when a file action event occurs.
	 *
	 * @param MOXMAN_Vfs_FileActionEventArgs $args File action event arguments.
	 */
	public function onFileAction(MOXMAN_Vfs_FileActionEventArgs $args) {
		if (isset($args->getData()->thumb)) {
			return;
		}

		switch ($args->getAction()) {
			case MOXMAN_Vfs_FileActionEventArgs::DELETE:
				$this->deleteThumbnail($args->getFile());
				break;

			case MOXMAN_Vfs_FileActionEventArgs::COPY:
				$this->copyThumbnail($args->getFile(), $args->getTargetFile());
				break;

			case MOXMAN_Vfs_FileActionEventArgs::MOVE:
				$this->moveThumbnail($args->getFile(), $args->getTargetFile());
				break;
		}
	}

	public function getThumbnail(MOXMAN_Vfs_IFile $file) {
		$config = $file->getConfig();

		if ($config->get('thumbnail.enabled') !== true) {
			return $file;
		}

		$thumbnailFolderPath = MOXMAN_Util_PathUtils::combine($file->getParent(), $config->get('thumbnail.folder'));
		$thumbnailFile = MOXMAN::getFile($thumbnailFolderPath, $config->get('thumbnail.prefix') . $file->getName());

		return $thumbnailFile;
	}

	/**
	 * Creates a thumbnail for the specified file and returns that file object
	 * or the input file if thumbnails are disabled or not supported.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to generate thumbnail for.
	 * @return MOXMAN_Vfs_IFile File instance that got generated or input file.
	 */
	public function createThumbnail(MOXMAN_Vfs_IFile $file, $localTempFile = null) {
		$config = $file->getConfig();

		// Thumbnails disabled in config
		if (!$config->get('thumbnail.enabled')) {
			return $file;
		}

		// File is not an image
		if (!MOXMAN_Media_ImageAlter::canEdit($file)) {
			return $file;
		}

		// No write access to parent path
		$dirFile = $file->getParentFile();
		if (!$dirFile->canWrite()) {
			return $file;
		}

		$thumbnailFolderPath = MOXMAN_Util_PathUtils::combine($file->getParent(), $config->get('thumbnail.folder'));
		$thumbnailFile = MOXMAN::getFile($thumbnailFolderPath, $config->get('thumbnail.prefix') . $file->getName());

		// Never generate thumbs in thumbs dirs
		if (basename($file->getParent()) == $config->get('thumbnail.folder')) {
			return $file;
		}

		$thumbnailFolderFile = $thumbnailFile->getParentFile();
		if ($thumbnailFile->exists()) {
			if ($file->isDirectory()) {
				return $file;
			}

			return $thumbnailFile;
		}

		if (!$thumbnailFolderFile->exists()) {
			$thumbnailFolderFile->mkdir();
			$this->fireThumbnailFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $thumbnailFolderFile);
		}

		// TODO: Maybe implement this inside MOXMAN_Media_ImageAlter
		if ($file instanceof MOXMAN_Vfs_Local_File) {
			if ($config->get('thumbnail.use_exif') && function_exists("exif_thumbnail") && preg_match('/jpe?g/i', MOXMAN_Util_PathUtils::getExtension($file->getName()))) {
				$imageType = null;
				$width = 0;
				$height = 0;

				try {
					// Silently fail this, hence the @, some exif data can be corrupt.
					$exifImage = @exif_thumbnail(
						$localTempFile ? $localTempFile : $file->getInternalPath(),
						$width,
						$height,
						$imageType
					);

					if ($exifImage) {
						$stream = $thumbnailFile->open(MOXMAN_Vfs_IFileStream::WRITE);
						$stream->write($exifImage);
						$stream->close();

						$this->fireThumbnailFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $thumbnailFile);
						return $thumbnailFile;
					}
				} catch (Exception $e) {
					// Ignore exif failure
				}
			}
		}

		$imageAlter = new MOXMAN_Media_ImageAlter();

		if ($localTempFile) {
			$imageAlter->load($localTempFile);
		} else {
			$imageAlter->loadFromFile($file);
		}

		$imageAlter->createThumbnail($config->get('thumbnail.width'), $config->get('thumbnail.height'), $config->get('thumbnail.mode', "resize"));
		$imageAlter->saveToFile($thumbnailFile, $config->get('thumbnail.jpeg_quality'));

		$this->fireThumbnailFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $thumbnailFile);

		return $thumbnailFile;
	}

	/**
	 * Deletes any existing thumbnail for the specified file.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to remove thumbnail for.
	 */
	public function deleteThumbnail(MOXMAN_Vfs_IFile $file) {
		if ($file->isDirectory() || !MOXMAN_Media_ImageAlter::canEdit($file)) {
			return false;
		}

		$config = $file->getConfig();

		if (!$config->get('thumbnail.delete')) {
			return false;
		}

		// Delete thumbnail file
		$thumbnailFolderPath = MOXMAN_Util_PathUtils::combine($file->getParent(), $config->get('thumbnail.folder'));
		$thumbnailFile = MOXMAN::getFile($thumbnailFolderPath, $config->get('thumbnail.prefix') . $file->getName());

		if ($thumbnailFile->exists()) {
			$thumbnailFile->delete();
			$this->fireThumbnailFileAction(MOXMAN_Vfs_FileActionEventArgs::DELETE, $thumbnailFile);

			// Was this last image in folder, if so, delete it.
			$thumbnailFolder = $thumbnailFile->getParentFile();
			if (count($thumbnailFolder->listFiles()->limit(0, 1)->toArray()) == 0) {
				$thumbnailFolder->delete();

				$args = new MOXMAN_Vfs_FileActionEventArgs(MOXMAN_Vfs_FileActionEventArgs::DELETE, $thumbnailFolder);
				MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
			}
			return true;
		}

		return false;
	}

	public function copyThumbnail(MOXMAN_Vfs_IFile $fromFile, MOXMAN_Vfs_IFile $toFile) {
		if ($fromFile->isDirectory() || !MOXMAN_Media_ImageAlter::canEdit($fromFile)) {
			return false;
		}

		$config = $fromFile->getConfig();

		// From thumbnail
		$fromThumbnailFolderPath = MOXMAN_Util_PathUtils::combine($fromFile->getParent(), $config->get('thumbnail.folder'));
		$fromThumbnailFile = MOXMAN::getFile($fromThumbnailFolderPath, $config->get('thumbnail.prefix') . $fromFile->getName());

		// To thumbnail
		$toThumbnailFolderPath = MOXMAN_Util_PathUtils::combine($toFile->getParent(), $config->get('thumbnail.folder'));
		$toThumbnailFile = MOXMAN::getFile($toThumbnailFolderPath, $config->get('thumbnail.prefix') . $toFile->getName());

		$thumbnailFolderFile = $toThumbnailFile->getParentFile();
		if (!$thumbnailFolderFile->exists()) {
			$thumbnailFolderFile->mkdir();
			$this->fireThumbnailFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $thumbnailFolderFile);
		}

		if ($fromThumbnailFile->exists()) {
			$fromThumbnailFile->copyTo($toThumbnailFile);
			$this->fireThumbnailTargetFileAction(MOXMAN_Vfs_FileActionEventArgs::COPY, $fromThumbnailFile, $toThumbnailFile);
			return true;
		}

		return false;
	}

	public function moveThumbnail(MOXMAN_Vfs_IFile $fromFile, MOXMAN_Vfs_IFile $toFile) {
		if ($fromFile->isDirectory() || !MOXMAN_Media_ImageAlter::canEdit($fromFile)) {
			return false;
		}

		$config = $fromFile->getConfig();

		// From thumbnail
		$fromThumbnailFolderPath = MOXMAN_Util_PathUtils::combine($fromFile->getParent(), $config->get('thumbnail.folder'));
		$fromThumbnailFile = MOXMAN::getFile($fromThumbnailFolderPath, $config->get('thumbnail.prefix') . $fromFile->getName());

		// To thumbnail
		$toThumbnailFolderPath = MOXMAN_Util_PathUtils::combine($toFile->getParent(), $config->get('thumbnail.folder'));
		$toThumbnailFile = MOXMAN::getFile($toThumbnailFolderPath, $config->get('thumbnail.prefix') . $toFile->getName());

		$thumbnailFolderFile = $toThumbnailFile->getParentFile();
		if (!$thumbnailFolderFile->exists()) {
			$thumbnailFolderFile->mkdir();
			$this->fireThumbnailFileAction(MOXMAN_Vfs_FileActionEventArgs::ADD, $thumbnailFolderFile);
		}

		if ($fromThumbnailFile->exists()) {
			$fromThumbnailFile->moveTo($toThumbnailFile);
			$this->fireThumbnailTargetFileAction(MOXMAN_Vfs_FileActionEventArgs::MOVE, $fromThumbnailFile, $toThumbnailFile);
			return true;
		}

		return false;
	}

	/**
	 * Converts a file instance to a JSON serializable object.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to convert into JSON format.
	 * @param Boolean $meta State if the meta data should be returned or not.
	 * @return stdClass JSON serializable object.
	 */
	public static function fileToJson($file, $meta = false) {
		$config = $file->getConfig();

		$renameFilter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "rename");
		$editFilter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "edit");
		$viewFilter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "view");

		$result = (object) array(
			"path" => $file->getPublicPath(),
			"size" => $file->getSize(),
			"lastModified" => $file->getLastModified(),
			"isFile" => $file->isFile(),
			"canRead" => $file->canRead(),
			"canWrite" => $file->canWrite(),
			"canEdit" => $file->isFile() && $editFilter->accept($file),
			"canRename" => $renameFilter->accept($file),
			"canView" => $file->isFile() && $viewFilter->accept($file),
			"canPreview" => $file->isFile() && MOXMAN_Media_ImageAlter::canEdit($file),
			"exists" => $file->exists()
		);

		if ($meta) {
			$args = new MOXMAN_Vfs_CustomInfoEventArgs(MOXMAN_Vfs_CustomInfoEventArgs::INSERT_TYPE, $file);
			MOXMAN::getPluginManager()->get("core")->fire("CustomInfo", $args);
			$metaData = (object) array_merge($file->getMetaData()->getAll(), $args->getInfo());

			if (MOXMAN_Media_ImageAlter::canEdit($file)) {
				$thumbnailFolderPath = MOXMAN_Util_PathUtils::combine($file->getParent(), $config->get('thumbnail.folder'));
				$thumbnailFile = MOXMAN::getFile($thumbnailFolderPath, $config->get('thumbnail.prefix') . $file->getName());

				// TODO: Implement stat info cache layer here
				if ($file instanceof MOXMAN_Vfs_Local_File) {
					$info = MOXMAN_Media_MediaInfo::getInfo($file->getPath());
					$metaData->width = $info["width"];
					$metaData->height = $info["height"];
				}

				if ($thumbnailFile->exists()) {
					$metaData->thumb_url = $thumbnailFile->getUrl();

					// Get image size server side only on local filesystem
					if ($file instanceof MOXMAN_Vfs_Local_File) {
						$info = MOXMAN_Media_MediaInfo::getInfo($thumbnailFile->getPath());
						$metaData->thumb_width = $info["width"];
						$metaData->thumb_height = $info["height"];
					}
				}
			}

			$metaData->url = $file->getUrl();
			$result->meta = $metaData;
		}

		return $result;
	}

	public function deleteFile(MOXMAN_Vfs_IFile $file) {
		MOXMAN::getPluginManager()->get("core")->deleteThumbnail($file);

		// Fire file actions for delete operation in a overwrite
		$args = new MOXMAN_Vfs_FileActionEventArgs("delete", $file);
		MOXMAN::getPluginManager()->get("core")->fire("BeforeFileAction", $args);
		$args = new MOXMAN_Vfs_FileActionEventArgs("delete", $file);
		MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);

		$file->delete();
	}

	/** @ignore */
	private function fireThumbnailFileAction($action, $file, $data = array()) {
		$args = new MOXMAN_Vfs_FileActionEventArgs($action, $file);
		$args->getData()->thumb = true;

		if (!empty($data)) {
			foreach ($data as $key => $value) {
				$args->getData()->{$key} = $value;
			}
		}

		return MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
	}

	protected function fireThumbnailTargetFileAction($action, $fromFile, $toFile) {
		$args = new MOXMAN_Vfs_FileActionEventArgs($action, $fromFile);
		$args->setTargetFile($toFile);
		$args->getData()->thumb = true;

		return MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);
	}
}

MOXMAN::getPluginManager()->add("core", new MOXMAN_CorePlugin());

?>